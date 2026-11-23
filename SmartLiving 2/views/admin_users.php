<?php
/**
 * views/admin_users.php
 * Backoffice: list, search and filter users + actions (edit, block/unblock, delete).
 * Expects: $user (UserModel), $users (UserModel[]), $q, $role, $status (string),
 *          $flash (?array), $csrfToken (string)
 */
require_once __DIR__ . '/partials/helpers.php';
ob_start();
$me = (int) ($_SESSION['user_id'] ?? 0);
?>
<div class="ph">
    <div><p class="crumb">Admin / Users</p><h1>Users</h1><p>Create, read, update and delete accounts. Manage roles and access.</p></div>
    <a href="index.php?action=admin_user_create" class="btn btn-primary"><?= icon('plus', 18) ?> Add user</a>
</div>

<?php $alertErrors = []; require __DIR__ . '/partials/alerts.php'; ?>

<div class="panel">
    <div class="hd">
        <div><h3>All accounts</h3><p class="sub" style="margin:0"><?= count($users) ?> result(s)</p></div>
        <form class="filters" method="GET" action="index.php">
            <input type="hidden" name="action" value="admin_users">
            <select name="role">
                <option value="">All roles</option>
                <option value="user" <?= $role === 'user' ? 'selected' : '' ?>>User</option>
                <option value="admin" <?= $role === 'admin' ? 'selected' : '' ?>>Admin</option>
            </select>
            <select name="status">
                <option value="">All statuses</option>
                <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="blocked" <?= $status === 'blocked' ? 'selected' : '' ?>>Blocked</option>
            </select>
            <input name="q" value="<?= e($q) ?>" placeholder="Search..." maxlength="100">
            <button class="btn btn-light" type="submit"><?= icon('filter', 16) ?> Filter</button>
            <?php if ($q !== '' || $role !== '' || $status !== ''): ?>
                <a class="btn btn-light" href="index.php?action=admin_users">Reset</a>
            <?php endif; ?>
        </form>
    </div>

    <table>
        <thead>
            <tr><th>User</th><th>Email</th><th>Role</th><th>Status</th><th>Points</th><th>Created at</th><th>Actions</th></tr>
        </thead>
        <tbody>
        <?php foreach ($users as $u): /** @var UserModel $u */ ?>
            <tr>
                <td><div class="who"><div class="av"><?= e($u->getInitials()) ?></div><div><b><?= e($u->getFullName()) ?></b><br><small class="muted">@<?= e($u->getUsername()) ?></small></div></div></td>
                <td><?= e($u->getEmail()) ?></td>
                <td><span class="badge <?= $u->getRole() === 'admin' ? 'badge-admin' : 'badge-user' ?>"><?= e($u->getRole()) ?></span></td>
                <td><span class="tag <?= $u->getStatus() === 'active' ? 't-green' : 't-red' ?>"><?= e($u->getStatus()) ?></span></td>
                <td><?= $u->getPoints() ?></td>
                <td><?= e(date('d M Y', strtotime($u->getCreatedAt()) ?: time())) ?></td>
                <td>
                    <div class="acts">
                        <a href="index.php?action=admin_user_edit&amp;id=<?= $u->getId() ?>" class="icon-btn e" title="Edit"><?= icon('edit', 16) ?></a>
                        <?php if ($u->getId() !== $me): ?>
                            <form method="POST" action="index.php?action=admin_user_toggle" class="inline"
                                  data-confirm="<?= $u->getStatus() === 'active' ? 'Block' : 'Unblock' ?> this account?">
                                <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                                <input type="hidden" name="id" value="<?= $u->getId() ?>">
                                <button type="submit" class="icon-btn" title="<?= $u->getStatus() === 'active' ? 'Block' : 'Unblock' ?>"><?= icon($u->getStatus() === 'active' ? 'lock' : 'unlock', 16) ?></button>
                            </form>
                            <form method="POST" action="index.php?action=admin_user_delete" class="inline"
                                  data-confirm="Delete this user permanently?">
                                <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                                <input type="hidden" name="id" value="<?= $u->getId() ?>">
                                <button type="submit" class="icon-btn d" title="Delete"><?= icon('trash', 16) ?></button>
                            </form>
                        <?php else: ?>
                            <span class="tag t-teal">You</span>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($users)): ?>
            <tr><td colspan="7" class="empty">No user matches your search.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php
$content = ob_get_clean();
$title = 'Users';
$activePage = 'users';
require __DIR__ . '/layout_admin.php';
