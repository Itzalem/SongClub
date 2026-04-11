<?php $pageTitle = htmlspecialchars($vm->user->username) . ' — SongClub'; ?>
<?php require __DIR__ . '/Partials/header.php'; ?>

<div class="container py-4">
    <div class="row g-4">

        <div class="col-md-8">

            <?php if ($vm->isOwner && !empty($vm->songs)): ?>
            <div class="mb-4">
                <button class="btn btn-sc-primary" data-bs-toggle="collapse" data-bs-target="#postSongForm">
                    + Compartir una canción
                </button>
                <div class="collapse mt-3" id="postSongForm">
                    <div class="post-form-card">
                        <form method="POST" action="/last-listened/set">
                            <div class="mb-3">
                                <label for="song_id" class="form-label">Canción</label>
                                <select name="song_id" id="song_id" class="form-select" required>
                                    <option value="">Elige una canción…</option>
                                    <?php foreach ($vm->songs as $song): ?>
                                        <option value="<?= (int)$song->id ?>">
                                            <?= htmlspecialchars($song->title) ?> — <?= htmlspecialchars($song->artist) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="caption" class="form-label">Comentario del post</label>
                                <textarea name="caption" id="caption" class="form-control" rows="2" placeholder="¿Qué te parece esta canción?"></textarea>
                            </div>
                            <button type="submit" class="btn btn-sc-primary btn-sm">Publicar</button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($vm->posts)): ?>
                <?php foreach ($vm->posts as $post): ?>
                <article class="post-card mb-4">
                    <div class="post-card-body">
                        <?php if (!empty($post->song_genre)): ?>
                            <span class="badge-genre mb-3 d-inline-block"><?= htmlspecialchars($post->song_genre) ?></span>
                        <?php endif; ?>

                        <p class="post-song-title mb-1"><?= htmlspecialchars($post->song_title) ?></p>
                        <p class="post-song-artist"><?= htmlspecialchars($post->song_artist) ?></p>

                        <?php if (!empty($post->caption)): ?>
                            <p class="post-caption">"<?= htmlspecialchars($post->caption) ?>"</p>
                        <?php endif; ?>

                        <?php if (!empty($post->song_link)): ?>
                            <a href="<?= htmlspecialchars($post->song_link) ?>" target="_blank" class="btn btn-sc-outline btn-sm mt-1">▶ Escuchar</a>
                        <?php endif; ?>
                    </div>

                    <div class="post-card-comments">
                        <h6 class="comments-label">Comentarios</h6>
                        <ul id="comments-list-<?= (int)$post->id ?>" class="list-unstyled mb-3">
                            <?php foreach ($post->comments as $comment): ?>
                            <li class="comment-item">
                                <div class="d-flex justify-content-between mb-1">
                                    <strong class="comment-author"><?= htmlspecialchars($comment->username) ?></strong>
                                    <small class="text-muted"><?= htmlspecialchars($comment->created_at) ?></small>
                                </div>
                                <p class="mb-0"><?= htmlspecialchars($comment->content) ?></p>
                            </li>
                            <?php endforeach; ?>
                        </ul>

                        <?php if (isset($_SESSION['user_id'])): ?>
                        <form class="comment-form" data-post-id="<?= (int)$post->id ?>">
                            <div class="d-flex gap-2">
                                <textarea class="form-control form-control-sm comment-content" rows="1" placeholder="Escribe un comentario..." required style="resize:none"></textarea>
                                <button type="submit" class="btn btn-sc-primary btn-sm flex-shrink-0">Enviar</button>
                            </div>
                        </form>
                        <?php endif; ?>
                    </div>
                </article>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="post-empty-state">
                    <p class="text-muted italic">Aún no hay canciones compartidas.</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="col-md-4">
            <div class="sidebar-profile-card mb-3">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="profile-avatar">
                        <?= htmlspecialchars(mb_strtoupper(mb_substr($vm->user->username, 0, 1))) ?>
                    </div>
                    <div>
                        <h5 class="mb-0 fw-bold text-sc-cream"><?= htmlspecialchars($vm->user->username) ?></h5>
                        <small class="opacity-75"><?= htmlspecialchars(ucfirst($vm->user->role ?? 'user')) ?></small>
                    </div>
                </div>
                <?php if (!empty($vm->user->bio)): ?>
                    <p class="mb-0 opacity-90 small"><?= htmlspecialchars($vm->user->bio) ?></p>
                <?php endif; ?>
            </div>

            <?php if ($vm->isOwner): ?>
                <a href="/profile/edit" class="btn btn-sc-outline btn-sm w-100 mb-2">Editar Perfil</a>
            <?php endif; ?>
            <a href="/profile/<?= (int)$vm->user->userId ?>/favorites" class="btn btn-fav btn-sm w-100 text-decoration-none mb-2">★ Favoritos (<?= count($vm->favorites) ?>)</a>
            <?php if ($vm->isOwner): ?>
                <a href="/profile/<?= (int)$vm->user->userId ?>/liked" class="btn btn-like btn-sm w-100 text-decoration-none">♥ Mis Likes (<?= count($vm->likes) ?>)</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require __DIR__ . '/Partials/footer.php'; ?>