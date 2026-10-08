$(function () {
  const API = '/icoms/api/orders.php';
  let expanded = new Set();
  let itemsCache = {};
  let ordersCache = [];

  initDefaultRange();
  loadCashiers();
  load();

  function initDefaultRange() {
    const today = new Date();
    const past  = new Date();
    past.setDate(today.getDate() - 6);
    $('#fFrom').val(fmt(past));
    $('#fTo').val(fmt(today));
  }

  function loadCashiers() {
    $.get(API, { action: 'cashiers' }).done(res => {
      if (!res.success) return;
      $('#fCashier').html(
        '<option value="">All cashiers</option>' +
        res.data.map(c => `<option value="${c.id}">${escapeHtml(c.full_name)}</option>`).join('')
      );
    });
  }

  function load() {
    const params = {
      action: 'search',
      from:       $('#fFrom').val(),
      to:         $('#fTo').val(),
      order_no:   $('#fOrderNo').val(),
      cashier_id: $('#fCashier').val()
    };

    $('#tblOrders tbody').html('<tr><td colspan="6" class="text-center text-muted py-4">Loading…</td></tr>');

    $.get(API, params).done(res => {
      if (!res.success) return toast(res.message, 'danger');
      ordersCache = res.data.orders;
      renderSummary(res.data.summary);
      render();
    }).fail(() => {
      $('#tblOrders tbody').html('<tr><td colspan="6" class="text-center text-danger py-4">Failed to load orders.</td></tr>');
    });
  }

  function renderSummary(s) {
    $('#summaryRow').show();
    $('#sumOrders').text(s.orders);
    $('#sumRevenue').text('₱' + (+s.revenue).toFixed(2));
    const avg = s.orders > 0 ? s.revenue / s.orders : 0;
    $('#sumAvg').text('₱' + avg.toFixed(2));
  }

  function render() {
    if (!ordersCache.length) {
      $('#tblOrders tbody').html(
        '<tr><td colspan="6" class="text-center text-muted py-4">No orders found for these filters.</td></tr>'
      );
      return;
    }

    const html = ordersCache.map(o => {
      const isOpen = expanded.has(o.id);
      const caret = isOpen
        ? '<i class="bi bi-chevron-down"></i>'
        : '<i class="bi bi-chevron-right"></i>';

      let rowHtml = `
        <tr class="order-row" data-id="${o.id}" style="cursor:pointer;">
          <td class="text-center text-muted">${caret}</td>
          <td><code>${escapeHtml(o.order_no)}</code></td>
          <td class="small text-muted">${o.created_at}</td>
          <td class="small">${escapeHtml(o.cashier_name)}</td>
          <td class="text-center">${o.total_items}</td>
          <td class="text-end fw-semibold">₱${(+o.total).toFixed(2)}</td>
        </tr>`;

      if (isOpen) {
        rowHtml += `
          <tr class="detail-row" data-parent="${o.id}">
            <td colspan="6" class="bg-light">
              <div class="detail-body p-2 small" data-id="${o.id}">
                <div class="text-muted">Loading items…</div>
              </div>
            </td>
          </tr>`;
      }
      return rowHtml;
    }).join('');

    $('#tblOrders tbody').html(html);

    expanded.forEach(id => {
      if (!itemsCache[id]) loadItems(id);
      else renderItems(id);
    });
  }

  $(document).on('click', '.order-row', function () {
    const id = +$(this).data('id');
    if (expanded.has(id)) {
      expanded.delete(id);
    } else {
      expanded.add(id);
    }
    render();
  });

  function loadItems(orderId) {
    $.get(API, { action: 'view', id: orderId }).done(res => {
      if (!res.success) return;
      itemsCache[orderId] = res.data.items;
      renderItems(orderId);
    });
  }

  function renderItems(orderId) {
    const items = itemsCache[orderId] || [];
    const box = $(`.detail-body[data-id="${orderId}"]`);
    if (!box.length) return;

    if (!items.length) {
      box.html('<em class="text-muted">No items recorded.</em>');
      return;
    }

    const rows = items.map(i => `
      <tr>
        <td>${escapeHtml(i.menu_name)}</td>
        <td class="text-center">${i.quantity}</td>
        <td class="text-end">₱${(+i.unit_price).toFixed(2)}</td>
        <td class="text-end">₱${(+i.subtotal).toFixed(2)}</td>
      </tr>`).join('');

    box.html(`
      <table class="table table-sm mb-0">
        <thead class="table-light">
          <tr>
            <th>Item</th>
            <th class="text-center" style="width:80px">Qty</th>
            <th class="text-end" style="width:120px">Price</th>
            <th class="text-end" style="width:120px">Subtotal</th>
          </tr>
        </thead>
        <tbody>${rows}</tbody>
      </table>`);
  }

  $('#btnApply').on('click', load);

  $('#btnReset').on('click', function () {
    initDefaultRange();
    $('#fOrderNo').val('');
    $('#fCashier').val('');
    expanded.clear();
    load();
  });

  $('#fOrderNo').on('keypress', function (e) {
    if (e.which === 13) load();
  });

  function fmt(d) {
    const y = d.getFullYear();
    const m = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${y}-${m}-${day}`;
  }
  function escapeHtml(s) {
    return $('<div>').text(s ?? '').html();
  }
});