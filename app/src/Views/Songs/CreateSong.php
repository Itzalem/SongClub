<?php $pageTitle = 'Add a Song — SongClub'; ?>
<?php require __DIR__ . '/../Partials/header.php'; ?>

<a href="/songs" style="color:var(--sc-olive);font-size:0.9rem">&larr; Back to songs</a>

<div class="auth-card mt-3">
    <h2 style="font-weight:800;margin-bottom:0.25rem">Add a Song</h2>
    <p style="color:var(--sc-text-muted);font-size:0.9rem;margin-bottom:1.5rem">Share a song with the community.</p>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="/songs/create">
        <div class="mb-3">
            <label for="title" class="form-label">Title *</label>
            <input type="text" id="title" name="title" class="form-control"
                   value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" required>
        </div>
        <div class="mb-3">
            <label for="artist" class="form-label">Artist *</label>
            <input type="text" id="artist" name="artist" class="form-control"
                   value="<?= htmlspecialchars($_POST['artist'] ?? '') ?>" required>
        </div>
        <div class="mb-3">
            <label for="album" class="form-label">
                Album <span style="font-weight:400;color:var(--sc-text-muted)">(optional)</span>
            </label>
            <input type="text" id="album" name="album" class="form-control"
                   value="<?= htmlspecialchars($_POST['album'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label for="genre" class="form-label">
                Genre <span style="font-weight:400;color:var(--sc-text-muted)">(optional)</span>
            </label>
            <input type="text" id="genre" name="genre" class="form-control"
                   placeholder="e.g. Pop, Rock, Jazz…"
                   value="<?= htmlspecialchars($_POST['genre'] ?? '') ?>">
        </div>
        <div class="mb-4">
            <label for="link" class="form-label">
                Link <span style="font-weight:400;color:var(--sc-text-muted)">(optional)</span>
            </label>
            <input type="url" id="link" name="link" class="form-control"
                   placeholder="https://open.spotify.com/…"
                   value="<?= htmlspecialchars($_POST['link'] ?? '') ?>">
        </div>
        <button type="submit" class="btn btn-sc-primary w-100">Add song</button>
    </form>
</div>

<?php require __DIR__ . '/../Partials/footer.php'; ?>
