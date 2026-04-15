<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Controller/OffreController.php';

$controller = new OffreController();
$offres = $controller->index();
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<title>Takwinibot - Offres</title>

	<!-- KEEP ORIGINAL ASSETS -->
	<link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
	<link href="https://fonts.googleapis.com/css2?family=Exo:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="assets/fonts/font-awesome.min.css">
	<link rel="stylesheet" href="assets/fonts/themify-icons.css">
	<link rel="stylesheet" href="assets/owlcarousel/css/owl.carousel.css">
	<link rel="stylesheet" href="assets/owlcarousel/css/owl.theme.css">
	<link rel="stylesheet" href="assets/css/fonts.css">
	<link href="assets/css/prettyPhoto.css" rel="stylesheet">
	<link rel="stylesheet" href="assets/css/magnific-popup.css">
	<link rel="stylesheet" href="assets/css/animate.css">
	<link rel="stylesheet" href="assets/css/slick.css">
	<link rel="stylesheet" href="assets/css/menu.css">
	<link rel="stylesheet" href="assets/css/style.css">
	<link rel="stylesheet" href="assets/css/responsive.css">
</head>

<body data-spy="scroll" data-offset="80">

<!-- PRELOADER -->
<div class="preloader">
	<div class="status">
		<div class="status-mes"></div>
	</div>
</div>

<!-- NAVBAR (UNCHANGED TEMPLATE) -->
<header class="site-navbar js-sticky-header site-navbar-target" role="banner">
	<div class="container">
		<div class="row align-items-center">
			<div class="col-6 col-xl-2">
				<h1 class="mb-0 site-logo">
					<a href="index.html"><img src="assets/img/logo.png" alt=""></a>
				</h1>
			</div>
		</div>
	</div>
</header>

<!-- TITLE -->
<section class="section-top" style="padding:60px 0;background:#f9f9fc;">
	<div class="container text-center">
		<h1>Nos Offres d'Emploi</h1>
	</div>
</section>

<style>
.job-card {
	transition: all 0.3s ease;
}
.job-card:hover {
	box-shadow: 0 5px 15px rgba(0,0,0,0.05);
	background: #fff;
	border-radius: 8px;
}
</style>

<!-- OFFERS SECTION -->
<section class="offres-section" style="padding:60px 0;background:#f9f9fc;">
	<div class="container">
		<div class="row">

			<!-- LEFT FILTER (UNCHANGED) -->
			<div class="col-lg-3 col-md-4">
				<!-- 그대로 keep your design -->
			</div>

			<!-- RIGHT LIST -->
			<div class="col-lg-9 col-md-8">

				<?php if (!empty($offres)) : ?>

					<?php foreach ($offres as $offre) : ?>
						<div class="job-card"
							style="background:transparent;margin-bottom:20px;padding:25px 0;border-bottom:1px solid #ebebeb;display:flex;align-items:center;justify-content:space-between;">

							<div class="job-info d-flex align-items-center">

								<div class="job-logo mr-4"
									style="width:80px;height:80px;border:1px solid #e1e1e1;display:flex;justify-content:center;align-items:center;background:#fff;">
									<img src="assets/img/offres/logo1.jpg" style="max-width:60px;">
								</div>

								<div class="job-details">
									<h3 style="font-size:20px;font-weight:500;margin-bottom:8px;color:#333;">
										<?= htmlspecialchars($offre['titre']) ?>
									</h3>

									<div style="color:#888;font-size:14px;">
										<?= htmlspecialchars($offre['type']) ?>
									</div>

									<div style="color:#aaa;font-size:13px;">
										<?= htmlspecialchars($offre['description']) ?>
									</div>
								</div>

							</div>

							<div class="job-action text-right">
								<a href="#" class="btn btn-outline-primary mb-2"
									style="border-radius:20px;border-color:#8b83f6;color:#8b83f6;">
									Postuler
								</a>

								<p style="margin:0;color:#888;font-size:13px;">
									<?= htmlspecialchars($offre['datePublication']) ?>
								</p>
							</div>

						</div>
					<?php endforeach; ?>

				<?php else : ?>
					<p>Aucune offre disponible</p>
				<?php endif; ?>

			</div>
		</div>
	</div>
</section>

<!-- FOOTER (UNCHANGED STRUCTURE) -->
<footer class="footer-area">
	<div class="container">
		<p class="text-center">Takwinibot © 2026</p>
	</div>
</footer>

<!-- SCRIPTS (KEEP ORIGINAL) -->
<script src="assets/js/jquery-1.12.4.min.js"></script>
<script src="assets/bootstrap/js/bootstrap.min.js"></script>
<script src="assets/js/scripts.js"></script>

</body>
</html>