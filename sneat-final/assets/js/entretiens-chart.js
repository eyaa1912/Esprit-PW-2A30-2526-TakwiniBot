document.addEventListener('DOMContentLoaded', function () {
  var el = document.getElementById('chart-postes');
  if (!el || typeof ApexCharts === 'undefined') {
    return;
  }

  var raw = [];
  try {
    raw = JSON.parse(el.dataset.postes || '[]');
  } catch (error) {
    raw = [];
  }

  if (!raw.length) {
    return;
  }

  var categories = raw.map(function (item) {
    return item.poste_cible;
  });

  var values = raw.map(function (item) {
    return Number(item.total || 0);
  });

  var options = {
    chart: {
      type: 'bar',
      height: 280,
      toolbar: { show: false }
    },
    series: [{
      name: 'Entretiens',
      data: values
    }],
    plotOptions: {
      bar: {
        borderRadius: 4,
        columnWidth: '40%'
      }
    },
    xaxis: {
      categories: categories,
      labels: { rotate: -25 }
    },
    dataLabels: { enabled: false },
    stroke: { width: 0 },
    colors: ['#696cff'],
    grid: {
      borderColor: '#eceef1'
    },
    yaxis: {
      title: { text: 'Nombre' }
    }
  };

  new ApexCharts(el, options).render();
});
