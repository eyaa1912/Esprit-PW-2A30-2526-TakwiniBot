<?php session_start(); ?>
﻿<!DOCTYPE html>
<html lang="en">

<head>
	<!-- Meta -->
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
	<!-- SITE TITLE -->
	<title>Takwinibot - Real Estate HTML Template</title>
	<!-- Latest Bootstrap min CSS -->
	<link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
	<!-- Google Font -->
	<link href="https://fonts.googleapis.com/css2?family=Exo:wght@300;400;500;600;700;800;900&display=swap"
		rel="stylesheet">
	<!-- Font Awesome CSS -->
	<link rel="stylesheet" href="assets/fonts/font-awesome.min.css">
	<link rel="stylesheet" href="assets/fonts/themify-icons.css">
	<!--- owl carousel Css-->
	<link rel="stylesheet" href="assets/owlcarousel/css/owl.carousel.css">
	<link rel="stylesheet" href="assets/owlcarousel/css/owl.theme.css">
	<!--fonts icons Css-->
	<link rel="stylesheet" href="assets/css/fonts.css">
	<!--prettyPhoto css-->
	<link href="assets/css/prettyPhoto.css" rel="stylesheet">
	<!-- animate CSS -->
	<link rel="stylesheet" href="assets/css/animate.css">
	<!-- Slick css -->
	<link rel="stylesheet" href="assets/css/slick.css">
	<!-- Style CSS -->
	<link rel="stylesheet" href="assets/css/menu.css">
	<link rel="stylesheet" href="assets/css/style.css">
	<link rel="stylesheet" href="assets/css/responsive.css">
	<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
	<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
	<!--[if lt IE 9]>
		  <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
		  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
		<![endif]-->
<style>
/* ══ NAVBAR CUSTOM ══════════════════════════════════════════ */
.site-navbar {
  background: rgba(255,255,255,0.97) !important;
  backdrop-filter: blur(10px);
  box-shadow: 0 2px 20px rgba(0,0,0,0.08);
  padding: 0 !important;
  height: 110px;
}
.site-navbar .container {
  height: 110px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  max-width: 100%;
  padding: 0 30px;
}
/* Logo tout à gauche */
.nav-logo {
  flex-shrink: 0;
  display: flex;
  align-items: center;
}
.nav-logo img {
  height: 100px;
  width: auto;
}
/* Liens nav au centre */
.nav-links {
  display: flex;
  align-items: center;
  gap: 4px;
  list-style: none;
  margin: 0;
  padding: 0;
  flex: 1;
  justify-content: center;
}
.nav-links > li > a {
  color: #333 !important;
  font-size: 17px;
  font-weight: 600;
  padding: 10px 16px;
  border-radius: 8px;
  text-decoration: none;
  transition: all .2s;
  white-space: nowrap;
}
.nav-links > li > a:hover {
  background: #e8f5e9;
  color: #2e7d32 !important;
}
/* Bouton user à droite */
.nav-user {
  flex-shrink: 0;
  position: relative;
}
.user-btn {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  background: linear-gradient(135deg, #4caf50, #2e7d32);
  border: none;
  border-radius: 50px;
  padding: 8px 20px 8px 8px;
  cursor: pointer;
  text-decoration: none;
  transition: all .2s;
  box-shadow: 0 4px 15px rgba(76,175,80,0.3);
  min-width: 200px;
}
.user-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 6px 20px rgba(76,175,80,0.4);
  text-decoration: none;
}
.user-btn .u-avatar {
  width: 44px; height: 44px;
  border-radius: 50%;
  background: rgba(255,255,255,0.25);
  display: flex; align-items: center; justify-content: center;
  overflow: hidden;
  flex-shrink: 0;
  border: 2px solid rgba(255,255,255,0.6);
}
.user-btn .u-avatar img {
  width: 44px; height: 44px;
  border-radius: 50%; object-fit: cover;
}
.user-btn .u-name {
  color: #fff;
  font-size: 15px;
  font-weight: 700;
  flex: 1;
  text-align: left;
}
.user-btn .u-caret {
  color: rgba(255,255,255,0.8);
  font-size: 10px;
}
/* Dropdown menu */
.user-drop-menu {
  display: none;
  position: absolute;
  right: 0; top: calc(100% + 10px);
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 10px 40px rgba(0,0,0,0.15);
  min-width: 200px;
  z-index: 9999;
  overflow: hidden;
  padding: 8px 0;
  border: 1px solid #e8f5e9;
}
.user-drop-menu.open { display: block; }
.user-drop-menu a {
  display: flex; align-items: center; gap: 10px;
  padding: 13px 20px;
  font-size: 14px; font-weight: 600;
  color: #333; text-decoration: none;
  transition: background .15s;
}
.user-drop-menu a:hover { background: #f1f8f2; color: #2e7d32; }
.user-drop-menu .dm-divider { height: 1px; background: #f0f0f0; margin: 4px 0; }
.user-drop-menu .dm-logout { color: #e53935; }
.user-drop-menu .dm-logout:hover { background: #ffebee; color: #c62828; }
</style>
</head>

<body data-spy="scroll" data-offset="80">

	<!-- START PRELOADER -->
	<div class="preloader">
		<div class="status">
			<div class="status-mes"></div>
		</div>
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

			<!-- LOGO à gauche -->
			<div class="nav-logo">
				<a href="index.php"><img src="assets/img/logo.png" alt="Takwinibot"></a>
			</div>

			<!-- LIENS au centre -->
			<ul class="nav-links d-none d-xl-flex">
				<li><a href="index.php">Accueil</a></li>
				<li><a href="about.php">À propos</a></li>
				<?php if (isset($_SESSION['user'])): ?>
				<li><a href="formation.php">Formations</a></li>
				<li><a href="gallery.html">Produits</a></li>
				<li><a href="blog.html">Entretiens</a></li>
				<li><a href="offres-emploi/offres-emploi.html">Offres</a></li>
				<li><a href="front_mes_reclamations.php">Réclamations</a></li>
				<?php endif; ?>
			</ul>

			<!-- BOUTON USER à droite -->
			<div class="nav-user d-none d-xl-flex">
				<?php if (isset($_SESSION['user']) && !empty($_SESSION['user']['nom'])): ?>
				<?php
					$_av = $_SESSION['user']['avatar'] ?? '';
					if (!empty($_av)) {
						// avatar stocké comme "uploads/avatars/xxx.png" depuis frontoffice/
						// depuis formations/ on remonte d'un niveau
						$_avatarSrc = '../' . htmlspecialchars($_av);
					} else {
						$_avatarSrc = null;
					}
				?>
				<a href="#" class="user-btn" onclick="toggleUserMenu(event)">
					<span class="u-avatar">
						<?php if ($_avatarSrc): ?>
							<img src="<?= $_avatarSrc ?>" alt="avatar">
						<?php else: ?>
							<svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" fill="white" viewBox="0 0 16 16"><path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4z"/></svg>
						<?php endif; ?>
					</span>
					<span class="u-name">Salut <?= htmlspecialchars($_SESSION['user']['nom']) ?></span>
					<span class="u-caret">&#9660;</span>
				</a>
				<div class="user-drop-menu" id="userDropMenu">
					<a href="../profil.php">👤 &nbsp;Mon profil</a>
					<div class="dm-divider"></div>
					<a href="../../../controller/logout.php" class="dm-logout">🚪 &nbsp;Déconnexion</a>
				</div>
				<?php else: ?>
				<a href="../login.php" style="background:linear-gradient(135deg,#4caf50,#2e7d32);color:#fff;padding:12px 28px;border-radius:50px;font-weight:700;text-decoration:none;font-size:15px;box-shadow:0 4px 15px rgba(76,175,80,0.3);">Se connecter</a>
				<?php endif; ?>
			</div>

			<!-- Mobile toggle -->
			<div class="d-xl-none ml-auto">
				<a href="#" class="site-menu-toggle js-menu-toggle"><span class="icon-menu h3"></span></a>
			</div>

		</div>
	</header>
	<!-- END NAVBAR-->

	<!-- START HOME -->
	<section id="home" class="home_bg"
		style="background-image: url(assets/img/bg/home-bg.jpg);  background-size:cover; background-position: center center;">
		<div class="container">
			<div class="row">
				<div class="col-lg-10 offset-lg-1 col-sm-12 col-xs-12 text-center">
					<div class="hero-text">
						<h2>Best Real state deals</h2>
						<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi fermentum justo vitae
							convallis varius. Nulla tristique risus ut justo pulvinar mattis.</p>
						<div class="home_btn">
							<a href="about.php" class="app-btn wow bounceIn page-scroll home_btn_color_one"
								data-wow-delay=".6s">About us</a>
							<a href="gallery.html" class="app-btn wow bounceIn page-scroll home_btn_color_two"
								data-wow-delay=".8s">our Listing</a>
						</div>
					</div>
				</div><!--- END COL -->
			</div><!--- END ROW -->
		</div><!--- END CONTAINER -->
	</section>
	<!-- END  HOME -->

	<!-- START SEARCH -->
	<div class="search_bar section-padding">
		<div class="container">
			<div class="row">
				<div class="col-lg-3 col-sm-6 col-xs-12">
					<div class="single_search">
						<select>
							<option value="1">Location</option>
							<option value="2">United States</option>
							<option value="3">United Kingdom</option>
							<option value="3">Afghanistan</option>
							<option value="3">Albania</option>
							<option value="3">Australia</option>
							<option value="3">Benin</option>
							<option value="3">Belgium</option>
						</select>
					</div>
				</div><!--- END COL -->
				<div class="col-lg-3 col-sm-6 col-xs-12">
					<div class="single_search">
						<select>
							<option value="1">Category</option>
							<option value="1">Category</option>
							<option value="1">Category</option>
							<option value="1">Category</option>

						</select>
					</div>
				</div><!--- END COL -->
				<div class="col-lg-3 col-sm-6 col-xs-12">
					<div class="single_search">
						<select>
							<option value="1">Property Type</option>
							<option value="1">Residential</option>
							<option value="1">Commercial</option>
							<option value="1">Land</option>
						</select>
					</div>
				</div><!--- END COL -->
				<div class="col-lg-3 col-sm-6 col-xs-12">
					<div class="single_search">
						<select>
							<option value="1">Property Status</option>
							<option value="1">For Sale</option>
							<option value="1">For Rent</option>
						</select>
					</div>
				</div><!--- END COL -->
				<div class="col-lg-3 col-sm-6 col-xs-12">
					<div class="single_search">
						<select>
							<option value="1">Price</option>
							<option value="1">$15000</option>
							<option value="1">$20000</option>
							<option value="1">$25000</option>
							<option value="1">$30000</option>
							<option value="1">$35000</option>
						</select>
					</div>
				</div><!--- END COL -->
				<div class="col-lg-3 col-sm-6 col-xs-12">
					<div class="single_search">
						<select>
							<option value="1">Area</option>
							<option value="1">50</option>
							<option value="1">150</option>
							<option value="1">250</option>
							<option value="1">350</option>
							<option value="1">450</option>
						</select>
					</div>
				</div><!--- END COL -->
				<div class="col-lg-3 col-sm-6 col-xs-12">
					<div class="single_search">
						<select>
							<option value="1">BedRooms</option>
							<option value="1">1</option>
							<option value="1">2</option>
							<option value="1">3</option>
							<option value="1">4</option>
							<option value="1">5</option>
							<option value="1">6</option>
							<option value="1">7</option>
						</select>
					</div>
				</div><!--- END COL -->
				<div class="col-lg-3 col-sm-6 col-xs-12">
					<div class="single_search">
						<select>
							<option value="1">Bathrooms</option>
							<option value="1">1</option>
							<option value="1">2</option>
							<option value="1">3</option>
							<option value="1">4</option>
						</select>
					</div>
				</div><!--- END COL -->
				<div class="col-lg-12 text-center">
					<div class="search_btn">
						<a href="#" class="btn btn-serach-bg">search</a>
					</div>
				</div>
			</div><!--- END ROW -->
		</div><!--- END CONTAINER -->
	</div>
	<!-- END  SEARCH -->

	<!-- START PROPERTY -->
	<section class="template_property">
		<div class="container">
			<div class="section-title text-center wow zoomIn">
				<h2>DerniÃ¨res formations</h2>
				<div></div>
			</div>
			<div class="row">
				<div class="col-lg-4 col-sm-12 col-xs-12">
					<div class="single_property">
						<img src="assets/img/property/1.jpg" class="img-fluid" alt="" />
						<div class="single_property_description text-center">
							<span><i class="fa fa-users"></i> Nombre de places : 20</span>
						</div>
						<div class="single_property_content">
							<h4><a href="#">DÃ©veloppement Web Full Stack</a></h4>
							<p>Cette formation couvre HTML, CSS, JavaScript, Bootstrap et introduction au backend (Node.js / PHP). Vous rÃ©aliserez des projets pratiques comme un site e-commerce et un tableau de bord admin.</p>
						</div>
						<div class="single_property_price">
							Certifiant <span>300 DT</span><br><a href="#" data-toggle="modal" data-target="#inscriptionModal" class="btn btn-serach-bg" style="display:inline-block; margin-top:15px; padding: 5px 20px; font-size: 14px; background-color: #3bafda; color: #fff; border-radius: 4px;">Inscription</a>
							<i class="fa fa-star"></i>
							<i class="fa fa-star"></i>
							<i class="fa fa-star"></i>
							<i class="fa fa-star"></i>
							<i class="fa fa-star"></i>
						</div>
					</div><!--- END SINGLE PROPERTY -->
				</div><!--- END COL -->
				<div class="col-lg-4 col-sm-12 col-xs-12">
					<div class="single_property">
						<img src="assets/img/property/2.jpg" class="img-fluid" alt="" />
						<div class="single_property_description text-center">
							<span><i class="fa fa-users"></i> Nombre de places : 20</span>
						</div>
						<div class="single_property_content">
							<h4><a href="#">DÃ©veloppement Web Full Stack</a></h4>
							<p>Cette formation couvre HTML, CSS, JavaScript, Bootstrap et introduction au backend (Node.js / PHP). Vous rÃ©aliserez des projets pratiques comme un site e-commerce et un tableau de bord admin.</p>
						</div>
						<div class="single_property_price">
							Certifiant <span>300 DT</span><br><a href="#" data-toggle="modal" data-target="#inscriptionModal" class="btn btn-serach-bg" style="display:inline-block; margin-top:15px; padding: 5px 20px; font-size: 14px; background-color: #3bafda; color: #fff; border-radius: 4px;">Inscription</a>
							<i class="fa fa-star"></i>
							<i class="fa fa-star"></i>
							<i class="fa fa-star"></i>
							<i class="fa fa-star"></i>
							<i class="fa fa-star"></i>
						</div>
					</div>
				</div><!--- END  COL-->
				<div class="col-lg-4 col-sm-12 col-xs-12">
					<div class="single_property">
						<img src="assets/img/property/3.jpg" class="img-fluid" alt="" />
						<div class="single_property_description text-center">
							<span><i class="fa fa-users"></i> Nombre de places : 20</span>
						</div>
						<div class="single_property_content">
							<h4><a href="#">DÃ©veloppement Web Full Stack</a></h4>
							<p>Cette formation couvre HTML, CSS, JavaScript, Bootstrap et introduction au backend (Node.js / PHP). Vous rÃ©aliserez des projets pratiques comme un site e-commerce et un tableau de bord admin.</p>
						</div>
						<div class="single_property_price">
							Certifiant <span>300 DT</span><br><a href="#" data-toggle="modal" data-target="#inscriptionModal" class="btn btn-serach-bg" style="display:inline-block; margin-top:15px; padding: 5px 20px; font-size: 14px; background-color: #3bafda; color: #fff; border-radius: 4px;">Inscription</a>
							<i class="fa fa-star"></i>
							<i class="fa fa-star"></i>
							<i class="fa fa-star"></i>
							<i class="fa fa-star"></i>
							<i class="fa fa-star"></i>
						</div>
					</div>
				</div><!--- END  COL-->
			</div><!--- END ROW -->
		</div><!--- END CONTAINER -->
	</section>
	<!-- END  PROPERTY -->

	<!-- START PROPERTY -->
	<section class="template_property section-padding">
		<div class="container">
			<div class="section-title  text-center wow zoomIn">
				<h2>Formations suggÃ©rÃ©es</h2>
				<div></div>
			</div>
			<div class="row">
				<div class="col-lg-4 col-sm-12 col-xs-12">
					<div class="single_property">
						<img src="assets/img/property/4.jpg" class="img-fluid" alt="" />
						<div class="single_property_description text-center">
							<span><i class="fa fa-users"></i> Nombre de places : 20</span>
						</div>
						<div class="single_property_content">
							<h4><a href="#">DÃ©veloppement Web Full Stack</a></h4>
							<p>Cette formation couvre HTML, CSS, JavaScript, Bootstrap et introduction au backend (Node.js / PHP). Vous rÃ©aliserez des projets pratiques comme un site e-commerce et un tableau de bord admin.</p>
						</div>
						<div class="single_property_price">
							Certifiant <span>300 DT</span><br><a href="#" data-toggle="modal" data-target="#inscriptionModal" class="btn btn-serach-bg" style="display:inline-block; margin-top:15px; padding: 5px 20px; font-size: 14px; background-color: #3bafda; color: #fff; border-radius: 4px;">Inscription</a>
							<i class="fa fa-star"></i>
							<i class="fa fa-star"></i>
							<i class="fa fa-star"></i>
							<i class="fa fa-star"></i>
							<i class="fa fa-star"></i>
						</div>
					</div>
				</div><!--- END  COL-->
				<div class="col-lg-4 col-sm-12 col-xs-12">
					<div class="single_property">
						<img src="assets/img/property/5.jpg" class="img-fluid" alt="" />
						<div class="single_property_description text-center">
							<span><i class="fa fa-users"></i> Nombre de places : 20</span>
						</div>
						<div class="single_property_content">
							<h4><a href="#">DÃ©veloppement Web Full Stack</a></h4>
							<p>Cette formation couvre HTML, CSS, JavaScript, Bootstrap et introduction au backend (Node.js / PHP). Vous rÃ©aliserez des projets pratiques comme un site e-commerce et un tableau de bord admin.</p>
						</div>
						<div class="single_property_price">
							Certifiant <span>300 DT</span><br><a href="#" data-toggle="modal" data-target="#inscriptionModal" class="btn btn-serach-bg" style="display:inline-block; margin-top:15px; padding: 5px 20px; font-size: 14px; background-color: #3bafda; color: #fff; border-radius: 4px;">Inscription</a>
							<i class="fa fa-star"></i>
							<i class="fa fa-star"></i>
							<i class="fa fa-star"></i>
							<i class="fa fa-star"></i>
							<i class="fa fa-star"></i>
						</div>
					</div>
				</div><!--- END  COL-->
				<div class="col-lg-4 col-sm-12 col-xs-12">
					<div class="single_property">
						<img src="assets/img/property/6.jpg" class="img-fluid" alt="" />
						<div class="single_property_description text-center">
							<span><i class="fa fa-users"></i> Nombre de places : 20</span>
						</div>
						<div class="single_property_content">
							<h4><a href="#">DÃ©veloppement Web Full Stack</a></h4>
							<p>Cette formation couvre HTML, CSS, JavaScript, Bootstrap et introduction au backend (Node.js / PHP). Vous rÃ©aliserez des projets pratiques comme un site e-commerce et un tableau de bord admin.</p>
						</div>
						<div class="single_property_price">
							Certifiant <span>300 DT</span><br><a href="#" data-toggle="modal" data-target="#inscriptionModal" class="btn btn-serach-bg" style="display:inline-block; margin-top:15px; padding: 5px 20px; font-size: 14px; background-color: #3bafda; color: #fff; border-radius: 4px;">Inscription</a>
							<i class="fa fa-star"></i>
							<i class="fa fa-star"></i>
							<i class="fa fa-star"></i>
							<i class="fa fa-star"></i>
							<i class="fa fa-star"></i>
						</div>
					</div>
				</div><!--- END  COL-->
			</div><!--- END ROW -->
		</div><!--- END CONTAINER -->
	</section>
	<!-- END  PROPERTY -->

	<!-- START PORTFOLIO -->
	<section id="gallery" class="works_area">
		<div class="container">
			<div class="section-title text-center wow zoomIn">
				<h2>Gallery</h2>
				<div></div>
			</div>
			<div class="col-lg-12 text-center">
				<ul class="portfolio-filters">
					<li class="filter active" data-filter="all">all</li>
					<li class="filter" data-filter="bedroom">Bedroom</li>
					<li class="filter" data-filter="bathroom">Bathroom</li>
					<li class="filter" data-filter="kitchen">kitchen</li>
					<li class="filter" data-filter="garage">Garage</li>
					<li class="filter" data-filter="basement">Basement</li>
				</ul>
			</div><!-- END COL -->
			<div class="row portfolio-items-list">
				<div class="col-lg-4 col-sm-4 col-xs-12 mix bathroom kitchen garage">
					<div class="grid">
						<figure class="effect-apollo">
							<img src="assets/img/portfolio/1.jpg" class="img-fluid" alt="" />
							<figcaption>
								<a class="prettyPhoto image_zoom" href="assets/img/portfolio/1.jpg"></a>
								<p><a href="#" data-toggle="modal" data-target="#projectModal">Your Dream House</a></p>
							</figcaption>
						</figure>
					</div>
				</div><!--- END COL -->
				<div class="col-lg-4 col-sm-4 col-xs-12 mix bedroom garage">
					<div class="grid">
						<figure class="effect-apollo">
							<img src="assets/img/portfolio/2.jpg" class="img-fluid" alt="" />
							<figcaption>
								<a class="prettyPhoto image_zoom" href="assets/img/portfolio/2.jpg"></a>
								<p><a href="#" data-toggle="modal" data-target="#projectModal">Your Dream House</a></p>
							</figcaption>
						</figure>
					</div>
				</div><!--- END COL -->
				<div class="col-lg-4 col-sm-4 col-xs-12 mix bathroom">
					<div class="grid">
						<figure class="effect-apollo">
							<img src="assets/img/portfolio/3.jpg" class="img-fluid" alt="" />
							<figcaption>
								<a class="prettyPhoto image_zoom" href="assets/img/portfolio/3.jpg"></a>
								<p><a href="#" data-toggle="modal" data-target="#projectModal">Your Dream House</a></p>
							</figcaption>
						</figure>
					</div>
				</div><!--- END COL -->
				<div class="col-lg-4 col-sm-4 col-xs-12 mix garage kitchen">
					<div class="grid">
						<figure class="effect-apollo">
							<img src="assets/img/portfolio/4.jpg" class="img-fluid" alt="" />
							<figcaption>
								<a class="prettyPhoto image_zoom" href="assets/img/portfolio/4.jpg"></a>
								<p><a href="#" data-toggle="modal" data-target="#projectModal">Your Dream House</a></p>
							</figcaption>
						</figure>
					</div>
				</div><!--- END COL -->
				<div class="col-lg-4 col-sm-4 col-xs-12 mix bedroom">
					<div class="grid">
						<figure class="effect-apollo">
							<img src="assets/img/portfolio/5.jpg" class="img-fluid" alt="" />
							<figcaption>
								<a class="prettyPhoto image_zoom" href="assets/img/portfolio/5.jpg"></a>
								<p><a href="#" data-toggle="modal" data-target="#projectModal">Your Dream House</a></p>
							</figcaption>
						</figure>
					</div>
				</div><!--- END COL -->
				<div class="col-lg-4 col-sm-4 col-xs-12 mix bathroom kitchen">
					<div class="grid">
						<figure class="effect-apollo">
							<img src="assets/img/portfolio/6.jpg" class="img-fluid" alt="" />
							<figcaption>
								<a class="prettyPhoto image_zoom" href="assets/img/portfolio/6.jpg"></a>
								<p><a href="#" data-toggle="modal" data-target="#projectModal">Your Dream House</a></p>
							</figcaption>
						</figure>
					</div>
				</div><!--- END COL -->
				<div class="col-lg-4 col-sm-4 col-xs-12 mix basement garage">
					<div class="grid">
						<figure class="effect-apollo">
							<img src="assets/img/portfolio/7.jpg" class="img-fluid" alt="" />
							<figcaption>
								<a class="prettyPhoto image_zoom" href="assets/img/portfolio/7.jpg"></a>
								<p><a href="#" data-toggle="modal" data-target="#projectModal">Your Dream House</a></p>
							</figcaption>
						</figure>
					</div>
				</div><!--- END COL -->
				<div class="col-lg-4 col-sm-4 col-xs-12 mix bedroom basement">
					<div class="grid">
						<figure class="effect-apollo">
							<img src="assets/img/portfolio/8.jpg" class="img-fluid" alt="" />
							<figcaption>
								<a class="prettyPhoto image_zoom" href="assets/img/portfolio/8.jpg"></a>
								<p><a href="#" data-toggle="modal" data-target="#projectModal">Your Dream House</a></p>
							</figcaption>
						</figure>
					</div>
				</div><!--- END COL -->
				<div class="col-lg-4 col-sm-4 col-xs-12 mix bedroom basement">
					<div class="grid">
						<figure class="effect-apollo">
							<img src="assets/img/portfolio/9.jpg" class="img-fluid" alt="" />
							<figcaption>
								<a class="prettyPhoto image_zoom" href="assets/img/portfolio/9.jpg"></a>
								<p><a href="#" data-toggle="modal" data-target="#projectModal">Your Dream House</a></p>
							</figcaption>
						</figure>
					</div>
				</div><!--- END COL -->
			</div><!--- END ROW -->
		</div><!--- END CONTAINER -->
	</section>
	<!-- END PORTFOLIO -->

		<!-- START TEAM US -->
	<section id="team" class="our_team section-padding">
		<div class="container">
			<div class="section-title text-center wow zoomIn">
				<h2>Professional team</h2>
				<div></div>
			</div>
			<div class="row text-center mb-4">
				<div class="col-lg-4 col-sm-4 col-xs-12 mb-4 mt-4">
					<div class="single_team">
						<img src="assets/img/team/team-1.jpg" class="img-fluid" alt="" />
						<h3>Oumayma Dhahri</h3>
						<p>Co Founder</p>
						<ul class="list-inline">
							<li><a href="#" class="st-facebook"><i class="fa fa-facebook"></i></a></li>
							<li><a href="#" class="st-twitter"><i class="fa fa-instagram"></i></a></li>
							<li><a href="#" class="st-instagram"><i class="fa fa-instagram"></i></a></li>
						</ul>
					</div><!--- END SINGLE TEAM -->
				</div><!--- END COL -->
				<div class="col-lg-4 col-sm-4 col-xs-12 mb-4 mt-4">
					<div class="single_team">
						<img src="assets/img/team/team-2.jpg" class="img-fluid" alt="" />
						<h3>Amen Ourak</h3>
						<p>Co Founder</p>
						<ul class="list-inline">
							<li><a href="#" class="st-facebook"><i class="fa fa-facebook"></i></a></li>
							<li><a href="#" class="st-twitter"><i class="fa fa-instagram"></i></a></li>
							<li><a href="#" class="st-instagram"><i class="fa fa-instagram"></i></a></li>
						</ul>
					</div><!--- END SINGLE TEAM -->
				</div><!--- END COL -->
				<div class="col-lg-4 col-sm-4 col-xs-12 mb-4 mt-4">
					<div class="single_team">
						<img src="assets/img/team/team-3.jpg" class="img-fluid" alt="" />
						<h3>Eya Toumi</h3>
						<p>Co Founder</p>
						<ul class="list-inline">
							<li><a href="#" class="st-facebook"><i class="fa fa-facebook"></i></a></li>
							<li><a href="#" class="st-twitter"><i class="fa fa-instagram"></i></a></li>
							<li><a href="#" class="st-instagram"><i class="fa fa-instagram"></i></a></li>
						</ul>
					</div><!--- END SINGLE TEAM -->
				</div><!--- END COL -->
			</div><!--- END ROW -->
			
			<div class="row text-center">
				<div class="col-lg-4 col-sm-4 col-xs-12 mb-4">
					<div class="single_team">
						<img src="assets/img/team/team-4.jpg" class="img-fluid" alt="" />
						<h3>Yoser Jeribi</h3>
						<p>Co Founder</p>
						<ul class="list-inline">
							<li><a href="#" class="st-facebook"><i class="fa fa-facebook"></i></a></li>
							<li><a href="#" class="st-twitter"><i class="fa fa-instagram"></i></a></li>
							<li><a href="#" class="st-instagram"><i class="fa fa-instagram"></i></a></li>
						</ul>
					</div><!--- END SINGLE TEAM -->
				</div><!--- END COL -->
				<div class="col-lg-4 col-sm-4 col-xs-12 mb-4">
					<div class="single_team">
						<img src="assets/img/team/team-1.jpg" class="img-fluid" alt="" />
						<h3>Fedi Medini</h3>
						<p>Team Member</p>
						<ul class="list-inline">
							<li><a href="#" class="st-facebook"><i class="fa fa-facebook"></i></a></li>
							<li><a href="#" class="st-twitter"><i class="fa fa-instagram"></i></a></li>
							<li><a href="#" class="st-instagram"><i class="fa fa-instagram"></i></a></li>
						</ul>
					</div><!--- END SINGLE TEAM -->
				</div><!--- END COL -->
				<div class="col-lg-4 col-sm-4 col-xs-12 mb-4">
					<div class="single_team">
						<img src="assets/img/team/team-2.jpg" class="img-fluid" alt="" />
						<h3>Slim Housmi</h3>
						<p>Team Member</p>
						<ul class="list-inline">
							<li><a href="#" class="st-facebook"><i class="fa fa-facebook"></i></a></li>
							<li><a href="#" class="st-twitter"><i class="fa fa-instagram"></i></a></li>
							<li><a href="#" class="st-instagram"><i class="fa fa-instagram"></i></a></li>
						</ul>
					</div><!--- END SINGLE TEAM -->
				</div><!--- END COL -->
			</div><!--- END ROW -->
		</div><!--- END CONTAINER -->
	</section>
	<!-- END TEAM US -->

	<!-- START TESTIMONIAL -->
	<section data-stellar-background-ratio="0.3" class="our_testimonial section-padding"
		style="background-image: url(assets/img/bg/testimonial-bg.jpg);  background-size:cover;background-position:center;">
		<div class="container">
			<div class="row">
				<div class="col-lg-8 offset-lg-2 col-sm-12 col-xs-12 text-center">
					<div class="testimonial1-carousel">
						<div class="single-testimonial">
							<img src="assets/img/testimonial/1.jpg" alt="">
							<h4>Mark Richard</h4>
							<span>Architecture</span>
							<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce vitae risus nec dui
								venenatis dignissim. Aenean vitae metus in augue pretium ultrices. Duis dictum eget
								dolor vel blandit.</p>
						</div>
						<div class="single-testimonial">
							<img src="assets/img/testimonial/2.jpg" alt="">
							<h4>Mark Richard</h4>
							<span>Architecture</span>
							<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce vitae risus nec dui
								venenatis dignissim. Aenean vitae metus in augue pretium ultrices. Duis dictum eget
								dolor vel blandit.</p>
						</div>
						<div class="single-testimonial">
							<img src="assets/img/testimonial/3.jpg" alt="">
							<h4>Mark Richard</h4>
							<span>Architecture</span>
							<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce vitae risus nec dui
								venenatis dignissim. Aenean vitae metus in augue pretium ultrices. Duis dictum eget
								dolor vel blandit.</p>
						</div>
					</div>
				</div><!-- END COL -->
			</div><!--END  ROW  -->
		</div><!-- END CONTAINER  -->
	</section>
	<!-- END TESTIMONIAL -->

	<!-- START NEWSLETTER -->
	<section class="newsletter section-padding">
		<div class="container">
			<div class="row">
				<div class="col-lg-12">
					<div class="partner  wow fadeInRight">
						<a href="#"><img src="assets/img/partner/1.png" alt="image"></a>
						<a href="#"><img src="assets/img/partner/2.png" alt="image"></a>
						<a href="#"><img src="assets/img/partner/3.png" alt="image"></a>
						<a href="#"><img src="assets/img/partner/4.png" alt="image"></a>
						<a href="#"><img src="assets/img/partner/5.png" alt="image"></a>
						<a href="#"><img src="assets/img/partner/1.png" alt="image"></a>
						<a href="#"><img src="assets/img/partner/2.png" alt="image"></a>
						<a href="#"><img src="assets/img/partner/3.png" alt="image"></a>
						<a href="#"><img src="assets/img/partner/4.png" alt="image"></a>
						<a href="#"><img src="assets/img/partner/5.png" alt="image"></a>
					</div>
				</div><!-- END COL  -->
			</div><!--END  ROW  -->
			<div class="row">
				<div class="col-lg-6 offset-lg-3 col-sm-12 col-xs-12 text-center">
					<div class="signup_form">
						<h3 class="section-title-white">Subscribe to stay update</h3>
						<!-- Replace the form action in the line below with your MailChimp embed action! -->
						<form novalidate="" class="validate" name="mc-embedded-subscribe-form" method="post" action="#">
							<input type="email" placeholder="Enter Email" id="mce-email" class="form-control"
								name="EMAIL">
							<span><button class="btn btn-detault btn-light-bg" name="subscribe"
									type="submit">Subscribe</button></span>
							<div id="mce-responses">
								<div style="display:none" id="mce-error-response" class="response"></div>
								<div style="display:none" id="mce-success-response" class="response"></div>
							</div>
						</form>
					</div>
				</div><!-- END COL  -->
			</div><!-- END ROW  -->
		</div><!-- END CONTAINER -->
	</section>
	<!-- END NEWSLETTER -->

	<!-- START BLOG -->
	<section id="blog" class="fresh-news section-padding">
		<div class="container">
			<div class="section-title text-center">
				<h2>Latest News</h2>
				<div></div>
			</div>
			<div class="row">
				<div class="col-lg-4 col-sm-4 col-xs-12">
					<div class="single_blog">
						<div class="blog_img">
							<a href="blog.html"><img src="assets/img/blog/blog-1.jpg" class="img-fluid"
									alt="image" /></a>
							<div class="post-date">
								<span class="date">15</span>
								<span class="month">Sep</span>
							</div>
						</div>
						<div class="blog_content">
							<h3><a href="blog.html">Team you want to work with mistake runners</a></h3>
							<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce vitae risus nec dui
								venenatis dignissim.</p>
						</div>
					</div>
				</div><!-- END COL-->
				<div class="col-lg-4 col-sm-4 col-xs-12">
					<div class="single_blog">
						<div class="blog_img">
							<a href="blog.html"><img src="assets/img/blog/blog-2.jpg" class="img-fluid"
									alt="image" /></a>
							<div class="post-date">
								<span class="date">16</span>
								<span class="month">Sep</span>
							</div>
						</div>
						<div class="blog_content">
							<h3><a href="blog.html">Lights winged seasons fish abundantly evening</a></h3>
							<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce vitae risus nec dui
								venenatis dignissim.</p>
						</div>
					</div>
				</div><!-- END COL-->
				<div class="col-lg-4 col-sm-4 col-xs-12">
					<div class="single_blog">
						<div class="blog_img">
							<a href="blog.html"><img src="assets/img/blog/blog-3.jpg" class="img-fluid"
									alt="image" /></a>
							<div class="post-date">
								<span class="date">17</span>
								<span class="month">Sep</span>
							</div>
						</div>
						<div class="blog_content">
							<h3><a href="blog.html">Winged moved stars, food creature seed night</a></h3>
							<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce vitae risus nec dui
								venenatis dignissim.</p>
						</div>
					</div>
				</div><!-- END COL-->
			</div><!-- END ROW -->
		</div><!-- END CONTAINER -->
	</section>
	<!-- END BLOG -->

	<!-- START FOOTER -->
	<footer class="footer-area">
		<div class="container">
			<div class="row">
				<div class="col-lg-12 text-center">
					<div class="footer_social">
						<ul>
							<li><a data-toggle="tooltip" data-placement="top" title="Facebook" href="#"><i
										class="fa fa-facebook"></i></a>
							</li>
							<li><a data-toggle="tooltip" data-placement="top" title="Twitter" href="#"><i
										class="fa fa-instagram"></i></a>
							</li>
							<li><a data-toggle="tooltip" data-placement="top" title="Google Plus" href="#"><i
										class="fa fa-google-plus"></i></a>
							</li>
							<li><a data-toggle="tooltip" data-placement="top" title="Linkedin" href="#"><i
										class="fa fa-linkedin"></i></a>
							</li>
							<li><a data-toggle="tooltip" data-placement="top" title="Youtube" href="#"><i
										class="fa fa-youtube"></i></a>
							</li>
							<li><a data-toggle="tooltip" data-placement="top" title="Skype" href="#"><i
										class="fa fa-skype"></i></a>
							</li>
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
								<li><i class="fa fa-rocket"></i> <span>3481 Melrose Place, Beverly Hills, CA
										90210</span></li>
								<li><i class="fa fa-phone"></i> <span>Call Us - (+1) 517 397 7100</span></li>
								<li><i class="fa fa-fax"></i> <span>Fax - (+12) 123 1234</span></li>
								<li><i class="fa fa-envelope"></i> <span>info@example.com</span></li>
							</ul>
						</div>
					</div>
				</div><!--- END COL -->
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
				</div><!--- END COL -->
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
				</div><!--- END COL -->
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
				</div><!--- END COL -->
			</div><!--- END ROW -->
			<div class="row text-center">
				<div class="col-lg-12 col-sm-12 col-xs-12 wow zoomIn">
					<p class="footer_copyright">Takwinibot &copy; 2026 All Rights Reserved. Distributed by <a
							href="https://themewagon.com" target="_blank">ThemeWagon</a></p>.
				</div><!--- END COL -->
			</div><!--- END ROW -->
		</div><!--- END CONTAINER -->
	</footer>
	<!-- END FOOTER -->

	<!-- Latest jQuery -->
	<script src="assets/js/jquery-1.12.4.min.js"></script>
	<!-- Latest compiled and minified Bootstrap -->
	<script src="assets/bootstrap/js/bootstrap.min.js"></script>
	<!-- modernizer JS -->
	<script src="assets/js/modernizr-2.8.3.min.js"></script>
	<!-- stellar js -->
	<script src="assets/js/jquery.stellar.min.js"></script>
	<!-- Menu js -->
	<script src="assets/js/menu.js"></script>
	<script src="assets/js/jquery.sticky.js"></script>
	<!-- owl-carousel min js  -->
	<script src="assets/owlcarousel/js/owl.carousel.min.js"></script>
	<!-- MAGNIFICANT JS -->
	<script src="assets/js/jquery.magnific-popup.min.js"></script>
	<!-- Slick JS -->
	<script src="assets/js/slick.min.js"></script>
	<!-- jquery mixitup min js -->
	<script src="assets/js/jquery.mixitup.js"></script>
	<!-- jquery.prettyPhoto js -->
	<script src="assets/js/jquery.prettyPhoto.js"></script>
	<!-- scrolltopcontrol js -->
	<script src="assets/js/scrolltopcontrol.js"></script>
	<!-- WOW - Reveal Animations When You Scroll -->
	<script src="assets/js/wow.min.js"></script>
	<!-- scripts js -->
	<script src="assets/js/scripts.js"></script>

	<!-- Modal Inscription -->
	<div class="modal fade" id="inscriptionModal" tabindex="-1" role="dialog" aria-labelledby="inscriptionModalLabel" aria-hidden="true" style="z-index: 99999;">
	  <div class="modal-dialog" role="document">
	    <div class="modal-content">
	      <div class="modal-header">
	        <h5 class="modal-title" id="inscriptionModalLabel">Formulaire d'inscription</h5>
	        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
	          <span aria-hidden="true">&times;</span>
	        </button>
	      </div>
	      <div class="modal-body">
	        <form>
	          <div class="form-group" style="text-align: left;">
	            <label for="cin">CIN</label>
	            <input type="text" class="form-control" id="cin" placeholder="Votre CIN" required>
	          </div>
	          <div class="form-group" style="text-align: left;">
	            <label for="nom">Nom</label>
	            <input type="text" class="form-control" id="nom" placeholder="Votre Nom" required>
	          </div>
	          <div class="form-group" style="text-align: left;">
	            <label for="prenom">PrÃ©nom</label>
	            <input type="text" class="form-control" id="prenom" placeholder="Votre PrÃ©nom" required>
	          </div>
	          <div class="form-group" style="text-align: left;">
	            <label for="email">Gmail</label>
	            <input type="email" class="form-control" id="email" placeholder="Votre adresse Gmail" required>
	          </div>
	        </form>
	      </div>
	      <div class="modal-footer">
	        <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
	        <button type="button" class="btn btn-serach-bg" style="background-color: #3bafda; color: #fff;">S'inscrire</button>
	      </div>
	    </div>
	  </div>
	</div>

</body>

<script>
function toggleUserMenu(e) {
    e.preventDefault();
    e.stopPropagation();
    var menu = document.getElementById('userDropMenu');
    if (menu) menu.classList.toggle('open');
}
document.addEventListener('click', function(e) {
    var menu = document.getElementById('userDropMenu');
    var btn  = document.querySelector('.user-btn');
    if (menu && btn && !btn.contains(e.target)) {
        menu.classList.remove('open');
    }
});
function openProfilModal(e) {
    e.preventDefault();
    document.getElementById('userDropMenu').classList.remove('open');
    document.getElementById('profilModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}
function closeProfilModal() {
    document.getElementById('profilModal').style.display = 'none';
    document.body.style.overflow = '';
}
</script>

<!-- MODAL PROFIL -->
<div id="profilModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:99999;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:24px;width:90%;max-width:500px;max-height:90vh;overflow-y:auto;position:relative;box-shadow:0 20px 60px rgba(0,0,0,0.3);font-family:'Montserrat',sans-serif;">

        <!-- Header vert -->
        <div style="background:linear-gradient(135deg,#43a047,#1b5e20);padding:24px 36px 60px;text-align:center;border-radius:24px 24px 0 0;position:relative;">
            <button onclick="closeProfilModal()" style="position:absolute;top:12px;right:16px;background:rgba(255,255,255,0.2);border:none;color:#fff;font-size:18px;width:30px;height:30px;border-radius:50%;cursor:pointer;">&times;</button>
            <h3 style="color:#fff;font-weight:800;font-size:1.2rem;margin:0;">👤 Mon Profil</h3>
        </div>

        <!-- Avatar -->
        <div style="display:flex;flex-direction:column;align-items:center;margin-top:-44px;padding-bottom:4px;">
            <?php
            $av = $_SESSION['user']['avatar'] ?? '';
            if (!empty($av)) {
                if (strpos($av, 'uploads/avatars/') !== false) $avSrc = $av;
                elseif (strpos($av, 'assets/img/avatars/') !== false) $avSrc = '../' . $av;
                else $avSrc = null;
            } else { $avSrc = null; }
            ?>
            <?php if ($avSrc): ?>
                <img src="<?= htmlspecialchars($avSrc) ?>" style="width:88px;height:88px;border-radius:50%;object-fit:cover;border:4px solid #fff;box-shadow:0 4px 16px rgba(0,0,0,.15);">
            <?php else: ?>
                <div style="width:88px;height:88px;border-radius:50%;background:#2e7d32;border:4px solid #fff;display:flex;align-items:center;justify-content:center;font-size:2rem;font-weight:800;color:#fff;box-shadow:0 4px 16px rgba(0,0,0,.15);">
                    <?= strtoupper(mb_substr($_SESSION['user']['nom'], 0, 1)) ?>
                </div>
            <?php endif; ?>
            <div style="margin-top:10px;font-size:1.05rem;font-weight:700;color:#1a2e1c;"><?= htmlspecialchars($_SESSION['user']['nom']) ?></div>
            <div style="font-size:.82rem;color:#616161;"><?= htmlspecialchars($_SESSION['user']['email']) ?></div>
        </div>

        <!-- Formulaire -->
        <div style="padding:16px 28px 28px;">
            <form method="POST" action="../profil.php">
                <input type="hidden" name="action" value="update">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
                    <div>
                        <label style="font-size:.75rem;font-weight:700;color:#616161;text-transform:uppercase;letter-spacing:.5px;display:block;margin-bottom:5px;">Nom</label>
                        <input type="text" name="nom" required value="<?= htmlspecialchars($_SESSION['user']['nom']) ?>" style="width:100%;padding:10px 14px;border:1.5px solid #e0e0e0;border-radius:10px;font-size:.93rem;background:#f1f8f2;outline:none;" onfocus="this.style.borderColor='#43a047'" onblur="this.style.borderColor='#e0e0e0'">
                    </div>
                    <div>
                        <label style="font-size:.75rem;font-weight:700;color:#616161;text-transform:uppercase;letter-spacing:.5px;display:block;margin-bottom:5px;">Email</label>
                        <input type="email" name="email" required value="<?= htmlspecialchars($_SESSION['user']['email']) ?>" style="width:100%;padding:10px 14px;border:1.5px solid #e0e0e0;border-radius:10px;font-size:.93rem;background:#f1f8f2;outline:none;" onfocus="this.style.borderColor='#43a047'" onblur="this.style.borderColor='#e0e0e0'">
                    </div>
                </div>
                <div style="margin-bottom:16px;">
                    <label style="font-size:.75rem;font-weight:700;color:#616161;text-transform:uppercase;letter-spacing:.5px;display:block;margin-bottom:5px;">Nouveau mot de passe</label>
                    <input type="password" name="password" placeholder="Laisser vide pour ne pas changer" style="width:100%;padding:10px 14px;border:1.5px solid #e0e0e0;border-radius:10px;font-size:.93rem;background:#f1f8f2;outline:none;" onfocus="this.style.borderColor='#43a047'" onblur="this.style.borderColor='#e0e0e0'">
                </div>
                <button type="submit" style="width:100%;padding:13px;background:#2e7d32;color:#fff;border:none;border-radius:10px;font-size:1rem;font-weight:700;cursor:pointer;">Enregistrer</button>
            </form>
        </div>
    </div>
</div>

</html>









