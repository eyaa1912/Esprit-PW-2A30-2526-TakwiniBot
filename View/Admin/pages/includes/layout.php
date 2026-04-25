<?php
// Layout variables
$title = $title ?? "Dashboard";
$content = $content ?? "";
?>

<!doctype html>
<html lang="fr" class="layout-menu-fixed layout-compact">

<?php include 'head.php'; ?>

<body>
<div class="layout-wrapper layout-content-navbar">
  <div class="layout-container">

    <!-- SIDEBAR -->
    <?php include 'sidebar.php'; ?>

    <div class="layout-page">

      <!-- NAVBAR -->
      <?php include 'navbar.php'; ?>

      <!-- CONTENT -->
      <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">

          <?= $content ?>

        </div>

        <!-- FOOTER -->
        <?php include 'footer.php'; ?>

      </div>
    </div>
  </div>

  <div class="layout-overlay layout-menu-toggle"></div>
</div>

<?php include 'scripts.php'; ?>
</body>
</html>