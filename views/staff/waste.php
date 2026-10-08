<?php
$pageTitle = 'Record Waste';
$pageScript = '/icoms/public/js/waste.js';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h3 class="mb-0">Record Waste</h3>
    <p class="text-muted small mb-0">Log spills, spoilage, or preparation errors. Stocks will adjust automatically.</p>
  </div>
  <button class="btn btn-danger" id="btnNewWaste">
    <i class="bi bi-plus-lg"></i> Record Waste
  </button>
</div>

<div class="row g-3 mb-4" id="summaryRow">
  <div class="col-md-3">
    <div class="card stat-card p-3">
      <div class="stat-icon bg-danger-subtle text-danger"><i class="bi bi-trash"></i></div>
      <div>
        <div class="text-muted small">Today's Entries</div>
        <div class="fs-4 fw-bold" id="sumCount">—</div>
      </div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-body">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
      <h6 class="mb-0">Today's Waste Log</h6>
      <input type="date" id="dateFilter" class="form-control form-control-sm" style="max-width:180px">
    </div>

    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0" id="tblWaste">
        <thead>
          <tr>
            <th>Time</th>
            <th>Ingredient</th>
            <th class="text-end">Quantity</th>
            <th>Reason</th>
            <th>Notes</th>
            <th>Recorded By</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</div>

<div class="modal fade" id="wasteModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="wasteForm" class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title"><i class="bi bi-trash me-1"></i> Record Waste</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Ingredient</label>
          <select name="ingredient_id" class="form-select" required>
            <option value="">— Select ingredient —</option>
          </select>
          <div class="form-text" id="stockInfo"></div>
        </div>

        <div class="row g-2">
          <div class="col-md-6">
            <label class="form-label">Quantity</label>
            <div class="input-group">
              <input name="quantity" type="number" step="0.01" min="0.01" class="form-control" required>
              <span class="input-group-text" id="unitLabel">unit</span>
            </div>
          </div>
          <div class="col-md-6">
            <label class="form-label">Reason</label>
            <select name="reason" class="form-select" required>
              <option value="">— Select reason —</option>
              <option value="spill">Spill</option>
              <option value="spoilage">Spoilage</option>
              <option value="prep_error">Preparation Error</option>
              <option value="other">Other</option>
            </select>
          </div>
        </div>

        <div class="mt-3">
          <label class="form-label">Notes <span class="text-muted small">(optional)</span></label>
          <textarea name="notes" class="form-control" rows="2" maxlength="500"
            placeholder="e.g. dropped a container while preparing a latte"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-danger"><i class="bi bi-check-lg me-1"></i> Record Waste</button>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>