<?php

namespace App\Core;

class Response
{
    public static function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function success(mixed $data = null, string $message = 'Success'): void
    {
        self::json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ]);
    }

    public static function error(string $message, int $status = 400): void
    {
        self::json([
            'success' => false,
            'error' => $message
        ], $status);
    }

    public static function view(string $path, array $data = []): void
    {
        extract($data);
        $viewPath = APP_ROOT . '/app/Views/' . $path . '.php';

        if (!file_exists($viewPath)) {
            throw new \RuntimeException("View not found: {$path}");
        }

        ob_start();
        include $viewPath;
        $content = ob_get_clean();

        include APP_ROOT . '/app/Views/layout.php';
    }

    public static function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }

    public static function back(): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        self::redirect($referer);
    }
}
