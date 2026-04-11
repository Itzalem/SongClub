<?php
$pageTitle = $editMode
    ? 'Edit Profile — SongClub'
    : htmlspecialchars($user->username) . ' — SongClub';
?>
<?php require __DIR__ . '/../Partials/header.php'; ?>

<div class="container py-4" style="max-width:680px">

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger mb-4"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div class="alert alert-success mb-4"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <!-- Profile card -->
    <div class="profile-view-card mb-4">

        <!-- Avatar + name row -->
        <div class="d-flex align-items-center gap-4 mb-4">
            <div class="profile-avatar-lg">
                <?= htmlspecialchars(mb_strtoupper(mb_substr($user->username, 0, 1))) ?>
            </div>
            <div class="flex-grow-1">
                <?php if ($editMode): ?>
                    <label class="form-label">Username</label>
                    <input type="text" name="username" form="edit-profile-form"
                           class="form-control mb-1"
                           value="<?= htmlspecialchars($user->username) ?>" required>
                <?php else: ?>
                    <h2 class="profile-view-name mb-0"><?= htmlspecialchars($user->username) ?></h2>
                    <small class="text-muted"><?= htmlspecialchars(ucfirst($user->role ?? 'user')) ?></small>
                <?php endif; ?>
            </div>
        </div>

        <!-- Bio -->
        <div class="mb-3">
            <?php if ($editMode): ?>
                <label class="form-label">Bio</label>
                <textarea name="bio" form="edit-profile-form"
                          class="form-control" rows="3"
                          placeholder="Tell people about yourself…"><?= htmlspecialchars($user->bio ?? '') ?></textarea>
            <?php else: ?>
                <?php if (!empty($user->bio)): ?>
                    <p class="profile-view-bio mb-0"><?= htmlspecialchars($user->bio) ?></p>
                <?php else: ?>
                    <p class="text-muted fst-italic mb-0">No bio yet.</p>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- Email (only shown to owner) -->
        <?php if ($isOwner): ?>
        <div class="mb-3">
            <?php if ($editMode): ?>
                <label class="form-label">Email</label>
                <input type="email" name="email" form="edit-profile-form"
                       class="form-control"
                       value="<?= htmlspecialchars($user->email ?? '') ?>" required>
            <?php else: ?>
                <div class="profile-view-field">
                    <span class="profile-view-label">Email</span>
                    <span><?= htmlspecialchars($user->email ?? '—') ?></span>
                </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Password change (edit mode only) -->
        <?php if ($editMode): ?>
        <hr>
        <p class="form-label mb-3">Change password <small class="text-muted fw-normal">(leave blank to keep current)</small></p>
        <div class="mb-3">
            <label class="form-label">Current password</label>
            <input type="password" name="current_password" form="edit-profile-form"
                   class="form-control" placeholder="Required only if changing password">
        </div>
        <div class="mb-3">
            <label class="form-label">New password</label>
            <input type="password" name="new_password" form="edit-profile-form"
                   class="form-control" placeholder="At least 6 characters">
        </div>
        <div class="mb-3">
            <label class="form-label">Confirm new password</label>
            <input type="password" name="confirm_password" form="edit-profile-form"
                   class="form-control">
        </div>
        <?php endif; ?>

    </div><!-- /profile-view-card -->

    <!-- Action buttons -->
    <?php if ($editMode): ?>

        <form id="edit-profile-form" method="POST" action="/profile/edit">
            <!-- hidden — the form fields above use form="edit-profile-form" -->
        </form>
        <div class="d-flex gap-2">
            <button type="submit" form="edit-profile-form"
                    class="btn btn-sc-primary">Save changes</button>
            <a href="/user/<?= (int)$user->userId ?>"
               class="btn btn-sc-outline">Cancel</a>
        </div>

    <?php elseif ($isOwner): ?>

        <div class="d-flex gap-2 flex-wrap">
            <a href="/profile/edit" class="btn btn-sc-primary">Edit profile</a>
            <button type="button" class="btn btn-sc-outline"
                    data-bs-toggle="modal" data-bs-target="#shareModal">
                Share profile
            </button>
        </div>

        <!-- Share modal -->
        <div class="modal fade" id="shareModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-3 border-0 shadow">
                    <div class="modal-header border-0">
                        <h5 class="modal-title fw-bold">Share your profile</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted small mb-2">Copy this link and share it with anyone:</p>
                        <div class="input-group">
                            <input type="text" id="share-url" class="form-control"
                                   value="<?= htmlspecialchars((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/user/' . (int)$user->userId) ?>"
                                   readonly>
                            <button class="btn btn-sc-primary" type="button" id="copy-share-url">
                                Copy
                            </button>
                        </div>
                        <p id="copy-feedback" class="text-success small mt-2" style="display:none">
                            Link copied to clipboard!
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <script>
        document.getElementById('copy-share-url').addEventListener('click', function () {
            var input = document.getElementById('share-url');
            navigator.clipboard.writeText(input.value).then(function () {
                var fb = document.getElementById('copy-feedback');
                fb.style.display = 'block';
                setTimeout(function () { fb.style.display = 'none'; }, 2500);
            });
        });
        </script>

    <?php endif; ?>

</div>

<?php require __DIR__ . '/../Partials/footer.php'; ?>
