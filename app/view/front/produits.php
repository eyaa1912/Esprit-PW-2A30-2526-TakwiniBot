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
//  OTP SEND
// ============================================================
function sendOTPByEmail($to_email, $to_name, $otp_code) {
    $subject = "🔐 Votre code de confirmation Takwinibot";
    $message = "Bonjour $to_name,\n\nVotre code de confirmation est : $otp_code\n\nCe code expire dans 5 minutes.\n\nCordialement,\nTakwinibot";
    $headers = "From: no-reply@takwinibot.com\r\nReply-To: support@takwinibot.com\r\n";
    return mail($to_email, $subject, $message, $headers);
}

if (isset($_POST['action']) && $_POST['action'] === 'send_otp') {
    header('Content-Type: application/json');
    $email = $_POST['email'] ?? '';
    $nom   = $_POST['nom'] ?? '';
    if (empty($email)) { echo json_encode(['success'=>false,'message'=>'Email requis']); exit(); }
    $otp_code = rand(100000, 999999);
    $_SESSION['payment_otp'] = ['code'=>$otp_code,'expires'=>time()+300,'email'=>$email];
    $sent = sendOTPByEmail($email, $nom, $otp_code);
    echo json_encode($sent ? ['success'=>true,'message'=>'Code envoyé'] : ['success'=>false,'message'=>'Erreur envoi email']);
    exit();
}

if (isset($_POST['action']) && $_POST['action'] === 'verify_otp') {
    header('Content-Type: application/json');
    $user_code = $_POST['otp_code'] ?? '';
    if (!isset($_SESSION['payment_otp'])) { echo json_encode(['success'=>false,'message'=>'Aucun code demandé']); exit(); }
    $stored = $_SESSION['payment_otp'];
    if (time() > $stored['expires']) { unset($_SESSION['payment_otp']); echo json_encode(['success'=>false,'message'=>'Code expiré']); exit(); }
    if ($user_code == $stored['code']) { unset($_SESSION['payment_otp']); echo json_encode(['success'=>true,'message'=>'Code valide']); }
    else { echo json_encode(['success'=>false,'message'=>'Code incorrect']); }
    exit();
}

// ============================================================
//  DELETE ORDER
// ============================================================
if (isset($_GET['delete_order']) && is_numeric($_GET['delete_order'])) {
    $order_id = intval($_GET['delete_order']);
    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("SELECT produit_id, quantite FROM ligne_commande WHERE commande_id = ?");
        $stmt->execute([$order_id]);
        foreach ($stmt->fetchAll() as $item) {
            $pdo->prepare("UPDATE produit SET stock = stock + ? WHERE id = ?")->execute([$item['quantite'],$item['produit_id']]);
        }
        $pdo->prepare("DELETE FROM ligne_commande WHERE commande_id = ?")->execute([$order_id]);
        $pdo->prepare("DELETE FROM commande WHERE id = ?")->execute([$order_id]);
        $pdo->commit();
        $_SESSION['order_success'] = "Commande #$order_id supprimée avec succès!";
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['order_error'] = "Erreur: " . $e->getMessage();
    }
    header("Location: produits.php"); exit();
}

// ============================================================
//  ORDER PROCESSING
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_order'])) {
    $produit_id     = intval($_POST['produit_id']);
    $quantite       = intval($_POST['quantite']);
    $nom_complet    = trim($_POST['nom_complet']);
    $email          = trim($_POST['email']);
    $telephone      = trim($_POST['telephone']);
    $adresse        = trim($_POST['adresse']);
    $payment_method = trim($_POST['payment_method'] ?? 'non specifie');
    $payment_phone  = trim($_POST['payment_phone'] ?? '');
    $statut_initial = ($payment_method === 'flouci' || $payment_method === 'd17') ? 'en attente paiement' : 'en attente';

    if ($produit_id > 0 && $quantite > 0 && !empty($nom_complet) && !empty($email) && !empty($telephone) && !empty($adresse)) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM produit WHERE id = ?");
            $stmt->execute([$produit_id]);
            $product = $stmt->fetch();
            if ($product && $product['stock'] >= $quantite) {
                $total = $product['prix'] * $quantite;
                $pdo->beginTransaction();
                $stmt = $pdo->prepare("INSERT INTO commande (date_commande,statut,total,nom_client,email_client,telephone_client,adresse_livraison,payment_method,payment_phone) VALUES (NOW(),?,?,?,?,?,?,?,?)");
                $stmt->execute([$statut_initial,$total,$nom_complet,$email,$telephone,$adresse,$payment_method,$payment_phone]);
                $commande_id = $pdo->lastInsertId();
                $pdo->prepare("INSERT INTO ligne_commande (commande_id,produit_id,quantite,prix_unitaire) VALUES (?,?,?,?)")->execute([$commande_id,$produit_id,$quantite,$product['prix']]);
                $pdo->prepare("UPDATE produit SET stock = stock - ? WHERE id = ?")->execute([$quantite,$produit_id]);
                $pdo->commit();
                $_SESSION['order_success'] = "Commande #$commande_id créée avec succès! Méthode: $payment_method";
                header("Location: produits.php"); exit();
            } else {
                $_SESSION['order_error'] = "Stock insuffisant! Seulement " . ($product['stock'] ?? 0) . " disponible(s).";
            }
        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['order_error'] = "Erreur: " . $e->getMessage();
        }
    } else {
        $_SESSION['order_error'] = "Veuillez remplir tous les champs!";
    }
    header("Location: produits.php"); exit();
}

// ============================================================
//  GET ALL ORDERS
// ============================================================
$stmt = $pdo->query("SELECT c.*, (SELECT COUNT(*) FROM ligne_commande WHERE commande_id = c.id) as nb_products FROM commande c ORDER BY c.date_commande DESC");
$all_orders = $stmt->fetchAll();

// ============================================================
//  AJAX ORDER DETAILS
// ============================================================
if (isset($_GET['get_order_details']) && is_numeric($_GET['get_order_details'])) {
    $order_id = intval($_GET['get_order_details']);
    $stmt = $pdo->prepare("SELECT * FROM commande WHERE id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();
    if ($order) {
        $stmt = $pdo->prepare("SELECT lc.*, p.nom as product_name, p.image FROM ligne_commande lc JOIN produit p ON lc.produit_id = p.id WHERE lc.commande_id = ?");
        $stmt->execute([$order_id]);
        $items = $stmt->fetchAll();
        header('Content-Type: application/json');
        echo json_encode(['order'=>$order,'items'=>$items]);
        exit();
    }
}

// ============================================================
//  ADD PRODUCT
// ============================================================
$add_success = null; $add_error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_product') {
    $nom         = trim($_POST['nom'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $prix        = floatval($_POST['prix'] ?? 0);
    $stock       = intval($_POST['stock'] ?? 0);
    $cat_id      = intval($_POST['categorie_id'] ?? 0);
    if (empty($nom) || $prix <= 0 || $cat_id <= 0) {
        $add_error = "Veuillez remplir tous les champs obligatoires.";
    } else {
        try {
            $image_path = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = '../back/uploads/produits/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
                $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $filename = uniqid() . '.' . $ext;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $filename)) $image_path = $filename;
            }
            $pdo->prepare("INSERT INTO produit (nom,description,prix,stock,categorie_id,image) VALUES (?,?,?,?,?,?)")->execute([$nom,$description,$prix,$stock,$cat_id,$image_path]);
            $add_success = "Produit \"$nom\" ajouté avec succès!";
        } catch (PDOException $e) { $add_error = "Erreur: " . $e->getMessage(); }
    }
}

// ============================================================
//  GET PRODUCTS
// ============================================================
$categorie_id = isset($_GET['categorie']) ? (int)$_GET['categorie'] : 0;
$search       = isset($_GET['search']) ? trim($_GET['search']) : '';
$sql    = "SELECT p.*, c.nom AS categorie_nom FROM produit p LEFT JOIN categorie c ON p.categorie_id = c.id WHERE 1=1";
$params = [];
if ($categorie_id > 0) { $sql .= " AND p.categorie_id = ?"; $params[] = $categorie_id; }
if (!empty($search))   { $sql .= " AND (p.nom LIKE ? OR p.description LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }
$sql .= " ORDER BY p.id DESC";
$stmt = $pdo->prepare($sql); $stmt->execute($params);
$produits   = $stmt->fetchAll();
$categories = $pdo->query("SELECT * FROM categorie ORDER BY nom")->fetchAll();

$cat_icons = [
    'Food'            => ['icon'=>'🍔','color'=>'#ff6b6b'],
    'Formation'       => ['icon'=>'📚','color'=>'#4ecdc4'],
    'Accessories'     => ['icon'=>'⌚','color'=>'#45b7d1'],
    'Informatique'    => ['icon'=>'💻','color'=>'#96ceb4'],
    'Bureautique'     => ['icon'=>'📎','color'=>'#f7b731'],
    'Logiciels'       => ['icon'=>'💿','color'=>'#a55eea'],
    'Matériel réseau' => ['icon'=>'🌐','color'=>'#2bcbba'],
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Takwinibot — Boutique</title>
<link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" href="assets/fonts/font-awesome.min.css">
<link rel="stylesheet" href="assets/css/menu.css">
<link rel="stylesheet" href="assets/css/style.css">
<link rel="stylesheet" href="assets/css/responsive.css">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
/* ═══════════════════════════════════════════
   DESIGN SYSTEM — TAKWINIBOT
   Aesthetic: Clean luxury retail — dark navy
   accents, warm whites, sharp type
   ═══════════════════════════════════════════ */
:root {
    --navy:   #0f172a;
    --navy2:  #1e293b;
    --navy3:  #334155;
    --accent: #3bafda;
    --accent2:#0ea5e9;
    --gold:   #f59e0b;
    --white:  #ffffff;
    --off:    #f8fafc;
    --gray:   #94a3b8;
    --light:  #e2e8f0;
    --green:  #10b981;
    --red:    #ef4444;
    --radius: 14px;
    --shadow: 0 4px 24px rgba(15,23,42,.10);
    --shadow-lg: 0 12px 40px rgba(15,23,42,.18);
}
*, *::before, *::after { box-sizing: border-box; }
body { font-family: 'DM Sans', sans-serif; background: var(--off); color: var(--navy); margin: 0; }
h1,h2,h3,h4,h5,h6 { font-family: 'Syne', sans-serif; }

/* ─── NAVBAR ─── */
.tk-nav {
    position: sticky; top: 0; z-index: 1000;
    background: rgba(15,23,42,.97);
    backdrop-filter: blur(12px);
    border-bottom: 1px solid rgba(255,255,255,.06);
    padding: 0 32px;
    display: flex; align-items: center; justify-content: space-between;
    height: 68px;
}
.tk-nav .logo { display:flex; align-items:center; gap:10px; text-decoration:none; }
.tk-nav .logo img { height: 36px; }
.tk-nav .logo span { font-family:'Syne',sans-serif; font-size:20px; font-weight:800; color:#fff; letter-spacing:-.5px; }
.tk-nav-links { display:flex; gap:8px; align-items:center; }
.tk-nav-links a { color: rgba(255,255,255,.7); text-decoration:none; font-size:14px; font-weight:500; padding:8px 14px; border-radius:8px; transition:.2s; }
.tk-nav-links a:hover, .tk-nav-links a.active { color:#fff; background:rgba(255,255,255,.1); }
.tk-nav-right { display:flex; align-items:center; gap:12px; }

/* Cart icon in navbar */
.cart-nav-btn {
    position:relative; background:var(--accent); color:#fff; border:none;
    border-radius:10px; padding:8px 18px; font-size:14px; font-weight:600;
    cursor:pointer; display:flex; align-items:center; gap:8px;
    transition: background .2s, transform .15s;
    font-family:'DM Sans',sans-serif;
}
.cart-nav-btn:hover { background:var(--accent2); transform:translateY(-1px); }
.cart-nav-btn .cart-count {
    background:var(--gold); color:var(--navy); border-radius:50%;
    width:20px; height:20px; display:flex; align-items:center; justify-content:center;
    font-size:11px; font-weight:800; line-height:1;
}

/* ─── HERO ─── */
.tk-hero {
    background: var(--navy);
    padding: 72px 32px 64px;
    text-align: center;
    position: relative;
    overflow: hidden;
}
.tk-hero::before {
    content:''; position:absolute; inset:0;
    background: radial-gradient(ellipse 80% 60% at 50% 0%, rgba(59,175,218,.18) 0%, transparent 70%);
    pointer-events:none;
}
.tk-hero h1 { font-size: clamp(32px,5vw,56px); color:#fff; font-weight:800; letter-spacing:-1.5px; margin:0 0 14px; }
.tk-hero h1 span { color: var(--accent); }
.tk-hero p { color: var(--gray); font-size:17px; max-width:520px; margin:0 auto; }

/* ─── TAB BAR ─── */
.tk-tabs {
    background:#fff; border-bottom:1px solid var(--light);
    display:flex; padding:0 32px; gap:4px;
    position:sticky; top:68px; z-index:900;
}
.tk-tab {
    padding:16px 22px; font-size:14px; font-weight:600; cursor:pointer;
    border:none; background:none; color:var(--gray);
    border-bottom:3px solid transparent; transition:.2s;
    font-family:'DM Sans',sans-serif; display:flex; align-items:center; gap:7px;
}
.tk-tab:hover { color:var(--navy); }
.tk-tab.active { color:var(--accent); border-bottom-color:var(--accent); }
.tk-section { display:none; }
.tk-section.active { display:block; }

/* ─── CATEGORY FILTER ─── */
.tk-cats {
    background:#fff; padding:28px 32px;
    border-bottom:1px solid var(--light);
}
.tk-cats-inner { display:flex; gap:10px; flex-wrap:wrap; align-items:center; }
.cat-pill {
    display:flex; align-items:center; gap:8px;
    padding:8px 18px; border-radius:50px;
    font-size:13px; font-weight:600; text-decoration:none;
    border:1.5px solid var(--light); background:#fff; color:var(--navy3);
    transition:.2s;
}
.cat-pill:hover { border-color:var(--accent); color:var(--accent); text-decoration:none; }
.cat-pill.active { background:var(--navy); border-color:var(--navy); color:#fff; }
.cat-pill .cat-em { font-size:16px; }

/* ─── SEARCH BAR ─── */
.tk-search { background:#fff; padding:20px 32px; border-bottom:1px solid var(--light); }
.tk-search-wrap {
    max-width:480px; display:flex;
    border:1.5px solid var(--light); border-radius:50px; overflow:hidden;
    transition:border .2s;
}
.tk-search-wrap:focus-within { border-color:var(--accent); }
.tk-search-wrap input {
    flex:1; border:none; padding:11px 20px; font-size:14px; outline:none;
    font-family:'DM Sans',sans-serif; background:transparent;
}
.tk-search-wrap button {
    background:var(--accent); color:#fff; border:none;
    padding:0 20px; font-size:14px; cursor:pointer;
    font-family:'DM Sans',sans-serif; font-weight:600;
}

/* ─── PRODUCT GRID ─── */
.tk-products { padding:32px; }
.tk-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:24px; }
.tk-card {
    background:#fff; border-radius:var(--radius);
    border:1px solid var(--light);
    overflow:hidden; transition:transform .2s, box-shadow .2s;
    display:flex; flex-direction:column;
}
.tk-card:hover { transform:translateY(-5px); box-shadow:var(--shadow-lg); }
.tk-card-img {
    height:200px; overflow:hidden; position:relative; flex-shrink:0;
}
.tk-card-img img { width:100%; height:100%; object-fit:cover; transition:transform .4s; }
.tk-card:hover .tk-card-img img { transform:scale(1.05); }
.tk-card-img .placeholder {
    width:100%; height:100%; display:flex; align-items:center; justify-content:center;
    font-size:72px;
}
.tk-card-badge {
    position:absolute; top:12px; left:12px;
    background:rgba(15,23,42,.75); color:#fff;
    padding:4px 10px; border-radius:20px; font-size:11px; font-weight:600;
    backdrop-filter:blur(4px);
}
.tk-stock-badge {
    position:absolute; top:12px; right:12px;
    padding:4px 10px; border-radius:20px; font-size:11px; font-weight:700;
}
.tk-stock-badge.in  { background:rgba(16,185,129,.15); color:#059669; border:1px solid rgba(16,185,129,.3); }
.tk-stock-badge.out { background:rgba(239,68,68,.12); color:#dc2626; border:1px solid rgba(239,68,68,.25); }
.tk-card-body { padding:20px; flex:1; display:flex; flex-direction:column; }
.tk-card-body h3 { font-size:16px; font-weight:700; margin:0 0 6px; color:var(--navy); line-height:1.3; }
.tk-card-body p { font-size:13px; color:var(--gray); margin:0 0 16px; line-height:1.6; flex:1; }
.tk-card-footer { display:flex; align-items:center; justify-content:space-between; gap:10px; margin-top:auto; }
.tk-price { font-family:'Syne',sans-serif; font-size:20px; font-weight:800; color:var(--navy); }
.tk-price small { font-size:13px; font-weight:500; color:var(--gray); }
.tk-card-actions { display:flex; flex-direction:column; gap:6px; margin-top:14px; }
.btn-cart {
    width:100%; padding:11px; background:var(--navy); color:#fff;
    border:none; border-radius:10px; font-size:14px; font-weight:600;
    cursor:pointer; transition:.2s; font-family:'DM Sans',sans-serif;
    display:flex; align-items:center; justify-content:center; gap:8px;
}
.btn-cart:hover { background:var(--accent); }
.btn-cart:disabled { background:var(--light); color:var(--gray); cursor:not-allowed; }
.btn-ai {
    width:100%; padding:9px; background:transparent; color:var(--accent);
    border:1.5px dashed var(--accent); border-radius:10px; font-size:13px;
    font-weight:600; cursor:pointer; transition:.2s; font-family:'DM Sans',sans-serif;
}
.btn-ai:hover { background:var(--accent); color:#fff; }

/* ─── CART DRAWER ─── */
.cart-overlay {
    position:fixed; inset:0; background:rgba(15,23,42,.5);
    z-index:1999; opacity:0; pointer-events:none; transition:opacity .3s;
}
.cart-overlay.open { opacity:1; pointer-events:all; }
.cart-drawer {
    position:fixed; right:0; top:0; bottom:0; width:420px; max-width:100vw;
    background:#fff; z-index:2000; transform:translateX(100%);
    transition:transform .35s cubic-bezier(.4,0,.2,1);
    display:flex; flex-direction:column; box-shadow:-8px 0 40px rgba(15,23,42,.2);
}
.cart-drawer.open { transform:translateX(0); }
.cart-drawer-head {
    padding:24px; border-bottom:1px solid var(--light);
    display:flex; align-items:center; justify-content:space-between;
}
.cart-drawer-head h2 { font-size:20px; font-weight:800; margin:0; }
.cart-close { background:none; border:none; font-size:22px; cursor:pointer; color:var(--navy3); }
.cart-items { flex:1; overflow-y:auto; padding:20px; display:flex; flex-direction:column; gap:14px; }
.cart-empty { flex:1; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:12px; padding:40px; text-align:center; }
.cart-empty .icon { font-size:64px; opacity:.3; }
.cart-empty p { color:var(--gray); font-size:15px; }
.cart-item {
    display:flex; gap:14px; padding:14px;
    border:1px solid var(--light); border-radius:12px;
    background:var(--off); align-items:center;
}
.cart-item-img { width:62px; height:62px; border-radius:8px; overflow:hidden; flex-shrink:0; }
.cart-item-img img { width:100%; height:100%; object-fit:cover; }
.cart-item-img .ph { width:100%; height:100%; display:flex; align-items:center; justify-content:center; font-size:28px; background:#fff; }
.cart-item-info { flex:1; min-width:0; }
.cart-item-info h4 { font-size:14px; font-weight:700; margin:0 0 2px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.cart-item-info .price { font-size:13px; color:var(--accent); font-weight:700; }
.cart-item-qty { display:flex; align-items:center; gap:8px; margin-top:8px; }
.qty-btn { width:26px; height:26px; background:var(--light); border:none; border-radius:6px; cursor:pointer; font-size:15px; display:flex; align-items:center; justify-content:center; transition:.15s; font-weight:700; }
.qty-btn:hover { background:var(--accent); color:#fff; }
.qty-num { font-size:14px; font-weight:700; min-width:20px; text-align:center; }
.cart-item-remove { background:none; border:none; color:var(--gray); cursor:pointer; font-size:17px; padding:4px; transition:.15s; flex-shrink:0; }
.cart-item-remove:hover { color:var(--red); }
.cart-footer { padding:20px 24px; border-top:1px solid var(--light); background:#fff; }
.cart-total-row { display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; }
.cart-total-row .label { font-size:15px; color:var(--gray); }
.cart-total-row .amount { font-family:'Syne',sans-serif; font-size:26px; font-weight:800; color:var(--navy); }
.btn-checkout {
    width:100%; padding:15px; background:var(--navy); color:#fff;
    border:none; border-radius:12px; font-size:15px; font-weight:700;
    cursor:pointer; transition:.2s; font-family:'DM Sans',sans-serif;
    display:flex; align-items:center; justify-content:center; gap:10px;
}
.btn-checkout:hover { background:var(--accent); }

/* ─── ORDERS SECTION ─── */
.tk-orders { padding:32px; }
.tk-section-head { margin-bottom:28px; }
.tk-section-head h2 { font-size:28px; font-weight:800; margin:0 0 6px; }
.tk-section-head p { color:var(--gray); margin:0; }
.order-card {
    background:#fff; border:1px solid var(--light); border-radius:var(--radius);
    padding:20px 24px; margin-bottom:12px; transition:.2s;
    display:flex; align-items:center; gap:20px; flex-wrap:wrap;
}
.order-card:hover { box-shadow:var(--shadow); border-color:#cbd5e1; }
.order-num { font-family:'Syne',sans-serif; font-weight:800; font-size:15px; color:var(--navy); }
.order-date { font-size:12px; color:var(--gray); margin-top:2px; }
.status-badge { padding:5px 12px; border-radius:20px; font-size:11px; font-weight:700; display:inline-flex; align-items:center; gap:5px; }
.status-en-attente { background:#fef3c7; color:#92400e; }
.status-en-attente-paiement { background:#ffedd5; color:#9a3412; }
.status-validee { background:#d1fae5; color:#065f46; }
.status-expediee { background:#dbeafe; color:#1e40af; }
.status-livree { background:#f1f5f9; color:#475569; }
.status-annulee { background:#fee2e2; color:#991b1b; }
.order-total { font-family:'Syne',sans-serif; font-size:18px; font-weight:800; color:var(--accent); }
.order-actions { display:flex; gap:8px; margin-left:auto; }
.btn-icon { width:36px; height:36px; border-radius:8px; border:1px solid var(--light); background:#fff; cursor:pointer; display:flex; align-items:center; justify-content:center; font-size:14px; transition:.15s; }
.btn-icon:hover { background:var(--off); border-color:#94a3b8; }
.btn-icon.danger:hover { background:#fee2e2; border-color:var(--red); color:var(--red); }

/* ─── ADD PRODUCT SECTION ─── */
.tk-add { padding:32px; }
.tk-add-card {
    background:#fff; border:1px solid var(--light); border-radius:var(--radius);
    padding:36px; max-width:700px; margin:0 auto;
}
.tk-add-card h2 { font-size:26px; font-weight:800; margin:0 0 6px; }
.tk-add-card p { color:var(--gray); margin:0 0 28px; }
.tk-form-group { margin-bottom:18px; }
.tk-form-group label { display:block; font-size:13px; font-weight:600; color:var(--navy3); margin-bottom:7px; }
.tk-form-group input,
.tk-form-group select,
.tk-form-group textarea {
    width:100%; padding:11px 15px; border:1.5px solid var(--light);
    border-radius:10px; font-size:14px; font-family:'DM Sans',sans-serif;
    outline:none; transition:border .2s; background:#fff;
}
.tk-form-group input:focus,
.tk-form-group select:focus,
.tk-form-group textarea:focus { border-color:var(--accent); }
.tk-form-row { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
.btn-primary-tk {
    padding:13px 30px; background:var(--navy); color:#fff;
    border:none; border-radius:10px; font-size:15px; font-weight:700;
    cursor:pointer; font-family:'DM Sans',sans-serif; transition:.2s;
    display:inline-flex; align-items:center; gap:8px;
}
.btn-primary-tk:hover { background:var(--accent); }

/* ─── AI ASSISTANT ─── */
.ai-bubble-wrap {
    position:fixed; bottom:28px; right:28px; z-index:1500;
}
.ai-toggle-btn {
    width:58px; height:58px; background:var(--navy); border:none;
    border-radius:50%; color:#fff; font-size:22px; cursor:pointer;
    box-shadow:0 4px 20px rgba(15,23,42,.4);
    display:flex; align-items:center; justify-content:center;
    transition:transform .2s, background .2s;
}
.ai-toggle-btn:hover { background:var(--accent); transform:scale(1.08); }
.ai-panel {
    position:absolute; bottom:70px; right:0; width:320px;
    background:#fff; border-radius:16px; border:1px solid var(--light);
    box-shadow:var(--shadow-lg); overflow:hidden;
    display:none; flex-direction:column;
}
.ai-panel.open { display:flex; }
.ai-panel-head {
    background:var(--navy); padding:14px 16px;
    display:flex; align-items:center; gap:10px; color:#fff;
}
.ai-av { width:32px; height:32px; background:rgba(255,255,255,.15); border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:16px; flex-shrink:0; }
.ai-panel-head h5 { font-size:14px; font-weight:700; margin:0; color:#fff; }
.ai-panel-head p { font-size:11px; color:rgba(255,255,255,.65); margin:0; }
.ai-close { margin-left:auto; background:none; border:none; color:rgba(255,255,255,.7); font-size:18px; cursor:pointer; }
.ai-msgs { padding:14px; height:240px; overflow-y:auto; display:flex; flex-direction:column; gap:8px; }
.ai-msg-row { display:flex; gap:7px; align-items:flex-end; }
.ai-msg-row.user { flex-direction:row-reverse; }
.ai-bub { padding:9px 13px; border-radius:12px; font-size:13px; line-height:1.5; max-width:84%; }
.ai-bub.bot { background:var(--off); color:var(--navy); }
.ai-bub.user { background:var(--accent); color:#fff; }
.ai-bub.typing { background:var(--off); color:var(--gray); font-style:italic; }
.ai-chips { padding:0 14px 10px; display:flex; flex-wrap:wrap; gap:5px; }
.ai-chip { background:var(--off); border:1px solid var(--accent); color:var(--accent); border-radius:20px; padding:4px 11px; font-size:11px; cursor:pointer; transition:.15s; font-family:'DM Sans',sans-serif; }
.ai-chip:hover { background:var(--accent); color:#fff; }
.ai-input-row { display:flex; gap:8px; padding:10px 12px; border-top:1px solid var(--light); }
.ai-input-row input { flex:1; border:1px solid var(--light); border-radius:20px; padding:8px 14px; font-size:13px; outline:none; font-family:'DM Sans',sans-serif; }
.ai-input-row input:focus { border-color:var(--accent); }
.ai-send { width:34px; height:34px; background:var(--navy); border:none; border-radius:50%; color:#fff; font-size:13px; cursor:pointer; flex-shrink:0; transition:background .2s; display:flex; align-items:center; justify-content:center; }
.ai-send:hover { background:var(--accent); }
.ai-footer-note { text-align:center; font-size:10px; color:var(--gray); padding:4px 0 8px; }

/* ─── MODALS ─── */
.tk-modal-backdrop { position:fixed; inset:0; background:rgba(15,23,42,.55); z-index:3000; display:none; align-items:center; justify-content:center; padding:20px; }
.tk-modal-backdrop.open { display:flex; }
.tk-modal { background:#fff; border-radius:18px; width:100%; max-width:500px; overflow:hidden; max-height:90vh; overflow-y:auto; }
.tk-modal-head { padding:22px 24px; border-bottom:1px solid var(--light); display:flex; align-items:center; justify-content:space-between; }
.tk-modal-head h3 { font-size:18px; font-weight:800; margin:0; }
.tk-modal-close { background:none; border:none; font-size:22px; cursor:pointer; color:var(--gray); }
.tk-modal-body { padding:24px; }

/* OTP */
.otp-group { display:flex; gap:10px; justify-content:center; margin:20px 0; }
.otp-box { width:50px; height:56px; border:2px solid var(--light); border-radius:10px; font-size:22px; text-align:center; font-weight:700; outline:none; transition:.2s; }
.otp-box:focus { border-color:var(--accent); }
.otp-box.filled { border-color:var(--green); background:#f0fdf4; color:var(--green); }

/* ─── ALERTS ─── */
.tk-alert { padding:14px 18px; border-radius:10px; margin-bottom:16px; font-size:14px; font-weight:500; }
.tk-alert.success { background:#d1fae5; color:#065f46; border:1px solid #6ee7b7; }
.tk-alert.error   { background:#fee2e2; color:#991b1b; border:1px solid #fca5a5; }

/* ─── EMPTY STATE ─── */
.tk-empty { text-align:center; padding:80px 20px; }
.tk-empty .icon { font-size:72px; opacity:.25; margin-bottom:16px; }
.tk-empty h3 { font-size:22px; font-weight:800; margin:0 0 8px; }
.tk-empty p { color:var(--gray); }

/* ─── MISC ─── */
.pm-badge { display:inline-flex; align-items:center; gap:5px; padding:4px 10px; border-radius:20px; font-size:11px; font-weight:600; background:var(--off); color:var(--navy3); border:1px solid var(--light); }
.spinner { display:inline-block; width:18px; height:18px; border:2px solid rgba(255,255,255,.3); border-top-color:#fff; border-radius:50%; animation:spin .7s linear infinite; }
@keyframes spin { to { transform:rotate(360deg); } }

/* ─── RESPONSIVE ─── */
@media(max-width:768px) {
    .tk-nav { padding:0 16px; }
    .tk-nav-links { display:none; }
    .tk-products, .tk-orders, .tk-add { padding:16px; }
    .tk-cats { padding:16px; }
    .tk-form-row { grid-template-columns:1fr; }
    .cart-drawer { width:100vw; }
    .order-card { flex-wrap:wrap; }
}
</style>
</head>
<body>

<!-- ═══════════ NAVBAR ═══════════ -->
<nav class="tk-nav">
    <a href="index.html" class="logo">
        <img src="assets/img/logo.png" alt="Takwinibot" onerror="this.style.display='none'">
        <span>Takwinibot</span>
    </a>
    <div class="tk-nav-links">
        <a href="index.html">Accueil</a>
        <a href="about.html">À propos</a>
        <a href="produits.php" class="active">Boutique</a>
    </div>
    <div class="tk-nav-right">
        <button class="cart-nav-btn" onclick="openCart()">
            <i class="fa fa-shopping-bag"></i>
            Panier
            <span class="cart-count" id="cartCount">0</span>
        </button>
    </div>
</nav>

<!-- ═══════════ HERO ═══════════ -->
<div class="tk-hero">
    <h1>Notre <span>Boutique</span></h1>
    <p>Découvrez nos produits soigneusement sélectionnés pour vous</p>
</div>

<!-- ═══════════ TABS ═══════════ -->
<div class="tk-tabs">
    <button class="tk-tab active" data-section="browse">
        <i class="fa fa-th-large"></i> Produits
    </button>
    <button class="tk-tab" data-section="orders">
        <i class="fa fa-list-alt"></i> Mes Commandes
        <?php if(count($all_orders)>0): ?><span class="cart-count" style="background:var(--navy);color:#fff;margin-left:4px;"><?= count($all_orders) ?></span><?php endif; ?>
    </button>
    <button class="tk-tab" data-section="add">
        <i class="fa fa-plus"></i> Ajouter un produit
    </button>
</div>

<!-- ═══════════════════════════════════════
     BROWSE SECTION
═══════════════════════════════════════════ -->
<div id="browse-section" class="tk-section active">

    <!-- Category filter -->
    <div class="tk-cats">
        <div class="tk-cats-inner">
            <a href="produits.php" class="cat-pill <?= $categorie_id===0?'active':'' ?>">
                <span class="cat-em">🛍️</span> Tous
            </a>
            <?php foreach($categories as $cat):
                $info = $cat_icons[$cat['nom']] ?? ['icon'=>'📦','color'=>'#94a3b8'];
            ?>
            <a href="produits.php?categorie=<?= $cat['id'] ?>"
               class="cat-pill <?= $categorie_id==$cat['id']?'active':'' ?>"
               style="<?= $categorie_id==$cat['id']?'background:'.$info['color'].';border-color:'.$info['color'].';color:#fff;':'' ?>">
                <span class="cat-em"><?= $info['icon'] ?></span>
                <?= htmlspecialchars($cat['nom']) ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Search -->
    <div class="tk-search">
        <form method="GET" action="produits.php">
            <?php if($categorie_id>0): ?><input type="hidden" name="categorie" value="<?= $categorie_id ?>"><?php endif; ?>
            <div class="tk-search-wrap">
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Rechercher un produit...">
                <button type="submit"><i class="fa fa-search"></i></button>
            </div>
        </form>
    </div>

    <!-- Products -->
    <div class="tk-products">
        <?php if(empty($produits)): ?>
        <div class="tk-empty">
            <div class="icon">📦</div>
            <h3>Aucun produit trouvé</h3>
            <p>Essayez une autre catégorie ou effacez votre recherche.</p>
            <a href="produits.php" class="btn-primary-tk" style="text-decoration:none;margin-top:16px;">Voir tout</a>
        </div>
        <?php else: ?>
        <div class="tk-grid">
            <?php foreach($produits as $p):
                $cat_nom = $p['categorie_nom'] ?? 'Produit';
                $info    = $cat_icons[$cat_nom] ?? ['icon'=>'📦','color'=>'#94a3b8'];
                $in_stock = $p['stock'] > 0;
            ?>
            <div class="tk-card">
                <div class="tk-card-img">
                    <?php if(!empty($p['image'])): ?>
                        <img src="../back/uploads/produits/<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['nom']) ?>">
                    <?php else: ?>
                        <div class="placeholder" style="background:<?= $info['color'] ?>18;"><?= $info['icon'] ?></div>
                    <?php endif; ?>
                    <span class="tk-card-badge"><?= $info['icon'] ?> <?= htmlspecialchars($cat_nom) ?></span>
                    <span class="tk-stock-badge <?= $in_stock?'in':'out' ?>">
                        <?= $in_stock ? '● En stock ('.$p['stock'].')' : '✕ Rupture' ?>
                    </span>
                </div>
                <div class="tk-card-body">
                    <h3><?= htmlspecialchars($p['nom']) ?></h3>
                    <p><?= htmlspecialchars(substr($p['description']??'',0,90)) ?><?= strlen($p['description']??'')>90?'…':'' ?></p>
                    <div class="tk-card-footer">
                        <div class="tk-price"><?= number_format($p['prix'],3) ?> <small>DT</small></div>
                    </div>
                    <div class="tk-card-actions">
                        <button class="btn-cart" <?= !$in_stock?'disabled':'' ?>
                            onclick="addToCart(<?= $p['id'] ?>, '<?= htmlspecialchars($p['nom'],ENT_QUOTES) ?>', <?= $p['prix'] ?>, '<?= htmlspecialchars($p['image']??'',ENT_QUOTES) ?>', '<?= $info['icon'] ?>', <?= $p['stock'] ?>)">
                            <i class="fa fa-shopping-bag"></i>
                            <?= $in_stock ? 'Ajouter au panier' : 'Rupture de stock' ?>
                        </button>
                        <button class="btn-ai" onclick="openAIChat('<?= htmlspecialchars($p['nom'],ENT_QUOTES) ?>','<?= htmlspecialchars($cat_nom,ENT_QUOTES) ?>',<?= $p['prix'] ?>,'<?= $p['stock'] ?>','<?= htmlspecialchars(substr($p['description']??'',0,200),ENT_QUOTES) ?>')">
                            🤖 Demander à l'assistant
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- ═══════════════════════════════════════
     ORDERS SECTION
═══════════════════════════════════════════ -->
<div id="orders-section" class="tk-section">
    <div class="tk-orders">
        <div class="tk-section-head">
            <h2>Mes Commandes</h2>
            <p>Historique complet de vos achats</p>
        </div>

        <?php if(isset($_SESSION['order_success'])): ?>
            <div class="tk-alert success"><i class="fa fa-check-circle"></i> <?= $_SESSION['order_success'] ?><?php unset($_SESSION['order_success']); ?></div>
        <?php endif; ?>
        <?php if(isset($_SESSION['order_error'])): ?>
            <div class="tk-alert error"><i class="fa fa-exclamation-triangle"></i> <?= $_SESSION['order_error'] ?><?php unset($_SESSION['order_error']); ?></div>
        <?php endif; ?>

        <?php if(empty($all_orders)): ?>
        <div class="tk-empty">
            <div class="icon">📋</div>
            <h3>Aucune commande</h3>
            <p>Vous n'avez pas encore passé de commande.</p>
        </div>
        <?php else: ?>
        <?php foreach($all_orders as $order):
            $pm = $order['payment_method'] ?? 'N/A';
            $pmIcon = $pm==='flouci'?'📱':($pm==='d17'?'🏦':($pm==='livraison'?'🚚':'❓'));
            $statusClass = 'status-'.str_replace([' ','é'],['-','e'],$order['statut']);
        ?>
        <div class="order-card">
            <div>
                <div class="order-num">#<?= str_pad($order['id'],6,'0',STR_PAD_LEFT) ?></div>
                <div class="order-date"><?= date('d/m/Y H:i',strtotime($order['date_commande'])) ?></div>
            </div>
            <span class="status-badge <?= $statusClass ?>"><?= ucfirst($order['statut']) ?></span>
            <div class="order-total"><?= number_format($order['total'],3) ?> DT</div>
            <span class="pm-badge"><?= $pmIcon ?> <?= htmlspecialchars(ucfirst($pm)) ?></span>
            <span style="font-size:13px;color:var(--gray);"><i class="fa fa-cube"></i> <?= $order['nb_products'] ?> article(s)</span>
            <div class="order-actions">
                <button class="btn-icon" onclick="viewOrderDetails(<?= $order['id'] ?>)" title="Détails"><i class="fa fa-eye"></i></button>
                <button class="btn-icon danger" onclick="confirmDelete(<?= $order['id'] ?>, '<?= str_pad($order['id'],6,'0',STR_PAD_LEFT) ?>')" title="Supprimer"><i class="fa fa-trash"></i></button>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- ═══════════════════════════════════════
     ADD PRODUCT SECTION
═══════════════════════════════════════════ -->
<div id="add-section" class="tk-section">
    <div class="tk-add">
        <div class="tk-add-card">
            <h2>Ajouter un produit</h2>
            <p>Remplissez le formulaire pour ajouter un nouveau produit au catalogue.</p>
            <?php if($add_success): ?><div class="tk-alert success"><?= htmlspecialchars($add_success) ?></div><?php endif; ?>
            <?php if($add_error):   ?><div class="tk-alert error"><?= htmlspecialchars($add_error) ?></div><?php endif; ?>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add_product">
                <div class="tk-form-group">
                    <label>Nom du produit *</label>
                    <input type="text" name="nom" required placeholder="Ex: Ordinateur Portable Dell XPS">
                </div>
                <div class="tk-form-row">
                    <div class="tk-form-group">
                        <label>Catégorie *</label>
                        <select name="categorie_id" required>
                            <option value="">-- Sélectionnez --</option>
                            <?php foreach($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nom']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="tk-form-group">
                        <label>Prix (DT) *</label>
                        <input type="number" step="0.001" name="prix" required placeholder="0.000">
                    </div>
                </div>
                <div class="tk-form-group">
                    <label>Stock initial</label>
                    <input type="number" name="stock" min="0" value="0">
                </div>
                <div class="tk-form-group">
                    <label>Description</label>
                    <textarea name="description" rows="3" placeholder="Décrivez le produit..."></textarea>
                </div>
                <div class="tk-form-group">
                    <label>Image du produit</label>
                    <input type="file" name="image" accept="image/*" style="padding:8px;">
                </div>
                <button type="submit" class="btn-primary-tk">
                    <i class="fa fa-plus-circle"></i> Ajouter le produit
                </button>
            </form>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════
     CART DRAWER
═══════════════════════════════════════════ -->
<div class="cart-overlay" id="cartOverlay" onclick="closeCart()"></div>
<div class="cart-drawer" id="cartDrawer">
    <div class="cart-drawer-head">
        <h2>🛒 Mon panier</h2>
        <button class="cart-close" onclick="closeCart()">✕</button>
    </div>
    <div class="cart-items" id="cartItems">
        <div class="cart-empty">
            <div class="icon">🛍️</div>
            <p>Votre panier est vide.<br>Ajoutez des produits pour commencer.</p>
        </div>
    </div>
    <div class="cart-footer" id="cartFooter" style="display:none;">
        <div class="cart-total-row">
            <span class="label">Total</span>
            <span class="amount" id="cartTotal">0.000 DT</span>
        </div>
        <button class="btn-checkout" onclick="goToCheckout()">
            <i class="fa fa-lock"></i> Passer au paiement
        </button>
    </div>
</div>

<!-- ═══════════════════════════════════════
     ORDER DETAILS MODAL
═══════════════════════════════════════════ -->
<div class="tk-modal-backdrop" id="orderDetailsModal">
    <div class="tk-modal">
        <div class="tk-modal-head">
            <h3>Détails de la commande</h3>
            <button class="tk-modal-close" onclick="closeModal('orderDetailsModal')">✕</button>
        </div>
        <div class="tk-modal-body" id="orderDetailsContent">
            <div style="text-align:center;padding:40px;color:var(--gray);">
                <div class="spinner" style="border-color:rgba(59,175,218,.3);border-top-color:var(--accent);width:30px;height:30px;margin:0 auto 12px;"></div>
                Chargement...
            </div>
        </div>
    </div>
</div>

<!-- Delete confirm modal -->
<div class="tk-modal-backdrop" id="deleteModal">
    <div class="tk-modal" style="max-width:380px;">
        <div class="tk-modal-head">
            <h3>Confirmer la suppression</h3>
            <button class="tk-modal-close" onclick="closeModal('deleteModal')">✕</button>
        </div>
        <div class="tk-modal-body">
            <p style="color:var(--gray);margin-bottom:20px;">Voulez-vous vraiment supprimer la commande <strong id="deleteOrderNum"></strong> ? Cette action est irréversible.</p>
            <div style="display:flex;gap:10px;">
                <button onclick="closeModal('deleteModal')" class="btn-primary-tk" style="background:var(--light);color:var(--navy);flex:1;">Annuler</button>
                <a id="confirmDeleteLink" href="#" class="btn-primary-tk" style="background:var(--red);flex:1;text-decoration:none;justify-content:center;">Supprimer</a>
            </div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════
     AI ASSISTANT
═══════════════════════════════════════════ -->
<div class="ai-bubble-wrap" id="aiWrap" style="display:none;">
    <div class="ai-panel" id="aiPanel">
        <div class="ai-panel-head">
            <div class="ai-av">🤖</div>
            <div>
                <h5 id="aiProdName">Assistant</h5>
                <p>Posez vos questions sur ce produit</p>
            </div>
            <button class="ai-close" onclick="closeAIChat()">✕</button>
        </div>
        <div class="ai-msgs" id="aiMsgs"></div>
        <div class="ai-chips" id="aiChips"></div>
        <div class="ai-input-row">
            <input type="text" id="aiInput" placeholder="Votre question..." onkeydown="if(event.key==='Enter')sendAIMsg()">
            <button class="ai-send" onclick="sendAIMsg()"><i class="fa fa-paper-plane"></i></button>
        </div>
        <div class="ai-footer-note">Takwinibot Assistant ✨</div>
    </div>
    <button class="ai-toggle-btn" onclick="toggleAI()" title="Assistant IA">🤖</button>
</div>

<!-- Hidden form for order submission (kept from original) -->
<form method="POST" action="" id="realOrderForm" style="display:none;">
    <input type="hidden" name="submit_order" value="1">
    <input type="hidden" name="produit_id"      id="form_produit_id">
    <input type="hidden" name="quantite"         id="form_quantite">
    <input type="hidden" name="nom_complet"      id="form_nom">
    <input type="hidden" name="email"            id="form_email">
    <input type="hidden" name="telephone"        id="form_telephone">
    <input type="hidden" name="adresse"          id="form_adresse">
    <input type="hidden" name="payment_method"   id="form_payment_method">
    <input type="hidden" name="payment_phone"    id="form_payment_phone">
</form>

<!-- ═══════════════════════════════════════
     FOOTER
═══════════════════════════════════════════ -->
<footer style="background:var(--navy);color:rgba(255,255,255,.5);text-align:center;padding:24px;font-size:13px;margin-top:60px;">
    Takwinibot &copy; 2026 — Tous droits réservés.
</footer>

<!-- JS -->
<script src="assets/js/jquery-1.12.4.min.js"></script>
<script src="assets/bootstrap/js/bootstrap.min.js"></script>
<script src="assets/js/menu.js"></script>
<script src="assets/js/wow.min.js"></script>
<script src="assets/js/scripts.js"></script>

<script>
// ═══════════════════════════════════════
//  TABS
// ═══════════════════════════════════════
document.querySelectorAll('.tk-tab').forEach(function(btn){
    btn.addEventListener('click', function(){
        document.querySelectorAll('.tk-tab').forEach(function(b){ b.classList.remove('active'); });
        document.querySelectorAll('.tk-section').forEach(function(s){ s.classList.remove('active'); });
        btn.classList.add('active');
        document.getElementById(btn.dataset.section + '-section').classList.add('active');
    });
});

// ═══════════════════════════════════════
//  CART STATE
// ═══════════════════════════════════════
var cart = [];

function addToCart(id, nom, prix, image, icon, stock) {
    var existing = cart.find(function(i){ return i.id === id; });
    if (existing) {
        if (existing.qty < stock) { existing.qty++; }
        else { showToast('Stock maximum atteint!', 'warning'); return; }
    } else {
        cart.push({ id:id, nom:nom, prix:prix, image:image, icon:icon, stock:stock, qty:1 });
    }
    updateCartUI();
    openCart();
    showToast(nom + ' ajouté au panier!', 'success');
}

function removeFromCart(id) {
    cart = cart.filter(function(i){ return i.id !== id; });
    updateCartUI();
}

function changeQty(id, delta) {
    var item = cart.find(function(i){ return i.id === id; });
    if (!item) return;
    item.qty += delta;
    if (item.qty <= 0) { removeFromCart(id); return; }
    if (item.qty > item.stock) { item.qty = item.stock; showToast('Stock maximum atteint!','warning'); }
    updateCartUI();
}

function getCartTotal() {
    return cart.reduce(function(sum, i){ return sum + (i.prix * i.qty); }, 0);
}

function getCartCount() {
    return cart.reduce(function(sum, i){ return sum + i.qty; }, 0);
}

function updateCartUI() {
    var count = getCartCount();
    document.getElementById('cartCount').textContent = count;

    var itemsEl = document.getElementById('cartItems');
    var footerEl = document.getElementById('cartFooter');

    if (cart.length === 0) {
        itemsEl.innerHTML = '<div class="cart-empty"><div class="icon">🛍️</div><p>Votre panier est vide.<br>Ajoutez des produits pour commencer.</p></div>';
        footerEl.style.display = 'none';
        return;
    }

    footerEl.style.display = 'block';
    document.getElementById('cartTotal').textContent = getCartTotal().toFixed(3) + ' DT';

    itemsEl.innerHTML = cart.map(function(item){
        var imgHtml = item.image
            ? '<img src="../back/uploads/produits/'+item.image+'" alt="">'
            : '<div class="ph">'+item.icon+'</div>';
        return '<div class="cart-item">'+
            '<div class="cart-item-img">'+imgHtml+'</div>'+
            '<div class="cart-item-info">'+
                '<h4>'+item.nom+'</h4>'+
                '<div class="price">'+item.prix.toFixed(3)+' DT</div>'+
                '<div class="cart-item-qty">'+
                    '<button class="qty-btn" onclick="changeQty('+item.id+',-1)">−</button>'+
                    '<span class="qty-num">'+item.qty+'</span>'+
                    '<button class="qty-btn" onclick="changeQty('+item.id+',1)">+</button>'+
                '</div>'+
            '</div>'+
            '<button class="cart-item-remove" onclick="removeFromCart('+item.id+')" title="Supprimer">✕</button>'+
        '</div>';
    }).join('');
}

function openCart() {
    document.getElementById('cartDrawer').classList.add('open');
    document.getElementById('cartOverlay').classList.add('open');
}

function closeCart() {
    document.getElementById('cartDrawer').classList.remove('open');
    document.getElementById('cartOverlay').classList.remove('open');
}

// ═══════════════════════════════════════
//  CHECKOUT — pass cart to checkout.php
// ═══════════════════════════════════════
function goToCheckout() {
    if (cart.length === 0) return;
    sessionStorage.setItem('tk_cart', JSON.stringify(cart));
    window.location.href = 'checkout.php';
}

// ═══════════════════════════════════════
//  TOAST NOTIFICATION
// ═══════════════════════════════════════
function showToast(msg, type) {
    var t = document.createElement('div');
    t.style.cssText = 'position:fixed;bottom:90px;left:50%;transform:translateX(-50%);'+
        'background:'+(type==='success'?'#0f172a':'#f59e0b')+';color:#fff;'+
        'padding:12px 24px;border-radius:50px;font-size:14px;font-weight:600;'+
        'z-index:9999;white-space:nowrap;box-shadow:0 4px 20px rgba(0,0,0,.3);'+
        'transition:opacity .3s;font-family:"DM Sans",sans-serif;';
    t.textContent = msg;
    document.body.appendChild(t);
    setTimeout(function(){ t.style.opacity='0'; setTimeout(function(){ t.remove(); },300); }, 2500);
}

// ═══════════════════════════════════════
//  MODALS
// ═══════════════════════════════════════
function closeModal(id) {
    document.getElementById(id).classList.remove('open');
}
document.querySelectorAll('.tk-modal-backdrop').forEach(function(m){
    m.addEventListener('click', function(e){ if(e.target===m) m.classList.remove('open'); });
});

function viewOrderDetails(orderId) {
    var modal = document.getElementById('orderDetailsModal');
    modal.classList.add('open');
    document.getElementById('orderDetailsContent').innerHTML = '<div style="text-align:center;padding:40px;color:var(--gray);">Chargement...</div>';
    fetch('produits.php?get_order_details='+orderId)
        .then(function(r){ return r.json(); })
        .then(function(data){
            var pm = data.order.payment_method || 'N/A';
            var pmIcon = pm==='flouci'?'📱':(pm==='d17'?'🏦':(pm==='livraison'?'🚚':'❓'));
            var html = '<p><strong>Commande #'+String(orderId).padStart(6,'0')+'</strong></p>'+
                '<p style="color:var(--gray);font-size:13px;">'+new Date(data.order.date_commande).toLocaleString()+'</p>'+
                '<p><span class="status-badge status-'+data.order.statut.replace(/ /g,'-').replace(/é/g,'e')+'">'+data.order.statut+'</span></p>'+
                '<p><span class="pm-badge">'+pmIcon+' '+pm+'</span></p>'+
                (data.order.payment_phone?'<p>Tel paiement: +216 '+data.order.payment_phone+'</p>':'')+
                '<p style="font-size:18px;font-weight:800;color:var(--accent);">Total: '+parseFloat(data.order.total).toFixed(3)+' DT</p>'+
                '<table style="width:100%;font-size:13px;border-collapse:collapse;margin-top:12px;">'+
                '<thead><tr style="background:var(--navy);color:#fff;"><th style="padding:8px;border-radius:6px 0 0 0;">Produit</th><th style="padding:8px;">Qté</th><th style="padding:8px;">Prix</th><th style="padding:8px;border-radius:0 6px 0 0;">Total</th></tr></thead><tbody>';
            data.items.forEach(function(item){
                html += '<tr style="border-bottom:1px solid var(--light);">'+
                    '<td style="padding:8px;">'+item.product_name+'</td>'+
                    '<td style="padding:8px;text-align:center;">'+item.quantite+'</td>'+
                    '<td style="padding:8px;">'+parseFloat(item.prix_unitaire).toFixed(3)+' DT</td>'+
                    '<td style="padding:8px;font-weight:700;">'+((item.quantite*item.prix_unitaire).toFixed(3))+' DT</td></tr>';
            });
            html += '</tbody></table>'+
                '<div style="background:var(--off);border-radius:10px;padding:14px;margin-top:16px;font-size:13px;">'+
                '<p style="margin:0 0 4px;"><strong>Client:</strong> '+data.order.nom_client+'</p>'+
                '<p style="margin:0 0 4px;"><strong>Email:</strong> '+data.order.email_client+'</p>'+
                '<p style="margin:0 0 4px;"><strong>Tél:</strong> '+data.order.telephone_client+'</p>'+
                '<p style="margin:0;"><strong>Adresse:</strong> '+data.order.adresse_livraison.replace(/\n/g,'<br>')+'</p>'+
                '</div>';
            document.getElementById('orderDetailsContent').innerHTML = html;
        });
}

function confirmDelete(id, num) {
    document.getElementById('deleteOrderNum').textContent = '#'+num;
    document.getElementById('confirmDeleteLink').href = '?delete_order='+id;
    document.getElementById('deleteModal').classList.add('open');
}

// ═══════════════════════════════════════
//  AI ASSISTANT
// ═══════════════════════════════════════
var currentProduct = null;
var categoryChips = {
    'Informatique':    ['Compatible Linux?','Garantie?','Caractéristiques?','Livraison?'],
    'Formation':       ['Certificat inclus?','Durée?','Niveau requis?','Accès à vie?'],
    'Food':            ['Ingrédients?','Date expiration?','Allergènes?','Livraison rapide?'],
    'Accessories':     ['Matière?','Garantie?','Taille disponible?','Livraison?'],
    'Bureautique':     ['Compatible Windows?','Garantie?','Livraison?','Marque?'],
    'Logiciels':       ['Licence?','Compatible Mac?','Mises à jour?','Installation?'],
    'Matériel réseau': ['Portée WiFi?','Compatible routeur?','Garantie?','Installation?']
};
var categoryAnswers = {
    'Informatique':{ 'garantie':'2 ans de garantie constructeur. SAV 6j/7.','linux':'Compatible Ubuntu 22.04+. Drivers principaux supportés.','windows':'Livré avec Windows 11 préinstallé.','compatible':'Compatible Windows 11, Ubuntu et distributions Linux.','caracteristique':'Specs: ' },
    'Formation':{ 'certificat':'Certificat de réussite délivré à la fin.','duree':'~40h de contenu à votre rythme.','niveau':'Aucun prérequis. Commence de zéro.','acces':'Accès à vie, mises à jour incluses.','livraison':'Formation en ligne — accès immédiat.' },
    'Food':{ 'ingredient':'Consultez l\'emballage pour la liste complète.','expir':'Toujours frais à la livraison.','allergen':'Consultez la fiche ou contactez-nous.','livraison':'Livraison sous 24-48h.' },
    'Logiciels':{ 'licence':'Licence permanente incluse.','mac':'Vérifiez la description pour compatibilité Mac.','mise':'Mises à jour mineures incluses.','install':'Envoyé par email après paiement.' }
};
var genericAnswers = {
    'prix':       function(p){ return '"'+p.nom+'" est à '+parseFloat(p.prix).toFixed(3)+' DT. Flouci, D17 ou livraison.'; },
    'combien':    function(p){ return 'Ce produit coûte '+parseFloat(p.prix).toFixed(3)+' DT.'; },
    'stock':      function(p){ return parseInt(p.stock)>0?'Il reste '+p.stock+' unité(s) en stock.':'Désolé, en rupture de stock.'; },
    'disponible': function(p){ return parseInt(p.stock)>0?p.nom+' est disponible.':'En rupture de stock.'; },
    'livraison':  function()  { return 'Livraison en Tunisie sous 2-4 jours ouvrés.'; },
    'paiement':   function()  { return 'Flouci 📱, D17 🏦 ou espèces à la livraison 🚚.'; },
    'flouci':     function()  { return 'Oui, Flouci accepté. Entrez votre numéro lors de la commande.'; },
    'd17':        function()  { return 'Oui, paiement D17 (Poste Tunisienne) disponible.'; },
    'bonjour':    function(p) { return 'Bonjour! Je vous aide pour "'+p.nom+'". Posez votre question!'; },
    'merci':      function()  { return 'Avec plaisir! 😊'; },
    'description':function(p) { return p.description||'Consultez la fiche produit pour les détails.'; },
    'info':       function(p) { return p.nom+' | '+p.categorie+' | '+parseFloat(p.prix).toFixed(3)+' DT | Stock: '+p.stock; }
};

function openAIChat(nom, cat, prix, stock, desc) {
    currentProduct = {nom:nom, categorie:cat, prix:prix, stock:stock, description:desc};
    document.getElementById('aiProdName').textContent = nom;
    document.getElementById('aiWrap').style.display = 'block';
    document.getElementById('aiPanel').classList.add('open');
    document.getElementById('aiMsgs').innerHTML = '';
    appendAI('bot','Bonjour! Je suis votre assistant pour <strong>'+nom+'</strong>. Que souhaitez-vous savoir?');
    var chips = categoryChips[cat] || ['Prix?','Stock?','Livraison?','Paiement?'];
    var el = document.getElementById('aiChips');
    el.innerHTML = '';
    chips.forEach(function(q){
        var s = document.createElement('span');
        s.className='ai-chip'; s.textContent=q;
        s.onclick=function(){ askAI(q); };
        el.appendChild(s);
    });
    document.getElementById('aiInput').focus();
}
function closeAIChat() { document.getElementById('aiPanel').classList.remove('open'); document.getElementById('aiWrap').style.display='none'; }
function toggleAI() { document.getElementById('aiPanel').classList.toggle('open'); }
function askAI(q) { document.getElementById('aiChips').style.display='none'; sendAIMsg(q); }
function appendAI(role, html, typing) {
    var box = document.getElementById('aiMsgs');
    var row = document.createElement('div'); row.className='ai-msg-row '+role;
    var bub = document.createElement('div'); bub.className='ai-bub '+(typing?'typing':role);
    bub.innerHTML = html;
    if(role==='bot'){ var av=document.createElement('div'); av.style.cssText='width:20px;height:20px;background:#3bafda22;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:11px;flex-shrink:0;'; av.textContent='🤖'; row.appendChild(av); }
    row.appendChild(bub); box.appendChild(row); box.scrollTop=box.scrollHeight; return bub;
}
function getSmartAnswer(q) {
    if(!currentProduct) return 'Sélectionnez un produit d\'abord.';
    var l=q.toLowerCase();
    for(var k in genericAnswers) if(l.includes(k)) return genericAnswers[k](currentProduct);
    var ca=categoryAnswers[currentProduct.categorie]||{};
    for(var k in ca){ if(l.includes(k)){ var a=ca[k]; if(k==='caracteristique') a+=currentProduct.description; return a; } }
    return 'Pour ce produit: '+currentProduct.description+'. Contactez notre équipe pour plus de détails.';
}
function sendAIMsg(text) {
    var inp=document.getElementById('aiInput');
    var q=text||inp.value.trim(); if(!q) return; inp.value='';
    appendAI('user',q);
    var t=appendAI('bot','Recherche en cours…',true);
    setTimeout(function(){ t.className='ai-bub bot'; t.innerHTML=getSmartAnswer(q); document.getElementById('aiMsgs').scrollTop=9999; },600+Math.random()*400);
}

// ═══════════════════════════════════════
//  ORDER FORM SUBMIT (kept for checkout.php to POST back here)
// ═══════════════════════════════════════
function submitOrderForm(produit_id,quantite,nom,email,telephone,adresse,payment_method,payment_phone){
    document.getElementById('form_produit_id').value=produit_id;
    document.getElementById('form_quantite').value=quantite;
    document.getElementById('form_nom').value=nom;
    document.getElementById('form_email').value=email;
    document.getElementById('form_telephone').value=telephone;
    document.getElementById('form_adresse').value=adresse;
    document.getElementById('form_payment_method').value=payment_method;
    document.getElementById('form_payment_phone').value=payment_phone;
    document.getElementById('realOrderForm').submit();
}
</script>
</body>
</html>