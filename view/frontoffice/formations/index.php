<!DOCTYPE html>
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
	<?php
	session_start();
	// Logout
	if (isset($_GET['logout'])) {
	    session_destroy();
	    header('Location: /gestion_utilisateur_v5/gestion_utilisateur1/view/frontoffice/formations/index.php');
	    exit;
	}
	$user      = $_SESSION['user'] ?? null;
	$role      = $user['role'] ?? 'visiteur';
	$nom       = $user ? htmlspecialchars($user['nom']) : '';
	$loginUrl  = '/gestion_utilisateur_v5/gestion_utilisateur1/view/frontoffice/login.php';
	$logoutUrl = '/gestion_utilisateur_v5/gestion_utilisateur1/controller/logout.php';
	?>
	<style>
	.tk-nav{background:#fff;box-shadow:0 2px 16px rgba(0,0,0,.08);position:sticky;top:0;z-index:9999;width:100%;}
	.tk-nav-inner{display:flex;align-items:center;justify-content:space-between;padding:14px 48px;}
	.tk-logo img{height:90px;width:auto;}
	.tk-links{display:flex;align-items:center;gap:6px;list-style:none;margin:0;padding:0;width:100%;}
	.tk-links li a{color:#444;text-decoration:none;font-size:15px;font-weight:600;padding:10px 18px;border-radius:8px;transition:all .2s;white-space:nowrap;display:block;}
	.tk-links li a:hover{background:#e8f5e9;color:#2e7d32;}
	.tk-links .spacer{flex:1;}
	.tk-btn-connect{background:linear-gradient(135deg,#4caf50,#2e7d32)!important;color:#fff!important;padding:12px 28px!important;border-radius:50px!important;font-weight:700!important;font-size:15px!important;box-shadow:0 4px 14px rgba(76,175,80,.3);}
	.tk-btn-connect:hover{transform:translateY(-2px);}
	.tk-user-wrap{position:relative;}
	.tk-user-btn{display:inline-flex;align-items:center;gap:10px;background:#4caf50;color:#fff;padding:9px 20px 9px 9px;border-radius:50px;font-weight:700;font-size:15px;cursor:pointer;margin-left:8px;}
	.tk-user-btn .av{width:42px;height:42px;border-radius:50%;object-fit:cover;border:2px solid #fff;}
	.tk-user-btn .av-init{width:42px;height:42px;border-radius:50%;background:#2e7d32;border:2px solid #fff;display:inline-flex;align-items:center;justify-content:center;font-size:18px;font-weight:800;color:#fff;}
	.tk-dropdown{display:none;position:absolute;top:calc(100% + 8px);right:0;background:#fff;border-radius:12px;box-shadow:0 8px 30px rgba(0,0,0,.12);min-width:180px;overflow:hidden;z-index:9999;}
	.tk-dropdown a{display:block;padding:14px 20px;color:#333;text-decoration:none;font-size:14px;font-weight:500;transition:background .2s;}
	.tk-dropdown a:hover{background:#f5f5f5;}
	.tk-dropdown a.logout{color:#e53935;}
	.tk-user-wrap.open .tk-dropdown{display:block;}
	</style>
	<nav class="tk-nav">
		<div class="tk-nav-inner">
			<a href="index.php" class="tk-logo"><img src="assets/img/logo.png" alt="Takwinibot"></a>
			<ul class="tk-links">
				<!-- Spacer gauche -->
				<li class="spacer"></li>
				<!-- Accueil et A propos au centre -->
				<li><a href="index.php">Accueil</a></li>
				<li><a href="about.html">A propos</a></li>
				<!-- Spacer droit -->
				<li class="spacer"></li>
				<?php if (in_array($role, ['candidat','recruteur','admin'])): ?>
				<li><a href="offres-emploi/offres-emploi.html">Offres</a></li>
				<?php endif; ?>
				<?php if (in_array($role, ['candidat','admin'])): ?>
				<li><a href="formation.html">Formations</a></li>
				<?php endif; ?>
				<?php if (in_array($role, ['candidat','recruteur','admin'])): ?>
				<li><a href="front_mes_reclamations.html">Reclamations</a></li>
				<li><a href="gallery.html">Produits</a></li>
				<?php endif; ?>
				<?php if (in_array($role, ['candidat','admin'])): ?>
				<li><a href="entretiens.html">Entretiens</a></li>
				<?php endif; ?>
				<?php if ($role === 'admin'): ?>
				<li>
					<a href="/gestion_utilisateur_v5/gestion_utilisateur1/view/backoffice/sneat-plateforme-finale/sneat-final/html/index.php"
					   style="background:#e8f5e9;color:#2e7d32;border-radius:8px;font-weight:700;">
						← Backoffice
					</a>
				</li>
				<?php endif; ?>
				<?php if ($user): ?>
				<li class="tk-user-wrap">
					<div class="tk-user-btn">
						<?php $av=$user['avatar']??''; $avSrc=!empty($av)?'../'.$av:null; ?>
						<?php if($avSrc): ?><img src="<?=htmlspecialchars($avSrc)?>" class="av"><?php else: ?><span class="av-init"><?=strtoupper(substr($nom,0,1))?></span><?php endif; ?>
						Salut <?=$nom?> <span style="font-size:10px;">&#9660;</span>
					</div>
					<div class="tk-dropdown">
						<a href="../profil.php">Mon profil</a>
						<a href="/gestion_utilisateur_v5/gestion_utilisateur1/view/frontoffice/formations/index.php?logout=1" class="logout" id="btn-logout">Deconnexion</a>
					</div>
				</li>
				<?php else: ?>
				<li><a href="<?=$loginUrl?>" class="tk-btn-connect">Se connecter</a></li>
				<?php endif; ?>
			</ul>
		</div>
	</nav>
	<script>
	// Dropdown au clic
	document.querySelector('.tk-user-btn') && document.querySelector('.tk-user-btn').addEventListener('click', function(e){
	    e.stopPropagation();
	    this.closest('.tk-user-wrap').classList.toggle('open');
	});
	document.addEventListener('click', function(){
	    var w = document.querySelector('.tk-user-wrap');
	    if(w) w.classList.remove('open');
	});
	</script>
	<!-- END NAVBAR -->

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
							<a href="about.html" class="app-btn wow bounceIn page-scroll home_btn_color_one"
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

</html>










