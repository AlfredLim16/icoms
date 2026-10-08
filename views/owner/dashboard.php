<?php
$pageTitle = 'Dashboard';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h3 class="mb-0">Welcome back, <?= htmlspecialchars(explode(' ', $user['name'])[0]) ?></h3>
    <p class="text-muted mb-0">Here's what's happening in your cafe today.</p>
  </div>
  <div class="text-muted small"><?= date('l, F j, Y') ?></div>
</div>

<div class="row g-3 mb-3 dashboard-top">
  <div class="col-lg-8 d-flex flex-column">
    <div class="row g-3 mb-3">

      <div class="col-6">
        <div class="card stat-card stat-primary">
          <div class="stat-left">
            <div class="stat-icon"><i class="bi bi-box-seam"></i></div>
            <div class="stat-label">Total Ingredients</div>
          </div>
          <div class="stat-value" id="statTotal">—</div>
        </div>
      </div>
      <div class="col-6">
        <div class="card stat-card stat-warning">
          <div class="stat-left">
            <div class="stat-icon"><i class="bi bi-exclamation-triangle"></i></div>
            <div class="stat-label">Low Stock</div>
          </div>
          <div class="stat-value" id="statLow">—</div>
        </div>
      </div>
      <div class="col-6">
        <div class="card stat-card stat-danger">
          <div class="stat-left">
            <div class="stat-icon"><i class="bi bi-calendar-x"></i></div>
            <div class="stat-label">Expiring Soon</div>
          </div>
          <div class="stat-value" id="statExp">—</div>
        </div>
      </div>
      <div class="col-6">
        <div class="card stat-card stat-success">
          <div class="stat-left">
            <div class="stat-icon"><i class="bi bi-cash-stack"></i></div>
            <div class="stat-label">Today's Sales</div>
          </div>
          <div class="stat-value" id="statSales">₱0.00</div>
        </div>
      </div>
    </div>

    <div class="card flex-grow-1 d-flex flex-column">
      <div class="card-body d-flex flex-column">
        <h6 class="card-title mb-3"><i class="bi bi-pie-chart me-1"></i> Inventory Health</h6>
        <div class="chart-wrap flex-grow-1">
          <canvas id="healthChart"></canvas>
        </div>
      </div>
    </div>

  </div>

  <div class="col-lg-4 d-flex">
    <div class="card alerts-card w-100 d-flex flex-column">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="bi bi-bell me-1"></i> Alerts &amp; Reminders</h6>
        <span class="badge bg-danger-subtle text-danger" id="alertCountBadge" style="display:none;">0</span>
      </div>
      <div class="alerts-scroll flex-grow-1" id="alertsBox">
        <div class="text-muted small p-3">Loading…</div>
      </div>
      <div class="card-footer text-end" id="alertsFooter" style="display:none;">
        <a href="#" class="small text-decoration-none" id="btnViewAllAlerts">
          View all <span id="alertTotalCount">0</span> alerts
          <i class="bi bi-arrow-right"></i>
        </a>
      </div>
    </div>
  </div>

</div>

<div class="row g-3">
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <h6 class="card-title mb-3"><i class="bi bi-graph-up me-1"></i> Sales — Last 7 Days</h6>
        <div class="chart-wrap" style="height: 280px;">
          <canvas id="salesChart"></canvas>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="alertsModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-bell me-1"></i> All Alerts</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="alertsModalBody"></div>
    </div>
  </div>
</div>

<script>
  $(function() {
    let allAlerts = [];
    const alertsModal = new bootstrap.Modal('#alertsModal');

    $.get('/icoms/api/alerts.php').done(res => {
      if (!res.success) return;
      const {
        alerts,
        counts
      } = res.data;
      allAlerts = alerts;

      $('#statTotal').text(counts.total);
      $('#statLow').text(counts.low_stock);
      $('#statExp').text(counts.expiring);

      const healthy = Math.max(0, counts.total - counts.low_stock - counts.expiring);
      new Chart('healthChart', {
        type: 'doughnut',
        data: {
          labels: ['Healthy', 'Low Stock', 'Expiring'],
          datasets: [{
            data: [healthy, counts.low_stock, counts.expiring],
            backgroundColor: ['#198754', '#ffc107', '#dc3545'],
            borderWidth: 2,
            borderColor: getComputedStyle(document.body).backgroundColor
          }]
        },
        options: {
          maintainAspectRatio: false,
          cutout: '65%',
          plugins: {
            legend: {
              position: 'bottom',
              labels: {
                boxWidth: 12,
                font: {
                  size: 11
                }
              }
            }
          }
        }
      });

      renderAlerts(alerts);

      const total = alerts.length;
      if (total > 0) {
        $('#alertCountBadge').text(total).show();
      } else {
        $('#alertCountBadge').hide();
      }
    }).fail(() => {
      $('#alertsBox').html('<div class="text-muted small p-3">Unable to load alerts.</div>');
    });

    function renderAlerts(alerts) {
      if (!alerts.length) {
        $('#alertsBox').html(`
        <div class="text-center py-5">
          <i class="bi bi-check-circle text-success" style="font-size:2.5rem;"></i>
          <p class="text-muted small mb-0 mt-2">All good! No alerts right now.</p>
        </div>`);
        $('#alertsFooter').hide();
        return;
      }

      const visible = alerts.slice(0, 6);
      $('#alertsBox').html(visible.map(renderAlertItem).join(''));

      if (alerts.length > 6) {
        $('#alertTotalCount').text(alerts.length);
        $('#alertsFooter').show();
      } else {
        $('#alertsFooter').hide();
      }
    }

    function renderAlertItem(a) {
      const isExp = a.expiration_date && new Date(a.expiration_date) - new Date() < 7 * 864e5;
      const cls = isExp ? 'danger' : 'warning';
      const icon = isExp ? 'bi-calendar-x' : 'bi-exclamation-triangle-fill';
      const reason = isExp ?
        `Expires ${a.expiration_date}` :
        `Stock: ${a.stock_qty} ${a.unit} <span class="text-muted">(reorder at ${a.reorder_point})</span>`;

      return `<div class="alert-item alert-${cls}">
      <div class="d-flex align-items-start gap-2">
        <i class="bi ${icon} flex-shrink-0 mt-1"></i>
        <div class="flex-grow-1">
          <div class="fw-semibold">${escapeHtml(a.name)}</div>
          <div class="small">${reason}</div>
        </div>
      </div>
    </div>`;
    }

    $('#btnViewAllAlerts').on('click', function(e) {
      e.preventDefault();
      $('#alertsModalBody').html(allAlerts.map(renderAlertItem).join(''));
      alertsModal.show();
    });

    const to = new Date();
    const from = new Date();
    from.setDate(to.getDate() - 6);

    $.get('/icoms/api/reports.php', {
      action: 'summary',
      from: dateStr(from),
      to: dateStr(to)
    }).done(res => {
      if (!res.success) return;

      $('#statSales').text('₱' + (+res.data.sales.revenue).toFixed(2));

      new Chart('salesChart', {
        type: 'line',
        data: {
          labels: res.data.daily.map(x => x.d.slice(5)),
          datasets: [{
            label: 'Revenue (₱)',
            data: res.data.daily.map(x => x.revenue),
            borderColor: '#f0a500',
            backgroundColor: 'rgba(240, 165, 0, .15)',
            fill: true,
            tension: .3,
            pointBackgroundColor: '#f0a500',
            pointRadius: 4
          }]
        },
        options: {
          maintainAspectRatio: false,
          plugins: {
            legend: {
              display: false
            }
          },
          scales: {
            y: {
              beginAtZero: true
            }
          }
        }
      });
    });

    function dateStr(d) {
      const y = d.getFullYear();
      const m = String(d.getMonth() + 1).padStart(2, '0');
      const day = String(d.getDate()).padStart(2, '0');
      return `${y}-${m}-${day}`;
    }

    function escapeHtml(s) {
      return $('<div>').text(s ?? '').html();
    }
  });
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>