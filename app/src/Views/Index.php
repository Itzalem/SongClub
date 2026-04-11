<?php $pageTitle = 'SongClub — Songs'; ?>
<?php require __DIR__ . '/Partials/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="sc-section-title mb-0">Songs</h2>
    <?php if (isset($_SESSION['user_id'])): ?>
        <a href="/songs/create" class="btn btn-sc-primary">+ Add a song</a>
    <?php endif; ?>
</div>

<?php if (empty($songs)): ?>
    <p class="text-muted">
        No songs yet.
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="/songs/create">Add the first one!</a>
        <?php endif; ?>
    </p>
<?php else: ?>
    <div class="row g-3">
        <?php foreach ($songs as $song): ?>
        <div class="col-12 col-sm-6 col-md-4">
            <article class="song-card h-100">
                <div class="card-body d-flex flex-column">

                    <?php if (!empty($song->genre)): ?>
                        <span class="badge-genre mb-2 align-self-start">
                            <?= htmlspecialchars($song->genre) ?>
                        </span>
                    <?php endif; ?>

                    <p class="song-title mb-1">
                        <a href="/songs/<?= (int) $song->id ?>" style="color:inherit;text-decoration:none">
                            <?= htmlspecialchars($song->title) ?>
                        </a>
                    </p>
                    <p class="song-artist"><?= htmlspecialchars($song->artist) ?></p>

                    <?php if (!empty($song->link)): ?>
                        <a href="<?= htmlspecialchars($song->link) ?>" target="_blank" rel="noopener noreferrer"
                           class="btn btn-sm btn-sc-outline mt-auto mb-2">▶ Listen</a>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="d-flex gap-2 mt-auto">
                        <button class="btn btn-like btn-sm <?= in_array($song->id, $likedIds) ? 'active' : '' ?>"
                                data-song-id="<?= (int) $song->id ?>">♥</button>
                        <button class="btn btn-fav btn-sm <?= in_array($song->id, $favIds) ? 'active' : '' ?>"
                                data-song-id="<?= (int) $song->id ?>">★</button>
                    </div>
                    <?php endif; ?>

                </div>
            </article>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if (!isset($_SESSION['user_id'])): ?>
    <div class="mt-4 text-center">
        <p class="text-muted">Join SongClub to like songs and build your profile.</p>
        <a href="/register" class="btn btn-sc-primary">Sign up free</a>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/Partials/footer.php'; ?>
