<style>
/* ═══════════════════════════════════════════
   BOUTON ACCESSIBILITÉ FIXE
═══════════════════════════════════════════ */
#accessibility-widget {
    position: fixed;
    bottom: 28px;
    right: 28px;
    z-index: 9999;
    font-family: 'DM Sans', sans-serif;
}
#accessibility-btn {
    width: 52px;
    height: 52px;
    border-radius: 50%;
    background: #7c3aed;
    color: #fff;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 18px rgba(124,58,237,.35);
    transition: background .2s, transform .2s;
    font-size: 22px;
}
#accessibility-btn:hover { background: #5b21b6; transform: scale(1.08); }

#accessibility-label {
    position: absolute;
    bottom: 58px;
    right: 0;
    background: #fff;
    border: 1px solid #ede9fe;
    border-radius: 8px;
    padding: 4px 10px;
    font-size: 11px;
    font-weight: 600;
    color: #7c3aed;
    white-space: nowrap;
    box-shadow: 0 2px 8px rgba(0,0,0,.08);
    display: none;
}
#accessibility-menu {
    position: absolute;
    bottom: 64px;
    right: 0;
    background: #fff;
    border: 1px solid #ede9fe;
    border-radius: 12px;
    box-shadow: 0 8px 32px rgba(124,58,237,.15);
    padding: 8px;
    min-width: 190px;
    display: none;
    flex-direction: column;
    gap: 4px;
    animation: slideUp .18s ease;
}
@keyframes slideUp {
    from { opacity:0; transform:translateY(10px); }
    to   { opacity:1; transform:translateY(0); }
}
.access-option {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    color: #374151;
    border: none;
    background: transparent;
    width: 100%;
    text-align: left;
    transition: background .15s;
}
.access-option:hover { background: #f5f3ff; color: #7c3aed; }
.access-option.active { background: #ede9fe; color: #7c3aed; font-weight: 700; }
.access-option i { font-size: 18px; }

/* ═══════════════════════════════════════════
   MODE DALTONISME
═══════════════════════════════════════════ */
body.mode-daltonisme a,
body.mode-daltonisme .btn-outline-primary,
body.mode-daltonisme .btn-primary {
    background: #1d4ed8 !important;
    border-color: #1d4ed8 !important;
    color: #fff !important;
}
body.mode-daltonisme [data-type-badge]::before {
    font-size: 10px;
    margin-right: 3px;
}
body.mode-daltonisme [data-type-badge="CDI"]::before    { content: '■ '; }
body.mode-daltonisme [data-type-badge="CDD"]::before    { content: '● '; }
body.mode-daltonisme [data-type-badge="Freelance"]::before { content: '▲ '; }
body.mode-daltonisme [data-type-badge="Stage"]::before  { content: '◆ '; }
body.mode-daltonisme .invalid-feedback {
    color: #1e3a8a !important;
    font-weight: 600;
}
body.mode-daltonisme .invalid-feedback::before { content: '⚠ '; }
body.mode-daltonisme .is-invalid { border-color: #1e3a8a !important; }

/* ═══════════════════════════════════════════
   MODE MALVOYANCE
═══════════════════════════════════════════ */
body.mode-malvoyance { background: #fff !important; color: #111 !important; }

body.mode-malvoyance h1,
body.mode-malvoyance h2,
body.mode-malvoyance h3,
body.mode-malvoyance h4,
body.mode-malvoyance h5 {
    color: #000 !important;
    font-weight: 900 !important;
    font-size: 1.6em !important;
}
body.mode-malvoyance p,
body.mode-malvoyance li,
body.mode-malvoyance span,
body.mode-malvoyance div {
    font-size: 17px !important;
    line-height: 2.2 !important;
    color: #111 !important;
}
body.mode-malvoyance .btn {
    font-size: 18px !important;
    padding: 14px 32px !important;
    font-weight: 800 !important;
    border-width: 3px !important;
}
body.mode-malvoyance .form-control {
    font-size: 17px !important;
    padding: 12px !important;
    border: 2px solid #000 !important;
}
body.mode-malvoyance label {
    font-size: 18px !important;
    font-weight: 700 !important;
    display: block !important;
    margin-bottom: 6px !important;
    color: #000 !important;
}
body.mode-malvoyance .fa,
body.mode-malvoyance .ti,
body.mode-malvoyance .bx {
    font-size: 1.5em !important;
}
body.mode-malvoyance .nav-link,
body.mode-malvoyance .site-menu a {
    font-size: 17px !important;
    font-weight: 700 !important;
    color: #000 !important;
}
</style>

<!-- WIDGET ACCESSIBILITÉ -->
<div id="accessibility-widget">
    <div id="accessibility-label"></div>
    <div id="accessibility-menu">
        <button class="access-option" id="btn-daltonisme" onclick="setAccessMode('daltonisme')">
            <i class="fa fa-eye"></i> Daltonisme
        </button>
        <button class="access-option" id="btn-malvoyance" onclick="setAccessMode('malvoyance')">
            <i class="fa fa-low-vision"></i> Malvoyance
        </button>
    </div>
    <button id="accessibility-btn" title="Accessibilité visuelle">
        <i class="fa fa-eye"></i>
    </button>
</div>

<script>
(function () {
    var widget  = document.getElementById('accessibility-widget');
    var menu    = document.getElementById('accessibility-menu');
    var label   = document.getElementById('accessibility-label');
    var modes   = ['daltonisme', 'malvoyance'];
    var labels  = { daltonisme: 'Daltonisme', malvoyance: 'Malvoyance' };
    var current = null;

    widget.addEventListener('mouseenter', function () { menu.style.display = 'flex'; });
    widget.addEventListener('mouseleave', function () { menu.style.display = 'none'; });

    function applyMode(mode) {
        modes.forEach(function (m) {
            document.body.classList.remove('mode-' + m);
            var el = document.getElementById('btn-' + m);
            if (el) el.classList.remove('active');
        });
        if (mode && mode !== current) {
            document.body.classList.add('mode-' + mode);
            var el = document.getElementById('btn-' + mode);
            if (el) el.classList.add('active');
            label.textContent = labels[mode];
            label.style.display = 'block';
            current = mode;
            localStorage.setItem('takwinibot_access', mode);
        } else {
            label.style.display = 'none';
            current = null;
            localStorage.removeItem('takwinibot_access');
        }
    }

    window.setAccessMode = function (mode) {
        applyMode(mode);
        menu.style.display = 'none';
    };

    // Restaurer depuis localStorage
    var saved = localStorage.getItem('takwinibot_access');
    if (saved && modes.indexOf(saved) !== -1) {
        applyMode(saved);
    }
})();
</script>
