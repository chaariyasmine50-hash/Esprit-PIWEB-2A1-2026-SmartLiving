<?php
/**
 * views/layout.php
 * Global HTML layout for the FRONTOFFICE (citizens - role "user")
 * Expects: $title (string), $content (string, already generated/escaped)
 * Optional: $activePage (string)
 */
require_once __DIR__ . '/partials/helpers.php';

$isLogged   = !empty($_SESSION['user_id']);
$isAdmin    = ($_SESSION['role'] ?? '') === 'admin';
$activePage = $activePage ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Smart Living') ?></title>
    <link rel="icon" href="views/assets/img/logo.svg">
    <link rel="stylesheet" href="views/assets/css/style.css">
    <script src="views/assets/js/validation.js" defer></script>
</head>
<body>

<div class="topbar"><div class="container">
    <span><?= icon('pin', 13) ?> Smart city platform for Tunisian municipalities</span>
    <span><?= icon('mail', 13) ?> contact@smartliving.tn &nbsp;·&nbsp; <?= icon('phone', 13) ?> +216 71 000 000</span>
</div></div>

<header class="nav"><div class="container">
    <a href="index.php" class="brand"><img src="views/assets/img/logo.svg" width="42" height="42" alt="">Smart <span>Living</span></a>

    <nav class="menu">
        <?php if ($isLogged): ?>
            <a href="index.php?action=dashboard" class="<?= $activePage === 'dashboard' ? 'active' : '' ?>">Home</a>
            <a href="index.php?action=profile" class="<?= $activePage === 'profile' ? 'active' : '' ?>">My profile</a>
            <?php if ($isAdmin): ?>
                <a href="index.php?action=admin">Admin panel</a>
            <?php endif; ?>
        <?php endif; ?>
    </nav>

    <div class="nav-actions">
        <?php if ($isLogged): ?>
            <a href="index.php?action=profile" class="user-chip">
                <span class="avatar"><?= e(strtoupper(mb_substr($_SESSION['username'] ?? '?', 0, 1))) ?></span>
                <span><?= e($_SESSION['username'] ?? '') ?></span>
            </a>
            <a href="index.php?action=logout" class="btn btn-outline btn-sm"><?= icon('logout', 16) ?> Log out</a>
        <?php else: ?>
            <a href="index.php?action=login" class="btn btn-outline btn-sm">Log in</a>
            <a href="index.php?action=register" class="btn btn-primary btn-sm">Sign up</a>
        <?php endif; ?>
    </div>
</div></header>

<main>
    <?= $content ?>
</main>

<footer class="site"><div class="container">
    <div class="cols">
        <div>
            <a href="index.php" class="brand" style="color:#fff"><img src="views/assets/img/logo.svg" width="34" height="34" alt="">Smart <span style="color:#5EEAD4">Living</span></a>
            <p style="margin-top:14px;max-width:320px">A Smarter City, A Better Life. One platform where citizens, the municipality and local partners make the city cleaner and safer together.</p>
            <div class="sdg"><span>SDG 11</span><span>SDG 9</span><span>SDG 13</span><span>SDG 8</span></div>
        </div>
        <div><h4>Platform</h4><a href="index.php?action=dashboard">Urban reporting</a><a href="index.php?action=dashboard">Green events</a><a href="index.php?action=dashboard">Community forum</a><a href="index.php?action=dashboard">Green rewards</a></div>
        <div><h4>Account</h4><a href="index.php?action=profile">My profile</a><a href="index.php?action=dashboard">Complaints</a><a href="index.php?action=dashboard">Donations</a></div>
        <div><h4>Contact</h4><a href="#">contact@smartliving.tn</a><a href="#">+216 71 000 000</a><a href="#">Tunis, Tunisia</a></div>
    </div>
    <div class="bottom"><span>© 2026 Smart Living. All rights reserved.</span><span>Esprit School of Engineering · PIWEB 2A</span></div>
</div></footer>

</body>
</html>
