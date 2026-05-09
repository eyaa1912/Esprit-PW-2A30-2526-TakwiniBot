/* ═══════════════════════════════════════════
   WIDGET ACCESSIBILITÉ VISUELLE — TAKWINIBOT
═══════════════════════════════════════════ */
(function () {
    var MODES  = ['daltonisme', 'malvoyance', 'dyslexie', 'concentration', 'anxiete'];
    var current = null;
    var menuOpen = false;

    /* ── Widget HTML ── */
    var widget = document.createElement('div');
    widget.id = 'accessibility-widget';
    widget.innerHTML =
        '<div id="accessibility-label"></div>' +
        '<button id="accessibility-btn" title="Accessibilité visuelle" onclick="AccessWidget.toggle()">' +
        '  &#128065;' +
        '</button>';
    document.body.appendChild(widget);

    /* ── Menu séparé directement dans body ── */
    var menuContainer = document.createElement('div');
    menuContainer.id = 'accessibility-menu';
    menuContainer.innerHTML =
        '<div style="padding:8px 12px 4px;font-size:11px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.06em;">Visuel</div>' +
        '<button class="access-option" id="btn-daltonisme" onclick="AccessWidget.set(\'daltonisme\')">' +
        '  <span>👁</span> Daltonisme' +
        '</button>' +
        '<button class="access-option" id="btn-malvoyance" onclick="AccessWidget.set(\'malvoyance\')">' +
        '  <span>🔍</span> Malvoyance' +
        '</button>' +
        '<div style="height:1px;background:#e5e7eb;margin:6px 4px;"></div>' +
        '<div style="padding:4px 12px;font-size:11px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.06em;">Cognitif</div>' +
        '<button class="access-option" id="btn-dyslexie" onclick="AccessWidget.set(\'dyslexie\')">' +
        '  <span>📖</span> Dyslexie' +
        '</button>' +
        '<button class="access-option" id="btn-concentration" onclick="AccessWidget.set(\'concentration\')">' +
        '  <span>🎯</span> Concentration (TDAH)' +
        '</button>' +
        '<button class="access-option" id="btn-anxiete" onclick="AccessWidget.set(\'anxiete\')">' +
        '  <span>💙</span> Anxiété' +
        '</button>' +
        '<div style="height:1px;background:#e5e7eb;margin:6px 4px;"></div>' +
        '<button class="access-option" id="btn-normal" onclick="AccessWidget.set(\'normal\')">' +
        '  <span>↺</span> Mode normal' +
        '</button>';
    document.body.appendChild(menuContainer);

    var menuEl  = menuContainer;
    var labelEl = document.getElementById('accessibility-label');
    var btnEl   = document.getElementById('accessibility-btn');

    /* ── Ouvrir/fermer au clic ET au survol ── */
    btnEl.addEventListener('click', function (e) {
        e.stopPropagation();
        menuOpen = !menuOpen;
        menuEl.style.display = menuOpen ? 'flex' : 'none';
    });

    /* Fermer si clic ailleurs */
    document.addEventListener('click', function () {
        menuOpen = false;
        menuEl.style.display = 'none';
    });

    widget.addEventListener('click', function (e) { e.stopPropagation(); });

    /* ── Appliquer un mode ── */
    function applyMode(mode) {
        MODES.forEach(function (m) {
            document.body.classList.remove('mode-' + m);
        });
        document.querySelectorAll('.access-option').forEach(function (b) {
            b.classList.remove('active');
        });

        if (mode === 'normal' || !mode) {
            labelEl.style.display = 'none';
            labelEl.textContent   = '';
            current = null;
            localStorage.removeItem('takwinibot_access');
            var nb = document.getElementById('btn-normal');
            if (nb) nb.classList.add('active');
            restoreHoverEffects();
        } else {
            document.body.classList.add('mode-' + mode);
            var b = document.getElementById('btn-' + mode);
            if (b) b.classList.add('active');
            var labels = {
                daltonisme: 'Daltonisme', malvoyance: 'Malvoyance',
                dyslexie: 'Dyslexie', concentration: 'Concentration',
                anxiete: 'Anxiété', epilepsie: 'Épilepsie'
            };
            labelEl.textContent   = labels[mode] || mode;
            labelEl.style.display = 'block';
            current = mode;
            localStorage.setItem('takwinibot_access', mode);
            // Désactiver les effets hover inline pour épilepsie et concentration
            if (mode === 'epilepsie' || mode === 'concentration') {
                disableHoverEffects();
            } else {
                restoreHoverEffects();
            }
        }

        /* Fermer le menu après sélection */
        menuOpen = false;
        menuEl.style.display = 'none';
    }

    /* ── Désactiver les effets hover inline (cartes contrats) ── */
    function disableHoverEffects() {
        document.querySelectorAll('[onmouseover]').forEach(function (el) {
            el.dataset.origMouseover = el.getAttribute('onmouseover');
            el.dataset.origMouseout  = el.getAttribute('onmouseout') || '';
            el.removeAttribute('onmouseover');
            el.removeAttribute('onmouseout');
            el.style.transition = 'none';
            el.style.transform  = '';
        });
    }

    /* ── Restaurer les effets hover inline ── */
    function restoreHoverEffects() {
        document.querySelectorAll('[data-orig-mouseover]').forEach(function (el) {
            el.setAttribute('onmouseover', el.dataset.origMouseover);
            if (el.dataset.origMouseout) el.setAttribute('onmouseout', el.dataset.origMouseout);
            el.style.transition = '';
            delete el.dataset.origMouseover;
            delete el.dataset.origMouseout;
        });
    }

    /* ── API publique ── */
    window.AccessWidget = {
        set    : applyMode,
        toggle : function () {
            menuOpen = !menuOpen;
            menuEl.style.display = menuOpen ? 'flex' : 'none';
        }
    };

    /* ── Restaurer depuis localStorage ── */
    var saved = localStorage.getItem('takwinibot_access');
    if (saved && MODES.indexOf(saved) !== -1) {
        applyMode(saved);
    }
})();
