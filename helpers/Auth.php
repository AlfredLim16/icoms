<?php
class Auth
{
    public static function requireLogin(): void
    {
        Session::start();
        if (!Session::get('user_id')) {
            $isAjax = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
            if ($isAjax) Response::error('Unauthenticated', 401);
            header('Location: /icoms/views/auth/login.php');
            exit;
        }
    }
    public static function requireRole(string ...$roles): void
    {
        self::requireLogin();
        if (!in_array(Session::get('role'), $roles)) Response::error('Forbidden', 403);
    }
    public static function user(): array
    {
        Session::start();
        return [
            'id'   => Session::get('user_id'),
            'name' => Session::get('full_name'),
            'role' => Session::get('role'),
        ];
    }
}
