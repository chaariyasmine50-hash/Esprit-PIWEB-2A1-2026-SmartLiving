<?php
/**
 * views/profile.php
 * The citizen reads and updates his own account (frontoffice).
 * Expects: $user (UserModel), $errors, $passwordErrors, $deleteErrors (arrays),
 *          $old (array), $flash (?array), $csrfToken (string)
 */
require_once __DIR__ . '/partials/helpers.php';
ob_start();
$old = $old ?? [];
$val = fn(string $k, string $default) => $old[$k] ?? $default;
?>
<section class="page-head"><div class="container">
    <p class="crumbs">Home / My profile</p>
    <h1>My profile</h1>
    <p>Update your personal information, change your password or delete your account.</p>
</div></section>

<section class="block"><div class="container">
    <?php $alertErrors = []; require __DIR__ . '/partials/alerts.php'; ?>

    <div class="layout-2">
        <!-- ===== UPDATE PROFILE ===== -->
        <div class="card">
            <h3>Personal information</h3>
            <p class="sub">This information is visible to the municipality.</p>
            <?php $alertErrors = $errors ?? []; $flash = null; require __DIR__ . '/partials/alerts.php'; ?>

            <form method="POST" action="index.php?action=profile" novalidate data-validate>
                <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                <div class="form-row">
                    <div class="field">
                        <label for="first_name">First name</label>
                        <input type="text" id="first_name" name="first_name" value="<?= e($val('first_name', $user->getFirstName())) ?>" data-rules="required|name">
                        <small class="err"></small>
                    </div>
                    <div class="field">
                        <label for="last_name">Last name</label>
                        <input type="text" id="last_name" name="last_name" value="<?= e($val('last_name', $user->getLastName())) ?>" data-rules="required|name">
                        <small class="err"></small>
                    </div>
                </div>
                <div class="field">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" value="<?= e($val('username', $user->getUsername())) ?>" data-rules="required|username">
                    <small class="err"></small>
                </div>
                <div class="field">
                    <label for="email">Email address</label>
                    <input type="email" id="email" name="email" value="<?= e($val('email', $user->getEmail())) ?>" data-rules="required|email">
                    <small class="err"></small>
                </div>
                <div class="form-row">
                    <div class="field"><label>Role</label><input type="text" value="<?= e(ucfirst($user->getRole())) ?>" disabled></div>
                    <div class="field"><label>Green points</label><input type="text" value="<?= $user->getPoints() ?>" disabled></div>
                </div>
                <button type="submit" class="btn btn-primary"><?= icon('check', 18) ?> Save changes</button>
            </form>
        </div>

        <div>
            <!-- ===== CHANGE PASSWORD ===== -->
            <div class="card" id="password">
                <h3>Change password</h3>
                <p class="sub">Your password is stored encrypted (bcrypt).</p>
                <?php $alertErrors = $passwordErrors ?? []; require __DIR__ . '/partials/alerts.php'; ?>

                <form method="POST" action="index.php?action=password" novalidate data-validate>
                    <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                    <div class="field">
                        <label for="current_password">Current password</label>
                        <input type="password" id="current_password" name="current_password" autocomplete="current-password" data-rules="required">
                        <small class="err"></small>
                    </div>
                    <div class="field">
                        <label for="new_password">New password</label>
                        <input type="password" id="new_password" name="new_password" autocomplete="new-password" data-rules="required|password">
                        <small class="err"></small>
                    </div>
                    <div class="field">
                        <label for="new_password_confirm">Confirm new password</label>
                        <input type="password" id="new_password_confirm" name="new_password_confirm" autocomplete="new-password" data-rules="required|match:new_password">
                        <small class="err"></small>
                    </div>
                    <button type="submit" class="btn btn-outline"><?= icon('lock', 18) ?> Update password</button>
                </form>
            </div>

            <!-- ===== DELETE ACCOUNT ===== -->
            <div class="card danger" id="delete">
                <h3>Delete my account</h3>
                <p class="sub">This action is permanent. All your data will be deleted.</p>
                <?php $alertErrors = $deleteErrors ?? []; require __DIR__ . '/partials/alerts.php'; ?>

                <form method="POST" action="index.php?action=account_delete" novalidate data-validate
                      data-confirm="Are you sure you want to delete your account? This cannot be undone.">
                    <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                    <div class="field">
                        <label for="delete_password">Confirm with your password</label>
                        <input type="password" id="delete_password" name="password" autocomplete="current-password" data-rules="required">
                        <small class="err"></small>
                    </div>
                    <button type="submit" class="btn btn-danger"><?= icon('trash', 18) ?> Delete my account</button>
                </form>
            </div>
        </div>
    </div>
</div></section>
<?php
$content = ob_get_clean();
$title = 'My profile - Smart Living';
$activePage = 'profile';
require __DIR__ . '/layout.php';
