<?php
date_default_timezone_set('Africa/Tunis');
// Récupérer les notifications non lues
if (!isset($db)) {
    require_once __DIR__ . '/../../../../../config.php';
    $db = config::getConnexion();
}

// Marquer comme lu via AJAX
if (isset($_GET['notif_lu'])) {
    $db->prepare('UPDATE notifications SET lu = 1 WHERE id = :id')->execute(['id' => (int)$_GET['notif_lu']]);
    echo json_encode(['ok' => true]);
    exit;
}
if (isset($_GET['notif_all_lu'])) {
    $db->prepare('UPDATE notifications SET lu = 1')->execute();
    echo json_encode(['ok' => true]);
    exit;
}

$notifs     = $db->query('SELECT * FROM notifications ORDER BY created_at DESC LIMIT 10')->fetchAll();
$nonLues    = $db->query('SELECT COUNT(*) FROM notifications WHERE lu = 0')->fetchColumn();

// Icône par type
function notifIcon($type) {
    return match($type) {
        'recruteur' => ['icon' => 'bx-briefcase', 'color' => 'bg-label-warning'],
        'candidat'  => ['icon' => 'bx-user-check', 'color' => 'bg-label-success'],
        'offre'     => ['icon' => 'bx-briefcase-alt', 'color' => 'bg-label-info'],
        'reclamation' => ['icon' => 'bx-error', 'color' => 'bg-label-danger'],
        default     => ['icon' => 'bx-bell', 'color' => 'bg-label-primary'],
    };
}

// Temps relatif
function tempsRelatif($date) {
    $now  = new DateTime('now', new DateTimeZone('Africa/Tunis'));
    $past = new DateTime($date, new DateTimeZone('Africa/Tunis'));
    $diff = $now->getTimestamp() - $past->getTimestamp();
    if ($diff < 60)    return 'à l\'instant';
    if ($diff < 3600)  return 'il y a ' . floor($diff/60) . ' min';
    if ($diff < 86400) return 'il y a ' . floor($diff/3600) . ' h';
    return 'il y a ' . floor($diff/86400) . ' j';
}
?>
<li class="nav-item dropdown-notifications navbar-dropdown dropdown me-2 me-xl-1">
    <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown" aria-expanded="false">
        <span class="position-relative">
            <i class="icon-base bx bx-bell icon-md"></i>
            <?php if ($nonLues > 0): ?>
            <span class="badge-notifications position-absolute" style="width:9px;height:9px;background:#ff3e1d;border-radius:50%;border:2px solid #fff;top:2px;right:-1px;"></span>
            <?php endif; ?>
        </span>
    </a>
    <ul class="dropdown-menu dropdown-menu-end" style="width:360px;max-height:480px;overflow-y:auto;">
        <li class="dropdown-menu-header border-bottom">
            <div class="d-flex align-items-center justify-content-between px-3 py-3">
                <h6 class="mb-0">Notifications</h6>
                <?php if ($nonLues > 0): ?>
                <span class="badge bg-label-primary rounded-pill notif-badge-count"><?= $nonLues ?> nouvelles</span>
                <?php endif; ?>
            </div>
        </li>
        <li class="list-group list-group-flush">
            <?php if (empty($notifs)): ?>
            <div class="text-center text-muted py-4" style="font-size:13px;">Aucune notification</div>
            <?php endif; ?>
            <?php foreach ($notifs as $n):
                $ic = notifIcon($n['type']);
            ?>
            <a href="javascript:void(0);"
               onclick="markNotifRead(<?= $n['id'] ?>, '<?= htmlspecialchars($n['lien'] ?? '') ?>')"
               class="list-group-item list-group-item-action dropdown-notifications-item <?= $n['lu'] ? '' : 'notif-unread fw-semibold' ?>"
               style="<?= $n['lu'] ? 'opacity:.7;' : '' ?>">
                <div class="d-flex align-items-start gap-3 py-1">
                    <div class="flex-shrink-0">
                        <div class="avatar avatar-sm <?= $ic['color'] ?>">
                            <i class="icon-base bx <?= $ic['icon'] ?> icon-sm"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <p class="mb-0" style="font-size:13px;"><?= htmlspecialchars($n['titre']) ?></p>
                        <small class="text-body-secondary"><?= htmlspecialchars($n['message']) ?></small><br>
                        <small class="text-body-secondary" style="font-size:11px;"><?= tempsRelatif($n['created_at']) ?></small>
                    </div>
                    <?php if (!$n['lu']): ?>
                    <div class="flex-shrink-0" id="notif-dot-<?= $n['id'] ?>">
                        <span class="notif-dot" style="width:8px;height:8px;background:#696cff;border-radius:50%;display:inline-block;margin-top:4px;"></span>
                    </div>
                    <?php endif; ?>
                </div>
            </a>
            <?php endforeach; ?>
        </li>
        <?php if ($nonLues > 0): ?>
        <li class="dropdown-menu-footer border-top">
            <a href="javascript:void(0);" onclick="markAllNotifRead()" class="dropdown-item d-flex justify-content-center text-primary py-2" style="font-size:13px;">
                Tout marquer comme lu
            </a>
        </li>
        <?php endif; ?>
    </ul>
</li>
<script>
function markNotifRead(id, lien) {
    fetch('notifications.php?notif_lu=' + id)
        .then(function() {
            // Supprimer le point bleu
            var dot = document.getElementById('notif-dot-' + id);
            if (dot) dot.remove();
            // Mettre à jour le badge
            updateNotifBadge();
            // Rediriger
            if (lien && lien !== '#') window.location.href = lien;
        });
}
function markAllNotifRead() {
    fetch('notifications.php?notif_all_lu=1')
        .then(function() {
            document.querySelectorAll('.notif-dot').forEach(function(d) { d.remove(); });
            document.querySelectorAll('.notif-unread').forEach(function(el) {
                el.classList.remove('notif-unread', 'fw-semibold');
                el.style.opacity = '0.7';
            });
            updateNotifBadge(0);
        });
}
function updateNotifBadge(count) {
    var badge = document.querySelector('.badge-notifications');
    var badgeCount = document.querySelector('.notif-badge-count');
    var remaining = count !== undefined ? count : document.querySelectorAll('.notif-dot').length;
    if (badge) badge.style.display = remaining > 0 ? '' : 'none';
    if (badgeCount) {
        if (remaining > 0) { badgeCount.textContent = remaining + ' nouvelles'; badgeCount.style.display = ''; }
        else badgeCount.style.display = 'none';
    }
}
</script>
