<?php
$pageTitle = 'Order History';
$pageScript = '/icoms/public/js/order_history.js';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h3 class="mb-0">Order History</h3>
    <p class="text-muted small mb-0">Search through past orders.</p>
  </div>
</div>

<div class="card mb-3">
  <div class="card-body">
    <div class="row g-2 align-items-end">
      <div class="col-md-2">
        <label class="form-label small mb-1">From</label>
        <input type="date" id="fFrom" class="form-control form-control-sm">
      </div>
      <div class="col-md-2">
        <label class="form-label small mb-1">To</label>
        <input type="date" id="fTo" class="form-control form-control-sm">
      </div>
      <div class="col-md-3">
        <label class="form-label small mb-1">Order No</label>
        <input type="text" id="fOrderNo" class="form-control form-control-sm" placeholder="e.g. ORD-20250101">
      </div>
      <div class="col-md-3">
        <label class="form-label small mb-1">Cashier</label>
        <select id="fCashier" class="form-select form-select-sm">
          <option value="">All cashiers</option>
        </select>
      </div>
      <div class="col-md-2 d-flex gap-1">
        <button class="btn btn-sm btn-dark flex-grow-1" id="btnApply">
          <i class="bi bi-search"></i>
        </button>
        <button class="btn btn-sm btn-outline-secondary" id="btnReset">
          <i class="bi bi-arrow-counterclockwise"></i>
        </button>
      </div>
    </div>
  </div>
</div>

<div class="row g-3 mb-3" id="summaryRow" style="display:none;">
  <div class="col-md-4">
    <div class="card stat-card p-3">
      <div class="stat-icon bg-primary-subtle text-primary"><i class="bi bi-receipt"></i></div>
      <div>
        <div class="text-muted small">Total Orders</div>
        <div class="fs-5 fw-bold" id="sumOrders">0</div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card stat-card p-3">
      <div class="stat-icon bg-success-subtle text-success"><i class="bi bi-cash-stack"></i></div>
      <div>
        <div class="text-muted small">Total Revenue</div>
        <div class="fs-5 fw-bold" id="sumRevenue">₱0.00</div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card stat-card p-3">
      <div class="stat-icon bg-info-subtle text-info"><i class="bi bi-graph-up-arrow"></i></div>
      <div>
        <div class="text-muted small">Average</div>
        <div class="fs-5 fw-bold" id="sumAvg">₱0.00</div>
      </div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0" id="tblOrders">
        <thead>
          <tr>
            <th style="width:32px"></th>
            <th>Order No</th>
            <th>Date / Time</th>
            <th>Cashier</th>
            <th class="text-center">Items</th>
            <th class="text-end">Total</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>