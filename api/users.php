<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/Response.php';
require_once __DIR__ . '/../core/BaseRepository.php';
require_once __DIR__ . '/../core/BaseService.php';
require_once __DIR__ . '/../helpers/Session.php';
require_once __DIR__ . '/../helpers/Auth.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../repositories/UserRepository.php';
require_once __DIR__ . '/../services/UserService.php';

Auth::requireRole('owner');
$action = $_POST['action'] ?? $_GET['action'] ?? 'list';
$svc = new UserService();
$me  = Auth::user();

try {
    switch ($action) {
        case 'list':
            Response::ok($svc->list());

        case 'create':
            Response::ok($svc->create($_POST)->toArray(), 'User created.');

        case 'update':
            Response::ok($svc->update((int)$_POST['id'], $_POST)->toArray(), 'User updated.');

        case 'reset_password':
            $svc->resetPassword((int)$_POST['id'], $_POST['password'] ?? '');
            Response::ok(null, 'Password reset successfully.');

        case 'delete':
            $svc->delete((int)$_POST['id'], (int)$me['id']);
            Response::ok(null, 'User deleted.');

        default:
            Response::error('Unknown action.', 404);
    }
} catch (Exception $e) {
    Response::error($e->getMessage());
}
