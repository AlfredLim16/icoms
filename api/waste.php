<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/Response.php';
require_once __DIR__ . '/../core/BaseRepository.php';
require_once __DIR__ . '/../core/BaseService.php';
require_once __DIR__ . '/../helpers/Session.php';
require_once __DIR__ . '/../helpers/Auth.php';
require_once __DIR__ . '/../models/WasteRecord.php';
require_once __DIR__ . '/../repositories/WasteRepository.php';
require_once __DIR__ . '/../repositories/IngredientRepository.php';
require_once __DIR__ . '/../services/WasteService.php';

Auth::requireLogin();

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$svc = new WasteService();
$me = Auth::user();

try {
    switch ($action) {
        case 'record':
            $record = $svc->record($_POST, (int)$me['id']);
            Response::ok($record, 'Waste recorded successfully.');

        case 'recent':
            $date = $_GET['date'] ?? null;
            Response::ok($svc->recent(50, $date));

        case 'summary':
            Response::ok([
                'by_reason' => $svc->todayByReason(),
                'count' => $svc->todayCount(),
            ]);

        default:
            Response::error('Unknown action.', 404);
    }
} catch (Exception $e) {
    Response::error($e->getMessage());
}
