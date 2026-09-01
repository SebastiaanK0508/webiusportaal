<?php
require_once 'includes/init.php';
$website_id = require_website_context();
require_module($website_id, 'geschiedenis');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ($_POST['csrf_token'] ?? '') === '' || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    http_response_code(403);
    echo json_encode(['error' => ['message' => 'Ongeldige of verlopen aanvraag.']]);
    exit;
}

try {
    $path = save_uploaded_image('upload', $website_id);
    if ($path === null) {
        throw new UploadException('Geen bestand ontvangen.');
    }
    echo json_encode(['url' => $path]);
} catch (UploadException $e) {
    http_response_code(400);
    echo json_encode(['error' => ['message' => $e->getMessage()]]);
}
