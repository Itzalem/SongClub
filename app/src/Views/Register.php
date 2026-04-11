<?php $pageTitle = 'Sign up'; ?>
<?php require __DIR__ . '/Partials/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-5">
        <h2 class="mb-4">Create account</h2>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="/register">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username"
                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="mb-3">
                <label for="bio" class="form-label">Bio (optional)</label>
                <textarea class="form-control" id="bio" name="bio" rows="2"></textarea>
            </div>
            <button type="submit" class="btn btn-primary w-100">Create account</button>
        </form>

        <p class="mt-3">Already have an account? <a href="/login">Log in</a></p>
    </div>
</div>

<?php require __DIR__ . '/Partials/footer.php'; ?>
