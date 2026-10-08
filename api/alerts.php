<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/Response.php';
require_once __DIR__ . '/../core/BaseRepository.php';
require_once __DIR__ . '/../helpers/Session.php';
require_once __DIR__ . '/../helpers/Auth.php';
require_once __DIR__ . '/../repositories/IngredientRepository.php';

Auth::requireLogin();

try {
    $repo = new IngredientRepository();
    Response::ok([
        'alerts' => $repo->alerts(),
        'counts' => $repo->counts(),
    ]);
} catch (Exception $e) {
    Response::error($e->getMessage(), 500);
}
