<?php
/**
 * views/register.php
 * Expects: $errors (array), $old (array of previous input values), $csrfToken (string)
 */
require_once __DIR__ . '/partials/helpers.php';
ob_start();
$old = $old ?? [];
$alertErrors = $errors ?? [];
?>
<section class="auth">
    <div class="art">
        <span class="eyebrow">Join Smart Living</span>
        <h2>A smarter city starts with you.</h2>
        <p>Create your free citizen account and help the municipality make your neighbourhood cleaner and safer.</p>
        <ul class="art-list">
            <li><?= icon('shield', 18) ?> Your password is stored encrypted</li>
            <li><?= icon('users', 18) ?> 3,500+ active citizens</li>
            <li><?= icon('leaf', 18) ?> Every action earns green points</li>
        </ul>
    </div>

    <div class="formside"><div class="box">
        <div class="tabs"><a href="index.php?action=login">Log in</a><a href="index.php?action=register" class="on">Sign up</a></div>
        <h1 class="auth-title">Create an account</h1>
        <p class="auth-sub">It takes less than a minute.</p>

        <?php require __DIR__ . '/partials/alerts.php'; ?>

        <form method="POST" action="index.php?action=register" novalidate data-validate>
            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">

            <div class="form-row">
                <div class="field">
                    <label for="first_name">First name</label>
                    <input type="text" id="first_name" name="first_name" autocomplete="given-name"
                           value="<?= e($old['first_name'] ?? '') ?>" data-rules="required|name">
                    <small class="err"></small>
                </div>
                <div class="field">
                    <label for="last_name">Last name</label>
                    <input type="text" id="last_name" name="last_name" autocomplete="family-name"
                           value="<?= e($old['last_name'] ?? '') ?>" data-rules="required|name">
                    <small class="err"></small>
                </div>
            </div>

            <div class="field">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" autocomplete="username" placeholder="e.g. yasmine_c"
                       value="<?= e($old['username'] ?? '') ?>" data-rules="required|username">
                <small class="err"></small>
            </div>

            <div class="field">
                <label for="email">Email address</label>
                <input type="email" id="email" name="email" autocomplete="email" placeholder="you@example.com"
                       value="<?= e($old['email'] ?? '') ?>" data-rules="required|email">
                <small class="err"></small>
            </div>

            <div class="form-row">
                <div class="field">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" autocomplete="new-password"
                           data-rules="required|password">
                    <small class="err"></small>
                </div>
                <div class="field">
                    <label for="password_confirm">Confirm password</label>
                    <input type="password" id="password_confirm" name="password_confirm" autocomplete="new-password"
                           data-rules="required|match:password">
                    <small class="err"></small>
                </div>
            </div>
            <p class="help">At least 8 characters, with at least one letter and one digit.</p>

            <button type="submit" class="btn btn-primary btn-block">Create my account <?= icon('arrow', 18) ?></button>
        </form>

        <div class="switch-link">Already have an account? <a href="index.php?action=login">Log in</a></div>
    </div></div>
</section>
<?php
$content = ob_get_clean();
$title = 'Sign up - Smart Living';
require __DIR__ . '/layout.php';
