<?php
$pageTitle = 'Dashboard';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
?>

<h3 class="mb-3">Hi <?= htmlspecialchars(explode(' ', $user['name'])[0]) ?> 👋</h3>
<p class="text-muted">Quick access to your daily tasks.</p>

<div class="row g-3">
  <div class="col-md-6">
    <a href="/icoms/views/staff/ordering.php" class="text-decoration-none">
      <div class="card stat-card p-4">
        <div class="stat-icon bg-primary-subtle text-primary"><i class="bi bi-cart-check"></i></div>
        <div>
          <div class="fs-5 fw-bold text-dark">Open Ordering</div>
        </div>
      </div>
    </a>
  </div>
  <div class="col-md-6">
    <a href="/icoms/views/staff/waste.php" class="text-decoration-none">
      <div class="card stat-card p-4">
        <div class="stat-icon bg-danger-subtle text-danger"><i class="bi bi-trash"></i></div>
        <div>
          <div class="fs-5 fw-bold text-dark">Record Waste</div>
          <div class="text-muted small">Log spills / spoilage</div>
        </div>
      </div>
    </a>
  </div>
</div>

<div class="card mt-4">
  <div class="card-body">
    <h6 class="mb-3"><i class="bi bi-bell me-1"></i> Alerts</h6>
    <div id="alertsBox">
      <div class="text-muted small">Loading…</div>
    </div>
  </div>
</div>

<script>
  $.get('/icoms/api/alerts.php').done(res => {
    if (!res.success) return;
    const alerts = res.data.alerts;
    if (!alerts.length) {
      $('#alertsBox').html('<div class="text-success small"><i class="bi bi-check-circle me-1"></i> All good!</div>');
      return;
    }
    $('#alertsBox').html(alerts.map(a => {
      const isExp = a.expiration_date && new Date(a.expiration_date) - new Date() < 7 * 864e5;
      const cls = isExp ? 'danger' : 'warning';
      const reason = isExp ? `Expires ${a.expiration_date}` : `Low: ${a.stock_qty} ${a.unit}`;
      return `<div class="alert alert-${cls} py-2 mb-2 small"><strong>${a.name}</strong> — ${reason}</div>`;
    }).join(''));
  });
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>