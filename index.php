<?php
require_once __DIR__ . '/includes/init.php';

if (!is_logged_in()) {
    redirect('login.php');
}

redirect_by_role(current_user_role());