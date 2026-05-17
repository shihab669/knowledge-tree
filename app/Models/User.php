<?php

namespace App\Models;

use App\Core\Database;
use App\Core\Auth;

class User
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findById(int $id): ?array
    {
        return $this->db->fetch(
            "SELECT * FROM users WHERE id = ?",
            [$id]
        );
    }

    public function findByUsername(string $username): ?array
    {
        return $this->db->fetch(
            "SELECT * FROM users WHERE username = ?",
            [$username]
        );
    }

    public function create(string $username, string $password, string $email = ''): int
    {
        return $this->db->insert('users', [
            'username' => $username,
            'password' => Auth::hashPassword($password),
            'email' => $email,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function updateLastLogin(int $userId): void
    {
        $this->db->update('users', [
            'last_login' => date('Y-m-d H:i:s')
        ], 'id = ?', [$userId]);
    }

    public function updatePassword(int $userId, string $newPassword): void
    {
        $this->db->update('users', [
            'password' => Auth::hashPassword($newPassword),
            'updated_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$userId]);
    }

    public function updateProfile(int $userId, array $data): void
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->update('users', $data, 'id = ?', [$userId]);
    }

    public function verifyCredentials(string $username, string $password): ?array
    {
        $user = $this->findByUsername($username);

        if (!$user) {
            return null;
        }

        if (Auth::verifyPassword($password, $user['password'])) {
            return $user;
        }

        return null;
    }

    public function usernameExists(string $username): bool
    {
        $result = $this->db->fetch(
            "SELECT COUNT(*) as count FROM users WHERE username = ?",
            [$username]
        );
        return $result['count'] > 0;
    }

    public function emailExists(string $email): bool
    {
        $result = $this->db->fetch(
            "SELECT COUNT(*) as count FROM users WHERE email = ?",
            [$email]
        );
        return $result['count'] > 0;
    }
}
