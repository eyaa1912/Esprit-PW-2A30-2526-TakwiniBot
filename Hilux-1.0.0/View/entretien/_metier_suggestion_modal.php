<?php
$suggestionEndpoint = $suggestionEndpoint ?? '../../Controller/suggest_metier.php';
?>
<div id="metier-suggestion-modal" class="metier-suggestion-modal" aria-hidden="true">
    <div class="metier-suggestion-modal__backdrop" data-metier-close></div>
    <div class="metier-suggestion-modal__panel" role="dialog" aria-modal="true" aria-labelledby="metier-suggestion-modal-title">
        <div class="metier-suggestion-modal__header">
            <div>
                <p class="metier-suggestion-modal__eyebrow">Assistant IA</p>
                <h3 id="metier-suggestion-modal-title">Suggestions de métiers adaptés</h3>
            </div>
            <button type="button" class="metier-suggestion-modal__close" data-metier-close aria-label="Fermer">&times;</button>
        </div>

        <div class="metier-suggestion-modal__body">
            <p class="metier-suggestion-modal__intro">Choisissez la suggestion la plus cohérente avec le profil du candidat.</p>
            <div class="metier-suggestion-modal__score">
                <span>Score RSE estimé</span>
                <strong id="metier-suggestion-score-value">0</strong>
                <span>/100</span>
            </div>
            <div id="metier-suggestion-loading" class="metier-suggestion-modal__message" hidden>Analyse du profil en cours...</div>
            <div id="metier-suggestion-error" class="metier-suggestion-modal__message metier-suggestion-modal__message--error" hidden></div>
            <div id="metier-suggestion-list" class="metier-suggestion-modal__list"></div>
        </div>

        <div class="metier-suggestion-modal__footer">
            <button type="button" class="btn btn-default" data-metier-close>Fermer</button>
            <button type="button" class="btn btn-default" style="background:#30b5e1;color:#fff;" data-metier-use>Utiliser la suggestion</button>
        </div>
    </div>
</div>

<style>
.metier-suggestion-modal {
    display: none;
    position: fixed;
    inset: 0;
    z-index: 2000;
}
.metier-suggestion-modal.is-open {
    display: block;
}
.metier-suggestion-modal__backdrop {
    position: absolute;
    inset: 0;
    background: rgba(15, 23, 42, 0.65);
}
.metier-suggestion-modal__panel {
    position: relative;
    width: min(880px, calc(100% - 32px));
    margin: 5vh auto;
    background: #fff;
    border-radius: 18px;
    box-shadow: 0 24px 80px rgba(15, 23, 42, 0.35);
    overflow: hidden;
}
.metier-suggestion-modal__header {
    display: flex;
    justify-content: space-between;
    gap: 16px;
    align-items: flex-start;
    padding: 22px 26px;
    background: linear-gradient(135deg, #1f3c88 0%, #30b5e1 100%);
    color: #fff;
}
.metier-suggestion-modal__eyebrow {
    margin: 0 0 6px;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.14em;
    opacity: 0.8;
}
.metier-suggestion-modal__header h3 {
    margin: 0;
    font-size: 24px;
    line-height: 1.2;
}
.metier-suggestion-modal__close {
    width: 40px;
    height: 40px;
    border: 0;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.18);
    color: #fff;
    font-size: 28px;
    line-height: 1;
}
.metier-suggestion-modal__body {
    padding: 24px 26px 18px;
}
.metier-suggestion-modal__intro {
    margin: 0 0 18px;
    color: #334155;
}
.metier-suggestion-modal__score {
    display: inline-flex;
    align-items: baseline;
    gap: 8px;
    margin-bottom: 18px;
    padding: 10px 14px;
    border-radius: 999px;
    background: #eef8fc;
    color: #0f172a;
    font-weight: 600;
}
.metier-suggestion-modal__score strong {
    font-size: 24px;
    color: #1f3c88;
}
.metier-suggestion-modal__message {
    margin-bottom: 16px;
    padding: 12px 14px;
    border-radius: 10px;
    background: #f8fafc;
    color: #334155;
}
.metier-suggestion-modal__message--error {
    background: #fef2f2;
    color: #b91c1c;
}
.metier-suggestion-modal__list {
    display: grid;
    gap: 14px;
}
.metier-suggestion-card {
    width: 100%;
    border: 1px solid #d7e4ea;
    border-radius: 14px;
    padding: 16px 18px;
    background: #fff;
    text-align: left;
    transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
}
.metier-suggestion-card:hover,
.metier-suggestion-card.is-selected {
    border-color: #30b5e1;
    box-shadow: 0 12px 30px rgba(48, 181, 225, 0.16);
    transform: translateY(-1px);
}
.metier-suggestion-card__title {
    margin: 0 0 8px;
    font-size: 18px;
    color: #0f172a;
    font-weight: 700;
}
.metier-suggestion-card__text {
    margin: 0;
    color: #475569;
    line-height: 1.6;
}
.metier-suggestion-modal__footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 18px 26px 24px;
    border-top: 1px solid #e2e8f0;
    background: #fafcff;
}
@media (max-width: 768px) {
    .metier-suggestion-modal__panel {
        width: calc(100% - 20px);
        margin: 10px auto;
    }
    .metier-suggestion-modal__header,
    .metier-suggestion-modal__body,
    .metier-suggestion-modal__footer {
        padding-left: 16px;
        padding-right: 16px;
    }
    .metier-suggestion-modal__header h3 {
        font-size: 20px;
    }
}
</style>

<script>
(function () {
    'use strict';

    const endpoint = <?= json_encode($suggestionEndpoint, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
    const modal = document.getElementById('metier-suggestion-modal');
    const loading = document.getElementById('metier-suggestion-loading');
    const errorBox = document.getElementById('metier-suggestion-error');
    const list = document.getElementById('metier-suggestion-list');
    const scoreValue = document.getElementById('metier-suggestion-score-value');
    const useButton = modal.querySelector('[data-metier-use]');
    const closeTargets = modal.querySelectorAll('[data-metier-close]');
    const triggerSelector = '[data-metier-suggest]';

    let activeForm = null;
    let suggestions = [];
    let selectedIndex = -1;

    function openModal() {
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }

    function setError(message) {
        errorBox.textContent = message;
        errorBox.hidden = false;
    }

    function clearError() {
        errorBox.textContent = '';
        errorBox.hidden = true;
    }

    function setLoading(active) {
        loading.hidden = !active;
    }

    function renderSuggestions(items) {
        list.innerHTML = '';

        items.forEach((item, index) => {
            const card = document.createElement('button');
            card.type = 'button';
            card.className = 'metier-suggestion-card' + (index === selectedIndex ? ' is-selected' : '');
            card.innerHTML = '<h4 class="metier-suggestion-card__title"></h4><p class="metier-suggestion-card__text"></p>';
            card.querySelector('.metier-suggestion-card__title').textContent = item.metier;
            card.querySelector('.metier-suggestion-card__text').textContent = item.justification;
            card.addEventListener('click', function () {
                selectedIndex = index;
                updateSelectedState();
                fillSelectedSuggestion();
            });
            list.appendChild(card);
        });
    }

    function updateSelectedState() {
        const cards = list.querySelectorAll('.metier-suggestion-card');
        cards.forEach((card, index) => {
            card.classList.toggle('is-selected', index === selectedIndex);
        });
    }

    function fillSelectedSuggestion() {
        if (!activeForm || selectedIndex < 0 || !suggestions[selectedIndex]) {
            return;
        }

        const metierField = activeForm.querySelector('[name="metier_suggere"]');
        if (metierField) {
            metierField.value = suggestions[selectedIndex].metier;
            metierField.dispatchEvent(new Event('input', { bubbles: true }));
            metierField.dispatchEvent(new Event('change', { bubbles: true }));
        }

        // Fill score_rse if available from API response
        if (window.lastSuggestionResponse && window.lastSuggestionResponse.score_rse) {
            const scoreField = activeForm.querySelector('[name="score_rse"]');
            if (scoreField) {
                scoreField.value = window.lastSuggestionResponse.score_rse;
                scoreField.dispatchEvent(new Event('input', { bubbles: true }));
                scoreField.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }
    }

    async function requestSuggestions(trigger) {
        activeForm = trigger.closest('form');
        if (!activeForm) {
            setError('Impossible de localiser le formulaire.');
            openModal();
            return;
        }

        const payload = {
            type_handicap: activeForm.querySelector('[name="type_handicap"]')?.value || '',
            amenagements: activeForm.querySelector('[name="amenagements"]')?.value || '',
            poste_cible: activeForm.querySelector('[name="poste_cible"]')?.value || '',
            genre: activeForm.querySelector('[name="genre"]')?.value || '',
            type_entretien_id: activeForm.querySelector('[name="type_entretien_id"]')?.value || '',
        };

        clearError();
        setLoading(true);
        scoreValue.textContent = '0';
        list.innerHTML = '';
        openModal();

        try {
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload),
            });

            const data = await response.json();
            if (!response.ok) {
                throw new Error(data.error || 'Impossible de générer des suggestions.');
            }

            // Store the full response for later use (especially score_rse)
            window.lastSuggestionResponse = data;

            suggestions = Array.isArray(data.suggestions) ? data.suggestions : [];
            selectedIndex = suggestions.length > 0 ? 0 : -1;
            scoreValue.textContent = Number.isFinite(Number(data.score_rse)) ? String(data.score_rse) : '0';

            if (suggestions.length === 0) {
                setError('Aucune suggestion n\'a pu être générée.');
                return;
            }

            renderSuggestions(suggestions);
            fillSelectedSuggestion();
        } catch (error) {
            setError(error.message || 'Erreur inattendue lors de la génération.');
        } finally {
            setLoading(false);
        }
    }

    document.addEventListener('click', function (event) {
        const trigger = event.target.closest(triggerSelector);
        if (trigger) {
            event.preventDefault();
            requestSuggestions(trigger);
        }
    });

    closeTargets.forEach(function (button) {
        button.addEventListener('click', closeModal);
    });

    useButton.addEventListener('click', function () {
        fillSelectedSuggestion();
        closeModal();
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && modal.classList.contains('is-open')) {
            closeModal();
        }
    });
})();
</script>
