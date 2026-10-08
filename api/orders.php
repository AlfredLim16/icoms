<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/Response.php';
require_once __DIR__ . '/../core/BaseRepository.php';
require_once __DIR__ . '/../core/BaseService.php';
require_once __DIR__ . '/../helpers/Session.php';
require_once __DIR__ . '/../helpers/Auth.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/OrderItem.php';
require_once __DIR__ . '/../repositories/OrderRepository.php';
require_once __DIR__ . '/../repositories/MenuRepository.php';
require_once __DIR__ . '/../repositories/RecipeRepository.php';
require_once __DIR__ . '/../repositories/IngredientRepository.php';
require_once __DIR__ . '/../services/OrderService.php';

Auth::requireLogin();
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$svc = new OrderService();
$me = Auth::user();

try {
    switch ($action) {
        case 'checkout':
            $items = json_decode($_POST['items'] ?? '[]', true) ?: [];
            $receipt = $svc->checkout($items, (int)$me['id']);
            Response::ok($receipt, 'Order placed successfully.');

        case 'recent':
            $date = $_GET['date'] ?? null;
            Response::ok($svc->recent(20, $date));

        case 'view':
            Response::ok($svc->findWithItems((int)$_GET['id']));

        case 'stats':
            Response::ok($svc->todayStats());

        case 'search':
            $filters = [
                'from'       => $_GET['from'] ?? null,
                'to'         => $_GET['to'] ?? null,
                'order_no'   => $_GET['order_no'] ?? null,
                'cashier_id' => $_GET['cashier_id'] ?? null,
                'limit'      => $_GET['limit'] ?? 100,
            ];
            Response::ok([
                'orders'  => $svc->search($filters),
                'summary' => $svc->searchSummary($filters),
            ]);

        case 'cashiers':
            Response::ok($svc->getCashiers());

        default:
            Response::error('Unknown action.', 404);
    }
} catch (Exception $e) {
    Response::error($e->getMessage());
}
