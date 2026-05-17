<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Response;
use App\Models\User;

class AuthController
{
    private User $user;

    public function __construct()
    {
        $this->user = new User();
    }

    public function showLogin(): void
    {
        if (Auth::check()) {
            Response::redirect('/dashboard');
        }
        Response::view('auth/login');
    }

    public function login(): void
    {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            Response::view('auth/login', ['error' => 'Please fill in all fields']);
            return;
        }

        $user = $this->user->verifyCredentials($username, $password);

        if (!$user) {
            Response::view('auth/login', ['error' => 'Invalid username or password']);
            return;
        }

        Auth::login($user['id'], $user['username']);
        $this->user->updateLastLogin($user['id']);

        Response::redirect('/dashboard');
    }

    public function showRegister(): void
    {
        if (Auth::check()) {
            Response::redirect('/dashboard');
        }
        Response::view('auth/register');
    }

    public function register(): void
    {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $email = trim($_POST['email'] ?? '');

        $errors = [];

        if (empty($username)) {
            $errors[] = 'Username is required';
        } elseif (strlen($username) < 3 || strlen($username) > 50) {
            $errors[] = 'Username must be between 3 and 50 characters';
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $errors[] = 'Username can only contain letters, numbers, and underscores';
        } elseif ($this->user->usernameExists($username)) {
            $errors[] = 'Username already taken';
        }

        if (empty($password)) {
            $errors[] = 'Password is required';
        } elseif (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters';
        }

        if ($password !== $confirmPassword) {
            $errors[] = 'Passwords do not match';
        }

        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format';
        }

        if (!empty($email) && $this->user->emailExists($email)) {
            $errors[] = 'Email already registered';
        }

        if (!empty($errors)) {
            Response::view('auth/register', [
                'errors' => $errors,
                'username' => $username,
                'email' => $email
            ]);
            return;
        }

        $userId = $this->user->create($username, $password, $email);
        Auth::login($userId, $username);

        Response::redirect('/dashboard');
    }

    public function logout(): void
    {
        Auth::logout();
        Response::redirect('/login');
    }
}
