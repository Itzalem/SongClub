<?php $pageTitle = htmlspecialchars($vm->user->username) . ' — SongClub'; ?>
<?php require __DIR__ . '/../partials/header.php'; ?>

<div class="row g-4">

    <!-- Feed (left) -->
    <div class="col-md-8">

        <h3 class="sc-section-title">Last Listened</h3>

        <?php if ($vm->lastSong): ?>
            <div class="song-card mb-3">
                <div class="card-body">
                    <?php if (!empty($vm->lastSong->genre)): ?>
                        <span class="badge-genre mb-2 d-inline-block">
                            <?= htmlspecialchars($vm->lastSong->genre) ?>
                        </span>
                    <?php endif; ?>
                    <p class="song-title mb-1">
                        <a href="/songs/<?= (int) $vm->lastSong->id ?>" style="color:inherit;text-decoration:none">
                            <?= htmlspecialchars($vm->lastSong->title) ?>
                        </a>
                    </p>
                    <p class="song-artist"><?= htmlspecialchars($vm->lastSong->artist) ?></p>
                </div>
            </div>
        <?php else: ?>
            <p class="text-muted">No posts yet.</p>
        <?php endif; ?>

        <?php if ($vm->isOwner): ?>
            <a href="#" class="btn btn-sc-primary btn-sm mt-2">+ Post last listened song</a>
        <?php endif; ?>

    </div>

    <!-- Side panel (right) -->
    <div class="col-md-4">

        <!-- Profile card -->
        <div class="card border-0 shadow-sm rounded-3 p-3 mb-3">
            <div class="d-flex align-items-center gap-3 mb-2">
                <div class="profile-avatar" style="width:52px;height:52px;font-size:1.5rem">
                    <?= strtoupper(substr($vm->user->username, 0, 1)) ?>
                </div>
                <div>
                    <strong><?= htmlspecialchars($vm->user->username) ?></strong>
                    <?php if ($vm->isOwner): ?>
                        <br>
                        <a href="#editProfile" data-bs-toggle="collapse"
                           style="font-size:0.8rem;color:var(--sc-olive)">Edit profile</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php if (!empty($vm->user->bio)): ?>
                <p style="font-size:0.875rem;color:var(--sc-text-muted);margin:0">
                    <?= htmlspecialchars($vm->user->bio) ?>
                </p>
            <?php endif; ?>
        </div>

        <!-- Edit profile form (owner, collapsed) -->
        <?php if ($vm->isOwner): ?>
        <div class="collapse mb-3" id="editProfile">
            <div class="card border-0 shadow-sm rounded-3 p-3">
                <h6 style="font-weight:700;margin-bottom:0.75rem">Edit profile</h6>
                <form method="POST" action="/profile/update">
                    <div class="mb-2">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" id="username" name="username" class="form-control"
                               value="<?= htmlspecialchars($vm->user->username) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="bio" class="form-label">Bio</label>
                        <textarea id="bio" name="bio" class="form-control"
                                  rows="2"><?= htmlspecialchars($vm->user->bio ?? '') ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-sc-primary btn-sm w-100">Save</button>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <!-- Favourite songs -->
        <div class="mb-3">
            <button class="btn btn-sc-outline btn-sm w-100 mb-2"
                    data-bs-toggle="collapse" data-bs-target="#favSongs">
                ★ Favourite Songs
            </button>
            <div class="collapse" id="favSongs">
                <?php if (empty($vm->favorites)): ?>
                    <p class="text-muted" style="font-size:0.85rem;padding:0.4rem 0">No favourites yet.</p>
                <?php else: ?>
                    <ul class="list-unstyled mb-0">
                        <?php foreach ($vm->favorites as $song): ?>
                        <li style="padding:0.35rem 0;border-bottom:1px solid #eee;font-size:0.9rem">
                            <a href="/songs/<?= (int) $song->id ?>" style="color:var(--sc-text)">
                                <?= htmlspecialchars($song->title) ?>
                            </a>
                            <span style="color:var(--sc-text-muted);font-size:0.8rem">
                                — <?= htmlspecialchars($song->artist) ?>
                            </span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>

        <!-- Liked songs (owner only) -->
        <?php if ($vm->isOwner): ?>
        <div class="mb-3">
            <button class="btn btn-like btn-sm w-100 mb-2"
                    data-bs-toggle="collapse" data-bs-target="#likedSongs">
                ♥ Liked Songs
            </button>
            <div class="collapse" id="likedSongs">
                <?php if (empty($vm->likes)): ?>
                    <p class="text-muted" style="font-size:0.85rem;padding:0.4rem 0">No likes yet.</p>
                <?php else: ?>
                    <ul class="list-unstyled mb-0">
                        <?php foreach ($vm->likes as $song): ?>
                        <li style="padding:0.35rem 0;border-bottom:1px solid #eee;font-size:0.9rem">
                            <a href="/songs/<?= (int) $song->id ?>" style="color:var(--sc-text)">
                                <?= htmlspecialchars($song->title) ?>
                            </a>
                            <span style="color:var(--sc-text-muted);font-size:0.8rem">
                                — <?= htmlspecialchars($song->artist) ?>
                            </span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
