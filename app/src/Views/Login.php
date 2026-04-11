<?php $pageTitle = 'Log in'; ?>
<?php require __DIR__ . '/Partials/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-5">
        <h2 class="mb-4">Log in</h2>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="/login">
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Log in</button>
        </form>

        <p class="mt-3">Don't have an account? <a href="/register">Sign up</a></p>
    </div>
</div>

<?php require __DIR__ . '/Partials/footer.php'; ?>
