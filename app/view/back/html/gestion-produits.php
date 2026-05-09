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
//  ORDER STATUS UPDATE  — now includes 'en attente paiement'
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_order_status'])) {
    $order_id   = intval($_POST['order_id']);
    $new_status = $_POST['order_status'];
    $valid_statuses = ['en attente', 'en attente paiement', 'validée', 'expédiée', 'livrée', 'annulée'];
    if (in_array($new_status, $valid_statuses)) {
        $stmt = $pdo->prepare("UPDATE commande SET statut = ? WHERE id = ?");
        $stmt->execute([$new_status, $order_id]);
        $_SESSION['admin_success'] = "Commande #$order_id mise à jour : $new_status";
    }
    header("Location: gestion-produits.php#orders-tab-link");
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
        $items = $stmt->fetchAll();
        foreach ($items as $item) {
            $stmt = $pdo->prepare("UPDATE produit SET stock = stock + ? WHERE id = ?");
            $stmt->execute([$item['quantite'], $item['produit_id']]);
        }
        $pdo->prepare("DELETE FROM ligne_commande WHERE commande_id = ?")->execute([$order_id]);
        $pdo->prepare("DELETE FROM commande WHERE id = ?")->execute([$order_id]);
        $pdo->commit();
        $_SESSION['admin_success'] = "Commande #$order_id supprimée. Stock restauré.";
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['admin_error'] = "Erreur : " . $e->getMessage();
    }
    header("Location: gestion-produits.php");
    exit();
}

// ============================================================
//  DELETE PRODUCT
// ============================================================
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $pdo->prepare("DELETE FROM produit WHERE id = ?")->execute([$id]);
    header("Location: gestion-produits.php?deleted=1");
    exit;
}

// ============================================================
//  EDIT PRODUCT
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id           = (int)($_POST['id'] ?? 0);
    $nom          = trim($_POST['nom'] ?? '');
    $categorie_id = (int)($_POST['categorie_id'] ?? 0);
    $prix         = (float)($_POST['prix'] ?? 0);
    $stock        = (int)($_POST['stock'] ?? 0);
    $description  = trim($_POST['description'] ?? '');
    $imageName    = $_POST['current_image'] ?? null;

    if ($id && $nom && $categorie_id && $prix >= 0) {
        if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $file    = $_FILES['image'];
            $allowed = ['image/jpeg','image/png','image/webp','image/gif'];
            $maxSize = 2 * 1024 * 1024;
            if (!in_array($file['type'], $allowed)) { $error = "Format non autorisé."; }
            elseif ($file['size'] > $maxSize)        { $error = "Image > 2MB."; }
            else {
                $uploadDir = 'C:/xampp/htdocs/takwini/app/view/back/uploads/produits/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                $ext     = pathinfo($file['name'], PATHINFO_EXTENSION);
                $newName = uniqid('prod_', true).'.'.$ext;
                if (move_uploaded_file($file['tmp_name'], $uploadDir.$newName)) {
                    if (!empty($imageName) && file_exists($uploadDir.$imageName)) unlink($uploadDir.$imageName);
                    $imageName = $newName;
                } else { $error = "Échec upload."; }
            }
        }
        if (!isset($error)) {
            $stmt = $pdo->prepare("UPDATE produit SET categorie_id=?,nom=?,prix=?,stock=?,description=?,image=? WHERE id=?");
            if ($stmt->execute([$categorie_id,$nom,$prix,$stock,$description,$imageName,$id])) {
                header("Location: gestion-produits.php?updated=1"); exit;
            }
        }
    }
}

// ============================================================
//  ADD PRODUCT
// ============================================================
$success = '';
$error   = '';

if (isset($_GET['deleted'])) $success = "Produit supprimé avec succès!";
if (isset($_GET['updated'])) $success = "Produit mis à jour avec succès!";
if (isset($_SESSION['admin_success'])) { $success = $_SESSION['admin_success']; unset($_SESSION['admin_success']); }
if (isset($_SESSION['admin_error']))   { $error   = $_SESSION['admin_error'];   unset($_SESSION['admin_error']); }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $nom          = trim($_POST['nom'] ?? '');
    $categorie_id = (int)($_POST['categorie_id'] ?? 0);
    $prix         = (float)($_POST['prix'] ?? 0);
    $stock        = (int)($_POST['stock'] ?? 0);
    $description  = trim($_POST['description'] ?? '');

    if ($nom && $categorie_id && $prix >= 0) {
        $imageName = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $file    = $_FILES['image'];
            $allowed = ['image/jpeg','image/png','image/webp','image/gif'];
            $maxSize = 2*1024*1024;
            if (!in_array($file['type'], $allowed)) { $error = "Format non autorisé."; }
            elseif ($file['size'] > $maxSize)        { $error = "Image > 2MB."; }
            else {
                $uploadDir = 'C:/xampp/htdocs/takwini/app/view/back/uploads/produits/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                $ext       = pathinfo($file['name'], PATHINFO_EXTENSION);
                $imageName = uniqid('prod_', true).'.'.$ext;
                if (!move_uploaded_file($file['tmp_name'], $uploadDir.$imageName)) {
                    $error = "Échec upload."; $imageName = null;
                }
            }
        }
        if (!$error) {
            $stmt = $pdo->prepare("INSERT INTO produit (categorie_id,nom,prix,stock,description,image) VALUES (?,?,?,?,?,?)");
            if ($stmt->execute([$categorie_id,$nom,$prix,$stock,$description,$imageName])) {
                $success = "Produit ajouté avec succès!";
            } else { $error = "Erreur lors de l'ajout."; }
        }
    } else { $error = "Remplissez tous les champs obligatoires."; }
}

// ============================================================
//  ADD CATEGORY (AJAX)
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_categorie') {
    header('Content-Type: application/json');
    $nom = trim($_POST['nom'] ?? '');
    if ($nom) {
        $stmt = $pdo->prepare("INSERT INTO categorie (nom) VALUES (?)");
        $stmt->execute([$nom]);
        echo json_encode(['success'=>true,'id'=>$pdo->lastInsertId(),'nom'=>$nom]);
    } else { echo json_encode(['success'=>false,'error'=>'Nom vide']); }
    exit;
}

// ============================================================
//  GET ORDER DETAILS (AJAX)
// ============================================================
if (isset($_GET['get_order_details']) && is_numeric($_GET['get_order_details'])) {
    header('Content-Type: application/json');
    $order_id = intval($_GET['get_order_details']);
    $stmt     = $pdo->prepare("SELECT * FROM commande WHERE id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();
    if ($order) {
        $stmt = $pdo->prepare("SELECT lc.*, p.nom as product_name FROM ligne_commande lc JOIN produit p ON lc.produit_id = p.id WHERE lc.commande_id = ?");
        $stmt->execute([$order_id]);
        $items = $stmt->fetchAll();
        echo json_encode(['order'=>$order,'items'=>$items]);
        exit();
    }
}

// ============================================================
//  LOAD DATA
// ============================================================
$categories = $pdo->query("SELECT * FROM categorie ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);
$produits   = $pdo->query("
    SELECT p.*, c.nom AS categorie_nom
    FROM produit p
    LEFT JOIN categorie c ON p.categorie_id = c.id
    ORDER BY p.id DESC
")->fetchAll(PDO::FETCH_ASSOC);

// ---- Orders with payment info ----
$orders = $pdo->query("
    SELECT c.*,
        (SELECT COUNT(*) FROM ligne_commande WHERE commande_id = c.id) as nb_products
    FROM commande c
    ORDER BY c.date_commande DESC
")->fetchAll();

// Stats
$totalProduits = count($produits);
$rupture       = count(array_filter($produits, fn($p) => $p['stock'] == 0));
$faible        = count(array_filter($produits, fn($p) => $p['stock'] > 0 && $p['stock'] < 5));
$disponible    = count(array_filter($produits, fn($p) => $p['stock'] >= 5));

// Order stats
$totalOrders      = count($orders);
$pendingPayment   = count(array_filter($orders, fn($o) => $o['statut'] === 'en attente paiement'));
$pendingOrders    = count(array_filter($orders, fn($o) => $o['statut'] === 'en attente'));
$revenueConfirmed = array_sum(array_map(fn($o) => in_array($o['statut'],['validée','expédiée','livrée']) ? $o['total'] : 0, $orders));
?>
<!doctype html>
<html lang="fr" class="layout-menu-fixed layout-compact" data-assets-path="../assets/" data-template="vertical-menu-template-free">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"/>
    <title>Produits & Commandes | Takwini Admin</title>
    <link rel="icon" type="image/x-icon" href="../assets/img/favicon/favicon.ico"/>
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet"/>
    <link rel="stylesheet" href="../assets/vendor/fonts/iconify-icons.css"/>
    <link rel="stylesheet" href="../assets/vendor/css/core.css"/>
    <link rel="stylesheet" href="../assets/css/demo.css"/>
    <link rel="stylesheet" href="../assets/css/dark-mode.css"/>
    <link rel="stylesheet" href="../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css"/>
    <script src="../assets/vendor/js/helpers.js"></script>
    <script src="../assets/js/config.js"></script>
    <style>
        /* ── Payment status badges ── */
        .status-badge { padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; display: inline-block; white-space: nowrap; }
        .status-en-attente           { background: #ffc107; color: #000; }
        .status-en-attente-paiement  { background: #fd7e14; color: #fff; }
        .status-validee              { background: #28a745; color: #fff; }
        .status-expediee             { background: #17a2b8; color: #fff; }
        .status-livree               { background: #6c757d; color: #fff; }
        .status-annulee              { background: #dc3545; color: #fff; }
        /* ── Payment method pill ── */
        .pay-pill { display: inline-flex; align-items: center; gap: 4px; padding: 3px 8px; border-radius: 12px; font-size: 11px; font-weight: 600; }
        .pay-flouci   { background: #d1fae5; color: #065f46; }
        .pay-d17      { background: #dbeafe; color: #1e40af; }
        .pay-livraison{ background: #fef9c3; color: #713f12; }
        /* ── Order rows that need attention ── */
        tr.needs-attention { background: #fff7ed !important; border-left: 3px solid #f97316; }
        /* ── Search ── */
        .search-input-wrapper { position: relative; display: flex; align-items: center; }
        .search-input-wrapper .bx-search { position: absolute; left: 12px; font-size: 1.1rem; color: #a5abb2; pointer-events: none; }
        .search-input-wrapper input { padding-left: 36px; border-radius: 40px; border: 1px solid #e9ecef; background: #f8f9fa; }
        .search-input-wrapper input:focus { border-color: #696cff; background: #fff; }
        .product-row { transition: opacity 0.2s ease; }
        /* ── Revenue card green accent ── */
        .revenue-card { border-left: 4px solid #10b981 !important; }
    </style>
</head>
<body>
<div class="layout-wrapper layout-content-navbar">
  <div class="layout-container">

    <!-- SIDEBAR (same as your original) -->
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
            <li class="menu-item"><a href="index.html" class="menu-link"><div class="text-truncate">Accueil</div></a></li>
            <li class="menu-item"><a href="gestion-reclamations.html" class="menu-link"><div class="text-truncate">Réclamations</div></a></li>
            <li class="menu-item active">
              <a href="gestion-produits.php" class="menu-link"><div class="text-truncate">Produits & Commandes</div></a>
            </li>
          </ul>
        </li>
      </ul>
    </aside>

    <div class="layout-page">
      <!-- NAVBAR -->
      <nav class="layout-navbar container-xxl navbar-detached navbar navbar-expand-xl align-items-center bg-navbar-theme" id="layout-navbar">
        <div class="layout-menu-toggle navbar-nav align-items-xl-center me-4 me-xl-0 d-xl-none">
          <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)">
            <i class="icon-base bx bx-menu icon-md"></i>
          </a>
        </div>
        <div class="navbar-nav-right d-flex align-items-center justify-content-end" id="navbar-collapse">
          <ul class="navbar-nav flex-row align-items-center ms-md-auto">
            <li class="nav-item me-2 me-xl-1">
              <a class="nav-link" href="javascript:void(0);" id="app-theme-toggle">
                <i class="icon-base bx bx-moon icon-md" id="app-theme-toggle-icon"></i>
              </a>
            </li>
            <li class="nav-item navbar-dropdown dropdown-user dropdown">
              <a class="nav-link dropdown-toggle hide-arrow p-0" href="javascript:void(0);" data-bs-toggle="dropdown">
                <div class="avatar avatar-online">
                  <img src="../assets/img/avatars/1.png" alt class="w-px-40 h-auto rounded-circle"/>
                </div>
              </a>
              <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="auth-login-basic.html"><i class="icon-base bx bx-power-off icon-md me-3"></i><span>Déconnexion</span></a></li>
              </ul>
            </li>
          </ul>
        </div>
      </nav>

      <!-- CONTENT -->
      <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">

          <h4 class="fw-bold py-3 mb-2">Gestion des Produits & Commandes</h4>

          <!-- ★ NEW: Payment alert if any orders pending payment -->
          <?php if ($pendingPayment > 0): ?>
          <div class="alert alert-warning d-flex align-items-center mb-4" role="alert">
            <i class="bx bx-time-five me-2" style="font-size:20px;"></i>
            <div>
              <strong><?= $pendingPayment ?> commande(s) en attente de confirmation paiement.</strong>
              Vérifiez votre compte Flouci/D17 et changez leur statut en <em>Validée</em>.
            </div>
          </div>
          <?php endif; ?>

          <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show">
              <i class="bx bx-check-circle me-2"></i><?= htmlspecialchars($success) ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
          <?php endif; ?>
          <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show">
              <i class="bx bx-error-circle me-2"></i><?= htmlspecialchars($error) ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
          <?php endif; ?>

          <!-- TABS -->
          <ul class="nav nav-tabs mb-4" role="tablist">
            <li class="nav-item">
              <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#products-tab" type="button">
                <i class="bx bx-package me-1"></i> Produits
              </button>
            </li>
            <li class="nav-item" id="orders-tab-link">
              <button class="nav-link" data-bs-toggle="tab" data-bs-target="#orders-tab" type="button">
                <i class="bx bx-shopping-bag me-1"></i> Commandes
                <?php if ($totalOrders > 0): ?>
                  <span class="badge bg-primary rounded-pill ms-1"><?= $totalOrders ?></span>
                <?php endif; ?>
                <?php if ($pendingPayment > 0): ?>
                  <span class="badge bg-warning rounded-pill ms-1" title="En attente paiement"><?= $pendingPayment ?> ⚠</span>
                <?php endif; ?>
              </button>
            </li>
          </ul>

          <div class="tab-content">

            <!-- ====== PRODUCTS TAB ====== -->
            <div class="tab-pane fade show active" id="products-tab" role="tabpanel">
              <!-- Stats Cards -->
              <div class="row g-6 mb-6">
                <div class="col-sm-6 col-xl-3">
                  <div class="card"><div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                      <div><span>Total produits</span><div class="d-flex align-items-end mt-2"><h4 class="mb-0 me-2"><?= $totalProduits ?></h4></div><p class="mb-0">Catalogue</p></div>
                      <div class="avatar"><span class="avatar-initial rounded bg-label-primary"><i class="bx bx-cart bx-sm"></i></span></div>
                    </div>
                  </div></div>
                </div>
                <div class="col-sm-6 col-xl-3">
                  <div class="card"><div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                      <div><span>Disponibles</span><div class="d-flex align-items-end mt-2"><h4 class="mb-0 me-2"><?= $disponible ?></h4></div><p class="mb-0">En stock</p></div>
                      <div class="avatar"><span class="avatar-initial rounded bg-label-success"><i class="bx bx-check-circle bx-sm"></i></span></div>
                    </div>
                  </div></div>
                </div>
                <div class="col-sm-6 col-xl-3">
                  <div class="card"><div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                      <div><span>Stock faible</span><div class="d-flex align-items-end mt-2"><h4 class="mb-0 me-2"><?= $faible ?></h4></div><p class="mb-0">Moins de 5</p></div>
                      <div class="avatar"><span class="avatar-initial rounded bg-label-warning"><i class="bx bx-pause-circle bx-sm"></i></span></div>
                    </div>
                  </div></div>
                </div>
                <div class="col-sm-6 col-xl-3">
                  <div class="card"><div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                      <div><span>Rupture</span><div class="d-flex align-items-end mt-2"><h4 class="mb-0 me-2"><?= $rupture ?></h4></div><p class="mb-0">Stock épuisé</p></div>
                      <div class="avatar"><span class="avatar-initial rounded bg-label-danger"><i class="bx bx-x-circle bx-sm"></i></span></div>
                    </div>
                  </div></div>
                </div>
              </div>

              <!-- ★ NEW: Order payment stats -->
              <div class="row g-6 mb-6">
                <div class="col-sm-6 col-xl-3">
                  <div class="card revenue-card"><div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                      <div><span>Revenu confirmé</span><div class="d-flex align-items-end mt-2"><h4 class="mb-0 me-2"><?= number_format($revenueConfirmed, 3) ?> DT</h4></div><p class="mb-0">Validé + Expédié + Livré</p></div>
                      <div class="avatar"><span class="avatar-initial rounded bg-label-success"><i class="bx bx-money bx-sm"></i></span></div>
                    </div>
                  </div></div>
                </div>
                <div class="col-sm-6 col-xl-3">
                  <div class="card"><div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                      <div><span>Total commandes</span><div class="d-flex align-items-end mt-2"><h4 class="mb-0 me-2"><?= $totalOrders ?></h4></div><p class="mb-0">Toutes statuts</p></div>
                      <div class="avatar"><span class="avatar-initial rounded bg-label-primary"><i class="bx bx-shopping-bag bx-sm"></i></span></div>
                    </div>
                  </div></div>
                </div>
                <div class="col-sm-6 col-xl-3">
                  <div class="card"><div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                      <div><span>Attente paiement</span><div class="d-flex align-items-end mt-2"><h4 class="mb-0 me-2" style="color:#fd7e14;"><?= $pendingPayment ?></h4></div><p class="mb-0">À confirmer</p></div>
                      <div class="avatar"><span class="avatar-initial rounded bg-label-warning"><i class="bx bx-time bx-sm"></i></span></div>
                    </div>
                  </div></div>
                </div>
                <div class="col-sm-6 col-xl-3">
                  <div class="card"><div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                      <div><span>En attente</span><div class="d-flex align-items-end mt-2"><h4 class="mb-0 me-2"><?= $pendingOrders ?></h4></div><p class="mb-0">COD / à traiter</p></div>
                      <div class="avatar"><span class="avatar-initial rounded bg-label-info"><i class="bx bx-package bx-sm"></i></span></div>
                    </div>
                  </div></div>
                </div>
              </div>

              <!-- Products Table -->
              <div class="card">
                <div class="card-header border-bottom d-flex flex-wrap align-items-center justify-content-between gap-3">
                  <h5 class="card-title mb-0">Liste des produits</h5>
                  <div class="d-flex flex-wrap gap-3 align-items-center">
                    <div style="max-width:300px; width:100%;">
                      <div class="search-input-wrapper">
                        <i class="bx bx-search"></i>
                        <input type="text" id="productSearchInput" class="form-control" placeholder="Rechercher produit...">
                      </div>
                    </div>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProduitModal">
                      <i class="bx bx-plus me-1"></i> Ajouter Produit
                    </button>
                  </div>
                </div>
                <div class="card-datatable table-responsive">
                  <table class="table border-top">
                    <thead>
                      <tr>
                        <th>#</th><th>IMAGE</th><th>PRODUIT</th><th>CATÉGORIE</th>
                        <th>PRIX</th><th>STOCK</th><th>STATUT</th><th>ACTIONS</th>
                      </tr>
                    </thead>
                    <tbody id="productsTableBody">
                      <?php if (empty($produits)): ?>
                        <tr><td colspan="8" class="text-center text-muted py-5">Aucun produit. Cliquez sur "Ajouter Produit".</td></tr>
                      <?php else: ?>
                        <?php foreach ($produits as $p): ?>
                          <tr class="product-row" data-product-name="<?= htmlspecialchars(strtolower($p['nom'])) ?>">
                            <td><?= $p['id'] ?></td>
                            <td>
                              <?php if (!empty($p['image'])): ?>
                                <img src="../uploads/produits/<?= htmlspecialchars($p['image']) ?>" style="width:48px;height:48px;object-fit:cover;border-radius:6px;border:1px solid #ddd;">
                              <?php else: ?>
                                <span style="width:48px;height:48px;background:#f0f0f0;display:flex;align-items:center;justify-content:center;border-radius:6px;font-size:20px;">📦</span>
                              <?php endif; ?>
                            </td>
                            <td>
                              <span class="fw-medium product-name-original"><?= htmlspecialchars($p['nom']) ?></span><br>
                              <small class="text-muted"><?= htmlspecialchars(substr($p['description'] ?? '', 0, 40)) ?><?= strlen($p['description'] ?? '') > 40 ? '...' : '' ?></small>
                            </td>
                            <td><?= htmlspecialchars($p['categorie_nom'] ?? 'N/A') ?></td>
                            <td><?= number_format((float)$p['prix'], 3) ?> DT</td>
                            <td><?= (int)$p['stock'] ?></td>
                            <td>
                              <?php if ($p['stock'] == 0): ?>
                                <span class="badge bg-label-danger">Rupture</span>
                              <?php elseif ($p['stock'] < 5): ?>
                                <span class="badge bg-label-warning">Faible</span>
                              <?php else: ?>
                                <span class="badge bg-label-success">Disponible</span>
                              <?php endif; ?>
                            </td>
                            <td>
                              <a href="javascript:;" class="text-body me-2" title="Modifier"
                                 onclick="openEditModal(<?= $p['id'] ?>,'<?= addslashes(htmlspecialchars($p['nom'])) ?>',<?= (int)$p['categorie_id'] ?>,<?= (float)$p['prix'] ?>,<?= (int)$p['stock'] ?>,'<?= addslashes(htmlspecialchars($p['description'] ?? '')) ?>','<?= htmlspecialchars($p['image'] ?? '') ?>')">
                                <i class="bx bx-edit bx-sm"></i>
                              </a>
                              <a href="javascript:;" class="text-body" title="Supprimer" onclick="openDeleteModal(<?= $p['id'] ?>)">
                                <i class="bx bx-trash bx-sm"></i>
                              </a>
                            </td>
                          </tr>
                        <?php endforeach; ?>
                      <?php endif; ?>
                    </tbody>
                  </table>
                  <div class="mx-2 mt-2 mb-3">
                    <span class="text-muted small" id="productsCounter"><?= $totalProduits ?> produit(s)</span>
                  </div>
                </div>
              </div>
            </div>
            <!-- ====== / PRODUCTS TAB ====== -->

            <!-- ====== ORDERS TAB ====== -->
            <div class="tab-pane fade" id="orders-tab" role="tabpanel">
              <div class="card">
                <div class="card-header border-bottom d-flex align-items-center justify-content-between">
                  <div>
                    <h5 class="card-title mb-0">Gestion des Commandes</h5>
                    <p class="text-muted mt-1 mb-0 small">
                      Les lignes <span style="border-left:3px solid #f97316; padding-left:6px;">orange</span> sont en attente de confirmation de paiement mobile.
                    </p>
                  </div>
                  <!-- ★ Filter by payment method -->
                  <div class="d-flex gap-2">
                    <select id="filterMethod" class="form-select form-select-sm" style="width:auto;" onchange="filterOrders()">
                      <option value="">Toutes méthodes</option>
                      <option value="flouci">📱 Flouci</option>
                      <option value="d17">🏦 D17</option>
                      <option value="livraison">🚚 Livraison</option>
                    </select>
                    <select id="filterStatus" class="form-select form-select-sm" style="width:auto;" onchange="filterOrders()">
                      <option value="">Tous statuts</option>
                      <option value="en attente paiement">⚠ En attente paiement</option>
                      <option value="en attente">En attente</option>
                      <option value="validée">Validée</option>
                      <option value="expédiée">Expédiée</option>
                      <option value="livrée">Livrée</option>
                      <option value="annulée">Annulée</option>
                    </select>
                  </div>
                </div>
                <div class="card-body">
                  <?php if (empty($orders)): ?>
                    <div class="text-center py-5">
                      <i class="bx bx-shopping-bag bx-lg text-muted mb-3"></i>
                      <h5>Aucune commande</h5>
                    </div>
                  <?php else: ?>
                    <div class="table-responsive">
                      <table class="table table-hover" id="ordersTable">
                        <thead>
                          <tr>
                            <th># Commande</th>
                            <th>Date</th>
                            <th>Client</th>
                            <th>Paiement</th>  <!-- ★ NEW column -->
                            <th>Total</th>
                            <th>Statut</th>
                            <th>Actions</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php foreach ($orders as $order):
                            $pm        = $order['payment_method'] ?? 'N/A';
                            $pmPhone   = $order['payment_phone'] ?? '';
                            $pmIcon    = $pm === 'flouci' ? '📱' : ($pm === 'd17' ? '🏦' : ($pm === 'livraison' ? '🚚' : '❓'));
                            $pmClass   = 'pay-' . $pm;
                            $needsAttn = $order['statut'] === 'en attente paiement';
                          ?>
                          <tr class="order-row <?= $needsAttn ? 'needs-attention' : '' ?>"
                              data-method="<?= htmlspecialchars($pm) ?>"
                              data-status="<?= htmlspecialchars($order['statut']) ?>">
                            <td><strong>#<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></strong></td>
                            <td><?= date('d/m/Y H:i', strtotime($order['date_commande'])) ?></td>
                            <td>
                              <?= htmlspecialchars($order['nom_client']) ?><br>
                              <small class="text-muted"><?= htmlspecialchars($order['email_client']) ?></small>
                            </td>
                            <!-- ★ Payment method + phone -->
                            <td>
                              <span class="pay-pill <?= $pmClass ?>">
                                <?= $pmIcon ?> <?= strtoupper($pm) ?>
                              </span>
                              <?php if ($pmPhone): ?>
                                <br><small class="text-muted">+216 <?= htmlspecialchars($pmPhone) ?></small>
                              <?php endif; ?>
                            </td>
                            <td><strong><?= number_format($order['total'], 3) ?> DT</strong></td>
                            <td>
                              <form method="POST" style="display:inline-block;">
                                <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                <input type="hidden" name="update_order_status" value="1">
                                <select name="order_status" class="form-select form-select-sm"
                                        style="width:170px;"
                                        onchange="this.form.submit()">
                                  <option value="en attente"          <?= $order['statut']==='en attente'          ?'selected':'' ?>>En attente</option>
                                  <option value="en attente paiement" <?= $order['statut']==='en attente paiement' ?'selected':'' ?>>⚠ En attente paiement</option>
                                  <option value="validée"             <?= $order['statut']==='validée'             ?'selected':'' ?>>✅ Validée</option>
                                  <option value="expédiée"            <?= $order['statut']==='expédiée'            ?'selected':'' ?>>🚚 Expédiée</option>
                                  <option value="livrée"              <?= $order['statut']==='livrée'              ?'selected':'' ?>>✓ Livrée</option>
                                  <option value="annulée"             <?= $order['statut']==='annulée'             ?'selected':'' ?>>✗ Annulée</option>
                                </select>
                              </form>
                            </td>
                            <td>
                              <button class="btn btn-sm btn-info me-1" onclick="viewOrderDetails(<?= $order['id'] ?>)">
                                <i class="bx bx-show"></i>
                              </button>
                              <a href="?delete_order=<?= $order['id'] ?>" class="btn btn-sm btn-danger"
                                 onclick="return confirm('Supprimer cette commande? Le stock sera restauré.')">
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
            <!-- ====== / ORDERS TAB ====== -->

          </div>

          <!-- ===== MODAL: ADD PRODUCT ===== -->
          <div class="modal fade" id="addProduitModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title">Ajouter un produit</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="gestion-produits.php" enctype="multipart/form-data">
                  <input type="hidden" name="action" value="add">
                  <div class="modal-body">
                    <div class="mb-3">
                      <label class="form-label">Nom *</label>
                      <input type="text" name="nom" class="form-control" required>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Catégorie *</label>
                      <select name="categorie_id" id="add_categorie_id" class="form-select" required>
                        <option value="">-- Choisir --</option>
                        <?php foreach ($categories as $cat): ?>
                          <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nom']) ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <div class="row">
                      <div class="col-6 mb-3">
                        <label class="form-label">Prix (DT) *</label>
                        <input type="number" step="0.001" name="prix" class="form-control" min="0" required>
                      </div>
                      <div class="col-6 mb-3">
                        <label class="form-label">Stock</label>
                        <input type="number" name="stock" class="form-control" min="0" value="0">
                      </div>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Description</label>
                      <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Image</label>
                      <input type="file" name="image" id="add_image" class="form-control" accept="image/*">
                      <img id="add_preview" src="#" style="display:none;width:100px;height:100px;object-fit:cover;border-radius:8px;margin-top:8px;">
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Ajouter</button>
                  </div>
                </form>
              </div>
            </div>
          </div>

          <!-- ===== MODAL: EDIT PRODUCT ===== -->
          <div class="modal fade" id="editProduitModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title">Modifier le produit</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="gestion-produits.php" enctype="multipart/form-data">
                  <input type="hidden" name="action" value="edit">
                  <input type="hidden" name="id" id="edit_id">
                  <input type="hidden" name="current_image" id="edit_current_image">
                  <div class="modal-body">
                    <div class="mb-3">
                      <label class="form-label">Nom *</label>
                      <input type="text" name="nom" id="edit_nom" class="form-control" required>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Catégorie *</label>
                      <select name="categorie_id" id="edit_categorie_id" class="form-select" required>
                        <?php foreach ($categories as $cat): ?>
                          <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nom']) ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <div class="row">
                      <div class="col-6 mb-3">
                        <label class="form-label">Prix (DT) *</label>
                        <input type="number" step="0.001" name="prix" id="edit_prix" class="form-control" min="0" required>
                      </div>
                      <div class="col-6 mb-3">
                        <label class="form-label">Stock</label>
                        <input type="number" name="stock" id="edit_stock" class="form-control" min="0">
                      </div>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Description</label>
                      <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Nouvelle image</label>
                      <input type="file" name="image" id="edit_image" class="form-control" accept="image/*">
                      <img id="edit_preview" src="#" style="display:none;width:100px;height:100px;object-fit:cover;border-radius:8px;margin-top:8px;">
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

          <!-- ===== MODAL: DELETE ===== -->
          <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" style="max-width:400px;">
              <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                  <h5 class="modal-title text-danger"><i class="bx bx-error-circle me-2"></i>Confirmer la suppression</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center py-4">
                  <i class="bx bx-trash bx-lg text-danger mb-3 d-block"></i>
                  <p class="mb-0">Supprimer ce produit ?</p>
                  <small class="text-muted">Action irréversible.</small>
                </div>
                <div class="modal-footer border-0 pt-0">
                  <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                  <a id="confirmDeleteBtn" href="#" class="btn btn-danger">Supprimer</a>
                </div>
              </div>
            </div>
          </div>

          <!-- ===== MODAL: ORDER DETAILS ===== -->
          <div class="modal fade" id="orderDetailsModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
              <div class="modal-content">
                <div class="modal-header" style="background:#696cff;color:white;">
                  <h5 class="modal-title">Détails de la commande</h5>
                  <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="orderDetailsContent">Chargement...</div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>

      <footer class="content-footer footer bg-footer-theme">
        <div class="container-xxl">
          <div class="footer-container d-flex align-items-center justify-content-between py-4">
            <div>&copy; <?= date('Y') ?> Takwini</div>
          </div>
        </div>
      </footer>
      <div class="content-backdrop fade"></div>
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
// ── Product search ──
const searchInput    = document.getElementById('productSearchInput');
const productRows    = document.querySelectorAll('#productsTableBody .product-row');
const counterEl      = document.getElementById('productsCounter');
const originalTotal  = <?= $totalProduits ?>;

if (searchInput) {
    searchInput.addEventListener('input', function() {
        const term = this.value.trim().toLowerCase();
        let visible = 0;
        let noRow = document.getElementById('noSearchRow');

        productRows.forEach(function(row) {
            const name = row.querySelector('.product-name-original')?.innerText.toLowerCase() || '';
            const cat  = row.cells[3]?.innerText.toLowerCase() || '';
            const match = !term || name.includes(term) || cat.includes(term);
            row.style.display = match ? '' : 'none';
            if (match) visible++;
        });

        if (term && visible === 0) {
            if (!noRow) {
                noRow = document.createElement('tr');
                noRow.id = 'noSearchRow';
                noRow.innerHTML = '<td colspan="8" class="text-center py-4 text-muted">Aucun produit trouvé pour "' + term + '"</td>';
                document.getElementById('productsTableBody').appendChild(noRow);
            }
        } else if (noRow) { noRow.remove(); }

        counterEl.textContent = term ? visible + ' / ' + originalTotal + ' produit(s)' : originalTotal + ' produit(s)';
    });
}

// ── Order filter by method + status ──
function filterOrders() {
    var method = document.getElementById('filterMethod').value.toLowerCase();
    var status = document.getElementById('filterStatus').value.toLowerCase();
    document.querySelectorAll('#ordersTable .order-row').forEach(function(row) {
        var rowMethod = row.dataset.method.toLowerCase();
        var rowStatus = row.dataset.status.toLowerCase();
        var mMatch = !method || rowMethod === method;
        var sMatch = !status || rowStatus === status;
        row.style.display = (mMatch && sMatch) ? '' : 'none';
    });
}

// ── Edit / Delete modals ──
function openEditModal(id, nom, categorie_id, prix, stock, description, image) {
    document.getElementById('edit_id').value            = id;
    document.getElementById('edit_nom').value           = nom;
    document.getElementById('edit_prix').value          = prix;
    document.getElementById('edit_stock').value         = stock;
    document.getElementById('edit_description').value   = description;
    document.getElementById('edit_current_image').value = image;
    var sel = document.getElementById('edit_categorie_id');
    for (var i = 0; i < sel.options.length; i++) {
        if (parseInt(sel.options[i].value) === parseInt(categorie_id)) { sel.options[i].selected = true; break; }
    }
    var preview = document.getElementById('edit_preview');
    if (image) { preview.src = '../uploads/produits/' + image; preview.style.display = 'block'; }
    else        { preview.src = '#'; preview.style.display = 'none'; }
    new bootstrap.Modal(document.getElementById('editProduitModal')).show();
}

function openDeleteModal(id) {
    document.getElementById('confirmDeleteBtn').href = 'gestion-produits.php?delete=' + id;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

// ── Image preview ──
document.getElementById('add_image')?.addEventListener('change', function() {
    var p = document.getElementById('add_preview');
    if (this.files && this.files[0]) { p.src = URL.createObjectURL(this.files[0]); p.style.display = 'block'; }
});
document.getElementById('edit_image')?.addEventListener('change', function() {
    var p = document.getElementById('edit_preview');
    if (this.files && this.files[0]) { p.src = URL.createObjectURL(this.files[0]); p.style.display = 'block'; }
});

// ── Order details modal ──
function viewOrderDetails(orderId) {
    var modal = new bootstrap.Modal(document.getElementById('orderDetailsModal'));
    modal.show();
    document.getElementById('orderDetailsContent').innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>';

    fetch(window.location.href + '?get_order_details=' + orderId)
        .then(function(r){ return r.json(); })
        .then(function(data) {
            var pm     = data.order.payment_method || 'N/A';
            var pmIcon = pm==='flouci'?'📱':pm==='d17'?'🏦':pm==='livraison'?'🚚':'❓';
            var html = `
                <div class="row mb-3">
                  <div class="col-md-6">
                    <p><strong>Commande #${String(orderId).padStart(6,'0')}</strong></p>
                    <p><strong>Date:</strong> ${new Date(data.order.date_commande).toLocaleString()}</p>
                    <p><strong>Statut:</strong> ${data.order.statut}</p>
                    <p><strong>Total:</strong> <strong style="color:#696cff;">${parseFloat(data.order.total).toFixed(3)} DT</strong></p>
                  </div>
                  <div class="col-md-6">
                    <p><strong>Client:</strong> ${data.order.nom_client}</p>
                    <p><strong>Email:</strong> ${data.order.email_client}</p>
                    <p><strong>Tél:</strong> ${data.order.telephone_client}</p>
                    <p><strong>Méthode paiement:</strong> ${pmIcon} ${pm.toUpperCase()}</p>
                    ${data.order.payment_phone ? '<p><strong>Tél paiement:</strong> +216 '+data.order.payment_phone+'</p>' : ''}
                  </div>
                </div>
                <h6>Articles:</h6>
                <div class="table-responsive">
                  <table class="table table-sm table-bordered">
                    <thead style="background:#696cff;color:white;"><tr><th>Produit</th><th>Qté</th><th>Prix</th><th>Total</th></tr></thead>
                    <tbody>`;
            data.items.forEach(function(item){
                html += `<tr><td>${item.product_name}</td><td>${item.quantite}</td><td>${parseFloat(item.prix_unitaire).toFixed(3)} DT</td><td>${(item.quantite*item.prix_unitaire).toFixed(3)} DT</td></tr>`;
            });
            html += `</tbody></table></div>
                <h6>Adresse:</h6>
                <div style="background:#f8f9fa;padding:12px;border-radius:8px;">
                  ${data.order.adresse_livraison.replace(/\n/g,'<br>')}
                </div>`;
            document.getElementById('orderDetailsContent').innerHTML = html;
        })
        .catch(function(){
            document.getElementById('orderDetailsContent').innerHTML = '<div class="alert alert-danger">Erreur de chargement</div>';
        });
}
</script>
</body>
</html>