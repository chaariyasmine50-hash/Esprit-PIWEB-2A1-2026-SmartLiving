<?php
/**
 * views/admin_space.php
 * Backoffice dashboard - visually distinct from the frontoffice.
 * Expects: $user (UserModel), $allUsers (UserModel[]), $latestUsers (UserModel[]),
 *          $totalUsers, $totalAdmins, $totalRegular, $totalBlocked (int), $flash (?array)
 */
require_once __DIR__ . '/partials/helpers.php';
ob_start();
$pct = fn(int $n) => $totalUsers > 0 ? round($n * 100 / $totalUsers) : 0;
?>
<div class="ph">
    <div><p class="crumb">Admin / Dashboard</p><h1>Dashboard</h1><p>Overview of the Smart Living accounts.</p></div>
    <a href="index.php?action=admin_user_create" class="btn btn-primary"><?= icon('plus', 18) ?> Add user</a>
</div>

<?php $alertErrors = []; require __DIR__ . '/partials/alerts.php'; ?>

<div class="kpis">
    <div class="kpi"><div class="i" style="background:#E6F2EF;color:#0F766E"><?= icon('users', 26) ?></div><div><b><?= $totalUsers ?></b><span>Total accounts</span></div></div>
    <div class="kpi"><div class="i" style="background:#E8EFFD;color:#2563EB"><?= icon('user', 26) ?></div><div><b><?= $totalRegular ?></b><span>Regular users (citizens)</span></div></div>
    <div class="kpi"><div class="i" style="background:#FDECEC;color:#DC2626"><?= icon('shield', 26) ?></div><div><b><?= $totalAdmins ?></b><span>Administrators</span></div></div>
    <div class="kpi"><div class="i" style="background:#FFF1E6;color:#C2410C"><?= icon('ban', 26) ?></div><div><b><?= $totalBlocked ?></b><span>Blocked accounts</span></div></div>
</div>

<div class="grid2">
    <div class="panel">
        <div class="hd">
            <div><h3>All users</h3><p class="sub" style="margin:0">Every account registered on the platform</p></div>
            <a href="index.php?action=admin_users" class="btn btn-light">Manage users</a>
        </div>
        <table>
            <thead>
                <tr><th>ID</th><th>User</th><th>Email</th><th>Role</th><th>Status</th><th>Created at</th></tr>
            </thead>
            <tbody>
            <?php foreach ($allUsers as $u): /** @var UserModel $u */ ?>
                <tr>
                    <td><?= $u->getId() ?></td>
                    <td><div class="who"><div class="av"><?= e($u->getInitials()) ?></div><div><b><?= e($u->getFullName()) ?></b><br><small class="muted">@<?= e($u->getUsername()) ?></small></div></div></td>
                    <td><?= e($u->getEmail()) ?></td>
                    <td><span class="badge <?= $u->getRole() === 'admin' ? 'badge-admin' : 'badge-user' ?>"><?= e($u->getRole()) ?></span></td>
                    <td><span class="tag <?= $u->getStatus() === 'active' ? 't-green' : 't-red' ?>"><?= e($u->getStatus()) ?></span></td>
                    <td><?= e(date('d M Y', strtotime($u->getCreatedAt()) ?: time())) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($allUsers)): ?>
                <tr><td colspan="6" class="empty">No users yet.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="panel">
        <h3>Accounts by role</h3>
        <p class="sub">Distribution of roles and statuses</p>
        <div class="mini">
            <div><div class="r"><span>Citizens (user)</span><b><?= $totalRegular ?></b></div><div class="progress"><i style="width:<?= $pct($totalRegular) ?>%;background:#2563EB"></i></div></div>
            <div><div class="r"><span>Administrators</span><b><?= $totalAdmins ?></b></div><div class="progress"><i style="width:<?= $pct($totalAdmins) ?>%;background:#DC2626"></i></div></div>
            <div><div class="r"><span>Active accounts</span><b><?= $totalUsers - $totalBlocked ?></b></div><div class="progress"><i style="width:<?= $pct($totalUsers - $totalBlocked) ?>%;background:#16A34A"></i></div></div>
            <div><div class="r"><span>Blocked accounts</span><b><?= $totalBlocked ?></b></div><div class="progress"><i style="width:<?= $pct($totalBlocked) ?>%;background:#EA580C"></i></div></div>
        </div>

        <h3 style="margin-top:26px">Latest sign-ups</h3>
        <div class="latest">
            <?php foreach ($latestUsers as $u): ?>
                <div class="who"><div class="av"><?= e($u->getInitials()) ?></div><div><b><?= e($u->getFullName()) ?></b><br><small class="muted"><?= e(date('d M Y', strtotime($u->getCreatedAt()) ?: time())) ?></small></div></div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
$title = 'Dashboard';
$activePage = 'dashboard';
require __DIR__ . '/layout_admin.php';
