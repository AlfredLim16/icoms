<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/Response.php';
require_once __DIR__ . '/../core/BaseRepository.php';
require_once __DIR__ . '/../core/BaseService.php';
require_once __DIR__ . '/../helpers/Session.php';
require_once __DIR__ . '/../helpers/Auth.php';
require_once __DIR__ . '/../models/Ingredient.php';
require_once __DIR__ . '/../repositories/IngredientRepository.php';
require_once __DIR__ . '/../services/InventoryService.php';

Auth::requireLogin();

$action = $_POST['action'] ?? $_GET['action'] ?? 'list';
$svc = new InventoryService();

try {
    switch ($action) {
        case 'list':
            Response::ok($svc->list());

        case 'alerts':
            Response::ok($svc->alerts());

        case 'save':
            $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
            Response::ok($svc->save($id, $_POST)->toArray(), 'Ingredient saved.');

        case 'delete':
            $svc->delete((int)$_POST['id']);
            Response::ok(null, 'Ingredient deleted.');

        default:
            Response::error('Unknown action.', 404);
    }
} catch (Exception $e) {
    Response::error($e->getMessage());
}
