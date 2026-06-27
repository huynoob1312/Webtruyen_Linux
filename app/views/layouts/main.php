<?php
// File: app/views/layouts/main.php
require_once 'app/views/includes/header.php';
?>

<!-- START PAGE CONTENT -->
<?= isset($content) ? $content : '' ?>
<!-- END PAGE CONTENT -->

<?php
require_once 'app/views/includes/footer.php';
?>
