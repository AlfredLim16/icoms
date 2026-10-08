<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/Response.php';
require_once __DIR__ . '/../core/BaseRepository.php';
require_once __DIR__ . '/../core/BaseService.php';
require_once __DIR__ . '/../helpers/Session.php';
require_once __DIR__ . '/../helpers/Auth.php';
require_once __DIR__ . '/../repositories/UserRepository.php';
require_once __DIR__ . '/../services/AuthService.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$svc = new AuthService();

try {
    switch ($action) {
        case 'login':
            $user = $svc->login($_POST['username'] ?? '', $_POST['password'] ?? '');
            Response::ok($user, 'Login successful.');
        case 'logout':
            $svc->logout();
            Response::ok(null, 'Logged out.');
        case 'check':
            Response::ok(Auth::user());
        default:
            Response::error('Unknown action.', 404);
    }
} catch (Exception $e) {
    Response::error($e->getMessage(), 401);
}
