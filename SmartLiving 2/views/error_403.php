<?php
/**
 * views/error_403.php
 * Shown when a logged-in user tries to open a page reserved to another role.
 */
require_once __DIR__ . '/partials/helpers.php';
ob_start();
?>
<section class="block"><div class="container narrow center">
    <div class="err-code">403</div>
    <h1>Access denied</h1>
    <p class="muted">You do not have the required permissions to access this page.</p>
    <a href="index.php" class="btn btn-primary" style="margin-top:20px">Back to my space</a>
</div></section>
<?php
$content = ob_get_clean();
$title = 'Access denied - Smart Living';
require __DIR__ . '/layout.php';
