<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'SongClub') ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/Assets/css/main.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark sticky-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="/">
            <span class="fs-3 me-2">🎧</span>
            <span class="fw-bold">SongClub</span>
        </a>

        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav mx-auto">
                <li class="nav-item"><a class="nav-link px-3" href="/songs">Descubrir</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item"><a class="nav-link px-3" href="/profile/<?= (int)$_SESSION['user_id'] ?>">Mi Feed</a></li>
                <?php endif; ?>
            </ul>

            <div class="d-flex align-items-center gap-3">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="search-box position-relative d-none d-md-block">
                        <input type="search" id="user-search" class="form-control form-control-sm bg-light border-0" 
                               placeholder="Buscar melómanos..." style="width: 200px;">
                        <div id="search-results" class="position-absolute shadow-lg rounded-3 w-100" style="top: 45px;"></div>
                    </div>
                    
                    <div class="dropdown">
                        <a class="nav-link dropdown-toggle fw-bold text-white" href="#" data-bs-toggle="dropdown">
                            <?= htmlspecialchars($_SESSION['username']) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-3 rounded-3">
                            <li><a class="dropdown-item py-2" href="/user/<?= (int)$_SESSION['user_id'] ?>">Perfil y Ajustes</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="/logout" class="px-2">
                                    <button class="btn btn-danger btn-sm w-100 rounded-2">Salir</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="/login" class="btn btn-outline-light btn-sm px-4 rounded-pill">Entrar</a>
                    <a href="/register" class="btn btn-sc-primary btn-sm px-4 rounded-pill">Unirse</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<main class="container my-5">