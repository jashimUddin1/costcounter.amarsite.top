

//js/dashboard_charts.js
function en2bnNumber(number) {
  const en = ['0','1','2','3','4','5','6','7','8','9'];
  const bn = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];
  return number.toString().replace(/[0-9]/g, d => bn[d]);
}


// Axis Chart (Main)
(function () {
  const el = document.getElementById('axisChart');
  if (!el) return;
  new Chart(el.getContext('2d'), {
    type: 'bar',
    data: {
      labels: axisLabels,
      datasets: [{
        label: axisLabelTitle,
        data: axisData,
        borderWidth: 1,
        backgroundColor: 'rgba(54,162,235,0.5)',
        borderColor: 'rgba(54,162,235,1)'
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: { 
        y: { 
          beginAtZero: true,
          suggestedMax: Math.max(...axisData) * 1.2,
          ticks: {
            callback: function(value) {
              return en2bnNumber(value.toString());
            }
          }
        },
        x: {
          ticks: {
            callback: function(value, index, ticks) {
              return en2bnNumber(this.getLabelForValue(value));
            }
          }
        }
      },
      plugins: {
        tooltip: {
          callbacks: {
            label: function(context) {
              let value = context.parsed.y || 0;
              if (value === 0) return null;

              let mode = axisLabelTitle;
              let label = context.label;
              let valBn = en2bnNumber(value.toLocaleString());

              if (mode === 'প্রতিবছরের খরচ') {
                return `${label} সালের খরচ ${valBn} টাকা`;
              } 
              else if (mode === 'প্রতিমাসের খরচ') {
                return `${label} মাসের খরচ ${valBn} টাকা`;
              } 
              else {
                return `${label} তারিখের খরচ ${valBn} টাকা`;
              }
            }
          }
        },
        datalabels: {
          anchor: 'end',
          align: 'end',
          rotation: -50,
          formatter: function(value) {
            if (value === 0) return null;
            return en2bnNumber(value.toLocaleString());
          },
          color: '#000',
          font: { 
            weight: '400',
            style: 'italic'
          }
        }
      }
    },
    plugins: [ChartDataLabels]
  });
})();


// Axis Chart (Full)
(function () {
  const el = document.getElementById('axisChartFull');
  if (!el) return;
  new Chart(el.getContext('2d'), {
    type: 'bar',
    data: {
      labels: axisLabels,
      datasets: [{
        label: axisLabelTitle,
        data: axisData,
        borderWidth: 1,
        backgroundColor: 'rgba(54,162,235,0.5)',
        borderColor: 'rgba(54,162,235,1)'
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: { 
        y: { 
          beginAtZero: true,
          ticks: {
            callback: function(value) {
              return en2bnNumber(value.toString());
            }
          }
        },
        x: {
          ticks: {
            callback: function(value, index, ticks) {
              return en2bnNumber(this.getLabelForValue(value));
            }
          }
        }
      },
      plugins: {
        tooltip: {
          callbacks: {
            label: function(context) {
              let value = context.parsed.y || 0;
              if (value === 0) return null;

              let mode = axisLabelTitle;
              let label = context.label;
              let valBn = en2bnNumber(value.toLocaleString());

              if (mode === 'প্রতিবছরের খরচ') {
                return `${label} সালের খরচ ${valBn} টাকা`;
              } 
              else if (mode === 'প্রতিমাসের খরচ') {
                return `${label} মাসের খরচ ${valBn} টাকা`;
              } 
              else {
                return `${label} তারিখের খরচ ${valBn} টাকা`;
              }
            }
          }
        },
        datalabels: {
          anchor: 'end',
          align: 'end',
          rotation: -50,
          formatter: function(value) {
            if (value === 0) return null;
            return en2bnNumber(value.toLocaleString());
          },
          color: '#000',
          font: { 
            weight: '400', 
            style: 'italic' 
          }
        }
      }
    },
    plugins: [ChartDataLabels]
  });
})();


(function () { // Category Chart (Main)
  const el = document.getElementById('categoryChart');
  if (!el) return;
  const ctx = el.getContext('2d');

  new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: categoryLabels,
      datasets: [{
        label: 'টাকা',
        data: categoryData,
        borderWidth: 1,
        backgroundColor: [
          '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0',
          '#9966FF', '#FF9F40', '#66FF66', '#FF6666'
        ]
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'left',
          align: 'start',
          labels: {
            generateLabels: function (chart) {
              const dataset = chart.data.datasets[0];
              const total = dataset.data.reduce((a, b) => a + b, 0);
              return chart.data.labels.map((label, i) => {
                const value = dataset.data[i];
                const percentage = total ? ((value / total) * 100).toFixed(1) : 0;
                return {
                  text: `${label}  ${percentage}%`,
                  fillStyle: dataset.backgroundColor[i],
                  index: i
                };
              });
            }
          }
        }
      }
    }
  });
})();

// Category Chart (Full)
(function () {
  const el = document.getElementById('categoryChartFull');
  if (!el) return;
  const ctx = el.getContext('2d');

  new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: categoryLabels,
      datasets: [{
        label: 'টাকা',
        data: categoryData,
        borderWidth: 1,
        backgroundColor: [
          '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0',
          '#9966FF', '#FF9F40', '#66FF66', '#FF6666'
        ]
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'left',
          align: 'start',
          labels: {
            generateLabels: function (chart) {
              const dataset = chart.data.datasets[0];
              const total = dataset.data.reduce((a, b) => a + b, 0);
              return chart.data.labels.map((label, i) => {
                const value = dataset.data[i];
                const percentage = total ? ((value / total) * 100).toFixed(1) : 0;
                return {
                  text: `${label} - ${percentage}% (${value.toLocaleString()} টাকা)`,
                  fillStyle: dataset.backgroundColor[i],
                  index: i
                };
              });
            }
          }
        }
      }
    }
  });
})();
