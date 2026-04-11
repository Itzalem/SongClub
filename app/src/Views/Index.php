<?php $pageTitle = 'Descubrir — SongClub'; ?>
<?php require __DIR__ . '/Partials/header.php'; ?>

<div class="d-flex justify-content-between align-items-end mb-5">
    <div>
        <h2 class="fw-extrabold fs-1 mb-1">Explorar</h2>
        <p class="text-muted">Descubre las últimas joyas añadidas por la comunidad.</p>
    </div>
    <?php if (isset($_SESSION['user_id'])): ?>
        <a href="/songs/create" class="btn btn-sc-primary">+ Añadir canción</a>
    <?php endif; ?>
</div>

<div class="row g-4">
    <?php foreach ($songs as $song): ?>
    <div class="col-12 col-md-6 col-lg-4">
        <article class="post-card h-100 d-flex flex-column p-4">
            <div class="mb-3">
                <span class="badge-genre"><?= htmlspecialchars($song->genre ?? 'Música') ?></span>
            </div>
            
            <h4 class="fw-bold mb-1">
                <a href="/songs/<?= (int)$song->id ?>" class="text-decoration-none text-dark hover-olive">
                    <?= htmlspecialchars($song->title) ?>
                </a>
            </h4>
            <p class="fs-5 text-muted mb-4"><?= htmlspecialchars($song->artist) ?></p>

            <div class="mt-auto d-flex justify-content-between align-items-center">
                <?php if (!empty($song->link)): ?>
                    <a href="<?= htmlspecialchars($song->link) ?>" target="_blank" class="btn btn-sc-outline btn-sm px-4">▶ Escuchar</a>
                <?php endif; ?>

                <?php if (isset($_SESSION['user_id'])): ?>
                <div class="btn-group shadow-sm rounded-pill">
                    <button class="btn btn-like btn-sm <?= in_array($song->id, $likedIds) ? 'active' : '' ?>" data-song-id="<?= (int)$song->id ?>">♥</button>
                    <button class="btn btn-fav btn-sm <?= in_array($song->id, $favIds) ? 'active' : '' ?>" data-song-id="<?= (int)$song->id ?>">★</button>
                </div>
                <?php endif; ?>
            </div>
        </article>
    </div>
    <?php endforeach; ?>
</div>