<?php
// layout_top.php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!doctype html>
<html lang="en" class="layout-menu-fixed layout-compact"
      data-assets-path="../assets/"
      data-template="vertical-menu-template-free">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= $title ?? 'Dashboard' ?></title>

    <!-- Favicon -->
    <link rel="icon" href="../assets/img/favicon/favicon.ico" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&display=swap">

    <!-- Core CSS -->
    <link rel="stylesheet" href="../assets/vendor/css/core.css">
    <link rel="stylesheet" href="../assets/css/demo.css">
    <link rel="stylesheet" href="../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css">

    <!-- Icons -->
    <link rel="stylesheet" href="../assets/vendor/fonts/iconify-icons.css">

    <script src="../assets/vendor/js/helpers.js"></script>
    <script src="../assets/js/config.js"></script>
</head>

<body>

<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">

        <?php include __DIR__ . '/sidebar.php'; ?>

        <div class="layout-page">

            <?php include __DIR__ . '/navbar.php'; ?>

            <div class="content-wrapper">