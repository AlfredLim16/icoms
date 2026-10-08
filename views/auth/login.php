<?php
require_once __DIR__ . '/../../helpers/Session.php';
Session::start();
if (Session::get('user_id')) {
  header('Location: /icoms/index.php');
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>ICOMS — Login</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@100..900&display=swap" rel="stylesheet">
  <link href="/icoms/public/css/base.css" rel="stylesheet">
  <link href="/icoms/public/css/login.css" rel="stylesheet">
</head>

<body class="login-page">

  <div class="login-card">
    <div class="text-center mb-4">
      <div class="brand">
        <i class="bi bi-cup-hot-fill"></i> ICOMS
      </div>
      <p class="login-subtitle mb-0">
        Integrated Cafe Inventory and Operation Management System
      </p>
    </div>

    <div id="loginAlert"></div>

    <form id="loginForm" autocomplete="off">
      <div class="mb-3">
        <label class="form-label login-label">Username</label>
        <div class="input-wrap">
          <i class="bi bi-person input-icon"></i>
          <input name="username" class="form-control login-input with-icon"
            placeholder="Enter your username" required autofocus>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label login-label">Password</label>
        <div class="input-wrap">
          <i class="bi bi-lock input-icon"></i>
          <input name="password" type="password"
            class="form-control login-input with-icon has-toggle"
            placeholder="Enter your password" required>
          <button type="button" class="toggle-password" tabindex="-1"
            aria-label="Show password" id="togglePassword">
            <i class="bi bi-eye"></i>
          </button>
        </div>
      </div>

      <button class="btn btn-login w-100 py-2 mt-2" type="submit">
        <i class="bi bi-box-arrow-in-right"></i> Sign In
      </button>
    </form>
  </div>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="/icoms/public/js/auth.js"></script>
</body>

</html>