<?php
class Response
{
    public static function json($data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    public static function ok($data = null, string $msg = 'OK'): void
    {
        self::json(['success' => true, 'message' => $msg, 'data' => $data]);
    }
    public static function error(string $msg, int $code = 400, $data = null): void
    {
        self::json(['success' => false, 'message' => $msg, 'data' => $data], $code);
    }
}
