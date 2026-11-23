<?php
/**
 * views/admin_user_form.php
 * Backoffice: create a user ($editUser === null) or edit a user (incl. role management).
 * Expects: $user (UserModel, the admin), $editUser (?UserModel), $errors (array),
 *          $old (array), $csrfToken (string)
 */
require_once __DIR__ . '/partials/helpers.php';
ob_start();
$isEdit = $editUser !== null;
$old = $old ?? [];
$action = $isEdit ? 'index.php?action=admin_user_edit&amp;id=' . $editUser->getId() : 'index.php?action=admin_user_create';
$isMe = $isEdit && $editUser->getId() === (int) ($_SESSION['user_id'] ?? 0);
?>
<div class="ph">
    <div>
        <p class="crumb">Admin / Users / <?= $isEdit ? 'Edit' : 'New' ?></p>
        <h1><?= $isEdit ? 'Edit user' : 'Add a user' ?></h1>
        <p><?= $isEdit ? 'Update the account, its role and its access.' : 'Create an account and choose its role.' ?></p>
    </div>
    <a href="index.php?action=admin_users" class="btn btn-light">Back to list</a>
</div>

<div class="grid-form">
    <div class="panel">
        <h3><?= $isEdit ? e($editUser->getFullName()) : 'Account information' ?></h3>
        <p class="sub">All fields are required<?= $isEdit ? ', except the new password' : '' ?>.</p>

        <?php $alertErrors = $errors ?? []; $flash = null; require __DIR__ . '/partials/alerts.php'; ?>

        <form method="POST" action="<?= $action ?>" novalidate data-validate>
            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">

            <div class="row2">
                <div class="field">
                    <label for="first_name">First name</label>
                    <input id="first_name" name="first_name" value="<?= e($old['first_name'] ?? '') ?>" data-rules="required|name">
                    <small class="err"></small>
                </div>
                <div class="field">
                    <label for="last_name">Last name</label>
                    <input id="last_name" name="last_name" value="<?= e($old['last_name'] ?? '') ?>" data-rules="required|name">
                    <small class="err"></small>
                </div>
            </div>
            <div class="row2">
                <div class="field">
                    <label for="username">Username</label>
                    <input id="username" name="username" value="<?= e($old['username'] ?? '') ?>" data-rules="required|username">
                    <small class="err"></small>
                </div>
                <div class="field">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?= e($old['email'] ?? '') ?>" data-rules="required|email">
                    <small class="err"></small>
                </div>
            </div>
            <div class="row2">
                <div class="field">
                    <label for="role">Role</label>
                    <select id="role" name="role" data-rules="required" <?= $isMe ? 'disabled' : '' ?>>
                        <option value="user" <?= ($old['role'] ?? '') === 'user' ? 'selected' : '' ?>>User (citizen)</option>
                        <option value="admin" <?= ($old['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
                    </select>
                    <?php if ($isMe): ?><input type="hidden" name="role" value="admin"><?php endif; ?>
                    <small class="err"></small>
                </div>
                <div class="field">
                    <label for="status">Status</label>
                    <select id="status" name="status" data-rules="required" <?= $isMe ? 'disabled' : '' ?>>
                        <option value="active" <?= ($old['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="blocked" <?= ($old['status'] ?? '') === 'blocked' ? 'selected' : '' ?>>Blocked</option>
                    </select>
                    <?php if ($isMe): ?><input type="hidden" name="status" value="active"><?php endif; ?>
                    <small class="err"></small>
                </div>
            </div>
            <?php if ($isEdit): ?>
                <div class="field">
                    <label for="points">Green points</label>
                    <input id="points" name="points" inputmode="numeric" value="<?= e($old['points'] ?? '0') ?>" data-rules="required|int">
                    <small class="err"></small>
                </div>
            <?php endif; ?>

            <div class="row2">
                <div class="field">
                    <label for="password"><?= $isEdit ? 'New password (optional)' : 'Password' ?></label>
                    <input type="password" id="password" name="password" autocomplete="new-password" data-rules="<?= $isEdit ? 'password' : 'required|password' ?>">
                    <small class="err"></small>
                </div>
                <div class="field">
                    <label for="password_confirm">Confirm password</label>
                    <input type="password" id="password_confirm" name="password_confirm" autocomplete="new-password" data-rules="<?= $isEdit ? 'match:password' : 'required|match:password' ?>">
                    <small class="err"></small>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><?= icon('check', 18) ?> <?= $isEdit ? 'Save changes' : 'Create user' ?></button>
                <a href="index.php?action=admin_users" class="btn btn-light">Cancel</a>
            </div>
        </form>
    </div>

    <div class="panel">
        <h3>Roles and access</h3>
        <p class="sub">What each role can do</p>
        <div class="ai"><?= icon('user', 18) ?><div><b>User (citizen)</b><br>Front office only: personal space, profile, all citizen services.</div></div>
        <div class="ai" style="background:#FDECEC;color:#7F1D1D"><?= icon('shield', 18) ?><div><b>Admin</b><br>Back office: dashboard, user management, roles and every module.</div></div>
        <div class="ai" style="background:#FFF1E6;color:#7C2D12"><?= icon('ban', 18) ?><div><b>Blocked</b><br>The account still exists but cannot log in anymore.</div></div>
        <?php if ($isMe): ?>
            <p class="sub">You are editing your own account: you cannot change your role or block yourself.</p>
        <?php endif; ?>
    </div>
</div>
<?php
$content = ob_get_clean();
$title = $isEdit ? 'Edit user' : 'Add user';
$activePage = 'users';
require __DIR__ . '/layout_admin.php';
