$(function () {
  const API = '/icoms/api/ingredients.php';
  const modal = new bootstrap.Modal('#ingModal');
  let cache = [];
  let searchTerm = '';
  let filter = '';

  function load() {
    $.get(API, { action: 'list' }).done(res => {
      if (!res.success) return toast(res.message, 'danger');
      cache = res.data;
      render();
    });
  }

  function render() {
    let rows = cache;

    if (searchTerm) {
      const q = searchTerm.toLowerCase();
      rows = rows.filter(i => i.name.toLowerCase().includes(q));
    }

    if (filter === 'low') {
      rows = rows.filter(i => +i.stock_qty <= +i.reorder_point);
    } else if (filter === 'expiring') {
      rows = rows.filter(i => {
        if (!i.expiration_date) return false;
        return new Date(i.expiration_date) - new Date() < 7 * 864e5;
      });
    }

    if (!rows.length) {
      $('#tblIngredients tbody').html(
        '<tr><td colspan="6" class="text-center text-muted py-4">No ingredients found.</td></tr>'
      );
      return;
    }

    const html = rows.map(i => {
      const stock = parseFloat(i.stock_qty);
      const rp    = parseFloat(i.reorder_point);
      const low   = stock <= rp;

      let expiryHtml = '<span class="text-muted">—</span>';
      let rowAccent = '';

      if (i.expiration_date) {
        const target = new Date(i.expiration_date);
        const today  = new Date();
        today.setHours(0,0,0,0);
        target.setHours(0,0,0,0);
        const days = Math.round((target - today) / 864e5);

        if (days < 0) {
          expiryHtml = `<span class="expiry-badge expiry-critical">
              <i class="bi bi-x-octagon-fill"></i> Expired ${Math.abs(days)}d ago</span>`;
          rowAccent = 'row-accent-danger';
        } else if (days === 0) {
          expiryHtml = `<span class="expiry-badge expiry-critical">
              <i class="bi bi-exclamation-octagon-fill"></i> Expires today</span>`;
          rowAccent = 'row-accent-danger';
        } else if (days <= 3) {
          expiryHtml = `<span class="expiry-badge expiry-critical">
              <i class="bi bi-exclamation-triangle-fill"></i> ${days} day${days>1?'s':''} left</span>`;
          rowAccent = 'row-accent-danger';
        } else if (days <= 7) {
          expiryHtml = `<span class="expiry-badge expiry-warning">
              <i class="bi bi-clock-history"></i> ${days} days left</span>`;
          rowAccent = 'row-accent-warning';
        } else {
          expiryHtml = `<span class="text-muted">${i.expiration_date}</span>`;
        }
      }

      const rowCls = low ? 'table-warning' : '';

      return `<tr class="${rowCls} ${rowAccent}">
        <td><strong>${escapeHtml(i.name)}</strong></td>
        <td>
          ${stock} ${i.unit}
          ${low ? '<i class="bi bi-exclamation-triangle-fill text-warning ms-1" title="Low stock"></i>' : ''}
        </td>
        <td>${rp}</td>
        <td>${expiryHtml}</td>
        <td>₱${parseFloat(i.cost_per_unit).toFixed(2)}</td>
        <td class="text-end">
          <button class="btn btn-sm btn-outline-primary edit" data-id="${i.id}" title="Edit"><i class="bi bi-pencil"></i></button>
          <button class="btn btn-sm btn-outline-danger del" data-id="${i.id}" title="Delete"><i class="bi bi-trash"></i></button>
        </td>
      </tr>`;
    }).join('');

    $('#tblIngredients tbody').html(html);
  }

  $('#searchBox').on('input', function () {
    searchTerm = $(this).val().trim();
    render();
  });
  $('#filterStatus').on('change', function () {
    filter = $(this).val();
    render();
  });

  $('#btnAddIng').on('click', () => {
    $('#ingForm')[0].reset();
    $('[name=id]').val('');
    $('[name=unit]').val('g');
    $('[name=stock_qty]').val(0);
    $('[name=reorder_point]').val(0);
    $('[name=cost_per_unit]').val(0);
    $('#ingModalTitle').text('Add Ingredient');
    modal.show();
  });

  $(document).on('click', '.edit', function () {
    const ing = cache.find(x => x.id == $(this).data('id'));
    if (!ing) return;
    $('[name=id]').val(ing.id);
    $('[name=name]').val(ing.name);
    $('[name=unit]').val(ing.unit);
    $('[name=stock_qty]').val(ing.stock_qty);
    $('[name=reorder_point]').val(ing.reorder_point);
    $('[name=cost_per_unit]').val(ing.cost_per_unit);
    $('[name=expiration_date]').val(ing.expiration_date || '');
    $('#ingModalTitle').text('Edit Ingredient');
    modal.show();
  });

  $('#ingForm').on('submit', function (e) {
    e.preventDefault();
    $.post(API, $(this).serialize() + '&action=save')
      .done(res => {
        toast(res.message, res.success ? 'success' : 'danger');
        if (res.success) { modal.hide(); load(); }
      })
      .fail(xhr => toast(xhr.responseJSON?.message || 'Server error.', 'danger'));
  });

  $(document).on('click', '.del', function () {
    const ing = cache.find(x => x.id == $(this).data('id'));
    if (!ing) return;
    if (!confirm(`Delete "${ing.name}"?\nThis cannot be undone.`)) return;

    $.post(API, { action: 'delete', id: ing.id })
      .done(res => {
        toast(res.message, res.success ? 'success' : 'danger');
        if (res.success) load();
      })
      .fail(xhr => toast(xhr.responseJSON?.message || 'Server error.', 'danger'));
  });

  function escapeHtml(s) {
    return $('<div>').text(s ?? '').html();
  }

  load();
});