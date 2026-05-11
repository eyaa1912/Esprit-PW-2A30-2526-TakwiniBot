# -*- coding: utf-8 -*-
"""Met à jour html/index.html pour la plateforme IncluEmploi."""
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
INDEX = ROOT / "html" / "index.html"

NEW_MENU = """          <ul class="menu-inner py-1" id="platform-menu-inner"></ul>
"""

NEW_CONTENT = """            <div class="container-xxl flex-grow-1 container-p-y">
              <h4 class="fw-bold py-3 mb-2">
                <span class="text-muted fw-normal">Tableau de bord ·</span> IncluEmploi
              </h4>
              <p class="text-muted mb-4">
                Plateforme d&apos;emploi inclusive : offres adaptées, formations et accompagnement pour les personnes en
                situation de handicap.
              </p>

              <div class="row mb-4">
                <div class="col-sm-6 col-lg-3 mb-4">
                  <div class="card h-100">
                    <div class="card-body">
                      <div class="d-flex justify-content-between align-items-start">
                        <div>
                          <span class="d-block mb-1 text-body-secondary small">Offres publiées</span>
                          <h3 class="card-title mb-0">24</h3>
                          <small class="text-success">+3 cette semaine</small>
                        </div>
                        <div class="avatar">
                          <span class="avatar-initial rounded bg-label-primary">
                            <i class="icon-base bx bx-briefcase icon-md text-primary"></i>
                          </span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-sm-6 col-lg-3 mb-4">
                  <div class="card h-100">
                    <div class="card-body">
                      <div class="d-flex justify-content-between align-items-start">
                        <div>
                          <span class="d-block mb-1 text-body-secondary small">Candidatures</span>
                          <h3 class="card-title mb-0">156</h3>
                          <small class="text-body-secondary">tous profils</small>
                        </div>
                        <div class="avatar">
                          <span class="avatar-initial rounded bg-label-info">
                            <i class="icon-base bx bx-user icon-md text-info"></i>
                          </span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-sm-6 col-lg-3 mb-4">
                  <div class="card h-100">
                    <div class="card-body">
                      <div class="d-flex justify-content-between align-items-start">
                        <div>
                          <span class="d-block mb-1 text-body-secondary small">Entretiens planifiés</span>
                          <h3 class="card-title mb-0">18</h3>
                          <small class="text-body-secondary">7 jours</small>
                        </div>
                        <div class="avatar">
                          <span class="avatar-initial rounded bg-label-success">
                            <i class="icon-base bx bx-calendar-check icon-md text-success"></i>
                          </span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-sm-6 col-lg-3 mb-4">
                  <div class="card h-100">
                    <div class="card-body">
                      <div class="d-flex justify-content-between align-items-start">
                        <div>
                          <span class="d-block mb-1 text-body-secondary small">Formations en cours</span>
                          <h3 class="card-title mb-0">9</h3>
                          <small class="text-body-secondary">sessions actives</small>
                        </div>
                        <div class="avatar">
                          <span class="avatar-initial rounded bg-label-warning">
                            <i class="icon-base bx bx-book-open icon-md text-warning"></i>
                          </span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-lg-8 mb-4 mb-lg-0">
                  <div class="card h-100">
                    <h5 class="card-header">À traiter</h5>
                    <div class="card-body">
                      <ul class="list-unstyled mb-0">
                        <li class="d-flex align-items-center mb-4 pb-3 border-bottom">
                          <span class="badge bg-label-danger rounded p-2 me-3"><i class="icon-base bx bx-message-square-error"></i></span>
                          <div>
                            <strong>5 réclamations</strong>
                            <span class="text-body-secondary d-block small">Accessibilité ou accompagnement — à examiner</span>
                          </div>
                          <a href="gestion-reclamations.html" class="btn btn-sm btn-outline-primary ms-auto">Voir</a>
                        </li>
                        <li class="d-flex align-items-center mb-4 pb-3 border-bottom">
                          <span class="badge bg-label-primary rounded p-2 me-3"><i class="icon-base bx bx-briefcase"></i></span>
                          <div>
                            <strong>8 offres</strong>
                            <span class="text-body-secondary d-block small">En attente de validation</span>
                          </div>
                          <a href="gestion-offres.html" class="btn btn-sm btn-outline-primary ms-auto">Voir</a>
                        </li>
                        <li class="d-flex align-items-center">
                          <span class="badge bg-label-info rounded p-2 me-3"><i class="icon-base bx bx-calendar"></i></span>
                          <div>
                            <strong>6 entretiens</strong>
                            <span class="text-body-secondary d-block small">Cette semaine (aménagements à confirmer)</span>
                          </div>
                          <a href="gestion-entretiens.html" class="btn btn-sm btn-outline-primary ms-auto">Voir</a>
                        </li>
                      </ul>
                    </div>
                  </div>
                </div>
                <div class="col-lg-4">
                  <div class="card h-100 border border-primary border-opacity-25 shadow-none">
                    <div class="card-body">
                      <h5 class="card-title text-primary mb-3">Engagement accessibilité</h5>
                      <p class="small mb-3">
                        Priorité aux parcours clairs, contrastes lisibles et formulaires compréhensibles. Les modules
                        <strong>Utilisateurs</strong>, <strong>Offres</strong> et <strong>Entretiens</strong> permettent de
                        noter les besoins d&apos;aménagement (télétravail, horaires, équipement).
                      </p>
                      <p class="small text-body-secondary mb-0">
                        Données de démonstration — branchez votre API ou votre base de données pour les chiffres réels.
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
"""


def main():
    text = INDEX.read_text(encoding="utf-8")
    lines = text.splitlines(keepends=True)

    # Menu: lines 127-605 (1-based) -> indices 126:605
    new_lines = lines[:126] + [NEW_MENU] + lines[605:]

    # Re-find content block after menu patch (line numbers shifted)
    full = "".join(new_lines)
    lines = full.splitlines(keepends=True)

    start_marker = '            <div class="container-xxl flex-grow-1 container-p-y">\n'
    end_marker = "            <!-- / Content -->\n"

    try:
        si = lines.index(start_marker)
        ei = lines.index(end_marker)
    except ValueError as e:
        raise SystemExit(f"Markers not found: {e}") from e

    lines = lines[:si] + [NEW_CONTENT + "\n"] + lines[ei:]

    INDEX.write_text("".join(lines), encoding="utf-8")
    print("Patched", INDEX)


if __name__ == "__main__":
    main()
