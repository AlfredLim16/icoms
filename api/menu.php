<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/Response.php';
require_once __DIR__ . '/../core/BaseRepository.php';
require_once __DIR__ . '/../core/BaseService.php';
require_once __DIR__ . '/../helpers/Session.php';
require_once __DIR__ . '/../helpers/Auth.php';
require_once __DIR__ . '/../models/MenuItem.php';
require_once __DIR__ . '/../repositories/MenuRepository.php';
require_once __DIR__ . '/../repositories/RecipeRepository.php';
require_once __DIR__ . '/../repositories/IngredientRepository.php';
require_once __DIR__ . '/../services/MenuService.php';

Auth::requireLogin();

$action = $_POST['action'] ?? $_GET['action'] ?? 'list';
$svc = new MenuService();

try {
    switch ($action) {
        case 'list':
            Response::ok($svc->list());

        case 'categories':
            Response::ok($svc->categories());

        case 'save':
            $id     = !empty($_POST['id']) ? (int)$_POST['id'] : null;
            $recipe = json_decode($_POST['recipe'] ?? '[]', true) ?: [];
            Response::ok($svc->save($id, $_POST, $recipe), 'Menu item saved.');

        case 'delete':
            $svc->delete((int)$_POST['id']);
            Response::ok(null, 'Menu item deleted.');

        default:
            Response::error('Unknown action.', 404);
    }
} catch (Exception $e) {
    Response::error($e->getMessage());
}
