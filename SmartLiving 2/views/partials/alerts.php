<?php
/**
 * views/partials/alerts.php
 * Displays a list of error messages and/or a flash message.
 * Expects (optional): $alertErrors (array), $flash (['type' => ..., 'message' => ...])
 */
require_once __DIR__ . '/helpers.php';
?>
<?php if (!empty($flash)): ?>
    <div class="alert <?= $flash['type'] === 'success' ? 'alert-success' : 'alert-error' ?>">
        <?= icon($flash['type'] === 'success' ? 'check' : 'alert', 18) ?>
        <div><?= e($flash['message']) ?></div>
    </div>
<?php endif; ?>
<?php if (!empty($alertErrors)): ?>
    <div class="alert alert-error">
        <?= icon('alert', 18) ?>
        <div>
            <?php foreach ($alertErrors as $error): ?>
                <div><?= e($error) ?></div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>
