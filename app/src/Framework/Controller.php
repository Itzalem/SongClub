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

    $parts = explode('.', substr($header, 7));

    if (count($parts) !== 3) {
        $this->json(['error' => 'Invalid token'], 401);
    }

    [$h, $p, $sig] = $parts;
    $expected = rtrim(strtr(base64_encode(hash_hmac('sha256', "$h.$p", \App\Config::JWT_SECRET, true)), '+/', '-_'), '=');

    if (!hash_equals($expected, $sig)) {
        $this->json(['error' => 'Invalid token signature'], 401);
    }

    $data = json_decode(base64_decode(strtr($p, '-_', '+/')));

    if (!$data || $data->exp < time()) {
        $this->json(['error' => 'Token expired'], 401);
    }

    return $data->data;
}
}