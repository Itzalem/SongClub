<?php

namespace App\Controllers;

use App\Config;
use App\Framework\Controller;
use App\Models\User;
use App\Repositories\UserRepository;
use App\Services\UserService;
use App\Framework\JwtHelper;

class AuthController extends Controller
{
    private UserService $userService;

    public function __construct()
    {
        $this->userService = new UserService(new UserRepository());
    }

    // POST /api/auth/login
    public function login(array $vars = []): void
    {
        $body     = json_decode(file_get_contents('php://input'), true) ?? [];
        $email    = trim($body['email']    ?? '');
        $password = $body['password'] ?? '';

        if (empty($email) || empty($password)) {
            $this->json(['error' => 'Email and password are required.'], 400);
        }

        $user = $this->userService->login($email, $password);

        if (!$user) {
            $this->json(['error' => 'Invalid credentials.'], 401);
        }

        $token = $this->generateToken($user);

        $this->json([
            'token' => $token,
            'user'  => $this->userToArray($user),
        ]);
    }

    // POST /api/auth/register
    public function register(array $vars = []): void
    {
        $body     = json_decode(file_get_contents('php://input'), true) ?? [];
        $username = trim($body['username'] ?? '');
        $email    = trim($body['email']    ?? '');
        $password = $body['password'] ?? '';
        $bio      = trim($body['bio']      ?? '');

        try {
            $userId = $this->userService->register($username, $email, $password, $bio);
        } catch (\InvalidArgumentException $e) {
            $this->json(['error' => $e->getMessage()], 400);
        }

        $user  = $this->userService->getUserById($userId);
        $token = $this->generateToken($user);

        $this->json([
            'token' => $token,
            'user'  => $this->userToArray($user),
        ], 201);
    }

    // GET /api/auth/me  — requires JWT
    public function me(array $vars = []): void
    {
        $tokenData = $this->validateJWT();
        $user      = $this->userService->getUserById($tokenData->id);

        if (!$user) {
            $this->json(['error' => 'User not found.'], 404);
        }

        $this->json($this->userToArray($user));
    }

    // --- private helpers ---

    private function generateToken(User $user): string
    {
        $now = time();

        $payload = [
            'iat'  => $now,
            'exp'  => $now + Config::JWT_EXPIRES_IN,
            'data' => [
                'id'       => $user->userId,
                'username' => $user->username,
                'role'     => $user->role,
            ],
        ];

        return JwtHelper::encode($payload);
    }

    private function userToArray(User $user): array
    {
        return [
            'id'       => $user->userId,
            'username' => $user->username,
            'email'    => $user->email,
            'bio'      => $user->bio,
            'role'     => $user->role,
        ];
    }
}