$(function () {
  const API = '/icoms/api/reports.php';

  let charts = {};
  let lastReport = null;

  initDateRange(7);
  load();
  loadRecentOrders();

  function initDateRange(days) {
    const to = new Date();
    const from = new Date();
    from.setDate(to.getDate() - (days - 1));

    $('#toDate').val(fmt(to));
    $('#fromDate').val(fmt(from));
  }

  $('.preset').on('click', function () {
    $('.preset').removeClass('active');
    $(this).addClass('active');

    const range = $(this).data('range');
    const days = { today: 1, '7d': 7, '30d': 30, '90d': 90 }[range] || 7;
    initDateRange(days);
    load();
  });

  $('#btnApply').on('click', () => {
    $('.preset').removeClass('active');
    load();
  });

  function fmt(d) {
    const y = d.getFullYear();
    const m = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${y}-${m}-${day}`;
  }

  function load() {
    const params = {
      action: 'summary',
      from: $('#fromDate').val(),
      to: $('#toDate').val()
    };

    $.get(API, params).done(res => {
      if (!res.success) return toast(res.message, 'danger');
      render(res.data);
    }).fail(() => toast('Failed to load report data.', 'danger'));
  }

  function render(d) {
    lastReport = d;

    $('#statRevenue').text('₱' + (+d.sales.revenue).toFixed(2));
    $('#statOrders').text(d.sales.orders);
    $('#statAvg').text('₱' + (+d.sales.avg).toFixed(2));
    $('#statWaste').text(d.waste.entries);

    Object.values(charts).forEach(c => c.destroy && c.destroy());
    charts = {};

    charts.revenue = new Chart('revenueChart', {
      type: 'line',
      data: {
        labels: d.daily.map(x => x.d.slice(5)),
        datasets: [
          {
            label: 'Revenue (₱)',
            data: d.daily.map(x => x.revenue),
            borderColor: '#198754',
            backgroundColor: 'rgba(25,135,84,.15)',
            fill: true,
            tension: .3,
            yAxisID: 'y'
          },
          {
            label: 'Orders',
            data: d.daily.map(x => x.orders),
            borderColor: '#0d6efd',
            borderDash: [4, 4],
            fill: false,
            tension: .3,
            yAxisID: 'y1'
          }
        ]
      },
      options: {
        interaction: { mode: 'index', intersect: false },
        plugins: { legend: { position: 'top' } },
        scales: {
          y: { beginAtZero: true, position: 'left', title: { display: true, text: '₱' } },
          y1: {
            beginAtZero: true, position: 'right', grid: { drawOnChartArea: false },
            title: { display: true, text: 'Orders' }
          }
        }
      }
    });

    const catColors = ['#0d6efd', '#198754', '#ffc107', '#dc3545', '#6f42c1', '#20c997', '#fd7e14', '#0dcaf0'];
    charts.category = new Chart('categoryChart', {
      type: 'doughnut',
      data: {
        labels: d.categories.map(c => c.category),
        datasets: [{
          data: d.categories.map(c => +c.revenue),
          backgroundColor: d.categories.map((_, i) => catColors[i % catColors.length])
        }]
      },
      options: {
        plugins: {
          legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } },
          tooltip: {
            callbacks: {
              label: ctx => `${ctx.label}: ₱${(+ctx.raw).toFixed(2)}`
            }
          }
        }
      }
    });

    charts.top = new Chart('topItemsChart', {
      type: 'bar',
      data: {
        labels: d.top.map(t => t.name),
        datasets: [
          {
            label: 'Units Sold', data: d.top.map(t => +t.qty),
            backgroundColor: '#0d6efd', yAxisID: 'y'
          }
        ]
      },
      options: {
        indexAxis: 'y',
        plugins: { legend: { display: false } },
        scales: { x: { beginAtZero: true } }
      }
    });

    const reasonLabels = {
      spill: 'Spill', spoilage: 'Spoilage',
      prep_error: 'Prep Error', other: 'Other'
    };
    const reasonColors = {
      spill: '#0d6efd', spoilage: '#ffc107',
      prep_error: '#dc3545', other: '#6c757d'
    };
    charts.wasteReason = new Chart('wasteReasonChart', {
      type: 'doughnut',
      data: {
        labels: d.waste_by_reason.map(w => reasonLabels[w.reason] || w.reason),
        datasets: [{
          data: d.waste_by_reason.map(w => +w.cnt),
          backgroundColor: d.waste_by_reason.map(w => reasonColors[w.reason] || '#adb5bd')
        }]
      },
      options: {
        plugins: {
          legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } }
        }
      }
    });

    charts.topWasted = new Chart('topWastedChart', {
      type: 'bar',
      data: {
        labels: d.top_wasted.map(w => w.name),
        datasets: [{
          label: 'Qty Wasted',
          data: d.top_wasted.map(w => +w.qty),
          backgroundColor: '#dc3545'
        }]
      },
      options: {
        indexAxis: 'y',
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: {
              label: ctx => {
                const unit = d.top_wasted[ctx.dataIndex]?.unit || '';
                return `${ctx.raw} ${unit}`;
              }
            }
          }
        },
        scales: { x: { beginAtZero: true } }
      }
    });
  }

  function loadRecentOrders() {
    $.get(API, { action: 'recent_orders' }).done(res => {
      if (!res.success) return;
      const rows = res.data;
      if (!rows.length) {
        $('#recentOrdersBody').html(
          '<tr><td colspan="5" class="text-center text-muted py-4">No orders yet.</td></tr>'
        );
        return;
      }
      $('#recentOrdersBody').html(rows.map(o => `
        <tr>
          <td><code>${escapeHtml(o.order_no)}</code></td>
          <td class="text-muted small">${o.created_at}</td>
          <td class="small">${escapeHtml(o.cashier_name)}</td>
          <td class="text-center">${o.item_lines}</td>
          <td class="text-end fw-semibold">₱${(+o.total).toFixed(2)}</td>
        </tr>
      `).join(''));
    });
  }

  function escapeHtml(s) {
    return $('<div>').text(s ?? '').html();
  }

  function toCSV(rows) {
    return rows.map(row =>
      row.map(cell => {
        const s = String(cell ?? '');
        if (/[",\n]/.test(s)) return '"' + s.replace(/"/g, '""') + '"';
        return s;
      }).join(',')
    ).join('\n');
  }

  function downloadCSV(filename, rows) {
    const csv = '\uFEFF' + toCSV(rows);
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
    toast('CSV downloaded: ' + filename, 'success');
  }

  function rangeSlug() {
    const from = $('#fromDate').val() || 'from';
    const to = $('#toDate').val() || 'to';
    return from + '_to_' + to;
  }

  $('#btnExportSales').on('click', function (e) {
    e.preventDefault();
    if (!lastReport) return toast('No data loaded yet.', 'warning');

    const rows = [['Date', 'Orders', 'Revenue']];
    lastReport.daily.forEach(d => {
      rows.push([d.d, d.orders, (+d.revenue).toFixed(2)]);
    });
    rows.push([]);
    rows.push(['TOTAL', lastReport.sales.orders, (+lastReport.sales.revenue).toFixed(2)]);
    rows.push(['AVERAGE / ORDER', '', (+lastReport.sales.avg).toFixed(2)]);

    downloadCSV('icoms_sales_' + rangeSlug() + '.csv', rows);
  });

  $('#btnExportItems').on('click', function (e) {
    e.preventDefault();
    if (!lastReport) return toast('No data loaded yet.', 'warning');

    const rows = [['Menu Item', 'Units Sold', 'Revenue']];
    lastReport.top.forEach(t => {
      rows.push([t.name, t.qty, (+t.revenue).toFixed(2)]);
    });
    rows.push([]);
    rows.push(['--- REVENUE BY CATEGORY ---']);
    rows.push(['Category', '', 'Revenue']);
    lastReport.categories.forEach(c => {
      rows.push([c.category, '', (+c.revenue).toFixed(2)]);
    });

    downloadCSV('icoms_top_items_' + rangeSlug() + '.csv', rows);
  });

  $('#btnExportWaste').on('click', function (e) {
    e.preventDefault();
    if (!lastReport) return toast('No data loaded yet.', 'warning');

    const reasonLabels = {
      spill: 'Spill', spoilage: 'Spoilage',
      prep_error: 'Prep Error', other: 'Other'
    };

    const rows = [['Waste by Reason']];
    rows.push(['Reason', 'Entries', 'Total Quantity']);
    lastReport.waste_by_reason.forEach(w => {
      rows.push([reasonLabels[w.reason] || w.reason, w.cnt, (+w.total_qty).toFixed(2)]);
    });
    rows.push([]);
    rows.push(['--- TOP WASTED INGREDIENTS ---']);
    rows.push(['Ingredient', 'Unit', 'Quantity Wasted']);
    lastReport.top_wasted.forEach(w => {
      rows.push([w.name, w.unit, (+w.qty).toFixed(2)]);
    });

    downloadCSV('icoms_waste_' + rangeSlug() + '.csv', rows);
  });

  $('#btnPrintPdf').on('click', function (e) {
    e.preventDefault();
    if (!lastReport) return toast('No data loaded yet.', 'warning');

    $('#printRange').text('Range: ' + lastReport.range.from + ' → ' + lastReport.range.to);
    $('#printGenerated').text(new Date().toLocaleString());

    setTimeout(() => window.print(), 100);
  });

});