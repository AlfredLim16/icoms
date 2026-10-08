<?php
$pageTitle = 'Reports';
$pageScript = '/icoms/public/js/reports.js';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
  <div>
    <h3 class="mb-0">Reports &amp; Analytics</h3>
    <p class="text-muted small mb-0">Business insights for your cafe.</p>
  </div>

  <div class="d-flex gap-2 align-items-center flex-wrap">
    <div class="btn-group btn-group-sm" role="group">
      <button class="btn btn-outline-secondary preset" data-range="today">Today</button>
      <button class="btn btn-outline-secondary preset active" data-range="7d">7 days</button>
      <button class="btn btn-outline-secondary preset" data-range="30d">30 days</button>
      <button class="btn btn-outline-secondary preset" data-range="90d">90 days</button>
    </div>
    <input type="date" id="fromDate" class="form-control form-control-sm" style="max-width:150px">
    <input type="date" id="toDate" class="form-control form-control-sm" style="max-width:150px">
    <button class="btn btn-sm btn-dark" id="btnApply"><i class="bi bi-arrow-repeat"></i></button>

    <div class="dropdown">
      <button class="btn btn-sm btn-success dropdown-toggle" data-bs-toggle="dropdown">
        <i class="bi bi-download me-1"></i> Export
      </button>
      <ul class="dropdown-menu dropdown-menu-end">
        <li><a class="dropdown-item" href="#" id="btnExportSales">
            <i class="bi bi-cash-stack me-1"></i> Sales CSV</a></li>
        <li><a class="dropdown-item" href="#" id="btnExportItems">
            <i class="bi bi-trophy me-1"></i> Top Items CSV</a></li>
        <li><a class="dropdown-item" href="#" id="btnExportWaste">
            <i class="bi bi-trash me-1"></i> Waste CSV</a></li>
        <li>
          <hr class="dropdown-divider">
        </li>
        <li><a class="dropdown-item" href="#" id="btnPrintPdf">
            <i class="bi bi-printer me-1"></i> Print / Save as PDF</a></li>
      </ul>
    </div>
  </div>
</div>

<div id="reportPrintArea">

  <div id="printHeader" class="d-none d-print-block mb-4">
    <h2 class="mb-0">ICOMS — Business Report</h2>
    <p class="text-muted mb-0" id="printRange">Range: —</p>
    <p class="text-muted small">Generated: <span id="printGenerated">—</span></p>
    <hr>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card stat-card p-3">
        <div class="stat-icon bg-success-subtle text-success"><i class="bi bi-cash-stack"></i></div>
        <div>
          <div class="text-muted small">Revenue</div>
          <div class="fs-4 fw-bold" id="statRevenue">₱0.00</div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card stat-card p-3">
        <div class="stat-icon bg-primary-subtle text-primary"><i class="bi bi-receipt"></i></div>
        <div>
          <div class="text-muted small">Orders</div>
          <div class="fs-4 fw-bold" id="statOrders">0</div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card stat-card p-3">
        <div class="stat-icon bg-info-subtle text-info"><i class="bi bi-graph-up-arrow"></i></div>
        <div>
          <div class="text-muted small">Avg Order Value</div>
          <div class="fs-4 fw-bold" id="statAvg">₱0.00</div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card stat-card p-3">
        <div class="stat-icon bg-danger-subtle text-danger"><i class="bi bi-trash"></i></div>
        <div>
          <div class="text-muted small">Waste Entries</div>
          <div class="fs-4 fw-bold" id="statWaste">0</div>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-lg-8">
      <div class="card h-100">
        <div class="card-body">
          <h6 class="mb-3"><i class="bi bi-graph-up me-1"></i> Daily Revenue</h6>
          <canvas id="revenueChart" height="80"></canvas>
        </div>
      </div>
    </div>
    <div class="col-lg-4">
      <div class="card h-100">
        <div class="card-body">
          <h6 class="mb-3"><i class="bi bi-pie-chart me-1"></i> Revenue by Category</h6>
          <canvas id="categoryChart" height="200"></canvas>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-lg-6">
      <div class="card h-100">
        <div class="card-body">
          <h6 class="mb-3"><i class="bi bi-trophy me-1"></i> Top Selling Items</h6>
          <canvas id="topItemsChart" height="160"></canvas>
        </div>
      </div>
    </div>
    <div class="col-lg-3">
      <div class="card h-100">
        <div class="card-body">
          <h6 class="mb-3"><i class="bi bi-exclamation-triangle me-1"></i> Waste by Reason</h6>
          <canvas id="wasteReasonChart" height="180"></canvas>
        </div>
      </div>
    </div>
    <div class="col-lg-3">
      <div class="card h-100">
        <div class="card-body">
          <h6 class="mb-3"><i class="bi bi-droplet me-1"></i> Top Wasted</h6>
          <canvas id="topWastedChart" height="180"></canvas>
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      <h6 class="mb-3"><i class="bi bi-clock-history me-1"></i> Recent Orders</h6>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead>
            <tr>
              <th>Order No</th>
              <th>Date</th>
              <th>Cashier</th>
              <th class="text-center">Items</th>
              <th class="text-end">Total</th>
            </tr>
          </thead>
          <tbody id="recentOrdersBody">
            <tr>
              <td colspan="5" class="text-center text-muted py-4">Loading…</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>