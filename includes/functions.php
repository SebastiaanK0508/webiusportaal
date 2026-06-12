<?php
require_once 'db_config.php';

function get_text($section_key) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT content_text FROM site_content WHERE section_key = ?");
    $stmt->execute([$section_key]);
    $result = $stmt->fetchColumn();
    return $result ? htmlspecialchars($result) : '';
}

function get_image($section_key, $default_url) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT content_text FROM site_content WHERE section_key = ?");
    $stmt->execute([$section_key]);
    $result = $stmt->fetchColumn();
    return !empty($result) ? htmlspecialchars($result) : $default_url;
}

function is_visible($section_key) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT is_visible FROM site_content WHERE section_key = ?");
    $stmt->execute([$section_key]);
    $result = $stmt->fetchColumn();
    return (int)$result === 1;
}

function get_portfolio() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM portfolio ORDER BY created_at DESC");
    return $stmt->fetchAll();
}

function get_services() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM services ORDER BY uuid ASC");
    return $stmt->fetchAll();
}

function get_usps() {
    global $pdo;
    return $pdo->query("SELECT * FROM usps ORDER BY created_at ASC")->fetchAll();
}

function get_reviews() {
    global $pdo;
    return $pdo->query("SELECT * FROM reviews ORDER BY created_at ASC")->fetchAll();
}

function get_faqs() {
    global $pdo;
    return $pdo->query("SELECT * FROM faqs ORDER BY created_at ASC")->fetchAll();
}

function get_categories_with_products() {
    global $pdo; 
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY sort_order ASC, name ASC");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($categories as $key => $category) {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE category_id = ? ORDER BY sort_order ASC, title ASC");
        $stmt->execute([$category['uuid']]);
        $categories[$key]['products'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    return $categories;
}

function get_categories() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY sort_order ASC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_all_products_admin() {
    global $pdo;
    $stmt = $pdo->query("
        SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.uuid 
        ORDER BY c.sort_order ASC, p.sort_order ASC
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_messages() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_legal($type) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM legals WHERE type = ?");
    $stmt->execute([$type]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function get_footer_setting($sleutel) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT waarde FROM footer WHERE sleutel = ?");
    $stmt->execute([$sleutel]);
    $resultaat = $stmt->fetchColumn();
    return $resultaat !== false ? $resultaat : '';
}

// --- NIEUWE FUNCTIES VOOR APP INSTELLINGEN ---
function get_app_setting($key, $default = '') {
    global $pdo;
    $stmt = $pdo->prepare("SELECT setting_value FROM app_settings WHERE setting_key = ?");
    $stmt->execute([$key]);
    $result = $stmt->fetchColumn();
    return $result !== false ? htmlspecialchars($result) : $default;
}

function is_toggled($key) {
    return get_app_setting($key, '0') === '1';
}
?>