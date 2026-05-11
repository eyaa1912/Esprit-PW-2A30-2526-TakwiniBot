# -*- coding: utf-8 -*-
"""
Ajoute : langue, mise en page, raccourcis, notifications, thème clair/sombre,
palette de couleurs (engrenage), liens menu utilisateur, navbar-extras.js.
"""
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
HTML_DIR = ROOT / "html"

TOOLBAR_MARKER = "<!-- app-toolbar-extras -->"

TOOLBAR_LANG_LAYOUT = """
                <li class="nav-item dropdown me-2 me-xl-1">
                  <a
                    class="nav-link dropdown-toggle hide-arrow"
                    href="javascript:void(0);"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                    aria-label="Langue">
                    <i class="icon-base bx bx-globe icon-md"></i>
                  </a>
                  <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                      <a class="dropdown-item" href="javascript:void(0);" data-app-lang="fr">Français</a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="javascript:void(0);" data-app-lang="en">English</a>
                    </li>
                  </ul>
                </li>
                <li class="nav-item dropdown me-2 me-xl-1">
                  <a
                    class="nav-link dropdown-toggle hide-arrow"
                    href="javascript:void(0);"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                    aria-label="Disposition du menu">
                    <i class="icon-base bx bx-layout icon-md"></i>
                  </a>
                  <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                      <a class="dropdown-item" href="javascript:void(0);" data-app-layout="vertical">Menu vertical</a>
                    </li>
                    <li>
                      <a
                        class="dropdown-item"
                        href="https://demos.themeselection.com/sneat-bootstrap-html-admin-template/html/horizontal-menu-template/"
                        target="_blank"
                        rel="noopener"
                        >Menu horizontal <span class="badge bg-label-primary ms-1 text-uppercase fs-tiny">Pro</span></a
                      >
                    </li>
                  </ul>
                </li>
"""

TOOLBAR_EXTRAS = """
                <li class="nav-item dropdown-shortcuts navbar-dropdown dropdown me-2 me-xl-1">
                  <a
                    class="nav-link dropdown-toggle hide-arrow"
                    href="javascript:void(0);"
                    data-bs-toggle="dropdown"
                    data-bs-auto-close="outside"
                    aria-expanded="false"
                    aria-label="Raccourcis">
                    <i class="icon-base bx bx-grid-alt icon-md"></i>
                  </a>
                  <div class="dropdown-menu dropdown-menu-end border-0 p-0">
                    <div class="dropdown-shortcuts-list">
                      <div class="row row-bordered g-0">
                        <div class="dropdown-shortcuts-item col-6 border-end border-bottom">
                          <a href="gestion-formations.html" class="d-flex flex-column align-items-center justify-content-center gap-2 py-4 text-body">
                            <span class="dropdown-shortcuts-icon rounded-circle d-inline-flex"><i class="icon-base bx bx-book-open icon-md"></i></span>
                            <small class="text-body-secondary">Formations</small>
                          </a>
                        </div>
                        <div class="dropdown-shortcuts-item col-6 border-bottom">
                          <a href="gestion-offres.html" class="d-flex flex-column align-items-center justify-content-center gap-2 py-4 text-body">
                            <span class="dropdown-shortcuts-icon rounded-circle d-inline-flex"><i class="icon-base bx bx-briefcase icon-md"></i></span>
                            <small class="text-body-secondary">Offres</small>
                          </a>
                        </div>
                        <div class="dropdown-shortcuts-item col-6 border-end">
                          <a href="gestion-reclamations.html" class="d-flex flex-column align-items-center justify-content-center gap-2 py-4 text-body">
                            <span class="dropdown-shortcuts-icon rounded-circle d-inline-flex"><i class="icon-base bx bx-error-circle icon-md"></i></span>
                            <small class="text-body-secondary">Réclamations</small>
                          </a>
                        </div>
                        <div class="dropdown-shortcuts-item col-6">
                          <a href="gestion-produits.html" class="d-flex flex-column align-items-center justify-content-center gap-2 py-4 text-body">
                            <span class="dropdown-shortcuts-icon rounded-circle d-inline-flex"><i class="icon-base bx bx-cart icon-md"></i></span>
                            <small class="text-body-secondary">Produits</small>
                          </a>
                        </div>
                      </div>
                    </div>
                  </div>
                </li>
                <li class="nav-item dropdown-notifications navbar-dropdown dropdown me-2 me-xl-1">
                  <a
                    class="nav-link dropdown-toggle hide-arrow"
                    href="javascript:void(0);"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                    aria-label="Notifications">
                    <span class="position-relative">
                      <i class="icon-base bx bx-bell icon-md"></i>
                      <span
                        class="badge rounded-pill bg-danger badge-notifications border border-2 border-card position-absolute top-0 start-100 translate-middle px-1"
                        >3</span
                      >
                    </span>
                  </a>
                  <ul class="dropdown-menu dropdown-menu-end dropdown-notifications-list">
                    <li class="dropdown-menu-header border-bottom">
                      <div class="d-flex align-items-center px-3 py-3">
                        <h6 class="mb-0">Notifications</h6>
                      </div>
                    </li>
                    <li class="list-group list-group-flush">
                      <a href="gestion-reclamations.html" class="list-group-item list-group-item-action dropdown-notifications-item">
                        <div class="d-flex align-items-center gap-3">
                          <div class="flex-shrink-0"><div class="avatar avatar-sm bg-label-primary"><i class="icon-base bx bx-error icon-sm"></i></div></div>
                          <div class="flex-grow-1">
                            <p class="mb-0 small">Réclamation à traiter</p>
                            <small class="text-body-secondary">Plateforme</small>
                          </div>
                        </div>
                      </a>
                      <a href="gestion-entretiens.html" class="list-group-item list-group-item-action dropdown-notifications-item">
                        <div class="d-flex align-items-center gap-3">
                          <div class="flex-shrink-0"><div class="avatar avatar-sm bg-label-warning"><i class="icon-base bx bx-calendar icon-sm"></i></div></div>
                          <div class="flex-grow-1">
                            <p class="mb-0 small">Entretien planifié demain</p>
                            <small class="text-body-secondary">Rappel</small>
                          </div>
                        </div>
                      </a>
                    </li>
                  </ul>
                </li>
                <li class="nav-item me-2 me-xl-1">
                  <a class="nav-link" href="javascript:void(0);" id="app-theme-toggle" aria-label="Basculer thème clair ou sombre">
                    <i class="icon-base bx bx-moon icon-md" id="app-theme-toggle-icon"></i>
                  </a>
                </li>
                <li class="nav-item dropdown me-2 me-xl-1">
                  <a
                    class="nav-link dropdown-toggle hide-arrow"
                    href="javascript:void(0);"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                    aria-label="Couleurs et apparence">
                    <i class="icon-base bx bx-cog icon-md"></i>
                  </a>
                  <ul class="dropdown-menu dropdown-menu-end">
                    <li><h6 class="dropdown-header">Couleur principale</h6></li>
                    <li>
                      <a class="dropdown-item" href="javascript:void(0);" data-app-primary-reset="1">Par défaut (Sneat)</a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="javascript:void(0);" data-app-primary="#696cff" data-app-primary-rgb="105, 108, 255">Violet</a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="javascript:void(0);" data-app-primary="#71dd37" data-app-primary-rgb="113, 221, 55">Vert</a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="javascript:void(0);" data-app-primary="#03c3ec" data-app-primary-rgb="3, 195, 236">Cyan</a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="javascript:void(0);" data-app-primary="#ff3e1d" data-app-primary-rgb="255, 62, 29">Rouge</a>
                    </li>
                    <li><div class="dropdown-divider"></div></li>
                    <li>
                      <a class="dropdown-item" href="pages-account-settings-account.html"
                        ><i class="icon-base bx bx-user icon-sm me-2"></i>Paramètres du compte</a
                      >
                    </li>
                  </ul>
                </li>
"""

SCRIPT_TAG = '\n    <script src="../assets/js/navbar-extras.js"></script>'

_AFTER_LAYOUT_TOOLBAR_ANCHOR = """                    </li>
                  </ul>
                </li>

                <!-- Place this tag where you want the button to render. -->"""


def inject_toolbar(html: str) -> str:
    if TOOLBAR_MARKER in html:
        return html

    block = TOOLBAR_MARKER + TOOLBAR_EXTRAS

    if _AFTER_LAYOUT_TOOLBAR_ANCHOR in html and "data-app-lang" in html:
        return html.replace(_AFTER_LAYOUT_TOOLBAR_ANCHOR, "                    </li>\n                  </ul>\n                </li>\n" + block + "\n\n                <!-- Place this tag where you want the button to render. -->", 1)

    needle = """              <ul class="navbar-nav flex-row align-items-center ms-md-auto">
                <!-- Place this tag where you want the button to render. -->"""
    if needle not in html:
        return html
    return html.replace(
        needle,
        """              <ul class="navbar-nav flex-row align-items-center ms-md-auto">"""
        + TOOLBAR_LANG_LAYOUT
        + block
        + """
                <!-- Place this tag where you want the button to render. -->""",
        1,
    )


def fix_user_menu(html: str) -> str:
    html = html.replace(
        """<a class="dropdown-item" href="#">
                        <i class="icon-base bx bx-user icon-md me-3"></i><span>My Profile</span>
                      </a>""",
        """<a class="dropdown-item" href="pages-account-settings-account.html">
                        <i class="icon-base bx bx-user icon-md me-3"></i><span>My Profile</span>
                      </a>""",
    )
    html = html.replace(
        """<a class="dropdown-item" href="#">
                        <i class="icon-base bx bx-cog icon-md me-3"></i><span>Settings</span>
                      </a>""",
        """<a class="dropdown-item" href="pages-account-settings-account.html">
                        <i class="icon-base bx bx-cog icon-md me-3"></i><span>Settings</span>
                      </a>""",
    )
    html = html.replace(
        """<a class="dropdown-item" href="javascript:void(0);">
                        <i class="icon-base bx bx-power-off icon-md me-3"></i><span>Log Out</span>
                      </a>""",
        """<a class="dropdown-item" href="auth-login-basic.html">
                        <i class="icon-base bx bx-power-off icon-md me-3"></i><span>Log Out</span>
                      </a>""",
    )
    html = html.replace(
        """<a class="dropdown-item" href="#">
                        <div class="d-flex">
                          <div class="flex-shrink-0 me-3">
                            <div class="avatar avatar-online">
                              <img src="../assets/img/avatars/1.png" alt class="w-px-40 h-auto rounded-circle" />
                            </div>
                          </div>
                          <div class="flex-grow-1">
                            <h6 class="mb-0">John Doe</h6>
                            <small class="text-body-secondary">Admin</small>
                          </div>
                        </div>
                      </a>""",
        """<a class="dropdown-item" href="pages-account-settings-account.html">
                        <div class="d-flex">
                          <div class="flex-shrink-0 me-3">
                            <div class="avatar avatar-online">
                              <img src="../assets/img/avatars/1.png" alt class="w-px-40 h-auto rounded-circle" />
                            </div>
                          </div>
                          <div class="flex-grow-1">
                            <h6 class="mb-0">John Doe</h6>
                            <small class="text-body-secondary">Admin</small>
                          </div>
                        </div>
                      </a>""",
    )
    return html


def inject_script(html: str) -> str:
    if "navbar-extras.js" in html:
        return html
    if '<script src="../assets/js/main.js"></script>' in html:
        return html.replace(
            '<script src="../assets/js/main.js"></script>',
            '<script src="../assets/js/main.js"></script>' + SCRIPT_TAG,
            1,
        )
    return html


def process_file(path: Path) -> bool:
    html = path.read_text(encoding="utf-8")
    if 'id="layout-navbar"' not in html or "dropdown-user" not in html:
        return False
    new = inject_toolbar(html)
    new = fix_user_menu(new)
    new = inject_script(new)
    if new != html:
        path.write_text(new, encoding="utf-8")
        return True
    return False


def main():
    n = 0
    for path in sorted(HTML_DIR.glob("*.html")):
        if process_file(path):
            n += 1
            print("OK", path.name)
    print("Fichiers modifiés:", n)


if __name__ == "__main__":
    main()
