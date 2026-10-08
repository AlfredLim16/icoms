<?php
$pageTitle = 'Inventory';
$pageScript = '/icoms/public/js/inventory.js';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h3 class="mb-0">Ingredient Inventory</h3>
    <p class="text-muted small mb-0">Track stock levels, reorder points, and expiration.</p>
  </div>
  <button class="btn btn-primary" id="btnAddIng">
    <i class="bi bi-plus-lg"></i> Add Ingredient
  </button>
</div>

<div class="card">
  <div class="card-body">
    <div class="row g-2 mb-3">
      <div class="col-md-6">
        <input id="searchBox" class="form-control" placeholder="🔍 Search ingredient…">
      </div>
      <div class="col-md-3">
        <select id="filterStatus" class="form-select">
          <option value="">All items</option>
          <option value="low">Low stock only</option>
          <option value="expiring">Expiring soon</option>
        </select>
      </div>
    </div>

    <table class="table table-hover align-middle mb-0" id="tblIngredients">
      <thead>
        <tr>
          <th>Name</th>
          <th>Stock</th>
          <th>Reorder Point</th>
          <th>Expiration</th>
          <th>Cost / Unit</th>
          <th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
  </div>
</div>

<div class="modal fade" id="ingModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="ingForm" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="ingModalTitle">Ingredient</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id">

        <div class="mb-3">
          <label class="form-label">Name</label>
          <input name="name" class="form-control" required>
        </div>

        <div class="row g-2">
          <div class="col-md-4">
            <label class="form-label">Unit</label>
            <select name="unit" class="form-select">
              <option value="g">grams (g)</option>
              <option value="kg">kilograms (kg)</option>
              <option value="ml">milliliters (ml)</option>
              <option value="L">liters (L)</option>
              <option value="pcs">pieces (pcs)</option>
              <option value="tbsp">tablespoon</option>
              <option value="tsp">teaspoon</option>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">Stock Qty</label>
            <input type="number" step="0.01" min="0" name="stock_qty" class="form-control" value="0" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">Reorder Point</label>
            <input type="number" step="0.01" min="0" name="reorder_point" class="form-control" value="0" required>
          </div>
        </div>

        <div class="row g-2 mt-2">
          <div class="col-md-6">
            <label class="form-label">Cost / Unit (₱)</label>
            <input type="number" step="0.01" min="0" name="cost_per_unit" class="form-control" value="0">
          </div>
          <div class="col-md-6">
            <label class="form-label">Expiration Date</label>
            <input type="date" name="expiration_date" class="form-control">
            <div class="form-text">Leave blank if not applicable.</div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-primary">Save</button>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>