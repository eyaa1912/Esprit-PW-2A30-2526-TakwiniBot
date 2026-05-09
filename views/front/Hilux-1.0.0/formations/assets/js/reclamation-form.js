/**
 * Formulaire de réclamation — validation côté client et envoi (fetch).
 * Règles alignées sur controllers/ReclamationController.php (addReclamation).
 * Fichier dédié pour revue / cours (séparation HTML / JavaScript).
 */
(function () {
  'use strict';

  var form = document.getElementById('reclamation-form');
  if (!form) {
    return;
  }

  var statusBox = document.getElementById('reclamation-status');
  var submitButton = document.getElementById('reclamation-submit');
  if (!statusBox || !submitButton) {
    return;
  }

  function getControllerUrl(action) {
    if (window.location.protocol === 'file:') {
      return 'http://localhost/TakwiniBot%20-%20Copie/controllers/ReclamationController.php?action=' + encodeURIComponent(action);
    }
    var basePath = window.location.pathname.split('/views/')[0];
    if (!basePath) {
      basePath = '/TakwiniBot%20-%20Copie';
    }
    return window.location.origin + basePath + '/controllers/ReclamationController.php?action=' + encodeURIComponent(action);
  }

  function showStatus(type, message) {
    statusBox.className = 'alert alert-' + type;
    statusBox.textContent = message;
  }

  function strLenUnicode(str) {
    return Array.from(str).length;
  }

  function clearFieldErrors() {
    ['type', 'sujet', 'message'].forEach(function (id) {
      var el = document.getElementById(id);
      if (el) {
        el.classList.remove('field-error');
      }
    });
  }

  function markFieldError(id) {
    var el = document.getElementById(id);
    if (el) {
      el.classList.add('field-error');
    }
  }

  function validateReclamationForm() {
    clearFieldErrors();
    var type = (document.getElementById('type').value || '').trim();
    var sujet = (document.getElementById('sujet').value || '').trim();
    var message = (document.getElementById('message').value || '').trim();

    if (!type) {
      markFieldError('type');
      return { ok: false, message: 'Veuillez sélectionner un type de réclamation.' };
    }
    if (strLenUnicode(type) > 100) {
      markFieldError('type');
      return { ok: false, message: 'Le type de réclamation est trop long (100 caractères maximum).' };
    }
    if (strLenUnicode(sujet) < 3) {
      markFieldError('sujet');
      return { ok: false, message: 'Le sujet doit contenir au moins 3 caractères.' };
    }
    if (strLenUnicode(sujet) > 200) {
      markFieldError('sujet');
      return { ok: false, message: 'Le sujet ne peut pas dépasser 200 caractères.' };
    }
    if (strLenUnicode(message) < 5) {
      markFieldError('message');
      return { ok: false, message: 'Le message doit contenir au moins 5 caractères.' };
    }
    return { ok: true };
  }

  function getStoredIds() {
    try {
      var ids = JSON.parse(localStorage.getItem('takwini_reclamation_ids') || '[]');
      return Array.isArray(ids) ? ids.filter(Boolean) : [];
    } catch (error) {
      return [];
    }
  }

  function storeId(id) {
    var ids = getStoredIds();
    var value = String(id);
    if (ids.indexOf(value) === -1) {
      ids.push(value);
    }
    localStorage.setItem('takwini_reclamation_ids', JSON.stringify(ids));
  }

  ['type', 'sujet', 'message'].forEach(function (id) {
    var el = document.getElementById(id);
    if (!el) {
      return;
    }
    el.addEventListener('input', function () {
      el.classList.remove('field-error');
    });
    el.addEventListener('change', function () {
      el.classList.remove('field-error');
    });
  });

  form.addEventListener('reset', function () {
    window.setTimeout(function () {
      clearFieldErrors();
      statusBox.className = 'alert d-none';
      statusBox.textContent = '';
    }, 0);
  });

  form.addEventListener('submit', async function (event) {
    event.preventDefault();

    var validation = validateReclamationForm();
    if (!validation.ok) {
      showStatus('danger', validation.message);
      if (statusBox.scrollIntoView) {
        statusBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
      }
      return;
    }

    var formData = new FormData(form);
    var params = new URLSearchParams(window.location.search);
    var userId = params.get('user_id');
    if (userId) {
      formData.set('user_id', userId);
    }

    submitButton.disabled = true;
    showStatus('info', 'Envoi en cours...');

    try {
      var response = await fetch(getControllerUrl('add'), {
        method: 'POST',
        body: formData
      });
      var json = await response.json();
      if (!response.ok || !json.success) {
        throw new Error((json && json.message) || 'Impossible d’enregistrer la réclamation.');
      }

      storeId(json.id);
      showStatus('success', 'Réclamation enregistrée. Redirection vers le suivi...');

      var target = new URL('front_mes_reclamations.html', window.location.href);
      if (userId) {
        target.searchParams.set('user_id', userId);
      }
      window.setTimeout(function () {
        window.location.href = target.pathname + target.search;
      }, 700);
    } catch (error) {
      showStatus('danger', (error && error.message) || 'Erreur réseau.');
      submitButton.disabled = false;
    }
  });
})();
