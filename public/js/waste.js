$(function () {
  const WASTE_API = '/icoms/api/waste.php';
  const ING_API   = '/icoms/api/ingredients.php';
  const modal = new bootstrap.Modal('#wasteModal');

  let ingredients = [];
  let ingLookup = {};
  let records = [];

  loadIngredients().then(() => {
    loadRecords();
    loadSummary();
  });

  function loadIngredients() {
    return $.get(ING_API, { action: 'list' }).then(res => {
      ingredients = res.success ? res.data : [];
      ingLookup = {};
      ingredients.forEach(i => ingLookup[i.id] = i);

      $('select[name=ingredient_id]').html(
        '<option value="">— Select ingredient —</option>' +
        ingredients.map(i =>
          `<option value="${i.id}">${escapeHtml(i.name)} (${i.unit})</option>`
        ).join('')
      );
    });
  }

  function loadRecords(date = null) {
    const params = { action: 'recent' };
    if (date) params.date = date;

    $.get(WASTE_API, params).done(res => {
      if (!res.success) return toast(res.message, 'danger');
      records = res.data;
      renderRecords();
    });
  }

  function loadSummary() {
    $.get(WASTE_API, { action: 'summary' }).done(res => {
      if (!res.success) return;
      const { by_reason, count } = res.data;
      $('#sumCount').text(count);

      const reasonLabels = {
        spill: 'Spill', spoilage: 'Spoilage',
        prep_error: 'Prep Error', other: 'Other'
      };
      const colors = {
        spill: 'primary', spoilage: 'warning', prep_error: 'danger', other: 'secondary'
      };

      $('.reason-card').remove();

      const cards = Object.entries(reasonLabels).map(([key, label]) => {
        const entry = by_reason.find(r => r.reason === key);
        const cnt   = entry ? entry.cnt : 0;
        const color = colors[key];
        return `
          <div class="col-md-3 reason-card">
            <div class="card stat-card p-3">
              <div class="stat-icon bg-${color}-subtle text-${color}">
                <i class="bi bi-${key === 'spill' ? 'droplet' : key === 'spoilage' ? 'hourglass-split' : key === 'prep_error' ? 'tools' : 'question-circle'}"></i>
              </div>
              <div>
                <div class="text-muted small">${label}</div>
                <div class="fs-5 fw-bold">${cnt}</div>
              </div>
            </div>
          </div>`;
      }).join('');

      $('#summaryRow').append(cards);
    });
  }

  function renderRecords() {
    if (!records.length) {
      $('#tblWaste tbody').html(
        '<tr><td colspan="6" class="text-center text-muted py-4">No waste recorded for this date.</td></tr>'
      );
      return;
    }

    const reasonBadges = {
      spill:      '<span class="badge bg-primary-subtle text-primary">Spill</span>',
      spoilage:   '<span class="badge bg-warning-subtle text-warning-emphasis">Spoilage</span>',
      prep_error: '<span class="badge bg-danger-subtle text-danger">Prep Error</span>',
      other:      '<span class="badge bg-secondary-subtle text-secondary">Other</span>'
    };

    const html = records.map(r => {
      const time = (r.created_at || '').split(' ')[1] || '';
      const qty  = parseFloat(r.quantity);
      const qtyStr = qty.toString().replace(/\.00$/, '');
      return `<tr>
        <td class="text-muted small">${time}</td>
        <td><strong>${escapeHtml(r.ingredient_name)}</strong></td>
        <td class="text-end">${qtyStr} ${escapeHtml(r.ingredient_unit)}</td>
        <td>${reasonBadges[r.reason] || escapeHtml(r.reason)}</td>
        <td class="small text-muted">${escapeHtml(r.notes || '—')}</td>
        <td class="small">${escapeHtml(r.user_name)}</td>
      </tr>`;
    }).join('');

    $('#tblWaste tbody').html(html);
  }

  $('#btnNewWaste').on('click', () => {
    $('#wasteForm')[0].reset();
    $('#stockInfo').text('');
    $('#unitLabel').text('unit');
    modal.show();
  });

  $(document).on('change', 'select[name=ingredient_id]', function () {
    const id = $(this).val();
    const ing = ingLookup[id];
    if (!ing) {
      $('#stockInfo').text('');
      $('#unitLabel').text('unit');
      return;
    }
    const stock = parseFloat(ing.stock_qty);
    const cls = stock <= parseFloat(ing.reorder_point) ? 'text-warning' : 'text-muted';
    $('#stockInfo').html(`<span class="${cls}">On hand: <strong>${stock} ${ing.unit}</strong></span>`);
    $('#unitLabel').text(ing.unit);
  });

  $('#wasteForm').on('submit', function (e) {
    e.preventDefault();
    $.post(WASTE_API, $(this).serialize() + '&action=record')
      .done(res => {
        toast(res.message, res.success ? 'success' : 'danger');
        if (res.success) {
          modal.hide();
          loadRecords($('#dateFilter').val() || null);
          loadSummary();
          loadIngredients();
        }
      })
      .fail(xhr => toast(xhr.responseJSON?.message || 'Server error.', 'danger'));
  });

  $('#dateFilter').on('change', function () {
    loadRecords($(this).val() || null);
  });

  function escapeHtml(s) {
    return $('<div>').text(s ?? '').html();
  }
});