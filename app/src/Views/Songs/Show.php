<?php $pageTitle = htmlspecialchars($song->title) . ' — SongClub'; ?>
<?php require __DIR__ . '/../Partials/header.php'; ?>

<div class="container">
    <a href="/songs" class="back-link mb-4 d-inline-block">← Volver al catálogo</a>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-0 shadow-2xl rounded-5 overflow-hidden">
                <div class="card-body p-5">
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <span class="badge-genre"><?= htmlspecialchars($song->genre ?? 'Música') ?></span>
                        <?php if (!empty($song->link)): ?>
                            <a href="<?= htmlspecialchars($song->link) ?>" target="_blank" class="btn btn-sc-outline btn-sm">Abrir en Spotify/YT</a>
                        <?php endif; ?>
                    </div>

                    <h1 class="display-4 fw-extrabold text-olive mb-2"><?= htmlspecialchars($song->title) ?></h1>
                    <p class="fs-3 text-muted mb-4"><?= htmlspecialchars($song->artist) ?></p>

                    <?php if (!empty($song->album)): ?>
                        <div class="d-flex align-items-center mb-4 text-muted">
                            <span class="me-2">💿</span>
                            <span class="fw-medium">Álbum: <?= htmlspecialchars($song->album) ?></span>
                        </div>
                    <?php endif; ?>

                    <div class="p-4 bg-sc-olive-soft rounded-4 mb-5 border-start border-4 border-accent">
                        <p class="mb-0 small text-muted">Añadida por <strong><?= htmlspecialchars($song->creator_name ?? 'Sistema') ?></strong></p>
                    </div>

                    <?php if (isset($_SESSION['user_id'])): ?>
                        <div class="d-flex gap-3">
                            <button class="btn btn-like btn-lg flex-grow-1 <?= $isLiked ? 'active' : '' ?>" data-song-id="<?= (int) $song->id ?>">
                                ❤️ Like <span class="like-count badge bg-white text-danger ms-2"><?= (int) $likeCount ?></span>
                            </button>
                            <button class="btn btn-fav btn-lg flex-grow-1 <?= $isFav ? 'active' : '' ?>" data-song-id="<?= (int) $song->id ?>">
                                ★ Favorito
                            </button>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['user_id']) && ((int)$song->created_by === (int)$_SESSION['user_id'] || $_SESSION['role'] === 'admin')): ?>
                        <div class="mt-5 pt-4 border-top d-flex gap-2">
                            <a href="/songs/<?= (int) $song->id ?>/edit" class="btn btn-light border btn-sm px-4">Editar</a>
                            <form method="POST" action="/songs/<?= (int) $song->id ?>/delete" onsubmit="return confirm('¿Seguro que quieres borrar esta canción?')">
                                <button class="btn btn-danger btn-sm px-4 opacity-75">Eliminar</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../Partials/footer.php'; ?>