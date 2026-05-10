document.addEventListener('DOMContentLoaded', function () {
    var deleteForms = document.querySelectorAll('.js-delete');
    deleteForms.forEach(function (form) {
        form.addEventListener('submit', function (event) {
            var message = form.getAttribute('data-confirm') || 'Confirmer la suppression ?';
            if (!window.confirm(message)) {
                event.preventDefault();
            }
        });
    });

    var forms = document.querySelectorAll('form.needs-validation');
    forms.forEach(function (form) {
        var setFieldError = function (field, message) {
            if (!field) {
                return;
            }

            field.classList.remove('is-valid');
            field.classList.add('is-invalid');

            var feedback = field.parentElement ? field.parentElement.querySelector('.invalid-feedback') : null;
            if (feedback && message) {
                feedback.textContent = message;
            }
        };

        var clearFieldError = function (field) {
            if (!field) {
                return;
            }

            field.classList.remove('is-invalid');
            if (field.value.trim() !== '') {
                field.classList.add('is-valid');
            } else {
                field.classList.remove('is-valid');
            }
        };

        var validateForm = function () {
            var isValid = true;
            var requiredFields = [
                { name: 'nom_candidat', message: 'Le nom du candidat est requis.' },
                { name: 'email_candidat', message: 'L\'email du candidat est requis.' },
                { name: 'genre', message: 'Le genre est requis.' },
                { name: 'type_handicap', message: 'Le type de handicap est requis.' },
                { name: 'type_entretien', message: 'Le type d\'entretien est requis.' },
                { name: 'date_entretien', message: 'La date d\'entretien est requise.' },
                { name: 'heure_entretien', message: 'L\'heure d\'entretien est requise.' },
                { name: 'poste_cible', message: 'Le poste cible est requis.' },
                { name: 'statut', message: 'Le statut est requis.' }
            ];

            requiredFields.forEach(function (rule) {
                var field = form.querySelector('[name="' + rule.name + '"]');
                if (!field) {
                    return;
                }

                var value = field.value.trim();
                if (value === '') {
                    setFieldError(field, rule.message);
                    isValid = false;
                } else {
                    clearFieldError(field);
                }
            });

            var emailInput = form.querySelector('[name="email_candidat"]');
            if (emailInput && emailInput.value.trim() !== '') {
                var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
                if (!emailPattern.test(emailInput.value.trim())) {
                    setFieldError(emailInput, 'Veuillez saisir une adresse email valide.');
                    isValid = false;
                }
            }

            var dateInput = form.querySelector('[name="date_entretien"]');
            if (dateInput && dateInput.value.trim() !== '') {
                var dateValue = dateInput.value.trim();
                var isoDatePattern = /^\d{4}-\d{2}-\d{2}$/;
                var parsedDate = new Date(dateValue + 'T00:00:00');
                if (!isoDatePattern.test(dateValue) || Number.isNaN(parsedDate.getTime())) {
                    setFieldError(dateInput, 'Veuillez saisir une date valide (AAAA-MM-JJ).');
                    isValid = false;
                }
            }

            var timeInput = form.querySelector('[name="heure_entretien"]');
            if (timeInput && timeInput.value.trim() !== '') {
                var timePattern = /^([01]\d|2[0-3]):([0-5]\d)$/;
                if (!timePattern.test(timeInput.value.trim())) {
                    setFieldError(timeInput, 'Veuillez saisir une heure valide (HH:MM).');
                    isValid = false;
                }
            }

            var scoreInput = form.querySelector('[name="score_rse"]');
            if (scoreInput && scoreInput.value.trim() !== '') {
                var numericScore = Number(scoreInput.value.trim());
                var isValidScore = Number.isInteger(numericScore) && numericScore >= 1 && numericScore <= 5;
                if (!isValidScore) {
                    setFieldError(scoreInput, 'Le score RSE doit etre un entier entre 1 et 5.');
                    isValid = false;
                } else {
                    clearFieldError(scoreInput);
                }
            }

            return isValid;
        };

        form.querySelectorAll('input, select, textarea').forEach(function (field) {
            field.addEventListener('input', function () {
                clearFieldError(field);
            });

            field.addEventListener('change', function () {
                clearFieldError(field);
            });
        });

        form.addEventListener('submit', function (event) {
            if (!validateForm()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });

    var avatarForm = document.querySelector('form[data-avatar-help="true"]');
    if (avatarForm) {
        var page = document.body.getAttribute('data-page');
        var bubbleId = avatarForm.getAttribute('data-avatar-help-target');
        var bubble = bubbleId ? document.getElementById(bubbleId) : null;

        var baseMessages = {
            create: 'Je t’aide à planifier un entretien accessible et bien structuré.',
            edit: 'Mettons à jour les données sans perdre les besoins d’accessibilité.'
        };

        var updateAvatarMessage = function () {
            if (!bubble) {
                return;
            }

            var message = baseMessages[page] || 'Prêt à accompagner un entretien inclusif.';
            var typeEntretien = avatarForm.querySelector('[name="type_entretien"]');
            var statut = avatarForm.querySelector('[name="statut"]');

            if (typeEntretien && typeEntretien.value === 'LST') {
                message = 'Pense à valider la disponibilité de l’interprète en LST.';
            } else if (typeEntretien && typeEntretien.value === 'visioconférence') {
                message = 'Vérifie sous-titres et qualité audio pour une visioconférence inclusive.';
            }

            if (statut && statut.value === 'annulé') {
                message = 'Ajoute une remarque claire pour tracer la raison d’annulation.';
            } else if (statut && statut.value === 'terminé') {
                message = 'Parfait, n’oublie pas de renseigner le score RSE et les remarques finales.';
            }

            bubble.textContent = message;
        };

        avatarForm.addEventListener('change', updateAvatarMessage);
        avatarForm.addEventListener('input', updateAvatarMessage);
        updateAvatarMessage();
    }
});
