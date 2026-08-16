<?php
require_once 'includes/init.php';

header('Location: ' . (is_logged_in() ? 'beheer.php' : 'login.php'));
exit;
