<?php

session_start();

require __DIR__ . '/../vendor/autoload.php';

use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;

$dispatcher = simpleDispatcher(function (RouteCollector $r) {
    // Landing page
    $r->addRoute('GET', '/', ['App\Controllers\AccountController', 'landing']);

    // Auth
    $r->addRoute(['GET', 'POST'], '/login',    ['App\Controllers\AccountController', 'login']);
    $r->addRoute(['GET', 'POST'], '/register', ['App\Controllers\AccountController', 'register']);
    $r->addRoute('POST',          '/logout',   ['App\Controllers\AccountController', 'logout']);

    // Songs
    $r->addRoute('GET',           '/songs',                    ['App\Controllers\SongController', 'index']);
    $r->addRoute(['GET', 'POST'], '/songs/create',             ['App\Controllers\SongController', 'create']);
    $r->addRoute('GET',           '/songs/{id:\d+}',           ['App\Controllers\SongController', 'show']);
    $r->addRoute(['GET', 'POST'], '/songs/{id:\d+}/edit',      ['App\Controllers\SongController', 'edit']);
    $r->addRoute('POST',          '/songs/{id:\d+}/delete',    ['App\Controllers\SongController', 'delete']);

    // Profile — home/feed view
    $r->addRoute('GET',  '/profile/{id:\d+}',           ['App\Controllers\ProfileController', 'show']);
    $r->addRoute('POST', '/profile/update',              ['App\Controllers\ProfileController', 'update']);

    // Profile — edit (must be defined before /profile/{id:\d+} even though regex excludes 'edit')
    $r->addRoute('GET',  '/profile/edit',                ['App\Controllers\ProfileController', 'editForm']);
    $r->addRoute('POST', '/profile/edit',                ['App\Controllers\ProfileController', 'editSave']);

    // User profile — read-only view (accessible via nav username link)
    $r->addRoute('GET', '/user/{id:\d+}', ['App\Controllers\ProfileController', 'userView']);

    // Favorites & Liked lists
    $r->addRoute('GET', '/profile/{id:\d+}/favorites', ['App\Controllers\FavoriteController', 'userFavorites']);
    $r->addRoute('GET', '/profile/{id:\d+}/liked',     ['App\Controllers\FavoriteController', 'userLiked']);

    // Last listened post
    $r->addRoute('POST', '/last-listened/set', ['App\Controllers\PostController', 'set']);

    // AJAX interactions
    $r->addRoute('POST', '/favorites/toggle', ['App\Controllers\FavoriteController', 'toggle']);
    $r->addRoute('POST', '/likes/toggle',     ['App\Controllers\FavoriteController', 'toggleLike']);
    $r->addRoute('POST', '/comments/store',   ['App\Controllers\CommentController', 'store']);

    // Admin
    $r->addRoute('GET',  '/admin/users',                  ['App\Controllers\UserController', 'index']);
    $r->addRoute('POST', '/admin/users/{id:\d+}/delete',  ['App\Controllers\UserController', 'delete']);

    // API (JSON)
    $r->addRoute('GET', '/api/songs',                    ['App\Controllers\SongController',     'apiIndex']);
    $r->addRoute('GET', '/api/favorites/{userId:\d+}',   ['App\Controllers\FavoriteController', 'export']);
    $r->addRoute('GET', '/api/users/search',             ['App\Controllers\UserController',     'search']);
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
