# -*- coding: utf-8 -*-
"""Applique le menu personnalisé à tous les fichiers html/*.html qui ont menu-inner."""
from pathlib import Path
import re
import sys

SCRIPTS = Path(__file__).resolve().parent
sys.path.insert(0, str(SCRIPTS))

from apply_custom_menu import NEW_MENU, replace_menu_inner  # noqa: E402

ROOT = Path(__file__).resolve().parents[1]
HTML_DIR = ROOT / "html"

# Pages sous « Tableau de bord » : href exact dans le menu + section parente à ouvrir
_GESTION_HREFS = {
    "gestion-utilisateurs.html": "gestion-utilisateurs.html",
    "gestion-offres.html": "gestion-offres.html",
    "gestion-contrats.html": "gestion-contrats.html",
    "gestion-formations.html": "gestion-formations.html",
    "gestion-inscriptions.html": "gestion-inscriptions.html",
    "gestion-certificats.html": "gestion-certificats.html",
    "gestion-reclamations.html": "gestion-reclamations.html",
    "gestion-entretiens.html": "gestion-entretiens.html",
    "gestion-produits.html": "gestion-produits.html",
}


def menu_without_index_active(menu: str) -> str:
    m = menu.replace(
        '<li class="menu-item active open">\n              <a href="javascript:void(0);" class="menu-link menu-toggle">\n                <i class="menu-icon tf-icons bx bx-home-smile"></i>',
        '<li class="menu-item">\n              <a href="javascript:void(0);" class="menu-link menu-toggle">\n                <i class="menu-icon tf-icons bx bx-home-smile"></i>',
        1,
    )
    return m.replace(
        '<li class="menu-item active">\n                  <a href="index.html" class="menu-link">',
        '<li class="menu-item">\n                  <a href="index.html" class="menu-link">',
        1,
    )


def _open_tableau_de_bord(menu: str) -> str:
    return re.sub(
        r'(<li class="menu-item)(?=">\s*<a href="javascript:void\(0\);" class="menu-link menu-toggle">\s*'
        r'<i class="menu-icon tf-icons bx bx-home-smile"></i>)',
        r"\1 active open",
        menu,
        count=1,
    )


def _open_menu_toggle_parent(menu: str, label: str) -> str:
    """Ouvre un sous-menu repliable (Utilisateurs, Offres, Formations)."""
    esc = re.escape(label)
    return re.sub(
        rf'(<li class="menu-item)(?=">\s*<a href="javascript:void\(0\);" class="menu-link menu-toggle">\s*'
        rf'<div class="text-truncate">{esc}</div>)',
        r"\1 open",
        menu,
        count=1,
    )


def _activate_leaf_by_href(menu: str, href: str) -> str:
    pat = (
        r'(<li class="menu-item)(?=">\s*<a\s+href="' + re.escape(href) + r'" class="menu-link">)'
    )
    return re.sub(pat, r"\1 active", menu, count=1)


def highlight_dashboard_page(menu: str, basename: str) -> str:
    """Surbrillance + ouverture des bons sous-menus pour les pages gestion / profil compte."""
    if basename == "pages-account-settings-account.html":
        m = _open_tableau_de_bord(menu)
        return _open_menu_toggle_parent(m, "Utilisateurs")

    href = _GESTION_HREFS.get(basename)
    if not href:
        return menu

    m = _open_tableau_de_bord(menu)
    if basename == "gestion-utilisateurs.html":
        m = _open_menu_toggle_parent(m, "Utilisateurs")
    elif basename in ("gestion-offres.html", "gestion-contrats.html"):
        m = _open_menu_toggle_parent(m, "Offres")
    elif basename in (
        "gestion-formations.html",
        "gestion-inscriptions.html",
        "gestion-certificats.html",
    ):
        m = _open_menu_toggle_parent(m, "Formations")
    return _activate_leaf_by_href(m, href)


def highlight_profil(menu: str, basename: str) -> str:
    if basename != "pages-account-settings-account.html":
        return menu
    return re.sub(
        r'(<li class="menu-item)(?=">\s*<a\s+href="pages-account-settings-account\.html" class="menu-link">)',
        r"\1 active",
        menu,
        count=1,
    )


def menu_for_file(basename: str) -> str:
    if basename == "index.html":
        m = NEW_MENU
    else:
        m = menu_without_index_active(NEW_MENU)
        m = highlight_dashboard_page(m, basename)
        m = highlight_profil(m, basename)
    return m


def main():
    marker = '<ul class="menu-inner py-1">'
    updated = 0
    for path in sorted(HTML_DIR.glob("*.html")):
        text = path.read_text(encoding="utf-8")
        if marker not in text:
            continue
        try:
            new_html = replace_menu_inner(text, menu_for_file(path.name))
        except SystemExit as e:
            print("SKIP (erreur menu):", path.name, e)
            continue
        path.write_text(new_html, encoding="utf-8")
        updated += 1
        print("OK", path.name)
    print("---")
    print("Fichiers mis à jour:", updated)


if __name__ == "__main__":
    main()
