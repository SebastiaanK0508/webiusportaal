<?php
require_once 'includes/init.php';
$website_id = require_website_context();

$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'algemeen';
$message = '';
$error = '';

// --- 1. VERWERK ALGEMENE TEKSTEN ---
if (isset($_POST['update_content'])) {
    csrf_verify();
    $pdo->prepare("UPDATE site_content SET is_visible = 0 WHERE website_id = ?")->execute([$website_id]);
    if (isset($_POST['content'])) {
        foreach ($_POST['content'] as $key => $value) {
            $is_visible = isset($_POST['visible'][$key]) ? 1 : 0;
            $stmt = $pdo->prepare("UPDATE site_content SET content_text = ?, is_visible = ? WHERE website_id = ? AND section_key = ?");
            $stmt->execute([$value, $is_visible, $website_id, $key]);
        }
    }
    $message = "Teksten en instellingen zijn succesvol bijgewerkt!";
}

// --- 1B. VERWERK ALGEMENE AFBEELDINGEN UPLOADEN ---
if (isset($_POST['update_images'])) {
    csrf_verify();
    $image_keys = ['hero_image', 'about_image_1', 'about_image_2'];
    try {
        foreach ($image_keys as $key) {
            $new_path = save_uploaded_image($key, $website_id);
            if ($new_path !== null) {
                $stmt_old = $pdo->prepare("SELECT content_text FROM site_content WHERE website_id = ? AND section_key = ?");
                $stmt_old->execute([$website_id, $key]);
                $old_image = $stmt_old->fetchColumn();
                delete_uploaded_file($old_image ?: null);

                $stmt = $pdo->prepare("UPDATE site_content SET content_text = ? WHERE website_id = ? AND section_key = ?");
                $stmt->execute([$new_path, $website_id, $key]);
            }
        }
        $message = "Afbeeldingen zijn succesvol bijgewerkt!";
    } catch (UploadException $e) {
        $error = $e->getMessage();
    }
}

// --- 1C. VERWERK ALGEMENE AFBEELDINGEN VERWIJDEREN ---
if (isset($_POST['delete_image'])) {
    csrf_verify();
    $key = $_POST['delete_image'];
    $allowed_keys = ['hero_image', 'about_image_1', 'about_image_2'];

    if (in_array($key, $allowed_keys, true)) {
        $stmt_old = $pdo->prepare("SELECT content_text FROM site_content WHERE website_id = ? AND section_key = ?");
        $stmt_old->execute([$website_id, $key]);
        $old_image = $stmt_old->fetchColumn();
        delete_uploaded_file($old_image ?: null);

        $stmt = $pdo->prepare("UPDATE site_content SET content_text = '' WHERE website_id = ? AND section_key = ?");
        $stmt->execute([$website_id, $key]);

        $message = "Afbeelding is verwijderd, de template wordt weer getoond!";
        $active_tab = 'images';
    }
}

// --- 2. VERWERK HOMEPAGE PRODUCTEN ---
if (isset($_POST['update_homepage_products'])) {
    csrf_verify();
    try {
        $pdo->prepare("UPDATE products SET show_on_homepage = 0 WHERE website_id = ?")->execute([$website_id]);

        if (isset($_POST['show_on_home']) && is_array($_POST['show_on_home'])) {
            $ids = array_keys($_POST['show_on_home']);
            if (!empty($ids)) {
                $inQuery = implode(',', array_fill(0, count($ids), '?'));
                $stmt = $pdo->prepare("UPDATE products SET show_on_homepage = 1 WHERE website_id = ? AND id IN ($inQuery)");
                $stmt->execute(array_merge([$website_id], $ids));
            }
        }
        $message = "Uitgelichte producten succesvol bijgewerkt!";
    } catch (PDOException $e) {
        $error = "Fout bij opslaan van de uitgelichte producten.";
    }
}

// --- 3. VERWERK PORTFOLIO ---
if (isset($_POST['add_portfolio'])) {
    csrf_verify();
    $title = trim($_POST['port_title'] ?? '');
    $desc = trim($_POST['port_desc'] ?? '');
    try {
        $target_file = save_uploaded_image('image', $website_id);
        if ($title === '' || $target_file === null) {
            $error = "Titel en foto zijn verplicht.";
        } else {
            $new_port_id = $pdo->query('SELECT UUID()')->fetchColumn();
            $stmt = $pdo->prepare("INSERT INTO portfolio (id, website_id, title, description, image_path) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$new_port_id, $website_id, $title, $desc, $target_file]);
            save_custom_field_values('portfolio', $new_port_id, $_POST['custom_fields'] ?? []);
            $message = "Foto succesvol toegevoegd!";
        }
    } catch (UploadException $e) {
        $error = $e->getMessage();
    }
}
if (isset($_POST['delete_portfolio'])) {
    csrf_verify();
    $id = $_POST['delete_portfolio'];
    $stmt = $pdo->prepare("SELECT image_path FROM portfolio WHERE id = ? AND website_id = ?");
    $stmt->execute([$id, $website_id]);
    $port = $stmt->fetch();
    if ($port) {
        delete_uploaded_file($port['image_path']);
        $stmt = $pdo->prepare("DELETE FROM portfolio WHERE id = ? AND website_id = ?");
        $stmt->execute([$id, $website_id]);
        delete_custom_field_values_for_entity($id);
        $message = "Foto is verwijderd!";
    }
    $active_tab = 'portfolio';
}

// --- 4. VERWERK USP's ---
if (isset($_POST['add_usp'])) {
    csrf_verify();
    $icon = trim($_POST['usp_icon'] ?? '');
    $title = trim($_POST['usp_title'] ?? '');
    $desc = trim($_POST['usp_desc'] ?? '');
    $new_usp_id = $pdo->query('SELECT UUID()')->fetchColumn();
    $stmt = $pdo->prepare("INSERT INTO usps (id, website_id, icon, title, description) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$new_usp_id, $website_id, $icon, $title, $desc]);
    save_custom_field_values('usp', $new_usp_id, $_POST['custom_fields'] ?? []);
    $message = "USP toegevoegd!";
}
if (isset($_POST['delete_usp'])) {
    csrf_verify();
    $stmt = $pdo->prepare("DELETE FROM usps WHERE id = ? AND website_id = ?");
    $stmt->execute([$_POST['delete_usp'], $website_id]);
    delete_custom_field_values_for_entity($_POST['delete_usp']);
    $message = "USP verwijderd!";
    $active_tab = 'usps';
}

// --- 5. VERWERK REVIEWS ---
if (isset($_POST['add_review'])) {
    csrf_verify();
    $name = trim($_POST['review_name'] ?? '');
    $text = trim($_POST['review_text'] ?? '');
    $stars = (int)($_POST['review_stars'] ?? 5);
    $new_review_id = $pdo->query('SELECT UUID()')->fetchColumn();
    $stmt = $pdo->prepare("INSERT INTO reviews (id, website_id, customer_name, review_text, stars) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$new_review_id, $website_id, $name, $text, $stars]);
    save_custom_field_values('review', $new_review_id, $_POST['custom_fields'] ?? []);
    $message = "Review toegevoegd!";
}
if (isset($_POST['delete_review'])) {
    csrf_verify();
    $stmt = $pdo->prepare("DELETE FROM reviews WHERE id = ? AND website_id = ?");
    $stmt->execute([$_POST['delete_review'], $website_id]);
    delete_custom_field_values_for_entity($_POST['delete_review']);
    $message = "Review verwijderd!";
    $active_tab = 'reviews';
}

// --- 6. VERWERK FAQ ---
if (isset($_POST['add_faq'])) {
    csrf_verify();
    $q = trim($_POST['faq_q'] ?? '');
    $a = trim($_POST['faq_a'] ?? '');
    $new_faq_id = $pdo->query('SELECT UUID()')->fetchColumn();
    $stmt = $pdo->prepare("INSERT INTO faqs (id, website_id, question, answer) VALUES (?, ?, ?, ?)");
    $stmt->execute([$new_faq_id, $website_id, $q, $a]);
    save_custom_field_values('faq', $new_faq_id, $_POST['custom_fields'] ?? []);
    $message = "FAQ toegevoegd!";
}
if (isset($_POST['delete_faq'])) {
    csrf_verify();
    $stmt = $pdo->prepare("DELETE FROM faqs WHERE id = ? AND website_id = ?");
    $stmt->execute([$_POST['delete_faq'], $website_id]);
    delete_custom_field_values_for_entity($_POST['delete_faq']);
    $message = "FAQ verwijderd!";
    $active_tab = 'faq';
}

$algemeen_content = $pdo->prepare("SELECT * FROM site_content WHERE website_id = ? AND section_key NOT LIKE 'service_%' AND section_key NOT LIKE '%\\_image%' ORDER BY page, section_key");
$algemeen_content->execute([$website_id]);
$algemeen_content = $algemeen_content->fetchAll();

$all_usps = $pdo->prepare("SELECT * FROM usps WHERE website_id = ? ORDER BY created_at ASC");
$all_usps->execute([$website_id]);
$all_usps = $all_usps->fetchAll();

$all_reviews = $pdo->prepare("SELECT * FROM reviews WHERE website_id = ? ORDER BY created_at ASC");
$all_reviews->execute([$website_id]);
$all_reviews = $all_reviews->fetchAll();

$all_faqs = $pdo->prepare("SELECT * FROM faqs WHERE website_id = ? ORDER BY created_at ASC");
$all_faqs->execute([$website_id]);
$all_faqs = $all_faqs->fetchAll();

$all_portfolio = $pdo->prepare("SELECT * FROM portfolio WHERE website_id = ? ORDER BY created_at DESC");
$all_portfolio->execute([$website_id]);
$all_portfolio = $all_portfolio->fetchAll();

$usp_custom_values = get_custom_field_value_map('usp', array_column($all_usps, 'id'));
$review_custom_values = get_custom_field_value_map('review', array_column($all_reviews, 'id'));
$faq_custom_values = get_custom_field_value_map('faq', array_column($all_faqs, 'id'));
$portfolio_custom_values = get_custom_field_value_map('portfolio', array_column($all_portfolio, 'id'));

function render_custom_field_badges(array $items)
{
    if (empty($items)) {
        return;
    }
    echo '<div class="flex flex-wrap gap-1.5 mt-2">';
    foreach ($items as $item) {
        echo '<span class="inline-flex items-center gap-1 bg-black/5 text-current text-[11px] font-medium px-2 py-0.5 rounded-full opacity-80">';
        echo '<strong>' . htmlspecialchars($item['label']) . ':</strong> ' . htmlspecialchars($item['value']);
        echo '</span>';
    }
    echo '</div>';
}

try {
    $all_products = $pdo->prepare("SELECT * FROM products WHERE website_id = ? ORDER BY sort_order ASC, title ASC");
    $all_products->execute([$website_id]);
    $all_products = $all_products->fetchAll();
} catch (PDOException $e) {
    $all_products = [];
}

$placeholder_img = 'https://placehold.co/600x400/fce7f3/db2777?text=Geen+Afbeelding';
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <title>Websitebeheer - <?php echo htmlspecialchars(current_website()['company_name'] ?? 'Webius Portaal'); ?></title>
    <?php include 'includes/head.php'; ?>
</head>
<body class="bg-gray-100 font-sans pb-24">
    <?php include 'includes/header.php'; ?>
    <div class="max-w-6xl mx-auto px-4 mt-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
            <h1 class="text-3xl font-bold text-gray-800">Homepagina <?php echo get_text('hero_title'); ?></h1>
            <a href="index.php" target="_blank" class="text-pink-600 hover:text-white font-bold bg-white hover:bg-pink-600 px-6 py-2 rounded-full shadow border border-pink-100 transition-all">Bekijk website &rarr;</a>
        </div>
        <?php if ($error): ?>
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 shadow-sm rounded-r-lg">
                <span class="font-medium"><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>
        <?php if ($message): ?>
            <div id="alert-message" class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 shadow-sm rounded-r-lg flex justify-between items-center transition-opacity duration-500">
                <span class="font-medium"><?php echo htmlspecialchars($message); ?></span>
                <button onclick="document.getElementById('alert-message').style.display='none'" class="text-green-700 hover:text-green-900 font-bold ml-4 focus:outline-none text-xl leading-none">
                    &times;
                </button>
            </div>
            <script>
                setTimeout(function() {
                    const alert = document.getElementById('alert-message');
                    if (alert) {
                        alert.classList.add('opacity-0');
                        setTimeout(() => alert.style.display = 'none', 500);
                    }
                }, 4000);
            </script>
        <?php endif; ?>
        <div class="border-b border-gray-200 mb-6 bg-white rounded-t-xl shadow-sm overflow-x-auto">
            <nav class="-mb-px flex space-x-6 px-4">
                <?php
                $tabs = ['algemeen' => 'Algemene Teksten', 'images' => 'Afbeeldingen', 'producten' => 'Producten (Home)', 'portfolio' => 'Portfolio', 'usps' => 'USP\'s', 'reviews' => 'Reviews', 'faq' => 'FAQ'];
                foreach($tabs as $key => $label):
                    $is_active = ($active_tab == $key);
                ?>
                <a href="websitebeheer.php?tab=<?php echo $key; ?>"
                class="tab-btn whitespace-nowrap py-4 px-1 border-b-2 font-bold text-sm <?php echo $is_active ? 'border-pink-600 text-pink-600' : 'border-transparent text-gray-500 hover:text-gray-700 font-medium'; ?>">
                    <?php echo $label; ?>
                </a>
                <?php endforeach; ?>
            </nav>
        </div>

        <!-- ALGEMENE TEKSTEN TAB -->
        <div id="algemeen" class="tab-content bg-white p-6 rounded-b-xl shadow-md border border-gray-200 border-t-0 mb-6 <?php echo $active_tab !== 'algemeen' ? 'hidden' : ''; ?>">
            <form method="POST" action="websitebeheer.php?tab=<?php echo $active_tab; ?>">
                <?php echo csrf_field(); ?>
                <div class="flex justify-between items-center mb-8 border-b pb-4">
                    <h2 class="text-2xl font-bold text-gray-800">Website Teksten & Instellingen</h2>
                    <button type="submit" name="update_content" class="bg-pink-600 text-white font-bold py-2 px-6 rounded-lg hover:bg-pink-700 shadow-sm transition-colors hidden md:block">Alles Opslaan</button>
                </div>
                <?php
                // Groepering: gebruikt eerst de group_name uit de database (zo komen
                // via super_velden.php toegevoegde velden vanzelf in de juiste, of een
                // eigen nieuwe, sectie terecht). Rijen zonder group_name (de historische
                // velden) vallen terug op de oude prefix-detectie, zodat de bestaande
                // indeling niet verandert.
                $group_meta = [
                    'hero' => ['icon' => '🏠', 'title' => 'Homepage Bovenkant (Hero)', 'desc' => 'De grote tekst bovenaan de website.'],
                    'about' => ['icon' => '👩', 'title' => 'Over Ons Sectie', 'desc' => 'Stel het bedrijf voor aan klanten.'],
                    'promo' => ['icon' => '🎁', 'title' => 'Aanbieding / Actie', 'desc' => 'Licht een tijdelijke actie uit.'],
                    'portfolio' => ['icon' => '📸', 'title' => 'Portfolio Sectie', 'desc' => 'Instellingen voor de fotogalerij.'],
                ];
                $labels = [
                    'hero_title' => 'Grote Hoofdtitel',
                    'hero_text' => 'Korte Introductie',
                    'about_title' => 'Titel (bijv. Even voorstellen)',
                    'about_text' => 'Het verhaal van het bedrijf',
                    'promo_title' => 'Naam van de actie',
                    'promo_text' => 'Uitleg en voorwaarden',
                    'portfolio_title' => 'Titel boven fotogalerij'
                ];

                $grouped_content = [];
                foreach (['hero', 'about', 'promo', 'portfolio'] as $gk) {
                    $grouped_content[$gk] = $group_meta[$gk] + ['items' => []];
                }
                foreach ($algemeen_content as $item) {
                    $k = $item['section_key'];
                    $group_key = !empty($item['group_name']) ? $item['group_name'] : null;
                    if (!$group_key) {
                        if (strpos($k, 'hero_') === 0) $group_key = 'hero';
                        elseif (strpos($k, 'about_') === 0) $group_key = 'about';
                        elseif (strpos($k, 'promo_') === 0) $group_key = 'promo';
                        elseif (strpos($k, 'portfolio_') === 0) $group_key = 'portfolio';
                        else $group_key = 'overig';
                    }
                    if (!isset($grouped_content[$group_key])) {
                        $meta = $group_meta[$group_key] ?? ['icon' => '📄', 'title' => ucfirst(str_replace('_', ' ', $group_key)), 'desc' => ''];
                        $grouped_content[$group_key] = $meta + ['items' => []];
                    }
                    $grouped_content[$group_key]['items'][] = $item;
                }
                // "Overig" komt altijd als laatste, ook als hij pas net is aangemaakt.
                $overig = $grouped_content['overig'] ?? (['icon' => '⚙️', 'title' => 'Overige Teksten', 'desc' => 'Andere instellingen op de website.', 'items' => []]);
                unset($grouped_content['overig']);
                $grouped_content['overig'] = $overig;
                ?>
                <div class="space-y-10">
                    <?php foreach ($grouped_content as $group_key => $group): ?>
                        <?php if (count($group['items']) > 0): ?>
                            <div class="bg-gray-50 rounded-2xl p-6 border border-gray-200">
                                <div class="mb-6 pb-3 border-b border-gray-200 flex flex-col md:flex-row md:items-center gap-2">
                                    <h3 class="text-xl font-bold text-gray-800 flex items-center gap-3">
                                        <span class="bg-white p-2 rounded-lg shadow-sm"><?php echo $group['icon']; ?></span>
                                        <?php echo $group['title']; ?>
                                    </h3>
                                    <p class="text-sm text-gray-500 md:ml-4 border-l-0 md:border-l-2 border-gray-300 md:pl-4"><?php echo $group['desc']; ?></p>
                                </div>
                                <div class="grid md:grid-cols-2 gap-6">
                                    <?php foreach ($group['items'] as $item): ?>
                                        <?php
                                            $key = $item['section_key'];
                                            $friendly_name = $labels[$key] ?? (!empty($item['label']) ? $item['label'] : ucfirst(str_replace('_', ' ', $key)));
                                        ?>
                                        <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm relative group">
                                            <div class="flex justify-between items-start mb-4">
                                                <div>
                                                    <label class="font-bold text-gray-800 text-sm block">
                                                        <?php echo htmlspecialchars($friendly_name); ?>
                                                    </label>
                                                </div>
                                                <label class="inline-flex items-center cursor-pointer">
                                                    <input type="checkbox" name="visible[<?php echo htmlspecialchars($key); ?>]" value="1" <?php echo $item['is_visible'] ? 'checked' : ''; ?> class="sr-only peer">
                                                    <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-pink-500"></div>
                                                </label>
                                            </div>
                                            <?php if (strpos($key, 'title') !== false): ?>
                                                <input type="text" name="content[<?php echo htmlspecialchars($key); ?>]" value="<?php echo htmlspecialchars($item['content_text']); ?>" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-pink-400 focus:border-pink-400 text-sm transition-all">
                                            <?php else: ?>
                                                <textarea name="content[<?php echo htmlspecialchars($key); ?>]" rows="4" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-pink-400 focus:border-pink-400 text-sm transition-all"><?php echo htmlspecialchars($item['content_text']); ?></textarea>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
                <div class="mt-8 sticky bottom-6 z-10 flex justify-end">
                    <button type="submit" name="update_content" class="bg-pink-600 text-white font-bold py-3 px-8 rounded-full hover:bg-pink-700 shadow-xl shadow-pink-600/30 transition-all transform hover:-translate-y-1 w-full md:w-auto">Wijzigingen Opslaan</button>
                </div>
            </form>
        </div>

        <!-- AFBEELDINGEN TAB -->
        <div id="images" class="tab-content bg-white p-6 rounded-b-xl shadow-md border border-gray-200 border-t-0 mb-6 <?php echo $active_tab !== 'images' ? 'hidden' : ''; ?>">
            <form method="POST" action="websitebeheer.php?tab=<?php echo $active_tab; ?>" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <div class="flex justify-between items-center mb-8 border-b pb-4">
                    <h2 class="text-2xl font-bold text-gray-800">Afbeeldingen Homepage</h2>
                    <button type="submit" name="update_images" class="bg-pink-600 text-white font-bold py-2 px-6 rounded-lg hover:bg-pink-700 shadow-sm transition-colors hidden md:block">Uploaden & Opslaan</button>
                </div>
                <div class="grid md:grid-cols-2 gap-6">
                    <?php
                    $image_fields = [
                        'hero_image' => ['title' => 'Hero Afbeelding (Bovenaan)', 'desc' => 'De grote foto die direct te zien is als de site laadt.'],
                        'about_image_1' => ['title' => 'Over Ons - Grote Afbeelding', 'desc' => 'De eerste/bovenste afbeelding naast het verhaal.'],
                        'about_image_2' => ['title' => 'Over Ons - Kleine Afbeelding', 'desc' => 'De kleinere, overlappende afbeelding naast het verhaal.'],
                    ];
                    foreach ($image_fields as $field_key => $field):
                        $current_img = get_image($field_key, $placeholder_img);
                        $has_img = ($current_img !== $placeholder_img);
                    ?>
                    <div class="bg-gray-50 p-6 rounded-xl border border-gray-200">
                        <h3 class="font-bold text-gray-800 mb-2"><?php echo htmlspecialchars($field['title']); ?></h3>
                        <p class="text-sm text-gray-500 mb-4"><?php echo htmlspecialchars($field['desc']); ?></p>
                        <img src="<?php echo htmlspecialchars($current_img); ?>" class="w-full h-32 object-cover rounded mb-4 border border-gray-200" alt="Huidige afbeelding">
                        <div class="flex gap-2">
                            <input type="file" name="<?php echo $field_key; ?>" accept="image/jpeg,image/png,image/webp,image/gif" class="w-full text-sm flex-grow">
                            <?php if ($has_img): ?>
                                <button type="submit" formaction="websitebeheer.php?tab=images" name="delete_image" value="<?php echo $field_key; ?>" onclick="return confirm('Weet je zeker dat je deze afbeelding wilt verwijderen en terug wilt naar de template?');" class="bg-white border border-red-200 text-red-500 hover:bg-red-50 hover:text-red-700 px-3 py-1 rounded shadow-sm text-sm font-bold flex items-center whitespace-nowrap">
                                    Verwijder
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="mt-8 flex justify-end">
                    <button type="submit" name="update_images" class="bg-pink-600 text-white font-bold py-3 px-8 rounded-full hover:bg-pink-700 shadow-xl shadow-pink-600/30 transition-all transform hover:-translate-y-1 w-full md:w-auto">Wijzigingen Opslaan</button>
                </div>
            </form>
        </div>

        <!-- PRODUCTEN TAB -->
        <div id="producten" class="tab-content bg-white p-6 rounded-b-xl shadow-md border border-gray-200 border-t-0 mb-6 <?php echo $active_tab !== 'producten' ? 'hidden' : ''; ?>">
            <form method="POST" action="websitebeheer.php?tab=producten">
                <?php echo csrf_field(); ?>
                <div class="flex justify-between items-center mb-6 border-b pb-4">
                    <h2 class="text-2xl font-bold text-gray-800">Uitgelichte Producten op Homepage</h2>
                    <button type="submit" name="update_homepage_products" class="bg-pink-600 text-white font-bold py-2 px-6 rounded-lg hover:bg-pink-700 shadow-sm transition-colors hidden md:block">Opslaan</button>
                </div>

                <p class="text-gray-600 mb-8 bg-pink-50 p-4 rounded-lg border border-pink-100">Selecteer hieronder welke producten op de homepagina uitgelicht moeten worden door de schakelaar aan te zetten.</p>

                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 mb-10">
                    <?php if (empty($all_products)): ?>
                        <div class="col-span-full p-4 bg-gray-50 text-gray-500 text-center rounded-lg border border-gray-200">
                            Geen producten gevonden.
                        </div>
                    <?php else: ?>
                        <?php foreach ($all_products as $product): ?>
                            <div class="bg-gray-50 p-5 rounded-lg border border-gray-200 flex flex-col justify-between hover:border-pink-300 transition-colors shadow-sm">
                                <div class="flex justify-between items-start mb-4">
                                    <div class="flex-grow pr-4">
                                        <h3 class="font-bold text-gray-800 text-lg leading-tight"><?php echo htmlspecialchars($product['title']); ?></h3>
                                        <p class="text-sm text-gray-600 mt-2 line-clamp-2"><?php echo htmlspecialchars($product['description']); ?></p>
                                    </div>
                                    <label class="inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="show_on_home[<?php echo htmlspecialchars($product['id']); ?>]" value="1" <?php echo (isset($product['show_on_homepage']) && $product['show_on_homepage']) ? 'checked' : ''; ?> class="sr-only peer">
                                        <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-pink-500"></div>
                                    </label>
                                </div>
                                <div class="border-t border-gray-200 pt-3">
                                    <span class="font-bold text-pink-600 text-lg">&euro; <?php echo number_format($product['price'], 2, ',', '.'); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="mt-8 flex justify-end">
                    <button type="submit" name="update_homepage_products" class="bg-pink-600 text-white font-bold py-3 px-8 rounded-full hover:bg-pink-700 shadow-xl shadow-pink-600/30 transition-all transform hover:-translate-y-1 w-full md:w-auto">Wijzigingen Opslaan</button>
                </div>
            </form>
        </div>

        <!-- PORTFOLIO TAB -->
        <div id="portfolio" class="tab-content bg-white p-6 rounded-b-xl shadow-md border border-gray-200 border-t-0 mb-6 <?php echo $active_tab !== 'portfolio' ? 'hidden' : ''; ?>">
            <div class="grid md:grid-cols-3 gap-6 mb-10">
                <?php foreach ($all_portfolio as $port): ?>
                    <div class="bg-gray-50 rounded-lg border border-gray-200 overflow-hidden flex flex-col shadow-sm">
                        <img src="<?php echo htmlspecialchars($port['image_path']); ?>" alt="<?php echo htmlspecialchars($port['title']); ?>" class="w-full h-48 object-cover">
                        <div class="p-4 flex-grow">
                            <h3 class="font-bold text-gray-800"><?php echo htmlspecialchars($port['title']); ?></h3>
                            <p class="text-sm text-gray-600 mt-1"><?php echo htmlspecialchars($port['description']); ?></p>
                            <?php render_custom_field_badges($portfolio_custom_values[$port['id']] ?? []); ?>
                        </div>
                        <div class="p-4 border-t border-gray-200 bg-white">
                            <form method="POST" action="websitebeheer.php?tab=portfolio" onsubmit="return confirm('Zeker weten dat je deze foto wilt verwijderen?');">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="delete_portfolio" value="<?php echo htmlspecialchars($port['id']); ?>">
                                <button type="submit" class="text-red-500 hover:text-red-700 text-sm font-bold bg-white px-3 py-2 rounded border border-red-100 block text-center w-full">Verwijderen</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <form method="POST" action="websitebeheer.php?tab=<?php echo $active_tab; ?>" enctype="multipart/form-data" class="bg-pink-50 p-6 rounded-xl border border-pink-100">
                <?php echo csrf_field(); ?>
                <h2 class="font-bold mb-4">Nieuwe Foto Toevoegen</h2>
                <input type="text" name="port_title" required placeholder="Titel werk" class="w-full px-4 py-2 border rounded-md mb-4">
                <textarea name="port_desc" rows="3" placeholder="Beschrijving..." class="w-full px-4 py-2 border rounded-md mb-4"></textarea>
                <input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif" required class="w-full text-gray-700 bg-white border border-gray-300 rounded-md py-2 px-3 mb-6">
                <?php render_custom_field_inputs('portfolio'); ?>
                <button type="submit" name="add_portfolio" class="bg-pink-600 text-white font-bold py-3 px-6 rounded-lg hover:bg-pink-700 mt-4">+ Foto Uploaden</button>
            </form>
        </div>

        <!-- USPS TAB -->
        <div id="usps" class="tab-content bg-white p-6 rounded-b-xl shadow-md border border-gray-200 border-t-0 mb-6 <?php echo $active_tab !== 'usps' ? 'hidden' : ''; ?>">
            <div class="grid md:grid-cols-3 gap-4 mb-10">
                <?php foreach ($all_usps as $usp): ?>
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 relative pt-8 mt-4">
                        <div class="absolute -top-4 left-4 text-3xl bg-white rounded-full px-2 shadow-sm"><?php echo htmlspecialchars($usp['icon']); ?></div>
                        <h3 class="font-bold text-gray-800"><?php echo htmlspecialchars($usp['title']); ?></h3>
                        <p class="text-sm text-gray-600 mb-4"><?php echo htmlspecialchars($usp['description']); ?></p>
                        <?php render_custom_field_badges($usp_custom_values[$usp['id']] ?? []); ?>
                        <form method="POST" action="websitebeheer.php?tab=usps">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="delete_usp" value="<?php echo htmlspecialchars($usp['id']); ?>">
                            <button type="submit" class="text-red-500 text-sm font-bold bg-white px-3 py-1 rounded border border-red-100">Verwijderen</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
            <form method="POST" action="websitebeheer.php?tab=<?php echo $active_tab; ?>" class="bg-green-50 p-6 rounded-xl border border-green-100">
                <?php echo csrf_field(); ?>
                <h2 class="font-bold mb-4">Nieuw Voordeel (USP) Toevoegen</h2>
                <div class="grid md:grid-cols-2 gap-4 mb-4">
                    <input type="text" name="usp_icon" required placeholder="Emoji (bijv. ⭐)" class="w-full px-4 py-2 border rounded-md">
                    <input type="text" name="usp_title" required placeholder="Titel (bijv. Ervaring)" class="w-full px-4 py-2 border rounded-md">
                </div>
                <textarea name="usp_desc" rows="2" required placeholder="Korte uitleg..." class="w-full px-4 py-2 border rounded-md mb-4"></textarea>
                <?php render_custom_field_inputs('usp'); ?>
                <button type="submit" name="add_usp" class="bg-green-600 text-white font-bold py-2 px-6 rounded-lg mt-4">+ USP Toevoegen</button>
            </form>
        </div>

        <!-- REVIEWS TAB -->
        <div id="reviews" class="tab-content bg-white p-6 rounded-b-xl shadow-md border border-gray-200 border-t-0 mb-6 <?php echo $active_tab !== 'reviews' ? 'hidden' : ''; ?>">
            <div class="grid md:grid-cols-2 gap-4 mb-10">
                <?php foreach ($all_reviews as $review): ?>
                    <?php $stars = isset($review['stars']) ? (int)$review['stars'] : 5; ?>
                    <div class="bg-slate-800 text-white p-6 rounded-lg relative">
                        <div class="text-pink-400 mb-2 text-sm tracking-widest">
                            <?php echo str_repeat('✦ ', $stars); ?>
                        </div>
                        <p class="italic mb-4">"<?php echo htmlspecialchars($review['review_text']); ?>"</p>
                        <div class="font-bold text-pink-400">- <?php echo htmlspecialchars($review['customer_name']); ?></div>
                        <?php render_custom_field_badges($review_custom_values[$review['id']] ?? []); ?>
                        <form method="POST" action="websitebeheer.php?tab=reviews" class="absolute top-4 right-4">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="delete_review" value="<?php echo htmlspecialchars($review['id']); ?>">
                            <button type="submit" class="text-red-400 hover:text-red-300 text-sm bg-slate-700 px-2 py-1 rounded">X</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
            <form method="POST" action="websitebeheer.php?tab=<?php echo $active_tab; ?>" class="bg-slate-100 p-6 rounded-xl border border-slate-200">
                <?php echo csrf_field(); ?>
                <h2 class="font-bold mb-4">Nieuwe Review Toevoegen</h2>
                <div class="grid md:grid-cols-2 gap-4 mb-4">
                    <input type="text" name="review_name" required placeholder="Naam klant" class="w-full px-4 py-2 border rounded-md">
                    <select name="review_stars" class="w-full px-4 py-2 border rounded-md" required>
                        <option value="5">5 Sterren</option>
                        <option value="4">4 Sterren</option>
                        <option value="3">3 Sterren</option>
                        <option value="2">2 Sterren</option>
                        <option value="1">1 Ster</option>
                    </select>
                </div>
                <textarea name="review_text" rows="3" required placeholder="Wat zei de klant?..." class="w-full px-4 py-2 border rounded-md mb-4"></textarea>
                <?php render_custom_field_inputs('review'); ?>
                <button type="submit" name="add_review" class="bg-slate-800 text-white font-bold py-2 px-6 rounded-lg mt-4">+ Review Toevoegen</button>
            </form>
        </div>

        <!-- FAQ TAB -->
        <div id="faq" class="tab-content bg-white p-6 rounded-b-xl shadow-md border border-gray-200 border-t-0 mb-6 <?php echo $active_tab !== 'faq' ? 'hidden' : ''; ?>">
            <div class="space-y-4 mb-10">
                <?php foreach ($all_faqs as $faq): ?>
                    <div class="bg-white border border-gray-200 p-4 rounded-lg flex justify-between items-start">
                        <div>
                            <h3 class="font-bold text-gray-800 mb-1">Q: <?php echo htmlspecialchars($faq['question']); ?></h3>
                            <p class="text-gray-600">A: <?php echo htmlspecialchars($faq['answer']); ?></p>
                            <?php render_custom_field_badges($faq_custom_values[$faq['id']] ?? []); ?>
                        </div>
                        <form method="POST" action="websitebeheer.php?tab=faq" class="ml-4">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="delete_faq" value="<?php echo htmlspecialchars($faq['id']); ?>">
                            <button type="submit" class="text-red-500 hover:text-red-700 font-bold border p-1 rounded">X</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
            <form method="POST" action="websitebeheer.php?tab=<?php echo $active_tab; ?>" class="bg-gray-50 p-6 rounded-xl border border-gray-200">
                <?php echo csrf_field(); ?>
                <h2 class="font-bold mb-4">Nieuwe Vraag Toevoegen</h2>
                <input type="text" name="faq_q" required placeholder="De vraag?" class="w-full px-4 py-2 border rounded-md mb-4 font-bold">
                <textarea name="faq_a" rows="3" required placeholder="Het antwoord..." class="w-full px-4 py-2 border rounded-md mb-4"></textarea>
                <?php render_custom_field_inputs('faq'); ?>
                <button type="submit" name="add_faq" class="bg-gray-800 text-white font-bold py-2 px-6 rounded-lg mt-4">+ Vraag Toevoegen</button>
            </form>
        </div>
    </div>
</body>
</html>
