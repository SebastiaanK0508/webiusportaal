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
    if (empty($result)) {
        return $default_url;
    }
    // Een opgeslagen pad kan verwijzen naar een bestand dat niet meer op deze
    // server staat (bijv. een lokale checkout zonder de uploads van productie,
    // of een handmatig verwijderd bestand) — val dan terug op de placeholder
    // i.p.v. een kapotte afbeelding te tonen die alleen de alt-tekst laat zien.
    if (!preg_match('#^https?://#i', $result) && !is_file($result)) {
        return $default_url;
    }
    return htmlspecialchars($result);
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

function get_footer_links($kolom)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM footer_links WHERE website_id = ? AND kolom = ? ORDER BY sort_order ASC, created_at ASC");
    $stmt->execute([current_website_id(), $kolom]);
    return $stmt->fetchAll();
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

// De vijf site-specifieke modules. Wijzig hier de lijst wanneer er ooit een
// module bijkomt/vervalt — super_websites.php en de migraties lezen deze
// sleutels niet dynamisch uit, dus die moeten in dat geval mee-updaten.
// De vaste paginaset van debandijk, gebruikt door footerbeheer.php om bij
// het aanmaken van een footer-link een pagina te laten kiezen i.p.v. een pad
// te laten intypen — het pad en het icoon (zelfde iconenset als de
// hoofdnavigatie, zie ICON_KEY_MAP in debandijk/footer.php) vullen dan
// automatisch mee. 'custom' is geen echte pagina maar de vluchtoptie voor
// een link die hier niet in staat (bijv. een pagina-anker of externe URL).
const FOOTER_LINK_PAGES = [
    'index.php' => ['label' => 'Home', 'icon' => 'home'],
    'index.php#openingstijden' => ['label' => 'Openingstijden (op homepage)', 'icon' => 'clock'],
    '#nieuws' => ['label' => 'Nieuws-sectie (op homepage)', 'icon' => 'newspaper'],
    'nieuws.php' => ['label' => 'Nieuws pagina', 'icon' => 'newspaper'],
    'assortiment.php' => ['label' => 'Assortiment', 'icon' => 'cart'],
    'cadeaukaarten.php' => ['label' => 'Cadeaukaarten', 'icon' => 'gift'],
    'contact.php' => ['label' => 'Contact', 'icon' => 'chat'],
    'about.php' => ['label' => 'Over Ons', 'icon' => 'people'],
    'geschiedenis.php' => ['label' => 'Geschiedenis', 'icon' => 'book'],
    'prijsvraag.php' => ['label' => 'Prijsvraag', 'icon' => 'trophy'],
    'services/pasfotos.php' => ['label' => "Services: Pasfoto's", 'icon' => 'camera'],
    'services/postnl.php' => ['label' => 'Services: PostNL', 'icon' => 'package'],
    'services/rdw.php' => ['label' => 'Services: RDW', 'icon' => 'check-circle'],
    'services/geldmaat.php' => ['label' => 'Services: Geldmaat', 'icon' => 'cash'],
];

const AVAILABLE_MODULES = [
    'assortiment'   => 'Assortiment',
    'cadeaukaarten' => 'Cadeaukaarten',
    'nieuws'        => 'Nieuws',
    'prijsvraag'    => 'Prijsvraag',
    'geschiedenis'  => 'Geschiedenis',
];

function module_enabled(string $website_id, string $module_key): bool
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT is_enabled FROM website_modules WHERE website_id = ? AND module_key = ?");
    $stmt->execute([$website_id, $module_key]);
    return (int)$stmt->fetchColumn() === 1;
}

function get_enabled_modules(string $website_id): array
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT module_key FROM website_modules WHERE website_id = ? AND is_enabled = 1");
    $stmt->execute([$website_id]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// Zorgt dat een website precies de 5 module-rijen heeft (nieuw aangemaakte
// sites hadden ze nog niet). Bestaande rijen blijven ongemoeid.
function ensure_website_modules(string $website_id): void
{
    global $pdo;
    $stmt = $pdo->prepare("INSERT IGNORE INTO website_modules (website_id, module_key, is_enabled) VALUES (?, ?, 0)");
    foreach (array_keys(AVAILABLE_MODULES) as $key) {
        $stmt->execute([$website_id, $key]);
    }
}

function set_website_modules(string $website_id, array $enabled_keys): void
{
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO website_modules (website_id, module_key, is_enabled) VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE is_enabled = VALUES(is_enabled)");
    foreach (array_keys(AVAILABLE_MODULES) as $key) {
        $stmt->execute([$website_id, $key, in_array($key, $enabled_keys, true) ? 1 : 0]);
    }
}

// Gate voor de vijf module-beheerpagina's: server-side check, onafhankelijk
// van of de navlink al dan niet zichtbaar is.
function require_module(string $website_id, string $module_key): void
{
    if (!module_enabled($website_id, $module_key)) {
        http_response_code(403);
        exit('Deze module is niet ingeschakeld voor deze website.');
    }
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
