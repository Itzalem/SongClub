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

    protected function validateJWT(): object
    {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

        if (!$header || !str_starts_with($header, 'Bearer ')) {
            $this->json(['error' => 'No token provided.'], 401);
        }

        $token = substr($header, 7);

        try {
            $decoded = JwtHelper::decode($token);
            return $decoded->data;
        } catch (\Exception $e) {
            $this->json(['error' => 'Invalid or expired token.'], 401);
        }
    }

    protected function getBody(): array
    {
        return json_decode(file_get_contents('php://input'), true) ?? [];
    }

    protected function getPagination(int $defaultLimit = 10): array
    {
        $limit  = max(1, min(50, (int) ($_GET['limit'] ?? $defaultLimit)));
        $page   = max(1, (int) ($_GET['page']  ?? 1));
        $offset = ($page - 1) * $limit;
        return ['page' => $page, 'limit' => $limit, 'offset' => $offset];
    }
}