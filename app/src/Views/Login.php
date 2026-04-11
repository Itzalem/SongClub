<?php $pageTitle = 'Bienvenido de nuevo — SongClub'; ?>
<?php require __DIR__ . '/Partials/header.php'; ?>

<div class="row justify-content-center align-items-center" style="min-height: 70vh;">
    <div class="col-md-5">
        <div class="card border-0 shadow-lg p-4 p-md-5 rounded-5 bg-white">
            <div class="text-center mb-4">
                <span class="fs-1">👋</span>
                <h2 class="fw-extrabold mt-3">Iniciar Sesión</h2>
                <p class="text-muted">Qué bueno verte de nuevo por aquí.</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger border-0 shadow-sm rounded-4 small mb-4"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="/login">
                <div class="mb-3">
                    <label for="email" class="form-label ps-2">Email</label>
                    <input type="email" class="form-control bg-light border-0 shadow-sm px-4" id="email" name="email" placeholder="tu@email.com" required>
                </div>
                <div class="mb-4">
                    <label for="password" class="form-label ps-2">Contraseña</label>
                    <input type="password" class="form-control bg-light border-0 shadow-sm px-4" id="password" name="password" placeholder="••••••••" required>
                </div>
                <button type="submit" class="btn btn-sc-primary w-100 py-3 shadow">Entrar a mi cuenta</button>
            </form>

            <p class="mt-4 text-center text-muted small">
                ¿Aún no eres miembro? <a href="/register" class="text-olive fw-bold text-decoration-none">Regístrate gratis</a>
            </p>
        </div>
    </div>
</div>

<?php require __DIR__ . '/Partials/footer.php'; ?>