<?php $pageTitle = htmlspecialchars($song->title) . ' — SongClub'; ?>
<?php require __DIR__ . '/../partials/header.php'; ?>

<a href="/songs" style="color:var(--sc-olive);font-size:0.9rem">&larr; Back to songs</a>

<div class="song-card mt-3" style="max-width:580px">
    <div class="card-body">

        <?php if (!empty($song->genre)): ?>
            <span class="badge-genre mb-3 d-inline-block"><?= htmlspecialchars($song->genre) ?></span>
        <?php endif; ?>

        <h2 style="font-weight:800"><?= htmlspecialchars($song->title) ?></h2>
        <p class="song-artist fs-6 mb-1"><?= htmlspecialchars($song->artist) ?></p>

        <?php if (!empty($song->album)): ?>
            <p class="text-muted" style="font-size:0.9rem">Album: <?= htmlspecialchars($song->album) ?></p>
        <?php endif; ?>

        <?php if (!empty($song->creator_name)): ?>
            <p class="text-muted" style="font-size:0.85rem">
                Added by <?= htmlspecialchars($song->creator_name) ?>
            </p>
        <?php endif; ?>

        <?php if (!empty($song->link)): ?>
            <a href="<?= htmlspecialchars($song->link) ?>" target="_blank" rel="noopener noreferrer"
               class="btn btn-sc-outline mt-2 me-2">▶ Listen externally</a>
        <?php endif; ?>

        <?php if (isset($_SESSION['user_id'])): ?>
        <div class="d-flex gap-2 mt-3">
            <button class="btn btn-like <?= $isLiked ? 'active' : '' ?>"
                    data-song-id="<?= (int) $song->id ?>">
                ♥ Like <span class="like-count"><?= (int) $likeCount ?></span>
            </button>
            <button class="btn btn-fav <?= $isFav ? 'active' : '' ?>"
                    data-song-id="<?= (int) $song->id ?>">★ Favourite</button>
        </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['user_id']) && ((int)$song->created_by === (int)$_SESSION['user_id'] || $_SESSION['role'] === 'admin')): ?>
        <div class="d-flex gap-2 mt-4">
            <a href="/songs/<?= (int) $song->id ?>/edit" class="btn btn-sc-primary btn-sm">Edit</a>
            <form method="POST" action="/songs/<?= (int) $song->id ?>/delete"
                  onsubmit="return confirm('Delete this song?')">
                <button class="btn btn-sm btn-danger">Delete</button>
            </form>
        </div>
        <?php endif; ?>

    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
