<?php
require_once __DIR__ . "/../../../Controller/FormationController.php";
$fc = new FormationController();

// R?cup?rer l ID de la formation depuis l URL
$formationId = isset($_GET["id"]) ? (int)$_GET["id"] : 0;

if ($formationId > 0) {
    $formation = $fc->getFormation($formationId);
    if (!$formation) {
        header("Location: formation.php");
        exit;
    }
} else {
    header("Location: formation.php");
    exit;
}

// Chercher l image
$titreClean = strtolower(preg_replace("/[^a-z0-9]/i", "", $formation["titre"]));
$imgPath = "assets/img/2.jpg"; // Image par d?faut
$extensions = ["jpg", "jpeg", "png", "webp", "gif"];
foreach ($extensions as $ext) {
    if (file_exists(__DIR__ . "/img/{$titreClean}.{$ext}")) {
        $imgPath = "img/{$titreClean}.{$ext}";
        break;
    }
}

// Charger 2 formations similaires
$allFormations = $fc->listFormations()->fetchAll();
$similaires = array_filter($allFormations, fn($f) => $f["id"] != $formationId);
$similaires = array_slice($similaires, 0, 2);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($formation["titre"]) ?> - Takwinibot</title>
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

  <div class="preloader"><div class="status"><div class="status-mes"></div></div></div>

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
                  <li><a href="formation-details.php" class="nav-link">D&eacute;tails de la Formation</a></li>
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

  <section class="section-top">
    <div class="container">
      <div class="col-lg-10 offset-lg-1 col-xs-12 text-center">
        <div class="section-top-title wow fadeInRight" data-wow-duration="1s" data-wow-delay="0.3s" data-wow-offset="0">
          <h1><?= htmlspecialchars($formation["titre"]) ?></h1>
        </div>
      </div>
    </div>
  </section>

  <section class="property_single_details section-padding">
    <div class="container">
      <div class="row">
        <div class="col-md-9 col-sm-9 col-xs-12">
          <div class="property_single_details_slide">
            <img src="<?= $imgPath ?>" class="img-fluid" alt="<?= htmlspecialchars($formation["titre"]) ?>">
          </div>
          <div class="property_single_details_price">
            <h1><?= htmlspecialchars($formation["titre"]) ?></h1>
            <h4><?= $formation["prix"] > 0 ? number_format($formation["prix"], 2)." TND" : "Gratuit" ?></h4>
            <p>Dur&eacute;e : <?= htmlspecialchars($formation["duree"]) ?></p>
            <ul>
              <li><i class="fa fa-check"></i> Niveau : <?= htmlspecialchars($formation["niveau"]) ?></li>
              <li><i class="fa fa-check"></i> Certifiant</li>
              <li><i class="fa fa-check"></i> Support en ligne</li>
            </ul>
          </div>
          <div class="property_single_details_description">
            <h4>Description de la formation</h4>
            <p><?= nl2br(htmlspecialchars($formation["description"])) ?></p>
          </div>
          
          <?php if (!empty($similaires)): ?>
          <div class="single_similar_property">
            <h4>Formations similaires</h4>
            <div class="row">
              <?php foreach ($similaires as $i => $sim): ?>
              <?php
                $simTitreClean = strtolower(preg_replace("/[^a-z0-9]/i", "", $sim["titre"]));
                $simImg = "assets/img/property/" . (($i % 6) + 1) . ".jpg";
                foreach ($extensions as $ext) {
                    if (file_exists(__DIR__ . "/img/{$simTitreClean}.{$ext}")) {
                        $simImg = "img/{$simTitreClean}.{$ext}";
                        break;
                    }
                }
              ?>
              <div class="col-md-6 col-sm-6 col-xs-12">
                <div class="single_property">
                  <img src="<?= $simImg ?>" class="img-fluid" alt="<?= htmlspecialchars($sim["titre"]) ?>" />
                  <div class="single_property_description text-center">
                    <span><i class="fa fa-graduation-cap"></i> Niveau : <?= htmlspecialchars($sim["niveau"]) ?></span>
                  </div>
                  <div class="single_property_content">
                    <h4><a href="formation-details.php?id=<?= $sim["id"] ?>"><?= htmlspecialchars($sim["titre"]) ?></a></h4>
                    <p><?= htmlspecialchars(mb_strimwidth($sim["description"], 0, 100, "...")) ?></p>
                  </div>
                  <div class="single_property_price">
                    <?= htmlspecialchars($sim["duree"]) ?> <span><?= $sim["prix"] > 0 ? number_format($sim["prix"], 2)." TND" : "Gratuit" ?></span>
                    <br><a href="formation-details.php?id=<?= $sim["id"] ?>" class="btn btn-serach-bg" style="display:inline-block;margin-top:15px;padding:5px 20px;font-size:14px;background-color:#3bafda;color:#fff;border-radius:4px;">Voir d&eacute;tails</a>
                  </div>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
          <?php endif; ?>
        </div>
        
        <div class="col-md-3 col-sm-3 col-xs-12">
          <div class="single_property_form">
            <h4>S inscrire &agrave; cette formation</h4>
            <form class="form" method="post" action="inscription.php">
              <input type="hidden" name="formation_id" value="<?= $formation["id"] ?>">
              <div class="row">
                <div class="form-group col-md-12">
                  <input type="text" name="cin" class="form-control" placeholder="CIN" required>
                </div>
                <div class="form-group col-md-12">
                  <input type="text" name="nom" class="form-control" placeholder="Nom" required>
                </div>
                <div class="form-group col-md-12">
                  <input type="text" name="prenom" class="form-control" placeholder="Pr&eacute;nom" required>
                </div>
                <div class="form-group col-md-12">
                  <input type="email" name="email" class="form-control" placeholder="Email" required>
                </div>
                <div class="form-group col-md-12">
                  <input type="text" name="phone" class="form-control" placeholder="T&eacute;l&eacute;phone" required>
                </div>
                <div class="col-md-12">
                  <div class="actions">
                    <input type="submit" value="S inscrire" name="submit" class="btn btn-lg btn-contact-bg" />
                  </div>
                </div>
              </div>
            </form>
          </div>
          <div class="single_property_form_agent">
            <div class="single_property_form_agent_profile">
              <h4>Informations</h4>
              <p><i class="fa fa-phone"></i> (+216) 71 000 000</p>
              <p><i class="fa fa-envelope"></i> info@takwini.tn</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <footer class="footer-area">
    <div class="container">
      <div class="row text-center">
        <div class="col-md-12 wow zoomIn">
          <p class="footer_copyright">Takwinibot &copy; <?php echo date("Y"); ?> All Rights Reserved.</p>
        </div>
      </div>
    </div>
  </footer>

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
