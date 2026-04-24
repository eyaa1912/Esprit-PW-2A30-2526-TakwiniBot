/**
 * Langue, menu vertical, thème clair/sombre, couleur primaire (localStorage).
 */
'use strict';

function applyTheme(mode) {
  var root = document.documentElement;
  if (mode === 'dark') {
    root.setAttribute('data-bs-theme', 'dark');
  } else {
    root.setAttribute('data-bs-theme', 'light');
  }
  var icon = document.getElementById('app-theme-toggle-icon');
  if (icon) {
    icon.className =
      'icon-base ' + (mode === 'dark' ? 'bx bx-sun' : 'bx bx-moon') + ' icon-md';
  }
}

function applyPrimary(hex, rgb) {
  var root = document.documentElement;
  if (!hex) {
    root.style.removeProperty('--bs-primary');
    root.style.removeProperty('--bs-primary-rgb');
    try {
      localStorage.removeItem('app.primaryHex');
      localStorage.removeItem('app.primaryRgb');
    } catch (e) {}
    return;
  }
  root.style.setProperty('--bs-primary', hex);
  root.style.setProperty('--bs-primary-rgb', rgb);
  try {
    localStorage.setItem('app.primaryHex', hex);
    localStorage.setItem('app.primaryRgb', rgb);
  } catch (e) {}
}

document.addEventListener('DOMContentLoaded', function () {
  try {
    var savedLang = localStorage.getItem('app.lang');
    if (savedLang) {
      document.documentElement.setAttribute('lang', savedLang);
    }
  } catch (e) {}

  try {
    var savedTheme = localStorage.getItem('app.theme');
    if (savedTheme === 'dark' || savedTheme === 'light') {
      applyTheme(savedTheme);
    } else {
      applyTheme(
        document.documentElement.getAttribute('data-bs-theme') === 'dark'
          ? 'dark'
          : 'light'
      );
    }
  } catch (e) {
    applyTheme('light');
  }

  try {
    var ph = localStorage.getItem('app.primaryHex');
    var pr = localStorage.getItem('app.primaryRgb');
    if (ph && pr) {
      document.documentElement.style.setProperty('--bs-primary', ph);
      document.documentElement.style.setProperty('--bs-primary-rgb', pr);
    }
  } catch (e) {}

  document.querySelectorAll('[data-app-lang]').forEach(function (el) {
    el.addEventListener('click', function (e) {
      e.preventDefault();
      var code = el.getAttribute('data-app-lang');
      if (!code) return;
      try {
        localStorage.setItem('app.lang', code);
      } catch (err) {}
      document.documentElement.setAttribute('lang', code);
    });
  });

  document.querySelectorAll('[data-app-layout="vertical"]').forEach(function (el) {
    el.addEventListener('click', function (e) {
      e.preventDefault();
      try {
        localStorage.setItem('app.menuLayout', 'vertical');
      } catch (err) {}
    });
  });

  var toggle = document.getElementById('app-theme-toggle');
  if (toggle) {
    toggle.addEventListener('click', function (e) {
      e.preventDefault();
      var next =
        document.documentElement.getAttribute('data-bs-theme') === 'dark'
          ? 'light'
          : 'dark';
      try {
        localStorage.setItem('app.theme', next);
      } catch (err) {}
      applyTheme(next);
    });
  }

  document.querySelectorAll('[data-app-primary-reset]').forEach(function (el) {
    el.addEventListener('click', function (e) {
      e.preventDefault();
      applyPrimary(null, null);
    });
  });

  document.querySelectorAll('[data-app-primary]').forEach(function (el) {
    if (el.getAttribute('data-app-primary-reset')) return;
    el.addEventListener('click', function (e) {
      e.preventDefault();
      var hex = el.getAttribute('data-app-primary');
      var rgb = el.getAttribute('data-app-primary-rgb');
      if (hex && rgb) applyPrimary(hex, rgb);
    });
  });
});
