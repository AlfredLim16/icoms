$(function () {
  const API = '/icoms/api/auth.php';

  $('#loginForm').on('submit', function (e) {
    e.preventDefault();
    const $btn = $(this).find('button[type=submit]');
    $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Signing in...');
    $('#loginAlert').html('');

    $.post(API, $(this).serialize() + '&action=login')
      .done(res => {
        if (res.success) {
          const role = res.data.role;
          const dest = role === 'owner'
            ? '/icoms/views/owner/dashboard.php'
            : '/icoms/views/staff/ordering.php';
          window.location.href = dest;
        } else {
          showError(res.message);
        }
      })
      .fail(xhr => {
        const msg = xhr.responseJSON?.message || 'Login failed. Please try again.';
        showError(msg);
      })
      .always(() => {
        $btn.prop('disabled', false).html('<i class="bi bi-box-arrow-in-right"></i> Sign In');
      });
  });

  function showError(msg) {
    $('#loginAlert').html(
      `<div class="alert alert-danger py-2 small">${msg}</div>`
    );
  }

  $('#togglePassword').on('click', function () {
    const $input = $('input[name=password]');
    const $icon  = $(this).find('i');

    const isHidden = $input.attr('type') === 'password';
    $input.attr('type', isHidden ? 'text' : 'password');

    $icon.toggleClass('bi-eye', !isHidden).toggleClass('bi-eye-slash', isHidden);

    $(this).attr('aria-label', isHidden ? 'Hide password' : 'Show password');
  });
});