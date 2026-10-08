<?php
  $pageTitle = 'Ordering';
  $pageScript = '/icoms/public/js/ordering.js';
  require_once __DIR__ . '/../layouts/header.php';
  require_once __DIR__ . '/../layouts/sidebar.php';
?>

<div class="row g-3" style="min-height: calc(100vh - 2rem);">

  <!-- ============ LEFT: MENU ============ -->
  <div class="col-lg-8 d-flex flex-column">
    <div class="card mb-3">
      <div class="card-body">
        <div class="d-flex flex-wrap gap-2 align-items-center">
          <input id="menuSearch" class="form-control" placeholder="🔍 Search menu…" style="max-width:260px">
          <div id="categoryTabs" class="d-flex flex-wrap gap-2"></div>
        </div>
      </div>
    </div>

    <div class="flex-grow-1 overflow-auto">
      <div id="menuGrid" class="row g-2"></div>
    </div>
  </div>

  <!-- ============ RIGHT: CART ============ -->
  <div class="col-lg-4 d-flex flex-column">
    <div class="card d-flex flex-column" style="height:100%;">
      <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
        <strong><i class="bi bi-cart3 me-1"></i> Current Order</strong>
        <button class="btn btn-sm btn-outline-light" id="btnClearCart" title="Clear cart">
          <i class="bi bi-x-circle"></i>
        </button>
      </div>

      <div class="card-body flex-grow-1 overflow-auto p-2" id="cartBody">
        <div class="text-center text-muted py-5 small" id="cartEmpty">
          <i class="bi bi-cart-x display-6 d-block mb-2"></i>
          Cart is empty.<br>Click a menu item to add.
        </div>
      </div>

      <div class="card-footer bg-white border-top">
        <div class="d-flex justify-content-between mb-1 small text-muted">
          <span>Items:</span><span id="cartCount">0</span>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-3">
          <span class="fs-6">Total:</span>
          <span class="fs-3 fw-bold">₱<span id="cartTotal">0.00</span></span>
        </div>
        <button class="btn btn-success w-100 py-2" id="btnCheckout" disabled>
          <i class="bi bi-check-circle me-1"></i> Checkout
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ============ RECEIPT MODAL ============ -->
<div class="modal fade" id="receiptModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" id="receiptContent">
      <!-- filled by JS -->
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>