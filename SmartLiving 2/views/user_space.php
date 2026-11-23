<?php
/**
 * views/user_space.php
 * Frontoffice home of a logged-in citizen.
 * Expects: $user (UserModel), $flash (?array)
 */
require_once __DIR__ . '/partials/helpers.php';
ob_start();

$modules = [
    ['alert',    '#E8EFFD', '#2563EB', 'Urban reporting', 'Report broken lights, potholes or waste with a photo and its exact location.'],
    ['calendar', '#E7F6EC', '#16A34A', 'Green events',    'Join clean-ups, tree planting and awareness days near you.'],
    ['chat',     '#F1EAFE', '#7C3AED', 'Community forum', 'Share ideas, ask questions and discuss the future of your neighbourhood.'],
    ['gift',     '#FFF7E6', '#B45309', 'Green rewards',   'Exchange your green points for local rewards and vouchers.'],
    ['flag',     '#FDECEC', '#DC2626', 'Complaints',      'Not satisfied with a service? File a complaint and get an official answer.'],
    ['heart',    '#FCE7F3', '#DB2777', 'Donations',       'Support green projects in your city: parks, trees, recycling points.'],
];
?>
<section class="page-head"><div class="container">
    <p class="crumbs">Home / My space</p>
    <h1>Welcome, <?= e($user->getFirstName() !== '' ? $user->getFirstName() : $user->getUsername()) ?> 👋</h1>
    <p>This is your personal space (frontoffice). From here you can manage your account and use every Smart Living service.</p>
</div></section>

<section class="block"><div class="container">
    <?php $alertErrors = []; require __DIR__ . '/partials/alerts.php'; ?>

    <div class="layout-side">
        <div>
            <div class="sec-head left"><span class="eyebrow">Services</span><h2>What do you want to do today?</h2></div>
            <div class="modules">
                <?php foreach ($modules as [$ic, $bg, $fg, $name, $desc]): ?>
                    <div class="module">
                        <div class="ic" style="background:<?= e($bg) ?>;color:<?= e($fg) ?>"><?= icon($ic, 26) ?></div>
                        <h3><?= e($name) ?></h3>
                        <p><?= e($desc) ?></p>
                        <span class="tag t-gray" style="align-self:flex-start">Coming soon</span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <aside class="side-col">
            <div class="card profile-card">
                <div class="avatar big"><?= e($user->getInitials()) ?></div>
                <h3><?= e($user->getFullName()) ?></h3>
                <p class="muted">@<?= e($user->getUsername()) ?></p>
                <span class="badge <?= $user->getRole() === 'admin' ? 'badge-admin' : 'badge-user' ?>">Role: <?= e(ucfirst($user->getRole())) ?></span>

                <table class="t mini">
                    <tr><th>ID</th><td><?= $user->getId() ?></td></tr>
                    <tr><th>Email</th><td><?= e($user->getEmail()) ?></td></tr>
                    <tr><th>Member since</th><td><?= e(date('d M Y', strtotime($user->getCreatedAt()) ?: time())) ?></td></tr>
                </table>
                <a href="index.php?action=profile" class="btn btn-outline btn-block"><?= icon('edit', 16) ?> Edit my profile</a>
            </div>

            <div class="points-card">
                <span>Your green points</span>
                <b><?= $user->getPoints() ?></b>
                <small>Earn points by reporting problems and joining green events.</small>
            </div>
        </aside>
    </div>
</div></section>
<?php
$content = ob_get_clean();
$title = 'My space - Smart Living';
$activePage = 'dashboard';
require __DIR__ . '/layout.php';
