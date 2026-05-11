# -*- coding: utf-8 -*-
"""Génère les pages gestion-*.html à partir de html/index.html."""
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
INDEX = ROOT / "html" / "index.html"

PAGES = [
    {
        "file": "gestion-utilisateurs.html",
        "page_id": "utilisateurs",
        "title": "Utilisateurs",
        "intro": "Comptes candidats, employeurs et accompagnateurs. Données indicatives.",
        "headers": ["Nom", "Rôle", "Besoins / RQTH", "Statut"],
        "rows": [
            ("Martin L.", "Candidat", "Mobilité réduite — poste télétravail", "Actif"),
            ("TechAccess SARL", "Employeur", "—", "Vérifié"),
            ("S. Benali", "Accompagnateur", "—", "Actif"),
        ],
    },
    {
        "file": "gestion-formations.html",
        "page_id": "formations",
        "title": "Formations",
        "intro": "Sessions de remise à niveau et compétences numériques accessibles.",
        "headers": ["Formation", "Durée", "Places", "Modalité", "Statut", "Actions"],
        "rows": [
            ("Outils bureautiques adaptés", "3 jours", "12/20", "Présentiel aménagé", "Ouverte"),
            ("Préparation entretien", "1 jour", "8/15", "Visio", "Ouverte"),
            ("Anglais professionnel", "6 sem.", "0/12", "Hybride", "Complète"),
        ],
    },
    {
        "file": "gestion-offres.html",
        "page_id": "offres",
        "title": "Offres d'emploi",
        "intro": "Postes ouverts avec mention des aménagements possibles.",
        "headers": ["Poste", "Entreprise", "Contrat", "Aménagements", "Statut", "Actions"],
        "rows": [
            ("Développeur front-end", "TechAccess", "CDI", "TT partiel, horaires flexibles", "Publiée"),
            ("Assistant·e RH", "Solidarité Pro", "CDD", "Open space calme", "En validation"),
            ("Conseiller·ère client", "Call Inclusif", "CDI", "Matériel vocal adapté", "Publiée"),
        ],
    },
    {
        "file": "gestion-entretiens.html",
        "page_id": "entretiens",
        "title": "Entretiens",
        "intro": "Planification et suivi des entretiens (présentiel, visio, accessibilité).",
        "headers": ["Candidat", "Poste", "Date", "Mode", "Statut", "Actions"],
        "rows": [
            ("Martin L.", "Développeur front-end", "15 avr. 14h", "Visio (sous-titres)", "Confirmé"),
            ("N. Kaya", "Assistant·e RH", "18 avr. 10h", "Sur site (accès PMR)", "À confirmer"),
            ("R. Dupont", "Conseiller·ère", "22 avr. 9h", "Téléphone", "Planifié"),
        ],
    },
    {
        "file": "gestion-reclamations.html",
        "page_id": "reclamations",
        "title": "Réclamations",
        "intro": "Signalements accessibilité, discrimination ou dysfonctionnement du service.",
        "headers": ["Réf.", "Sujet", "Auteur", "Priorité", "Statut", "Actions"],
        "rows": [
            ("R-104", "Contraste insuffisant sur formulaire", "Candidat", "Haute", "En cours"),
            ("R-103", "Délai de réponse employeur", "Employeur", "Moyenne", "Ouverte"),
            ("R-102", "Lien cassé page formation", "Candidat", "Basse", "Résolue"),
        ],
    },
    {
        "file": "gestion-produits.html",
        "page_id": "produits",
        "title": "Produits & services",
        "intro": "Matériel, logiciels d'assistance ou services liés à l'emploi inclusif.",
        "headers": ["Produit", "Catégorie", "Stock", "Prix TTC", "Statut", "Actions"],
        "rows": [
            ("Licence lecteur d'écran Pro", "Logiciel", "Illimité", "120 €", "Actif"),
            ("Casque anti-bruit", "Équipement", "34", "89 €", "Actif"),
            ("Audit accessibilité site", "Service", "—", "Sur devis", "Actif"),
        ],
    },
]


def table_html(headers, rows):
    th = "".join(f"<th>{h}</th>" for h in headers)
    body = ""
    for row in rows:
        cells = "".join(f"<td>{c}</td>" for c in row)
        body += f"""                      <tr>
                        {cells}
                        <td>
                          <div class="dropdown">
                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown" aria-expanded="false">
                              <i class="icon-base bx bx-dots-vertical-rounded"></i>
                            </button>
                            <div class="dropdown-menu">
                              <a class="dropdown-item" href="javascript:void(0);"><i class="icon-base bx bx-edit-alt me-1"></i> Modifier</a>
                              <a class="dropdown-item" href="javascript:void(0);"><i class="icon-base bx bx-trash me-1"></i> Supprimer</a>
                            </div>
                          </div>
                        </td>
                      </tr>
"""
    return f"""              <div class="card">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                  <h5 class="mb-0">Liste</h5>
                  <button type="button" class="btn btn-sm btn-primary">Ajouter</button>
                </div>
                <div class="px-4 pb-3">
                  <label for="platform-table-search" class="form-label small text-body-secondary mb-1">Filtrer le tableau</label>
                  <input type="search" id="platform-table-search" class="form-control form-control-sm" placeholder="Tapez pour filtrer les lignes…" autocomplete="off" />
                </div>
                <div class="table-responsive text-nowrap">
                  <table class="table table-hover mb-0" data-platform-table>
                    <thead>
                      <tr>
                        {th}
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
{body}                    </tbody>
                  </table>
                </div>
              </div>
"""


def page_inner(p):
    intro = p["intro"].replace("'", "&apos;")
    title = p["title"].replace("'", "&apos;")
    tbl = table_html(p["headers"], p["rows"])
    return f"""
              <h4 class="fw-bold py-3 mb-1">{title}</h4>
              <p class="text-muted mb-4">{intro}</p>
{tbl}"""


def main():
    text = INDEX.read_text(encoding="utf-8")
    marker_open = '<div id="main-content" class="container-xxl flex-grow-1 container-p-y" tabindex="-1">'
    marker_close = "\n            <!-- / Content -->"
    oi = text.index(marker_open)
    end_open = oi + len(marker_open)
    ci = text.index(marker_close, end_open)
    prefix = text[:end_open]
    suffix = text[ci:]

    for p in PAGES:
        inner = page_inner(p)
        out = prefix + inner + suffix
        out = out.replace(
            '<body data-platform-page="dashboard">',
            f'<body data-platform-page="{p["page_id"]}">',
            1,
        )
        title_esc = (
            p["title"]
            .replace("&", "&amp;")
            .replace("<", "&lt;")
        )
        import re

        out = re.sub(
            r"<title>.*?</title>",
            f"<title>IncluEmploi — {title_esc}</title>",
            out,
            count=1,
            flags=re.DOTALL,
        )
        (ROOT / "html" / p["file"]).write_text(out, encoding="utf-8")
        print("Wrote", p["file"])

    # Append platform-tables.js to gestion pages only — patch suffix in generated files
    for p in PAGES:
        fp = ROOT / "html" / p["file"]
        t = fp.read_text(encoding="utf-8")
        if "platform-tables.js" not in t:
            t = t.replace(
                '<script src="../assets/js/main.js"></script>',
                '<script src="../assets/js/main.js"></script>\n    <script src="../assets/js/platform-tables.js"></script>',
                1,
            )
            fp.write_text(t, encoding="utf-8")


if __name__ == "__main__":
    main()
