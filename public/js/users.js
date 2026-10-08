$(function () {
  const API = '/icoms/api/users.php';
  const userModal = new bootstrap.Modal('#userModal');
  const pwdModal  = new bootstrap.Modal('#pwdModal');
  let cache = [];

  function load() {
    $.get(API, { action: 'list' }).done(res => {
      if (!res.success) return toast(res.message, 'danger');
      cache = res.data;

      const rows = cache.map(u => {
        const roleBadge = u.role === 'owner'
          ? '<span class="badge bg-warning text-dark">Owner</span>'
          : '<span class="badge bg-info text-dark">Staff</span>';
        const statusBadge = u.is_active
          ? '<span class="badge bg-success-subtle text-success">Active</span>'
          : '<span class="badge bg-secondary-subtle text-secondary">Disabled</span>';
        const isMe = u.id === window.ICOMS.user.id;
        const meTag = isMe ? ' <span class="badge bg-primary-subtle text-primary">You</span>' : '';

        return `<tr>
          <td>${escapeHtml(u.full_name)}${meTag}</td>
          <td><code>${escapeHtml(u.username)}</code></td>
          <td>${roleBadge}</td>
          <td>${statusBadge}</td>
          <td class="small text-muted">${(u.created_at || '').split(' ')[0]}</td>
          <td class="text-end">
            <button class="btn btn-sm btn-outline-primary edit" data-id="${u.id}" title="Edit"><i class="bi bi-pencil"></i></button>
            <button class="btn btn-sm btn-outline-warning reset" data-id="${u.id}" title="Reset Password"><i class="bi bi-key"></i></button>
            ${isMe ? '' : `<button class="btn btn-sm btn-outline-danger del" data-id="${u.id}" title="Delete"><i class="bi bi-trash"></i></button>`}
          </td></tr>`;
      }).join('');

      $('#tblUsers tbody').html(rows || '<tr><td colspan="6" class="text-center text-muted py-4">No users yet.</td></tr>');
    });
  }

  $('#btnAddUser').on('click', () => {
    $('#userForm')[0].reset();
    $('[name=id]').val('');
    $('[name=is_active]').prop('checked', true);
    $('#userModalTitle').text('Add User');
    $('#passwordGroup').show();
    $('[name=password]').prop('required', true);
    userModal.show();
  });

  $(document).on('click', '.edit', function () {
    const u = cache.find(x => x.id === $(this).data('id'));
    $('[name=id]').val(u.id);
    $('[name=full_name]').val(u.full_name);
    $('[name=username]').val(u.username);
    $('[name=role]').val(u.role);
    $('[name=is_active]').prop('checked', u.is_active == 1);
    $('#userModalTitle').text('Edit User');
    $('#passwordGroup').hide();
    $('[name=password]').prop('required', false).val('');
    userModal.show();
  });

  $('#userForm').on('submit', function (e) {
    e.preventDefault();
    const id = $('[name=id]').val();
    const action = id ? 'update' : 'create';
    $.post(API, $(this).serialize() + '&action=' + action)
      .done(res => {
        toast(res.message, res.success ? 'success' : 'danger');
        if (res.success) { userModal.hide(); load(); }
      })
      .fail(xhr => toast(xhr.responseJSON?.message || 'Server error.', 'danger'));
  });

  $(document).on('click', '.reset', function () {
    const u = cache.find(x => x.id === $(this).data('id'));
    $('#pwdForm')[0].reset();
    $('[name=id]').val(u.id);
    $('#pwdUserName').text(u.full_name);
    pwdModal.show();
  });

  $('#pwdForm').on('submit', function (e) {
    e.preventDefault();
    $.post(API, $(this).serialize() + '&action=reset_password')
      .done(res => {
        toast(res.message, res.success ? 'success' : 'danger');
        if (res.success) pwdModal.hide();
      })
      .fail(xhr => toast(xhr.responseJSON?.message || 'Server error.', 'danger'));
  });

  $(document).on('click', '.del', function () {
    const u = cache.find(x => x.id === $(this).data('id'));
    if (!confirm(`Delete "${u.full_name}"?\nThis cannot be undone.`)) return;
    $.post(API, { action: 'delete', id: u.id })
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