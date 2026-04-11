<?php $pageTitle = 'SongClub — Where songs connect people'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/Assets/css/main.css">
</head>
<body class="landing-body">

<div class="landing-card text-center shadow-2xl">
    <div class="landing-logo mb-3">SongClub</div>
    <p class="fs-4 opacity-75 mb-5">Comparte lo que escuchas. Descubre tu próxima canción favorita.</p>

    <div class="d-grid gap-3 d-sm-flex justify-content-sm-center">
        <a href="/register" class="btn btn-sc-landing btn-lg px-5">Empezar ahora — Es gratis</a>
    </div>

    <p class="mt-4 opacity-50">
        ¿Ya tienes cuenta? <a href="/login" class="text-white fw-bold text-decoration-none">Inicia sesión</a>
    </p>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>