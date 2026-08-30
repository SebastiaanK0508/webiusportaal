<?php
require_once __DIR__ . '/session.php';

function current_user_id(): ?string
{
    return $_SESSION['user_id'] ?? null;
}

function current_role(): ?string
{
    return $_SESSION['role'] ?? null;
}

function current_website_id(): ?string
{
    return $_SESSION['website_id'] ?? null;
}

function is_super_admin(): bool
{
    return current_role() === 'super_admin';
}

function is_logged_in(): bool
{
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true && isset($_SESSION['user_id']);
}

function require_login(): void
{
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

function require_role(string ...$roles): void
{
    require_login();
    if (!in_array(current_role(), $roles, true)) {
        http_response_code(403);
        exit('Je hebt geen toegang tot deze pagina.');
    }
}

// Content-pagina's roepen dit aan: super_admins zonder gekozen website worden
// naar de siteswitcher gestuurd i.p.v. te crashen op een lege website_id.
function require_website_context(): string
{
    require_login();
    $wid = current_website_id();
    if (!$wid) {
        header('Location: super_websites.php?select=1');
        exit;
    }
    return $wid;
}

function current_user(): ?array
{
    static $user = null;
    static $loaded = false;
    if ($loaded) {
        return $user;
    }
    $loaded = true;
    global $pdo;
    if (!current_user_id() || !isset($pdo)) {
        return null;
    }
    $stmt = $pdo->prepare("SELECT id, website_id, role, username, email FROM users WHERE id = ? LIMIT 1");
    $stmt->execute([current_user_id()]);
    $user = $stmt->fetch() ?: null;
    return $user;
}

function current_website(): ?array
{
    static $site = null;
    static $loaded = false;
    if ($loaded) {
        return $site;
    }
    $loaded = true;
    global $pdo;
    $wid = current_website_id();
    if (!$wid || !isset($pdo)) {
        return null;
    }
    $stmt = $pdo->prepare("SELECT * FROM websites WHERE id = ? LIMIT 1");
    $stmt->execute([$wid]);
    $site = $stmt->fetch() ?: null;
    return $site;
}

// Bouwt de live URL naar een pagina op de publieke website van de huidige klant
// (elke klant heeft z'n eigen domain_name — dit mag dus nooit een relatief pad
// binnen het portaal zelf zijn, anders opent "Bekijk pagina" altijd dezelfde site).
function public_site_url(string $path = ''): string
{
    $site = current_website();
    $domain = trim($site['domain_name'] ?? '', "/ \t\n\r\0\x0B");
    if ($domain === '') {
        return '#';
    }
    return 'https://' . $domain . '/' . ltrim($path, '/');
}
