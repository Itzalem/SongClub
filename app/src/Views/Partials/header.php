<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'SongClub') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/Assets/css/main.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand fw-bold" href="/">SongClub</a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="/">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="/songs">Songs</a></li>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <li class="nav-item"><a class="nav-link" href="/admin/users">Admin</a></li>
                <?php endif; ?>
            </ul>

            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="me-3 position-relative">
                    <input type="search" id="user-search" class="form-control form-control-sm"
                           placeholder="Search users...">
                    <div id="search-results" class="position-absolute bg-white shadow rounded w-100"
                         style="z-index:100; top:100%"></div>
                </div>
            <?php endif; ?>

            <ul class="navbar-nav">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/profile/<?= (int)$_SESSION['user_id'] ?>">
                            <?= htmlspecialchars($_SESSION['username']) ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <form method="POST" action="/logout">
                            <button class="btn btn-sm btn-outline-light ms-2">Log out</button>
                        </form>
                    </li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="/login">Log in</a></li>
                    <li class="nav-item"><a class="nav-link" href="/register">Sign up</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<main class="container my-4">
