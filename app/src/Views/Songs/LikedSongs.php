<?php $pageTitle = 'My Liked Songs — SongClub'; ?>
<?php require __DIR__ . '/../Partials/header.php'; ?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <a href="/profile/<?= (int)$_SESSION['user_id'] ?>"
           class="back-link">&larr; Back to profile</a>
        <h2 class="sc-section-title mb-0 mt-2">My Liked Songs</h2>
    </div>
</div>

<?php if (empty($songs)): ?>
    <p class="text-muted">You haven't liked any songs yet.</p>
<?php else: ?>
    <div class="song-list">
        <?php foreach ($songs as $i => $song): ?>
        <div class="song-list-item">

            <!-- Collapsed row -->
            <div class="song-list-row"
                 data-bs-toggle="collapse"
                 data-bs-target="#liked-details-<?= (int)$song->id ?>-<?= $i ?>"
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
                        <span class="song-list-chevron">›</span>
                    </div>
                </div>
            </div>

            <!-- Expanded details -->
            <div class="collapse song-list-details"
                 id="liked-details-<?= (int)$song->id ?>-<?= $i ?>">
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
