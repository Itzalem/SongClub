<?php $pageTitle = htmlspecialchars($vm->user->username) . '\'s Profile'; ?>
<?php require __DIR__ . '/../partials/header.php'; ?>

<div class="container py-4">
    <div class="row g-4">

        <!-- Left: Feed -->
        <div class="col-md-8">

            <h2 class="sc-section-title">Last Listened</h2>

            <?php if ($vm->lastPost !== null): ?>
            <div class="song-card p-3 mb-4">
                <?php if (!empty($vm->lastPost->song_genre)): ?>
                    <span class="badge-genre d-inline-block mb-2">
                        <?= htmlspecialchars($vm->lastPost->song_genre) ?>
                    </span>
                <?php endif; ?>

                <p class="song-title mb-1"><?= htmlspecialchars($vm->lastPost->song_title) ?></p>
                <p class="song-artist"><?= htmlspecialchars($vm->lastPost->song_artist) ?></p>

                <?php if (!empty($vm->lastPost->song_album)): ?>
                    <small class="text-muted">
                        Album: <?= htmlspecialchars($vm->lastPost->song_album) ?>
                    </small>
                <?php endif; ?>

                <?php if (!empty($vm->lastPost->caption)): ?>
                    <p class="mt-2 fst-italic">"<?= htmlspecialchars($vm->lastPost->caption) ?>"</p>
                <?php endif; ?>

                <?php if (!empty($vm->lastPost->song_link)): ?>
                    <a href="<?= htmlspecialchars($vm->lastPost->song_link) ?>"
                       target="_blank" rel="noopener noreferrer"
                       class="btn btn-sm btn-sc-outline mt-2">
                        ▶ Listen
                    </a>
                <?php endif; ?>
            </div>
            <?php else: ?>
                <p class="text-muted mb-4">No song posted yet.</p>
            <?php endif; ?>

            <!-- Set Last Listened (owner only) -->
            <?php if ($vm->isOwner && !empty($vm->songs)): ?>
            <div class="mb-4">
                <button class="btn btn-sc-outline btn-sm"
                        data-bs-toggle="collapse"
                        data-bs-target="#setLastListened">
                    + Set last listened
                </button>
                <div class="collapse mt-2" id="setLastListened">
                    <div class="card p-3">
                        <form method="POST" action="/last-listened/set">
                            <div class="mb-3">
                                <label for="song_id" class="form-label">Song</label>
                                <select name="song_id" id="song_id" class="form-select" required>
                                    <option value="">Choose a song…</option>
                                    <?php foreach ($vm->songs as $song): ?>
                                        <option value="<?= (int) $song->id ?>">
                                            <?= htmlspecialchars($song->title) ?> — <?= htmlspecialchars($song->artist) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="caption" class="form-label">Caption (optional)</label>
                                <textarea name="caption" id="caption"
                                          class="form-control" rows="2"
                                          placeholder="What did you think?"></textarea>
                            </div>
                            <button type="submit" class="btn btn-sc-primary btn-sm">Save</button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Comments -->
            <div id="comments-section">
                <h3 class="sc-section-title">Comments</h3>

                <ul id="comments-list" class="list-unstyled">
                    <?php foreach ($vm->comments as $comment): ?>
                    <li class="mb-3 p-3 bg-white rounded-3 shadow-sm">
                        <div class="d-flex justify-content-between mb-1">
                            <strong><?= htmlspecialchars($comment->username) ?></strong>
                            <small class="text-muted"><?= htmlspecialchars($comment->created_at) ?></small>
                        </div>
                        <p class="mb-0"><?= htmlspecialchars($comment->content) ?></p>
                    </li>
                    <?php endforeach; ?>
                    <?php if (empty($vm->comments)): ?>
                        <li id="no-comments" class="text-muted">No comments yet.</li>
                    <?php endif; ?>
                </ul>

                <!-- Comment form: logged-in users who are NOT the profile owner -->
                <?php if (isset($_SESSION['user_id']) && $vm->lastPost !== null && !$vm->isOwner): ?>
                <form id="comment-form" class="mt-3" data-post-id="<?= (int) $vm->lastPost->id ?>">
                    <div class="mb-2">
                        <textarea id="comment-content"
                                  class="form-control" rows="2"
                                  placeholder="Add a comment…" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-sc-primary btn-sm">Post</button>
                </form>
                <?php endif; ?>
            </div>

        </div>

        <!-- Right: Sidebar -->
        <div class="col-md-4">

            <!-- Profile header -->
            <div class="profile-header mb-3">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="profile-avatar">
                        <?= htmlspecialchars(mb_strtoupper(mb_substr($vm->user->username, 0, 1))) ?>
                    </div>
                    <div>
                        <h2 class="mb-0"><?= htmlspecialchars($vm->user->username) ?></h2>
                        <small class="opacity-75"><?= htmlspecialchars($vm->user->role) ?></small>
                    </div>
                </div>
                <?php if (!empty($vm->user->bio)): ?>
                    <p class="mb-0 opacity-90"><?= htmlspecialchars($vm->user->bio) ?></p>
                <?php endif; ?>
            </div>

            <!-- Edit profile (owner only) -->
            <?php if ($vm->isOwner): ?>
            <div class="mb-3">
                <button class="btn btn-sc-outline btn-sm w-100"
                        data-bs-toggle="collapse"
                        data-bs-target="#editProfile">
                    Edit profile
                </button>
                <div class="collapse mt-2" id="editProfile">
                    <div class="card p-3">
                        <form method="POST" action="/profile/update">
                            <div class="mb-3">
                                <label for="edit_username" class="form-label">Username</label>
                                <input type="text" name="username" id="edit_username"
                                       class="form-control"
                                       value="<?= htmlspecialchars($vm->user->username) ?>">
                            </div>
                            <div class="mb-3">
                                <label for="edit_bio" class="form-label">Bio</label>
                                <textarea name="bio" id="edit_bio"
                                          class="form-control" rows="3"><?= htmlspecialchars($vm->user->bio ?? '') ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-sc-primary btn-sm">Save</button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Favorites -->
            <?php if (!empty($vm->favorites)): ?>
            <div class="mb-3">
                <button class="btn btn-fav btn-sm w-100"
                        data-bs-toggle="collapse"
                        data-bs-target="#favSongs">
                    ★ Favorites (<?= count($vm->favorites) ?>)
                </button>
                <div class="collapse mt-2" id="favSongs">
                    <ul class="list-unstyled">
                        <?php foreach ($vm->favorites as $song): ?>
                            <li class="mb-1">
                                <a href="/songs/<?= (int) $song->id ?>"
                                   style="color:inherit;text-decoration:none">
                                    <?= htmlspecialchars($song->title) ?> — <?= htmlspecialchars($song->artist) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <?php endif; ?>

            <!-- Liked songs (owner only) -->
            <?php if ($vm->isOwner && !empty($vm->likes)): ?>
            <div class="mb-3">
                <button class="btn btn-like btn-sm w-100"
                        data-bs-toggle="collapse"
                        data-bs-target="#likedSongs">
                    ♥ Liked (<?= count($vm->likes) ?>)
                </button>
                <div class="collapse mt-2" id="likedSongs">
                    <ul class="list-unstyled">
                        <?php foreach ($vm->likes as $song): ?>
                            <li class="mb-1">
                                <a href="/songs/<?= (int) $song->id ?>"
                                   style="color:inherit;text-decoration:none">
                                    <?= htmlspecialchars($song->title) ?> — <?= htmlspecialchars($song->artist) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
