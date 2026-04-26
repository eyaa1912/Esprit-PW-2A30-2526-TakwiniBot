<?php
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
//  ORDER MANAGEMENT - UPDATE STATUS
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_order_status'])) {
    $order_id = intval($_POST['order_id']);
    $new_status = $_POST['order_status'];
    $valid_statuses = ['en attente', 'validée', 'expédiée', 'livrée', 'annulée'];
    
    if (in_array($new_status, $valid_statuses)) {
        $stmt = $pdo->prepare("UPDATE commande SET statut = ? WHERE id = ?");
        $stmt->execute([$new_status, $order_id]);
        $_SESSION['admin_success'] = "Commande #$order_id mise à jour: $new_status";
    }
    header("Location: gestion-produits.php");
    exit();
}

// ============================================================
//  ORDER MANAGEMENT - DELETE ORDER
// ============================================================
if (isset($_GET['delete_order']) && is_numeric($_GET['delete_order'])) {
    $order_id = intval($_GET['delete_order']);
    
    try {
        $pdo->beginTransaction();
        
        // Get all items to restore stock
        $stmt = $pdo->prepare("SELECT produit_id, quantite FROM ligne_commande WHERE commande_id = ?");
        $stmt->execute([$order_id]);
        $items = $stmt->fetchAll();
        
        // Restore stock
        foreach ($items as $item) {
            $stmt = $pdo->prepare("UPDATE produit SET stock = stock + ? WHERE id = ?");
            $stmt->execute([$item['quantite'], $item['produit_id']]);
        }
        
        // Delete order items
        $stmt = $pdo->prepare("DELETE FROM ligne_commande WHERE commande_id = ?");
        $stmt->execute([$order_id]);
        
        // Delete order
        $stmt = $pdo->prepare("DELETE FROM commande WHERE id = ?");
        $stmt->execute([$order_id]);
        
        $pdo->commit();
        $_SESSION['admin_success'] = "Commande #$order_id supprimée avec succès! Stock restauré.";
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['admin_error'] = "Erreur lors de la suppression: " . $e->getMessage();
    }
    header("Location: gestion-produits.php");
    exit();
}

// ============================================================
//  HANDLE DELETE PRODUCT
// ============================================================
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $pdo->prepare("DELETE FROM produit WHERE id = ?")->execute([$id]);
    header("Location: gestion-produits.php?deleted=1");
    exit;
}

// ============================================================
//  HANDLE EDIT PRODUCT
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id           = (int) ($_POST['id'] ?? 0);
    $nom          = trim($_POST['nom'] ?? '');
    $categorie_id = (int) ($_POST['categorie_id'] ?? 0);
    $prix         = (float) ($_POST['prix'] ?? 0);
    $stock        = (int) ($_POST['stock'] ?? 0);
    $description  = trim($_POST['description'] ?? '');
    $imageName    = $_POST['current_image'] ?? null;

    if ($id && $nom && $categorie_id && $prix >= 0) {

        if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $file    = $_FILES['image'];
            $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            $maxSize = 2 * 1024 * 1024;

            if (!in_array($file['type'], $allowed)) {
                $error = "Format d'image non autorisé. (jpg, png, webp, gif)";
            } elseif ($file['size'] > $maxSize) {
                $error = "L'image ne doit pas dépasser 2MB.";
            } else {
                $uploadDir = 'C:/xampp/htdocs/takwini/app/view/back/uploads/produits/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                $ext       = pathinfo($file['name'], PATHINFO_EXTENSION);
                $newName   = uniqid('prod_', true) . '.' . $ext;
                if (move_uploaded_file($file['tmp_name'], $uploadDir . $newName)) {
                    if (!empty($imageName) && file_exists($uploadDir . $imageName)) {
                        unlink($uploadDir . $imageName);
                    }
                    $imageName = $newName;
                } else {
                    $error = "Échec lors de l'enregistrement de l'image.";
                }
            }
        }

        if (!$error) {
            $stmt = $pdo->prepare("UPDATE produit SET categorie_id=?, nom=?, prix=?, stock=?, description=?, image=? WHERE id=?");
            if ($stmt->execute([$categorie_id, $nom, $prix, $stock, $description, $imageName, $id])) {
                header("Location: gestion-produits.php?updated=1");
                exit;
            }
        }
    }
}

// ============================================================
//  HANDLE ADD PRODUCT
// ============================================================
$success = '';
$error   = '';

if (isset($_GET['deleted'])) $success = "Produit supprimé avec succès !";
if (isset($_GET['updated'])) $success = "Produit mis à jour avec succès !";
if (isset($_SESSION['admin_success'])) {
    $success = $_SESSION['admin_success'];
    unset($_SESSION['admin_success']);
}
if (isset($_SESSION['admin_error'])) {
    $error = $_SESSION['admin_error'];
    unset($_SESSION['admin_error']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $nom          = trim($_POST['nom'] ?? '');
    $categorie_id = (int) ($_POST['categorie_id'] ?? 0);
    $prix         = (float) ($_POST['prix'] ?? 0);
    $stock        = (int) ($_POST['stock'] ?? 0);
    $description  = trim($_POST['description'] ?? '');

    if ($nom && $categorie_id && $prix >= 0) {

        $imageName = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $file    = $_FILES['image'];
            $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            $maxSize = 2 * 1024 * 1024;

            if (!in_array($file['type'], $allowed)) {
                $error = "Format d'image non autorisé. (jpg, png, webp, gif)";
            } elseif ($file['size'] > $maxSize) {
                $error = "L'image ne doit pas dépasser 2MB.";
            } else {
                $uploadDir = 'C:/xampp/htdocs/takwini/app/view/back/uploads/produits/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                $ext       = pathinfo($file['name'], PATHINFO_EXTENSION);
                $imageName = uniqid('prod_', true) . '.' . $ext;
                if (!move_uploaded_file($file['tmp_name'], $uploadDir . $imageName)) {
                    $error     = "Échec lors de l'enregistrement de l'image.";
                    $imageName = null;
                }
            }
        }

        if (!$error) {
            $stmt = $pdo->prepare("INSERT INTO produit (categorie_id, nom, prix, stock, description, image) VALUES (?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$categorie_id, $nom, $prix, $stock, $description, $imageName])) {
                $success = "Produit ajouté avec succès !";
            } else {
                $error = "Erreur lors de l'ajout.";
            }
        }

    } else {
        $error = "Veuillez remplir tous les champs obligatoires.";
    }
}

// ============================================================
//  HANDLE ADD CATEGORIE (AJAX)
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_categorie') {
    header('Content-Type: application/json');
    $nom = trim($_POST['nom'] ?? '');
    if ($nom) {
        $stmt = $pdo->prepare("INSERT INTO categorie (nom) VALUES (?)");
        $stmt->execute([$nom]);
        echo json_encode(['success' => true, 'id' => $pdo->lastInsertId(), 'nom' => $nom]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Nom vide']);
    }
    exit;
}

// ============================================================
//  GET CATEGORIES (AJAX)
// ============================================================
if (isset($_GET['get_categories'])) {
    header('Content-Type: application/json');
    $cats = $pdo->query("SELECT * FROM categorie ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($cats);
    exit;
}

// ============================================================
//  GET ORDER DETAILS (AJAX)
// ============================================================
if (isset($_GET['get_order_details']) && is_numeric($_GET['get_order_details'])) {
    header('Content-Type: application/json');
    $order_id = intval($_GET['get_order_details']);
    
    $stmt = $pdo->prepare("SELECT * FROM commande WHERE id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();
    
    if ($order) {
        $stmt = $pdo->prepare("
            SELECT lc.*, p.nom as product_name 
            FROM ligne_commande lc 
            JOIN produit p ON lc.produit_id = p.id 
            WHERE lc.commande_id = ?
        ");
        $stmt->execute([$order_id]);
        $items = $stmt->fetchAll();
        
        echo json_encode(['order' => $order, 'items' => $items]);
        exit();
    }
}

// ============================================================
//  LOAD DATA
// ============================================================
$categories   = $pdo->query("SELECT * FROM categorie ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);
$produits     = $pdo->query("
    SELECT p.*, c.nom AS categorie_nom
    FROM produit p
    LEFT JOIN categorie c ON p.categorie_id = c.id
    ORDER BY p.id DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Load orders for management
$orders = $pdo->query("
    SELECT c.*, 
    (SELECT COUNT(*) FROM ligne_commande WHERE commande_id = c.id) as nb_products
    FROM commande c 
    ORDER BY c.date_commande DESC
")->fetchAll();

$totalProduits = count($produits);
$rupture       = count(array_filter($produits, fn($p) => $p['stock'] == 0));
$faible        = count(array_filter($produits, fn($p) => $p['stock'] > 0 && $p['stock'] < 5));
$disponible    = count(array_filter($produits, fn($p) => $p['stock'] >= 5));
?>
<!doctype html>
<html
  lang="fr"
  class="layout-menu-fixed layout-compact"
  data-assets-path="../assets/"
  data-template="vertical-menu-template-free">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>Produits | Tableaux</title>
    <meta name="description" content="" />
    <link rel="icon" type="image/x-icon" href="../assets/img/favicon/favicon.ico" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="../assets/vendor/fonts/iconify-icons.css" />
    <link rel="stylesheet" href="../assets/vendor/css/core.css" />
    <link rel="stylesheet" href="../assets/css/demo.css" />
    <link rel="stylesheet" href="../assets/css/dark-mode.css" />
    <link rel="stylesheet" href="../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
    <link rel="stylesheet" href="../assets/vendor/libs/apex-charts/apex-charts.css" />
    <script src="../assets/vendor/js/helpers.js"></script>
    <script src="../assets/js/config.js"></script>
    <style>
        /* Order management styles */
        .nav-tabs .nav-link {
            font-weight: 500;
            padding: 10px 20px;
        }
        .nav-tabs .nav-link.active {
            border-bottom: 2px solid #696cff;
            color: #696cff;
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
        .order-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: all 0.3s;
        }
        .order-card:hover {
            box-shadow: 0 5px 20px rgba(0,0,0,0.12);
        }
    </style>
  </head>


  <body>
    <div class="layout-wrapper layout-content-navbar">
      <div class="layout-container">

        <!-- ========== SIDEBAR MENU ========== -->
        <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
          <div class="app-brand demo">
            <a href="index.html" class="app-brand-link">
              <span class="app-brand-logo demo">
                <img src="../assets/img/favicon/tak.png" alt="Takwinibot" style="width:56px;height:56px;object-fit:contain;">
              </span>
            </a>
            <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
              <i class="bx bx-chevron-left d-block d-xl-none align-middle"></i>
            </a>
          </div>
          <div class="menu-divider mt-0"></div>
          <div class="menu-inner-shadow"></div>
          <ul class="menu-inner py-1">
            <li class="menu-item active open">
              <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-home-smile"></i>
                <div class="text-truncate">Tableau de bord</div>
              </a>
              <ul class="menu-sub">
                <li class="menu-item">
                  <a href="index.html" class="menu-link"><div class="text-truncate">Accueil</div></a>
                </li>
                <li class="menu-item">
                  <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <div class="text-truncate">Formations</div>
                  </a>
                  <ul class="menu-sub">
                    <li class="menu-item"><a href="gestion-formations.html" class="menu-link"><div class="text-truncate">Vue d'ensemble</div></a></li>
                    <li class="menu-item"><a href="gestion-formations.html#sessions" class="menu-link"><div class="text-truncate">Nos formations</div></a></li>
                    <li class="menu-item"><a href="gestion-inscriptions.html" class="menu-link"><div class="text-truncate">Inscriptions</div></a></li>
                    <li class="menu-item"><a href="gestion-certificats.html" class="menu-link"><div class="text-truncate">Certificats</div></a></li>
                  </ul>
                </li>
                <li class="menu-item">
                  <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <div class="text-truncate">Offres</div>
                  </a>
                  <ul class="menu-sub">
                    <li class="menu-item"><a href="gestion-offres.html" class="menu-link"><div class="text-truncate">Liste des offres</div></a></li>
                    <li class="menu-item"><a href="gestion-contrats.html" class="menu-link"><div class="text-truncate">Contrats</div></a></li>
                  </ul>
                </li>
                <li class="menu-item"><a href="gestion-reclamations.html" class="menu-link"><div class="text-truncate">Réclamations</div></a></li>
                <li class="menu-item"><a href="gestion-entretiens.html" class="menu-link"><div class="text-truncate">Entretiens</div></a></li>
                <li class="menu-item active">
                  <a href="gestion-produits.php" class="menu-link"><div class="text-truncate">Produits & Commandes</div></a>
                </li>
                <li class="menu-item">
                  <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <div class="text-truncate">Utilisateurs</div>
                  </a>
                  <ul class="menu-sub">
                    <li class="menu-item"><a href="gestion-utilisateurs.html" class="menu-link"><div class="text-truncate">Liste des utilisateurs</div></a></li>
                    <li class="menu-item"><a href="pages-account-settings-account.html" class="menu-link"><div class="text-truncate">Profil</div></a></li>
                  </ul>
                </li>
              </ul>
            </li>
            <li class="menu-header small text-uppercase">
              <span class="menu-header-text">Applications</span>
            </li>
            <li class="menu-item"><a href="email-boite.html" class="menu-link"><i class="menu-icon tf-icons bx bx-envelope"></i><div class="text-truncate">Email</div></a></li>
            <li class="menu-item"><a href="app-chat-local.html" class="menu-link"><i class="menu-icon tf-icons bx bx-chat"></i><div class="text-truncate">Discuter</div></a></li>
            <li class="menu-item"><a href="app-calendrier-local.html" class="menu-link"><i class="menu-icon tf-icons bx bx-calendar"></i><div class="text-truncate">Calendrier</div></a></li>
            <li class="menu-item">
              <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-lock-open-alt"></i>
                <div class="text-truncate">Authentification</div>
              </a>
              <ul class="menu-sub">
                <li class="menu-item"><a href="auth-login-basic.html" class="menu-link" target="_blank"><div class="text-truncate">Connexion</div></a></li>
                <li class="menu-item"><a href="auth-register-basic.html" class="menu-link" target="_blank"><div class="text-truncate">Inscription</div></a></li>
                <li class="menu-item"><a href="auth-forgot-password-basic.html" class="menu-link" target="_blank"><div class="text-truncate">Mot de passe oublié</div></a></li>
              </ul>
            </li>
          </ul>
        </aside>
        <!-- ========== / SIDEBAR MENU ========== -->

        <div class="layout-page">

          <!-- ========== NAVBAR ========== -->
          <nav class="layout-navbar container-xxl navbar-detached navbar navbar-expand-xl align-items-center bg-navbar-theme" id="layout-navbar">
            <div class="layout-menu-toggle navbar-nav align-items-xl-center me-4 me-xl-0 d-xl-none">
              <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)">
                <i class="icon-base bx bx-menu icon-md"></i>
              </a>
            </div>
            <div class="navbar-nav-right d-flex align-items-center justify-content-end" id="navbar-collapse">
              <div class="navbar-nav align-items-center me-auto">
                <div class="nav-item d-flex align-items-center">
                  <span class="w-px-22 h-px-22"><i class="icon-base bx bx-search icon-md"></i></span>
                  <input type="text" class="form-control border-0 shadow-none ps-1 ps-sm-2 d-md-block d-none" placeholder="Search..." aria-label="Search..." />
                </div>
              </div>
              <ul class="navbar-nav flex-row align-items-center ms-md-auto">
                <li class="nav-item me-2 me-xl-1">
                  <a class="nav-link" href="javascript:void(0);" id="app-theme-toggle" aria-label="Basculer thème">
                    <i class="icon-base bx bx-moon icon-md" id="app-theme-toggle-icon"></i>
                  </a>
                </li>
                <li class="nav-item navbar-dropdown dropdown-user dropdown">
                  <a class="nav-link dropdown-toggle hide-arrow p-0" href="javascript:void(0);" data-bs-toggle="dropdown">
                    <div class="avatar avatar-online">
                      <img src="../assets/img/avatars/1.png" alt class="w-px-40 h-auto rounded-circle" />
                    </div>
                  </a>
                  <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                      <a class="dropdown-item" href="pages-account-settings-account.html">
                        <div class="d-flex">
                          <div class="flex-shrink-0 me-3">
                            <div class="avatar avatar-online">
                              <img src="../assets/img/avatars/1.png" alt class="w-px-40 h-auto rounded-circle" />
                            </div>
                          </div>
                          <div class="flex-grow-1">
                            <h6 class="mb-0">Admin</h6>
                            <small class="text-body-secondary">Administrateur</small>
                          </div>
                        </div>
                      </a>
                    </li>
                    <li><div class="dropdown-divider my-1"></div></li>
                    <li>
                      <a class="dropdown-item" href="auth-login-basic.html">
                        <i class="icon-base bx bx-power-off icon-md me-3"></i><span>Déconnexion</span>
                      </a>
                    </li>
                  </ul>
                </li>
              </ul>
            </div>
          </nav>
          <!-- ========== / NAVBAR ========== -->

          <!-- ========== CONTENT ========== -->
<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">

    <h4 class="fw-bold py-3 mb-2">Gestion des Produits & Commandes</h4>
    <p class="text-muted mb-4">Catalogue des produits et gestion des commandes clients.</p>

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-4" role="tablist">
      <li class="nav-item">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#products-tab" type="button" role="tab">
          <i class="bx bx-package me-1"></i> Produits
        </button>
      </li>
      <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#orders-tab" type="button" role="tab">
          <i class="bx bx-shopping-bag me-1"></i> Commandes
          <?php if(count($orders) > 0): ?>
            <span class="badge bg-primary rounded-pill ms-1"><?= count($orders) ?></span>
          <?php endif; ?>
        </button>
      </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content">
      
      <!-- ========== PRODUCTS TAB ========== -->
      <div class="tab-pane fade show active" id="products-tab" role="tabpanel">
        <!-- Alerts -->
        <?php if ($success): ?>
          <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bx bx-check-circle me-2"></i><?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        <?php endif; ?>
        <?php if ($error): ?>
          <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bx bx-error-circle me-2"></i><?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        <?php endif; ?>

        <!-- Stats Cards -->
        <div class="row g-6 mb-6">
          <div class="col-sm-6 col-xl-3">
            <div class="card">
              <div class="card-body">
                <div class="d-flex align-items-start justify-content-between">
                  <div class="content-left">
                    <span>Total produits</span>
                    <div class="d-flex align-items-end mt-2">
                      <h4 class="mb-0 me-2"><?= $totalProduits ?></h4>
                    </div>
                    <p class="mb-0">Catalogue complet</p>
                  </div>
                  <div class="avatar">
                    <span class="avatar-initial rounded bg-label-primary"><i class="bx bx-cart bx-sm"></i></span>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-sm-6 col-xl-3">
            <div class="card">
              <div class="card-body">
                <div class="d-flex align-items-start justify-content-between">
                  <div class="content-left">
                    <span>Disponibles</span>
                    <div class="d-flex align-items-end mt-2">
                      <h4 class="mb-0 me-2"><?= $disponible ?></h4>
                    </div>
                    <p class="mb-0">En stock</p>
                  </div>
                  <div class="avatar">
                    <span class="avatar-initial rounded bg-label-success"><i class="bx bx-check-circle bx-sm"></i></span>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-sm-6 col-xl-3">
            <div class="card">
              <div class="card-body">
                <div class="d-flex align-items-start justify-content-between">
                  <div class="content-left">
                    <span>Stock faible</span>
                    <div class="d-flex align-items-end mt-2">
                      <h4 class="mb-0 me-2"><?= $faible ?></h4>
                    </div>
                    <p class="mb-0">Moins de 5</p>
                  </div>
                  <div class="avatar">
                    <span class="avatar-initial rounded bg-label-warning"><i class="bx bx-pause-circle bx-sm"></i></span>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-sm-6 col-xl-3">
            <div class="card">
              <div class="card-body">
                <div class="d-flex align-items-start justify-content-between">
                  <div class="content-left">
                    <span>Rupture</span>
                    <div class="d-flex align-items-end mt-2">
                      <h4 class="mb-0 me-2"><?= $rupture ?></h4>
                    </div>
                    <p class="mb-0">Stock épuisé</p>
                  </div>
                  <div class="avatar">
                    <span class="avatar-initial rounded bg-label-danger"><i class="bx bx-x-circle bx-sm"></i></span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Products Table -->
        <div class="card" id="produits">
          <div class="card-header border-bottom">
            <h5 class="card-title mb-0">Liste des produits</h5>
            <div class="d-flex justify-content-end mt-3">
              <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#addProduitModal">
                <i class="bx bx-plus me-1 bx-sm"></i> Ajouter Produit
              </button>
            </div>
          </div>
          <div class="card-datatable table-responsive">
            <table class="table border-top dataTable">
              <thead>
                <tr>
                  <th>#</th>
                  <th>IMAGE</th>
                  <th>PRODUIT</th>
                  <th>CATÉGORIE</th>
                  <th>PRIX</th>
                  <th>STOCK</th>
                  <th>STATUT</th>
                  <th>ACTIONS</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($produits)): ?>
                  <tr>
                    <td colspan="8" class="text-center text-muted py-5">
                      <i class="bx bx-package bx-lg d-block mb-2"></i>
                      Aucun produit trouvé. Cliquez sur "Ajouter Produit" pour commencer.
                    </td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($produits as $p): ?>
                    <tr>
                      <td><?= $p['id'] ?></td>
                      <td>
                        <?php if (!empty($p['image'])): ?>
                          <img src="../uploads/produits/<?= htmlspecialchars($p['image']) ?>"
                               style="width:48px; height:48px; object-fit:cover; border-radius:6px; border:1px solid #ddd;">
                        <?php else: ?>
                          <span class="avatar-initial rounded bg-label-secondary" style="width:48px; height:48px; display:flex; align-items:center; justify-content:center; font-size:20px;">
                            📦
                          </span>
                        <?php endif; ?>
                       </div>
                      <td>
                        <div class="d-flex justify-content-start align-items-center">
                          <div class="d-flex flex-column">
                            <span class="fw-medium text-heading"><?= htmlspecialchars($p['nom']) ?></span>
                            <small class="text-muted"><?= htmlspecialchars(substr($p['description'] ?? '', 0, 40)) ?><?= strlen($p['description'] ?? '') > 40 ? '...' : '' ?></small>
                          </div>
                        </div>
                       </div>
                      <td><?= htmlspecialchars($p['categorie_nom'] ?? 'N/A') ?></td>
                      <td><?= number_format((float)$p['prix'], 2) ?> €</div>
                      <td><?= (int)$p['stock'] ?> </div>
                      <td>
                        <?php if ($p['stock'] == 0): ?>
                          <span class="badge bg-label-danger text-capitalize">Rupture</span>
                        <?php elseif ($p['stock'] < 5): ?>
                          <span class="badge bg-label-warning text-capitalize">Faible</span>
                        <?php else: ?>
                          <span class="badge bg-label-success text-capitalize">Disponible</span>
                        <?php endif; ?>
                       </div>
                      <td>
                        <div class="d-flex align-items-center">
                          <a href="javascript:;"
                             class="text-body me-2"
                             title="Modifier"
                             onclick="openEditModal(
                               <?= $p['id'] ?>,
                               '<?= addslashes(htmlspecialchars($p['nom'])) ?>',
                               <?= (int)$p['categorie_id'] ?>,
                               <?= (float)$p['prix'] ?>,
                               <?= (int)$p['stock'] ?>,
                               '<?= addslashes(htmlspecialchars($p['description'] ?? '')) ?>',
                               '<?= htmlspecialchars($p['image'] ?? '') ?>')">
                            <i class="bx bx-edit bx-sm"></i>
                          </a>
                          <a href="javascript:;" class="text-body me-2" title="Supprimer" onclick="openDeleteModal(<?= $p['id'] ?>)">
                            <i class="bx bx-trash bx-sm"></i>
                          </a>
                        </div>
                       </div>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>

            <div class="row mx-2 mt-3 mb-3">
              <div class="col-sm-12 d-flex align-items-center">
                <div class="dataTables_info text-muted small">
                  <?= $totalProduits ?> produit(s) au total
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- ========== / PRODUCTS TAB ========== -->

      <!-- ========== ORDERS TAB ========== -->
      <div class="tab-pane fade" id="orders-tab" role="tabpanel">
        <div class="card">
          <div class="card-header border-bottom">
            <h5 class="card-title mb-0">Gestion des Commandes</h5>
            <p class="text-muted mt-1 mb-0">Suivez et gérez les commandes des clients</p>
          </div>
          <div class="card-body">
            <?php if (empty($orders)): ?>
              <div class="text-center py-5">
                <i class="bx bx-shopping-bag bx-lg text-muted mb-3"></i>
                <h5>Aucune commande pour le moment</h5>
                <p class="text-muted">Les commandes des clients apparaîtront ici.</p>
              </div>
            <?php else: ?>
              <div class="table-responsive">
                <table class="table table-hover">
                  <thead>
                    <tr>
                      <th># Commande</th>
                      <th>Date</th>
                      <th>Client</th>
                      <th>Total</th>
                      <th>Articles</th>
                      <th>Statut</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($orders as $order): ?>
                      <tr>
                        <td><strong>#<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></strong></td>
                        <td><?= date('d/m/Y H:i', strtotime($order['date_commande'])) ?></td>
                        <td>
                          <?= htmlspecialchars($order['nom_client']) ?><br>
                          <small class="text-muted"><?= htmlspecialchars($order['email_client']) ?></small>
                        </td>
                        <td><strong><?= number_format($order['total'], 2) ?> DT</strong></td>
                        <td><?= $order['nb_products'] ?></td>
                        <td>
                          <form method="POST" style="display: inline-block;">
    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
    <select name="order_status" class="form-select form-select-sm" style="width: auto; display: inline-block; width: 130px;" onchange="this.form.submit()">
        <option value="en attente" <?= $order['statut'] == 'en attente' ? 'selected' : '' ?>>En attente</option>
        <option value="validée" <?= $order['statut'] == 'validée' ? 'selected' : '' ?>>Validée</option>
        <option value="expédiée" <?= $order['statut'] == 'expédiée' ? 'selected' : '' ?>>Expédiée</option>
        <option value="livrée" <?= $order['statut'] == 'livrée' ? 'selected' : '' ?>>Livrée</option>
        <option value="annulée" <?= $order['statut'] == 'annulée' ? 'selected' : '' ?>>Annulée</option>
    </select>
    <input type="hidden" name="update_order_status" value="1">
</form>
                        </td>
                        <td>
                          <button class="btn btn-sm btn-info" onclick="viewOrderDetails(<?= $order['id'] ?>)">
                            <i class="bx bx-show"></i> Détails
                          </button>
                          <a href="?delete_order=<?= $order['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer cette commande ? Le stock sera restauré.')">
                            <i class="bx bx-trash"></i>
                          </a>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <!-- ========== / ORDERS TAB ========== -->

    </div>
    <!-- / Tab Content -->

    <!-- ===== MODAL: ADD PRODUIT ===== -->
    <div class="modal fade" id="addProduitModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Ajouter un produit</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form method="POST" action="gestion-produits.php" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add">
            <div class="modal-body">
              <div class="row g-3">
                <div class="col-12">
                  <label class="form-label">Nom du produit <span class="text-danger">*</span></label>
                  <input type="text" name="nom" class="form-control" placeholder="Ex: Clavier braille" required />
                </div>
                <div class="col-md-6">
                  <label class="form-label">Catégorie <span class="text-danger">*</span></label>
                  <select name="categorie_id" class="form-select" required>
                    <option value="">-- Choisir --</option>
                    <?php foreach ($categories as $cat): ?>
                      <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nom']) ?></option>
                    <?php endforeach; ?>
                  </select>
                  <div class="mt-2 d-flex gap-2">
                    <input type="text" id="new_categorie_nom" class="form-control form-control-sm" placeholder="Nouvelle catégorie..." />
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="addCategorie()">
                      <i class="bx bx-plus"></i>
                    </button>
                  </div>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Prix (€) <span class="text-danger">*</span></label>
                  <input type="number" name="prix" class="form-control" placeholder="0.00" step="0.01" min="0" required />
                </div>
                <div class="col-md-6">
                  <label class="form-label">Stock</label>
                  <input type="number" name="stock" class="form-control" placeholder="0" min="0" value="0" />
                </div>
                <div class="col-12">
                  <label class="form-label">Description</label>
                  <textarea name="description" class="form-control" rows="3" placeholder="Description du produit..."></textarea>
                </div>
                <div class="col-12">
                  <label class="form-label">Image du produit</label>
                  <input type="file" name="image" id="add_image" class="form-control" accept="image/*" />
                  <div class="mt-2 text-center">
                    <img id="add_preview" src="#" alt="Aperçu"
                         style="display:none; width:120px; height:120px; object-fit:cover; border-radius:8px; border:1px solid #ddd;">
                  </div>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
              <button type="submit" class="btn btn-primary">Enregistrer</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <!-- ===== / MODAL: ADD PRODUIT ===== -->

    <!-- ===== MODAL: EDIT PRODUIT ===== -->
    <div class="modal fade" id="editProduitModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Modifier le produit</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form method="POST" action="gestion-produits.php" enctype="multipart/form-data">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="edit_id">
            <input type="hidden" name="current_image" id="edit_current_image">
            <div class="modal-body">
              <div class="row g-3">
                <div class="col-12">
                  <label class="form-label">Nom du produit <span class="text-danger">*</span></label>
                  <input type="text" name="nom" id="edit_nom" class="form-control" required />
                </div>
                <div class="col-md-6">
                  <label class="form-label">Catégorie <span class="text-danger">*</span></label>
                  <select name="categorie_id" id="edit_categorie_id" class="form-select" required>
                    <option value="">-- Choisir --</option>
                    <?php foreach ($categories as $cat): ?>
                      <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nom']) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Prix (€) <span class="text-danger">*</span></label>
                  <input type="number" name="prix" id="edit_prix" class="form-control" step="0.01" min="0" required />
                </div>
                <div class="col-md-6">
                  <label class="form-label">Stock</label>
                  <input type="number" name="stock" id="edit_stock" class="form-control" min="0" />
                </div>
                <div class="col-12">
                  <label class="form-label">Description</label>
                  <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
                </div>
                <div class="col-12">
                  <label class="form-label">Image du produit</label>
                  <input type="file" name="image" id="edit_image" class="form-control" accept="image/*" />
                  <div class="mt-2 text-center">
                    <img id="edit_preview" src="#" alt="Aperçu"
                         style="display:none; width:120px; height:120px; object-fit:cover; border-radius:8px; border:1px solid #ddd;">
                  </div>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
              <button type="submit" class="btn btn-primary">Mettre à jour</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <!-- ===== / MODAL: EDIT PRODUIT ===== -->

    <!-- ===== MODAL: DELETE CONFIRM PRODUCT ===== -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
        <div class="modal-content">
          <div class="modal-header border-0 pb-0">
            <h5 class="modal-title text-danger"><i class="bx bx-error-circle me-2"></i>Confirmer la suppression</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body text-center py-4">
            <i class="bx bx-trash bx-lg text-danger mb-3 d-block"></i>
            <p class="mb-0">Êtes-vous sûr de vouloir supprimer ce produit ?</p>
            <small class="text-muted">Cette action est irréversible.</small>
          </div>
          <div class="modal-footer border-0 pt-0">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
            <a id="confirmDeleteBtn" href="#" class="btn btn-danger">Supprimer</a>
          </div>
        </div>
      </div>
    </div>
    <!-- ===== / MODAL: DELETE CONFIRM PRODUCT ===== -->

    <!-- ===== MODAL: ORDER DETAILS ===== -->
    <div class="modal fade" id="orderDetailsModal" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header" style="background: #696cff; color: white;">
            <h5 class="modal-title">Détails de la commande</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body" id="orderDetailsContent">
            <div class="text-center py-4">
              <i class="bx bx-loader-alt bx-spin bx-lg"></i> Chargement...
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
          </div>
        </div>
      </div>
    </div>
    <!-- ===== / MODAL: ORDER DETAILS ===== -->

  </div>
  </div>
  <!-- /container-xxl -->

  <!-- Footer -->
  <footer class="content-footer footer bg-footer-theme">
    <div class="container-xxl">
      <div class="footer-container d-flex align-items-center justify-content-between py-4 flex-md-row flex-column">
        <div class="mb-2 mb-md-0">
          &copy; <?= date('Y') ?> Takwini
        </div>
      </div>
    </div>
  </footer>
  <!-- / Footer -->

  <div class="content-backdrop fade"></div>
</div>
<!-- ========== / CONTENT ========== -->

</div>
</div>
<div class="layout-overlay layout-menu-toggle"></div>
</div>

<!-- Core JS -->
<script src="../assets/vendor/libs/jquery/jquery.js"></script>
<script src="../assets/vendor/libs/popper/popper.js"></script>
<script src="../assets/vendor/js/bootstrap.js"></script>
<script src="../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
<script src="../assets/vendor/js/menu.js"></script>
<script src="../assets/js/main.js"></script>
<script src="../assets/js/navbar-extras.js"></script>

<script>
  // Add modal image preview
  document.getElementById('add_image').addEventListener('change', function () {
    const preview = document.getElementById('add_preview');
    if (this.files && this.files[0]) {
      preview.src = URL.createObjectURL(this.files[0]);
      preview.style.display = 'block';
    }
  });

  // Edit modal image preview
  document.getElementById('edit_image').addEventListener('change', function () {
    const preview = document.getElementById('edit_preview');
    if (this.files && this.files[0]) {
      preview.src = URL.createObjectURL(this.files[0]);
      preview.style.display = 'block';
    }
  });

  // Open edit modal and populate all fields including image
  function openEditModal(id, nom, categorie_id, prix, stock, description, image) {
    document.getElementById('edit_id').value            = id;
    document.getElementById('edit_nom').value           = nom;
    document.getElementById('edit_prix').value          = prix;
    document.getElementById('edit_stock').value         = stock;
    document.getElementById('edit_description').value   = description;
    document.getElementById('edit_current_image').value = image;

    var sel = document.getElementById('edit_categorie_id');
    for (var i = 0; i < sel.options.length; i++) {
      if (parseInt(sel.options[i].value) === parseInt(categorie_id)) {
        sel.options[i].selected = true;
        break;
      }
    }

    var preview = document.getElementById('edit_preview');
    if (image) {
      preview.src = '../uploads/produits/' + image;
      preview.style.display = 'block';
    } else {
      preview.src = '#';
      preview.style.display = 'none';
    }

    var modal = new bootstrap.Modal(document.getElementById('editProduitModal'));
    modal.show();
  }

  function openDeleteModal(id) {
    document.getElementById('confirmDeleteBtn').href = 'gestion-produits.php?delete=' + id;
    var modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
  }

  function addCategorie() {
    const nom = document.getElementById('new_categorie_nom').value.trim();
    if (!nom) return;

    fetch('gestion-produits.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=add_categorie&nom=' + encodeURIComponent(nom)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const option = new Option(data.nom, data.id);
            document.querySelector('#addProduitModal select[name="categorie_id"]').add(option.cloneNode(true));
            document.getElementById('edit_categorie_id').add(option);
            document.getElementById('new_categorie_nom').value = '';
        } else {
            alert('Erreur: ' + data.error);
        }
    });
  }

  // View order details
  function viewOrderDetails(orderId) {
    $('#orderDetailsModal').modal('show');
    $('#orderDetailsContent').html('<div class="text-center py-4"><i class="bx bx-loader-alt bx-spin bx-lg"></i> Chargement...</div>');
    
    $.ajax({
        url: window.location.href,
        type: 'GET',
        data: { get_order_details: orderId },
        dataType: 'json',
        success: function(data) {
            var html = `
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p><strong>Commande #${String(orderId).padStart(6, '0')}</strong></p>
                        <p><strong>Date:</strong> ${new Date(data.order.date_commande).toLocaleString()}</p>
                        <p><strong>Statut:</strong> <span class="status-badge status-${data.order.statut.replace(/ /g, '-')}">${data.order.statut}</span></p>
                        <p><strong>Total:</strong> <strong style="color: #696cff;">${parseFloat(data.order.total).toFixed(2)} DT</strong></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Client:</strong> ${data.order.nom_client}</p>
                        <p><strong>Email:</strong> ${data.order.email_client}</p>
                        <p><strong>Téléphone:</strong> ${data.order.telephone_client}</p>
                    </div>
                </div>
                <h6>Articles commandés:</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead style="background: #696cff; color: white;">
                            <tr><th>Produit</th><th>Quantité</th><th>Prix unit.</th><th>Total</th></tr>
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
                <h6>Adresse de livraison:</h6>
                <div style="background: #f8f9fa; padding: 15px; border-radius: 8px;">
                    ${data.order.adresse_livraison.replace(/\n/g, '<br>')}
                </div>
            `;
            
            $('#orderDetailsContent').html(html);
        },
        error: function() {
            $('#orderDetailsContent').html('<div class="alert alert-danger">Erreur lors du chargement des détails</div>');
        }
    });
  }

  // Reload categories from DB every time the add modal opens
  document.getElementById('addProduitModal').addEventListener('show.bs.modal', function () {
    fetch('gestion-produits.php?get_categories=1')
    .then(res => res.json())
    .then(categories => {
        const addSelect  = document.querySelector('#addProduitModal select[name="categorie_id"]');
        const editSelect = document.getElementById('edit_categorie_id');

        [addSelect, editSelect].forEach(sel => {
            const currentVal = sel.value;
            sel.innerHTML = '<option value="">-- Choisir --</option>';
            categories.forEach(cat => {
                const opt = new Option(cat.nom, cat.id);
                sel.add(opt);
            });
            sel.value = currentVal;
        });
    });
  });
</script>

</body>
</html>