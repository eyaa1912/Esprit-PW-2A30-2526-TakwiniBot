/**
 * Démonstration : recherche simple sur les tableaux de gestion
 */
(function () {
  var input = document.getElementById('platform-table-search');
  var table = document.querySelector('[data-platform-table]');
  if (!input || !table) return;

  input.addEventListener('input', function () {
    var q = input.value.trim().toLowerCase();
    var rows = table.querySelectorAll('tbody tr');
    rows.forEach(function (row) {
      var text = row.textContent.toLowerCase();
      row.style.display = !q || text.indexOf(q) !== -1 ? '' : 'none';
    });
  });
})();
