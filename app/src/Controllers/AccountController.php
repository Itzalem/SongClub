<?php

namespace App\Controllers;

use App\Framework\Controller;
use App\Services\UserService;
use App\Repositories\UserRepository;

class AccountController extends Controller
{
    // GET / — landing page; redirect to home if already logged in
    public function landing(array $vars = []): void
    {
        if (isset($_SESSION['user_id'])) {
            header('Location: /profile/' . (int) $_SESSION['user_id']);
            exit;
        }

        $this->render('Landing', []);
    }

    public function login(array $vars = []): void
    {
        if (isset($_SESSION['user_id'])) {
            header('Location: /profile/' . (int) $_SESSION['user_id']);
            exit;
        }

        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email    = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                $error = 'All fields are required.';
            } else {
                $userService = new UserService(new UserRepository());
                $user        = $userService->login($email, $password);

                if ($user) {
                    $_SESSION['user_id']  = $user->userId;
                    $_SESSION['username'] = $user->username;
                    $_SESSION['role']     = $user->role;
                    header('Location: /profile/' . (int) $user->userId);
                    exit;
                } else {
                    $error = 'Invalid email or password.';
                }
            }
        }

        $this->render('Login', ['error' => $error]);
    }

    public function register(array $vars = []): void
    {
        if (isset($_SESSION['user_id'])) {
            header('Location: /profile/' . (int) $_SESSION['user_id']);
            exit;
        }

        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $userService = new UserService(new UserRepository());
                $userId      = $userService->register(
                    $_POST['username'] ?? '',
                    $_POST['email']    ?? '',
                    $_POST['password'] ?? '',
                    $_POST['bio']      ?? ''
                );
                $_SESSION['user_id']  = $userId;
                $_SESSION['username'] = trim($_POST['username'] ?? '');
                $_SESSION['role']     = 'user';
                header('Location: /profile/' . (int) $userId);
                exit;
            } catch (\InvalidArgumentException $e) {
                $error = $e->getMessage();
            }
        }

        $this->render('Register', ['error' => $error]);
    }

    public function logout(array $vars = []): void
    {
        unset($_SESSION['user_id'], $_SESSION['username'], $_SESSION['role']);
        session_destroy();
        header('Location: /login');
        exit;
    }
}
