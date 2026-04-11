<?php
$pageTitle = $isOwner
    ? 'My Favorite Songs — SongClub'
    : htmlspecialchars($profileUser->username) . "'s Favorites — SongClub";
?>
<?php require __DIR__ . '/../Partials/header.php'; ?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <a href="/profile/<?= (int)$profileUser->userId ?>"
           class="back-link">&larr; Back to profile</a>
        <h2 class="sc-section-title mb-0 mt-2">
            <?= $isOwner ? 'My Favorite Songs' : htmlspecialchars($profileUser->username) . "'s Favorites" ?>
        </h2>
    </div>
    <?php if ($isOwner): ?>
        <a href="/songs/create" class="btn btn-sc-primary">+ Add a new song</a>
    <?php endif; ?>
</div>

<?php if (empty($songs)): ?>
    <p class="text-muted">
        <?= $isOwner ? 'You haven\'t added any favorites yet.' : 'No favorites yet.' ?>
    </p>
<?php else: ?>
    <div class="song-list">
        <?php foreach ($songs as $i => $song): ?>
        <div class="song-list-item">

            <!-- Collapsed row -->
            <div class="song-list-row"
                 data-bs-toggle="collapse"
                 data-bs-target="#song-details-<?= (int)$song->id ?>-<?= $i ?>"
                 style="cursor:pointer">
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <div class="song-list-info flex-grow-1">
                        <span class="song-list-title"><?= htmlspecialchars($song->title) ?></span>
                        <span class="song-list-sep">—</span>
                        <span class="song-list-artist"><?= htmlspecialchars($song->artist) ?></span>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-shrink-0">
                        <?php if (!empty($song->link)): ?>
                            <a href="<?= htmlspecialchars($song->link) ?>"
                               target="_blank" rel="noopener noreferrer"
                               class="btn btn-sc-outline btn-sm"
                               onclick="event.stopPropagation()">▶ Listen</a>
                        <?php endif; ?>
                        <?php if (!$isOwner && isset($_SESSION['user_id'])): ?>
                            <button class="btn btn-like btn-sm <?= in_array($song->id, $viewerLikedIds) ? 'active' : '' ?>"
                                    data-song-id="<?= (int)$song->id ?>"
                                    onclick="event.stopPropagation()">♥</button>
                            <button class="btn btn-fav btn-sm <?= in_array($song->id, $viewerFavIds) ? 'active' : '' ?>"
                                    data-song-id="<?= (int)$song->id ?>"
                                    onclick="event.stopPropagation()">★</button>
                        <?php endif; ?>
                        <span class="song-list-chevron">›</span>
                    </div>
                </div>
            </div>

            <!-- Expanded details -->
            <div class="collapse song-list-details"
                 id="song-details-<?= (int)$song->id ?>-<?= $i ?>">
                <div class="song-list-body">
                    <?php if (!empty($song->genre)): ?>
                        <span class="badge-genre mb-2 d-inline-block">
                            <?= htmlspecialchars($song->genre) ?>
                        </span>
                    <?php endif; ?>
                    <?php if (!empty($song->album)): ?>
                        <p class="mb-1">
                            <span class="song-detail-label">Album:</span>
                            <?= htmlspecialchars($song->album) ?>
                        </p>
                    <?php endif; ?>
                    <?php if (empty($song->genre) && empty($song->album)): ?>
                        <p class="text-muted small mb-0">No additional details available.</p>
                    <?php endif; ?>
                </div>
            </div>

        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../Partials/footer.php'; ?>
