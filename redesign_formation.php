<?php
$path = "C:/xampp2/htdocs/Esprit-PW-2A30-2627-TakwiniBot-gestion_formation/Esprit-PW-2A30-2627-TakwiniBot-gestion_formation/gestion_formation/View/front_office/formations/formation.php";
$content = file_get_contents($path);

// Chercher la section dynamique (celle qu'on a injectée)
$start = strpos($content, '<section class="template_property"');
if ($start === false) {
    // Afficher les sections disponibles pour debug
    preg_match_all('/<section[^>]*>/', $content, $matches);
    die("ERREUR: Sections trouvees: " . implode(" | ", $matches[0]));
}
$end = strpos($content, '</section>', $start) + strlen('</section>');

$newSection = '
<section class="template_property" style="padding:60px 0;background:#eaf4fb;">
    <div class="container">
        <div class="section-title text-center" style="margin-bottom:40px;">
            <h2 style="font-size:2rem;font-weight:800;color:#1a2e1c;">Derni&egrave;res Formations</h2>
            <div style="width:60px;height:3px;background:#22c55e;margin:12px auto 0;border-radius:2px;"></div>
        </div>
        <?php if(!empty($insc_success)): ?>
        <div style="background:#e8f5e9;color:#2e7d32;border-left:4px solid #4caf50;padding:14px 20px;border-radius:10px;margin-bottom:24px;font-weight:600;">
            &check; <?= htmlspecialchars($insc_success) ?>
        </div>
        <?php endif; ?>
        <?php if(!empty($insc_error)): ?>
        <div style="background:#fde8e8;color:#c0392b;border-left:4px solid #e53935;padding:14px 20px;border-radius:10px;margin-bottom:24px;font-weight:600;">
            <?= htmlspecialchars($insc_error) ?>
        </div>
        <?php endif; ?>
        <div class="row">
        <?php if(empty($formations)): ?>
            <div class="col-12 text-center"><p style="color:#888;font-size:16px;">Aucune formation disponible.</p></div>
        <?php else: foreach($formations as $f):
            $imgs = ["assets/img/property/1.jpg","assets/img/property/2.jpg","assets/img/property/3.jpg","assets/img/property/4.jpg","assets/img/property/5.jpg","assets/img/property/6.jpg"];
            $img = $imgs[$f["id"] % count($imgs)];
        ?>
            <div class="col-md-4 col-sm-6 col-xs-12" style="margin-bottom:30px;">
                <div class="property-item" style="background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,.08);">
                    <div style="position:relative;overflow:hidden;height:200px;">
                        <img src="<?= $img ?>" alt="<?= htmlspecialchars($f[\'titre\']) ?>" style="width:100%;height:100%;object-fit:cover;">
                        <div style="position:absolute;bottom:0;left:50%;transform:translateX(-50%);background:rgba(255,255,255,.92);padding:10px 20px;border-radius:4px;text-align:center;width:80%;">
                            <span style="font-size:13px;font-weight:700;color:#1a2e1c;"><?= htmlspecialchars($f[\'titre\']) ?></span>
                        </div>
                    </div>
                    <div style="background:#22c55e;padding:10px 16px;text-align:center;">
                        <span style="color:#fff;font-weight:700;font-size:14px;">Niveau : <?= htmlspecialchars($f[\'niveau\'] ?? \'N/A\') ?></span>
                    </div>
                    <div style="padding:20px 24px;">
                        <h4 style="font-size:1.2rem;font-weight:800;color:#1a2e1c;margin-bottom:4px;"><?= htmlspecialchars($f[\'titre\']) ?></h4>
                        <p style="color:#888;font-size:13px;margin-bottom:10px;line-height:1.5;"><?= htmlspecialchars(substr($f[\'description\'] ?? \'\', 0, 80)) ?>...</p>
                        <p style="color:#555;font-size:14px;margin-bottom:4px;"><?= htmlspecialchars($f[\'duree\'] ?? \'\') ?></p>
                        <p style="font-weight:800;font-size:1.1rem;color:#22c55e;margin-bottom:16px;">
                            <?= $f[\'prix\'] > 0 ? number_format($f[\'prix\'],2).\' TND\' : \'<span style="color:#16a34a;">Gratuit</span>\' ?>
                        </p>
                        <div style="display:flex;align-items:center;gap:12px;">
                            <button onclick="ouvrirModalInscription(<?= (int)$f[\'id\'] ?>, \'<?= addslashes(htmlspecialchars($f[\'titre\'])) ?>\')"
                                style="background:#3bafda;color:#fff;border:none;padding:10px 22px;border-radius:6px;font-size:14px;font-weight:700;cursor:pointer;">
                                Inscription
                            </button>
                            <span style="color:#f5a623;font-size:18px;">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; endif; ?>
        </div>
    </div>
</section>

<!-- MODAL INSCRIPTION -->
<div class="modal fade" id="inscriptionModal" tabindex="-1" role="dialog" aria-hidden="true" style="z-index:99999;">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">INSCRIPTION - <span id="modal-titre-nom"></span></h5>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <div class="modal-body">
        <form method="POST" action="formation.php">
          <input type="hidden" name="action" value="inscrire">
          <input type="hidden" name="formation_id" id="modal-formation-id">
          <div class="form-group">
            <label>ID Utilisateur <span style="color:red;">*</span></label>
            <input type="number" class="form-control" name="user_id" placeholder="Votre ID utilisateur" value="<?= isset($user) && $user ? (int)$user[\'id\'] : \'\' ?>" required min="1">
            <small class="form-text text-muted">Entrez votre identifiant utilisateur</small>
          </div>
          <div class="form-group">
            <label>Nom <span style="color:red;">*</span></label>
            <input type="text" class="form-control" name="nom" placeholder="Votre nom" value="<?= isset($user) && $user ? htmlspecialchars($user[\'nom\']) : \'\' ?>" required>
          </div>
          <div class="form-group">
            <label>Pr&eacute;nom <span style="color:red;">*</span></label>
            <input type="text" class="form-control" name="prenom" placeholder="Votre pr&eacute;nom" value="<?= isset($user) && $user ? htmlspecialchars($user[\'prenom\'] ?? \'\') : \'\' ?>">
          </div>
          <div class="form-group">
            <label>Email <span style="color:red;">*</span></label>
            <input type="email" class="form-control" name="email" placeholder="votre.email@exemple.com" value="<?= isset($user) && $user ? htmlspecialchars($user[\'email\']) : \'\' ?>" required>
          </div>
          <div class="form-group">
            <label>Niveau</label>
            <input type="text" class="form-control" name="niveau" placeholder="Ex: D&eacute;butant, Interm&eacute;diaire, Avanc&eacute;">
          </div>
          <div class="form-group">
            <label>Mode de formation</label>
            <select class="form-control" name="mode_formation">
              <option value="">-- S&eacute;lectionner --</option>
              <option value="En ligne">En ligne</option>
              <option value="Presentiel">Pr&eacute;sentiel</option>
              <option value="Hybride">Hybride</option>
            </select>
          </div>
          <?php if(!isset($user) || !$user): ?>
          <div style="background:#fff8e1;color:#f57f17;border-left:3px solid #ffc107;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:14px;">
            Vous devez etre connecte pour vous inscrire.
          </div>
          <?php endif; ?>
          <button type="submit" class="btn btn-primary btn-block" <?= (!isset($user) || $user === null) ? \'disabled\' : \'\' ?>>Confirmer l\'inscription</button>
        </form>
      </div>
    </div>
  </div>
</div>
<script>
function ouvrirModalInscription(id, titre) {
    document.getElementById("modal-formation-id").value = id;
    document.getElementById("modal-titre-nom").textContent = titre.toUpperCase();
    $("#inscriptionModal").modal("show");
}
</script>
';

$newContent = substr($content, 0, $start) . $newSection . substr($content, $end);
$result = file_put_contents($path, $newContent);
echo $result !== false ? "OK: design mis a jour! ($result bytes ecrits)" : "ERREUR: impossible d ecrire le fichier";
