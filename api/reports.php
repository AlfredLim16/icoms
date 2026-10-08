<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/Response.php';
require_once __DIR__ . '/../core/BaseRepository.php';
require_once __DIR__ . '/../core/BaseService.php';
require_once __DIR__ . '/../helpers/Session.php';
require_once __DIR__ . '/../helpers/Auth.php';
require_once __DIR__ . '/../repositories/OrderRepository.php';
require_once __DIR__ . '/../repositories/WasteRepository.php';
require_once __DIR__ . '/../services/ReportService.php';

Auth::requireRole('owner');

$action = $_POST['action'] ?? $_GET['action'] ?? 'summary';
$svc = new ReportService();

try {
    switch ($action) {
        case 'summary':
            $from = $_GET['from'] ?? null;
            $to   = $_GET['to']   ?? null;
            Response::ok($svc->summary($from, $to));

        case 'recent_orders':
            Response::ok($svc->recentOrders(20));

        default:
            Response::error('Unknown action.', 404);
    }
} catch (Exception $e) {
    Response::error($e->getMessage());
}
