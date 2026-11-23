<?php
/**
 * views/login.php
 * Expects: $errors (array), $old (array), $csrfToken (string)
 */
require_once __DIR__ . '/partials/helpers.php';
ob_start();
$old = $old ?? [];
$alertErrors = $errors ?? [];
?>
<section class="auth">
    <div class="art">
        <span class="eyebrow">Welcome back</span>
        <h2>Your city is waiting for you.</h2>
        <p>Log in to report problems, join green events and follow everything happening in your neighbourhood.</p>
        <ul class="art-list">
            <li><?= icon('camera', 18) ?> Report a problem in seconds</li>
            <li><?= icon('calendar', 18) ?> Join green events near you</li>
            <li><?= icon('gift', 18) ?> Earn green points and rewards</li>
        </ul>
    </div>

    <div class="formside"><div class="box">
        <div class="tabs"><a href="index.php?action=login" class="on">Log in</a><a href="index.php?action=register">Sign up</a></div>
        <h1 class="auth-title">Log in</h1>
        <p class="auth-sub">Enter your email and password to access your space.</p>

        <?php require __DIR__ . '/partials/alerts.php'; ?>

        <?php if (!empty($_GET['registered'])): ?>
            <div class="alert alert-success"><?= icon('check', 18) ?><div>Account created successfully, you can now log in.</div></div>
        <?php endif; ?>
        <?php if (!empty($_GET['deleted'])): ?>
            <div class="alert alert-success"><?= icon('check', 18) ?><div>Your account has been deleted.</div></div>
        <?php endif; ?>

        <form method="POST" action="index.php?action=login" novalidate data-validate>
            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">

            <div class="field">
                <label for="email">Email address</label>
                <input type="email" id="email" name="email" autocomplete="email" placeholder="you@example.com"
                       value="<?= e($old['email'] ?? '') ?>" data-rules="required|email">
                <small class="err"></small>
            </div>

            <div class="field">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" autocomplete="current-password" placeholder="Your password"
                       data-rules="required">
                <small class="err"></small>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Log in <?= icon('arrow', 18) ?></button>
        </form>

        <div class="switch-link">Don't have an account yet? <a href="index.php?action=register">Sign up</a></div>
    </div></div>
</section>
<?php
$content = ob_get_clean();
$title = 'Log in - Smart Living';
require __DIR__ . '/layout.php';
