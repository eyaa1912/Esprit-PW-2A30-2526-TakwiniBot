/**
 * Menu latéral — Plateforme emploi & handicap (IncluEmploi)
 * Remplit #platform-menu-inner avant l’initialisation de menu.js
 */
(function () {
  var root = document.getElementById('platform-menu-inner');
  if (!root) return;

  var page = document.body.getAttribute('data-platform-page') || 'dashboard';

  var items = [
    { id: 'dashboard', href: 'index.html', icon: 'bx-home-smile', label: 'Tableau de bord' },
    { id: 'utilisateurs', href: 'gestion-utilisateurs.html', icon: 'bx-group', label: 'Utilisateurs' },
    { id: 'formations', href: 'gestion-formations.html', icon: 'bx-book-open', label: 'Formations' },
    { id: 'offres', href: 'gestion-offres.php', icon: 'bx-briefcase', label: "Offres d'emploi" },
    { id: 'entretiens', href: 'gestion-entretiens.html', icon: 'bx-calendar-check', label: 'Entretiens' },
    { id: 'reclamations', href: 'gestion-reclamations.html', icon: 'bx-message-square-error', label: 'Réclamations' },
    { id: 'produits', href: 'gestion-produits.html', icon: 'bx-package', label: 'Produits' }
  ];

  function esc(s) {
    return String(s)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/"/g, '&quot;');
  }

  var navHtml = items
    .map(function (it) {
      var active = it.id === page ? ' active' : '';
      return (
        '<li class="menu-item' +
        active +
        '">' +
        '<a href="' +
        esc(it.href) +
        '" class="menu-link">' +
        '<i class="menu-icon tf-icons bx ' +
        esc(it.icon) +
        '"></i>' +
        '<div class="text-truncate">' +
        esc(it.label) +
        '</div></a></li>'
      );
    })
    .join('');

  var accountBlock =
    '<li class="menu-header small text-uppercase"><span class="menu-header-text">Compte</span></li>' +
    '<li class="menu-item">' +
    '<a href="pages-account-settings-account.html" class="menu-link">' +
    '<i class="menu-icon tf-icons bx bx-user-circle"></i>' +
    '<div class="text-truncate">Paramètres du compte</div></a></li>' +
    '<li class="menu-item">' +
    '<a href="auth-login-basic.html" class="menu-link">' +
    '<i class="menu-icon tf-icons bx bx-log-in-circle"></i>' +
    '<div class="text-truncate">Connexion / déconnexion</div></a></li>';

  root.innerHTML =
    '<li class="menu-header small text-uppercase"><span class="menu-header-text">Gestion</span></li>' +
    navHtml +
    accountBlock;
})();
