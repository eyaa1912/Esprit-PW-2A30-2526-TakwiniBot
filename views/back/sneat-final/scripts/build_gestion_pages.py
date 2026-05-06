# -*- coding: utf-8 -*-
"""Menu Gestion + notifications navbar ; pages avec 3 tableaux style Sneat."""
import re
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
INDEX = ROOT / "html" / "index.html"

MARK_START = "            <!-- GESTION_MENU_START -->\n"
MARK_END = "            <!-- GESTION_MENU_END -->\n"


def gestion_block(active_key):
    """active_key: None (index) ou nom de page."""
    parent = "active open" if active_key else ""

    def act(k):
        return "active" if active_key == k else ""

    return (
        MARK_START
        + """            <li class="menu-header small text-uppercase"><span class="menu-header-text">Gestion emploi</span></li>
            <li class="menu-item """
        + parent
        + """">
              <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-folder-open"></i>
                <div class="text-truncate">Gestion</div>
              </a>
              <ul class="menu-sub">
                <li class="menu-item """
        + act("utilisateurs")
        + """">
                  <a href="gestion-utilisateurs.html" class="menu-link">
                    <div class="text-truncate">Utilisateurs</div>
                  </a>
                </li>
                <li class="menu-item """
        + act("formations")
        + """">
                  <a href="gestion-formations.html" class="menu-link">
                    <div class="text-truncate">Formations</div>
                  </a>
                </li>
                <li class="menu-item """
        + act("offres")
        + """">
                  <a href="gestion-offres.html" class="menu-link">
                    <div class="text-truncate">Offres d&apos;emploi</div>
                  </a>
                </li>
                <li class="menu-item """
        + act("entretiens")
        + """">
                  <a href="gestion-entretiens.html" class="menu-link">
                    <div class="text-truncate">Entretiens</div>
                  </a>
                </li>
                <li class="menu-item """
        + act("reclamations")
        + """">
                  <a href="gestion-reclamations.html" class="menu-link">
                    <div class="text-truncate">Réclamations</div>
                  </a>
                </li>
              </ul>
            </li>
"""
        + MARK_END
    )


def apply_gestion_menu(html, active_key):
    block = gestion_block(active_key)
    region = re.compile(
        r"            <!-- GESTION_MENU_START -->.*?            <!-- GESTION_MENU_END -->\n",
        re.DOTALL,
    )
    if region.search(html):
        return region.sub(block, html, count=1)
    return html.replace(
        "            <!-- Pages -->",
        block + "            <!-- Pages -->",
        1,
    )


NOTIFICATIONS_BLOCK = """
                <li class="nav-item navbar-dropdown dropdown-notifications dropdown me-3 me-xl-2">
                  <a
                    class="nav-link dropdown-toggle hide-arrow"
                    href="javascript:void(0);"
                    data-bs-toggle="dropdown"
                    aria-expanded="false">
                    <span class="position-relative">
                      <i class="icon-base bx bx-bell icon-md"></i>
                      <span class="badge rounded-pill bg-danger badge-dot badge-notifications border"></span>
                    </span>
                  </a>
                  <ul class="dropdown-menu dropdown-menu-end p-0" style="min-width: 22rem">
                    <li class="dropdown-menu-header border-bottom">
                      <div class="d-flex align-items-center px-4 py-3">
                        <h6 class="mb-0 me-auto">Notifications</h6>
                        <span class="badge rounded-pill bg-label-primary">4 New</span>
                      </div>
                    </li>
                    <li class="list-group list-group-flush dropdown-notifications-list p-0">
                      <div class="dropdown-notifications-item border-bottom">
                        <div class="d-flex px-4 py-3 align-items-start">
                          <span class="avatar-initial rounded-2 bg-label-primary me-3"><i class="icon-base bx bx-briefcase"></i></span>
                          <div>
                            <p class="mb-0 small fw-medium">New application received</p>
                            <small class="text-body-secondary">Inclusive job offer — 2 min ago</small>
                          </div>
                        </div>
                      </div>
                      <div class="dropdown-notifications-item border-bottom">
                        <div class="d-flex px-4 py-3 align-items-start">
                          <span class="avatar-initial rounded-2 bg-label-success me-3"><i class="icon-base bx bx-calendar-check"></i></span>
                          <div>
                            <p class="mb-0 small fw-medium">Interview scheduled</p>
                            <small class="text-body-secondary">Accessibility adjustments requested</small>
                          </div>
                        </div>
                      </div>
                      <div class="dropdown-notifications-item">
                        <div class="d-flex px-4 py-3 align-items-start">
                          <span class="avatar-initial rounded-2 bg-label-warning me-3"><i class="icon-base bx bx-message-square-error"></i></span>
                          <div>
                            <p class="mb-0 small fw-medium">New complaint ticket</p>
                            <small class="text-body-secondary">Priority: medium</small>
                          </div>
                        </div>
                      </div>
                    </li>
                    <li class="border-top text-center py-2">
                      <a href="pages-account-settings-notifications.html" class="btn btn-sm btn-text-primary">View all notifications</a>
                    </li>
                  </ul>
                </li>
"""


def add_notifications(html):
    if "dropdown-notifications" in html:
        return html
    return html.replace(
        '              <ul class="navbar-nav flex-row align-items-center ms-md-auto">\n                <!-- Place this tag where you want the button to render. -->',
        '              <ul class="navbar-nav flex-row align-items-center ms-md-auto">'
        + NOTIFICATIONS_BLOCK
        + "\n                <!-- Place this tag where you want the button to render. -->",
        1,
    )


def fix_settings_link(html):
    return html.replace(
        '<a class="dropdown-item" href="#">\n                        <i class="icon-base bx bx-cog icon-md me-3"></i><span>Settings</span>\n                      </a>',
        '<a class="dropdown-item" href="pages-account-settings-account.html">\n                        <i class="icon-base bx bx-cog icon-md me-3"></i><span>Settings</span>\n                      </a>',
        1,
    )


def strip_dashboard_active(html):
    html = html.replace(
        '<li class="menu-item active open">\n              <a href="javascript:void(0);" class="menu-link menu-toggle">\n                <i class="menu-icon tf-icons bx bx-home-smile"></i>',
        '<li class="menu-item">\n              <a href="javascript:void(0);" class="menu-link menu-toggle">\n                <i class="menu-icon tf-icons bx bx-home-smile"></i>',
        1,
    )
    html = html.replace(
        '<li class="menu-item active">\n                  <a href="index.html" class="menu-link">\n                    <div class="text-truncate" data-i18n="Analytics">Analytics</div>',
        '<li class="menu-item">\n                  <a href="index.html" class="menu-link">\n                    <div class="text-truncate" data-i18n="Analytics">Analytics</div>',
        1,
    )
    return html


ACTIONS_CELL = """                        <td>
                          <div class="dropdown">
                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                              <i class="icon-base bx bx-dots-vertical-rounded"></i>
                            </button>
                            <div class="dropdown-menu">
                              <a class="dropdown-item" href="javascript:void(0);"><i class="icon-base bx bx-plus-circle me-1"></i> Add</a>
                              <a class="dropdown-item" href="javascript:void(0);"><i class="icon-base bx bx-edit-alt me-1"></i> Edit</a>
                              <a class="dropdown-item" href="javascript:void(0);"><i class="icon-base bx bx-trash me-1"></i> Delete</a>
                            </div>
                          </div>
                        </td>"""


def table_card(title, headers, rows):
    th = "".join(f"<th>{h}</th>" for h in headers)
    trs = []
    for r in rows:
        tds = "".join(f"<td>{c}</td>" for c in r)
        trs.append(f"                      <tr>\n{tds}\n{ACTIONS_CELL}\n                      </tr>")
    body = "\n".join(trs)
    return f"""
              <div class="card mb-6">
                <div class="card-header d-flex align-items-center justify-content-between">
                  <h5 class="mb-0">{title}</h5>
                  <button type="button" class="btn btn-sm btn-primary">Add</button>
                </div>
                <div class="table-responsive text-nowrap">
                  <table class="table">
                    <thead>
                      <tr>
                        {th}
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
{body}
                    </tbody>
                  </table>
                </div>
              </div>"""


def page_content(spec):
    parts = [f'              <h4 class="fw-bold py-3 mb-2">{spec["heading"]}</h4>']
    if spec.get("intro"):
        parts.append(f'              <p class="text-muted mb-4">{spec["intro"]}</p>')
    for t in spec["tables"]:
        parts.append(table_card(t["title"], t["headers"], t["rows"]))
    return "\n".join(parts) + "\n"


PAGES = {
    "utilisateurs": {
        "heading": "Gestion — Utilisateurs",
        "intro": "Trois tableaux (candidats, employeurs, accompagnateurs) — actions Add / Edit / Delete comme le thème Sneat.",
        "tables": [
            {
                "title": "Candidats",
                "headers": ["Name", "Email", "Profile", "Status"],
                "rows": [
                    ("Sophie Martin", "s.martin@mail.com", "Mobility", '<span class="badge bg-label-success me-1">Active</span>'),
                    ("Alex Rivera", "a.rivera@mail.com", "Hearing", '<span class="badge bg-label-warning me-1">Pending</span>'),
                ],
            },
            {
                "title": "Employeurs",
                "headers": ["Company", "Contact", "Offers", "Status"],
                "rows": [
                    ("TechAccess", "hr@techaccess.io", "6", '<span class="badge bg-label-primary me-1">Verified</span>'),
                    ("Solidarity Pro", "jobs@solpro.org", "2", '<span class="badge bg-label-success me-1">Active</span>'),
                ],
            },
            {
                "title": "Accompagnateurs",
                "headers": ["Name", "Organization", "Region", "Status"],
                "rows": [
                    ("Dr. Nora K.", "Cap Emploi", "IDF", '<span class="badge bg-label-info me-1">Active</span>'),
                    ("M. Ben Y.", "Association H+", "PACA", '<span class="badge bg-label-success me-1">Active</span>'),
                ],
            },
        ],
    },
    "formations": {
        "heading": "Gestion — Formations",
        "intro": "Sessions, formateurs et inscriptions.",
        "tables": [
            {
                "title": "Sessions planifiées",
                "headers": ["Title", "Dates", "Seats", "Status"],
                "rows": [
                    ("Accessible Office", "Apr 12–14", "12/20", '<span class="badge bg-label-primary me-1">Open</span>'),
                    ("Interview prep", "Apr 20", "8/15", '<span class="badge bg-label-primary me-1">Open</span>'),
                ],
            },
            {
                "title": "Formateurs",
                "headers": ["Name", "Topic", "Sessions", "Status"],
                "rows": [
                    ("Claire Dubois", "Digital skills", "4", '<span class="badge bg-label-success me-1">Active</span>'),
                    ("Omar Haddad", "Soft skills", "2", '<span class="badge bg-label-warning me-1">Busy</span>'),
                ],
            },
            {
                "title": "Inscriptions",
                "headers": ["Trainee", "Session", "Registered", "Status"],
                "rows": [
                    ("Sophie Martin", "Accessible Office", "Apr 01", '<span class="badge bg-label-success me-1">Confirmed</span>'),
                    ("Alex Rivera", "Interview prep", "Apr 02", '<span class="badge bg-label-warning me-1">Waiting</span>'),
                ],
            },
        ],
    },
    "offres": {
        "heading": "Gestion — Offres d&apos;emploi",
        "intro": "Publiées, brouillons et archives.",
        "tables": [
            {
                "title": "Offres publiées",
                "headers": ["Role", "Company", "Type", "Status"],
                "rows": [
                    ("Frontend developer", "TechAccess", "CDI", '<span class="badge bg-label-primary me-1">Live</span>'),
                    ("HR assistant", "Solidarity Pro", "CDD", '<span class="badge bg-label-primary me-1">Live</span>'),
                ],
            },
            {
                "title": "Brouillons",
                "headers": ["Role", "Company", "Updated", "Status"],
                "rows": [
                    ("Data analyst", "Nova Labs", "Apr 08", '<span class="badge bg-label-warning me-1">Draft</span>'),
                ],
            },
            {
                "title": "Archives",
                "headers": ["Role", "Company", "Closed", "Status"],
                "rows": [
                    ("Support agent", "Call Inclusif", "Mar 15", '<span class="badge bg-label-secondary me-1">Closed</span>'),
                ],
            },
        ],
    },
    "entretiens": {
        "heading": "Gestion — Entretiens",
        "intro": "À venir, terminés et comptes rendus.",
        "tables": [
            {
                "title": "À venir",
                "headers": ["Candidate", "Role", "Date", "Mode", "Status"],
                "rows": [
                    ("Sophie Martin", "Frontend dev", "Apr 15 14:00", "Video (captions)", '<span class="badge bg-label-primary me-1">Scheduled</span>'),
                    ("Alex Rivera", "HR assistant", "Apr 18 10:00", "On-site PMR", '<span class="badge bg-label-warning me-1">TBC</span>'),
                ],
            },
            {
                "title": "Terminés",
                "headers": ["Candidate", "Role", "Date", "Outcome", "Status"],
                "rows": [
                    ("Jamal T.", "Support", "Mar 22", "Positive", '<span class="badge bg-label-success me-1">Done</span>'),
                ],
            },
            {
                "title": "Comptes rendus",
                "headers": ["Interview", "Author", "Sent", "Status"],
                "rows": [
                    ("INT-104", "Recruiter A", "Apr 02", '<span class="badge bg-label-info me-1">Shared</span>'),
                ],
            },
        ],
    },
    "reclamations": {
        "heading": "Gestion — Réclamations",
        "intro": "Ouvertes, en traitement et clôturées.",
        "tables": [
            {
                "title": "Ouvertes",
                "headers": ["Ref", "Subject", "From", "Priority", "Status"],
                "rows": [
                    ("R-104", "Form contrast", "Candidate", "High", '<span class="badge bg-label-danger me-1">Open</span>'),
                ],
            },
            {
                "title": "En traitement",
                "headers": ["Ref", "Owner", "Updated", "Status"],
                "rows": [
                    ("R-101", "Support team", "Apr 09", '<span class="badge bg-label-warning me-1">In progress</span>'),
                ],
            },
            {
                "title": "Clôturées",
                "headers": ["Ref", "Subject", "Closed", "Status"],
                "rows": [
                    ("R-098", "Broken link", "Mar 28", '<span class="badge bg-label-success me-1">Resolved</span>'),
                ],
            },
        ],
    },
}


def replace_main_content(html, inner):
    start = '<div class="container-xxl flex-grow-1 container-p-y">'
    end = "\n            <!-- / Content -->"
    i0 = html.find(start)
    if i0 == -1:
        raise SystemExit("container start not found")
    i1 = html.find(end, i0)
    if i1 == -1:
        raise SystemExit("content end not found")
    i0b = i0 + len(start)
    return html[:i0b] + "\n" + inner + html[i1:]


def remove_chart_scripts(html):
    html = html.replace(
        '\n    <!-- Vendors JS -->\n    <script src="../assets/vendor/libs/apex-charts/apexcharts.js"></script>\n\n    <!-- Main JS -->',
        "\n    <!-- Main JS -->",
        1,
    )
    html = html.replace('\n    <!-- Page JS -->\n    <script src="../assets/js/dashboards-analytics.js"></script>', "", 1)
    return html


def set_title(html, title):
    return re.sub(r"<title>.*?</title>", f"<title>{title}</title>", html, count=1, flags=re.DOTALL)


def patch_common(html, gestion_active):
    html = apply_gestion_menu(html, gestion_active)
    html = add_notifications(html)
    html = fix_settings_link(html)
    return html


def main():
    raw = INDEX.read_text(encoding="utf-8")
    idx = patch_common(raw, None)
    INDEX.write_text(idx, encoding="utf-8")
    print("Updated index.html")

    template = INDEX.read_text(encoding="utf-8")
    for key, spec in PAGES.items():
        h = template
        h = strip_dashboard_active(h)
        h = apply_gestion_menu(h, key)
        h = replace_main_content(h, page_content(spec))
        h = remove_chart_scripts(h)
        h = set_title(h, f"Gestion — {key.capitalize()} | Sneat")
        (ROOT / "html" / f"gestion-{key}.html").write_text(h, encoding="utf-8")
        print("Wrote gestion-%s.html" % key)


if __name__ == "__main__":
    main()
