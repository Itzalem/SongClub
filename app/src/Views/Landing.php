<?php $pageTitle = 'SongClub — Where songs connect people'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/Assets/css/main.css">
</head>
<body class="landing-body">

<div class="landing-wrapper">
    <div class="landing-card text-center">

        <div class="landing-logo">SongClub</div>
        <p class="landing-tagline">Where songs connect people.</p>

        <a href="/login" class="btn btn-sc-landing btn-lg px-5 mb-3">Start exploring</a>

        <p class="landing-sub">
            No account yet? <a href="/register" class="landing-link">Sign up free</a>
        </p>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
