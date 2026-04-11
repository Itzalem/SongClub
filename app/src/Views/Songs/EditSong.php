<?php $pageTitle = 'Edit Song — SongClub'; ?>
<?php require __DIR__ . '/../Partials/header.php'; ?>

<a href="/songs/<?= (int) $song->id ?>" style="color:var(--sc-olive);font-size:0.9rem">&larr; Back</a>

<div class="auth-card mt-3">
    <h2 style="font-weight:800;margin-bottom:0.25rem">Edit Song</h2>
    <p style="color:var(--sc-text-muted);font-size:0.9rem;margin-bottom:1.5rem">Update the song details.</p>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="/songs/<?= (int) $song->id ?>/edit">
        <div class="mb-3">
            <label for="title" class="form-label">Title *</label>
            <input type="text" id="title" name="title" class="form-control"
                   value="<?= htmlspecialchars($_POST['title'] ?? $song->title) ?>" required>
        </div>
        <div class="mb-3">
            <label for="artist" class="form-label">Artist *</label>
            <input type="text" id="artist" name="artist" class="form-control"
                   value="<?= htmlspecialchars($_POST['artist'] ?? $song->artist) ?>" required>
        </div>
        <div class="mb-3">
            <label for="album" class="form-label">
                Album <span style="font-weight:400;color:var(--sc-text-muted)">(optional)</span>
            </label>
            <input type="text" id="album" name="album" class="form-control"
                   value="<?= htmlspecialchars($_POST['album'] ?? $song->album ?? '') ?>">
        </div>
        <div class="mb-3">
            <label for="genre" class="form-label">
                Genre <span style="font-weight:400;color:var(--sc-text-muted)">(optional)</span>
            </label>
            <input type="text" id="genre" name="genre" class="form-control"
                   value="<?= htmlspecialchars($_POST['genre'] ?? $song->genre ?? '') ?>">
        </div>
        <div class="mb-4">
            <label for="link" class="form-label">
                Link <span style="font-weight:400;color:var(--sc-text-muted)">(optional)</span>
            </label>
            <input type="url" id="link" name="link" class="form-control"
                   value="<?= htmlspecialchars($_POST['link'] ?? $song->link ?? '') ?>">
        </div>
        <button type="submit" class="btn btn-sc-primary w-100">Save changes</button>
    </form>
</div>

<?php require __DIR__ . '/../Partials/footer.php'; ?>
