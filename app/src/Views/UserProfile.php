<?php $pageTitle = htmlspecialchars($vm->user->username) . ' — Perfil'; ?>
<?php require __DIR__ . '/Partials/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-lg-10 mb-5">
        <div class="bg-white p-4 p-md-5 rounded-5 shadow-sm border d-md-flex align-items-center text-center text-md-start">
            <div class="profile-avatar shadow-lg mb-3 mb-md-0 me-md-5">
                <?= htmlspecialchars(mb_strtoupper(mb_substr($vm->user->username, 0, 1))) ?>
            </div>
            <div class="flex-grow-1">
                <div class="d-flex flex-column flex-md-row align-items-center gap-3 mb-3">
                    <h1 class="fw-bold mb-0"><?= htmlspecialchars($vm->user->username) ?></h1>
                    <?php if ($vm->isOwner): ?>
                        <a href="/profile/edit" class="btn btn-light btn-sm border fw-bold px-4 rounded-pill">Editar Perfil</a>
                    <?php endif; ?>
                </div>
                <p class="text-muted fs-5 mb-0"><?= htmlspecialchars($vm->user->bio ?? 'Amante de la música en SongClub.') ?></p>
            </div>
            <div class="d-flex gap-4 justify-content-center mt-4 mt-md-0 ps-md-5 border-md-start">
                <div class="text-center">
                    <div class="fw-bold fs-3"><?= count($vm->favorites) ?></div>
                    <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Favoritos</small>
                </div>
                <div class="text-center">
                    <div class="fw-bold fs-3"><?= count($vm->posts) ?></div>
                    <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Posts</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <h4 class="fw-bold mb-4 d-flex align-items-center">
            <span class="me-2">🎵</span> Muro de actividad
        </h4>

        <?php if ($vm->isOwner): ?>
            <div class="card border-0 shadow-sm rounded-4 mb-5 overflow-hidden">
                <div class="card-body p-4" style="background: var(--sc-olive-soft);">
                    <h6 class="fw-bold mb-3">¿Qué canción define tu día hoy?</h6>
                    <form method="POST" action="/last-listened/set">
                        <select name="song_id" class="form-select border-0 shadow-sm mb-3" required>
                            <option value="">Selecciona de la lista...</option>
                            <?php foreach ($vm->songs as $song): ?>
                                <option value="<?= (int)$song->id ?>"><?= htmlspecialchars($song->title) ?> — <?= htmlspecialchars($song->artist) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <textarea name="caption" class="form-control border-0 shadow-sm mb-3" rows="2" placeholder="Añade un comentario..."></textarea>
                        <button class="btn btn-sc-primary w-100">Publicar en mi muro</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($vm->posts)): ?>
            <?php foreach ($vm->posts as $post): ?>
                <article class="post-card mb-5 border-0">
                    <div class="p-4 p-md-5">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <span class="badge-genre"><?= htmlspecialchars($post->song_genre ?? 'Música') ?></span>
                            <small class="text-muted fw-medium"><?= date('j M, Y', strtotime($post->created_at)) ?></small>
                        </div>
                        
                        <h2 class="fw-bold mb-1 text-olive"><?= htmlspecialchars($post->song_title) ?></h2>
                        <p class="text-muted fs-4 mb-4"><?= htmlspecialchars($post->song_artist) ?></p>
                        
                        <?php if ($post->caption): ?>
                            <div class="post-caption">"<?= htmlspecialchars($post->caption) ?>"</div>
                        <?php endif; ?>

                        <div class="d-flex gap-2">
                            <?php if ($post->song_link): ?>
                                <a href="<?= htmlspecialchars($post->song_link) ?>" target="_blank" class="btn btn-sc-primary flex-grow-1">Escuchar ahora</a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="post-card-comments">
                        <ul id="comments-list-<?= (int)$post->id ?>" class="list-unstyled mb-4">
                            <?php foreach ($post->comments as $comment): ?>
                                <li class="comment-item">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="comment-author">@<?= htmlspecialchars($comment->username) ?></span>
                                        <small class="text-muted" style="font-size: 0.7rem;"><?= date('H:i', strtotime($comment->created_at)) ?></small>
                                    </div>
                                    <p class="mb-0 text-secondary"><?= htmlspecialchars($comment->content) ?></p>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <form class="comment-form" data-post-id="<?= (int)$post->id ?>">
                                <div class="input-group bg-white shadow-sm rounded-pill p-1">
                                    <input type="text" class="form-control border-0 px-3 comment-content" placeholder="Escribe un comentario...">
                                    <button class="btn btn-sc-primary rounded-pill px-4">Enviar</button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="text-center py-5 opacity-50">
                <p class="fs-4 italic">No hay actividad reciente en este muro.</p>
            </div>
        <?php endif; ?>
    </div>

    <div class="col-lg-3 d-none d-lg-block">
        <div class="sticky-top" style="top: 100px;">
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-3">
                <h6 class="fw-bold mb-3">Listas Musicales</h6>
                <a href="/profile/<?= (int)$vm->user->userId ?>/favorites" class="btn btn-sc-outline w-100 mb-2 py-2">★ Mis Favoritos</a>
                <?php if ($vm->isOwner): ?>
                    <a href="/profile/<?= (int)$vm->user->userId ?>/liked" class="btn btn-sc-outline w-100 py-2">♥ Mis Likes</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/Partials/footer.php'; ?>