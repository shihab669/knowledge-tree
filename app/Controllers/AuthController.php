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

    public function logout(): void
    {
        Auth::logout();
        Response::redirect('/login');
    }
}
