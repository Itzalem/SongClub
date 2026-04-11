<?php $pageTitle = 'Mis Joyas — SongClub'; ?>
<?php require __DIR__ . '/../Partials/header.php'; ?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-5">
    <div>
        <h2 class="fw-extrabold fs-1 mb-1">Favoritos ★</h2>
        <p class="text-muted">Las canciones que más te inspiran.</p>
    </div>
    <div class="d-flex gap-2 mt-3 mt-md-0">
        <a href="/api/favorites/<?= (int)$profileUser->userId ?>" class="btn btn-sc-outline btn-sm">Exportar JSON</a>
        <?php if ($isOwner): ?>
            <a href="/songs/create" class="btn btn-sc-primary btn-sm">+ Nueva</a>
        <?php endif; ?>
    </div>
</div>

<div class="song-list">
    <?php foreach ($songs as $song): ?>
    <div class="song-list-item p-3 mb-2 shadow-sm bg-white rounded-4 border-0 d-flex align-items-center">
        <div class="flex-grow-1">
            <h6 class="fw-bold mb-0 text-olive"><?= htmlspecialchars($song->title) ?></h6>
            <small class="text-muted"><?= htmlspecialchars($song->artist) ?></small>
        </div>
        <div class="d-flex gap-2">
            <?php if ($song->link): ?>
                <a href="<?= htmlspecialchars($song->link) ?>" target="_blank" class="btn btn-light btn-sm rounded-circle shadow-sm">▶</a>
            <?php endif; ?>
            <button class="btn btn-fav active btn-sm rounded-circle shadow-sm" data-song-id="<?= (int)$song->id ?>">★</button>
        </div>
    </div>
    <?php endforeach; ?>
    <?php if (empty($songs)): ?>
        <div class="text-center py-5 opacity-25">
            <span class="display-1">🌑</span>
            <p class="fs-4 mt-3">Aún no hay favoritos aquí.</p>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../Partials/footer.php'; ?>