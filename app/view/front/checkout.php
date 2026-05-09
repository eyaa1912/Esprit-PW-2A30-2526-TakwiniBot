<?php
session_start();
// ============================================================
//  DB CONNECTION
// ============================================================
$host = 'localhost';
$db   = 'takwini_db';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connexion échouée : " . $e->getMessage());
}

// ============================================================
//  FONCTION POUR ENVOYER LE CODE OTP PAR EMAIL
// ============================================================
function sendOTPByEmail($to_email, $to_name, $otp_code) {
    $subject = "🔐 Votre code de confirmation Takwinibot";
    
    $message = "Bonjour $to_name,\n\n";
    $message .= "Votre code de confirmation est : $otp_code\n\n";
    $message .= "Ce code expire dans 5 minutes.\n\n";
    $message .= "Si vous n'avez pas demandé ce code, ignorez cet email.\n\n";
    $message .= "Cordialement,\n";
    $message .= "Takwinibot";
    
    $headers = "From: no-reply@takwinibot.com\r\n";
    $headers .= "Reply-To: support@takwinibot.com\r\n";
    
    return mail($to_email, $subject, $message, $headers);
}

// ============================================================
//  AJAX: GENERER ET ENVOYER UN CODE OTP
// ============================================================
if (isset($_POST['action']) && $_POST['action'] === 'send_otp') {
    header('Content-Type: application/json');
    
    $email = $_POST['email'] ?? '';
    $nom = $_POST['nom'] ?? '';
    
    if (empty($email)) {
        echo json_encode(['success' => false, 'message' => 'Email requis']);
        exit();
    }
    
    $otp_code = rand(100000, 999999);
    
    $_SESSION['payment_otp'] = [
        'code' => $otp_code,
        'expires' => time() + 300,
        'email' => $email
    ];
    
    $email_sent = sendOTPByEmail($email, $nom, $otp_code);
    
    if ($email_sent) {
        echo json_encode(['success' => true, 'message' => 'Code envoyé à votre email']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'envoi de l\'email']);
    }
    exit();
}

// ============================================================
//  AJAX: VERIFIER LE CODE OTP
// ============================================================
if (isset($_POST['action']) && $_POST['action'] === 'verify_otp') {
    header('Content-Type: application/json');
    
    $user_code = $_POST['otp_code'] ?? '';
    
    if (!isset($_SESSION['payment_otp'])) {
        echo json_encode(['success' => false, 'message' => 'Aucun code demandé']);
        exit();
    }
    
    $stored_otp = $_SESSION['payment_otp'];
    
    if (time() > $stored_otp['expires']) {
        unset($_SESSION['payment_otp']);
        echo json_encode(['success' => false, 'message' => 'Code expiré. Veuillez recommencer']);
        exit();
    }
    
    if ($user_code == $stored_otp['code']) {
        unset($_SESSION['payment_otp']);
        echo json_encode(['success' => true, 'message' => 'Code valide']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Code incorrect']);
    }
    exit();
}

// ============================================================
//  PROCESS MULTI-ITEM ORDER FROM CART
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_order'])) {
    $cart_items   = json_decode($_POST['cart_items'] ?? '[]', true);
    $nom_complet  = trim($_POST['nom_complet']);
    $email        = trim($_POST['email']);
    $telephone    = trim($_POST['telephone']);
    $adresse      = trim($_POST['adresse']);
    $payment_method = trim($_POST['payment_method'] ?? 'non specifie');
    $payment_phone  = trim($_POST['payment_phone'] ?? '');
    $statut_initial = ($payment_method === 'flouci' || $payment_method === 'd17') ? 'en attente paiement' : 'en attente';

    if (empty($cart_items) || empty($nom_complet) || empty($email) || empty($telephone) || empty($adresse)) {
        $_SESSION['order_error'] = "Veuillez remplir tous les champs!";
        header("Location: checkout.php");
        exit();
    }

    try {
        $pdo->beginTransaction();
        
        // Calculate total and verify stock
        $total = 0;
        $order_items = [];
        
        foreach ($cart_items as $item) {
            $stmt = $pdo->prepare("SELECT * FROM produit WHERE id = ?");
            $stmt->execute([$item['id']]);
            $product = $stmt->fetch();
            
            if (!$product) {
                throw new Exception("Produit introuvable: " . ($item['nom'] ?? 'ID ' . $item['id']));
            }
            
            if ($product['stock'] < $item['qty']) {
                throw new Exception("Stock insuffisant pour {$product['nom']}. Disponible: {$product['stock']}");
            }
            
            $item_total = $product['prix'] * $item['qty'];
            $total += $item_total;
            
            $order_items[] = [
                'product' => $product,
                'qty' => $item['qty'],
                'prix_unitaire' => $product['prix']
            ];
        }
        
        // Insert commande
        $stmt = $pdo->prepare("
            INSERT INTO commande 
                (date_commande, statut, total, nom_client, email_client,
                 telephone_client, adresse_livraison, payment_method, payment_phone)
            VALUES (NOW(), ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $statut_initial,
            $total,
            $nom_complet,
            $email,
            $telephone,
            $adresse,
            $payment_method,
            $payment_phone,
        ]);
        $commande_id = $pdo->lastInsertId();
        
        // Insert ligne_commande and update stock
        foreach ($order_items as $item) {
            $stmt = $pdo->prepare("INSERT INTO ligne_commande (commande_id, produit_id, quantite, prix_unitaire) VALUES (?, ?, ?, ?)");
            $stmt->execute([$commande_id, $item['product']['id'], $item['qty'], $item['prix_unitaire']]);
            
            $stmt = $pdo->prepare("UPDATE produit SET stock = stock - ? WHERE id = ?");
            $stmt->execute([$item['qty'], $item['product']['id']]);
        }
        
        $pdo->commit();
        
        $_SESSION['order_success'] = "Commande #$commande_id créée avec succès! Méthode: $payment_method";
        header("Location: produits.php");
        exit();
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['order_error'] = "Erreur: " . $e->getMessage();
        header("Location: checkout.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paiement - Takwinibot</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/fonts/font-awesome.min.css">
    <style>
        :root {
            --navy: #0f172a;
            --accent: #3bafda;
            --green: #10b981;
            --red: #ef4444;
            --gray: #64748b;
            --light: #e2e8f0;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DM Sans', sans-serif;
            background: #f8fafc;
            color: var(--navy);
        }
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Syne', sans-serif;
        }
        
        /* Checkout Layout */
        .checkout-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 24px;
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 32px;
        }
        @media (max-width: 768px) {
            .checkout-container {
                grid-template-columns: 1fr;
                padding: 20px;
            }
        }
        
        /* Header */
        .checkout-header {
            background: var(--navy);
            padding: 20px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .checkout-header .logo {
            font-family: 'Syne', sans-serif;
            font-size: 22px;
            font-weight: 800;
            color: #fff;
            text-decoration: none;
        }
        .checkout-header .back-link {
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            font-size: 14px;
            transition: color 0.2s;
        }
        .checkout-header .back-link:hover {
            color: #fff;
        }
        
        /* Form Section */
        .form-section {
            background: #fff;
            border-radius: 20px;
            padding: 32px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        }
        .form-section h2 {
            font-size: 24px;
            font-weight: 800;
            margin-bottom: 8px;
        }
        .form-section .sub {
            color: var(--gray);
            font-size: 14px;
            margin-bottom: 28px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 6px;
            color: var(--navy);
        }
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid var(--light);
            border-radius: 12px;
            font-size: 14px;
            font-family: 'DM Sans', sans-serif;
            transition: border-color 0.2s;
            outline: none;
        }
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            border-color: var(--accent);
        }
        
        /* Payment Methods */
        .payment-methods {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-bottom: 20px;
        }
        .payment-option {
            border: 2px solid var(--light);
            border-radius: 12px;
            padding: 14px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            background: #fff;
        }
        .payment-option.selected {
            border-color: var(--accent);
            background: #f0f9ff;
        }
        .payment-option .icon {
            font-size: 24px;
            display: block;
            margin-bottom: 6px;
        }
        .payment-option .title {
            font-weight: 700;
            font-size: 13px;
        }
        
        .phone-field {
            display: none;
            margin-bottom: 20px;
        }
        .phone-field.active {
            display: block;
        }
        .phone-input-wrap {
            display: flex;
            align-items: center;
            border: 2px solid var(--light);
            border-radius: 12px;
            overflow: hidden;
        }
        .phone-prefix {
            padding: 12px 15px;
            background: #f0fdf4;
            color: var(--green);
            font-weight: 700;
            border-right: 2px solid var(--light);
        }
        .phone-input-wrap input {
            border: none;
            flex: 1;
            padding: 12px 15px;
            outline: none;
            font-size: 14px;
        }
        
        .cod-info {
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 12px;
            padding: 14px;
            font-size: 13px;
            color: #92400e;
            margin-bottom: 20px;
            display: none;
        }
        .cod-info.active {
            display: block;
        }
        
        /* Order Summary */
        .order-summary {
            background: #fff;
            border-radius: 20px;
            padding: 24px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            position: sticky;
            top: 90px;
            height: fit-content;
        }
        .order-summary h3 {
            font-size: 18px;
            font-weight: 800;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 2px solid var(--light);
        }
        .cart-items-summary {
            max-height: 300px;
            overflow-y: auto;
            margin-bottom: 20px;
        }
        .summary-item {
            display: flex;
            gap: 12px;
            padding: 10px 0;
            border-bottom: 1px solid var(--light);
        }
        .summary-item-img {
            width: 50px;
            height: 50px;
            background: #f1f5f9;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            flex-shrink: 0;
        }
        .summary-item-details {
            flex: 1;
        }
        .summary-item-details .name {
            font-weight: 700;
            font-size: 13px;
        }
        .summary-item-details .price {
            font-size: 12px;
            color: var(--accent);
            font-weight: 600;
        }
        .summary-item-qty {
            font-size: 12px;
            color: var(--gray);
        }
        .summary-total {
            border-top: 2px solid var(--light);
            padding-top: 16px;
            margin-top: 8px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            padding: 6px 0;
        }
        .total-row.grand {
            font-size: 20px;
            font-weight: 800;
            color: var(--accent);
            border-top: 1px dashed var(--light);
            margin-top: 8px;
            padding-top: 12px;
        }
        
        .btn-submit {
            width: 100%;
            padding: 16px;
            background: var(--navy);
            color: #fff;
            border: none;
            border-radius: 14px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.2s;
            margin-top: 20px;
            font-family: 'DM Sans', sans-serif;
        }
        .btn-submit:hover {
            background: var(--accent);
        }
        .btn-submit:disabled {
            background: var(--gray);
            cursor: not-allowed;
        }
        
        /* OTP Modal */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.6);
            backdrop-filter: blur(4px);
            z-index: 1000;
            display: none;
            align-items: center;
            justify-content: center;
        }
        .modal-overlay.active {
            display: flex;
        }
        .otp-modal {
            background: #fff;
            border-radius: 24px;
            max-width: 450px;
            width: 90%;
            padding: 32px;
            text-align: center;
        }
        .otp-modal h3 {
            font-size: 22px;
            margin-bottom: 8px;
        }
        .otp-group {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin: 24px 0;
        }
        .otp-input {
            width: 50px;
            height: 58px;
            border: 2px solid var(--light);
            border-radius: 12px;
            text-align: center;
            font-size: 24px;
            font-weight: 700;
            outline: none;
        }
        .otp-input:focus {
            border-color: var(--accent);
        }
        .otp-timer {
            font-size: 13px;
            color: var(--gray);
            margin-bottom: 20px;
        }
        .otp-timer .time {
            color: var(--accent);
            font-weight: 700;
        }
        .btn-confirm {
            background: var(--green);
            color: #fff;
            border: none;
            padding: 14px 24px;
            border-radius: 12px;
            font-weight: 700;
            cursor: pointer;
            width: 100%;
        }
        .resend-link {
            background: none;
            border: none;
            color: var(--accent);
            cursor: pointer;
            margin-top: 16px;
            font-size: 13px;
        }
        .error-msg {
            color: var(--red);
            font-size: 13px;
            margin-top: 12px;
            display: none;
        }
        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255,255,255,0.3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
            margin-right: 8px;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        
        .alert {
            padding: 14px 18px;
            border-radius: 12px;
            margin-bottom: 20px;
        }
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
    </style>
</head>
<body>

<div class="checkout-header">
    <a href="produits.php" class="logo">Takwinibot</a>
    <a href="produits.php" class="back-link"><i class="fa fa-arrow-left"></i> Retour à la boutique</a>
</div>

<div class="checkout-container">
    <!-- LEFT: FORM -->
    <div class="form-section">
        <h2>Finaliser votre commande</h2>
        <p class="sub">Veuillez remplir vos informations de livraison</p>
        
        <?php if(isset($_SESSION['order_error'])): ?>
            <div class="alert alert-error">
                <i class="fa fa-exclamation-circle"></i> <?= $_SESSION['order_error'] ?>
                <?php unset($_SESSION['order_error']); ?>
            </div>
        <?php endif; ?>
        
        <form id="checkoutForm">
            <div class="form-group">
                <label><i class="fa fa-user"></i> Nom complet *</label>
                <input type="text" id="nom_complet" required placeholder="Votre nom et prénom">
            </div>
            
            <div class="form-group">
                <label><i class="fa fa-envelope"></i> Email *</label>
                <input type="email" id="email" required placeholder="votre@email.com">
            </div>
            
            <div class="form-group">
                <label><i class="fa fa-phone"></i> Téléphone *</label>
                <input type="tel" id="telephone" required placeholder="Ex: 22 333 444">
            </div>
            
            <div class="form-group">
                <label><i class="fa fa-map-marker"></i> Adresse de livraison *</label>
                <textarea id="adresse" rows="3" required placeholder="Rue, Ville, Code postal..."></textarea>
            </div>
            
            <div class="form-group">
                <label><i class="fa fa-credit-card"></i> Méthode de paiement *</label>
                <div class="payment-methods">
                    <div class="payment-option" data-method="flouci">
                        <span class="icon">📱</span>
                        <span class="title">Flouci</span>
                    </div>
                    <div class="payment-option" data-method="d17">
                        <span class="icon">🏦</span>
                        <span class="title">D17 (Poste)</span>
                    </div>
                    <div class="payment-option" data-method="livraison">
                        <span class="icon">🚚</span>
                        <span class="title">Paiement à la livraison</span>
                    </div>
                </div>
                <input type="hidden" id="selected_payment" value="flouci">
            </div>
            
            <div id="phoneField" class="phone-field active">
                <label><i class="fa fa-mobile"></i> Numéro Flouci/D17 *</label>
                <div class="phone-input-wrap">
                    <span class="phone-prefix">🇹🇳 +216</span>
                    <input type="tel" id="payment_phone" placeholder="12 345 678" maxlength="9">
                </div>
            </div>
            
            <div id="codInfo" class="cod-info">
                <i class="fa fa-info-circle"></i> Vous paierez en espèces à la réception de votre commande.
            </div>
        </form>
    </div>
    
    <!-- RIGHT: ORDER SUMMARY -->
    <div class="order-summary">
        <h3>Récapitulatif</h3>
        <div class="cart-items-summary" id="cartSummary">
            <div style="text-align:center;padding:20px;color:var(--gray);">
                <div class="loading-spinner" style="border-color:rgba(59,175,218,0.3);border-top-color:var(--accent);"></div>
                Chargement...
            </div>
        </div>
        <div class="summary-total">
            <div class="total-row">
                <span>Sous-total</span>
                <span id="subtotal">0.000 DT</span>
            </div>
            <div class="total-row grand">
                <span>Total TTC</span>
                <span id="grandTotal">0.000 DT</span>
            </div>
        </div>
        <button class="btn-submit" id="submitOrderBtn" onclick="initiatePayment()">
            <i class="fa fa-lock"></i> Passer commande
        </button>
    </div>
</div>

<!-- OTP MODAL -->
<div class="modal-overlay" id="otpModal">
    <div class="otp-modal">
        <h3>🔐 Vérification</h3>
        <p>Un code à 6 chiffres a été envoyé à :<br><strong id="otpEmailDisplay"></strong></p>
        <div class="otp-group">
            <input type="text" class="otp-input" maxlength="1" id="otp0" oninput="otpMove(0)" onkeydown="otpBack(event,0)">
            <input type="text" class="otp-input" maxlength="1" id="otp1" oninput="otpMove(1)" onkeydown="otpBack(event,1)">
            <input type="text" class="otp-input" maxlength="1" id="otp2" oninput="otpMove(2)" onkeydown="otpBack(event,2)">
            <input type="text" class="otp-input" maxlength="1" id="otp3" oninput="otpMove(3)" onkeydown="otpBack(event,3)">
            <input type="text" class="otp-input" maxlength="1" id="otp4" oninput="otpMove(4)" onkeydown="otpBack(event,4)">
            <input type="text" class="otp-input" maxlength="1" id="otp5" oninput="otpMove(5)" onkeydown="otpBack(event,5)">
        </div>
        <div class="otp-timer">Code valable <span class="time" id="otpTimer">02:00</span></div>
        <div class="error-msg" id="otpError"></div>
        <button class="btn-confirm" id="confirmOtpBtn" onclick="verifyOTP()">Confirmer le code</button>
        <button class="resend-link" onclick="resendOTP()">Renvoyer le code</button>
    </div>
</div>

<script>
// ============================================================
//  LOAD CART FROM SESSION STORAGE
// ============================================================
let cart = [];
let currentPaymentMethod = 'flouci';

function loadCart() {
    const savedCart = sessionStorage.getItem('tk_cart');
    if (savedCart) {
        cart = JSON.parse(savedCart);
    }
    
    if (cart.length === 0) {
        window.location.href = 'produits.php';
        return;
    }
    
    displayCartSummary();
}

function displayCartSummary() {
    const container = document.getElementById('cartSummary');
    let subtotal = 0;
    
    let html = '';
    cart.forEach(item => {
        const itemTotal = item.prix * item.qty;
        subtotal += itemTotal;
        
        html += `
            <div class="summary-item">
                <div class="summary-item-img">
                    ${item.icon || '🛍️'}
                </div>
                <div class="summary-item-details">
                    <div class="name">${escapeHtml(item.nom)}</div>
                    <div class="price">${item.prix.toFixed(3)} DT</div>
                    <div class="summary-item-qty">Quantité: ${item.qty}</div>
                </div>
                <div style="font-weight:700;">${itemTotal.toFixed(3)} DT</div>
            </div>
        `;
    });
    
    container.innerHTML = html;
    document.getElementById('subtotal').textContent = subtotal.toFixed(3) + ' DT';
    document.getElementById('grandTotal').textContent = subtotal.toFixed(3) + ' DT';
}

function escapeHtml(str) {
    return str.replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}

// ============================================================
//  PAYMENT METHOD SELECTION
// ============================================================
document.querySelectorAll('.payment-option').forEach(opt => {
    opt.addEventListener('click', function() {
        document.querySelectorAll('.payment-option').forEach(o => o.classList.remove('selected'));
        this.classList.add('selected');
        currentPaymentMethod = this.dataset.method;
        document.getElementById('selected_payment').value = currentPaymentMethod;
        
        const phoneField = document.getElementById('phoneField');
        const codInfo = document.getElementById('codInfo');
        
        if (currentPaymentMethod === 'livraison') {
            phoneField.classList.remove('active');
            codInfo.classList.add('active');
        } else {
            phoneField.classList.add('active');
            codInfo.classList.remove('active');
        }
    });
});

// ============================================================
//  OTP HANDLERS
// ============================================================
let otpSeconds = 120;
let otpInterval = null;

function otpMove(idx) {
    const input = document.getElementById(`otp${idx}`);
    input.value = input.value.replace(/\D/g, '').slice(-1);
    if (input.value && idx < 5) {
        document.getElementById(`otp${idx+1}`).focus();
    }
    if (idx === 5 && input.value) {
        verifyOTP();
    }
}

function otpBack(e, idx) {
    if (e.key === 'Backspace' && !document.getElementById(`otp${idx}`).value && idx > 0) {
        document.getElementById(`otp${idx-1}`).focus();
    }
}

function startOTPTimer() {
    otpSeconds = 120;
    if (otpInterval) clearInterval(otpInterval);
    updateTimerDisplay();
    otpInterval = setInterval(() => {
        otpSeconds--;
        updateTimerDisplay();
        if (otpSeconds <= 0) {
            clearInterval(otpInterval);
            document.getElementById('otpError').textContent = 'Code expiré. Veuillez renvoyer un nouveau code.';
            document.getElementById('otpError').style.display = 'block';
        }
    }, 1000);
}

function updateTimerDisplay() {
    const m = Math.floor(otpSeconds / 60);
    const s = otpSeconds % 60;
    document.getElementById('otpTimer').textContent = `${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
}

function getOtpCode() {
    let code = '';
    for (let i = 0; i < 6; i++) {
        code += document.getElementById(`otp${i}`).value;
    }
    return code;
}

function clearOtpInputs() {
    for (let i = 0; i < 6; i++) {
        const input = document.getElementById(`otp${i}`);
        input.value = '';
        input.classList.remove('filled');
    }
    document.getElementById('otp0').focus();
}

// ============================================================
//  SEND OTP AND INITIATE PAYMENT
// ============================================================
let pendingOrderData = null;

function initiatePayment() {
    const nom = document.getElementById('nom_complet').value.trim();
    const email = document.getElementById('email').value.trim();
    const telephone = document.getElementById('telephone').value.trim();
    const adresse = document.getElementById('adresse').value.trim();
    const paymentPhone = document.getElementById('payment_phone').value.replace(/\s/g, '');
    
    if (!nom || !email || !telephone || !adresse) {
        showError('Veuillez remplir tous les champs obligatoires.');
        return;
    }
    
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        showError('Adresse email invalide.');
        return;
    }
    
    if (telephone.replace(/\s/g, '').length < 8) {
        showError('Numéro de téléphone invalide.');
        return;
    }
    
    if (currentPaymentMethod !== 'livraison') {
        if (paymentPhone.length !== 8) {
            showError('Numéro Flouci/D17 invalide (8 chiffres requis).');
            return;
        }
    }
    
    pendingOrderData = {
        nom: nom,
        email: email,
        telephone: telephone,
        adresse: adresse,
        payment_method: currentPaymentMethod,
        payment_phone: paymentPhone,
        cart: cart
    };
    
    // If COD, submit directly
    if (currentPaymentMethod === 'livraison') {
        submitFinalOrder();
    } else {
        sendOTPCode(email, nom);
    }
}

function showError(msg) {
    const errorDiv = document.createElement('div');
    errorDiv.className = 'alert alert-error';
    errorDiv.innerHTML = '<i class="fa fa-exclamation-circle"></i> ' + msg;
    const container = document.querySelector('.form-section');
    const existing = container.querySelector('.alert-error');
    if (existing) existing.remove();
    container.insertBefore(errorDiv, container.firstChild);
    setTimeout(() => errorDiv.remove(), 5000);
}

function sendOTPCode(email, nom) {
    const btn = document.getElementById('submitOrderBtn');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="loading-spinner"></span> Envoi du code...';
    
    fetch(window.location.href, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=send_otp&email=${encodeURIComponent(email)}&nom=${encodeURIComponent(nom)}`
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = originalText;
        
        if (data.success) {
            document.getElementById('otpEmailDisplay').textContent = email;
            document.getElementById('otpModal').classList.add('active');
            clearOtpInputs();
            startOTPTimer();
            document.getElementById('otpError').style.display = 'none';
        } else {
            showError(data.message);
        }
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = originalText;
        showError('Erreur de connexion. Veuillez réessayer.');
    });
}

function resendOTP() {
    if (!pendingOrderData) return;
    
    fetch(window.location.href, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=send_otp&email=${encodeURIComponent(pendingOrderData.email)}&nom=${encodeURIComponent(pendingOrderData.nom)}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            startOTPTimer();
            document.getElementById('otpError').style.display = 'none';
        } else {
            document.getElementById('otpError').textContent = data.message;
            document.getElementById('otpError').style.display = 'block';
        }
    });
}

function verifyOTP() {
    const code = getOtpCode();
    if (code.length < 6) {
        document.getElementById('otpError').textContent = 'Entrez les 6 chiffres du code.';
        document.getElementById('otpError').style.display = 'block';
        return;
    }
    
    const confirmBtn = document.getElementById('confirmOtpBtn');
    const originalText = confirmBtn.innerHTML;
    confirmBtn.disabled = true;
    confirmBtn.innerHTML = '<span class="loading-spinner"></span> Vérification...';
    
    fetch(window.location.href, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=verify_otp&otp_code=${code}`
    })
    .then(res => res.json())
    .then(data => {
        confirmBtn.disabled = false;
        confirmBtn.innerHTML = originalText;
        
        if (data.success) {
            if (otpInterval) clearInterval(otpInterval);
            document.getElementById('otpModal').classList.remove('active');
            submitFinalOrder();
        } else {
            document.getElementById('otpError').textContent = data.message;
            document.getElementById('otpError').style.display = 'block';
            clearOtpInputs();
        }
    })
    .catch(() => {
        confirmBtn.disabled = false;
        confirmBtn.innerHTML = originalText;
        document.getElementById('otpError').textContent = 'Erreur de vérification.';
        document.getElementById('otpError').style.display = 'block';
    });
}

function submitFinalOrder() {
    if (!pendingOrderData) return;
    
    const btn = document.getElementById('submitOrderBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="loading-spinner"></span> Traitement de la commande...';
    
    const formData = new URLSearchParams();
    formData.append('submit_order', '1');
    formData.append('cart_items', JSON.stringify(pendingOrderData.cart));
    formData.append('nom_complet', pendingOrderData.nom);
    formData.append('email', pendingOrderData.email);
    formData.append('telephone', pendingOrderData.telephone);
    formData.append('adresse', pendingOrderData.adresse);
    formData.append('payment_method', pendingOrderData.payment_method);
    formData.append('payment_phone', pendingOrderData.payment_phone);
    
    fetch(window.location.href, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: formData.toString()
    })
    .then(response => {
        if (response.redirected) {
            window.location.href = response.url;
        } else {
            window.location.href = 'produits.php';
        }
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa fa-lock"></i> Passer commande';
        showError('Erreur lors de la commande. Veuillez réessayer.');
    });
}

// ============================================================
//  INITIALIZE
// ============================================================
loadCart();

// Set default selected payment method
document.querySelector('.payment-option[data-method="flouci"]').classList.add('selected');
</script>
</body>
</html>