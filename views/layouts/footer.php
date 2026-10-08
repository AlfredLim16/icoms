</main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
  window.ICOMS = { user: <?= json_encode($user) ?> };

  window.toast = function(msg, type = 'success') {
    const el = $(`<div class="position-fixed top-0 end-0 p-3" style="z-index:2000">
      <div class="alert alert-${type} shadow mb-0">${msg}</div></div>`);
    $('body').append(el);
    setTimeout(() => el.fadeOut(300, () => el.remove()), 2500);
  };

  $(document).on('click', '#logoutBtn', function(e){
    e.preventDefault();
    $.post('/icoms/api/auth.php', { action: 'logout' })
      .always(() => location.href = '/icoms/views/auth/login.php');
  });
</script>

<?php if (!empty($pageScript)): ?>
  <script src="<?= $pageScript ?>"></script>
<?php endif; ?>
</body>
</html>