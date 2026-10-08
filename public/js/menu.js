$(function () {
  const MENU_API = '/icoms/api/menu.php';
  const ING_API  = '/icoms/api/ingredients.php';
  const modal = new bootstrap.Modal('#menuModal');

  let menuCache = [];
  let ingredients = [];
  let categories = [];
  let searchTerm = '';
  let filterCat = '';
  let filterStatus = '';

  loadIngredients().then(() => {
    loadCategories();
    load();
  });

  function loadIngredients() {
    return $.get(ING_API, { action: 'list' }).then(res => {
      ingredients = res.success ? res.data : [];
    });
  }
  function loadCategories() {
    return $.get(MENU_API, { action: 'categories' }).then(res => {
      categories = res.success ? res.data : [];
      $('#catList').html(categories.map(c => `<option value="${c}">`).join(''));
      $('#filterCat').html(
        '<option value="">All categories</option>' +
        categories.map(c => `<option>${escapeHtml(c)}</option>`).join('')
      );
    });
  }

  function load() {
    $.get(MENU_API, { action: 'list' }).done(res => {
      if (!res.success) return toast(res.message, 'danger');
      menuCache = res.data;
      render();
    });
  }

  function render() {
    let rows = menuCache;

    if (searchTerm) {
      const q = searchTerm.toLowerCase();
      rows = rows.filter(m =>
        m.name.toLowerCase().includes(q) ||
        m.category.toLowerCase().includes(q)
      );
    }
    if (filterCat) {
      rows = rows.filter(m => m.category === filterCat);
    }
    if (filterStatus === 'available') {
      rows = rows.filter(m => m.is_available == 1);
    } else if (filterStatus === 'unavailable') {
      rows = rows.filter(m => m.is_available == 0);
    } else if (filterStatus === 'outofstock') {
      rows = rows.filter(m => m.max_servings !== null && m.max_servings <= 0);
    }

    if (!rows.length) {
      $('#tblMenu tbody').html(
        '<tr><td colspan="8" class="text-center text-muted py-4">No menu items found.</td></tr>'
      );
      return;
    }

    const html = rows.map(m => {
      const statusBadge = m.is_available
        ? '<span class="badge bg-success-subtle text-success">Available</span>'
        : '<span class="badge bg-secondary-subtle text-secondary">Unavailable</span>';

      let servingsHtml = '—';
      if (m.max_servings !== null) {
        const s = m.max_servings;
        if (s <= 0) servingsHtml = '<span class="badge bg-danger">0</span>';
        else if (s <= 5) servingsHtml = `<span class="badge bg-warning text-dark">${s}</span>`;
        else servingsHtml = `<span class="badge bg-success-subtle text-success">${s}</span>`;
      }

      const recipeHtml = m.recipe.length
        ? m.recipe.map(r => {
            const ing = ingredients.find(i => i.id == r.ingredient_id);
            const low = ing && parseFloat(ing.stock_qty) < parseFloat(r.quantity);
            const cls = low ? 'text-danger' : 'text-muted';
            return `<span class="${cls} small">${r.quantity}${r.unit} ${escapeHtml(r.name)}</span>`;
          }).join('<span class="text-muted small"> + </span>')
        : '<em class="text-muted small">no recipe</em>';

      const profitCls = (m.price - m.cost_per_serving) <= 0 ? 'text-danger' : 'text-muted';

      return `<tr>
        <td><strong>${escapeHtml(m.name)}</strong></td>
        <td><span class="badge bg-light text-dark">${escapeHtml(m.category)}</span></td>
        <td class="text-end">₱${parseFloat(m.price).toFixed(2)}</td>
        <td class="text-end ${profitCls}">₱${parseFloat(m.cost_per_serving).toFixed(2)}</td>
        <td class="text-center">${servingsHtml}</td>
        <td>${recipeHtml}</td>
        <td class="text-center">${statusBadge}</td>
        <td class="text-end">
          <button class="btn btn-sm btn-outline-primary edit" data-id="${m.id}" title="Edit"><i class="bi bi-pencil"></i></button>
          <button class="btn btn-sm btn-outline-danger del" data-id="${m.id}" title="Delete"><i class="bi bi-trash"></i></button>
        </td>
      </tr>`;
    }).join('');

    $('#tblMenu tbody').html(html);
  }

  $('#searchBox').on('input', function () { searchTerm = $(this).val().trim(); render(); });
  $('#filterCat').on('change', function () { filterCat = $(this).val(); render(); });
  $('#filterStatus').on('change', function () { filterStatus = $(this).val(); render(); });

  function addRecipeRow(row = {}) {
    const options = ingredients.map(i =>
      `<option value="${i.id}" ${i.id == row.ingredient_id ? 'selected' : ''}>
        ${escapeHtml(i.name)} (${i.unit})
      </option>`
    ).join('');

    const stockDisplay = row.ingredient_id ? renderStock(row.ingredient_id) : '<span class="text-muted small">—</span>';
    const unit = row.unit || (ingredients.find(i => i.id == row.ingredient_id)?.unit ?? '');

    const html = `<tr>
      <td>
        <select class="form-select form-select-sm ing-select">
          <option value="">— select ingredient —</option>
          ${options}
        </select>
      </td>
      <td>
        <div class="input-group input-group-sm">
          <input type="number" step="0.01" min="0" class="form-control ing-qty" value="${row.quantity ?? 1}">
          <span class="input-group-text ing-unit">${unit}</span>
        </div>
      </td>
      <td class="stock-cell">${stockDisplay}</td>
      <td class="text-end">
        <button type="button" class="btn btn-sm btn-outline-danger rmRow"><i class="bi bi-x-lg"></i></button>
      </td>
    </tr>`;
    $('#recipeTable tbody').append(html);
  }

  function renderStock(ingredientId) {
    const ing = ingredients.find(i => i.id == ingredientId);
    if (!ing) return '<span class="text-muted small">—</span>';
    const qty = parseFloat(ing.stock_qty);
    const cls = qty <= parseFloat(ing.reorder_point) ? 'text-warning fw-semibold' : 'text-muted';
    return `<span class="small ${cls}">${qty} ${ing.unit}</span>`;
  }

  function recalcSummary() {
    let cost = 0;
    let maxServings = null;

    $('#recipeTable tbody tr').each(function () {
      const ingId = $(this).find('.ing-select').val();
      const qty   = parseFloat($(this).find('.ing-qty').val()) || 0;
      const ing   = ingredients.find(i => i.id == ingId);
      if (!ing || qty <= 0) return;

      cost += parseFloat(ing.cost_per_unit) * qty;
      const canMake = Math.floor(parseFloat(ing.stock_qty) / qty);
      maxServings = maxServings === null ? canMake : Math.min(maxServings, canMake);
    });

    $('#sumCost').text('₱' + cost.toFixed(2));
    if (maxServings === null) {
      $('#sumServings').html('<span class="text-muted">—</span>');
    } else if (maxServings <= 0) {
      $('#sumServings').html('<span class="badge bg-danger">0 (out of stock)</span>');
    } else {
      $('#sumServings').html(`<span class="badge bg-success-subtle text-success">${maxServings} servings</span>`);
    }
  }

  $(document).on('change', '.ing-select', function () {
    const $row = $(this).closest('tr');
    const ing = ingredients.find(i => i.id == $(this).val());
    if (ing) {
      $row.find('.ing-unit').text(ing.unit);
      $row.find('.stock-cell').html(renderStock(ing.id));
    } else {
      $row.find('.ing-unit').text('');
      $row.find('.stock-cell').html('<span class="text-muted small">—</span>');
    }
    recalcSummary();
  });

  $(document).on('input', '.ing-qty', recalcSummary);

  $(document).on('click', '.rmRow', function () {
    $(this).closest('tr').remove();
    recalcSummary();
  });

  $('#btnAddRow').on('click', () => { addRecipeRow(); recalcSummary(); });

  $('#btnAddMenu').on('click', () => {
    $('#menuForm')[0].reset();
    $('[name=id]').val('');
    $('[name=is_available][type=hidden]').val('0');
    $('#availChk').prop('checked', true);
    $('#menuModalTitle').text('Add Menu Item');
    $('#recipeTable tbody').empty();
    addRecipeRow();
    recalcSummary();
    modal.show();
  });

  $(document).on('click', '.edit', function () {
    const m = menuCache.find(x => x.id == $(this).data('id'));
    if (!m) return;

    $('[name=id]').val(m.id);
    $('[name=name]').val(m.name);
    $('[name=category]').val(m.category);
    $('[name=price]').val(m.price);
    $('[name=is_available][type=hidden]').val('0');
    $('#availChk').prop('checked', m.is_available == 1);
    $('#menuModalTitle').text('Edit Menu Item');

    $('#recipeTable tbody').empty();
    if (m.recipe.length) {
      m.recipe.forEach(r => addRecipeRow(r));
    } else {
      addRecipeRow();
    }
    recalcSummary();
    modal.show();
  });

  $('#menuForm').on('submit', function (e) {
    e.preventDefault();

    const recipe = [];
    $('#recipeTable tbody tr').each(function () {
      const ingId = $(this).find('.ing-select').val();
      const qty   = parseFloat($(this).find('.ing-qty').val()) || 0;
      if (ingId && qty > 0) {
        recipe.push({ ingredient_id: parseInt(ingId), quantity: qty });
      }
    });

    if (recipe.length === 0) {
      if (!confirm('This menu item has no recipe. Continue anyway?')) return;
    }

    const data = $(this).serialize() + '&action=save&recipe=' + encodeURIComponent(JSON.stringify(recipe));
    $.post(MENU_API, data)
      .done(res => {
        toast(res.message, res.success ? 'success' : 'danger');
        if (res.success) { modal.hide(); loadCategories(); load(); }
      })
      .fail(xhr => toast(xhr.responseJSON?.message || 'Server error.', 'danger'));
  });

  $(document).on('click', '.del', function () {
    const m = menuCache.find(x => x.id == $(this).data('id'));
    if (!m) return;
    if (!confirm(`Delete "${m.name}"?\nThis will also remove its recipe.`)) return;

    $.post(MENU_API, { action: 'delete', id: m.id })
      .done(res => {
        toast(res.message, res.success ? 'success' : 'danger');
        if (res.success) load();
      })
      .fail(xhr => toast(xhr.responseJSON?.message || 'Server error.', 'danger'));
  });

  function escapeHtml(s) {
    return $('<div>').text(s ?? '').html();
  }
});