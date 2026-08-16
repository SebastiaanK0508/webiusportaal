<?php
require_once __DIR__ . '/db_config.php';
require_once __DIR__ . '/auth.php';

// Alle functies hieronder werken impliciet op de website die in de sessie
// actief staat (current_website_id(), zie includes/auth.php). Elke pagina
// moet dus eerst require_website_context() aanroepen voordat deze functies
// zinnig resultaat geven.

function get_text($section_key)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT content_text FROM site_content WHERE website_id = ? AND section_key = ? LIMIT 1");
    $stmt->execute([current_website_id(), $section_key]);
    $result = $stmt->fetchColumn();
    return $result ? htmlspecialchars($result) : '';
}

function get_image($section_key, $default_url)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT content_text FROM site_content WHERE website_id = ? AND section_key = ? LIMIT 1");
    $stmt->execute([current_website_id(), $section_key]);
    $result = $stmt->fetchColumn();
    return !empty($result) ? htmlspecialchars($result) : $default_url;
}

function is_visible($section_key)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT is_visible FROM site_content WHERE website_id = ? AND section_key = ? LIMIT 1");
    $stmt->execute([current_website_id(), $section_key]);
    $result = $stmt->fetchColumn();
    return (int)$result === 1;
}

function get_portfolio()
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM portfolio WHERE website_id = ? ORDER BY created_at DESC");
    $stmt->execute([current_website_id()]);
    return $stmt->fetchAll();
}

function get_services()
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM services WHERE website_id = ? ORDER BY title ASC");
    $stmt->execute([current_website_id()]);
    return $stmt->fetchAll();
}

function get_usps()
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM usps WHERE website_id = ? ORDER BY created_at ASC");
    $stmt->execute([current_website_id()]);
    return $stmt->fetchAll();
}

function get_reviews()
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM reviews WHERE website_id = ? ORDER BY created_at ASC");
    $stmt->execute([current_website_id()]);
    return $stmt->fetchAll();
}

function get_faqs()
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM faqs WHERE website_id = ? ORDER BY created_at ASC");
    $stmt->execute([current_website_id()]);
    return $stmt->fetchAll();
}

function get_categories_with_products()
{
    global $pdo;
    $website_id = current_website_id();
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE website_id = ? ORDER BY sort_order ASC, name ASC");
    $stmt->execute([$website_id]);
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($categories as $key => $category) {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE website_id = ? AND category_id = ? ORDER BY sort_order ASC, title ASC");
        $stmt->execute([$website_id, $category['id']]);
        $categories[$key]['products'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    return $categories;
}

function get_categories()
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE website_id = ? ORDER BY sort_order ASC");
    $stmt->execute([current_website_id()]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_all_products_admin()
{
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT p.*, c.name as category_name
        FROM products p
        LEFT JOIN categories c ON c.id = p.category_id AND c.website_id = p.website_id
        WHERE p.website_id = ?
        ORDER BY c.sort_order ASC, p.sort_order ASC
    ");
    $stmt->execute([current_website_id()]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_messages()
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM contact_messages WHERE website_id = ? ORDER BY created_at DESC");
    $stmt->execute([current_website_id()]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_legal($type)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM legals WHERE website_id = ? AND type = ?");
    $stmt->execute([current_website_id(), $type]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function get_footer_setting($sleutel)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT waarde FROM footer WHERE website_id = ? AND sleutel = ?");
    $stmt->execute([current_website_id(), $sleutel]);
    $resultaat = $stmt->fetchColumn();
    return $resultaat !== false ? $resultaat : '';
}

function get_app_setting($key, $default = '')
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT setting_value FROM app_settings WHERE website_id = ? AND setting_key = ?");
    $stmt->execute([current_website_id(), $key]);
    $result = $stmt->fetchColumn();
    return $result !== false ? htmlspecialchars($result) : $default;
}

function is_toggled($key)
{
    return get_app_setting($key, '0') === '1';
}

function set_footer_setting($sleutel, $waarde)
{
    global $pdo;
    $website_id = current_website_id();
    $stmt = $pdo->prepare("SELECT id FROM footer WHERE website_id = ? AND sleutel = ?");
    $stmt->execute([$website_id, $sleutel]);
    $existing_id = $stmt->fetchColumn();
    if ($existing_id) {
        $pdo->prepare("UPDATE footer SET waarde = ? WHERE id = ?")->execute([$waarde, $existing_id]);
    } else {
        $pdo->prepare("INSERT INTO footer (id, website_id, sleutel, waarde) VALUES (UUID(), ?, ?, ?)")->execute([$website_id, $sleutel, $waarde]);
    }
}

function set_app_setting($key, $value)
{
    global $pdo;
    $website_id = current_website_id();
    $stmt = $pdo->prepare("SELECT id FROM app_settings WHERE website_id = ? AND setting_key = ?");
    $stmt->execute([$website_id, $key]);
    $existing_id = $stmt->fetchColumn();
    if ($existing_id) {
        $pdo->prepare("UPDATE app_settings SET setting_value = ? WHERE id = ?")->execute([$value, $existing_id]);
    } else {
        $pdo->prepare("INSERT INTO app_settings (id, website_id, setting_key, setting_value) VALUES (UUID(), ?, ?, ?)")->execute([$website_id, $key, $value]);
    }
}
