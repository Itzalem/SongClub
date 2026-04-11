<?php $pageTitle = 'Admin — Users'; ?>
<?php require __DIR__ . '/../partials/header.php'; ?>

<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="sc-section-title mb-0">User Management</h1>
        <span class="badge bg-secondary"><?= count($users) ?> users</span>
    </div>

    <?php if (empty($users)): ?>
        <p class="text-muted">No users found.</p>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table table-hover align-middle bg-white rounded-3 overflow-hidden shadow-sm">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Bio</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= (int) $user->userId ?></td>
                    <td>
                        <a href="/profile/<?= (int) $user->userId ?>">
                            <?= htmlspecialchars($user->username) ?>
                        </a>
                    </td>
                    <td><?= htmlspecialchars($user->email) ?></td>
                    <td>
                        <?php if ($user->role === 'admin'): ?>
                            <span class="badge bg-danger">admin</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">user</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-muted" style="max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                        <?= htmlspecialchars($user->bio ?? '') ?>
                    </td>
                    <td>
                        <?php if ((int) $user->userId !== (int) $_SESSION['user_id']): ?>
                        <form method="POST"
                              action="/admin/users/<?= (int) $user->userId ?>/delete"
                              onsubmit="return confirm('Delete <?= htmlspecialchars($user->username, ENT_QUOTES) ?>?')">
                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                        <?php else: ?>
                            <span class="text-muted small">you</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
