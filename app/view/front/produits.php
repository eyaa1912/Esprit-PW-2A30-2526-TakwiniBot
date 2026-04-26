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
//  DELETE ORDER
// ============================================================
if (isset($_GET['delete_order']) && is_numeric($_GET['delete_order'])) {
    $order_id = intval($_GET['delete_order']);
    
    try {
        // Start transaction
        $pdo->beginTransaction();
        
        // First, get all items in this order to restore stock
        $stmt = $pdo->prepare("SELECT produit_id, quantite FROM ligne_commande WHERE commande_id = ?");
        $stmt->execute([$order_id]);
        $items = $stmt->fetchAll();
        
        // Restore stock for each product
        foreach ($items as $item) {
            $stmt = $pdo->prepare("UPDATE produit SET stock = stock + ? WHERE id = ?");
            $stmt->execute([$item['quantite'], $item['produit_id']]);
        }
        
        // Delete from ligne_commande first (foreign key constraint)
        $stmt = $pdo->prepare("DELETE FROM ligne_commande WHERE commande_id = ?");
        $stmt->execute([$order_id]);
        
        // Then delete from commande
        $stmt = $pdo->prepare("DELETE FROM commande WHERE id = ?");
        $stmt->execute([$order_id]);
        
        $pdo->commit();
        
        $_SESSION['order_success'] = "Commande #$order_id supprimée avec succès!";
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['order_error'] = "Erreur lors de la suppression: " . $e->getMessage();
    }
    
    header("Location: produits.php");
    exit();
}

// ============================================================
//  ORDER PROCESSING (when form is submitted)
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_order'])) {
    $produit_id = intval($_POST['produit_id']);
    $quantite = intval($_POST['quantite']);
    $nom_complet = trim($_POST['nom_complet']);
    $email = trim($_POST['email']);
    $telephone = trim($_POST['telephone']);
    $adresse = trim($_POST['adresse']);
    
    if ($produit_id > 0 && $quantite > 0 && !empty($nom_complet) && !empty($email) && !empty($telephone) && !empty($adresse)) {
        try {
            // Get product details
            $stmt = $pdo->prepare("SELECT * FROM produit WHERE id = ?");
            $stmt->execute([$produit_id]);
            $product = $stmt->fetch();
            
            if ($product && $product['stock'] >= $quantite) {
                $total = $product['prix'] * $quantite;
                
                $pdo->beginTransaction();
                
                // Insert into commande table
                $stmt = $pdo->prepare("INSERT INTO commande (date_commande, statut, total, nom_client, email_client, telephone_client, adresse_livraison) 
                                       VALUES (NOW(), 'en attente', ?, ?, ?, ?, ?)");
                $stmt->execute([$total, $nom_complet, $email, $telephone, $adresse]);
                $commande_id = $pdo->lastInsertId();
                
                // Insert into ligne_commande table
                $stmt = $pdo->prepare("INSERT INTO ligne_commande (commande_id, produit_id, quantite, prix_unitaire) VALUES (?, ?, ?, ?)");
                $stmt->execute([$commande_id, $produit_id, $quantite, $product['prix']]);
                
                // Update product stock
                $stmt = $pdo->prepare("UPDATE produit SET stock = stock - ? WHERE id = ?");
                $stmt->execute([$quantite, $produit_id]);
                
                $pdo->commit();
                
                $_SESSION['order_success'] = "Commande #$commande_id créée avec succès!";
                header("Location: produits.php");
                exit();
            } else {
                $_SESSION['order_error'] = "Stock insuffisant! Seulement " . $product['stock'] . " disponible(s).";
            }
        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['order_error'] = "Erreur: " . $e->getMessage();
        }
    } else {
        $_SESSION['order_error'] = "Veuillez remplir tous les champs!";
    }
    header("Location: produits.php");
    exit();
}

// ============================================================
//  GET ALL ORDERS FOR DISPLAY
// ============================================================
$all_orders = [];
$stmt = $pdo->query("SELECT c.*, 
        (SELECT COUNT(*) FROM ligne_commande WHERE commande_id = c.id) as nb_products
        FROM commande c 
        ORDER BY c.date_commande DESC");
$all_orders = $stmt->fetchAll();

// ============================================================
//  AJAX - GET ORDER DETAILS
// ============================================================
if (isset($_GET['get_order_details']) && is_numeric($_GET['get_order_details'])) {
    $order_id = intval($_GET['get_order_details']);
    
    $stmt = $pdo->prepare("SELECT * FROM commande WHERE id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();
    
    if ($order) {
        $stmt = $pdo->prepare("
            SELECT lc.*, p.nom as product_name, p.image 
            FROM ligne_commande lc 
            JOIN produit p ON lc.produit_id = p.id 
            WHERE lc.commande_id = ?
        ");
        $stmt->execute([$order_id]);
        $items = $stmt->fetchAll();
        
        header('Content-Type: application/json');
        echo json_encode([
            'order' => $order,
            'items' => $items
        ]);
        exit();
    }
}

// ============================================================
//  HANDLE PRODUCT ADDITION
// ============================================================
$add_success = null;
$add_error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_product') {
    $nom = trim($_POST['nom'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $prix = floatval($_POST['prix'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);
    $categorie_id = intval($_POST['categorie_id'] ?? 0);
    
    if (empty($nom) || $prix <= 0 || $categorie_id <= 0) {
        $add_error = "Veuillez remplir tous les champs obligatoires (nom, prix, catégorie).";
    } else {
        try {
            $image_path = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = '../back/uploads/produits/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $filename = uniqid() . '.' . $ext;
                $destination = $upload_dir . $filename;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
                    $image_path = $filename;
                }
            }
            
            $sql = "INSERT INTO produit (nom, description, prix, stock, categorie_id, image) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nom, $description, $prix, $stock, $categorie_id, $image_path]);
            $add_success = "Produit \"$nom\" ajouté avec succès !";
        } catch (PDOException $e) {
            $add_error = "Erreur lors de l'ajout : " . $e->getMessage();
        }
    }
}

// ============================================================
//  GET PRODUCTS FOR DISPLAY
// ============================================================
$categorie_id = isset($_GET['categorie']) ? (int)$_GET['categorie'] : 0;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$sql = "SELECT p.*, c.nom AS categorie_nom 
        FROM produit p 
        LEFT JOIN categorie c ON p.categorie_id = c.id 
        WHERE 1=1";
$params = [];

if ($categorie_id > 0) {
    $sql .= " AND p.categorie_id = ?";
    $params[] = $categorie_id;
}

if (!empty($search)) {
    $sql .= " AND (p.nom LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= " ORDER BY p.id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$produits = $stmt->fetchAll();

$categories = $pdo->query("SELECT * FROM categorie ORDER BY nom")->fetchAll();

// Icon + color map
$cat_icons = [
    'Food'             => ['icon' => '🍔', 'color' => '#ff6b6b'],
    'Formation'        => ['icon' => '📚', 'color' => '#4ecdc4'],
    'Accessories'      => ['icon' => '⌚', 'color' => '#45b7d1'],
    'Informatique'     => ['icon' => '💻', 'color' => '#96ceb4'],
    'Bureautique'      => ['icon' => '📎', 'color' => '#ffeaa7'],
    'Logiciels'        => ['icon' => '💿', 'color' => '#dda0dd'],
    'Matériel réseau'  => ['icon' => '🌐', 'color' => '#98d8c8'],
];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nos Produits - Takwinibot</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Exo:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/fonts/font-awesome.min.css">
    <link rel="stylesheet" href="assets/fonts/themify-icons.css">
    <link rel="stylesheet" href="assets/owlcarousel/css/owl.carousel.css">
    <link rel="stylesheet" href="assets/owlcarousel/css/owl.theme.css">
    <link rel="stylesheet" href="assets/css/fonts.css">
    <link href="assets/css/prettyPhoto.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/animate.css">
    <link rel="stylesheet" href="assets/css/slick.css">
    <link rel="stylesheet" href="assets/css/menu.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
    <style>
        /* ── Category Filter Bar ── */
        .category-filter-section {
            padding: 40px 0 20px;
            background: #f8f9fa;
        }
        .category-filter-section h3 {
            text-align: center;
            font-weight: 700;
            margin-bottom: 30px;
            color: #333;
        }
        .cat-cards-row {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 16px;
        }
        .cat-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 110px;
            height: 110px;
            border-radius: 16px;
            text-decoration: none;
            color: #333;
            font-weight: 600;
            font-size: 13px;
            text-align: center;
            transition: transform 0.2s, box-shadow 0.2s;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            background: #fff;
            border: 2px solid transparent;
            padding: 10px;
        }
        .cat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
            color: #333;
            text-decoration: none;
        }
        .cat-card.active {
            border-color: #3bafda;
            box-shadow: 0 4px 16px rgba(59,175,218,0.3);
        }
        .cat-card .cat-icon {
            font-size: 36px;
            margin-bottom: 8px;
            display: block;
        }
        .cat-card-all {
            background: linear-gradient(135deg, #3bafda, #2a8cbf);
            color: #fff;
        }
        .cat-card-all:hover { color: #fff; }
        .cat-card-all.active { border-color: #2a8cbf; }

        /* ── Product Cards ── */
        .single_property {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .single_property:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.14);
        }
        
        /* ── Add Product Form Styles ── */
        .add-product-section {
            padding: 40px 0;
            background: linear-gradient(135deg, #f0f7ff 0%, #e9f0fa 100%);
            border-radius: 20px;
            margin: 30px 0;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }
        .add-product-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .add-product-card h3 {
            color: #3bafda;
            font-weight: 700;
            margin-bottom: 10px;
        }
        .add-product-card .form-group label {
            font-weight: 600;
            color: #555;
        }
        .add-product-card .form-control {
            border-radius: 10px;
            border: 1px solid #ddd;
            padding: 12px;
        }
        .add-product-card .form-control:focus {
            border-color: #3bafda;
            box-shadow: 0 0 0 0.2rem rgba(59,175,218,0.25);
        }
        .btn-submit-product {
            background: linear-gradient(135deg, #3bafda, #2a8cbf);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 30px;
            font-weight: 600;
            transition: transform 0.2s;
        }
        .btn-submit-product:hover {
            transform: translateY(-2px);
            color: white;
        }
        .alert-custom {
            border-radius: 12px;
            padding: 15px 20px;
            margin-bottom: 20px;
        }
        .choice-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        .choice-btn {
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid #3bafda;
            background: white;
            color: #3bafda;
        }
        .choice-btn.active {
            background: #3bafda;
            color: white;
        }
        .choice-btn:hover {
            transform: translateY(-2px);
        }
        .section-toggle {
            display: none;
        }
        .section-toggle.active-section {
            display: block;
        }
        
        /* Orders Section Styles */
        .orders-section {
            margin-top: 20px;
            margin-bottom: 50px;
        }
        .order-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: all 0.3s;
        }
        .order-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.12);
        }
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
        }
        .status-en-attente { background: #ffc107; color: #000; }
        .status-validee { background: #28a745; color: #fff; }
        .status-expediee { background: #17a2b8; color: #fff; }
        .status-livree { background: #6c757d; color: #fff; }
        .status-annulee { background: #dc3545; color: #fff; }
        
        .order-details-modal .modal-dialog {
            max-width: 700px;
        }
        
        .btn-delete {
            background: #dc3545;
            color: white;
            border: none;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn-delete:hover {
            background: #c82333;
            transform: scale(1.05);
        }
        
        .action-buttons {
            display: flex;
            gap: 8px;
            justify-content: flex-end;
        }

/* Form validation styles */
.form-control.is-invalid {
    border-color: #dc3545;
    background-image: url("data:image/svg+xml,...");
}

.form-control.is-valid {
    border-color: #28a745;
}

.invalid-feedback {
    display: none;
    color: #dc3545;
    font-size: 12px;
    margin-top: 5px;
}

.valid-feedback {
    display: none;
    color: #28a745;
    font-size: 12px;
    margin-top: 5px;
}

.form-control.is-invalid ~ .invalid-feedback {
    display: block;
}

.form-control.is-valid ~ .valid-feedback {
    display: block;
}

/* Price input styling */
.price-input-wrapper {
    position: relative;
}

.price-input-wrapper::before {
    content: "DT";
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #666;
    pointer-events: none;
}

/* Stock input with increment/decrement */
.stock-control {
    display: flex;
    align-items: center;
    gap: 10px;
}

.stock-control .form-control {
    flex: 1;
}

.stock-buttons {
    display: flex;
    gap: 5px;
}

.stock-btn {
    width: 36px;
    height: 36px;
    border: 1px solid #ddd;
    background: #f8f9fa;
    border-radius: 8px;
    cursor: pointer;
    font-size: 18px;
    font-weight: bold;
    transition: all 0.2s;
}

.stock-btn:hover {
    background: #e9ecef;
    border-color: #3bafda;
}

/* Image preview with controls */
.image-preview-container {
    position: relative;
    display: inline-block;
}

.image-preview {
    width: 150px;
    height: 150px;
    object-fit: cover;
    border-radius: 12px;
    border: 2px solid #ddd;
    margin-top: 10px;
}

.image-preview-actions {
    margin-top: 10px;
    display: flex;
    gap: 10px;
}

.btn-remove-image {
    background: #dc3545;
    color: white;
    border: none;
    padding: 5px 15px;
    border-radius: 5px;
    cursor: pointer;
    font-size: 12px;
}

/* Character counter */
.char-counter {
    font-size: 11px;
    color: #666;
    text-align: right;
    margin-top: 5px;
}

.char-counter.warning {
    color: #ffc107;
}

.char-counter.danger {
    color: #dc3545;
}

/* Real-time product name preview */
.product-name-preview {
    background: #f8f9fa;
    padding: 10px;
    border-radius: 8px;
    margin-top: 10px;
    display: none;
}

.product-name-preview.active {
    display: block;
}

    </style>
</head>

<body data-spy="scroll" data-offset="80">

    <!-- START PRELOADER -->
    <div class="preloader">
        <div class="status"><div class="status-mes"></div></div>
    </div>
    <!-- END PRELOADER -->

    <!-- START NAVBAR -->
    <div class="site-mobile-menu site-navbar-target">
        <div class="site-mobile-menu-header">
            <div class="site-mobile-menu-close mt-3">
                <span class="icon-close2 js-menu-toggle"></span>
            </div>
        </div>
        <div class="site-mobile-menu-body"></div>
    </div>

    <header class="site-navbar js-sticky-header site-navbar-target" role="banner">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-6 col-xl-2">
                    <h1 class="mb-0 site-logo"><a href="index.html"><img src="assets/img/logo.png" alt=""></a></h1>
                </div>
                <div class="col-12 col-md-10 d-none d-xl-block">
                    <nav class="site-navigation position-relative text-right" role="navigation">
                        <ul class="site-menu main-menu js-clone-nav mr-auto d-none d-lg-block">
                            <li class="has-children">
                                <a href="index.html" class="nav-link">Home</a>
                                <ul class="dropdown">
                                    <li><a href="index_map.html" class="nav-link">Home Map</a></li>
                                    <li><a href="index_parallax.html" class="nav-link">Home Parallax</a></li>
                                    <li><a href="index_slideshow.html" class="nav-link">Home Slider</a></li>
                                    <li><a href="index_video.html" class="nav-link">Home video</a></li>
                                </ul>
                            </li>
                            <li><a class="nav-link" href="about.html">about</a></li>
                            <li class="has-children">
                                <a href="formation.html" class="nav-link">Formations</a>
                                <ul class="dropdown">
                                    <li><a href="formation-details.html" class="nav-link">Détails de la Formation</a></li>
                                </ul>
                            </li>
                            <li class="active"><a href="produits.php" class="nav-link">Produits</a></li>
                            <li><a href="#" class="nav-link" onclick="$('.choice-btn[data-section=\'orders\']').click(); return false;">Mes Commandes</a></li>
                            <li class="has-children">
                                <a href="#" class="nav-link">Pages</a>
                                <ul class="dropdown">
                                    <li><a href="agent_profile.html" class="nav-link">agent profile</a></li>
                                    <li><a href="login.html" class="nav-link">login page</a></li>
                                    <li><a href="register.html" class="nav-link">register page</a></li>
                                    <li><a href="faq.html" class="nav-link">Faqs</a></li>
                                    <li><a href="404.html" class="nav-link">404 page</a></li>
                                </ul>
                            </li>
                            <li class="has-children">
                                <a href="blog.html" class="nav-link">Entretien</a>
                                <ul class="dropdown">
                                    <li><a href="blog.html" class="nav-link">Blog Post</a></li>
                                    <li><a href="blog-post.html" class="nav-link">Blog Single</a></li>
                                </ul>
                            </li>
                            <li><a class="nav-link" href="offres-emploi.html">Offres</a></li>
                            <li class="nav-reclamation-login">
                                <a class="nav-link" href="front_mes_reclamations.html">Réclamations</a>
                                <a href="login.html" class="login-pill">Se connecter</a>
                            </li>
                        </ul>
                    </nav>
                </div>
                <div class="col-6 d-inline-block d-xl-none ml-md-0 py-3" style="position: relative; top: 3px;">
                    <a href="#" class="site-menu-toggle js-menu-toggle float-right"><span class="icon-menu h3"></span></a>
                </div>
            </div>
        </div>
    </header>
    <!-- END NAVBAR -->

    <!-- START HOME -->
    <section id="home" class="home_bg" style="background-image: url(assets/img/bg/home-bg.jpg); background-size:cover; background-position: center center;">
        <div class="container">
            <div class="row">
                <div class="col-lg-10 offset-lg-1 col-sm-12 col-xs-12 text-center">
                    <div class="hero-text">
                        <h2>Bienvenue sur Takwinibot</h2>
                        <p>Parcourez nos produits, passez commande, ou ajoutez un produit</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- END HOME -->

    <!-- START CHOICE SECTION -->
    <div class="container" style="margin-top: 30px;">
        <div class="choice-buttons">
            <button class="choice-btn active" data-section="browse">📋 Parcourir les produits</button>
            <button class="choice-btn" data-section="orders">📦 Mes Commandes</button>
            <button class="choice-btn" data-section="add">➕ Ajouter un produit</button>
        </div>
    </div>

    <!-- START BROWSE PRODUCTS SECTION -->
    <div id="browse-section" class="section-toggle active-section">
        <!-- START CATEGORY FILTER -->
        <div class="category-filter-section">
            <div class="container">
                <h3>Parcourir par catégorie</h3>
                <div class="cat-cards-row">
                    <!-- All -->
                    <a href="produits.php" class="cat-card cat-card-all <?= $categorie_id === 0 ? 'active' : '' ?>">
                        <span class="cat-icon">🛍️</span>
                        Tous
                    </a>
                    <!-- Dynamic categories -->
                    <?php foreach ($categories as $cat): 
                        $info  = $cat_icons[$cat['nom']] ?? ['icon' => '📦', 'color' => '#ddd'];
                        $isActive = $categorie_id == $cat['id'];
                    ?>
                    <a href="produits.php?categorie=<?= $cat['id'] ?>"
                       class="cat-card <?= $isActive ? 'active' : '' ?>"
                       style="background-color: <?= $info['color'] ?>22; <?= $isActive ? 'border-color:' . $info['color'] . ';' : '' ?>">
                        <span class="cat-icon"><?= $info['icon'] ?></span>
                        <?= htmlspecialchars($cat['nom']) ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <!-- END CATEGORY FILTER -->

        <!-- START PRODUCTS -->
        <section class="template_property section-padding">
            <div class="container">
                <div class="section-title text-center wow zoomIn">
                    <h2>
                        <?php if ($categorie_id > 0):
                            $activeCat = array_filter($categories, fn($c) => $c['id'] == $categorie_id);
                            $activeCat = reset($activeCat);
                            $info = $cat_icons[$activeCat['nom']] ?? ['icon' => '📦'];
                            echo $info['icon'] . ' ' . htmlspecialchars($activeCat['nom']);
                        else: ?>
                            Tous les Produits
                        <?php endif; ?>
                    </h2>
                    <div></div>
                    <p class="mt-2"><?= count($produits) ?> produit(s) trouvé(s)</p>
                </div>

                <?php if (empty($produits)): ?>
                    <div class="row">
                        <div class="col-lg-12 text-center">
                            <div style="padding: 60px 20px; background: #f8f9fa; border-radius: 12px;">
                                <span style="font-size:64px;">📦</span>
                                <h3 class="mt-3">Aucun produit trouvé</h3>
                                <p>Cette catégorie ne contient pas encore de produits.</p>
                                <a href="produits.php" class="btn btn-serach-bg">Voir tous les produits</a>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($produits as $p): 
                            $categorie_nom = $p['categorie_nom'] ?? 'Produit';
                            $info = $cat_icons[$categorie_nom] ?? ['icon' => '📦', 'color' => '#667eea'];
                        ?>
                            <div class="col-lg-4 col-sm-12 col-xs-12 mb-4">
                                <div class="single_property">
                                    <?php if (!empty($p['image'])): ?>
                                        <img src="../back/uploads/produits/<?= htmlspecialchars($p['image']) ?>"
                                             alt="<?= htmlspecialchars($p['nom']) ?>"
                                             style="width:100%; height:200px; object-fit:cover;">
                                    <?php else: ?>
                                        <div style="height:200px; background:linear-gradient(135deg, <?= $info['color'] ?> 0%, <?= $info['color'] ?>99 100%); display:flex; align-items:center; justify-content:center; font-size:64px;">
                                            <?= $info['icon'] ?>
                                        </div>
                                    <?php endif; ?>

                                    <div class="single_property_description text-center">
                                        <span><i class="fa fa-tag"></i> <?= htmlspecialchars($categorie_nom) ?></span>
                                    </div>
                                    <div class="single_property_content">
                                        <h4><a href="#"><?= htmlspecialchars($p['nom']) ?></a></h4>
                                        <p><?= htmlspecialchars(substr($p['description'] ?? '', 0, 100)) ?><?= strlen($p['description'] ?? '') > 100 ? '...' : '' ?></p>
                                    </div>
                                    <div class="single_property_price">
                                        <?= number_format($p['prix'], 2) ?> DT
                                        <?php if ($p['stock'] > 0): ?>
                                            <span style="font-size:12px; color:#28a745; margin-left:10px;">
                                                <i class="fa fa-check-circle"></i> En stock
                                            </span>
                                        <?php else: ?>
                                            <span style="font-size:12px; color:#dc3545; margin-left:10px;">
                                                <i class="fa fa-times-circle"></i> Rupture
                                            </span>
                                        <?php endif; ?>
                                        <br>
                                        <a href="#" data-toggle="modal" data-target="#commandeModal"
                                           data-produit-id="<?= $p['id'] ?>"
                                           data-produit-nom="<?= htmlspecialchars($p['nom']) ?>"
                                           data-produit-prix="<?= $p['prix'] ?>"
                                           class="btn btn-serach-bg"
                                           style="display:inline-block; margin-top:15px; padding:5px 20px; font-size:14px; background-color:#3bafda; color:#fff; border-radius:4px;">
                                            <i class="fa fa-shopping-cart"></i> Commander
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
        <!-- END PRODUCTS -->
    </div>
    <!-- END BROWSE PRODUCTS SECTION -->

    <!-- START ORDERS SECTION -->
    <div id="orders-section" class="section-toggle">
        <div class="container">
            <div class="orders-section">
                <div class="section-title text-center wow zoomIn">
                    <h2>📦 Mes Commandes</h2>
                    <div></div>
                    <p>Historique de toutes vos commandes</p>
                </div>
                
                <?php if (isset($_SESSION['order_success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <i class="fa fa-check-circle"></i> <?= $_SESSION['order_success'] ?>
                        <?php unset($_SESSION['order_success']); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['order_error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <i class="fa fa-exclamation-triangle"></i> <?= $_SESSION['order_error'] ?>
                        <?php unset($_SESSION['order_error']); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (empty($all_orders)): ?>
                    <div class="text-center" style="padding: 60px 20px; background: #f8f9fa; border-radius: 12px;">
                        <span style="font-size: 64px;">📦</span>
                        <h3>Aucune commande pour le moment</h3>
                        <p>Vous n'avez pas encore passé de commande.</p>
                        <button class="btn btn-primary" onclick="$('.choice-btn[data-section=\'browse\']').click();">
                            Commencer mes achats
                        </button>
                    </div>
                <?php else: ?>
                    <?php foreach ($all_orders as $order): ?>
                        <div class="order-card">
                            <div class="row align-items-center">
                                <div class="col-md-3">
                                    <strong>Commande #<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></strong><br>
                                    <small class="text-muted"><?= date('d/m/Y H:i', strtotime($order['date_commande'])) ?></small>
                                </div>
                                <div class="col-md-3">
                                    <span class="status-badge status-<?= str_replace(' ', '-', $order['statut']) ?>">
                                        <?= ucfirst($order['statut']) ?>
                                    </span>
                                </div>
                                <div class="col-md-2">
                                    <strong style="color: #3bafda; font-size: 18px;"><?= number_format($order['total'], 2) ?> DT</strong>
                                </div>
                                <div class="col-md-2">
                                    <small><i class="fa fa-boxes"></i> <?= $order['nb_products'] ?> article(s)</small>
                                </div>
                                <div class="col-md-2">
                                    <div class="action-buttons">
                                        <button class="btn btn-sm btn-info" onclick="viewOrderDetails(<?= $order['id'] ?>)">
                                            <i class="fa fa-eye"></i> Détails
                                        </button>
                                        <button class="btn-delete" onclick="deleteOrder(<?= $order['id'] ?>, '<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?>')">
                                            <i class="fa fa-trash"></i> Supprimer
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <!-- END ORDERS SECTION -->

    <!-- START ADD PRODUCT SECTION -->
<div id="add-section" class="section-toggle">
    <div class="container">
        <div class="add-product-section">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="add-product-card">
                        <div class="text-center">
                            <span style="font-size: 48px;">🛍️</span>
                            <h3>Ajouter un nouveau produit</h3>
                            <p>Partagez votre produit avec la communauté Takwinibot</p>
                        </div>
                        
                        <?php if ($add_success): ?>
                            <div class="alert alert-success alert-custom">
                                <i class="fa fa-check-circle"></i> <?= htmlspecialchars($add_success) ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($add_error): ?>
                            <div class="alert alert-danger alert-custom">
                                <i class="fa fa-exclamation-triangle"></i> <?= htmlspecialchars($add_error) ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="" enctype="multipart/form-data" id="addProductForm">
                            <input type="hidden" name="action" value="add_product">
                            
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label><i class="fa fa-tag"></i> Nom du produit *</label>
                                        <input type="text" class="form-control" name="nom" id="productName" required 
                                               placeholder="Ex: Ordinateur Portable" 
                                               onkeyup="validateProductName()">
                                        <div class="invalid-feedback">Le nom du produit est requis (minimum 3 caractères)</div>
                                        <div class="valid-feedback">Nom du produit valide !</div>
                                    </div>
                                    <div id="namePreview" class="product-name-preview">
                                        <small><i class="fa fa-eye"></i> Aperçu: <strong id="previewName"></strong></small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><i class="fa fa-folder"></i> Catégorie *</label>
                                        <select class="form-control" name="categorie_id" id="categorySelect" required onchange="validateCategory()">
                                            <option value="">-- Sélectionnez une catégorie --</option>
                                            <?php foreach ($categories as $cat): ?>
                                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nom']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="invalid-feedback">Veuillez sélectionner une catégorie</div>
                                        <div class="valid-feedback">Catégorie sélectionnée !</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><i class="fa fa-money"></i> Prix (DT) *</label>
                                        <div class="price-input-wrapper">
                                            <input type="number" step="0.01" class="form-control" name="prix" id="productPrice" 
                                                   required placeholder="0.00" min="0" oninput="validatePrice()">
                                        </div>
                                        <div class="invalid-feedback">Le prix doit être supérieur à 0</div>
                                        <div class="valid-feedback">Prix valide !</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><i class="fa fa-boxes"></i> Stock *</label>
                                        <div class="stock-control">
                                            <input type="number" class="form-control" name="stock" id="productStock" 
                                                   required placeholder="0" min="0" value="0" oninput="validateStock()">
                                            <div class="stock-buttons">
                                                <button type="button" class="stock-btn" onclick="adjustStock(-1)">-</button>
                                                <button type="button" class="stock-btn" onclick="adjustStock(1)">+</button>
                                            </div>
                                        </div>
                                        <div class="invalid-feedback">Le stock doit être un nombre valide (0 ou plus)</div>
                                        <div class="valid-feedback">Stock valide !</div>
                                        <small id="stockWarning" class="text-muted"></small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><i class="fa fa-info-circle"></i> Statut stock</label>
                                        <div id="stockStatus" class="mt-2">
                                            <span class="badge" style="background: #28a745; color: white; padding: 5px 10px;">Disponible</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label><i class="fa fa-align-left"></i> Description</label>
                                <textarea class="form-control" name="description" id="productDescription" 
                                          rows="4" placeholder="Décrivez votre produit..." 
                                          maxlength="500" oninput="updateCharCount()"></textarea>
                                <div class="char-counter" id="charCount">0 / 500 caractères</div>
                            </div>
                            
                            <div class="form-group">
                                <label><i class="fa fa-image"></i> Image du produit</label>
                                <input type="file" class="form-control-file" name="image" id="productImage" 
                                       accept="image/*" onchange="previewImage(this)">
                                <small class="form-text text-muted">Formats acceptés: JPG, PNG, GIF (max 5MB)</small>
                                
                                <div id="imagePreviewContainer" style="display: none;" class="text-center mt-3">
                                    <div class="image-preview-container">
                                        <img id="imagePreview" class="image-preview" src="#" alt="Aperçu">
                                    </div>
                                    <div class="image-preview-actions">
                                        <button type="button" class="btn-remove-image" onclick="removeImage()">
                                            <i class="fa fa-trash"></i> Supprimer l'image
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group" id="formSummary" style="display: none; background: #e8f4f8; padding: 15px; border-radius: 10px; margin-top: 20px;">
                                <h6><i class="fa fa-check-circle"></i> Récapitulatif</h6>
                                <p><strong>Produit:</strong> <span id="summaryName">-</span></p>
                                <p><strong>Catégorie:</strong> <span id="summaryCategory">-</span></p>
                                <p><strong>Prix:</strong> <span id="summaryPrice">-</span> DT</p>
                                <p><strong>Stock:</strong> <span id="summaryStock">-</span> unités</p>
                            </div>
                            
                            <div class="text-center">
                                <button type="button" class="btn btn-secondary" onclick="resetForm()" style="margin-right: 10px;">
                                    <i class="fa fa-refresh"></i> Réinitialiser
                                </button>
                                <button type="submit" class="btn btn-submit-product" id="submitBtn">
                                    <i class="fa fa-plus-circle"></i> Ajouter le produit
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END ADD PRODUCT SECTION -->

    <!-- START FOOTER -->
    <footer class="footer-area">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <div class="footer_social">
                        <ul>
                            <li><a data-toggle="tooltip" data-placement="top" title="Facebook" href="#"><i class="fa fa-facebook"></i></a></li>
                            <li><a data-toggle="tooltip" data-placement="top" title="Twitter" href="#"><i class="fa fa-instagram"></i></a></li>
                            <li><a data-toggle="tooltip" data-placement="top" title="Google Plus" href="#"><i class="fa fa-google-plus"></i></a></li>
                            <li><a data-toggle="tooltip" data-placement="top" title="Linkedin" href="#"><i class="fa fa-linkedin"></i></a></li>
                            <li><a data-toggle="tooltip" data-placement="top" title="Youtube" href="#"><i class="fa fa-youtube"></i></a></li>
                            <li><a data-toggle="tooltip" data-placement="top" title="Skype" href="#"><i class="fa fa-skype"></i></a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="row footer-padding">
                <div class="col-lg-3 col-sm-3 col-xs-12">
                    <div class="single_footer">
                        <h4>Contact Us</h4>
                        <div class="footer_contact">
                            <ul>
                                <li><i class="fa fa-rocket"></i> <span>3481 Melrose Place, Beverly Hills, CA 90210</span></li>
                                <li><i class="fa fa-phone"></i> <span>Call Us - (+1) 517 397 7100</span></li>
                                <li><i class="fa fa-fax"></i> <span>Fax - (+12) 123 1234</span></li>
                                <li><i class="fa fa-envelope"></i> <span>info@example.com</span></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-3 col-xs-12">
                    <div class="single_footer">
                        <h4>Customer service</h4>
                        <div class="footer_contact">
                            <ul>
                                <li><a href="#">My Account</a></li>
                                <li><a href="#">Order History</a></li>
                                <li><a href="#">FAQ</a></li>
                                <li><a href="#">Specials</a></li>
                                <li><a href="#">Help Center</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-3 col-xs-12">
                    <div class="single_footer">
                        <h4>Helpful Link</h4>
                        <div class="footer_contact">
                            <ul>
                                <li><a href="#">About us</a></li>
                                <li><a href="#">Customer Service</a></li>
                                <li><a href="#">Company</a></li>
                                <li><a href="#">Investor Relations</a></li>
                                <li><a href="#">Advanced Search</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-3 col-xs-12">
                    <div class="single_footer">
                        <h4>Why choose Us</h4>
                        <div class="footer_contact">
                            <ul>
                                <li><a href="#">Shopping Guide</a></li>
                                <li><a href="#">Blog</a></li>
                                <li><a href="#">Company</a></li>
                                <li><a href="#">Investor Relations</a></li>
                                <li><a href="front_formulaire_reclamation.html">Contact Us</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row text-center">
                <div class="col-lg-12 col-sm-12 col-xs-12 wow zoomIn">
                    <p class="footer_copyright">Takwinibot &copy; 2026 All Rights Reserved.</p>
                </div>
            </div>
        </div>
    </footer>
    <!-- END FOOTER -->

    <!-- Modal Commande -->
    <div class="modal fade" id="commandeModal" tabindex="-1" role="dialog" aria-hidden="true" style="z-index: 99999;">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Passer commande</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <form method="POST" action="">
                    <input type="hidden" name="submit_order" value="1">
                    <div class="modal-body">
                        <input type="hidden" name="produit_id" id="modal_produit_id">
                        <div class="form-group" style="text-align:left;">
                            <label>Produit</label>
                            <input type="text" class="form-control" id="modal_produit_nom" readonly>
                        </div>
                        <div class="form-group" style="text-align:left;">
                            <label>Prix (DT)</label>
                            <input type="text" class="form-control" id="modal_produit_prix" readonly>
                        </div>
                        <div class="form-group" style="text-align:left;">
                            <label>Quantité</label>
                            <input type="number" class="form-control" name="quantite" value="1" min="1" required>
                        </div>
                        <div class="form-group" style="text-align:left;">
                            <label>Nom complet</label>
                            <input type="text" class="form-control" name="nom_complet" required>
                        </div>
                        <div class="form-group" style="text-align:left;">
                            <label>Email</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="form-group" style="text-align:left;">
                            <label>Téléphone</label>
                            <input type="tel" class="form-control" name="telephone" required>
                        </div>
                        <div class="form-group" style="text-align:left;">
                            <label>Adresse de livraison</label>
                            <textarea class="form-control" name="adresse" rows="2" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-serach-bg" style="background-color:#3bafda; color:#fff;">Confirmer la commande</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Order Details Modal -->
    <div class="modal fade order-details-modal" id="orderDetailsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header" style="background: #3bafda; color: white;">
                    <h5 class="modal-title">Détails de la commande</h5>
                    <button type="button" class="close" data-dismiss="modal" style="color: white;">&times;</button>
                </div>
                <div class="modal-body" id="orderDetailsContent">
                    <div class="text-center">
                        <i class="fa fa-spinner fa-spin"></i> Chargement...
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header" style="background: #dc3545; color: white;">
                    <h5 class="modal-title">Confirmer la suppression</h5>
                    <button type="button" class="close" data-dismiss="modal" style="color: white;">&times;</button>
                </div>
                <div class="modal-body">
                    <p>Êtes-vous sûr de vouloir supprimer cette commande ?</p>
                    <p class="text-danger"><strong>Attention:</strong> Cette action est irréversible et le stock des produits sera restauré.</p>
                    <p><strong>Commande:</strong> <span id="deleteOrderNumber"></span></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <a href="#" id="confirmDeleteBtn" class="btn btn-danger">Supprimer</a>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/jquery-1.12.4.min.js"></script>
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
    <script src="assets/js/modernizr-2.8.3.min.js"></script>
    <script src="assets/js/jquery.stellar.min.js"></script>
    <script src="assets/js/menu.js"></script>
    <script src="assets/js/jquery.sticky.js"></script>
    <script src="assets/owlcarousel/js/owl.carousel.min.js"></script>
    <script src="assets/js/jquery.magnific-popup.min.js"></script>
    <script src="assets/js/slick.min.js"></script>
    <script src="assets/js/jquery.mixitup.js"></script>
    <script src="assets/js/jquery.prettyPhoto.js"></script>
    <script src="assets/js/scrolltopcontrol.js"></script>
    <script src="assets/js/wow.min.js"></script>
    <script src="assets/js/scripts.js"></script>

    <script>
        // Toggle between sections
        $('.choice-btn').on('click', function() {
            var section = $(this).data('section');
            $('.choice-btn').removeClass('active');
            $(this).addClass('active');
            $('.section-toggle').removeClass('active-section');
            $('#' + section + '-section').addClass('active-section');
        });
        
        // Modal commande
        $('#commandeModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget);
            var modal = $(this);
            modal.find('#modal_produit_id').val(button.data('produit-id'));
            modal.find('#modal_produit_nom').val(button.data('produit-nom'));
            modal.find('#modal_produit_prix').val(button.data('produit-prix') + ' DT');
        });
        
        // View order details
        function viewOrderDetails(orderId) {
            $('#orderDetailsModal').modal('show');
            $('#orderDetailsContent').html('<div class="text-center"><i class="fa fa-spinner fa-spin"></i> Chargement...</div>');
            
            $.ajax({
                url: window.location.href,
                type: 'GET',
                data: { get_order_details: orderId },
                dataType: 'json',
                success: function(data) {
                    var html = `
                        <h6>Commande #${String(orderId).padStart(6, '0')}</h6>
                        <p><strong>Date:</strong> ${new Date(data.order.date_commande).toLocaleString()}</p>
                        <p><strong>Statut:</strong> <span class="status-badge status-${data.order.statut.replace(/ /g, '-')}">${data.order.statut}</span></p>
                        <p><strong>Total:</strong> <strong style="color: #3bafda;">${parseFloat(data.order.total).toFixed(2)} DT</strong></p>
                        
                        <h6 class="mt-3">Articles commandés:</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead style="background: #3bafda; color: white;">
                                    <tr><th>Produit</th><th>Qté</th><th>Prix unit.</th><th>Total</th></tr>
                                </thead>
                                <tbody>
                    `;
                    
                    data.items.forEach(function(item) {
                        html += `
                            <tr>
                                <td>${item.product_name}</td>
                                <td>${item.quantite}</td>
                                <td>${parseFloat(item.prix_unitaire).toFixed(2)} DT</td>
                                <td>${(item.quantite * item.prix_unitaire).toFixed(2)} DT</td>
                            </tr>
                        `;
                    });
                    
                    html += `
                                </tbody>
                            </table>
                        </div>
                        
                        <h6>Informations de livraison:</h6>
                        <div style="background: #f8f9fa; padding: 15px; border-radius: 8px;">
                            <p class="mb-1"><strong>Nom:</strong> ${data.order.nom_client}</p>
                            <p class="mb-1"><strong>Email:</strong> ${data.order.email_client}</p>
                            <p class="mb-1"><strong>Téléphone:</strong> ${data.order.telephone_client}</p>
                            <p class="mb-0"><strong>Adresse:</strong><br>${data.order.adresse_livraison.replace(/\n/g, '<br>')}</p>
                        </div>
                    `;
                    
                    $('#orderDetailsContent').html(html);
                },
                error: function() {
                    $('#orderDetailsContent').html('<div class="alert alert-danger">Erreur lors du chargement des détails</div>');
                }
            });
        }
        
        // Delete order function
        function deleteOrder(orderId, orderNumber) {
            $('#deleteOrderNumber').text(orderNumber);
            $('#confirmDeleteBtn').attr('href', '?delete_order=' + orderId);
            $('#deleteConfirmModal').modal('show');
        }

        // ============================================================
// FORM VALIDATION & CONTROLS
// ============================================================

// Product name validation
function validateProductName() {
    const nameInput = document.getElementById('productName');
    const name = nameInput.value.trim();
    const previewDiv = document.getElementById('namePreview');
    const previewSpan = document.getElementById('previewName');
    
    if (name.length >= 3) {
        nameInput.classList.remove('is-invalid');
        nameInput.classList.add('is-valid');
        previewDiv.classList.add('active');
        previewSpan.textContent = name;
        updateFormSummary();
        return true;
    } else if (name.length > 0) {
        nameInput.classList.add('is-invalid');
        nameInput.classList.remove('is-valid');
        previewDiv.classList.remove('active');
        return false;
    } else {
        nameInput.classList.remove('is-invalid');
        nameInput.classList.remove('is-valid');
        previewDiv.classList.remove('active');
        return false;
    }
}

// Category validation
function validateCategory() {
    const categorySelect = document.getElementById('categorySelect');
    const selected = categorySelect.value;
    
    if (selected) {
        categorySelect.classList.remove('is-invalid');
        categorySelect.classList.add('is-valid');
        updateFormSummary();
        return true;
    } else {
        categorySelect.classList.add('is-invalid');
        categorySelect.classList.remove('is-valid');
        return false;
    }
}

// Price validation
function validatePrice() {
    const priceInput = document.getElementById('productPrice');
    const price = parseFloat(priceInput.value);
    
    if (price > 0) {
        priceInput.classList.remove('is-invalid');
        priceInput.classList.add('is-valid');
        updateFormSummary();
        return true;
    } else if (priceInput.value !== '') {
        priceInput.classList.add('is-invalid');
        priceInput.classList.remove('is-valid');
        return false;
    } else {
        priceInput.classList.remove('is-invalid');
        priceInput.classList.remove('is-valid');
        return false;
    }
}

// Stock validation
function validateStock() {
    const stockInput = document.getElementById('productStock');
    const stock = parseInt(stockInput.value);
    const stockStatus = document.getElementById('stockStatus');
    const stockWarning = document.getElementById('stockWarning');
    
    if (!isNaN(stock) && stock >= 0) {
        stockInput.classList.remove('is-invalid');
        stockInput.classList.add('is-valid');
        
        // Update stock status badge
        if (stock === 0) {
            stockStatus.innerHTML = '<span class="badge" style="background: #dc3545; color: white; padding: 5px 10px;">⚠️ Rupture de stock</span>';
            stockWarning.innerHTML = '<i class="fa fa-warning"></i> Ce produit sera marqué comme "Rupture"';
            stockWarning.style.color = '#dc3545';
        } else if (stock < 5) {
            stockStatus.innerHTML = '<span class="badge" style="background: #ffc107; color: #000; padding: 5px 10px;">⚠️ Stock faible (' + stock + ' unités)</span>';
            stockWarning.innerHTML = '<i class="fa fa-info-circle"></i> Stock faible, pensez à réapprovisionner';
            stockWarning.style.color = '#ffc107';
        } else {
            stockStatus.innerHTML = '<span class="badge" style="background: #28a745; color: white; padding: 5px 10px;">✓ En stock (' + stock + ' unités)</span>';
            stockWarning.innerHTML = '';
        }
        
        updateFormSummary();
        return true;
    } else {
        stockInput.classList.add('is-invalid');
        stockInput.classList.remove('is-valid');
        return false;
    }
}

// Adjust stock with buttons
function adjustStock(change) {
    const stockInput = document.getElementById('productStock');
    let currentValue = parseInt(stockInput.value);
    if (isNaN(currentValue)) currentValue = 0;
    const newValue = Math.max(0, currentValue + change);
    stockInput.value = newValue;
    validateStock();
}

// Character counter for description
function updateCharCount() {
    const description = document.getElementById('productDescription');
    const charCount = document.getElementById('charCount');
    const count = description.value.length;
    const max = 500;
    
    charCount.textContent = count + ' / ' + max + ' caractères';
    
    if (count > max * 0.9) {
        charCount.classList.add('danger');
        charCount.classList.remove('warning');
    } else if (count > max * 0.7) {
        charCount.classList.add('warning');
        charCount.classList.remove('danger');
    } else {
        charCount.classList.remove('warning', 'danger');
    }
}

// Image preview
function previewImage(input) {
    const previewContainer = document.getElementById('imagePreviewContainer');
    const preview = document.getElementById('imagePreview');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.src = e.target.result;
            previewContainer.style.display = 'block';
        }
        
        reader.readAsDataURL(input.files[0]);
        
        // Validate file size (5MB max)
        const fileSize = input.files[0].size / 1024 / 1024;
        if (fileSize > 5) {
            alert('L\'image est trop grande! Maximum 5MB.');
            input.value = '';
            previewContainer.style.display = 'none';
        }
    }
}

// Remove image
function removeImage() {
    const imageInput = document.getElementById('productImage');
    const previewContainer = document.getElementById('imagePreviewContainer');
    imageInput.value = '';
    previewContainer.style.display = 'none';
}

// Update form summary
function updateFormSummary() {
    const summaryDiv = document.getElementById('formSummary');
    const name = document.getElementById('productName').value.trim();
    const categorySelect = document.getElementById('categorySelect');
    const category = categorySelect.options[categorySelect.selectedIndex]?.text || '-';
    const price = document.getElementById('productPrice').value;
    const stock = document.getElementById('productStock').value;
    
    if (name && categorySelect.value && price > 0) {
        summaryDiv.style.display = 'block';
        document.getElementById('summaryName').textContent = name;
        document.getElementById('summaryCategory').textContent = category;
        document.getElementById('summaryPrice').textContent = parseFloat(price).toFixed(2);
        document.getElementById('summaryStock').textContent = stock;
    } else {
        summaryDiv.style.display = 'none';
    }
}

// Reset form
function resetForm() {
    if (confirm('Réinitialiser tous les champs du formulaire ?')) {
        document.getElementById('addProductForm').reset();
        document.getElementById('namePreview').classList.remove('active');
        document.getElementById('imagePreviewContainer').style.display = 'none';
        document.getElementById('formSummary').style.display = 'none';
        
        // Reset validation classes
        document.querySelectorAll('.form-control').forEach(el => {
            el.classList.remove('is-valid', 'is-invalid');
        });
        
        // Reset stock status
        document.getElementById('stockStatus').innerHTML = '<span class="badge" style="background: #28a745; color: white; padding: 5px 10px;">Disponible</span>';
        document.getElementById('stockWarning').innerHTML = '';
        document.getElementById('charCount').textContent = '0 / 500 caractères';
    }
}

// Form submission validation
document.getElementById('addProductForm').addEventListener('submit', function(e) {
    const isValidName = validateProductName();
    const isValidCategory = validateCategory();
    const isValidPrice = validatePrice();
    const isValidStock = validateStock();
    
    if (!isValidName || !isValidCategory || !isValidPrice || !isValidStock) {
        e.preventDefault();
        alert('Veuillez corriger les erreurs dans le formulaire avant de soumettre.');
        
        // Scroll to first error
        const firstError = document.querySelector('.is-invalid');
        if (firstError) {
            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            firstError.focus();
        }
    }
});

// Real-time validation on input
document.getElementById('productName').addEventListener('input', validateProductName);
document.getElementById('categorySelect').addEventListener('change', validateCategory);
document.getElementById('productPrice').addEventListener('input', validatePrice);
document.getElementById('productStock').addEventListener('input', validateStock);

// Auto-format price
document.getElementById('productPrice').addEventListener('blur', function() {
    let value = parseFloat(this.value);
    if (!isNaN(value)) {
        this.value = value.toFixed(3);
    }
});

// Initialize character counter
updateCharCount();

    </script>
</body>
</html>