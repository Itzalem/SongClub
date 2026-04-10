<?php

namespace app\Controllers;

use app\Repositories\UserRepository;

namespace App\Controllers;

use App\Framework\Controller;
use App\Services\UserService;
use App\Services\SongService;
use App\ViewModels\ProfileViewModel;
use App\ViewModels\ProfileVm;
use App\Models\User;

class UserController extends Controller {
    
    private $userService;
    private $songService;

    public function __construct(UserService $userService, SongService $songService) {
        $this->userService = $userService;
        $this->songService = $songService;
    }

    public function show($profileId) {
        // 1. ¿Quién está logueado? (De la sesión)
        $currentUserId = $_SESSION['user_id'] ?? null;

        // 2. Cargamos los datos básicos del dueño del perfil
        $user = $this->userService->getUserById($profileId);
        if (!$user) {
            $this->render('404'); // Si el usuario no existe
            return;
        }

        // 3. Creamos el ViewModel
        $vm = new ProfileVm();
        $vm->user = $user;
        $vm->isOwner = ($currentUserId == $profileId);

        // 4. Pedimos las canciones al Service
        // El Service se encargará de usar los Repositories con los JOINs que vimos
        $vm->favorites = $this->songService->getFavorites($profileId);
        $vm->lastSong = $this->songService->getLastListened($profileId);
        $vm->comments = $this->songService->getCommentsForPost($vm->lastSong->id ?? 0);

        // 5. PRIVACIDAD: Solo cargamos los likes si es el dueño
        if ($vm->isOwner) {
            $vm->likes = $this->songService->getLikes($profileId);
        } else {
            $vm->likes = []; // Vacío para otros usuarios
        }

        // 6. ¡A la vista!
        $this->render('UserProfile', ['vm' => $vm]);
    }

    public function update(array $vars = []): void
    {
        $this->requireAuth();

        $username = trim($_POST['username'] ?? '');
        $bio      = trim($_POST['bio']      ?? '');

        if ($username === '') {
            header('Location: /profile/' . (int) $_SESSION['user_id']);
            exit;
        }

        $userService = new UserService(new UserRepository());
        $user        = $userService->getUserById((int) $_SESSION['user_id']);

        if ($user) {
            $user->username       = $username;
            $user->bio            = $bio ?: null;
            $userService->updateUser($user);
            $_SESSION['username'] = $username;
        }

        header('Location: /profile/' . (int) $_SESSION['user_id']);
        exit;
    }

}