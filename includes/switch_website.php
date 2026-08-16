<?php
require_once __DIR__ . '/init.php';
require_role('super_admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../beheer.php');
    exit;
}

csrf_verify();

$website_id = trim($_POST['website_id'] ?? '');
$return_to = $_POST['return_to'] ?? '../beheer.php';

// Alleen een eigen, relatief pad toestaan als terugkeer-bestemming, om een
// open-redirect via een gemanipuleerde return_to-waarde te voorkomen.
$is_safe_return = $return_to !== ''
    && $return_to[0] === '/'
    && substr($return_to, 0, 2) !== '//'
    && strpos($return_to, ':') === false;
if (!$is_safe_return) {
    $return_to = '../beheer.php';
}

if ($website_id !== '') {
    $stmt = $pdo->prepare("SELECT id FROM websites WHERE id = ? AND is_active = 1");
    $stmt->execute([$website_id]);
    if ($stmt->fetchColumn()) {
        $_SESSION['website_id'] = $website_id;
    }
}

header('Location: ' . $return_to);
exit;
