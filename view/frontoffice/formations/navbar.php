<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<style>
.site-navbar { background: rgba(255,255,255,0.97) !important; backdrop-filter: blur(10px); box-shadow: 0 2px 20px rgba(0,0,0,0.08); padding: 0 !important; height: 110px; }
.site-navbar .container { height: 110px; display: flex; align-items: center; justify-content: space-between; max-width: 100%; padding: 0 30px; }
.nav-logo { flex-shrink: 0; display: flex; align-items: center; }
.nav-logo img { height: 100px; width: auto; }
.nav-links { display: flex; align-items: center; gap: 4px; list-style: none; margin: 0; padding: 0; flex: 1; justify-content: center; }
.nav-links > li > a { color: #333 !important; font-size: 17px; font-weight: 600; padding: 10px 16px; border-radius: 8px; text-decoration: none; transition: all .2s; white-space: nowrap; }
.nav-links > li > a:hover { background: #e8f5e9; color: #2e7d32 !important; }
.nav-user { flex-shrink: 0; position: relative; }
.user-btn { display: inline-flex; align-items: center; gap: 10px; background: linear-gradient(135deg, #4caf50, #2e7d32); border: none; border-radius: 50px; padding: 8px 20px 8px 8px; cursor: pointer; text-decoration: none; transition: all .2s; box-shadow: 0 4px 15px rgba(76,175,80,0.3); min-width: 200px; }
.user-btn:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(76,175,80,0.4); text-decoration: none; }
.user-btn .u-avatar { width: 44px; height: 44px; border-radius: 50%; background: rgba(255,255,255,0.25); display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0; border: 2px solid rgba(255,255,255,0.6); }
.user-btn .u-avatar img { width: 44px; height: 44px; border-radius: 50%; object-fit: cover; }
.user-btn .u-name { color: #fff; font-size: 15px; font-weight: 700; flex: 1; text-align: left; }
.user-btn .u-caret { color: rgba(255,255,255,0.8); font-size: 10px; }
.user-drop-menu { display: none; position: absolute; right: 0; top: calc(100% + 10px); background: #fff; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.15); min-width: 200px; z-index: 9999; overflow: hidden; padding: 8px 0; border: 1px solid #e8f5e9; }
.user-drop-menu.open { display: block; }
.user-drop-menu a { display: flex; align-items: center; gap: 10px; padding: 13px 20px; font-size: 14px; font-weight: 600; color: #333; text-decoration: none; transition: background .15s; }
.user-drop-menu a:hover { background: #f1f8f2; color: #2e7d32; }
.user-drop-menu .dm-divider { height: 1px; background: #f0f0f0; margin: 4px 0; }
.user-drop-menu .dm-logout { color: #e53935; }
.user-drop-menu .dm-logout:hover { background: #ffebee; color: #c62828; }
</style>

<div class="site-mobile-menu site-navbar-target">
    <div class="site-mobile-menu-header">
        <div class="site-mobile-menu-close mt-3"><span class="icon-close2 js-menu-toggle"></span></div>
    </div>
    <div class="site-mobile-menu-body"></div>
</div>

<header class="site-navbar js-sticky-header site-navbar-target" role="banner">
    <div class="container">
        <div class="nav-logo">
            <a href="index.php"><img src="assets/img/logo.png" alt="Takwini"></a>
        </div>
        <ul class="nav-links d-none d-xl-flex">
            <li><a href="index.php">Accueil</a></li>
            <li><a href="about.php">À propos</a></li>
            <?php if (isset($_SESSION['user'])): ?>
                <?php if ($_SESSION['user']['role'] === 'candidat'): ?>
                    <li><a href="formation.php">Formations</a></li>
                    <li><a href="gallery.php">Produits</a></li>
                    <li><a href="blog.php">Entretiens</a></li>
                    <li><a href="offres-emploi/offres-emploi.html">Offres</a></li>
                    <li><a href="front_mes_reclamations.php">Réclamations</a></li>
                <?php elseif ($_SESSION['user']['role'] === 'recruteur'): ?>
                    <li><a href="gallery.php">Produits</a></li>
                    <li><a href="offres-emploi/offres-emploi.html">Offres</a></li>
                    <li><a href="front_mes_reclamations.php">Réclamations</a></li>
                <?php endif; ?>
            <?php endif; ?>
        </ul>
        <div class="nav-user d-none d-xl-flex">
            <?php if (isset($_SESSION['user']) && !empty($_SESSION['user']['nom'])): ?>
            <?php
                $_av = $_SESSION['user']['avatar'] ?? '';
                $_avatarSrc = !empty($_av) ? '../' . htmlspecialchars($_av) : null;
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
        <div class="d-xl-none ml-auto">
            <a href="#" class="site-menu-toggle js-menu-toggle"><span class="icon-menu h3"></span></a>
        </div>
    </div>
</header>

<script>
function toggleUserMenu(e) {
    e.preventDefault(); e.stopPropagation();
    var menu = document.getElementById('userDropMenu');
    if (menu) menu.classList.toggle('open');
}
document.addEventListener('click', function(e) {
    var menu = document.getElementById('userDropMenu');
    var btn = document.querySelector('.user-btn');
    if (menu && btn && !btn.contains(e.target)) menu.classList.remove('open');
});
</script>
