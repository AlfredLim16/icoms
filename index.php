<?php
require_once __DIR__ . '/helpers/Session.php';
Session::start();

if (!Session::get('user_id')) {
    header('Location: /icoms/views/auth/login.php');
    exit;
}

$role = Session::get('role');
header('Location: /icoms/views/' . $role . '/dashboard.php');
exit;
