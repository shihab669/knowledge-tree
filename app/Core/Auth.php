<?php

namespace App\Core;

class Auth
{
    public static function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function login(int $userId, string $username): void
    {
        self::startSession();
        session_regenerate_id(true);
        $_SESSION['user_id'] = $userId;
        $_SESSION['username'] = $username;
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();
        $_SESSION['csrf_token'] = self::generateToken();
    }

    public static function logout(): void
    {
        self::startSession();
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        session_destroy();
    }

    public static function check(): bool
    {
        self::startSession();

        if (!isset($_SESSION['user_id'])) {
            return false;
        }

        // Check session timeout
        if (isset($_SESSION['last_activity']) &&
            (time() - $_SESSION['last_activity']) > SESSION_LIFETIME) {
            self::logout();
            return false;
        }

        $_SESSION['last_activity'] = time();
        return true;
    }

    public static function requireAuth(): void
    {
        if (!self::check()) {
            if (str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/api')) {
                header('Content-Type: application/json');
                http_response_code(401);
                echo json_encode(['error' => 'Authentication required']);
                exit;
            }
            header('Location: /login');
            exit;
        }
    }

    public static function getUserId(): ?int
    {
        self::startSession();
        return $_SESSION['user_id'] ?? null;
    }

    public static function getUsername(): ?string
    {
        self::startSession();
        return $_SESSION['username'] ?? null;
    }

    public static function getCsrfToken(): string
    {
        self::startSession();
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = self::generateToken();
        }
        return $_SESSION['csrf_token'];
    }

    public static function verifyCsrfToken(string $token): bool
    {
        self::startSession();
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    public static function generateToken(): string
    {
        return bin2hex(random_bytes(CSRF_TOKEN_LENGTH));
    }

    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]);
    }

    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
}
