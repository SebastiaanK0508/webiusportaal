<?php
require_once 'includes/init.php';
$website_id = require_website_context();

$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'tab-socials';
$message = '';

if (isset($_POST['update_socials'])) {
    csrf_verify();
    $socials = ['social_instagram', 'social_facebook', 'social_tiktok'];
    foreach ($socials as $social) {
        set_footer_setting($social, trim($_POST[$social] ?? ''));
        set_footer_setting($social . '_actief', isset($_POST[$social . '_actief']) ? '1' : '0');
    }
    $message = "Social media links succesvol bijgewerkt!";
}
if (isset($_POST['update_contact'])) {
    csrf_verify();
    $contacts = ['contact_telefoon', 'contact_email', 'contact_regio'];
    foreach ($contacts as $contact) {
        set_footer_setting($contact, trim($_POST[$contact] ?? ''));
    }
    $message = "Contactgegevens succesvol bijgewerkt!";
}
if (isset($_POST['update_bedrijf'])) {
    csrf_verify();
    $bedrijf = ['bedrijf_kvk', 'bedrijf_btw'];
    foreach ($bedrijf as $gegeven) {
        set_footer_setting($gegeven, trim($_POST[$gegeven] ?? ''));
    }
    $message = "Bedrijfsgegevens succesvol bijgewerkt!";
}
if (isset($_POST['update_info'])) {
    csrf_verify();
    set_footer_setting('footer_text', trim($_POST['footer_text'] ?? ''));
    $message = "Informatie tekst succesvol bijgewerkt!";
}

$footer_link_kolommen = ['services' => 'Services', 'navigatie' => 'Navigatie'];

// Haalt het gekozen pad + bijpassend icoon op uit een pagina-select
// ('paginakeuze'), of valt terug op het vrij ingetypte veld als "Aangepaste
// link..." is gekozen. Zo hoeft een beheerder nooit zelf een pad te typen
// voor een bestaande pagina, maar kan dat nog wel voor een uitzondering
// (bijv. een pagina-anker of externe URL).
function resolve_footer_link_url_and_icon(array $post): array
{
    $keuze = $post['paginakeuze'] ?? 'custom';
    if ($keuze !== 'custom' && isset(FOOTER_LINK_PAGES[$keuze])) {
        return [$keuze, FOOTER_LINK_PAGES[$keuze]['icon']];
    }
    return [trim($post['custom_url'] ?? ''), 'link'];
}

if (isset($_POST['add_footer_link'])) {
    csrf_verify();
    $kolom = $_POST['kolom'] ?? '';
    $label = trim($_POST['label'] ?? '');
    [$url, $icon] = resolve_footer_link_url_and_icon($_POST);
    if (isset($footer_link_kolommen[$kolom]) && $label !== '' && $url !== '') {
        $max_order = $pdo->prepare("SELECT COALESCE(MAX(sort_order), 0) FROM footer_links WHERE website_id = ? AND kolom = ?");
        $max_order->execute([$website_id, $kolom]);
        $new_order = (int)$max_order->fetchColumn() + 1;

        $new_id = $pdo->query('SELECT UUID()')->fetchColumn();
        $stmt = $pdo->prepare("INSERT INTO footer_links (id, website_id, kolom, label, url, icon, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$new_id, $website_id, $kolom, $label, $url, $icon, $new_order]);
        $message = "Link toegevoegd!";
    }
    $active_tab = 'tab-links';
}
if (isset($_POST['update_footer_link'])) {
    csrf_verify();
    $label = trim($_POST['label'] ?? '');
    [$url, $icon] = resolve_footer_link_url_and_icon($_POST);
    if ($label !== '' && $url !== '') {
        $stmt = $pdo->prepare("UPDATE footer_links SET label = ?, url = ?, icon = ? WHERE id = ? AND website_id = ?");
        $stmt->execute([$label, $url, $icon, $_POST['update_footer_link'], $website_id]);
        $message = "Link bijgewerkt!";
    }
    $active_tab = 'tab-links';
}
if (isset($_POST['delete_footer_link'])) {
    csrf_verify();
    $stmt = $pdo->prepare("DELETE FROM footer_links WHERE id = ? AND website_id = ?");
    $stmt->execute([$_POST['delete_footer_link'], $website_id]);
    $message = "Link verwijderd!";
    $active_tab = 'tab-links';
}

$footer_links = [];
foreach (array_keys($footer_link_kolommen) as $kolom) {
    $footer_links[$kolom] = get_footer_links($kolom);
}

$footer_data = [];
$stmt = $pdo->prepare("SELECT sleutel, waarde FROM footer WHERE website_id = ?");
$stmt->execute([$website_id]);
while ($row = $stmt->fetch()) {
    $footer_data[$row['sleutel']] = $row['waarde'];
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <title>Footer Beheer - <?php echo htmlspecialchars(current_website()['company_name'] ?? 'Webius Portaal'); ?></title>
    <?php include 'includes/head.php'; ?>
</head>
<body class="bg-gray-100 font-sans pb-24">
    <?php include 'includes/header.php'; ?>

    <div class="max-w-6xl mx-auto px-4 mt-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Footer & Contact</h1>
                <p class="text-gray-500 mt-1">Beheer hier de social media links, contactgegevens en bedrijfsinfo.</p>
            </div>
            <div class="flex gap-3">
                <a href="websitebeheer.php" class="text-gray-600 hover:text-gray-900 font-bold bg-white px-6 py-2 rounded-full shadow border border-gray-200 transition-all">&larr; Terug naar Websitebeheer</a>
            </div>
        </div>

        <?php if ($message): ?>
            <script>showToast(<?php echo json_encode($message); ?>, 'success');</script>
        <?php endif; ?>
        <div class="border-b border-gray-200 mb-6 bg-white rounded-t-xl shadow-sm overflow-x-auto">
            <nav class="-mb-px flex space-x-6 px-4">
                <?php
                $tabs = [
                    'tab-socials' => 'Social Media',
                    'tab-links' => 'Links',
                    'tab-info' => 'Tekst',
                    'tab-contact' => 'Contactgegevens',
                    'tab-bedrijf' => 'Bedrijfsgegevens'
                ];
                foreach($tabs as $key => $label):
                    $is_active = ($active_tab === $key);
                    $active_class = $is_active ? 'border-pink-600 text-pink-600 font-bold' : 'border-transparent text-gray-500 hover:text-gray-700 font-medium';
                ?>
                <a href="footerbeheer.php?tab=<?php echo $key; ?>" class="tab-btn whitespace-nowrap py-4 px-1 border-b-2 text-sm <?php echo $active_class; ?>">
                    <?php echo $label; ?>
                </a>
                <?php endforeach; ?>
            </nav>
        </div>
        <div id="tab-socials" class="tab-content bg-white p-6 rounded-b-xl shadow-md border border-gray-200 border-t-0 mb-6 <?php echo $active_tab !== 'tab-socials' ? 'hidden' : ''; ?>">
            <form method="POST" action="footerbeheer.php?tab=tab-socials">
                <?php echo csrf_field(); ?>
                <div class="flex justify-between items-center mb-6 border-b pb-4">
                    <h2 class="text-2xl font-bold text-gray-800">Social Media Links</h2>
                    <button type="submit" name="update_socials" class="bg-pink-600 text-white font-bold py-2 px-6 rounded-lg hover:bg-pink-700 shadow-sm transition-colors">Opslaan</button>
                </div>
                <p class="text-gray-500 text-sm mb-8">Zet een kanaal uit met de schakelaar als deze tijdelijk (of helemaal niet) getoond moet worden op de website.</p>
                <div class="mb-6 p-4 bg-gray-50 border border-gray-200 rounded-xl">
                    <div class="flex justify-between items-center mb-3">
                        <label class="font-bold text-gray-800 flex items-center gap-2">
                            <span class="text-pink-600">📸</span> Instagram URL
                        </label>
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="social_instagram_actief" value="1" <?php echo ($footer_data['social_instagram_actief'] ?? '0') == '1' ? 'checked' : ''; ?> class="sr-only peer">
                            <div class="relative w-11 h-6 bg-gray-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-pink-500"></div>
                            <span class="ms-3 text-sm font-medium text-gray-600">Zichtbaar</span>
                        </label>
                    </div>
                    <input type="text" name="social_instagram" placeholder="https://instagram.com/paginanaam" value="<?php echo htmlspecialchars($footer_data['social_instagram'] ?? ''); ?>" class="w-full px-4 py-2 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-pink-400 focus:border-pink-400 text-sm transition-all">
                </div>
                <div class="mb-6 p-4 bg-gray-50 border border-gray-200 rounded-xl">
                    <div class="flex justify-between items-center mb-3">
                        <label class="font-bold text-gray-800 flex items-center gap-2">
                            <span class="text-blue-600">📘</span> Facebook URL
                        </label>
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="social_facebook_actief" value="1" <?php echo ($footer_data['social_facebook_actief'] ?? '0') == '1' ? 'checked' : ''; ?> class="sr-only peer">
                            <div class="relative w-11 h-6 bg-gray-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-pink-500"></div>
                            <span class="ms-3 text-sm font-medium text-gray-600">Zichtbaar</span>
                        </label>
                    </div>
                    <input type="text" name="social_facebook" placeholder="https://facebook.com/paginanaam" value="<?php echo htmlspecialchars($footer_data['social_facebook'] ?? ''); ?>" class="w-full px-4 py-2 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-pink-400 focus:border-pink-400 text-sm transition-all">
                </div>
                <div class="mb-6 p-4 bg-gray-50 border border-gray-200 rounded-xl">
                    <div class="flex justify-between items-center mb-3">
                        <label class="font-bold text-gray-800 flex items-center gap-2">
                            <span class="text-black">🎵</span> TikTok URL
                        </label>
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="social_tiktok_actief" value="1" <?php echo ($footer_data['social_tiktok_actief'] ?? '0') == '1' ? 'checked' : ''; ?> class="sr-only peer">
                            <div class="relative w-11 h-6 bg-gray-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-pink-500"></div>
                            <span class="ms-3 text-sm font-medium text-gray-600">Zichtbaar</span>
                        </label>
                    </div>
                    <input type="text" name="social_tiktok" placeholder="https://tiktok.com/@paginanaam" value="<?php echo htmlspecialchars($footer_data['social_tiktok'] ?? ''); ?>" class="w-full px-4 py-2 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-pink-400 focus:border-pink-400 text-sm transition-all">
                </div>
            </form>
        </div>
        <div id="tab-links" class="tab-content bg-white p-6 rounded-b-xl shadow-md border border-gray-200 border-t-0 mb-6 <?php echo $active_tab !== 'tab-links' ? 'hidden' : ''; ?>">
            <div class="flex justify-between items-center mb-6 border-b pb-4">
                <h2 class="text-2xl font-bold text-gray-800">Footer Links</h2>
            </div>
            <p class="text-gray-500 text-sm mb-8">Beheer de links in de kolommen "Services" en "Navigatie" onderaan de website. Kies een pagina uit de lijst — het pad vult dan automatisch in, net als het bijpassende icoon.</p>
            <div class="grid md:grid-cols-2 gap-8">
                <?php foreach ($footer_link_kolommen as $kolom => $kolom_label): ?>
                    <div>
                        <h3 class="text-lg font-bold text-gray-800 mb-4"><?php echo htmlspecialchars($kolom_label); ?></h3>
                        <div class="space-y-3 mb-6">
                            <?php foreach ($footer_links[$kolom] as $link): ?>
                                <?php $is_known = isset(FOOTER_LINK_PAGES[$link['url']]); ?>
                                <form method="POST" action="footerbeheer.php?tab=tab-links" class="bg-gray-50 border border-gray-200 p-3 rounded-lg space-y-2">
                                    <?php echo csrf_field(); ?>
                                    <div class="flex flex-col sm:flex-row gap-2">
                                        <input type="text" name="label" required value="<?php echo htmlspecialchars($link['label']); ?>" placeholder="Tekst" class="w-full sm:w-1/3 px-3 py-1.5 border border-gray-200 rounded-md text-sm font-bold text-gray-800">
                                        <select name="paginakeuze" class="w-full flex-grow px-3 py-1.5 border border-gray-200 rounded-md text-sm text-gray-600" onchange="footerLinkPageChange(this)">
                                            <?php foreach (FOOTER_LINK_PAGES as $page_url => $page_info): ?>
                                                <option value="<?php echo htmlspecialchars($page_url); ?>" data-label="<?php echo htmlspecialchars($page_info['label']); ?>" <?php echo $link['url'] === $page_url ? 'selected' : ''; ?>><?php echo htmlspecialchars($page_info['label']); ?></option>
                                            <?php endforeach; ?>
                                            <option value="custom" <?php echo !$is_known ? 'selected' : ''; ?>>Aangepaste link...</option>
                                        </select>
                                    </div>
                                    <input type="text" name="custom_url" value="<?php echo htmlspecialchars($is_known ? '' : $link['url']); ?>" placeholder="Pad of URL (bijv. contact.php)" class="footer-custom-url w-full px-3 py-1.5 border border-gray-200 rounded-md text-sm text-gray-600" style="<?php echo $is_known ? 'display:none' : ''; ?>">
                                    <div class="flex gap-2 justify-end">
                                        <button type="submit" name="update_footer_link" value="<?php echo htmlspecialchars($link['id']); ?>" class="text-xs font-bold text-pink-600 hover:text-pink-700 px-2">Opslaan</button>
                                        <button type="submit" name="delete_footer_link" value="<?php echo htmlspecialchars($link['id']); ?>" onclick="return confirmSubmit(event, 'Deze link verwijderen?');" class="text-red-500 hover:text-red-700 font-bold border p-1.5 rounded text-xs">Verwijder</button>
                                    </div>
                                </form>
                            <?php endforeach; ?>
                            <?php if (empty($footer_links[$kolom])): ?>
                                <p class="text-gray-400 italic text-sm">Nog geen links in deze kolom.</p>
                            <?php endif; ?>
                        </div>
                        <form method="POST" action="footerbeheer.php?tab=tab-links" class="bg-green-50 p-4 rounded-xl border border-green-100 space-y-2">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="kolom" value="<?php echo htmlspecialchars($kolom); ?>">
                            <div class="flex flex-col sm:flex-row gap-2">
                                <input type="text" name="label" required placeholder="Tekst (bijv. Contact)" class="w-full px-3 py-2 border rounded-md text-sm">
                                <select name="paginakeuze" class="w-full px-3 py-2 border rounded-md text-sm" onchange="footerLinkPageChange(this)">
                                    <?php foreach (FOOTER_LINK_PAGES as $page_url => $page_info): ?>
                                        <option value="<?php echo htmlspecialchars($page_url); ?>" data-label="<?php echo htmlspecialchars($page_info['label']); ?>"><?php echo htmlspecialchars($page_info['label']); ?></option>
                                    <?php endforeach; ?>
                                    <option value="custom">Aangepaste link...</option>
                                </select>
                            </div>
                            <input type="text" name="custom_url" placeholder="Pad of URL (bijv. contact.php)" class="footer-custom-url w-full px-3 py-2 border rounded-md text-sm" style="display:none">
                            <button type="submit" name="add_footer_link" class="bg-green-600 text-white font-bold py-2 px-6 rounded-lg text-sm">+ Link Toevoegen</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <script>
            function footerLinkPageChange(select) {
                const form = select.closest('form');
                const custom = form.querySelector('.footer-custom-url');
                const labelInput = form.querySelector('input[name="label"]');
                if (select.value === 'custom') {
                    custom.style.display = '';
                    custom.focus();
                } else {
                    custom.style.display = 'none';
                    custom.value = '';
                    const opt = select.options[select.selectedIndex];
                    if (labelInput && opt.dataset.label) {
                        labelInput.value = opt.dataset.label;
                    }
                }
            }
        </script>
        <div id="tab-info" class="tab-content bg-white p-6 rounded-b-xl shadow-md border border-gray-200 border-t-0 mb-6 <?php echo $active_tab !== 'tab-info' ? 'hidden' : ''; ?>">
            <form method="POST" action="footerbeheer.php?tab=tab-info">
                <?php echo csrf_field(); ?>
                <div class="flex justify-between items-center mb-6 border-b pb-4">
                    <h2 class="text-2xl font-bold text-gray-800">Tekst</h2>
                    <button type="submit" name="update_info" class="bg-pink-600 text-white font-bold py-2 px-6 rounded-lg hover:bg-pink-700 shadow-sm transition-colors">Opslaan</button>
                </div>

                <div class="grid md:grid-cols-2 gap-6">
                    <div class="mb-4">
                        <label class="font-bold text-gray-800 text-sm block mb-2">Tekst onder Titel</label>
                        <input type="text" name="footer_text" placeholder="Een korte omschrijving..." value="<?php echo htmlspecialchars($footer_data['footer_text'] ?? ''); ?>" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-pink-400 focus:border-pink-400 text-sm transition-all">
                    </div>
                </div>
            </form>
        </div>
        <div id="tab-contact" class="tab-content bg-white p-6 rounded-b-xl shadow-md border border-gray-200 border-t-0 mb-6 <?php echo $active_tab !== 'tab-contact' ? 'hidden' : ''; ?>">
            <form method="POST" action="footerbeheer.php?tab=tab-contact">
                <?php echo csrf_field(); ?>
                <div class="flex justify-between items-center mb-6 border-b pb-4">
                    <h2 class="text-2xl font-bold text-gray-800">Contactgegevens</h2>
                    <button type="submit" name="update_contact" class="bg-pink-600 text-white font-bold py-2 px-6 rounded-lg hover:bg-pink-700 shadow-sm transition-colors">Opslaan</button>
                </div>

                <div class="grid md:grid-cols-2 gap-6">
                    <div class="mb-4">
                        <label class="font-bold text-gray-800 text-sm block mb-2">Telefoonnummer</label>
                        <input type="text" name="contact_telefoon" placeholder="06 - 12 34 56 78" value="<?php echo htmlspecialchars($footer_data['contact_telefoon'] ?? ''); ?>" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-pink-400 focus:border-pink-400 text-sm transition-all">
                    </div>
                    <div class="mb-4">
                        <label class="font-bold text-gray-800 text-sm block mb-2">E-mailadres</label>
                        <input type="email" name="contact_email" placeholder="info@klant.nl" value="<?php echo htmlspecialchars($footer_data['contact_email'] ?? ''); ?>" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-pink-400 focus:border-pink-400 text-sm transition-all">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="font-bold text-gray-800 text-sm block mb-2">Regio & Werkgebied (HTML toegestaan, bijv. &lt;br&gt;)</label>
                    <textarea name="contact_regio" rows="3" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-pink-400 focus:border-pink-400 text-sm transition-all leading-relaxed"><?php echo htmlspecialchars($footer_data['contact_regio'] ?? ''); ?></textarea>
                </div>
            </form>
        </div>
        <div id="tab-bedrijf" class="tab-content bg-white p-6 rounded-b-xl shadow-md border border-gray-200 border-t-0 mb-6 <?php echo $active_tab !== 'tab-bedrijf' ? 'hidden' : ''; ?>">
            <form method="POST" action="footerbeheer.php?tab=tab-bedrijf">
                <?php echo csrf_field(); ?>
                <div class="flex justify-between items-center mb-6 border-b pb-4">
                    <h2 class="text-2xl font-bold text-gray-800">Bedrijfsgegevens</h2>
                    <button type="submit" name="update_bedrijf" class="bg-pink-600 text-white font-bold py-2 px-6 rounded-lg hover:bg-pink-700 shadow-sm transition-colors">Opslaan</button>
                </div>

                <div class="grid md:grid-cols-2 gap-6">
                    <div class="mb-4">
                        <label class="font-bold text-gray-800 text-sm block mb-2">KVK Nummer</label>
                        <input type="text" name="bedrijf_kvk" placeholder="12345678" value="<?php echo htmlspecialchars($footer_data['bedrijf_kvk'] ?? ''); ?>" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-pink-400 focus:border-pink-400 text-sm transition-all">
                    </div>
                    <div class="mb-4">
                        <label class="font-bold text-gray-800 text-sm block mb-2">BTW Nummer</label>
                        <input type="text" name="bedrijf_btw" placeholder="NL123456789B01" value="<?php echo htmlspecialchars($footer_data['bedrijf_btw'] ?? ''); ?>" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-pink-400 focus:border-pink-400 text-sm transition-all">
                    </div>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
