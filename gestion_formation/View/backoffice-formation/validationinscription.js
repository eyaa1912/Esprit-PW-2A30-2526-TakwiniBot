/**
 * Validation du formulaire d'inscription - Backoffice
 */

function showError(input, message) {
    const existing = input.parentElement.querySelector('.error-message');
    if (existing) existing.remove();
    input.classList.add('is-invalid');
    input.style.borderColor = '#dc3545';
    const div = document.createElement('div');
    div.className = 'error-message text-danger small mt-1';
    div.innerHTML = '<i class="fa fa-exclamation-circle"></i> ' + message;
    input.parentElement.appendChild(div);
}

function clearError(input) {
    input.classList.remove('is-invalid');
    input.classList.remove('is-valid');
    input.style.borderColor = '';
    const div = input.parentElement.querySelector('.error-message');
    if (div) div.remove();
}

function showSuccess(input) {
    clearError(input);
    input.classList.add('is-valid');
    input.style.borderColor = '#28a745';
}

// ── Règles ──────────────────────────────────────────────────────────────────

function validateUserId(val) {
    if (!val || val.toString().trim() === '')
        return 'L\'ID utilisateur est obligatoire.';
    if (!Number.isInteger(Number(val)) || Number(val) < 1)
        return 'L\'ID utilisateur doit être un entier positif.';
    return null;
}

function validateFormationId(val) {
    if (!val || val.toString().trim() === '')
        return 'L\'ID formation est obligatoire.';
    if (!Number.isInteger(Number(val)) || Number(val) < 1)
        return 'L\'ID formation doit être un entier positif.';
    return null;
}

function validateNom(val) {
    if (!val || val.trim() === '')
        return 'Le nom est obligatoire.';
    if (val.trim().length < 2)
        return 'Le nom doit contenir au moins 2 caractères.';
    if (val.trim().length > 100)
        return 'Le nom ne doit pas dépasser 100 caractères.';
    if (!/^[a-zA-ZÀ-ÿ\s\-']+$/.test(val.trim()))
        return 'Le nom ne doit contenir que des lettres.';
    return null;
}

function validatePrenom(val) {
    if (!val || val.trim() === '')
        return 'Le prénom est obligatoire.';
    if (val.trim().length < 2)
        return 'Le prénom doit contenir au moins 2 caractères.';
    if (val.trim().length > 100)
        return 'Le prénom ne doit pas dépasser 100 caractères.';
    if (!/^[a-zA-ZÀ-ÿ\s\-']+$/.test(val.trim()))
        return 'Le prénom ne doit contenir que des lettres.';
    return null;
}

function validateEmail(val) {
    if (!val || val.trim() === '')
        return 'L\'email est obligatoire.';
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val.trim()))
        return 'L\'email doit être valide (ex: nom@domaine.com).';
    if (val.length > 150)
        return 'L\'email ne doit pas dépasser 150 caractères.';
    return null;
}

// ── Validation d'un formulaire complet ──────────────────────────────────────

function validateInscriptionForm(form) {
    let valid = true;

    const fields = {
        user_id:      validateUserId,
        formation_id: validateFormationId,
        nom:          validateNom,
        prenom:       validatePrenom,
        email:        validateEmail
    };

    // Nettoyer d'abord
    form.querySelectorAll('input, select').forEach(el => clearError(el));

    Object.entries(fields).forEach(([name, fn]) => {
        const input = form.querySelector('[name="' + name + '"]');
        if (!input) return;
        const err = fn(input.value);
        if (err) { showError(input, err); valid = false; }
        else      { showSuccess(input); }
    });

    if (!valid) {
        const first = form.querySelector('.is-invalid');
        if (first) { first.scrollIntoView({ behavior: 'smooth', block: 'center' }); first.focus(); }
    }

    return valid;
}

// ── Attacher la validation en temps réel ────────────────────────────────────

function attachLive(form) {
    const map = {
        user_id:      validateUserId,
        formation_id: validateFormationId,
        nom:          validateNom,
        prenom:       validatePrenom,
        email:        validateEmail
    };

    Object.entries(map).forEach(([name, fn]) => {
        const input = form.querySelector('[name="' + name + '"]');
        if (!input) return;

        input.addEventListener('blur', () => {
            const err = fn(input.value);
            err ? showError(input, err) : showSuccess(input);
        });

        input.addEventListener('input', () => {
            if (input.classList.contains('is-invalid')) {
                const err = fn(input.value);
                if (!err) showSuccess(input);
            }
        });
    });
}

// ── Init ─────────────────────────────────────────────────────────────────────

document.addEventListener('DOMContentLoaded', function () {

    // 1. Formulaire page addInscription.php (form direct)
    const directForm = document.querySelector('form[action="addInscription.php"]');
    if (directForm) {
        attachLive(directForm);
        directForm.addEventListener('submit', function (e) {
            if (!validateInscriptionForm(this)) e.preventDefault();
        });
    }

    // 2. Modal d'édition dans gestion-inscriptions.php
    const editForm = document.querySelector('#editInscriptionModal form');
    if (editForm) {
        attachLive(editForm);
        editForm.addEventListener('submit', function (e) {
            if (!validateInscriptionForm(this)) e.preventDefault();
        });
    }

    console.log('✅ Validation inscription (backoffice) initialisée');
});
