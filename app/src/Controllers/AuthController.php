<?php

namespace App\Controllers;

use App\Config;
use App\Framework\Controller;
use App\Framework\JwtHelper;
use App\Models\User;
use App\Repositories\UserRepository;
use App\Services\UserService;

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
        $body     = $this->getBody();
        $email    = trim($body['email']    ?? '');
        $password = $body['password'] ?? '';

        if (empty($email) || empty($password)) {
            $this->json(['error' => 'Email and password are required.'], 400);
        }

        $user = $this->userService->login($email, $password);

        if (!$user) {
            $this->json(['error' => 'Invalid credentials.'], 401);
        }

        $this->json([
            'token' => $this->generateToken($user),
            'user'  => $this->userToArray($user),
        ]);
    }

    // POST /api/auth/register
    public function register(array $vars = []): void
    {
        $body = $this->getBody();

        try {
            $userId = $this->userService->register(
                trim($body['username'] ?? ''),
                trim($body['email']    ?? ''),
                $body['password'] ?? '',
                trim($body['bio']      ?? '')
            );
        } catch (\InvalidArgumentException $e) {
            $this->json(['error' => $e->getMessage()], 400);
        }

        $user = $this->userService->getUserById($userId);

        $this->json([
            'token' => $this->generateToken($user),
            'user'  => $this->userToArray($user),
        ], 201);
    }

    // GET /api/auth/me 
    public function me(array $vars = []): void
    {
        $tokenData = $this->validateJWT();
        $user      = $this->userService->getUserById($tokenData->id);

        if (!$user) {
            $this->json(['error' => 'User not found.'], 404);
        }

        $this->json($this->userToArray($user));
    }

    private function generateToken(User $user): string
    {
        $now = time();

        return JwtHelper::encode([
            'iat'  => $now,
            'exp'  => $now + Config::JWT_EXPIRES_IN,
            'data' => [
                'id'       => $user->userId,
                'username' => $user->username,
                'role'     => $user->role,
            ],
        ]);
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