<?php
$pageTitle = 'Menu & Recipes';
$pageScript = '/icoms/public/js/menu.js';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h3 class="mb-0">Menu &amp; Recipes</h3>
    <p class="text-muted small mb-0">Each menu item is linked to the ingredients it consumes.</p>
  </div>
  <button class="btn btn-primary" id="btnAddMenu">
    <i class="bi bi-plus-lg"></i> Add Menu Item
  </button>
</div>

<div class="card">
  <div class="card-body">
    <div class="row g-2 mb-3">
      <div class="col-md-5">
        <input id="searchBox" class="form-control" placeholder="🔍 Search menu item…">
      </div>
      <div class="col-md-3">
        <select id="filterCat" class="form-select">
          <option value="">All categories</option>
        </select>
      </div>
      <div class="col-md-4">
        <select id="filterStatus" class="form-select">
          <option value="">All statuses</option>
          <option value="available">Available only</option>
          <option value="unavailable">Unavailable only</option>
          <option value="outofstock">Cannot make (out of stock)</option>
        </select>
      </div>
    </div>

    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0" id="tblMenu">
        <thead>
          <tr>
            <th>Menu Item</th>
            <th>Category</th>
            <th class="text-end">Price</th>
            <th class="text-end">Cost</th>
            <th class="text-center">Max Servings</th>
            <th>Recipe</th>
            <th class="text-center">Status</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</div>

<div class="modal fade" id="menuModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <form id="menuForm" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="menuModalTitle">Add Menu Item</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id">

        <div class="row g-2">
          <div class="col-md-6">
            <label class="form-label">Menu Name</label>
            <input name="name" class="form-control" required>
          </div>
          <div class="col-md-3">
            <label class="form-label">Category</label>
            <input name="category" class="form-control" list="catList" placeholder="e.g. Coffee">
            <datalist id="catList"></datalist>
          </div>
          <div class="col-md-3">
            <label class="form-label">Price (₱)</label>
            <input name="price" type="number" step="0.01" min="0.01" class="form-control" required>
          </div>
        </div>

        <div class="form-check form-switch mt-3">
          <input type="hidden" name="is_available" value="0">
          <input class="form-check-input" type="checkbox" name="is_available" value="1" id="availChk" checked>
          <label class="form-check-label" for="availChk">Available for ordering</label>
        </div>

        <hr class="my-4">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <h6 class="mb-0"><i class="bi bi-list-check me-1"></i> Recipe (Ingredients)</h6>
          <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddRow">
            <i class="bi bi-plus-lg"></i> Add Ingredient
          </button>
        </div>

        <div class="table-responsive">
          <table class="table table-sm align-middle mb-0" id="recipeTable">
            <thead class="table-light">
              <tr>
                <th style="min-width:220px">Ingredient</th>
                <th style="width:130px">Quantity</th>
                <th style="width:130px" class="text-muted small">Stock on hand</th>
                <th style="width:50px"></th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>

        <div id="recipeSummary" class="alert alert-light border mt-3 mb-0 small">
          <div class="row">
            <div class="col-md-6">
              <strong>Cost / serving:</strong> <span id="sumCost">₱0.00</span>
            </div>
            <div class="col-md-6">
              <strong>Can make:</strong> <span id="sumServings">—</span>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-primary">Save Menu Item</button>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>