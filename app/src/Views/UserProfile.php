<?php $pageTitle = htmlspecialchars($vm->user->username) . ' — SongClub'; ?>
<?php require __DIR__ . '/Partials/header.php'; ?>

<div class="container py-4">
    <div class="row g-4">

        <!-- ===== LEFT: Post feed ===== -->
        <div class="col-md-8">

            <!-- Post a song (owner only) -->
            <?php if ($vm->isOwner && !empty($vm->songs)): ?>
            <div class="mb-4">
                <button class="btn btn-sc-primary"
                        data-bs-toggle="collapse"
                        data-bs-target="#postSongForm">
                    + Post a song
                </button>
                <div class="collapse mt-3" id="postSongForm">
                    <div class="post-form-card">
                        <form method="POST" action="/last-listened/set">
                            <div class="mb-3">
                                <label for="song_id" class="form-label">Song</label>
                                <select name="song_id" id="song_id" class="form-select" required>
                                    <option value="">Choose a song…</option>
                                    <?php foreach ($vm->songs as $song): ?>
                                        <option value="<?= (int)$song->id ?>">
                                            <?= htmlspecialchars($song->title) ?> — <?= htmlspecialchars($song->artist) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="caption" class="form-label">Caption</label>
                                <textarea name="caption" id="caption"
                                          class="form-control" rows="2"
                                          placeholder="e.g. Last listened song, or what you thought of it…"></textarea>
                            </div>
                            <button type="submit" class="btn btn-sc-primary btn-sm">Post</button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Post card -->
            <?php if ($vm->lastPost !== null): ?>
            <article class="post-card mb-2">
                <div class="post-card-body">

                    <?php if (!empty($vm->lastPost->song_genre)): ?>
                        <span class="badge-genre mb-3 d-inline-block">
                            <?= htmlspecialchars($vm->lastPost->song_genre) ?>
                        </span>
                    <?php endif; ?>

                    <p class="post-song-title mb-1">
                        <?= htmlspecialchars($vm->lastPost->song_title) ?>
                    </p>
                    <p class="post-song-artist">
                        <?= htmlspecialchars($vm->lastPost->song_artist) ?>
                    </p>

                    <?php if (!empty($vm->lastPost->song_album)): ?>
                        <p class="post-song-album">
                            Album: <?= htmlspecialchars($vm->lastPost->song_album) ?>
                        </p>
                    <?php endif; ?>

                    <?php if (!empty($vm->lastPost->caption)): ?>
                        <p class="post-caption">
                            "<?= htmlspecialchars($vm->lastPost->caption) ?>"
                        </p>
                    <?php endif; ?>

                    <?php if (!empty($vm->lastPost->song_link)): ?>
                        <a href="<?= htmlspecialchars($vm->lastPost->song_link) ?>"
                           target="_blank" rel="noopener noreferrer"
                           class="btn btn-sc-outline btn-sm mt-1">▶ Listen</a>
                    <?php endif; ?>

                </div>

                <!-- Comments section — inside the card -->
                <div class="post-card-comments">
                    <h6 class="comments-label">Comments</h6>

                    <ul id="comments-list" class="list-unstyled mb-3">
                        <?php foreach ($vm->comments as $comment): ?>
                        <li class="comment-item">
                            <div class="d-flex justify-content-between mb-1">
                                <strong class="comment-author">
                                    <?= htmlspecialchars($comment->username) ?>
                                </strong>
                                <small class="text-muted">
                                    <?= htmlspecialchars($comment->created_at) ?>
                                </small>
                            </div>
                            <p class="mb-0"><?= htmlspecialchars($comment->content) ?></p>
                        </li>
                        <?php endforeach; ?>
                        <?php if (empty($vm->comments)): ?>
                            <li id="no-comments" class="text-muted small">No comments yet.</li>
                        <?php endif; ?>
                    </ul>

                    <!-- Comment form: logged-in non-owners only -->
                    <?php if (isset($_SESSION['user_id']) && !$vm->isOwner): ?>
                    <form id="comment-form" data-post-id="<?= (int)$vm->lastPost->id ?>">
                        <div class="d-flex gap-2">
                            <textarea id="comment-content"
                                      class="form-control form-control-sm" rows="1"
                                      placeholder="Add a comment…" required
                                      style="resize:none"></textarea>
                            <button type="submit" class="btn btn-sc-primary btn-sm flex-shrink-0">
                                Post
                            </button>
                        </div>
                    </form>
                    <?php endif; ?>

                </div>
            </article>

            <?php else: ?>
                <div class="post-empty-state">
                    <p class="text-muted">
                        <?= $vm->isOwner
                            ? 'You haven\'t posted a song yet. Use the button above to share what you\'re listening to!'
                            : htmlspecialchars($vm->user->username) . ' hasn\'t posted a song yet.' ?>
                    </p>
                </div>
            <?php endif; ?>

        </div>
        <!-- ===== /LEFT ===== -->

        <!-- ===== RIGHT: Sidebar ===== -->
        <div class="col-md-4">

            <!-- Profile card -->
            <div class="sidebar-profile-card mb-3">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="profile-avatar">
                        <?= htmlspecialchars(mb_strtoupper(mb_substr($vm->user->username, 0, 1))) ?>
                    </div>
                    <div>
                        <h5 class="mb-0 fw-bold text-sc-cream">
                            <?= htmlspecialchars($vm->user->username) ?>
                        </h5>
                        <small class="opacity-75">
                            <?= htmlspecialchars(ucfirst($vm->user->role ?? 'user')) ?>
                        </small>
                    </div>
                </div>
                <?php if (!empty($vm->user->bio)): ?>
                    <p class="mb-0 opacity-90 small"><?= htmlspecialchars($vm->user->bio) ?></p>
                <?php endif; ?>
            </div>

            <!-- Edit profile (owner only) -->
            <?php if ($vm->isOwner): ?>
            <div class="mb-2">
                <a href="/profile/edit" class="btn btn-sc-outline btn-sm w-100">
                    Edit profile
                </a>
            </div>
            <?php endif; ?>

            <!-- Favorites button → separate view -->
            <div class="mb-2">
                <a href="/profile/<?= (int)$vm->user->userId ?>/favorites"
                   class="btn btn-fav btn-sm w-100 text-decoration-none">
                    ★ Favorites (<?= count($vm->favorites) ?>)
                </a>
            </div>

            <!-- Liked songs button → separate view (owner only) -->
            <?php if ($vm->isOwner): ?>
            <div class="mb-2">
                <a href="/profile/<?= (int)$vm->user->userId ?>/liked"
                   class="btn btn-like btn-sm w-100 text-decoration-none">
                    ♥ Liked (<?= count($vm->likes) ?>)
                </a>
            </div>
            <?php endif; ?>

        </div>
        <!-- ===== /RIGHT ===== -->

    </div>
</div>

<?php require __DIR__ . '/Partials/footer.php'; ?>
