<?php

namespace App\Framework;

abstract class Controller
{
    /**
     * * @param string $view 
     * @param array $data 
     */
    protected function render(string $view, array $data = []): void
    {
        extract($data);
        require __DIR__ . '/../Views/' . $view . '.php';
    }

    protected function requireAuth(): void
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
    }

    protected function requireAdmin(): void
    {
        $this->requireAuth();
        if (($_SESSION['role'] ?? '') !== 'admin') {
            header('Location: /');
            exit;
        }
    }

    protected function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    // Validates the JWT from the Authorization header.
    // Returns the token payload (id, username, role) or responds 401 and exits.
    protected function validateJWT(): object
    {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

        if (!$header || !str_starts_with($header, 'Bearer ')) {
            $this->json(['error' => 'No token provided'], 401);
        }

        $token = substr($header, 7);

        try {
            $decoded = JWT::decode($token, new Key(Config::JWT_SECRET, 'HS256'));
            return $decoded->data;
        } catch (\Exception $e) {
            $this->json(['error' => 'Invalid or expired token'], 401);
        }
    }
}