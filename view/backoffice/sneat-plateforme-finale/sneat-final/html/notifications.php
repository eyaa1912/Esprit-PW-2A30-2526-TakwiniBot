<?php
if (!class_exists('config')) return;

$_notifDb   = config::getConnexion();
$_notifStmt = $_notifDb->query('SELECT * FROM notifications ORDER BY id DESC LIMIT 10');
$_notifs    = $_notifStmt ? $_notifStmt->fetchAll() : [];
$_unread    = count(array_filter($_notifs, fn($n) => (int)$n['lu'] === 0));

if (isset($_GET['mark_notif_read'])) {
    if ($_GET['mark_notif_read'] === 'all') {
        $_notifDb->exec('UPDATE notifications SET lu = 1');
    } elseif (is_numeric($_GET['mark_notif_read'])) {
        $_notifDb->prepare('UPDATE notifications SET lu = 1 WHERE id = :id')->execute(['id' => (int)$_GET['mark_notif_read']]);
    }
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    exit;
}

// Icônes et couleurs par type
$_notifStyles = [
    'recruteur' => ['icon' => 'bx-user-check',    'bg' => '#e8f5e9', 'color' => '#4caf50'],
    'info'      => ['icon' => 'bx-book-open',      'bg' => '#ede7f6', 'color' => '#7c4dff'],
    'warning'   => ['icon' => 'bx-calendar',       'bg' => '#fff8e1', 'color' => '#ff9800'],
    'success'   => ['icon' => 'bx-check-circle',   'bg' => '#e8f5e9', 'color' => '#4caf50'],
    'danger'    => ['icon' => 'bx-error-circle',   'bg' => '#fde8e8', 'color' => '#e53935'],
    'offre'     => ['icon' => 'bx-briefcase',      'bg' => '#e3f2fd', 'color' => '#2196f3'],
];
?>

<style>
.notif-dropdown {
    min-width: 380px;
    max-height: 520px;
    border-radius: 16px !important;
    box-shadow: 0 8px 40px rgba(0,0,0,.14) !important;
    border: none !important;
    overflow: hidden;
    padding: 0 !important;
}
.notif-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 18px 20px 14px;
    border-bottom: 1px solid #f0f0f0;
}
.notif-header h6 { font-size: 16px; font-weight: 700; margin: 0; color: #1a1a2e; }
.notif-count-badge {
    background: #ede7f6;
    color: #7c4dff;
    font-size: 12px;
    font-weight: 700;
    padding: 4px 12px;
    border-radius: 50px;
}
.notif-list-scroll { max-height: 380px; overflow-y: auto; }
.notif-item-row {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    padding: 14px 20px;
    border-bottom: 1px solid #f8f8f8;
    transition: background .15s;
    cursor: pointer;
    text-decoration: none !important;
    color: inherit !important;
}
.notif-item-row:hover { background: #fafafa; }
.notif-item-row.unread { background: #fafbff; }
.notif-icon-box {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 20px;
}
.notif-content { flex: 1; min-width: 0; }
.notif-title { font-size: 14px; font-weight: 600; color: #1a1a2e; margin-bottom: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.notif-msg { font-size: 12px; color: #888; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.notif-close {
    background: none;
    border: none;
    color: #bbb;
    font-size: 16px;
    cursor: pointer;
    padding: 0 4px;
    flex-shrink: 0;
    line-height: 1;
    transition: color .15s;
}
.notif-close:hover { color: #555; }
.notif-footer {
    padding: 12px 20px;
    text-align: center;
    border-top: 1px solid #f0f0f0;
}
.notif-footer a { font-size: 14px; font-weight: 600; color: #696cff; text-decoration: none; }
.notif-footer a:hover { text-decoration: underline; }
</style>

<li class="nav-item dropdown me-2">
    <a class="nav-link position-relative" href="javascript:void(0);" data-bs-toggle="dropdown" aria-expanded="false" style="padding:8px;">
        <i class="icon-base bx bx-bell icon-md" style="font-size:22px;"></i>
        <?php if ($_unread > 0): ?>
        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:10px;min-width:18px;padding:2px 5px;">
            <?= $_unread > 99 ? '99+' : $_unread ?>
        </span>
        <?php endif; ?>
    </a>

    <div class="dropdown-menu dropdown-menu-end notif-dropdown">
        <!-- Header -->
        <div class="notif-header">
            <h6>Notifications</h6>
            <?php if ($_unread > 0): ?>
            <span class="notif-count-badge"><?= $_unread ?> nouvelle<?= $_unread > 1 ? 's' : '' ?></span>
            <?php endif; ?>
        </div>

        <!-- Liste -->
        <div class="notif-list-scroll">
            <?php if (empty($_notifs)): ?>
            <div class="text-center text-muted py-5">
                <i class="bx bx-bell-off d-block mb-2" style="font-size:32px;"></i>
                <span style="font-size:14px;">Aucune notification</span>
            </div>
            <?php else: ?>
            <?php foreach ($_notifs as $_n):
                $type   = $_n['type'] ?? 'info';
                $style  = $_notifStyles[$type] ?? $_notifStyles['info'];
                $isUnread = (int)$_n['lu'] === 0;
                $link   = !empty($_n['lien']) ? htmlspecialchars($_n['lien']) : '#';
                $timeAgo = '';
                if (!empty($_n['created_at'])) {
                    $diff = time() - strtotime($_n['created_at']);
                    if ($diff < 60)        $timeAgo = 'il y a ' . $diff . ' sec';
                    elseif ($diff < 3600)  $timeAgo = 'il y a ' . floor($diff/60) . ' min';
                    elseif ($diff < 86400) $timeAgo = 'il y a ' . floor($diff/3600) . ' h';
                    else                   $timeAgo = 'il y a ' . floor($diff/86400) . ' j';
                }
            ?>
            <div class="notif-item-row <?= $isUnread ? 'unread' : '' ?>" data-notif-id="<?= (int)$_n['id'] ?>" onclick="window.location='<?= $link ?>'">
                <div class="notif-icon-box" style="background:<?= $style['bg'] ?>;color:<?= $style['color'] ?>;">
                    <i class="bx <?= $style['icon'] ?>"></i>
                </div>
                <div class="notif-content">
                    <div class="notif-title"><?= htmlspecialchars($_n['titre'] ?? 'Notification') ?></div>
                    <div class="notif-msg">
                        <?= htmlspecialchars($_n['message'] ?? '') ?>
                        <?php if ($timeAgo): ?> &nbsp;·&nbsp; <span><?= $timeAgo ?></span><?php endif; ?>
                    </div>
                </div>
                <button class="notif-close" onclick="event.stopPropagation();marquerLu(<?= (int)$_n['id'] ?>,this);" title="Marquer comme lu">×</button>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Footer -->
        <div class="notif-footer">
            <a href="javascript:void(0);" id="markAllNotifRead">Tout marquer comme lu</a>
        </div>
    </div>
</li>

<script>
function marquerLu(id, btn) {
    fetch('?mark_notif_read=' + id).catch(function(){});
    var row = btn.closest('.notif-item-row');
    if (row) { row.classList.remove('unread'); row.style.opacity = '.5'; }
    var badge = document.querySelector('.nav-item.dropdown .badge.bg-danger');
    if (badge) { var c = parseInt(badge.textContent)||1; c--; if(c<=0) badge.remove(); else badge.textContent = c > 99 ? '99+' : c; }
}
var markAllBtn = document.getElementById('markAllNotifRead');
if (markAllBtn) {
    markAllBtn.addEventListener('click', function() {
        fetch('?mark_notif_read=all').catch(function(){});
        document.querySelectorAll('.notif-item-row').forEach(function(r){ r.classList.remove('unread'); r.style.opacity='.5'; });
        var badge = document.querySelector('.nav-item.dropdown .badge.bg-danger');
        if (badge) badge.remove();
    });
}
</script>
