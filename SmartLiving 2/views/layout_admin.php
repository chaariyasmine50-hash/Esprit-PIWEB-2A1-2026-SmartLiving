<?php
/**
 * views/layout_admin.php
 * Global HTML layout for the BACKOFFICE (role "admin" only).
 * Visually distinct from the frontoffice (dark sidebar, "Administration" label).
 * Expects: $title (string), $content (string), $activePage (string), $user (UserModel)
 */
require_once __DIR__ . '/partials/helpers.php';

$activePage = $activePage ?? '';
$modules = [
    ['alert', 'Incidents'], ['calendar', 'Green events'], ['chat', 'Forum'],
    ['gift', 'Rewards'], ['flag', 'Complaints'], ['heart', 'Donations'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Admin') ?> · Smart Living Admin</title>
    <link rel="icon" href="views/assets/img/logo.svg">
    <link rel="stylesheet" href="views/assets/css/admin.css">
    <script src="views/assets/js/validation.js" defer></script>
</head>
<body>

<aside class="side">
    <a href="index.php?action=admin" class="brand"><img src="views/assets/img/logo.svg" width="44" height="44" alt=""><div>Smart <span>Living</span><small>Administration</small></div></a>

    <p class="label">MAIN</p>
    <a href="index.php?action=admin" class="item <?= $activePage === 'dashboard' ? 'on' : '' ?>"><?= icon('grid') ?> Dashboard</a>

    <p class="label">SHARED</p>
    <a href="index.php?action=admin_users" class="item <?= $activePage === 'users' ? 'on' : '' ?>"><?= icon('users') ?> Users</a>

    <p class="label">MODULES</p>
    <?php foreach ($modules as [$ic, $label]): ?>
        <span class="item disabled" title="Module in progress"><?= icon($ic) ?> <?= e($label) ?><span class="count">soon</span></span>
    <?php endforeach; ?>

    <div class="foot">
        <a href="index.php?action=dashboard" class="item"><?= icon('home') ?> View website</a>
        <a href="index.php?action=logout" class="item"><?= icon('logout') ?> Log out</a>
    </div>
</aside>

<main>
    <div class="top">
        <form class="search" method="GET" action="index.php">
            <input type="hidden" name="action" value="admin_users">
            <?= icon('search', 18) ?>
            <input name="q" placeholder="Search users by name, username or email..." maxlength="100">
        </form>
        <div class="right">
            <span class="tag t-red">BACKOFFICE</span>
            <div class="me">
                <div class="av"><?= e(isset($user) && $user ? $user->getInitials() : 'AD') ?></div>
                <div><b><?= e(isset($user) && $user ? $user->getFullName() : ($_SESSION['username'] ?? '')) ?></b><small>Administrator</small></div>
            </div>
        </div>
    </div>

    <div class="content">
        <?= $content ?>
    </div>
</main>

</body>
</html>
