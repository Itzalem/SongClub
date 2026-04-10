<?php $pageTitle = 'SongClub'; ?>
<?php require __DIR__ . '/../partials/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Songs</h1>
    <?php if (isset($_SESSION['user_id'])): ?>
        <a href="/songs/create" class="btn btn-primary">+ Add song</a>
    <?php else: ?>
        <a href="/login" class="btn btn-outline-primary">Log in to add songs</a>
    <?php endif; ?>
</div>

<?php if (empty($songs)): ?>
    <p class="text-muted">No songs yet.</p>
<?php else: ?>
    <div class="row g-3">
        <?php foreach ($songs as $song): ?>
        <div class="col-12 col-sm-6 col-md-4">
            <div class="card h-100 song-card">
                <div class="card-body">
                    <h5 class="card-title">
                        <a href="/songs/<?= (int)$song->id ?>" class="text-decoration-none text-dark">
                            <?= htmlspecialchars($song->title) ?>
                        </a>
                    </h5>
                    <p class="card-text text-muted"><?= htmlspecialchars($song->artist) ?></p>

                    <?php if ($song->genre): ?>
                        <span class="badge bg-secondary"><?= htmlspecialchars($song->genre) ?></span>
                    <?php endif; ?>

                    <?php if ($song->link): ?>
                        <div class="mt-2">
                            <a href="<?= htmlspecialchars($song->link) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                Listen ↗
                            </a>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="mt-2 d-flex gap-2">
                        <button class="btn btn-sm btn-outline-danger btn-like"
                                data-song-id="<?= (int)$song->id ?>">
                            ♥ <span class="like-count">0</span>
                        </button>
                        <button class="btn btn-sm btn-outline-success btn-fav"
                                data-song-id="<?= (int)$song->id ?>">
                            ★ Fav
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../partials/footer.php'; ?>
