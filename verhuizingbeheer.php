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

function verhuizing_set_content(string $website_id, string $key, string $value, string $type, string $label, string $group, int $visible): void
{
    global $pdo;
    $pdo->prepare(
        "INSERT INTO site_content (id, website_id, page, section_key, content_text, type, label, group_name, is_visible)
         VALUES (UUID(), ?, 'verhuizing', ?, ?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE content_text = VALUES(content_text), is_visible = VALUES(is_visible)"
    )->execute([$website_id, $key, $value, $type, $label, $group, $visible]);
}

function paths_list(?string $csv): array
{
    return $csv ? array_values(array_filter(explode(',', $csv))) : [];
}

// Paden die bij Opslaan moeten blijven: de oude min de aangevinkte "verwijderen".
function kept_paths(array $old): array
{
    return array_values(array_diff($old, (array)($_POST['remove_images'] ?? [])));
}

// De eerste afbeelding is de hoofdafbeelding. main_image is een bestaand pad of
// "new:<n>" (de n-de nieuw geüploade); die wordt vooraan gezet.
function order_with_main(array $keep, array $new): array
{
    $all = array_merge($keep, $new);
    $choice = (string)($_POST['main_image'] ?? '');
    if (strncmp($choice, 'new:', 4) === 0) {
        $main = $new[(int)substr($choice, 4)] ?? null;
    } else {
        $main = in_array($choice, $keep, true) ? $choice : null;
    }
    if ($main !== null) {
        $all = array_values(array_diff($all, [$main]));
        array_unshift($all, $main);
    }
    return $all;
}

// Een upload groter dan post_max_size komt binnen als lege $_POST (dus ook zonder
// CSRF-token). Geef dan een duidelijke melding i.p.v. "ongeldige aanvraag".
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST) && (int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
    flash_redirect('verhuizingbeheer.php', '', 'De foto\'s waren samen te groot om in één keer te versturen (max ' . ini_get('post_max_size') . '). Voeg ze in kleinere groepjes toe.');
}

$post_action = $_SERVER['REQUEST_METHOD'] === 'POST' ? (string)($_POST['action'] ?? '') : '';

// Fotogalerij op de verhuispagina (site_content 'fotos' = komma-gescheiden paden).
if ($post_action === 'fotos_save') {
    csrf_verify();
    $stmt = $pdo->prepare("SELECT content_text FROM site_content WHERE website_id = ? AND page = 'verhuizing' AND section_key = 'fotos'");
    $stmt->execute([$website_id]);
    $old = paths_list($stmt->fetchColumn() ?: null);
    $keep = kept_paths($old);
    try {
        $new = save_uploaded_images('images', $website_id);
    } catch (UploadException $e) {
        flash_redirect('verhuizingbeheer.php#fotos', '', $e->getMessage());
    }
    verhuizing_set_content($website_id, 'fotos', implode(',', order_with_main($keep, $new)), 'text', "Foto's op de verhuispagina", 'fotos', 1);
    verhuizing_set_content($website_id, 'fotos_title', trim((string)($_POST['fotos_title'] ?? '')), 'text', "Kop boven de foto's", 'fotos', isset($_POST['fotos_visible']) ? 1 : 0);
    foreach (array_diff($old, $keep) as $p) {
        delete_uploaded_file($p);
    }
    flash_redirect('verhuizingbeheer.php#fotos', "Foto's opgeslagen.");
}

// Kop/intro en hoofdschakelaar van de blog.
if ($post_action === 'blog_settings') {
    csrf_verify();
    $visible = isset($_POST['blog_visible']) ? 1 : 0;
    verhuizing_set_content($website_id, 'blog_title', trim((string)($_POST['blog_title'] ?? '')), 'text', 'Kop boven de blog', 'blog', $visible);
    verhuizing_set_content($website_id, 'blog_text', trim((string)($_POST['blog_text'] ?? '')), 'textarea', 'Intro van de blog', 'blog', 1);
    flash_redirect('verhuizingbeheer.php#blog', $visible ? 'Blog bijgewerkt.' : 'Blog bijgewerkt. De blog staat uit op de website.');
}

// Blogbericht toevoegen (id leeg) of bijwerken.
if ($post_action === 'blog_save') {
    csrf_verify();
    $id = (string)($_POST['id'] ?? '');
    $titel = trim((string)($_POST['titel'] ?? ''));
    $tekst = trim((string)($_POST['tekst'] ?? ''));
    $datum_obj = DateTime::createFromFormat('!Y-m-d', (string)($_POST['datum'] ?? ''));
    $datum = $datum_obj ? $datum_obj->format('Y-m-d') : date('Y-m-d');
    $zichtbaar = isset($_POST['zichtbaar']) ? 1 : 0;
    if ($titel === '' || $tekst === '') {
        flash_redirect('verhuizingbeheer.php#blog', '', 'Vul een titel en een tekst in.');
    }

    $old = [];
    if ($id !== '') {
        $stmt = $pdo->prepare("SELECT image_paths FROM verhuizing_blog WHERE id = ? AND website_id = ?");
        $stmt->execute([$id, $website_id]);
        $row = $stmt->fetch();
        if (!$row) {
            flash_redirect('verhuizingbeheer.php#blog', '', 'Blogbericht niet gevonden.');
        }
        $old = paths_list($row['image_paths']);
    }
    $keep = kept_paths($old);
    try {
        $new = save_uploaded_images('images', $website_id);
    } catch (UploadException $e) {
        flash_redirect('verhuizingbeheer.php#blog', '', $e->getMessage());
    }
    $paths = order_with_main($keep, $new);
    $paths_csv = $paths ? implode(',', $paths) : null;

    if ($id === '') {
        $pdo->prepare("INSERT INTO verhuizing_blog (id, website_id, titel, tekst, image_paths, datum, is_visible) VALUES (UUID(), ?, ?, ?, ?, ?, ?)")
            ->execute([$website_id, $titel, $tekst, $paths_csv, $datum, $zichtbaar]);
    } else {
        $pdo->prepare("UPDATE verhuizing_blog SET titel = ?, tekst = ?, image_paths = ?, datum = ?, is_visible = ? WHERE id = ? AND website_id = ?")
            ->execute([$titel, $tekst, $paths_csv, $datum, $zichtbaar, $id, $website_id]);
    }
    foreach (array_diff($old, $keep) as $p) {
        delete_uploaded_file($p);
    }
    flash_redirect('verhuizingbeheer.php#blog', $id === '' ? 'Blogbericht geplaatst.' : 'Blogbericht bijgewerkt.');
}

// Snel tonen/verbergen vanuit de lijst.
if ($post_action === 'blog_toggle') {
    csrf_verify();
    $pdo->prepare("UPDATE verhuizing_blog SET is_visible = 1 - is_visible WHERE id = ? AND website_id = ?")
        ->execute([(string)($_POST['id'] ?? ''), $website_id]);
    flash_redirect('verhuizingbeheer.php#blog', 'Zichtbaarheid aangepast.');
}

if ($post_action === 'blog_delete') {
    csrf_verify();
    $stmt = $pdo->prepare("SELECT image_paths FROM verhuizing_blog WHERE id = ? AND website_id = ?");
    $stmt->execute([(string)($_POST['id'] ?? ''), $website_id]);
    $row = $stmt->fetch();
    if ($row) {
        $pdo->prepare("DELETE FROM verhuizing_blog WHERE id = ? AND website_id = ?")->execute([(string)$_POST['id'], $website_id]);
        foreach (paths_list($row['image_paths']) as $p) {
            delete_uploaded_file($p);
        }
    }
    flash_redirect('verhuizingbeheer.php#blog', 'Blogbericht verwijderd.');
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

[$message, $error] = get_flash_messages();

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

$fotos = paths_list($values['fotos']['content_text'] ?? null);
$stmt = $pdo->prepare("SELECT * FROM verhuizing_blog WHERE website_id = ? ORDER BY datum DESC, created_at DESC");
$stmt->execute([$website_id]);
$blog_posts = $stmt->fetchAll();
// Grootste bestand dat PHP per afbeelding accepteert (en ons eigen maximum van 5MB).
$max_upload_bytes = min(ini_size_bytes((string)ini_get('upload_max_filesize')), 5242880);

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

// Eén fototegel: × markeert om te verwijderen, de knop linksonder kiest de
// hoofdafbeelding (radio main_image). Opmaak van de staten: .img-tile in <style>.
function render_image_tile(string $src, string $main_value, bool $is_main, string $remove_value = ''): string
{
    $html = '<div class="img-tile relative aspect-square rounded-lg overflow-hidden border border-gray-200 bg-gray-50">'
        . '<img src="' . htmlspecialchars($src) . '" alt="" class="w-full h-full object-cover">';
    if ($remove_value !== '') {
        $html .= '<label class="tile-remove absolute top-1 right-1 w-7 h-7 rounded-full text-sm font-bold shadow cursor-pointer bg-red-600 text-white hover:bg-red-700 flex items-center justify-center" title="Verwijderen / terugzetten">'
            . '<input type="checkbox" name="remove_images[]" value="' . htmlspecialchars($remove_value) . '" class="sr-only">'
            . '<span class="tile-x">&times;</span><span class="tile-undo">&#8634;</span></label>'
            . '<span class="tile-removed-note absolute inset-x-0 bottom-1 text-center text-[11px] font-semibold text-red-600">Wordt verwijderd</span>';
    }
    $html .= '<label class="tile-main absolute bottom-1 left-1 cursor-pointer" title="Als hoofdafbeelding gebruiken">'
        . '<input type="radio" name="main_image" value="' . htmlspecialchars($main_value) . '" ' . ($is_main ? 'checked' : '') . ' class="sr-only">'
        . '<span class="tile-main-off text-[10px] font-bold bg-white/90 text-gray-700 hover:text-pink-600 px-1.5 py-0.5 rounded shadow">&#9734; Maak hoofd</span>'
        . '<span class="tile-main-on text-[10px] font-bold bg-pink-600 text-white px-1.5 py-0.5 rounded shadow">&#9733; Hoofdafbeelding</span>'
        . '</label>'
        . '</div>';
    return $html;
}

// Bestaande afbeeldingen + knop om er meer toe te voegen. De gekozen bestanden
// komen in images[]; zie het script onder de Foto's-sectie.
function render_image_picker(array $paths): string
{
    $html = '<div class="grid grid-cols-3 sm:grid-cols-5 gap-3 mb-3' . ($paths ? '' : ' hidden') . '" data-existing>';
    foreach ($paths as $i => $path) {
        $html .= render_image_tile($path, $path, $i === 0, $path);
    }
    $html .= '</div>'
        . '<div class="grid grid-cols-3 sm:grid-cols-5 gap-3 mb-3 hidden" data-new></div>'
        . '<label class="inline-flex items-center gap-2 cursor-pointer bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-bold py-2 px-4 rounded-lg transition-colors">'
        . '+ Afbeeldingen toevoegen'
        . '<input type="file" accept="image/jpeg,image/png,image/webp,image/gif" multiple class="hidden" data-picker>'
        . '</label>'
        . '<input type="file" name="images[]" multiple class="hidden" data-files>';
    return '<div data-image-picker>' . $html . '</div>';
}

function format_datum_nl(string $datum): string
{
    $maanden = ['januari', 'februari', 'maart', 'april', 'mei', 'juni', 'juli', 'augustus', 'september', 'oktober', 'november', 'december'];
    $t = strtotime($datum);
    return date('j', $t) . ' ' . $maanden[(int)date('n', $t) - 1] . ' ' . date('Y', $t);
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <title>Verhuizing - <?php echo htmlspecialchars(current_website()['company_name'] ?? 'Webius Portaal'); ?></title>
    <?php include 'includes/head.php'; ?>
    <style>
        summary::-webkit-details-marker { display: none; }
        .img-tile:has(.tile-main input:checked) { outline: 3px solid #db2777; outline-offset: 1px; }
        .tile-main-on, .tile-undo, .tile-removed-note { display: none; }
        .tile-main:has(input:checked) .tile-main-off { display: none; }
        .tile-main:has(input:checked) .tile-main-on { display: inline; }
        .tile-remove:has(input:checked) { background: #fff; color: #334155; }
        .tile-remove:has(input:checked) .tile-x { display: none; }
        .tile-remove:has(input:checked) .tile-undo { display: inline; }
        .img-tile:has(.tile-remove input:checked) img { opacity: .3; filter: grayscale(1); }
        .img-tile:has(.tile-remove input:checked) .tile-removed-note { display: block; }
        .img-tile:has(.tile-remove input:checked) .tile-main { display: none; }
    </style>
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
            <?php foreach (['blog' => 'Blog', 'fotos' => "Foto's", 'gegevens' => 'Gegevens', 'pagina' => 'Teksten', 'uitleg' => 'Uitleg', 'faq' => 'FAQ', 'formulier' => 'Formulier', 'melding' => 'Melding', 'berichten' => 'Berichten' . ($unread_verhuizing ? " ($unread_verhuizing)" : '')] as $anchor => $label): ?>
                <a href="#<?php echo $anchor; ?>" class="bg-white border border-gray-200 rounded-full px-4 py-1.5 font-bold text-gray-600 hover:text-pink-600 hover:border-pink-200"><?php echo $label; ?></a>
            <?php endforeach; ?>
        </nav>

        <?php if ($message): ?>
            <script>showToast(<?php echo json_encode($message); ?>, 'success');</script>
        <?php endif; ?>
        <?php if ($error): ?>
            <script>showToast(<?php echo json_encode($error); ?>, 'error');</script>
        <?php endif; ?>

        <!-- Blog: elk onderdeel heeft een eigen formulier, zodat foto's per bericht
             worden verstuurd en niet alles in één (te grote) upload belandt. -->
        <section id="blog" class="<?php echo $card_class; ?> mb-6 scroll-mt-24">
            <form method="POST" action="verhuizingbeheer.php">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="action" value="blog_settings">
                <div class="flex justify-between items-start gap-4 mb-5">
                    <div>
                        <h2 class="text-xl font-bold text-gray-800">Blog: volg de verhuizing</h2>
                        <p class="text-sm text-gray-500">Korte berichtjes met foto's over hoe het gaat. Nieuwste bovenaan. Per bericht wordt de hoofdafbeelding groot getoond, de rest als klikbare kleine foto's. Zonder zichtbare berichten wordt de blog niet getoond.</p>
                    </div>
                    <?php echo render_toggle('blog_visible', $is_on('blog_title')); ?>
                </div>
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label for="f-blog_title" class="font-bold text-gray-700 text-sm block mb-1">Kop boven de blog</label>
                        <input id="f-blog_title" type="text" name="blog_title" value="<?php echo $val('blog_title'); ?>" class="<?php echo $input_class; ?>">
                    </div>
                    <div>
                        <label for="f-blog_text" class="font-bold text-gray-700 text-sm block mb-1">Intro</label>
                        <input id="f-blog_text" type="text" name="blog_text" value="<?php echo $val('blog_text'); ?>" class="<?php echo $input_class; ?>">
                    </div>
                </div>
                <div class="flex justify-end mt-3">
                    <button type="submit" class="bg-white border border-gray-200 hover:border-pink-200 hover:text-pink-600 text-gray-600 text-sm font-bold px-4 py-2 rounded-lg">Kop en schakelaar opslaan</button>
                </div>
            </form>

            <form method="POST" action="verhuizingbeheer.php" enctype="multipart/form-data" class="mt-6 border-2 border-dashed border-pink-200 rounded-lg p-4 bg-pink-50/40">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="action" value="blog_save">
                <input type="hidden" name="id" value="">
                <p class="text-sm font-bold text-pink-700 mb-3">+ Nieuw blogbericht</p>
                <div class="grid md:grid-cols-[1fr_11rem] gap-2 mb-2">
                    <input type="text" name="titel" required placeholder="Titel, bijv. &quot;De sleutel is binnen!&quot;" class="<?php echo $input_class; ?> bg-white">
                    <input type="date" name="datum" value="<?php echo date('Y-m-d'); ?>" required class="<?php echo $input_class; ?> bg-white">
                </div>
                <textarea name="tekst" rows="4" required placeholder="Wat is er gebeurd?" class="<?php echo $input_class; ?> bg-white mb-3"></textarea>
                <?php echo render_image_picker([]); ?>
                <div class="flex flex-wrap justify-between items-center gap-3 mt-4">
                    <label class="inline-flex items-center gap-2 text-sm font-bold text-gray-600 cursor-pointer">
                        <input type="checkbox" name="zichtbaar" value="1" checked class="w-4 h-4 accent-pink-600"> Direct tonen op de website
                    </label>
                    <button type="submit" class="bg-pink-600 hover:bg-pink-700 text-white text-sm font-bold px-5 py-2 rounded-lg">Bericht plaatsen</button>
                </div>
            </form>

            <?php if ($blog_posts): ?>
                <div class="mt-6 space-y-3">
                    <?php foreach ($blog_posts as $post):
                        $pid = htmlspecialchars($post['id']);
                        $post_images = paths_list($post['image_paths']);
                        $zichtbaar = (int)$post['is_visible'] === 1;
                    ?>
                        <details class="group border border-gray-200 rounded-lg bg-gray-50 <?php echo $zichtbaar ? '' : 'opacity-75'; ?>">
                            <summary class="list-none cursor-pointer p-3 flex items-center gap-3">
                                <?php if ($post_images): ?>
                                    <img src="<?php echo htmlspecialchars($post_images[0]); ?>" alt="" class="w-12 h-12 rounded object-cover border border-gray-200 shrink-0">
                                <?php else: ?>
                                    <span class="w-12 h-12 rounded bg-pink-100 text-pink-600 flex items-center justify-center shrink-0 font-bold">&#9998;</span>
                                <?php endif; ?>
                                <span class="min-w-0 flex-1">
                                    <span class="flex items-center gap-2">
                                        <span class="font-bold text-gray-800 truncate"><?php echo htmlspecialchars($post['titel']); ?></span>
                                        <?php if (!$zichtbaar): ?><span class="text-[10px] uppercase font-bold px-1.5 py-0.5 rounded bg-gray-200 text-gray-600 shrink-0">Verborgen</span><?php endif; ?>
                                    </span>
                                    <span class="block text-xs text-gray-500"><?php echo format_datum_nl($post['datum']); ?><?php echo $post_images ? ' · ' . count($post_images) . ' foto' . (count($post_images) === 1 ? '' : "'s") : ''; ?></span>
                                </span>
                                <svg class="w-4 h-4 text-gray-400 shrink-0 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </summary>
                            <div class="px-3 pb-3">
                                <form method="POST" action="verhuizingbeheer.php" enctype="multipart/form-data" class="bg-white border border-gray-200 rounded-lg p-4">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="action" value="blog_save">
                                    <input type="hidden" name="id" value="<?php echo $pid; ?>">
                                    <div class="grid md:grid-cols-[1fr_11rem] gap-2 mb-2">
                                        <input type="text" name="titel" required value="<?php echo htmlspecialchars($post['titel']); ?>" class="<?php echo $input_class; ?> font-bold">
                                        <input type="date" name="datum" required value="<?php echo htmlspecialchars($post['datum']); ?>" class="<?php echo $input_class; ?>">
                                    </div>
                                    <textarea name="tekst" rows="5" required class="<?php echo $input_class; ?> mb-3"><?php echo htmlspecialchars($post['tekst']); ?></textarea>
                                    <?php echo render_image_picker($post_images); ?>
                                    <div class="flex flex-wrap justify-between items-center gap-3 mt-4">
                                        <label class="inline-flex items-center gap-2 text-sm font-bold text-gray-600 cursor-pointer">
                                            <input type="checkbox" name="zichtbaar" value="1" <?php echo $zichtbaar ? 'checked' : ''; ?> class="w-4 h-4 accent-pink-600"> Tonen op de website
                                        </label>
                                        <button type="submit" class="bg-pink-600 hover:bg-pink-700 text-white text-sm font-bold px-5 py-2 rounded-lg">Wijzigingen opslaan</button>
                                    </div>
                                </form>
                                <div class="flex flex-wrap gap-2 mt-3">
                                    <form method="POST" action="verhuizingbeheer.php">
                                        <?php echo csrf_field(); ?>
                                        <input type="hidden" name="action" value="blog_toggle">
                                        <input type="hidden" name="id" value="<?php echo $pid; ?>">
                                        <button type="submit" class="bg-white border border-gray-200 hover:border-pink-200 hover:text-pink-600 text-gray-600 text-sm font-bold px-4 py-2 rounded-lg"><?php echo $zichtbaar ? 'Verbergen' : 'Weer tonen'; ?></button>
                                    </form>
                                    <form method="POST" action="verhuizingbeheer.php" class="ml-auto">
                                        <?php echo csrf_field(); ?>
                                        <input type="hidden" name="action" value="blog_delete">
                                        <input type="hidden" name="id" value="<?php echo $pid; ?>">
                                        <button type="submit" onclick="return confirmSubmit(event, 'Dit blogbericht en de foto\'s ervan definitief verwijderen?');" class="bg-white border border-red-200 text-red-500 hover:bg-red-50 text-sm font-bold px-4 py-2 rounded-lg">Verwijderen</button>
                                    </form>
                                </div>
                            </div>
                        </details>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <!-- Fotogalerij op de verhuispagina -->
        <form id="fotos" method="POST" action="verhuizingbeheer.php" enctype="multipart/form-data" class="<?php echo $card_class; ?> mb-6 scroll-mt-24 block">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="action" value="fotos_save">
            <div class="flex justify-between items-start gap-4 mb-5">
                <div>
                    <h2 class="text-xl font-bold text-gray-800">Foto's</h2>
                    <p class="text-sm text-gray-500">Fotogalerij op de verhuispagina, bijvoorbeeld van de nieuwe winkel. Voeg er zoveel toe als je wilt. De <strong>hoofdafbeelding</strong> (roze omrand) wordt groot getoond, de rest als klikbare kleine foto's eronder. Kies een andere met "Maak hoofd"; verwijderen met &times;.</p>
                </div>
                <?php echo render_toggle('fotos_visible', $is_on('fotos_title')); ?>
            </div>
            <label for="f-fotos_title" class="font-bold text-gray-700 text-sm block mb-1">Kop boven de foto's</label>
            <input id="f-fotos_title" type="text" name="fotos_title" value="<?php echo $val('fotos_title'); ?>" class="<?php echo $input_class; ?> mb-5">
            <?php echo render_image_picker($fotos); ?>
            <div class="flex justify-end mt-4">
                <button type="submit" class="bg-pink-600 hover:bg-pink-700 text-white text-sm font-bold px-5 py-2 rounded-lg">Foto's opslaan</button>
            </div>
        </form>

        <script>
            // Meerdere keren "+ Afbeeldingen toevoegen" kiezen stapelt de bestanden op
            // (een gewone file-input vervangt de vorige keuze). De echte input
            // (images[]) wordt telkens opnieuw gevuld via DataTransfer.
            (function () {
                const MAX_BYTES = <?php echo (int)$max_upload_bytes; ?>;
                const MAX_LABEL = <?php echo json_encode(round($max_upload_bytes / 1024 / 1024, 1) . 'MB'); ?>;
                document.querySelectorAll('[data-image-picker]').forEach(function (root) {
                    const picker = root.querySelector('[data-picker]');
                    const filesInput = root.querySelector('[data-files]');
                    const grid = root.querySelector('[data-new]');
                    const tileTemplate = <?php echo json_encode(render_image_tile('', '', false)); ?>;
                    let files = [];
                    let mainFile = null; // gekozen nieuwe hoofdafbeelding (object uit files)

                    // Er moet altijd precies één (niet-verwijderde) hoofdafbeelding zijn.
                    function ensureMain() {
                        const radios = Array.from(root.querySelectorAll('input[name="main_image"]'));
                        const usable = radios.filter(r => !r.closest('.img-tile').querySelector('.tile-remove input:checked'));
                        if (!usable.some(r => r.checked) && usable.length) {
                            usable[0].checked = true;
                            usable[0].dispatchEvent(new Event('change', { bubbles: true }));
                        }
                    }

                    function sync() {
                        const dt = new DataTransfer();
                        files.forEach(f => dt.items.add(f.file));
                        filesInput.files = dt.files;
                        grid.innerHTML = '';
                        files.forEach(function (f, i) {
                            const wrap = document.createElement('div');
                            wrap.innerHTML = tileTemplate;
                            const tile = wrap.firstElementChild;
                            tile.querySelector('img').src = f.url;
                            const radio = tile.querySelector('input[name="main_image"]');
                            radio.value = 'new:' + i;
                            radio.checked = f === mainFile;
                            tile.insertAdjacentHTML('beforeend',
                                '<span class="absolute top-1 left-1 text-[10px] font-bold bg-slate-900/80 text-white px-1.5 py-0.5 rounded">Nieuw</span>'
                                + '<button type="button" aria-label="Verwijderen" class="absolute top-1 right-1 w-7 h-7 rounded-full text-sm font-bold shadow flex items-center justify-center bg-red-600 text-white hover:bg-red-700">&times;</button>');
                            tile.querySelector('button').addEventListener('click', function () {
                                URL.revokeObjectURL(f.url);
                                files.splice(i, 1);
                                if (mainFile === f) mainFile = null;
                                sync();
                            });
                            grid.appendChild(tile);
                        });
                        grid.classList.toggle('hidden', files.length === 0);
                        ensureMain();
                    }

                    root.addEventListener('change', function (e) {
                        if (e.target.name === 'main_image') {
                            const v = e.target.value;
                            mainFile = v.startsWith('new:') ? files[parseInt(v.slice(4), 10)] : null;
                        } else if (e.target.name === 'remove_images[]') {
                            ensureMain();
                        }
                    });

                    picker.addEventListener('change', function () {
                        const te_groot = [];
                        Array.from(picker.files).forEach(function (file) {
                            if (!file.type.startsWith('image/')) return;
                            if (file.size > MAX_BYTES) { te_groot.push(file.name); return; }
                            files.push({ file: file, url: URL.createObjectURL(file) });
                        });
                        picker.value = '';
                        if (te_groot.length) {
                            showToast('Te groot (max ' + MAX_LABEL + ' per foto): ' + te_groot.join(', '), 'error');
                        }
                        sync();
                    });
                });
            })();
        </script>

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
