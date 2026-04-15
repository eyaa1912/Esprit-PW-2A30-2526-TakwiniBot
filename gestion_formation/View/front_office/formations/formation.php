<?php
require_once __DIR__ . '/../../../Controller/FormationController.php';
$fc = new FormationController();
$formations = $fc->listFormations()->fetchAll();
$images = ['assets/img/property/1.jpg','assets/img/property/2.jpg','assets/img/property/3.jpg',
           'assets/img/property/4.jpg','assets/img/property/5.jpg','assets/img/property/6.jpg'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Takwinibot - Formations</title>
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
</head>
<body data-spy="scroll" data-offset="80">

  <!-- PRELOADER -->
  <div class="preloader"><div class="status"><div class="status-mes"></div></div></div>

  <!-- NAVBAR -->
  <div class="site-mobile-menu site-navbar-target">
    <div class="site-mobile-menu-header">
      <div class="site-mobile-menu-close mt-3"><span class="icon-close2 js-menu-toggle"></span></div>
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
              <li><a href="index.html" class="nav-link">Home</a></li>
              <li><a class="nav-link" href="about.html">About</a></li>
              <li class="has-children">
                <a href="formation.php" class="nav-link">Formations</a>
                <ul class="dropdown">
                  <li><a href="formation-details.php" class="nav-link">Détails de la Formation</a></li>
                </ul>
              </li>
              <li><a href="offres-emploi/offres-emploi.html" class="nav-link">Offres</a></li>
              <li class="nav-reclamation-login"><a class="nav-link" href="front_mes_reclamations.html">Réclamations</a><a href="Modern-Login-master/login.html" class="login-pill">Se connecter</a></li>
            </ul>
          </nav>
        </div>
        <div class="col-6 d-inline-block d-xl-none ml-md-0 py-3" style="position:relative;top:3px;">
          <a href="#" class="site-menu-toggle js-menu-toggle float-right"><span class="icon-menu h3"></span></a>
        </div>
      </div>
    </div>
  </header>
  <!-- END NAVBAR -->

  <!-- SECTION TOP -->
  <section class="section-top">
    <div class="container">
      <div class="col-lg-10 offset-lg-1 col-xs-12 text-center">
        <div class="section-top-title wow fadeInRight" data-wow-duration="1s" data-wow-delay="0.3s" data-wow-offset="0">
          <h1>Formations</h1>
        </div>
      </div>
    </div>
  </section>

  <!-- FORMATIONS DEPUIS LA BDD -->
  <section class="template_property">
    <div class="container">
      <div class="section-title text-center wow zoomIn">
        <h2>Derni&egrave;res formations</h2>
        <div></div>
      </div>
      <div class="row">
        <?php if (empty($formations)): ?>
          <div class="col-12 text-center py-5">
            <p class="text-muted">Aucune formation disponible pour le moment.</p>
          </div>
        <?php else: ?>
          <?php foreach ($formations as $i => $f): ?>
          <?php
            // Chercher l'image basée sur le titre de la formation
            $titreClean = strtolower(preg_replace('/[^a-z0-9]/i', '', $f['titre'])); // Enlever espaces et caractères spéciaux
            $imgPath = null;
            
            // Extensions possibles
            $extensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            foreach ($extensions as $ext) {
              if (file_exists(__DIR__ . "/img/{$titreClean}.{$ext}")) {
                $imgPath = "img/{$titreClean}.{$ext}";
                break;
              }
            }
            
            // Si pas d'image trouvée, utiliser une image par défaut
            if (!$imgPath) {
              $imgPath = $images[$i % count($images)];
            }
          ?>
          <div class="col-md-4 col-sm-12 col-xs-12">
            <div class="single_property">
              <img src="<?= $imgPath ?>" class="img-fluid" alt="<?= htmlspecialchars($f['titre']) ?>" />
              <div class="single_property_description text-center">
                <span><i class="fa fa-graduation-cap"></i> Niveau : <?= htmlspecialchars($f['niveau']) ?></span>
              </div>
              <div class="single_property_content">
                <h4><a href="formation-details.php?id=<?= $f['id'] ?>"><?= htmlspecialchars($f['titre']) ?></a></h4>
                <p><?= htmlspecialchars(mb_strimwidth($f['description'], 0, 120, '...')) ?></p>
              </div>
              <div class="single_property_price">
                <?= htmlspecialchars($f['duree']) ?>
                <span><?= $f['prix'] > 0 ? number_format($f['prix'], 2).' TND' : 'Gratuit' ?></span>
                <br>
                <a href="#" data-toggle="modal" data-target="#inscriptionModal<?= $f['id'] ?>"
                   class="btn btn-serach-bg"
                   style="display:inline-block;margin-top:15px;padding:5px 20px;font-size:14px;background-color:#3bafda;color:#fff;border-radius:4px;">
                  Inscription
                </a>
                <i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i>
                <i class="fa fa-star"></i><i class="fa fa-star"></i>
              </div>
            </div>
          </div>
          <!-- Modal Inscription pour cette formation -->
          <div class="modal fade" id="inscriptionModal<?= $f['id'] ?>" tabindex="-1" role="dialog" aria-hidden="true" style="z-index:99999;">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title">Inscription - <?= htmlspecialchars($f['titre']) ?></h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body">
                  <form>
                    <input type="hidden" name="formation_id" value="<?= $f['id'] ?>">
                    <div class="form-group" style="text-align:left;">
                      <label>CIN</label>
                      <input type="text" class="form-control" placeholder="Votre CIN" required>
                    </div>
                    <div class="form-group" style="text-align:left;">
                      <label>Nom</label>
                      <input type="text" class="form-control" placeholder="Votre Nom" required>
                    </div>
                    <div class="form-group" style="text-align:left;">
                      <label>Pr&eacute;nom</label>
                      <input type="text" class="form-control" placeholder="Votre Pr&eacute;nom" required>
                    </div>
                    <div class="form-group" style="text-align:left;">
                      <label>Email</label>
                      <input type="email" class="form-control" placeholder="Votre adresse email" required>
                    </div>
                  </form>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                  <button type="button" class="btn btn-serach-bg" style="background-color:#3bafda;color:#fff;">S'inscrire</button>
                </div>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- FOOTER -->
  <footer class="footer-area">
    <div class="container">
      <div class="row footer-padding">
        <div class="col-md-3 col-sm-3 col-xs-12">
          <div class="single_footer">
            <h4>Contact Us</h4>
            <div class="footer_contact">
              <ul>
                <li><i class="fa fa-rocket"></i> <span>Tunis, Tunisie</span></li>
                <li><i class="fa fa-phone"></i> <span>(+216) 71 000 000</span></li>
                <li><i class="fa fa-envelope"></i> <span>info@takwini.tn</span></li>
              </ul>
            </div>
          </div>
        </div>
        <div class="col-md-3 col-sm-3 col-xs-12">
          <div class="single_footer">
            <h4>Formations</h4>
            <div class="footer_contact">
              <ul>
                <li><a href="formation.php">Toutes les formations</a></li>
                <li><a href="front_mes_reclamations.html">R&eacute;clamations</a></li>
              </ul>
            </div>
          </div>
        </div>
      </div>
      <div class="row text-center">
        <div class="col-md-12 wow zoomIn">
          <p class="footer_copyright">Takwinibot &copy; <?php echo date('Y'); ?> All Rights Reserved.</p>
        </div>
      </div>
    </div>
  </footer>

  <!-- JS -->
  <script src="assets/js/jquery-1.12.4.min.js"></script>
  <script src="assets/bootstrap/js/bootstrap.min.js"></script>
  <script src="assets/js/modernizr-2.8.3.min.js"></script>
  <script src="assets/owlcarousel/js/owl.carousel.min.js"></script>
  <script src="assets/js/menu.js"></script>
  <script src="assets/js/jquery.sticky.js"></script>
  <script src="assets/js/scrolltopcontrol.js"></script>
  <script src="assets/js/wow.min.js"></script>
  <script src="assets/js/scripts.js"></script>
</body>
</html>
