/**
 * Validation du formulaire d'inscription
 * Contrôle de saisie côté client
 */

const ValidationInscription = {

    rules: {
        user_id: {
            required: true,
            min: 1,
            integer: true,
            label: 'ID Utilisateur'
        },
        nom: {
            required: true,
            minLength: 2,
            maxLength: 100,
            pattern: /^[a-zA-ZÀ-ÿ\s\-']+$/,
            label: 'Nom'
        },
        prenom: {
            required: true,
            minLength: 2,
            maxLength: 100,
            pattern: /^[a-zA-ZÀ-ÿ\s\-']+$/,
            label: 'Prénom'
        },
        email: {
            required: true,
            email: true,
            maxLength: 150,
            label: 'Email'
        },
        niveau: {
            required: false,
            maxLength: 100,
            label: 'Niveau'
        },
        mode_formation: {
            required: false,
            label: 'Mode de formation'
        }
    },

    messages: {
        required:   (label) => `Le champ "${label}" est obligatoire.`,
        min:        (label, min) => `"${label}" doit être supérieur à ${min}.`,
        minLength:  (label, min) => `"${label}" doit contenir au moins ${min} caractères.`,
        maxLength:  (label, max) => `"${label}" ne doit pas dépasser ${max} caractères.`,
        integer:    (label) => `"${label}" doit être un nombre entier valide.`,
        email:      (label) => `"${label}" doit être une adresse email valide (ex: nom@domaine.com).`,
        pattern:    (label) => `"${label}" contient des caractères non autorisés (chiffres ou symboles).`
    },

    /**
     * Valider un champ individuel
     */
    validateField(name, value) {
        const rule = this.rules[name];
        if (!rule) return null;

        const val = String(value).trim();

        if (rule.required && val === '') {
            return this.messages.required(rule.label);
        }

        if (val === '') return null; // Champ optionnel vide = OK

        if (rule.integer && (isNaN(value) || !Number.isInteger(Number(value)))) {
            return this.messages.integer(rule.label);
        }

        if (rule.min !== undefined && Number(value) < rule.min) {
            return this.messages.min(rule.label, rule.min);
        }

        if (rule.minLength && val.length < rule.minLength) {
            return this.messages.minLength(rule.label, rule.minLength);
        }

        if (rule.maxLength && val.length > rule.maxLength) {
            return this.messages.maxLength(rule.label, rule.maxLength);
        }

        if (rule.email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(val)) {
                return this.messages.email(rule.label);
            }
        }

        if (rule.pattern && !rule.pattern.test(val)) {
            return this.messages.pattern(rule.label);
        }

        return null; // Pas d'erreur
    },

    /**
     * Valider tout le formulaire
     * @param {HTMLFormElement} form
     * @returns {boolean} true si valide
     */
    validateForm(form) {
        let isValid = true;

        // Nettoyer les erreurs précédentes
        this.clearErrors(form);

        Object.keys(this.rules).forEach(name => {
            const input = form.querySelector(`[name="${name}"]`);
            if (!input) return;

            const error = this.validateField(name, input.value);
            if (error) {
                this.showError(input, error);
                isValid = false;
            } else {
                this.showSuccess(input);
            }
        });

        return isValid;
    },

    /**
     * Afficher une erreur sur un champ
     */
    showError(input, message) {
        input.classList.remove('is-valid');
        input.classList.add('is-invalid');
        input.style.borderColor = '#dc3545';
        input.style.boxShadow = '0 0 0 0.2rem rgba(220,53,69,0.25)';

        // Supprimer l'ancien message d'erreur s'il existe
        const existing = input.parentNode.querySelector('.invalid-feedback');
        if (existing) existing.remove();

        const feedback = document.createElement('div');
        feedback.className = 'invalid-feedback';
        feedback.style.cssText = 'display:block; color:#dc3545; font-size:0.85em; margin-top:4px;';
        feedback.innerHTML = `<i class="fa fa-exclamation-circle"></i> ${message}`;
        input.parentNode.appendChild(feedback);
    },

    /**
     * Afficher le succès sur un champ
     */
    showSuccess(input) {
        input.classList.remove('is-invalid');
        input.classList.add('is-valid');
        input.style.borderColor = '#28a745';
        input.style.boxShadow = '0 0 0 0.2rem rgba(40,167,69,0.25)';

        const existing = input.parentNode.querySelector('.invalid-feedback');
        if (existing) existing.remove();
    },

    /**
     * Nettoyer toutes les erreurs du formulaire
     */
    clearErrors(form) {
        form.querySelectorAll('.is-invalid, .is-valid').forEach(el => {
            el.classList.remove('is-invalid', 'is-valid');
            el.style.borderColor = '';
            el.style.boxShadow = '';
        });
        form.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
    },

    /**
     * Attacher la validation en temps réel sur un formulaire
     * @param {HTMLFormElement} form
     */
    attachLiveValidation(form) {
        Object.keys(this.rules).forEach(name => {
            const input = form.querySelector(`[name="${name}"]`);
            if (!input) return;

            // Validation au moment où l'utilisateur quitte le champ
            input.addEventListener('blur', () => {
                const error = this.validateField(name, input.value);
                if (error) {
                    this.showError(input, error);
                } else if (input.value.trim() !== '') {
                    this.showSuccess(input);
                }
            });

            // Effacer l'erreur dès que l'utilisateur retape
            input.addEventListener('input', () => {
                if (input.classList.contains('is-invalid')) {
                    const error = this.validateField(name, input.value);
                    if (!error) {
                        this.showSuccess(input);
                    }
                }
            });
        });
    },

    /**
     * Initialiser la validation sur tous les formulaires d'inscription de la page
     */
    init() {
        // Cibler tous les formulaires d'inscription (IDs dynamiques)
        const forms = document.querySelectorAll('form[id^="inscriptionForm"]');

        forms.forEach(form => {
            // Validation en temps réel
            this.attachLiveValidation(form);

            // Validation à la soumission
            form.addEventListener('submit', (e) => {
                if (!this.validateForm(form)) {
                    e.preventDefault();
                    e.stopPropagation();

                    // Scroller vers la première erreur
                    const firstError = form.querySelector('.is-invalid');
                    if (firstError) {
                        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        firstError.focus();
                    }
                }
            });
        });

        // Aussi cibler le formulaire générique #inscriptionModal si présent
        const genericForm = document.querySelector('#inscriptionModal form');
        if (genericForm && !genericForm.id.startsWith('inscriptionForm')) {
            this.attachLiveValidation(genericForm);
            genericForm.addEventListener('submit', (e) => {
                if (!this.validateForm(genericForm)) {
                    e.preventDefault();
                    e.stopPropagation();
                    const firstError = genericForm.querySelector('.is-invalid');
                    if (firstError) {
                        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        firstError.focus();
                    }
                }
            });
        }

        console.log(`✅ Validation inscription initialisée sur ${forms.length} formulaire(s)`);
    }
};

// Lancer automatiquement au chargement du DOM
document.addEventListener('DOMContentLoaded', () => {
    ValidationInscription.init();
});
