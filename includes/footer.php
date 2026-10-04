</main>

<footer class="site-footer">
    <p>&copy; 2026 TutorBloom - PHP vanilla</p>
</footer>

<?php
if (!isset($loadGlobalScripts)) {
    $loadGlobalScripts = true;
}

if (!isset($extraScripts)) {
    $extraScripts = [];
}
?>

<?php if ($loadGlobalScripts): ?>
    <script src="<?= e(app_url('assets/js/jquery.min.js')) ?>"></script>
    <script src="<?= e(app_url('assets/js/app.js')) ?>"></script>
<?php endif; ?>

<?php if ($bodyId === 'home-page'): ?>
    <script src="<?= e(app_url('assets/js/index.js')) ?>"></script>
<?php endif; ?>

<?php if ($bodyId === 'profile-page'): ?>
    <script src="<?= e(app_url('assets/js/profile.js')) ?>"></script>
<?php endif; ?>

<?php foreach ($extraScripts as $scriptPath): ?>
    <script src="<?= e(app_url($scriptPath)) ?>"></script>
<?php endforeach; ?>

</body>
</html>