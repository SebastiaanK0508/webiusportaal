<?php
require_once 'includes/init.php';
require_role('super_admin');

const ALLOWED_TEMPLATE_KEYS = ['default'];
const ALLOWED_FONTS = ['Outfit', 'Inter', 'Montserrat', 'Poppins', 'Roboto', 'Lato', 'Nunito Sans', 'Playfair Display'];

function new_uuid(PDO $pdo): string
{
    return $pdo->query('SELECT UUID()')->fetchColumn();
}

function valid_hex_color(string $value): bool
{
    return $value === '' || preg_match('/^#[0-9a-fA-F]{6}$/', $value) === 1;
}

// Kloont de teksten-/footer-/legals-catalogus van een bestaande website als lege
// startpunten voor een nieuwe website, zodat websitebeheer.php niet volledig leeg is.
function provision_website(PDO $pdo, string $new_website_id): void
{
    $ref_stmt = $pdo->prepare("SELECT id FROM websites WHERE id != ? ORDER BY created_at ASC LIMIT 1");
    $ref_stmt->execute([$new_website_id]);
    $ref_id = $ref_stmt->fetchColumn();
    if (!$ref_id) {
        return;
    }

    $content_stmt = $pdo->prepare("SELECT page, section_key, type, label, group_name FROM site_content WHERE website_id = ?");
    $content_stmt->execute([$ref_id]);
    $insert = $pdo->prepare("INSERT INTO site_content (id, website_id, page, section_key, content_text, type, label, group_name, is_visible) VALUES (UUID(), ?, ?, ?, '', ?, ?, ?, 1)");
    foreach ($content_stmt->fetchAll() as $row) {
        $insert->execute([$new_website_id, $row['page'], $row['section_key'], $row['type'], $row['label'], $row['group_name']]);
    }

    $footer_stmt = $pdo->prepare("SELECT sleutel FROM footer WHERE website_id = ?");
    $footer_stmt->execute([$ref_id]);
    $footer_insert = $pdo->prepare("INSERT INTO footer (id, website_id, sleutel, waarde) VALUES (UUID(), ?, ?, '')");
    foreach ($footer_stmt->fetchAll() as $row) {
        $footer_insert->execute([$new_website_id, $row['sleutel']]);
    }

    $legal_insert = $pdo->prepare("INSERT INTO legals (id, website_id, type, title, content) VALUES (UUID(), ?, ?, '', '')");
    foreach (['voorwaarden', 'privacy', 'cookies'] as $type) {
        $legal_insert->execute([$new_website_id, $type]);
    }
}

$active_tab = $_GET['tab'] ?? 'websites';
$message = '';
$error = '';

// --- WEBSITE AANMAKEN ---
if (isset($_POST['action']) && $_POST['action'] === 'create_website') {
    csrf_verify();
    $domain = trim($_POST['domain_name'] ?? '');
    $company = trim($_POST['company_name'] ?? '');
    $template_key = in_array($_POST['template_key'] ?? '', ALLOWED_TEMPLATE_KEYS, true) ? $_POST['template_key'] : 'default';
    $font = in_array($_POST['font_family'] ?? '', ALLOWED_FONTS, true) ? $_POST['font_family'] : 'Outfit';
    $primary = trim($_POST['color_primary'] ?? '');
    $secondary = trim($_POST['color_secondary'] ?? '');
    $accent = trim($_POST['color_accent'] ?? '');

    if ($domain === '' || $company === '') {
        $error = "Domeinnaam en bedrijfsnaam zijn verplicht.";
    } elseif (!valid_hex_color($primary) || !valid_hex_color($secondary) || !valid_hex_color($accent)) {
        $error = "Kleuren moeten een geldige hexcode zijn (bijv. #db2777).";
    } else {
        $new_id = new_uuid($pdo);
        try {
            $logo_path = save_uploaded_image('logo', $new_id);
        } catch (UploadException $e) {
            $logo_path = null;
            $error = $e->getMessage();
        }
        if (!$error) {
            $stmt = $pdo->prepare("INSERT INTO websites (id, domain_name, company_name, template_key, logo_path, color_primary, color_secondary, color_accent, font_family, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");
            $stmt->execute([$new_id, $domain, $company, $template_key, $logo_path, $primary ?: null, $secondary ?: null, $accent ?: null, $font]);
            provision_website($pdo, $new_id);
            set_website_modules($new_id, $_POST['modules'] ?? []);
            $message = "Website '{$company}' is aangemaakt.";
        }
    }
}

// --- WEBSITE BIJWERKEN ---
if (isset($_POST['action']) && $_POST['action'] === 'update_website') {
    csrf_verify();
    $id = $_POST['website_id'] ?? '';
    $domain = trim($_POST['domain_name'] ?? '');
    $company = trim($_POST['company_name'] ?? '');
    $template_key = in_array($_POST['template_key'] ?? '', ALLOWED_TEMPLATE_KEYS, true) ? $_POST['template_key'] : 'default';
    $font = in_array($_POST['font_family'] ?? '', ALLOWED_FONTS, true) ? $_POST['font_family'] : 'Outfit';
    $primary = trim($_POST['color_primary'] ?? '');
    $secondary = trim($_POST['color_secondary'] ?? '');
    $accent = trim($_POST['color_accent'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if ($domain === '' || $company === '') {
        $error = "Domeinnaam en bedrijfsnaam zijn verplicht.";
    } elseif (!valid_hex_color($primary) || !valid_hex_color($secondary) || !valid_hex_color($accent)) {
        $error = "Kleuren moeten een geldige hexcode zijn (bijv. #db2777).";
    } else {
        try {
            $new_logo = save_uploaded_image('logo', $id);
        } catch (UploadException $e) {
            $new_logo = null;
            $error = $e->getMessage();
        }
        if (!$error) {
            if ($new_logo !== null) {
                $old = $pdo->prepare("SELECT logo_path FROM websites WHERE id = ?");
                $old->execute([$id]);
                delete_uploaded_file($old->fetchColumn() ?: null);
                $pdo->prepare("UPDATE websites SET logo_path = ? WHERE id = ?")->execute([$new_logo, $id]);
            }
            $stmt = $pdo->prepare("UPDATE websites SET domain_name = ?, company_name = ?, template_key = ?, color_primary = ?, color_secondary = ?, color_accent = ?, font_family = ?, is_active = ? WHERE id = ?");
            $stmt->execute([$domain, $company, $template_key, $primary ?: null, $secondary ?: null, $accent ?: null, $font, $is_active, $id]);
            set_website_modules($id, $_POST['modules'] ?? []);
            $message = "Website bijgewerkt.";
        }
    }
}

// --- GEBRUIKER AANMAKEN ---
if (isset($_POST['action']) && $_POST['action'] === 'create_user') {
    csrf_verify();
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = in_array($_POST['role'] ?? '', ['super_admin', 'client'], true) ? $_POST['role'] : 'client';
    $user_website_id = $_POST['user_website_id'] ?? '';

    if ($username === '' || $email === '' || strlen($password) < 8) {
        $error = "Gebruikersnaam, e-mail zijn verplicht en het wachtwoord moet minimaal 8 tekens zijn.";
    } elseif ($role === 'client' && $user_website_id === '') {
        $error = "Kies een website voor een client-account.";
    } else {
        $exists = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $exists->execute([$username]);
        if ($exists->fetchColumn()) {
            $error = "Deze gebruikersnaam bestaat al.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO users (id, website_id, role, username, email, password_hash, is_active) VALUES (UUID(), ?, ?, ?, ?, ?, 1)");
            $stmt->execute([
                $role === 'client' ? $user_website_id : null,
                $role,
                $username,
                $email,
                password_hash($password, PASSWORD_DEFAULT),
            ]);
            $message = "Gebruiker '{$username}' is aangemaakt.";
        }
    }
}

// --- GEBRUIKER ACTIVEREN/DEACTIVEREN ---
if (isset($_POST['action']) && $_POST['action'] === 'toggle_user') {
    csrf_verify();
    $id = $_POST['user_id'] ?? '';
    if ($id === current_user_id()) {
        $error = "Je kunt je eigen account niet deactiveren.";
    } else {
        $pdo->prepare("UPDATE users SET is_active = 1 - is_active WHERE id = ?")->execute([$id]);
        $message = "Gebruikersstatus bijgewerkt.";
    }
}

// --- GEBRUIKER VERWIJDEREN ---
if (isset($_POST['action']) && $_POST['action'] === 'delete_user') {
    csrf_verify();
    $id = $_POST['user_id'] ?? '';
    if ($id === current_user_id()) {
        $error = "Je kunt je eigen account niet verwijderen.";
    } else {
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
        $message = "Gebruiker verwijderd.";
    }
}

$websites = $pdo->query("SELECT * FROM websites ORDER BY company_name ASC")->fetchAll();
$website_modules_map = [];
foreach ($pdo->query("SELECT website_id, module_key FROM website_modules WHERE is_enabled = 1")->fetchAll() as $row) {
    $website_modules_map[$row['website_id']][] = $row['module_key'];
}
$users = $pdo->query("
    SELECT u.*, w.company_name
    FROM users u
    LEFT JOIN websites w ON w.id = u.website_id
    ORDER BY u.username ASC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <title>Klantwebsites - Webius Portaal</title>
    <?php include 'includes/head.php'; ?>
</head>
<body class="bg-gray-100 font-sans pb-24">
    <?php include 'includes/header.php'; ?>
    <div class="max-w-6xl mx-auto px-4 mt-8">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Klantwebsites Beheren</h1>
            <p class="text-gray-500 mt-1">Voeg klantwebsites toe en beheer wie er toegang toe heeft. Alleen zichtbaar voor super admins.</p>
        </div>

        <?php if (isset($_GET['select'])): ?>
            <div class="bg-blue-50 border-l-4 border-blue-500 text-blue-800 p-4 mb-6 shadow-sm rounded-r-lg flex items-center gap-3">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="font-medium">Kies eerst een website via de schakelaar hierboven om verder te gaan, of maak hieronder een nieuwe website aan.</span>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 shadow-sm rounded-r-lg"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($message): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 shadow-sm rounded-r-lg"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <div class="border-b border-gray-200 mb-6 bg-white rounded-t-xl shadow-sm overflow-x-auto">
            <nav class="-mb-px flex space-x-6 px-4">
                <?php $tabs = ['websites' => 'Websites', 'gebruikers' => 'Gebruikers']; foreach ($tabs as $key => $label): $is_active = ($active_tab === $key); ?>
                <a href="super_websites.php?tab=<?php echo $key; ?>" class="whitespace-nowrap py-4 px-1 border-b-2 font-bold text-sm <?php echo $is_active ? 'border-pink-600 text-pink-600' : 'border-transparent text-gray-500 hover:text-gray-700 font-medium'; ?>"><?php echo $label; ?></a>
                <?php endforeach; ?>
            </nav>
        </div>

        <!-- WEBSITES TAB -->
        <div class="<?php echo $active_tab !== 'websites' ? 'hidden' : ''; ?>">
            <div class="grid lg:grid-cols-3 gap-8">
                <div class="lg:col-span-1 bg-white p-6 rounded-xl shadow-sm border border-gray-200 h-fit">
                    <h2 class="text-lg font-bold mb-4">Nieuwe Website</h2>
                    <form method="POST" action="super_websites.php?tab=websites" enctype="multipart/form-data" class="space-y-4">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="action" value="create_website">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Domeinnaam</label>
                            <input type="text" name="domain_name" required placeholder="klant.nl" class="w-full border-gray-300 rounded-md border p-2 focus:border-pink-500 focus:ring-pink-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Bedrijfsnaam</label>
                            <input type="text" name="company_name" required placeholder="Bedrijfsnaam klant" class="w-full border-gray-300 rounded-md border p-2 focus:border-pink-500 focus:ring-pink-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Sjabloon</label>
                            <select name="template_key" class="w-full border-gray-300 rounded-md border p-2 bg-white">
                                <?php foreach (ALLOWED_TEMPLATE_KEYS as $tk): ?>
                                    <option value="<?php echo htmlspecialchars($tk); ?>"><?php echo htmlspecialchars($tk); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Lettertype</label>
                            <select name="font_family" class="w-full border-gray-300 rounded-md border p-2 bg-white">
                                <?php foreach (ALLOWED_FONTS as $f): ?>
                                    <option value="<?php echo htmlspecialchars($f); ?>"><?php echo htmlspecialchars($f); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="grid grid-cols-3 gap-2">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Primair</label>
                                <input type="color" name="color_primary" value="#db2777" class="w-full h-10 border-gray-300 rounded-md border">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Secundair</label>
                                <input type="color" name="color_secondary" value="#fce7f3" class="w-full h-10 border-gray-300 rounded-md border">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Accent</label>
                                <input type="color" name="color_accent" value="#1f2937" class="w-full h-10 border-gray-300 rounded-md border">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Logo (optioneel)</label>
                            <input type="file" name="logo" accept="image/jpeg,image/png,image/webp,image/gif" class="w-full text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Modules (site-specifieke pagina's)</label>
                            <div class="grid grid-cols-2 gap-2">
                                <?php foreach (AVAILABLE_MODULES as $key => $label): ?>
                                    <label class="inline-flex items-center gap-2 cursor-pointer text-sm">
                                        <input type="checkbox" name="modules[]" value="<?php echo htmlspecialchars($key); ?>" class="rounded border-gray-300 text-pink-600 focus:ring-pink-500">
                                        <?php echo htmlspecialchars($label); ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <button type="submit" class="w-full bg-pink-600 text-white font-bold py-2.5 px-4 rounded-lg hover:bg-pink-700 transition-colors">Website Toevoegen</button>
                    </form>
                </div>

                <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                    <h2 class="text-lg font-bold mb-4">Bestaande Websites</h2>
                    <?php if (empty($websites)): ?>
                        <p class="text-gray-500 italic">Nog geen websites aangemaakt.</p>
                    <?php else: ?>
                        <div class="space-y-4">
                            <?php foreach ($websites as $w): ?>
                                <details class="border border-gray-200 rounded-lg">
                                    <summary class="cursor-pointer list-none px-4 py-3 flex items-center justify-between gap-3">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <?php if (!empty($w['logo_path'])): ?>
                                                <img src="<?php echo htmlspecialchars($w['logo_path']); ?>" class="w-8 h-8 rounded object-cover border border-gray-200 flex-shrink-0">
                                            <?php endif; ?>
                                            <div class="min-w-0">
                                                <div class="font-bold text-gray-800 truncate"><?php echo htmlspecialchars($w['company_name']); ?></div>
                                                <div class="text-xs text-gray-500 truncate"><?php echo htmlspecialchars($w['domain_name']); ?></div>
                                            </div>
                                        </div>
                                        <span class="text-xs font-bold px-2 py-1 rounded-full flex-shrink-0 <?php echo $w['is_active'] ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'; ?>"><?php echo $w['is_active'] ? 'Actief' : 'Inactief'; ?></span>
                                    </summary>
                                    <div class="border-t border-gray-100 p-4">
                                        <form method="POST" action="super_websites.php?tab=websites" enctype="multipart/form-data" class="space-y-4">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="action" value="update_website">
                                            <input type="hidden" name="website_id" value="<?php echo htmlspecialchars($w['id']); ?>">
                                            <div class="grid md:grid-cols-2 gap-4">
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">Domeinnaam</label>
                                                    <input type="text" name="domain_name" value="<?php echo htmlspecialchars($w['domain_name']); ?>" required class="w-full border-gray-300 rounded-md border p-2">
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">Bedrijfsnaam</label>
                                                    <input type="text" name="company_name" value="<?php echo htmlspecialchars($w['company_name']); ?>" required class="w-full border-gray-300 rounded-md border p-2">
                                                </div>
                                            </div>
                                            <div class="grid md:grid-cols-2 gap-4">
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">Sjabloon</label>
                                                    <select name="template_key" class="w-full border-gray-300 rounded-md border p-2 bg-white">
                                                        <?php foreach (ALLOWED_TEMPLATE_KEYS as $tk): ?>
                                                            <option value="<?php echo htmlspecialchars($tk); ?>" <?php echo $w['template_key'] === $tk ? 'selected' : ''; ?>><?php echo htmlspecialchars($tk); ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">Lettertype</label>
                                                    <select name="font_family" class="w-full border-gray-300 rounded-md border p-2 bg-white">
                                                        <?php foreach (ALLOWED_FONTS as $f): ?>
                                                            <option value="<?php echo htmlspecialchars($f); ?>" <?php echo $w['font_family'] === $f ? 'selected' : ''; ?>><?php echo htmlspecialchars($f); ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="grid grid-cols-3 gap-2">
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-700 mb-1">Primair</label>
                                                    <input type="color" name="color_primary" value="<?php echo htmlspecialchars($w['color_primary'] ?: '#db2777'); ?>" class="w-full h-10 border-gray-300 rounded-md border">
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-700 mb-1">Secundair</label>
                                                    <input type="color" name="color_secondary" value="<?php echo htmlspecialchars($w['color_secondary'] ?: '#fce7f3'); ?>" class="w-full h-10 border-gray-300 rounded-md border">
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-700 mb-1">Accent</label>
                                                    <input type="color" name="color_accent" value="<?php echo htmlspecialchars($w['color_accent'] ?: '#1f2937'); ?>" class="w-full h-10 border-gray-300 rounded-md border">
                                                </div>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Nieuw logo uploaden (optioneel)</label>
                                                <input type="file" name="logo" accept="image/jpeg,image/png,image/webp,image/gif" class="w-full text-sm">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Modules (site-specifieke pagina's)</label>
                                                <div class="grid grid-cols-2 gap-2">
                                                    <?php $w_modules = $website_modules_map[$w['id']] ?? []; ?>
                                                    <?php foreach (AVAILABLE_MODULES as $key => $label): ?>
                                                        <label class="inline-flex items-center gap-2 cursor-pointer text-sm">
                                                            <input type="checkbox" name="modules[]" value="<?php echo htmlspecialchars($key); ?>" <?php echo in_array($key, $w_modules, true) ? 'checked' : ''; ?> class="rounded border-gray-300 text-pink-600 focus:ring-pink-500">
                                                            <?php echo htmlspecialchars($label); ?>
                                                        </label>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                                <input type="checkbox" name="is_active" value="1" <?php echo $w['is_active'] ? 'checked' : ''; ?> class="rounded border-gray-300 text-pink-600 focus:ring-pink-500">
                                                <span class="text-sm font-medium text-gray-700">Actief (zichtbaar in de siteswitcher)</span>
                                            </label>
                                            <div>
                                                <button type="submit" class="bg-slate-900 text-white font-bold py-2 px-6 rounded-lg hover:bg-slate-800 transition-colors">Opslaan</button>
                                            </div>
                                        </form>
                                    </div>
                                </details>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- GEBRUIKERS TAB -->
        <div class="<?php echo $active_tab !== 'gebruikers' ? 'hidden' : ''; ?>">
            <div class="grid lg:grid-cols-3 gap-8">
                <div class="lg:col-span-1 bg-white p-6 rounded-xl shadow-sm border border-gray-200 h-fit">
                    <h2 class="text-lg font-bold mb-4">Nieuwe Gebruiker</h2>
                    <form method="POST" action="super_websites.php?tab=gebruikers" class="space-y-4">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="action" value="create_user">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Gebruikersnaam</label>
                            <input type="text" name="username" required class="w-full border-gray-300 rounded-md border p-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">E-mailadres</label>
                            <input type="email" name="email" required class="w-full border-gray-300 rounded-md border p-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tijdelijk wachtwoord</label>
                            <input type="password" name="password" required minlength="8" class="w-full border-gray-300 rounded-md border p-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Rol</label>
                            <select name="role" id="role-select" class="w-full border-gray-300 rounded-md border p-2 bg-white" onchange="document.getElementById('website-select-wrap').classList.toggle('hidden', this.value === 'super_admin')">
                                <option value="client">Client (beheert 1 website)</option>
                                <option value="super_admin">Super Admin (beheert alle websites)</option>
                            </select>
                        </div>
                        <div id="website-select-wrap">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Website</label>
                            <select name="user_website_id" class="w-full border-gray-300 rounded-md border p-2 bg-white">
                                <option value="">-- kies een website --</option>
                                <?php foreach ($websites as $w): ?>
                                    <option value="<?php echo htmlspecialchars($w['id']); ?>"><?php echo htmlspecialchars($w['company_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="w-full bg-pink-600 text-white font-bold py-2.5 px-4 rounded-lg hover:bg-pink-700 transition-colors">Gebruiker Toevoegen</button>
                    </form>
                </div>

                <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                    <h2 class="text-lg font-bold mb-4">Bestaande Gebruikers</h2>
                    <div class="overflow-x-auto border rounded-lg border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Gebruiker</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Rol</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Website</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Actie</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <?php foreach ($users as $u): ?>
                                    <tr>
                                        <td class="px-4 py-3">
                                            <div class="font-medium text-gray-900"><?php echo htmlspecialchars($u['username']); ?></div>
                                            <div class="text-xs text-gray-500"><?php echo htmlspecialchars($u['email']); ?></div>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-600"><?php echo $u['role'] === 'super_admin' ? 'Super Admin' : 'Client'; ?></td>
                                        <td class="px-4 py-3 text-sm text-gray-600"><?php echo htmlspecialchars($u['company_name'] ?? '—'); ?></td>
                                        <td class="px-4 py-3 text-right whitespace-nowrap">
                                            <form method="POST" action="super_websites.php?tab=gebruikers" class="inline">
                                                <?php echo csrf_field(); ?>
                                                <input type="hidden" name="action" value="toggle_user">
                                                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($u['id']); ?>">
                                                <button type="submit" class="text-xs font-bold px-2 py-1 rounded-full <?php echo $u['is_active'] ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'; ?>"><?php echo $u['is_active'] ? 'Actief' : 'Inactief'; ?></button>
                                            </form>
                                            <form method="POST" action="super_websites.php?tab=gebruikers" class="inline" onsubmit="return confirm('Deze gebruiker definitief verwijderen?');">
                                                <?php echo csrf_field(); ?>
                                                <input type="hidden" name="action" value="delete_user">
                                                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($u['id']); ?>">
                                                <button type="submit" class="text-red-500 hover:text-red-700 font-medium text-sm ml-2">Verwijder</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
