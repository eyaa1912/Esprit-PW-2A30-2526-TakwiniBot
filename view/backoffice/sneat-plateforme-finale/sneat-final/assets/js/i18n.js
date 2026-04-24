// ── Traductions FR / EN ──────────────────────────────────────────────────────
const translations = {
  en: {
    'Tableau de bord': 'Dashboard',
    'Accueil': 'Home',
    'Formations': 'Trainings',
    'Offres': 'Job Offers',
    'Réclamations': 'Complaints',
    'Entretiens': 'Interviews',
    'Produits': 'Products',
    'Utilisateurs': 'Users',
    'Liste des utilisateurs': 'User List',
    'Profil': 'Profile',
    'Applications': 'Applications',
    'Email': 'Email',
    'Discuter': 'Chat',
    'Calendrier': 'Calendar',
    'Voir le site': 'View site',
    'Déconnexion': 'Logout',
    'Mon profil': 'My profile',
    'Paramètres': 'Settings',
    'Administrateur': 'Administrator',
    'Notifications': 'Notifications',
    'Réclamation à traiter': 'Complaint to handle',
    'Entretien planifié demain': 'Interview scheduled tomorrow',
    'Rappel': 'Reminder',
    'Plateforme': 'Platform',
    'Liste utilisateurs': 'User list',
    'Rechercher...': 'Search...',
  }
};

// Sauvegarder les textes originaux FR au premier chargement
const originalTexts = new Map();

function saveOriginals() {
  document.querySelectorAll('.text-truncate, .dropdown-menu span, .dropdown-menu small, .menu-header-text').forEach(el => {
    if (!originalTexts.has(el)) {
      originalTexts.set(el, el.textContent.trim());
    }
  });
}

function applyLang(lang) {
  saveOriginals();
  const t = translations['en'];

  document.querySelectorAll('.text-truncate, .dropdown-menu span, .dropdown-menu small, .menu-header-text').forEach(el => {
    const original = originalTexts.get(el) || el.textContent.trim();
    if (lang === 'en' && t[original]) {
      el.textContent = t[original];
    } else if (lang === 'fr') {
      // Restaurer le texte original français
      if (originalTexts.has(el)) {
        el.textContent = originalTexts.get(el);
      }
    }
  });

  document.documentElement.setAttribute('lang', lang);
  try { localStorage.setItem('app.lang', lang); } catch(e) {}
}

document.addEventListener('DOMContentLoaded', function() {
  saveOriginals();

  let lang = 'fr';
  try { lang = localStorage.getItem('app.lang') || 'fr'; } catch(e) {}
  if (lang === 'en') applyLang('en');

  document.querySelectorAll('[data-app-lang]').forEach(el => {
    el.addEventListener('click', function(e) {
      e.preventDefault();
      applyLang(el.getAttribute('data-app-lang'));
    });
  });
});
