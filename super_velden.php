<?php
require_once 'includes/init.php';
require_role('super_admin');
$website_id = require_website_context();

function normalize_key($raw)
{
    $key = strtolower(trim($raw));
    $key = preg_replace('/[^a-z0-9]+/', '_', $key);
    return trim($key, '_');
}

function all_website_ids(PDO $pdo)
{
    return $pdo->query("SELECT id FROM websites")->fetchAll(PDO::FETCH_COLUMN);
}

$active_tab = $_GET['tab'] ?? 'teksten';
$entity_type = $_GET['entity'] ?? 'product';
if (!array_key_exists($entity_type, CUSTOM_FIELD_ENTITY_TYPES)) {
    $entity_type = 'product';
}
$message = '';
$error = '';

// ============================================================
// SITE_CONTENT KEYS
// ============================================================
if (isset($_POST['action']) && $_POST['action'] === 'add_content_key') {
    csrf_verify();
    $section_key = normalize_key($_POST['section_key'] ?? '');
    $page = trim($_POST['page'] ?? 'home');
    $type = in_array($_POST['type'] ?? '', ['text', 'textarea', 'image', 'number', 'boolean'], true) ? $_POST['type'] : 'text';
    $label = trim($_POST['label'] ?? '');
    $group_name = trim($_POST['group_name'] ?? '');
    $apply_all = isset($_POST['apply_all']);

    if ($section_key === '' || $label === '') {
        $error = "Veldnaam (key) en label zijn verplicht.";
    } else {
        $targets = $apply_all ? all_website_ids($pdo) : [$website_id];
        $added = 0;
        foreach ($targets as $target_id) {
            $check = $pdo->prepare("SELECT id FROM site_content WHERE website_id = ? AND section_key = ?");
            $check->execute([$target_id, $section_key]);
            if ($check->fetchColumn()) {
                continue;
            }
            $pdo->prepare("INSERT INTO site_content (id, website_id, page, section_key, content_text, type, label, group_name, is_visible) VALUES (UUID(), ?, ?, ?, '', ?, ?, ?, 1)")
                ->execute([$target_id, $page, $section_key, $type, $label, $group_name ?: null]);
            $added++;
        }
        $message = "Veld '{$section_key}' toegevoegd aan {$added} website(s).";
    }
}
if (isset($_POST['action']) && $_POST['action'] === 'delete_content_key') {
    csrf_verify();
    $section_key = $_POST['section_key'] ?? '';
    $apply_all = isset($_POST['apply_all_delete']);
    $targets = $apply_all ? all_website_ids($pdo) : [$website_id];
    $in = implode(',', array_fill(0, count($targets), '?'));
    $pdo->prepare("DELETE FROM site_content WHERE section_key = ? AND website_id IN ($in)")
        ->execute(array_merge([$section_key], $targets));
    $message = "Veld '{$section_key}' verwijderd.";
}

// ============================================================
// FOOTER KEYS
// ============================================================
if (isset($_POST['action']) && $_POST['action'] === 'add_footer_key') {
    csrf_verify();
    $sleutel = normalize_key($_POST['sleutel'] ?? '');
    $apply_all = isset($_POST['apply_all']);

    if ($sleutel === '') {
        $error = "Veldnaam (key) is verplicht.";
    } else {
        $targets = $apply_all ? all_website_ids($pdo) : [$website_id];
        $added = 0;
        foreach ($targets as $target_id) {
            $check = $pdo->prepare("SELECT id FROM footer WHERE website_id = ? AND sleutel = ?");
            $check->execute([$target_id, $sleutel]);
            if ($check->fetchColumn()) {
                continue;
            }
            $pdo->prepare("INSERT INTO footer (id, website_id, sleutel, waarde) VALUES (UUID(), ?, ?, '')")
                ->execute([$target_id, $sleutel]);
            $added++;
        }
        $message = "Footer-veld '{$sleutel}' toegevoegd aan {$added} website(s).";
    }
}
if (isset($_POST['action']) && $_POST['action'] === 'delete_footer_key') {
    csrf_verify();
    $sleutel = $_POST['sleutel'] ?? '';
    $apply_all = isset($_POST['apply_all_delete']);
    $targets = $apply_all ? all_website_ids($pdo) : [$website_id];
    $in = implode(',', array_fill(0, count($targets), '?'));
    $pdo->prepare("DELETE FROM footer WHERE sleutel = ? AND website_id IN ($in)")
        ->execute(array_merge([$sleutel], $targets));
    $message = "Footer-veld '{$sleutel}' verwijderd.";
}

// ============================================================
// APP_SETTINGS KEYS
// ============================================================
if (isset($_POST['action']) && $_POST['action'] === 'add_setting_key') {
    csrf_verify();
    $setting_key = normalize_key($_POST['setting_key'] ?? '');
    $apply_all = isset($_POST['apply_all']);

    if ($setting_key === '') {
        $error = "Veldnaam (key) is verplicht.";
    } else {
        $targets = $apply_all ? all_website_ids($pdo) : [$website_id];
        $added = 0;
        foreach ($targets as $target_id) {
            $check = $pdo->prepare("SELECT id FROM app_settings WHERE website_id = ? AND setting_key = ?");
            $check->execute([$target_id, $setting_key]);
            if ($check->fetchColumn()) {
                continue;
            }
            $pdo->prepare("INSERT INTO app_settings (id, website_id, setting_key, setting_value) VALUES (UUID(), ?, ?, '')")
                ->execute([$target_id, $setting_key]);
            $added++;
        }
        $message = "Instelling '{$setting_key}' toegevoegd aan {$added} website(s).";
    }
}
if (isset($_POST['action']) && $_POST['action'] === 'delete_setting_key') {
    csrf_verify();
    $setting_key = $_POST['setting_key'] ?? '';
    $apply_all = isset($_POST['apply_all_delete']);
    $targets = $apply_all ? all_website_ids($pdo) : [$website_id];
    $in = implode(',', array_fill(0, count($targets), '?'));
    $pdo->prepare("DELETE FROM app_settings WHERE setting_key = ? AND website_id IN ($in)")
        ->execute(array_merge([$setting_key], $targets));
    $message = "Instelling '{$setting_key}' verwijderd.";
}

// ============================================================
// CUSTOM FIELD DEFINITIONS (extra eigenschappen op producten/portfolio/etc.)
// ============================================================
if (isset($_POST['action']) && $_POST['action'] === 'add_custom_field') {
    csrf_verify();
    $target_entity = $_POST['entity_type'] ?? '';
    $field_key = normalize_key($_POST['field_key'] ?? '');
    $label = trim($_POST['label'] ?? '');
    $field_type = array_key_exists($_POST['field_type'] ?? '', CUSTOM_FIELD_TYPES) ? $_POST['field_type'] : 'text';
    $apply_all = isset($_POST['apply_all']);

    if (!array_key_exists($target_entity, CUSTOM_FIELD_ENTITY_TYPES)) {
        $error = "Ongeldig type item gekozen.";
    } elseif ($field_key === '' || $label === '') {
        $error = "Veldnaam (key) en label zijn verplicht.";
    } else {
        $targets = $apply_all ? all_website_ids($pdo) : [$website_id];
        $added = 0;
        foreach ($targets as $target_id) {
            $check = $pdo->prepare("SELECT id FROM custom_field_definitions WHERE website_id = ? AND entity_type = ? AND field_key = ?");
            $check->execute([$target_id, $target_entity, $field_key]);
            if ($check->fetchColumn()) {
                continue;
            }
            $pdo->prepare("INSERT INTO custom_field_definitions (id, website_id, entity_type, field_key, label, field_type, sort_order) VALUES (UUID(), ?, ?, ?, ?, ?, 0)")
                ->execute([$target_id, $target_entity, $field_key, $label, $field_type]);
            $added++;
        }
        $message = "Extra veld '{$label}' toegevoegd aan {$added} website(s) voor '" . CUSTOM_FIELD_ENTITY_TYPES[$target_entity] . "'.";
        $entity_type = $target_entity;
        $active_tab = 'extra';
    }
}
if (isset($_POST['action']) && $_POST['action'] === 'delete_custom_field') {
    csrf_verify();
    $field_id = $_POST['field_id'] ?? '';
    $apply_all = isset($_POST['apply_all_delete']);

    $stmt = $pdo->prepare("SELECT entity_type, field_key FROM custom_field_definitions WHERE id = ? AND website_id = ?");
    $stmt->execute([$field_id, $website_id]);
    $def = $stmt->fetch();

    if ($def) {
        if ($apply_all) {
            $matching = $pdo->prepare("SELECT id FROM custom_field_definitions WHERE entity_type = ? AND field_key = ?");
            $matching->execute([$def['entity_type'], $def['field_key']]);
            $ids = $matching->fetchAll(PDO::FETCH_COLUMN);
        } else {
            $ids = [$field_id];
        }
        if (!empty($ids)) {
            $in = implode(',', array_fill(0, count($ids), '?'));
            $pdo->prepare("DELETE FROM custom_field_values WHERE field_id IN ($in)")->execute($ids);
            $pdo->prepare("DELETE FROM custom_field_definitions WHERE id IN ($in)")->execute($ids);
        }
        $message = "Extra veld '{$def['field_key']}' verwijderd.";
        $entity_type = $def['entity_type'];
    }
    $active_tab = 'extra';
}

$content_keys_stmt = $pdo->prepare("SELECT section_key, page, type, label, group_name FROM site_content WHERE website_id = ? ORDER BY page, section_key");
$content_keys_stmt->execute([$website_id]);
$content_keys = $content_keys_stmt->fetchAll();

$footer_keys_stmt = $pdo->prepare("SELECT sleutel FROM footer WHERE website_id = ? ORDER BY sleutel");
$footer_keys_stmt->execute([$website_id]);
$footer_keys = $footer_keys_stmt->fetchAll();

$setting_keys_stmt = $pdo->prepare("SELECT setting_key FROM app_settings WHERE website_id = ? ORDER BY setting_key");
$setting_keys_stmt->execute([$website_id]);
$setting_keys = $setting_keys_stmt->fetchAll();

$custom_fields = get_custom_field_definitions($entity_type);
$website_count = count(all_website_ids($pdo));
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <title>Velden Beheren - Webius Portaal</title>
    <?php include 'includes/head.php'; ?>
</head>
<body class="bg-gray-100 font-sans pb-24">
    <?php include 'includes/header.php'; ?>
    <div class="max-w-6xl mx-auto px-4 mt-8">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Velden Beheren</h1>
            <p class="text-gray-500 mt-1">Voeg nieuwe tags/keys toe aan de content-tabellen, of definieer extra eigenschappen op producten, portfolio en meer. Alleen zichtbaar voor super admins.</p>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 shadow-sm rounded-r-lg"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($message): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 shadow-sm rounded-r-lg"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <div class="border-b border-gray-200 mb-6 bg-white rounded-t-xl shadow-sm overflow-x-auto">
            <nav class="-mb-px flex space-x-6 px-4">
                <?php $tabs = ['teksten' => 'Website Teksten', 'footer' => 'Footer & Social', 'instellingen' => 'App-instellingen', 'extra' => 'Extra Velden op Items']; foreach ($tabs as $key => $label): $is_active = ($active_tab === $key); ?>
                <a href="super_velden.php?tab=<?php echo $key; ?><?php echo $key === 'extra' ? '&entity=' . urlencode($entity_type) : ''; ?>" class="whitespace-nowrap py-4 px-1 border-b-2 font-bold text-sm <?php echo $is_active ? 'border-pink-600 text-pink-600' : 'border-transparent text-gray-500 hover:text-gray-700 font-medium'; ?>"><?php echo $label; ?></a>
                <?php endforeach; ?>
            </nav>
        </div>

        <!-- WEBSITE TEKSTEN -->
        <div class="<?php echo $active_tab !== 'teksten' ? 'hidden' : ''; ?>">
            <div class="grid lg:grid-cols-3 gap-8">
                <div class="lg:col-span-1 bg-white p-6 rounded-xl shadow-sm border border-gray-200 h-fit">
                    <h2 class="text-lg font-bold mb-4">Nieuw Tekstveld</h2>
                    <p class="text-xs text-gray-500 mb-4">Verschijnt automatisch als bewerkbaar veld in "Homepagina &rarr; Algemene Teksten".</p>
                    <form method="POST" action="super_velden.php?tab=teksten" class="space-y-4">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="action" value="add_content_key">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Veldnaam (key)</label>
                            <input type="text" name="section_key" required placeholder="bijv. promo_video_url" class="w-full border-gray-300 rounded-md border p-2 focus:border-pink-500 focus:ring-pink-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Label</label>
                            <input type="text" name="label" required placeholder="bijv. Video URL van de actie" class="w-full border-gray-300 rounded-md border p-2 focus:border-pink-500 focus:ring-pink-500">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Pagina</label>
                                <input type="text" name="page" value="home" class="w-full border-gray-300 rounded-md border p-2 focus:border-pink-500 focus:ring-pink-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                                <select name="type" class="w-full border-gray-300 rounded-md border p-2 bg-white">
                                    <option value="text">Tekst (kort)</option>
                                    <option value="textarea">Tekst (lang)</option>
                                    <option value="number">Getal</option>
                                    <option value="boolean">Ja/Nee</option>
                                    <option value="image">Afbeelding</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Groep (optioneel, bijv. "promo")</label>
                            <input type="text" name="group_name" placeholder="hero / about / promo / portfolio" class="w-full border-gray-300 rounded-md border p-2 focus:border-pink-500 focus:ring-pink-500">
                        </div>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="apply_all" class="rounded border-gray-300 text-pink-600 focus:ring-pink-500">
                            <span class="text-sm text-gray-700">Ook toevoegen aan alle <?php echo $website_count; ?> bestaande websites</span>
                        </label>
                        <button type="submit" class="w-full bg-pink-600 text-white font-bold py-2.5 px-4 rounded-lg hover:bg-pink-700 transition-colors">Veld Toevoegen</button>
                    </form>
                </div>
                <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                    <h2 class="text-lg font-bold mb-4">Bestaande Tekstvelden (huidige website)</h2>
                    <div class="overflow-x-auto border rounded-lg border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50"><tr>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Key</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Label</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Type</th>
                                <th class="px-3 py-2 text-right text-xs font-semibold text-gray-500 uppercase">Actie</th>
                            </tr></thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <?php foreach ($content_keys as $ck): ?>
                                <tr>
                                    <td class="px-3 py-2 font-mono text-xs text-gray-600"><?php echo htmlspecialchars($ck['section_key']); ?></td>
                                    <td class="px-3 py-2"><?php echo htmlspecialchars($ck['label'] ?: '—'); ?></td>
                                    <td class="px-3 py-2 text-gray-500"><?php echo htmlspecialchars($ck['type']); ?></td>
                                    <td class="px-3 py-2 text-right">
                                        <form method="POST" action="super_velden.php?tab=teksten" onsubmit="return confirm('Dit veld en de bijbehorende tekst verwijderen?');" class="inline-flex items-center gap-2">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="action" value="delete_content_key">
                                            <input type="hidden" name="section_key" value="<?php echo htmlspecialchars($ck['section_key']); ?>">
                                            <label class="text-xs text-gray-400 flex items-center gap-1"><input type="checkbox" name="apply_all_delete" class="rounded border-gray-300"> overal</label>
                                            <button type="submit" class="text-red-500 hover:text-red-700 font-medium text-xs">Verwijder</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($content_keys)): ?>
                                <tr><td colspan="4" class="px-3 py-4 text-center text-gray-400 italic">Nog geen tekstvelden.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- FOOTER -->
        <div class="<?php echo $active_tab !== 'footer' ? 'hidden' : ''; ?>">
            <div class="grid lg:grid-cols-3 gap-8">
                <div class="lg:col-span-1 bg-white p-6 rounded-xl shadow-sm border border-gray-200 h-fit">
                    <h2 class="text-lg font-bold mb-4">Nieuw Footer-veld</h2>
                    <p class="text-xs text-gray-500 mb-4">Let op: footerbeheer.php heeft vaste velden per tabblad. Een geheel nieuwe key is hier vooral bruikbaar als opslagplek — voor een eigen invoerveld in footerbeheer.php zelf is nog een kleine aanpassing nodig.</p>
                    <form method="POST" action="super_velden.php?tab=footer" class="space-y-4">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="action" value="add_footer_key">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Veldnaam (key)</label>
                            <input type="text" name="sleutel" required placeholder="bijv. social_linkedin" class="w-full border-gray-300 rounded-md border p-2 focus:border-pink-500 focus:ring-pink-500">
                        </div>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="apply_all" class="rounded border-gray-300 text-pink-600 focus:ring-pink-500">
                            <span class="text-sm text-gray-700">Ook toevoegen aan alle <?php echo $website_count; ?> bestaande websites</span>
                        </label>
                        <button type="submit" class="w-full bg-pink-600 text-white font-bold py-2.5 px-4 rounded-lg hover:bg-pink-700 transition-colors">Veld Toevoegen</button>
                    </form>
                </div>
                <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                    <h2 class="text-lg font-bold mb-4">Bestaande Footer-velden (huidige website)</h2>
                    <div class="overflow-x-auto border rounded-lg border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50"><tr>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Key</th>
                                <th class="px-3 py-2 text-right text-xs font-semibold text-gray-500 uppercase">Actie</th>
                            </tr></thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <?php foreach ($footer_keys as $fk): ?>
                                <tr>
                                    <td class="px-3 py-2 font-mono text-xs text-gray-600"><?php echo htmlspecialchars($fk['sleutel']); ?></td>
                                    <td class="px-3 py-2 text-right">
                                        <form method="POST" action="super_velden.php?tab=footer" onsubmit="return confirm('Dit footer-veld verwijderen?');" class="inline-flex items-center gap-2">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="action" value="delete_footer_key">
                                            <input type="hidden" name="sleutel" value="<?php echo htmlspecialchars($fk['sleutel']); ?>">
                                            <label class="text-xs text-gray-400 flex items-center gap-1"><input type="checkbox" name="apply_all_delete" class="rounded border-gray-300"> overal</label>
                                            <button type="submit" class="text-red-500 hover:text-red-700 font-medium text-xs">Verwijder</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($footer_keys)): ?>
                                <tr><td colspan="2" class="px-3 py-4 text-center text-gray-400 italic">Nog geen footer-velden.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- APP-INSTELLINGEN -->
        <div class="<?php echo $active_tab !== 'instellingen' ? 'hidden' : ''; ?>">
            <div class="grid lg:grid-cols-3 gap-8">
                <div class="lg:col-span-1 bg-white p-6 rounded-xl shadow-sm border border-gray-200 h-fit">
                    <h2 class="text-lg font-bold mb-4">Nieuwe Instelling</h2>
                    <p class="text-xs text-gray-500 mb-4">Zelfde opmerking als bij footer: app_instellingen.php toont vaste velden. Deze key is vooral handig als losse opslagplek of voor toekomstig gebruik.</p>
                    <form method="POST" action="super_velden.php?tab=instellingen" class="space-y-4">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="action" value="add_setting_key">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Veldnaam (key)</label>
                            <input type="text" name="setting_key" required placeholder="bijv. whatsapp_nummer" class="w-full border-gray-300 rounded-md border p-2 focus:border-pink-500 focus:ring-pink-500">
                        </div>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="apply_all" class="rounded border-gray-300 text-pink-600 focus:ring-pink-500">
                            <span class="text-sm text-gray-700">Ook toevoegen aan alle <?php echo $website_count; ?> bestaande websites</span>
                        </label>
                        <button type="submit" class="w-full bg-pink-600 text-white font-bold py-2.5 px-4 rounded-lg hover:bg-pink-700 transition-colors">Instelling Toevoegen</button>
                    </form>
                </div>
                <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                    <h2 class="text-lg font-bold mb-4">Bestaande Instellingen (huidige website)</h2>
                    <div class="overflow-x-auto border rounded-lg border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50"><tr>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Key</th>
                                <th class="px-3 py-2 text-right text-xs font-semibold text-gray-500 uppercase">Actie</th>
                            </tr></thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <?php foreach ($setting_keys as $sk): ?>
                                <tr>
                                    <td class="px-3 py-2 font-mono text-xs text-gray-600"><?php echo htmlspecialchars($sk['setting_key']); ?></td>
                                    <td class="px-3 py-2 text-right">
                                        <form method="POST" action="super_velden.php?tab=instellingen" onsubmit="return confirm('Deze instelling verwijderen?');" class="inline-flex items-center gap-2">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="action" value="delete_setting_key">
                                            <input type="hidden" name="setting_key" value="<?php echo htmlspecialchars($sk['setting_key']); ?>">
                                            <label class="text-xs text-gray-400 flex items-center gap-1"><input type="checkbox" name="apply_all_delete" class="rounded border-gray-300"> overal</label>
                                            <button type="submit" class="text-red-500 hover:text-red-700 font-medium text-xs">Verwijder</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($setting_keys)): ?>
                                <tr><td colspan="2" class="px-3 py-4 text-center text-gray-400 italic">Nog geen instellingen.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- EXTRA VELDEN OP ITEMS -->
        <div class="<?php echo $active_tab !== 'extra' ? 'hidden' : ''; ?>">
            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200 mb-6">
                <label class="text-sm font-bold text-gray-700 mr-3">Type item:</label>
                <div class="inline-flex flex-wrap gap-2 mt-2">
                    <?php foreach (CUSTOM_FIELD_ENTITY_TYPES as $key => $label): ?>
                        <a href="super_velden.php?tab=extra&entity=<?php echo $key; ?>" class="px-3 py-1.5 rounded-full text-sm font-medium <?php echo $entity_type === $key ? 'bg-pink-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'; ?>"><?php echo htmlspecialchars($label); ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="grid lg:grid-cols-3 gap-8">
                <div class="lg:col-span-1 bg-white p-6 rounded-xl shadow-sm border border-gray-200 h-fit">
                    <h2 class="text-lg font-bold mb-4">Nieuw Extra Veld</h2>
                    <p class="text-xs text-gray-500 mb-4">Verschijnt automatisch in het "nieuw toevoegen"-formulier van <?php echo htmlspecialchars(CUSTOM_FIELD_ENTITY_TYPES[$entity_type]); ?>, en als badge op bestaande items.</p>
                    <form method="POST" action="super_velden.php?tab=extra&entity=<?php echo $entity_type; ?>" class="space-y-4">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="action" value="add_custom_field">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Type item</label>
                            <select name="entity_type" class="w-full border-gray-300 rounded-md border p-2 bg-white">
                                <?php foreach (CUSTOM_FIELD_ENTITY_TYPES as $key => $label): ?>
                                    <option value="<?php echo $key; ?>" <?php echo $entity_type === $key ? 'selected' : ''; ?>><?php echo htmlspecialchars($label); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Veldnaam (key)</label>
                            <input type="text" name="field_key" required placeholder="bijv. materiaal" class="w-full border-gray-300 rounded-md border p-2 focus:border-pink-500 focus:ring-pink-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Label</label>
                            <input type="text" name="label" required placeholder="bijv. Materiaal" class="w-full border-gray-300 rounded-md border p-2 focus:border-pink-500 focus:ring-pink-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Type veld</label>
                            <select name="field_type" class="w-full border-gray-300 rounded-md border p-2 bg-white">
                                <?php foreach (CUSTOM_FIELD_TYPES as $key => $label): ?>
                                    <option value="<?php echo $key; ?>"><?php echo htmlspecialchars($label); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="apply_all" class="rounded border-gray-300 text-pink-600 focus:ring-pink-500">
                            <span class="text-sm text-gray-700">Ook toevoegen aan alle <?php echo $website_count; ?> bestaande websites</span>
                        </label>
                        <button type="submit" class="w-full bg-pink-600 text-white font-bold py-2.5 px-4 rounded-lg hover:bg-pink-700 transition-colors">Veld Toevoegen</button>
                    </form>
                </div>
                <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                    <h2 class="text-lg font-bold mb-4">Bestaande Extra Velden — <?php echo htmlspecialchars(CUSTOM_FIELD_ENTITY_TYPES[$entity_type]); ?> (huidige website)</h2>
                    <div class="overflow-x-auto border rounded-lg border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50"><tr>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Key</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Label</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Type</th>
                                <th class="px-3 py-2 text-right text-xs font-semibold text-gray-500 uppercase">Actie</th>
                            </tr></thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <?php foreach ($custom_fields as $cf): ?>
                                <tr>
                                    <td class="px-3 py-2 font-mono text-xs text-gray-600"><?php echo htmlspecialchars($cf['field_key']); ?></td>
                                    <td class="px-3 py-2"><?php echo htmlspecialchars($cf['label']); ?></td>
                                    <td class="px-3 py-2 text-gray-500"><?php echo htmlspecialchars(CUSTOM_FIELD_TYPES[$cf['field_type']] ?? $cf['field_type']); ?></td>
                                    <td class="px-3 py-2 text-right">
                                        <form method="POST" action="super_velden.php?tab=extra&entity=<?php echo $entity_type; ?>" onsubmit="return confirm('Dit extra veld en alle ingevulde waarden verwijderen?');" class="inline-flex items-center gap-2">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="action" value="delete_custom_field">
                                            <input type="hidden" name="field_id" value="<?php echo htmlspecialchars($cf['id']); ?>">
                                            <label class="text-xs text-gray-400 flex items-center gap-1"><input type="checkbox" name="apply_all_delete" class="rounded border-gray-300"> overal</label>
                                            <button type="submit" class="text-red-500 hover:text-red-700 font-medium text-xs">Verwijder</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($custom_fields)): ?>
                                <tr><td colspan="4" class="px-3 py-4 text-center text-gray-400 italic">Nog geen extra velden voor dit type.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
