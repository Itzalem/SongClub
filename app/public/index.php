<?php

session_start();

require __DIR__ . '/../vendor/autoload.php';

use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;

// CORS — allow all origins (Vue dev server port can vary)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Browsers send OPTIONS preflight before actual request — respond and stop
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$dispatcher = simpleDispatcher(function (RouteCollector $r) {
    // Landing page
    $r->addRoute('GET', '/', ['App\Controllers\AccountController', 'landing']);

    // Auth (PHP session-based — kept for existing PHP views)
    $r->addRoute(['GET', 'POST'], '/login',    ['App\Controllers\AccountController', 'login']);
    $r->addRoute(['GET', 'POST'], '/register', ['App\Controllers\AccountController', 'register']);
    $r->addRoute('POST',          '/logout',   ['App\Controllers\AccountController', 'logout']);

    // Songs
    $r->addRoute('GET',           '/songs',                 ['App\Controllers\SongController', 'index']);
    $r->addRoute(['GET', 'POST'], '/songs/create',          ['App\Controllers\SongController', 'create']);
    $r->addRoute('GET',           '/songs/{id:\d+}',        ['App\Controllers\SongController', 'show']);
    $r->addRoute(['GET', 'POST'], '/songs/{id:\d+}/edit',   ['App\Controllers\SongController', 'edit']);
    $r->addRoute('POST',          '/songs/{id:\d+}/delete', ['App\Controllers\SongController', 'delete']);

    // Profile
    $r->addRoute('GET',  '/profile/{id:\d+}', ['App\Controllers\ProfileController', 'show']);
    $r->addRoute('POST', '/profile/update',   ['App\Controllers\ProfileController', 'update']);
    $r->addRoute('GET',  '/profile/edit',     ['App\Controllers\ProfileController', 'editForm']);
    $r->addRoute('POST', '/profile/edit',     ['App\Controllers\ProfileController', 'editSave']);
    $r->addRoute('GET',  '/user/{id:\d+}',    ['App\Controllers\ProfileController', 'userView']);

    // Favorites & Liked lists
    $r->addRoute('GET', '/profile/{id:\d+}/favorites', ['App\Controllers\FavoriteController', 'userFavorites']);
    $r->addRoute('GET', '/profile/{id:\d+}/liked',     ['App\Controllers\FavoriteController', 'userLiked']);

    // Last listened post
    $r->addRoute('POST', '/last-listened/set', ['App\Controllers\PostController', 'set']);

    // AJAX interactions (PHP view layer)
    $r->addRoute('POST', '/favorites/toggle', ['App\Controllers\FavoriteController', 'toggle']);
    $r->addRoute('POST', '/likes/toggle',     ['App\Controllers\FavoriteController', 'toggleLike']);
    $r->addRoute('POST', '/comments/store',   ['App\Controllers\CommentController', 'store']);

    // Admin
    $r->addRoute('GET',  '/admin/users',                 ['App\Controllers\UserController', 'index']);
    $r->addRoute('POST', '/admin/users/{id:\d+}/delete', ['App\Controllers\UserController', 'delete']);

    // ── REST API ────────────────────────────────────────────────────────────

    // Auth (JWT-based — for Vue frontend)
    $r->addRoute('POST', '/api/auth/login',    ['App\Controllers\AuthController', 'login']);
    $r->addRoute('POST', '/api/auth/register', ['App\Controllers\AuthController', 'register']);
    $r->addRoute('GET',  '/api/auth/me',       ['App\Controllers\AuthController', 'me']);

    // Songs REST API
    $r->addRoute('GET',    '/api/songs',          ['App\Controllers\SongController', 'apiList']);
    $r->addRoute('POST',   '/api/songs',          ['App\Controllers\SongController', 'apiCreate']);
    $r->addRoute('GET',    '/api/songs/{id:\d+}', ['App\Controllers\SongController', 'apiShow']);
    $r->addRoute('PUT',    '/api/songs/{id:\d+}', ['App\Controllers\SongController', 'apiUpdate']);
    $r->addRoute('DELETE', '/api/songs/{id:\d+}', ['App\Controllers\SongController', 'apiDelete']);

    // Social feed
    $r->addRoute('GET',  '/api/feed',                       ['App\Controllers\PostController', 'apiFeed']);
    $r->addRoute('POST', '/api/posts',                      ['App\Controllers\PostController', 'apiSet']);
    $r->addRoute('GET',  '/api/posts/{id:\d+}/comments',    ['App\Controllers\PostController', 'apiGetComments']);
    $r->addRoute('POST', '/api/posts/{id:\d+}/comments',    ['App\Controllers\PostController', 'apiAddComment']);

    // User favorites & likes
    $r->addRoute('GET',  '/api/users/{id:\d+}/favorites', ['App\Controllers\FavoriteController', 'apiFavorites']);
    $r->addRoute('GET',  '/api/users/{id:\d+}/liked',     ['App\Controllers\FavoriteController', 'apiLiked']);
    $r->addRoute('POST', '/api/songs/{id:\d+}/favorite',  ['App\Controllers\FavoriteController', 'apiToggleFavorite']);
    $r->addRoute('POST', '/api/songs/{id:\d+}/like',      ['App\Controllers\FavoriteController', 'apiToggleLike']);

    // User profile & admin
    $r->addRoute('GET',    '/api/users/search',          ['App\Controllers\UserController', 'search']);
    $r->addRoute('GET',    '/api/users/{id:\d+}',        ['App\Controllers\UserController', 'apiShow']);
    $r->addRoute('GET',    '/api/admin/users',           ['App\Controllers\UserController', 'apiAdminList']);
    $r->addRoute('DELETE', '/api/admin/users/{id:\d+}',  ['App\Controllers\UserController', 'apiAdminDelete']);

    // Legacy export
    $r->addRoute('GET', '/api/favorites/{userId:\d+}',   ['App\Controllers\FavoriteController', 'export']);
});

$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri        = strtok($_SERVER['REQUEST_URI'], '?');
$routeInfo  = $dispatcher->dispatch($httpMethod, $uri);

switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        http_response_code(404);
        echo 'Not Found';
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        http_response_code(405);
        echo 'Method Not Allowed';
        break;
    case FastRoute\Dispatcher::FOUND:
        $class      = $routeInfo[1][0];
        $method     = $routeInfo[1][1];
        $controller = new $class();
        $vars       = $routeInfo[2];
        $controller->$method($vars);
        break;
}