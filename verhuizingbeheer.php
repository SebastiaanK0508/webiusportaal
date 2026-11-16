<?php
require_once 'includes/init.php';
$website_id = require_website_context();
require_module($website_id, 'verhuizing');

$text_sections = [
    'gegevens' => [
        'title' => 'Verhuisgegevens',
        'desc'  => 'Leeg laten toont "Volgt binnenkort" op de website.',
        'fields' => [
            'verhuis_datum' => ['label' => 'Verhuisdatum', 'type' => 'text', 'placeholder' => 'bijv. maandag 5 januari 2027'],
            'nieuw_adres'   => ['label' => 'Nieuw adres', 'type' => 'text', 'placeholder' => 'Straat + huisnummer'],
            'nieuw_plaats'  => ['label' => 'Postcode en plaats', 'type' => 'text', 'placeholder' => '7471 AB Goor'],
        ],
    ],
    'pagina' => [
        'title' => 'Teksten op de pagina',
        'desc'  => 'Bovenaan en onderaan de verhuispagina.',
        'fields' => [
            'hero_title' => ['label' => 'Hoofdtitel', 'type' => 'text'],
            'hero_text'  => ['label' => 'Introductie', 'type' => 'textarea'],
            'cta_title'  => ['label' => 'Titel onderaan', 'type' => 'text'],
            'cta_text'   => ['label' => 'Tekst onderaan', 'type' => 'textarea'],
            'meta_title' => ['label' => 'Titel in de browsertab', 'type' => 'text'],
        ],
    ],
];
// Teksten die bij een lijst/blok horen en daar in de UI staan.
$extra_fields = [
    'details_title' => ['label' => 'Kop boven de uitlegblokken', 'type' => 'text', 'group' => 'uitleg'],
    'faq_title'     => ['label' => 'Kop boven de veelgestelde vragen', 'type' => 'text', 'group' => 'faq'],
    'form_title'    => ['label' => 'Kop boven het formulier', 'type' => 'text', 'group' => 'formulier'],
    'form_text'     => ['label' => 'Tekst boven het formulier', 'type' => 'textarea', 'group' => 'formulier'],
    'banner_text'   => ['label' => 'Tekst van de gele balk', 'type' => 'text', 'group' => 'melding'],
];
// Zichtbaarheidsschakelaars: veld => naam van de checkbox.
$toggles = ['banner_text' => 'banner_visible', 'form_title' => 'form_visible'];

$item_types = [
    'uitleg' => [
        'title' => 'Uitlegblokken',
        'desc'  => 'Losse blokjes met uitleg, bijvoorbeeld over sluitingsdagen of parkeren. Zonder blokken wordt dit onderdeel niet getoond.',
        'titel_label' => 'Titel', 'tekst_label' => 'Uitleg',
        'suggesties' => ['Laatste dag op de oude locatie', 'Gesloten tijdens de verhuizing', 'Openingstijden nieuwe winkel', 'Bereikbaarheid & parkeren', 'PostNL, pasfoto\'s en andere diensten', 'Cadeaukaarten en tegoeden blijven geldig'],
    ],
    'faq' => [
        'title' => 'Veelgestelde vragen',
        'desc'  => 'Uitklapbare vragen op de verhuispagina. Zonder vragen wordt dit onderdeel niet getoond.',
        'titel_label' => 'Vraag', 'tekst_label' => 'Antwoord',
        'suggesties' => ['Is de winkel open tijdens de verhuizing?', 'Kan ik mijn pakket nog ophalen?', 'Blijven de openingstijden hetzelfde?', 'Is er parkeergelegenheid bij de nieuwe winkel?', 'Blijft mijn cadeaukaart geldig?'],
    ],
];

function verhuizing_items(string $website_id, string $soort): array
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM verhuizing_items WHERE website_id = ? AND soort = ? ORDER BY sort_order ASC, created_at ASC");
    $stmt->execute([$website_id, $soort]);
    return $stmt->fetchAll();
}

// Nummert de volgorde opnieuw 0..n, zodat "omhoog" altijd met de buur kan wisselen.
function verhuizing_renumber(string $website_id, string $soort): void
{
    global $pdo;
    $stmt = $pdo->prepare("UPDATE verhuizing_items SET sort_order = ? WHERE id = ? AND website_id = ?");
    foreach (verhuizing_items($website_id, $soort) as $i => $item) {
        $stmt->execute([$i, $item['id'], $website_id]);
    }
}

// Berichten uit het verhuisformulier (contact_messages, onderwerp "Verhuizing").
// Eigen mini-forms per bericht; moet vóór de grote form-handler hieronder
// staan, want die verwerkt elke andere POST als "alles opslaan".
if (isset($_POST['msg_action'], $_POST['msg_id'])) {
    csrf_verify();
    $msg_id = (string)$_POST['msg_id'];
    $where = "WHERE id = ? AND website_id = ? AND subject = 'Verhuizing'";
    switch ($_POST['msg_action']) {
        case 'read': // via fetch() bij openklappen
            $pdo->prepare("UPDATE contact_messages SET is_read = 1, status = IF(status = 'nieuw', 'gelezen', status) $where")->execute([$msg_id, $website_id]);
            exit('ok');
        case 'unread':
            $pdo->prepare("UPDATE contact_messages SET is_read = 0, status = 'nieuw' $where")->execute([$msg_id, $website_id]);
            $flash = 'Bericht gemarkeerd als ongelezen.';
            break;
        case 'beantwoord':
            $pdo->prepare("UPDATE contact_messages SET is_read = 1, status = 'beantwoord' $where")->execute([$msg_id, $website_id]);
            $flash = 'Bericht gemarkeerd als beantwoord.';
            break;
        case 'delete':
            $pdo->prepare("DELETE FROM contact_messages $where")->execute([$msg_id, $website_id]);
            $flash = 'Bericht verwijderd.';
            break;
        default:
            $flash = '';
    }
    flash_redirect('verhuizingbeheer.php#berichten', $flash);
}

// De hoofdschakelaar: losse mini-form die direct opslaat bij omzetten.
if (isset($_POST['toggle_actief'])) {
    csrf_verify();
    $actief = isset($_POST['actief']) ? '1' : '0';
    $pdo->prepare(
        "INSERT INTO site_content (id, website_id, page, section_key, content_text, type, label, group_name, is_visible)
         VALUES (UUID(), ?, 'verhuizing', 'actief', ?, 'text', 'Verhuizing tonen op de website', 'status', 1)
         ON DUPLICATE KEY UPDATE content_text = VALUES(content_text), is_visible = 1"
    )->execute([$website_id, $actief]);
    flash_redirect('verhuizingbeheer.php', $actief === '1' ? 'De verhuizing staat nu online.' : 'De verhuizing is van de website gehaald.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    // 1. Vaste teksten
    $upsert = $pdo->prepare(
        "INSERT INTO site_content (id, website_id, page, section_key, content_text, type, label, group_name, is_visible)
         VALUES (UUID(), ?, 'verhuizing', ?, ?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE content_text = VALUES(content_text), is_visible = VALUES(is_visible)"
    );
    $all_fields = $extra_fields;
    foreach ($text_sections as $group => $section) {
        foreach ($section['fields'] as $key => $field) {
            $all_fields[$key] = $field + ['group' => $group];
        }
    }
    foreach ($all_fields as $key => $field) {
        $value = trim((string)($_POST['content'][$key] ?? ''));
        $visible = isset($toggles[$key]) ? (isset($_POST[$toggles[$key]]) ? 1 : 0) : 1;
        $upsert->execute([$website_id, $key, $value, $field['type'], $field['label'], $field['group'], $visible]);
    }

    // 2. Bestaande uitlegblokken / vragen bijwerken
    $update = $pdo->prepare("UPDATE verhuizing_items SET titel = ?, tekst = ? WHERE id = ? AND website_id = ?");
    foreach ($_POST['items'] ?? [] as $id => $item) {
        $titel = trim((string)($item['titel'] ?? ''));
        $tekst = trim((string)($item['tekst'] ?? ''));
        if ($titel !== '' && $tekst !== '') {
            $update->execute([$titel, $tekst, (string)$id, $website_id]);
        }
    }

    // 3. Nieuwe toevoegen (alleen als zowel titel als tekst is ingevuld)
    foreach (array_keys($item_types) as $soort) {
        $titel = trim((string)($_POST['new'][$soort]['titel'] ?? ''));
        $tekst = trim((string)($_POST['new'][$soort]['tekst'] ?? ''));
        if ($titel !== '' && $tekst !== '') {
            $pdo->prepare("INSERT INTO verhuizing_items (id, website_id, soort, titel, tekst, sort_order) VALUES (UUID(), ?, ?, ?, ?, 9999)")
                ->execute([$website_id, $soort, $titel, $tekst]);
        }
    }

    // 4. Verwijderen / omhoog verplaatsen
    if (!empty($_POST['delete_item'])) {
        $pdo->prepare("DELETE FROM verhuizing_items WHERE id = ? AND website_id = ?")->execute([$_POST['delete_item'], $website_id]);
    }
    foreach (array_keys($item_types) as $soort) {
        verhuizing_renumber($website_id, $soort);
    }
    if (!empty($_POST['move_up'])) {
        $stmt = $pdo->prepare("SELECT soort, sort_order FROM verhuizing_items WHERE id = ? AND website_id = ?");
        $stmt->execute([$_POST['move_up'], $website_id]);
        $row = $stmt->fetch();
        if ($row && (int)$row['sort_order'] > 0) {
            $prev = (int)$row['sort_order'] - 1;
            $pdo->prepare("UPDATE verhuizing_items SET sort_order = ? WHERE website_id = ? AND soort = ? AND sort_order = ?")
                ->execute([$prev + 1, $website_id, $row['soort'], $prev]);
            $pdo->prepare("UPDATE verhuizing_items SET sort_order = ? WHERE id = ? AND website_id = ?")
                ->execute([$prev, $_POST['move_up'], $website_id]);
        }
    }

    flash_redirect('verhuizingbeheer.php', 'Verhuizing succesvol bijgewerkt!');
}

$message = get_flash_messages()[0];

$stmt = $pdo->prepare("SELECT section_key, content_text, is_visible FROM site_content WHERE website_id = ? AND page = 'verhuizing'");
$stmt->execute([$website_id]);
$values = [];
foreach ($stmt->fetchAll() as $row) {
    $values[$row['section_key']] = $row;
}
$val = fn(string $key) => htmlspecialchars($values[$key]['content_text'] ?? '');
$is_on = fn(string $key) => !isset($values[$key]) || (int)$values[$key]['is_visible'] === 1;
$verhuizing_actief = ($values['actief']['content_text'] ?? '0') === '1';

$items = [];
foreach (array_keys($item_types) as $soort) {
    $items[$soort] = verhuizing_items($website_id, $soort);
}

$stmt = $pdo->prepare("SELECT * FROM contact_messages WHERE website_id = ? AND subject = 'Verhuizing' ORDER BY is_read ASC, created_at DESC");
$stmt->execute([$website_id]);
$berichten = $stmt->fetchAll();
$unread_verhuizing = count(array_filter($berichten, fn($m) => (int)$m['is_read'] === 0));

// send_mail.php slaat berichten al ge-escaped op (FILTER_SANITIZE_SPECIAL_CHARS);
// eerst terugdecoderen, anders zie je &#39; i.p.v. een apostrof.
$plain = fn($v) => html_entity_decode((string)$v, ENT_QUOTES | ENT_HTML5, 'UTF-8');
$txt = fn($v) => htmlspecialchars($plain($v));

$input_class = 'w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-pink-400 focus:border-pink-400 text-sm transition-all';
$card_class = 'bg-white p-6 rounded-xl shadow-sm border border-gray-200';

function render_toggle(string $name, bool $checked): string
{
    return '<label class="inline-flex items-center gap-2 cursor-pointer shrink-0 text-sm font-bold text-gray-600">'
        . '<span>Tonen</span>'
        . '<input type="checkbox" name="' . $name . '" value="1" ' . ($checked ? 'checked' : '') . ' class="sr-only peer">'
        . '<div class="relative w-11 h-6 bg-gray-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[\'\'] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-pink-500"></div>'
        . '</label>';
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <title>Verhuizing - <?php echo htmlspecialchars(current_website()['company_name'] ?? 'Webius Portaal'); ?></title>
    <?php include 'includes/head.php'; ?>
    <style>summary::-webkit-details-marker { display: none; }</style>
</head>
<body class="bg-gray-100 font-sans pb-24">
    <?php include 'includes/header.php'; ?>

    <div class="max-w-4xl mx-auto px-4 mt-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Verhuizing</h1>
                <p class="text-gray-500 mt-1">De verhuispagina, de link in het menu en de melding op de homepage.</p>
            </div>
            <a href="<?php echo htmlspecialchars(public_site_url('verhuizing.php')); ?>" target="_blank" title="<?php echo $verhuizing_actief ? '' : 'Werkt pas als de verhuizing aan staat'; ?>" class="<?php echo $verhuizing_actief ? '' : 'opacity-50'; ?> shrink-0 text-pink-600 hover:text-white font-bold bg-white hover:bg-pink-600 px-6 py-2 rounded-full shadow border border-pink-100 transition-all">Bekijk pagina &rarr;</a>
        </div>

        <!-- Hoofdschakelaar: eigen formulier, slaat direct op bij omzetten -->
        <form method="POST" action="verhuizingbeheer.php" class="mb-6">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="toggle_actief" value="1">
            <div class="p-5 rounded-xl border-2 flex justify-between items-center gap-4 shadow-sm <?php echo $verhuizing_actief ? 'bg-green-50 border-green-300' : 'bg-white border-gray-300'; ?>">
                <div>
                    <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                        Verhuizing tonen op de website
                        <span class="text-xs font-bold uppercase px-2 py-0.5 rounded-full <?php echo $verhuizing_actief ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-600'; ?>"><?php echo $verhuizing_actief ? 'Online' : 'Uit'; ?></span>
                    </h2>
                    <p class="text-sm text-gray-600 mt-1">Zet in één keer de verhuispagina, de link in het menu, de gele balk bovenaan elke pagina en een eventuele footerlink aan of uit. Je teksten blijven bewaard.</p>
                </div>
                <label class="inline-flex items-center cursor-pointer shrink-0">
                    <input type="checkbox" name="actief" value="1" <?php echo $verhuizing_actief ? 'checked' : ''; ?> onchange="this.form.submit()" class="sr-only peer">
                    <div class="relative w-14 h-7 bg-gray-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-green-600"></div>
                </label>
            </div>
        </form>

        <!-- Berichten uit het formulier op de verhuispagina -->
        <section id="berichten" class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6 scroll-mt-24">
            <div class="p-5 border-b border-gray-100 flex justify-between items-center gap-4">
                <div>
                    <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                        Berichten
                        <?php if ($unread_verhuizing > 0): ?><span class="bg-pink-500 text-white text-xs font-bold px-2 py-0.5 rounded-full"><?php echo $unread_verhuizing; ?> nieuw</span><?php endif; ?>
                    </h2>
                    <p class="text-sm text-gray-500">Ingevuld via het formulier op de verhuispagina. Ze staan ook in <a href="contactbeheer.php" class="text-pink-600 font-bold hover:underline">Contactberichten</a>.</p>
                </div>
            </div>
            <?php if (!$berichten): ?>
                <p class="p-5 text-sm text-gray-500">Nog geen berichten ontvangen.</p>
            <?php else: ?>
                <div class="divide-y divide-gray-100">
                    <?php foreach ($berichten as $msg):
                        $id = htmlspecialchars($msg['id']);
                        $is_new = (int)$msg['is_read'] === 0;
                        $status_badge = $msg['status'] === 'beantwoord'
                            ? '<span class="text-[10px] uppercase font-bold px-1.5 py-0.5 rounded bg-green-100 text-green-700">Beantwoord</span>'
                            : ($is_new ? '<span data-new-badge class="text-[10px] uppercase font-bold px-1.5 py-0.5 rounded bg-pink-500 text-white">Nieuw</span>' : '');
                        $reply_body = "\n\n\n--- Uw bericht van " . date('d-m-Y', strtotime($msg['created_at'])) . " ---\n" . $plain($msg['message']);
                        $mailto = 'mailto:' . rawurlencode($plain($msg['email'])) . '?subject=' . rawurlencode('Re: Uw vraag over onze verhuizing') . '&body=' . rawurlencode($reply_body);
                    ?>
                        <details class="group" <?php echo $is_new ? 'data-unread="' . $id . '"' : ''; ?>>
                            <summary class="list-none cursor-pointer p-4 flex items-center gap-3 hover:bg-gray-50 <?php echo $is_new ? 'bg-pink-50/40' : ''; ?>">
                                <span class="w-9 h-9 rounded-full bg-pink-100 text-pink-600 flex items-center justify-center font-bold shrink-0"><?php echo htmlspecialchars(mb_strtoupper(mb_substr($plain($msg['name']), 0, 1))); ?></span>
                                <span class="min-w-0 flex-1">
                                    <span class="flex items-center gap-2">
                                        <span class="<?php echo $is_new ? 'font-bold text-gray-900' : 'font-medium text-gray-700'; ?> truncate"><?php echo $txt($msg['name']); ?></span>
                                        <?php echo $status_badge; ?>
                                    </span>
                                    <span class="block text-sm text-gray-500 truncate group-open:hidden"><?php echo $txt(mb_strimwidth($plain($msg['message']), 0, 90, '…')); ?></span>
                                </span>
                                <span class="text-xs text-gray-400 shrink-0"><?php echo date('d-m-Y H:i', strtotime($msg['created_at'])); ?></span>
                                <svg class="w-4 h-4 text-gray-400 shrink-0 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </summary>
                            <div class="px-4 pb-4 pl-16">
                                <p class="text-sm text-gray-500 mb-2"><a href="mailto:<?php echo $txt($msg['email']); ?>" class="hover:text-pink-600"><?php echo $txt($msg['email']); ?></a></p>
                                <p class="text-gray-800 whitespace-pre-line bg-gray-50 border border-gray-100 rounded-lg p-4 text-sm"><?php echo $txt($msg['message']); ?></p>
                                <div class="flex flex-wrap gap-2 mt-3">
                                    <a href="<?php echo htmlspecialchars($mailto); ?>" class="bg-pink-600 hover:bg-pink-700 text-white text-sm font-bold px-4 py-2 rounded-lg">Beantwoorden per e-mail</a>
                                    <?php foreach (($msg['status'] === 'beantwoord' ? [] : ['beantwoord' => 'Markeer als beantwoord']) + ['unread' => 'Markeer als ongelezen'] as $action => $label): ?>
                                        <form method="POST" action="verhuizingbeheer.php">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="msg_id" value="<?php echo $id; ?>">
                                            <button type="submit" name="msg_action" value="<?php echo $action; ?>" class="bg-white border border-gray-200 hover:border-pink-200 hover:text-pink-600 text-gray-600 text-sm font-bold px-4 py-2 rounded-lg"><?php echo $label; ?></button>
                                        </form>
                                    <?php endforeach; ?>
                                    <form method="POST" action="verhuizingbeheer.php" class="ml-auto">
                                        <?php echo csrf_field(); ?>
                                        <input type="hidden" name="msg_id" value="<?php echo $id; ?>">
                                        <input type="hidden" name="msg_action" value="delete">
                                        <button type="submit" onclick="return confirmSubmit(event, 'Dit bericht definitief verwijderen?');" class="bg-white border border-red-200 text-red-500 hover:bg-red-50 text-sm font-bold px-4 py-2 rounded-lg">Verwijderen</button>
                                    </form>
                                </div>
                            </div>
                        </details>
                    <?php endforeach; ?>
                </div>
                <script>
                    // Openklappen = gelezen (zelfde idee als "Lees bericht" in contactbeheer).
                    document.querySelectorAll('#berichten details[data-unread]').forEach(function (el) {
                        el.addEventListener('toggle', function () {
                            if (!el.open || !el.dataset.unread) return;
                            const body = new FormData();
                            body.append('csrf_token', <?php echo json_encode(csrf_token()); ?>);
                            body.append('msg_action', 'read');
                            body.append('msg_id', el.dataset.unread);
                            fetch('verhuizingbeheer.php', { method: 'POST', body: body });
                            delete el.dataset.unread;
                            const badge = el.querySelector('[data-new-badge]');
                            if (badge) badge.remove();
                        });
                    });
                </script>
            <?php endif; ?>
        </section>

        <!-- Snelmenu -->
        <nav class="flex flex-wrap gap-2 mb-6 text-sm">
            <?php foreach (['gegevens' => 'Gegevens', 'pagina' => 'Teksten', 'uitleg' => 'Uitleg', 'faq' => 'FAQ', 'formulier' => 'Formulier', 'melding' => 'Melding', 'berichten' => 'Berichten' . ($unread_verhuizing ? " ($unread_verhuizing)" : '')] as $anchor => $label): ?>
                <a href="#<?php echo $anchor; ?>" class="bg-white border border-gray-200 rounded-full px-4 py-1.5 font-bold text-gray-600 hover:text-pink-600 hover:border-pink-200"><?php echo $label; ?></a>
            <?php endforeach; ?>
        </nav>

        <?php if ($message): ?>
            <script>showToast(<?php echo json_encode($message); ?>, 'success');</script>
        <?php endif; ?>

        <form method="POST" action="verhuizingbeheer.php" class="space-y-6">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="delete_item" value="">
            <!-- Enter in een tekstveld gebruikt de eerste submitknop van het formulier:
                 dat moet "Opslaan" zijn, niet de verwijder/omhoog-knop van het eerste blok. -->
            <button type="submit" name="save" value="1" tabindex="-1" aria-hidden="true" style="position:absolute;left:-9999px;width:1px;height:1px;overflow:hidden;"></button>

            <?php foreach ($text_sections as $section_key => $section): ?>
                <section id="<?php echo $section_key; ?>" class="<?php echo $card_class; ?> scroll-mt-24">
                    <h2 class="text-xl font-bold text-gray-800"><?php echo htmlspecialchars($section['title']); ?></h2>
                    <p class="text-sm text-gray-500 mb-5"><?php echo htmlspecialchars($section['desc']); ?></p>
                    <div class="space-y-4">
                        <?php foreach ($section['fields'] as $key => $field): ?>
                            <div>
                                <label for="f-<?php echo $key; ?>" class="font-bold text-gray-700 text-sm block mb-1"><?php echo htmlspecialchars($field['label']); ?></label>
                                <?php if ($field['type'] === 'textarea'): ?>
                                    <textarea id="f-<?php echo $key; ?>" name="content[<?php echo $key; ?>]" rows="3" class="<?php echo $input_class; ?>"><?php echo $val($key); ?></textarea>
                                <?php else: ?>
                                    <input id="f-<?php echo $key; ?>" type="text" name="content[<?php echo $key; ?>]" value="<?php echo $val($key); ?>" placeholder="<?php echo htmlspecialchars($field['placeholder'] ?? ''); ?>" class="<?php echo $input_class; ?>">
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endforeach; ?>

            <?php foreach ($item_types as $soort => $type): $kop_key = $soort === 'uitleg' ? 'details_title' : 'faq_title'; ?>
                <section id="<?php echo $soort; ?>" class="<?php echo $card_class; ?> scroll-mt-24">
                    <h2 class="text-xl font-bold text-gray-800"><?php echo htmlspecialchars($type['title']); ?></h2>
                    <p class="text-sm text-gray-500 mb-5"><?php echo htmlspecialchars($type['desc']); ?></p>

                    <label class="font-bold text-gray-700 text-sm block mb-1"><?php echo htmlspecialchars($extra_fields[$kop_key]['label']); ?></label>
                    <input type="text" name="content[<?php echo $kop_key; ?>]" value="<?php echo $val($kop_key); ?>" class="<?php echo $input_class; ?> mb-6">

                    <div class="space-y-3">
                        <?php foreach ($items[$soort] as $i => $item): $id = htmlspecialchars($item['id']); ?>
                            <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                                <div class="flex gap-2 items-start mb-2">
                                    <input type="text" name="items[<?php echo $id; ?>][titel]" value="<?php echo htmlspecialchars($item['titel']); ?>" required class="<?php echo $input_class; ?> font-bold bg-white">
                                    <?php if ($i > 0): ?>
                                        <button type="submit" name="move_up" value="<?php echo $id; ?>" title="Omhoog" class="shrink-0 px-3 py-2 rounded-lg border border-gray-200 bg-white text-gray-500 hover:text-pink-600">&uarr;</button>
                                    <?php endif; ?>
                                    <button type="submit" formnovalidate onclick="this.form.delete_item.value='<?php echo $id; ?>'; return confirmSubmit(event, 'Weet je zeker dat je dit wilt verwijderen?');" title="Verwijderen" class="shrink-0 px-3 py-2 rounded-lg border border-red-200 bg-white text-red-500 hover:bg-red-50">&times;</button>
                                </div>
                                <textarea name="items[<?php echo $id; ?>][tekst]" rows="3" required class="<?php echo $input_class; ?> bg-white"><?php echo htmlspecialchars($item['tekst']); ?></textarea>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="mt-4 border-2 border-dashed border-pink-200 rounded-lg p-4 bg-pink-50/40">
                        <p class="text-sm font-bold text-pink-700 mb-2">+ Nieuw toevoegen</p>
                        <input type="text" name="new[<?php echo $soort; ?>][titel]" list="suggesties-<?php echo $soort; ?>" placeholder="<?php echo htmlspecialchars($type['titel_label']); ?> (kies een suggestie of typ zelf)" class="<?php echo $input_class; ?> bg-white mb-2">
                        <datalist id="suggesties-<?php echo $soort; ?>">
                            <?php foreach ($type['suggesties'] as $s): ?><option value="<?php echo htmlspecialchars($s); ?>"><?php endforeach; ?>
                        </datalist>
                        <textarea name="new[<?php echo $soort; ?>][tekst]" rows="3" placeholder="<?php echo htmlspecialchars($type['tekst_label']); ?>" class="<?php echo $input_class; ?> bg-white"></textarea>
                        <p class="text-xs text-gray-500 mt-1">Wordt toegevoegd bij Opslaan, als beide velden zijn ingevuld.</p>
                    </div>
                </section>
            <?php endforeach; ?>

            <section id="formulier" class="<?php echo $card_class; ?> scroll-mt-24">
                <div class="flex justify-between items-start gap-4 mb-5">
                    <div>
                        <h2 class="text-xl font-bold text-gray-800">Contactformulier</h2>
                        <p class="text-sm text-gray-500">Klein formulier op de verhuispagina. Berichten komen binnen bij
                            <a href="#berichten" class="text-pink-600 font-bold hover:underline">Berichten</a> bovenaan deze pagina<?php if ($unread_verhuizing > 0): ?> — <span class="text-pink-600 font-bold"><?php echo $unread_verhuizing; ?> nieuw</span><?php endif; ?>.</p>
                    </div>
                    <?php echo render_toggle('form_visible', $is_on('form_title')); ?>
                </div>
                <div class="space-y-4">
                    <?php foreach (['form_title', 'form_text'] as $key): $field = $extra_fields[$key]; ?>
                        <div>
                            <label class="font-bold text-gray-700 text-sm block mb-1"><?php echo htmlspecialchars($field['label']); ?></label>
                            <?php if ($field['type'] === 'textarea'): ?>
                                <textarea name="content[<?php echo $key; ?>]" rows="2" class="<?php echo $input_class; ?>"><?php echo $val($key); ?></textarea>
                            <?php else: ?>
                                <input type="text" name="content[<?php echo $key; ?>]" value="<?php echo $val($key); ?>" class="<?php echo $input_class; ?>">
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <section id="melding" class="bg-yellow-50 p-6 rounded-xl shadow-sm border border-yellow-200 scroll-mt-24">
                <div class="flex justify-between items-start gap-4 mb-4">
                    <div>
                        <h2 class="text-xl font-bold text-gray-800">Melding op de website</h2>
                        <p class="text-sm text-gray-600">Gele balk op elke pagina, vast onder het menu, die naar de verhuispagina linkt. Eén regel: te lange tekst wordt afgekapt, dus houd hem kort.</p>
                    </div>
                    <?php echo render_toggle('banner_visible', $is_on('banner_text')); ?>
                </div>
                <input type="text" name="content[banner_text]" value="<?php echo $val('banner_text'); ?>" class="<?php echo $input_class; ?>">
            </section>

            <div class="sticky bottom-6 z-10 flex justify-end">
                <button type="submit" name="save" value="1" class="bg-pink-600 text-white font-bold py-3 px-8 rounded-full hover:bg-pink-700 shadow-xl shadow-pink-600/30 transition-all transform hover:-translate-y-1 w-full md:w-auto">Opslaan</button>
            </div>
        </form>
    </div>
</body>
</html>
