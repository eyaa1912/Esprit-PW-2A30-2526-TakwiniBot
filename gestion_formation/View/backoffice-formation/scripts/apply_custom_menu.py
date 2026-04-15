# -*- coding: utf-8 -*-
"""Menu latéral personnalisé (index + pages gestion) selon spécification utilisateur."""
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
INDEX = ROOT / "html" / "index.html"

NEW_MENU = r"""          <ul class="menu-inner py-1">
            <!-- Tableau de bord : accueil + modules plateforme (pas de démos externes Académie / e-commerce) -->
            <li class="menu-item active open">
              <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-home-smile"></i>
                <div class="text-truncate">Tableau de bord</div>
              </a>
              <ul class="menu-sub">
                <li class="menu-item active">
                  <a href="index.html" class="menu-link">
                    <div class="text-truncate">Accueil</div>
                  </a>
                </li>
                <li class="menu-item">
                  <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <div class="text-truncate">Formations</div>
                  </a>
                  <ul class="menu-sub">
                    <li class="menu-item">
                      <a href="gestion-formations.html" class="menu-link">
                        <div class="text-truncate">Vue d&apos;ensemble</div>
                      </a>
                    </li>
                    <li class="menu-item">
                      <a href="gestion-formations.html#sessions" class="menu-link">
                        <div class="text-truncate">Nos formations</div>
                      </a>
                    </li>
                    <li class="menu-item">
                      <a href="gestion-inscriptions.html" class="menu-link">
                        <div class="text-truncate">Inscriptions</div>
                      </a>
                    </li>
                    <li class="menu-item">
                      <a href="gestion-certificats.html" class="menu-link">
                        <div class="text-truncate">Certificats</div>
                      </a>
                    </li>
                  </ul>
                </li>
                <li class="menu-item">
                  <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <div class="text-truncate">Offres</div>
                  </a>
                  <ul class="menu-sub">
                    <li class="menu-item">
                      <a href="gestion-offres.html" class="menu-link">
                        <div class="text-truncate">Liste des offres</div>
                      </a>
                    </li>
                    <li class="menu-item">
                      <a href="gestion-contrats.html" class="menu-link">
                        <div class="text-truncate">Contrats</div>
                      </a>
                    </li>
                  </ul>
                </li>
                <li class="menu-item">
                  <a href="gestion-reclamations.html" class="menu-link">
                    <div class="text-truncate">Réclamations</div>
                  </a>
                </li>
                <li class="menu-item">
                  <a href="gestion-entretiens.html" class="menu-link">
                    <div class="text-truncate">Entretiens</div>
                  </a>
                </li>
                <li class="menu-item">
                  <a href="gestion-produits.html" class="menu-link">
                    <div class="text-truncate">Produits</div>
                  </a>
                </li>
                <li class="menu-item">
                  <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <div class="text-truncate">Utilisateurs</div>
                  </a>
                  <ul class="menu-sub">
                    <li class="menu-item">
                      <a href="gestion-utilisateurs.html" class="menu-link">
                        <div class="text-truncate">Liste des utilisateurs</div>
                      </a>
                    </li>
                    <li class="menu-item">
                      <a href="pages-account-settings-account.html" class="menu-link">
                        <div class="text-truncate">Profil</div>
                      </a>
                    </li>
                  </ul>
                </li>
              </ul>
            </li>

            <li class="menu-header small text-uppercase">
              <span class="menu-header-text">Applications</span>
            </li>
            <li class="menu-item">
              <a href="email-boite.html" class="menu-link">
                <i class="menu-icon tf-icons bx bx-envelope"></i>
                <div class="text-truncate">Email</div>
              </a>
            </li>
            <li class="menu-item">
              <a href="app-chat-local.html" class="menu-link">
                <i class="menu-icon tf-icons bx bx-chat"></i>
                <div class="text-truncate">Discuter</div>
              </a>
            </li>
            <li class="menu-item">
              <a href="app-calendrier-local.html" class="menu-link">
                <i class="menu-icon tf-icons bx bx-calendar"></i>
                <div class="text-truncate">Calendrier</div>
              </a>
            </li>

            <li class="menu-item">
              <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-lock-open-alt"></i>
                <div class="text-truncate">Authentification</div>
              </a>
              <ul class="menu-sub">
                <li class="menu-item">
                  <a href="auth-login-basic.html" class="menu-link" target="_blank">
                    <div class="text-truncate">Connexion</div>
                  </a>
                </li>
                <li class="menu-item">
                  <a href="auth-register-basic.html" class="menu-link" target="_blank">
                    <div class="text-truncate">Inscription</div>
                  </a>
                </li>
                <li class="menu-item">
                  <a href="auth-forgot-password-basic.html" class="menu-link" target="_blank">
                    <div class="text-truncate">Mot de passe oublié</div>
                  </a>
                </li>
              </ul>
            </li>
          </ul>"""


def replace_menu_inner(html: str, new_menu: str) -> str:
    start = html.find('<ul class="menu-inner py-1">')
    if start == -1:
        raise SystemExit("menu-inner not found")
    i = start + len('<ul class="menu-inner py-1">')
    depth = 1
    while depth and i < len(html):
        nu = html.find("<ul", i)
        nc = html.find("</ul>", i)
        if nc == -1:
            raise SystemExit("unclosed ul")
        if nu != -1 and nu < nc:
            depth += 1
            i = nu + 3
        else:
            depth -= 1
            if depth == 0:
                end = nc + len("</ul>")
                return html[:start] + new_menu + html[end:]
            i = nc + 5
    raise SystemExit("parse error")


ACTIONS = """                        <td>
                          <div class="dropdown">
                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                              <i class="icon-base bx bx-dots-vertical-rounded"></i>
                            </button>
                            <div class="dropdown-menu">
                              <a class="dropdown-item" href="javascript:void(0);"><i class="icon-base bx bx-edit-alt me-1"></i> Modifier</a>
                              <a class="dropdown-item" href="javascript:void(0);"><i class="icon-base bx bx-trash me-1"></i> Supprimer</a>
                            </div>
                          </div>
                        </td>"""


def table_block(title, headers, rows):
    th = "".join(f"<th>{h}</th>" for h in headers)
    body = ""
    for r in rows:
        tds = "".join(f"<td>{c}</td>" for c in r)
        body += f"                      <tr>\n{tds}\n{ACTIONS}\n                      </tr>\n"
    return f"""
              <div class="card mb-6">
                <div class="card-header d-flex align-items-center justify-content-between">
                  <h5 class="mb-0">{title}</h5>
                  <button type="button" class="btn btn-sm btn-primary">Ajouter</button>
                </div>
                <div class="table-responsive text-nowrap">
                  <table class="table">
                    <thead><tr>{th}<th>Actions</th></tr></thead>
                    <tbody class="table-border-bottom-0">
{body}                    </tbody>
                  </table>
                </div>
              </div>"""


def page_body(heading, intro, sections):
    parts = [f'              <h4 class="fw-bold py-3 mb-2">{heading}</h4>']
    if intro:
        parts.append(f'              <p class="text-muted mb-4">{intro}</p>')
    for sec in sections:
        parts.append(table_block(sec["title"], sec["headers"], sec["rows"]))
    return "\n".join(parts) + "\n"


PAGES = {
    "gestion-offres.html": {
        "title": "Offres | Tableaux",
        "body": page_body(
            "Offres d&apos;emploi",
            "Trois tableaux (style Sneat) : liste principale, brouillons, archives.",
            [
                {
                    "title": "Offres actives",
                    "headers": ["Poste", "Entreprise", "Type", "Statut"],
                    "rows": [
                        ("Développeur web", "TechAccess", "CDI", '<span class="badge bg-label-primary">Publiée</span>'),
                        ("Assistant RH", "Solidarité Pro", "CDD", '<span class="badge bg-label-primary">Publiée</span>'),
                    ],
                },
                {
                    "title": "Brouillons",
                    "headers": ["Poste", "Entreprise", "Maj", "Statut"],
                    "rows": [
                        ("Analyste données", "Nova Labs", "10/04", '<span class="badge bg-label-warning">Brouillon</span>'),
                    ],
                },
                {
                    "title": "Archives",
                    "headers": ["Poste", "Entreprise", "Clôture", "Statut"],
                    "rows": [
                        ("Support client", "Call Inclusif", "15/03", '<span class="badge bg-label-secondary">Archivée</span>'),
                    ],
                },
            ],
        ),
    },
    "gestion-formations.html": {
        "title": "Formations | Tableaux",
        "body": "",  # défini après fix_formations_page()
    },
    "gestion-reclamations.html": {
        "title": "Réclamations | Tableaux",
        "body": page_body(
            "Réclamations",
            "Ouvertes, en cours, clôturées.",
            [
                {
                    "title": "Ouvertes",
                    "headers": ["Réf.", "Sujet", "Priorité", "Statut"],
                    "rows": [
                        ("R-12", "Accessibilité formulaire", "Haute", '<span class="badge bg-label-danger">Ouverte</span>'),
                    ],
                },
                {
                    "title": "En cours",
                    "headers": ["Réf.", "Assigné", "Maj", "Statut"],
                    "rows": [
                        ("R-09", "Support", "08/04", '<span class="badge bg-label-warning">Traitement</span>'),
                    ],
                },
                {
                    "title": "Clôturées",
                    "headers": ["Réf.", "Sujet", "Date", "Statut"],
                    "rows": [
                        ("R-04", "Lien cassé", "20/03", '<span class="badge bg-label-secondary">Fermée</span>'),
                    ],
                },
            ],
        ),
    },
    "gestion-produits.html": {
        "title": "Produits | Tableaux",
        "body": page_body(
            "Produits",
            "Catalogue (sans clients ni parrainage — uniquement produits / stock).",
            [
                {
                    "title": "Catalogue",
                    "headers": ["Produit", "Catégorie", "Prix", "Statut"],
                    "rows": [
                        ("Licence lecteur d&apos;écran", "Logiciel", "120 €", '<span class="badge bg-label-success">Actif</span>'),
                    ],
                },
                {
                    "title": "Stock",
                    "headers": ["SKU", "Qté", "Entrepôt", "Statut"],
                    "rows": [
                        ("SKU-104", "34", "Principal", '<span class="badge bg-label-primary">OK</span>'),
                    ],
                },
                {
                    "title": "Promotions",
                    "headers": ["Produit", "Réduction", "Fin", "Statut"],
                    "rows": [
                        ("Casque anti-bruit", "-15%", "30/04", '<span class="badge bg-label-warning">Active</span>'),
                    ],
                },
            ],
        ),
    },
    "gestion-entretiens.html": {
        "title": "Entretiens | Tableaux",
        "body": "",  # défini ci-dessous (page_body_v2)
    },
    "gestion-utilisateurs.html": {
        "title": "Utilisateurs | Tableaux",
        "body": page_body(
            "Utilisateurs",
            "Même présentation que les autres écrans de gestion : Ajouter, puis menu ⋮ pour modifier ou supprimer.",
            [
                {
                    "title": "Candidats",
                    "headers": ["Nom", "Email", "Profil", "Statut"],
                    "rows": [
                        ("Sophie Martin", "s.martin@mail.com", "RQTH — mobilité", '<span class="badge bg-label-success">Actif</span>'),
                        ("Alex Rivera", "a.r@mail.com", "Audition", '<span class="badge bg-label-warning">En attente</span>'),
                    ],
                },
                {
                    "title": "Employeurs",
                    "headers": ["Entreprise", "Contact", "Offres", "Statut"],
                    "rows": [
                        ("TechAccess", "rh@tech.io", "4", '<span class="badge bg-label-primary">Vérifié</span>'),
                    ],
                },
                {
                    "title": "Accompagnateurs",
                    "headers": ["Nom", "Structure", "Région", "Statut"],
                    "rows": [
                        ("N. Karim", "Cap Emploi", "IDF", '<span class="badge bg-label-info">Actif</span>'),
                    ],
                },
            ],
        ),
    },
    "gestion-inscriptions.html": {
        "title": "Inscriptions | Formations",
        "body": page_body(
            "Inscriptions aux formations",
            "Gérez les inscriptions : tableau et actions Modifier / Supprimer.",
            [
                {
                    "title": "En attente de validation",
                    "headers": ["Stagiaire", "Formation", "Date demande", "Statut"],
                    "rows": [
                        ("J. Petit", "Bureautique accessible", "05/04", '<span class="badge bg-label-warning">À valider</span>'),
                    ],
                },
                {
                    "title": "Confirmées",
                    "headers": ["Stagiaire", "Session", "Date début", "Statut"],
                    "rows": [
                        ("M. Dupont", "Bureautique — avril", "12/04", '<span class="badge bg-label-success">Confirmée</span>'),
                    ],
                },
                {
                    "title": "Annulées / refusées",
                    "headers": ["Stagiaire", "Formation", "Motif", "Statut"],
                    "rows": [
                        ("L. Noir", "Anglais pro", "Places complètes", '<span class="badge bg-label-secondary">Refusée</span>'),
                    ],
                },
            ],
        ),
    },
    "gestion-certificats.html": {
        "title": "Certificats | Formations",
        "body": page_body(
            "Certificats",
            "Émission et suivi des certificats (modifier / supprimer depuis le menu ⋮).",
            [
                {
                    "title": "Émis",
                    "headers": ["Réf.", "Stagiaire", "Formation", "Date", "Statut"],
                    "rows": [
                        ("CERT-104", "L. Martin", "Prépa entretien", "28/03", '<span class="badge bg-label-success">Valide</span>'),
                    ],
                },
                {
                    "title": "En préparation",
                    "headers": ["Stagiaire", "Formation", "Session", "Statut"],
                    "rows": [
                        ("A. Ben", "Bureautique", "Avril", '<span class="badge bg-label-warning">Génération</span>'),
                    ],
                },
                {
                    "title": "Expirés / révoqués",
                    "headers": ["Réf.", "Stagiaire", "Fin validité", "Statut"],
                    "rows": [
                        ("CERT-011", "M. X.", "01/01/25", '<span class="badge bg-label-secondary">Expiré</span>'),
                    ],
                },
            ],
        ),
    },
    "email-boite.html": {
        "title": "Email | Application",
        "body": page_body(
            "Boîte mail",
            "Page locale : plus de redirection vers l&apos;ancienne démo Sneat Pro. Remplacez ce bloc par votre webmail ou API métier.",
            [],
        ),
    },
    "app-chat-local.html": {
        "title": "Messagerie | Application",
        "body": page_body(
            "Discussions",
            "Module chat local (démo). À connecter à votre service de messagerie interne.",
            [],
        ),
    },
    "app-calendrier-local.html": {
        "title": "Calendrier | Application",
        "body": page_body(
            "Calendrier",
            "Agenda local (démo). Intégrez ici FullCalendar, Outlook ou Google Calendar selon vos besoins.",
            [],
        ),
    },
    "gestion-contrats.html": {
        "title": "Contrats | Tableaux",
        "body": page_body(
            "Contrats",
            "Lié aux offres : brouillons, signés, archivés.",
            [
                {
                    "title": "Brouillons",
                    "headers": ["Réf.", "Offre", "Type", "Statut"],
                    "rows": [
                        ("C-22", "Dev web TechAccess", "CDI", '<span class="badge bg-label-warning">Brouillon</span>'),
                    ],
                },
                {
                    "title": "Signés",
                    "headers": ["Réf.", "Signataire", "Date", "Statut"],
                    "rows": [
                        ("C-18", "M. Martin", "25/03", '<span class="badge bg-label-success">Signé</span>'),
                    ],
                },
                {
                    "title": "Archives",
                    "headers": ["Réf.", "Offre", "Fin", "Statut"],
                    "rows": [
                        ("C-02", "Stage RH", "31/12/24", '<span class="badge bg-label-secondary">Archivé</span>'),
                    ],
                },
            ],
        ),
    },
}


def build_table_with_ids(sec):
    """Si section a 'id', ajoute id sur la card."""
    title = sec["title"]
    hid = sec.get("id")
    id_attr = f' id="{hid}"' if hid else ""
    th = "".join(f"<th>{h}</th>" for h in sec["headers"])
    body = ""
    for r in sec["rows"]:
        tds = "".join(f"<td>{c}</td>" for c in r)
        body += f"                      <tr>\n{tds}\n{ACTIONS}\n                      </tr>\n"
    return f"""
              <div class="card mb-6"{id_attr}>
                <div class="card-header d-flex align-items-center justify-content-between">
                  <h5 class="mb-0">{title}</h5>
                  <button type="button" class="btn btn-sm btn-primary">Ajouter</button>
                </div>
                <div class="table-responsive text-nowrap">
                  <table class="table">
                    <thead><tr>{th}<th>Actions</th></tr></thead>
                    <tbody class="table-border-bottom-0">
{body}                    </tbody>
                  </table>
                </div>
              </div>"""


def page_body_v2(heading, intro, sections):
    parts = [f'              <h4 class="fw-bold py-3 mb-2">{heading}</h4>']
    if intro:
        parts.append(f'              <p class="text-muted mb-4">{intro}</p>')
    for sec in sections:
        parts.append(build_table_with_ids(sec))
    return "\n".join(parts) + "\n"


def fix_formations_page():
    """Uniquement les formations (inscriptions / certificats : pages dédiées)."""
    secs = [
        {
            "title": "Sessions publiées",
            "id": "sessions",
            "headers": ["Titre", "Durée", "Places", "Statut"],
            "rows": [
                ("Bureautique accessible", "3 j", "12/20", '<span class="badge bg-label-success">Ouverte</span>'),
                ("Prépa entretien", "1 j", "8/15", '<span class="badge bg-label-primary">Ouverte</span>'),
            ],
        },
        {
            "title": "Formateurs",
            "headers": ["Nom", "Spécialité", "Sessions", "Statut"],
            "rows": [
                ("C. Dubois", "Numérique", "5", '<span class="badge bg-label-success">Actif</span>'),
            ],
        },
        {
            "title": "À planifier",
            "headers": ["Thème", "Demandeur", "Date souhaitée", "Statut"],
            "rows": [
                ("Anglais pro", "Pôle emploi", "Mai", '<span class="badge bg-label-warning">En attente</span>'),
            ],
        },
    ]
    return page_body_v2("Nos formations", "Tableaux avec actions Modifier / Supprimer (menu ⋮).", secs)


# Corriger entrée formations dans PAGES
PAGES["gestion-formations.html"]["body"] = fix_formations_page()
PAGES["gestion-entretiens.html"]["body"] = page_body_v2(
    "Entretiens",
    "Ancre #types sur le premier tableau.",
    [
        {
            "title": "Types d&apos;entretien",
            "id": "types",
            "headers": ["Type", "Durée", "Modalité", "Statut"],
            "rows": [
                ("Technique", "45 min", "Visio ST", '<span class="badge bg-label-primary">Actif</span>'),
                ("RH", "30 min", "Présentiel", '<span class="badge bg-label-primary">Actif</span>'),
            ],
        },
        {
            "title": "À venir",
            "headers": ["Candidat", "Poste", "Date", "Statut"],
            "rows": [
                ("A. Ben", "Dev web", "12/04 14h", '<span class="badge bg-label-warning">Planifié</span>'),
            ],
        },
        {
            "title": "Terminés",
            "headers": ["Candidat", "Poste", "Date", "Statut"],
            "rows": [
                ("S. Kaya", "Assistant", "01/04", '<span class="badge bg-label-secondary">Terminé</span>'),
            ],
        },
    ],
)


def replace_main_content(html: str, inner: str) -> str:
    start = '<div class="container-xxl flex-grow-1 container-p-y">'
    end = "\n            <!-- / Content -->"
    i0 = html.find(start)
    i1 = html.find(end, i0)
    if i0 == -1 or i1 == -1:
        raise SystemExit("content markers not found")
    return html[: i0 + len(start)] + "\n" + inner + html[i1:]


def set_title(html: str, title: str) -> str:
    import re

    return re.sub(r"<title>.*?</title>", f"<title>{title}</title>", html, count=1, flags=re.DOTALL)


def strip_dashboard_active(html: str) -> str:
    html = html.replace(
        '<li class="menu-item active open">\n              <a href="javascript:void(0);" class="menu-link menu-toggle">\n                <i class="menu-icon tf-icons bx bx-home-smile"></i>',
        '<li class="menu-item">\n              <a href="javascript:void(0);" class="menu-link menu-toggle">\n                <i class="menu-icon tf-icons bx bx-home-smile"></i>',
        1,
    )
    html = html.replace(
        '<li class="menu-item active">\n                  <a href="index.html" class="menu-link">\n                    <div class="text-truncate">Accueil</div>',
        '<li class="menu-item">\n                  <a href="index.html" class="menu-link">\n                    <div class="text-truncate">Accueil</div>',
        1,
    )
    return html


def remove_chart_scripts(html: str) -> str:
    html = html.replace(
        '\n    <!-- Vendors JS -->\n    <script src="../assets/vendor/libs/apex-charts/apexcharts.js"></script>\n\n    <!-- Main JS -->',
        "\n    <!-- Main JS -->",
        1,
    )
    return html.replace(
        '\n    <!-- Page JS -->\n    <script src="../assets/js/dashboards-analytics.js"></script>', "", 1
    )


def main():
    html = INDEX.read_text(encoding="utf-8")
    html = replace_menu_inner(html, NEW_MENU)
    INDEX.write_text(html, encoding="utf-8")
    print("Menu mis à jour :", INDEX)

    template = INDEX.read_text(encoding="utf-8")
    for fname, spec in PAGES.items():
        h = strip_dashboard_active(template)
        h = set_title(h, spec["title"])
        h = replace_main_content(h, spec["body"])
        h = remove_chart_scripts(h)
        (ROOT / "html" / fname).write_text(h, encoding="utf-8")
        print("Écrit", fname)


if __name__ == "__main__":
    main()
