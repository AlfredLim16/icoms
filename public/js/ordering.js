$(function () {
  const MENU_API   = '/icoms/api/menu.php';
  const ORDER_API  = '/icoms/api/orders.php';
  const receiptModal = new bootstrap.Modal('#receiptModal');

  let menuItems = [];
  let menuLookup = {};
  let cart = {};
  let activeCategory = 'All';
  let searchTerm = '';

  loadMenu();

  function loadMenu() {
    $.get(MENU_API, { action: 'list' }).done(res => {
      if (!res.success) return toast(res.message, 'danger');
      menuItems = res.data.filter(m => m.is_available == 1);
      menuLookup = {};
      menuItems.forEach(m => menuLookup[m.id] = m);

      renderCategoryTabs();
      renderMenuGrid();
    });
  }

  function renderCategoryTabs() {
    const cats = ['All', ...new Set(menuItems.map(m => m.category))];
    $('#categoryTabs').html(cats.map(c => `
      <button class="btn btn-sm ${c === activeCategory ? 'btn-dark' : 'btn-outline-secondary'}" data-cat="${escapeHtml(c)}">
        ${escapeHtml(c)}
      </button>
    `).join(''));
  }

  function renderMenuGrid() {
    let rows = menuItems;

    if (activeCategory !== 'All') {
      rows = rows.filter(m => m.category === activeCategory);
    }
    if (searchTerm) {
      const q = searchTerm.toLowerCase();
      rows = rows.filter(m => m.name.toLowerCase().includes(q));
    }

    if (!rows.length) {
      $('#menuGrid').html('<div class="col-12 text-center text-muted py-5">No menu items found.</div>');
      return;
    }

    const html = rows.map(m => {
      const out = m.max_servings !== null && m.max_servings <= 0;
      const low = m.max_servings !== null && m.max_servings > 0 && m.max_servings <= 5;
      const stockBadge = out
        ? '<span class="badge bg-danger">Out of stock</span>'
        : low
          ? `<span class="badge bg-warning text-dark">Only ${m.max_servings} left</span>`
          : '';

      return `
        <div class="col-6 col-md-4 col-xl-3">
          <div class="card menu-tile ${out ? 'opacity-50' : ''}" data-id="${m.id}" style="${out ? 'cursor:not-allowed' : 'cursor:pointer'}">
            <div class="card-body p-3">
              <div class="small text-muted">${escapeHtml(m.category)}</div>
              <div class="fw-bold mb-1" style="min-height:2.4em; line-height:1.2;">${escapeHtml(m.name)}</div>
              <div class="d-flex justify-content-between align-items-center">
                <span class="fs-6 fw-semibold text-primary">₱${parseFloat(m.price).toFixed(2)}</span>
                ${stockBadge}
              </div>
            </div>
          </div>
        </div>
      `;
    }).join('');

    $('#menuGrid').html(html);
  }

  $(document).on('click', '#categoryTabs button', function () {
    activeCategory = $(this).data('cat');
    renderCategoryTabs();
    renderMenuGrid();
  });

  $('#menuSearch').on('input', function () {
    searchTerm = $(this).val().trim();
    renderMenuGrid();
  });

  $(document).on('click', '.menu-tile', function () {
    const id = $(this).data('id');
    const m = menuLookup[id];
    if (!m) return;

    const out = m.max_servings !== null && m.max_servings <= 0;
    if (out) return toast(`"${m.name}" is out of stock.`, 'warning');

    cart[id] = (cart[id] || 0) + 1;
    renderCart();
  });

  function renderCart() {
    const ids = Object.keys(cart).filter(id => cart[id] > 0);

    if (!ids.length) {
      $('#cartBody').html(`
        <div class="text-center text-muted py-5 small">
          <i class="bi bi-cart-x display-6 d-block mb-2"></i>
          Cart is empty.<br>Click a menu item to add.
        </div>`);
      $('#cartTotal').text('0.00');
      $('#cartCount').text('0');
      $('#btnCheckout').prop('disabled', true);
      return;
    }

    let total = 0;
    let count = 0;

    const html = ids.map(id => {
      const m = menuLookup[id];
      const qty = cart[id];
      const sub = parseFloat(m.price) * qty;
      total += sub;
      count += qty;

      return `
        <div class="cart-row d-flex align-items-center gap-2 p-2 border-bottom" data-id="${id}">
          <div class="flex-grow-1">
            <div class="fw-semibold small">${escapeHtml(m.name)}</div>
            <div class="small text-muted">₱${parseFloat(m.price).toFixed(2)} × ${qty} = <strong>₱${sub.toFixed(2)}</strong></div>
          </div>
          <div class="btn-group btn-group-sm">
            <button class="btn btn-outline-secondary qty-dec">−</button>
            <button class="btn btn-outline-secondary qty-inc">+</button>
            <button class="btn btn-outline-danger cart-rm"><i class="bi bi-x"></i></button>
          </div>
        </div>`;
    }).join('');

    $('#cartBody').html(html);
    $('#cartTotal').text(total.toFixed(2));
    $('#cartCount').text(count);
    $('#btnCheckout').prop('disabled', false);
  }

  $(document).on('click', '.qty-inc', function () {
    const id = $(this).closest('.cart-row').data('id');
    cart[id]++;
    renderCart();
  });

  $(document).on('click', '.qty-dec', function () {
    const id = $(this).closest('.cart-row').data('id');
    cart[id]--;
    if (cart[id] <= 0) delete cart[id];
    renderCart();
  });

  $(document).on('click', '.cart-rm', function () {
    const id = $(this).closest('.cart-row').data('id');
    delete cart[id];
    renderCart();
  });

  $('#btnClearCart').on('click', () => {
    if (!Object.keys(cart).length) return;
    if (!confirm('Clear the entire cart?')) return;
    cart = {};
    renderCart();
  });

  $('#btnCheckout').on('click', function () {
    const items = Object.keys(cart).map(id => ({
      menu_item_id: parseInt(id),
      quantity: cart[id]
    }));

    if (!items.length) return;

    const $btn = $(this);
    $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Processing…');

    $.post(ORDER_API, {
      action: 'checkout',
      items: JSON.stringify(items)
    })
    .done(res => {
      if (!res.success) { toast(res.message, 'danger'); return; }
      showReceipt(res.data);
      cart = {};
      renderCart();
      loadMenu();
    })
    .fail(xhr => toast(xhr.responseJSON?.message || 'Checkout failed.', 'danger'))
    .always(() => $btn.prop('disabled', false).html('<i class="bi bi-check-circle me-1"></i> Checkout'));
  });

  function showReceipt(r) {
    const itemsHtml = r.items.map(i => `
      <tr>
        <td>${escapeHtml(i.menu_name)}</td>
        <td class="text-center">${i.quantity}</td>
        <td class="text-end">₱${(+i.unit_price).toFixed(2)}</td>
        <td class="text-end">₱${(+i.subtotal).toFixed(2)}</td>
      </tr>`).join('');

    const html = `
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title"><i class="bi bi-check-circle-fill me-1"></i> Order Placed</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="receiptPrint">
        <div class="text-center mb-3">
          <div class="h5 mb-0">ICOMS</div>
          <div class="small text-muted">Order Receipt</div>
        </div>

        <div class="d-flex justify-content-between small mb-1">
          <span class="text-muted">Order No:</span><span><strong>${r.order_no}</strong></span>
        </div>
        <div class="d-flex justify-content-between small mb-1">
          <span class="text-muted">Cashier:</span><span>${escapeHtml(r.cashier_name)}</span>
        </div>
        <div class="d-flex justify-content-between small mb-3">
          <span class="text-muted">Date:</span><span>${r.created_at}</span>
        </div>

        <table class="table table-sm mb-2">
          <thead class="table-light">
            <tr><th>Item</th><th class="text-center">Qty</th><th class="text-end">Price</th><th class="text-end">Subtotal</th></tr>
          </thead>
          <tbody>${itemsHtml}</tbody>
        </table>

        <div class="d-flex justify-content-between fs-5">
          <span>Total</span>
          <span class="fw-bold">₱${(+r.total).toFixed(2)}</span>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-outline-secondary" onclick="window.print()"><i class="bi bi-printer"></i> Print</button>
        <button class="btn btn-success" data-bs-dismiss="modal">Done</button>
      </div>
    `;

    $('#receiptContent').html(html);
    receiptModal.show();
  }

  function escapeHtml(s) {
    return $('<div>').text(s ?? '').html();
  }
});