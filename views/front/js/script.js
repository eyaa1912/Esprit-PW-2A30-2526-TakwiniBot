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
        form.addEventListener('submit', function (event) {
            var scoreInput = form.querySelector('[name="score_rse"]');
            if (scoreInput) {
                scoreInput.setCustomValidity('');
                var scoreValue = scoreInput.value.trim();
                if (scoreValue !== '') {
                    var numericScore = Number(scoreValue);
                    var isValidScore = Number.isInteger(numericScore) && numericScore >= 1 && numericScore <= 5;
                    if (!isValidScore) {
                        scoreInput.setCustomValidity('Le score RSE doit être un entier entre 1 et 5.');
                    }
                }
            }

            if (!form.checkValidity()) {
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
