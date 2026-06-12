<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['user_uuid'])) {
    header('Location: login.php');
    exit();
}
require_once 'includes/functions.php';
include 'includes/init.php';
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'algemeen';
$message = '';

// --- 1. VERWERK ALGEMENE TEKSTEN ---
if (isset($_POST['update_content'])) {
    $pdo->query("UPDATE site_content SET is_visible = 0");
    if (isset($_POST['content'])) {
        foreach ($_POST['content'] as $key => $value) {
            $is_visible = isset($_POST['visible'][$key]) ? 1 : 0;
            $stmt = $pdo->prepare("UPDATE site_content SET content_text = ?, is_visible = ? WHERE section_key = ?");
            $stmt->execute([$value, $is_visible, $key]);
        }
    }
    $message = "Teksten en instellingen zijn succesvol bijgewerkt!";
}

// --- 1B. VERWERK ALGEMENE AFBEELDINGEN UPLOADEN ---
if (isset($_POST['update_images'])) {
    $image_keys = ['hero_image', 'about_image_1', 'about_image_2'];
    $target_dir = "uploads/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }
    
    foreach ($image_keys as $key) {
        if (isset($_FILES[$key]) && $_FILES[$key]['error'] == 0) {
            $file_name = time() . '_' . basename($_FILES[$key]["name"]);
            $target_file = $target_dir . $file_name;
            
            if (move_uploaded_file($_FILES[$key]["tmp_name"], $target_file)) {
                
                // 1. Haal de oude afbeelding op uit de database
                $stmt_old = $pdo->prepare("SELECT content_text FROM site_content WHERE section_key = ?");
                $stmt_old->execute([$key]);
                $old_image = $stmt_old->fetchColumn();
                
                // 2. Verwijder de oude afbeelding van de server als deze bestaat
                if (!empty($old_image) && file_exists($old_image)) {
                    unlink($old_image);
                }
                
                // 3. Update de database met het nieuwe pad
                $stmt = $pdo->prepare("UPDATE site_content SET content_text = ? WHERE section_key = ?");
                $stmt->execute([$target_file, $key]);
            }
        }
    }
    $message = "Afbeeldingen zijn succesvol bijgewerkt!";
}

// --- 1C. VERWERK ALGEMENE AFBEELDINGEN VERWIJDEREN ---
if (isset($_GET['delete_image'])) {
    $key = $_GET['delete_image'];
    $allowed_keys = ['hero_image', 'about_image_1', 'about_image_2'];
    
    if (in_array($key, $allowed_keys)) {
        $stmt_old = $pdo->prepare("SELECT content_text FROM site_content WHERE section_key = ?");
        $stmt_old->execute([$key]);
        $old_image = $stmt_old->fetchColumn();
        
        if (!empty($old_image) && file_exists($old_image)) {
            unlink($old_image); // Verwijder fysiek uit de uploads map
        }
        
        $stmt = $pdo->prepare("UPDATE site_content SET content_text = '' WHERE section_key = ?");
        $stmt->execute([$key]);
        
        $message = "Afbeelding is verwijderd, de template wordt weer getoond!";
        $active_tab = 'images'; // Zorg dat we op de juiste tab blijven
    }
}

// --- 2. VERWERK HOMEPAGE PRODUCTEN ---
if (isset($_POST['update_homepage_products'])) {
    try {
        // Zet eerst alle producten uit voor de homepage
        $pdo->query("UPDATE products SET show_on_homepage = 0");
        
        // Als er producten zijn aangevinkt, zet deze op 1 op basis van UUID
        if (isset($_POST['show_on_home']) && is_array($_POST['show_on_home'])) {
            $uuids = array_keys($_POST['show_on_home']);
            if (!empty($uuids)) {
                // Maak een string met vraagtekens voor de prepared statement (?,?,?)
                $inQuery = implode(',', array_fill(0, count($uuids), '?'));
                $stmt = $pdo->prepare("UPDATE products SET show_on_homepage = 1 WHERE uuid IN ($inQuery)");
                $stmt->execute($uuids);
            }
        }
        $message = "Uitgelichte producten succesvol bijgewerkt!";
    } catch (PDOException $e) {
        $message = "Fout bij opslaan: controleer of de tabel 'products' de kolom 'uuid' heeft.";
    }
}

// --- 3. VERWERK PORTFOLIO ---
if (isset($_POST['add_portfolio']) && isset($_FILES['image'])) {
    $title = $_POST['port_title'];
    $desc = $_POST['port_desc'];
    $target_dir = "uploads/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }
    $file_name = time() . '_' . basename($_FILES["image"]["name"]);
    $target_file = $target_dir . $file_name;
    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        $stmt = $pdo->prepare("INSERT INTO portfolio (title, description, image_path) VALUES (?, ?, ?)");
        $stmt->execute([$title, $desc, $target_file]);
        $message = "Foto succesvol toegevoegd!";
    } else {
        $message = "Fout bij het uploaden van de foto.";
    }
}
if (isset($_GET['delete_portfolio'])) {
    $stmt = $pdo->prepare("SELECT image_path FROM portfolio WHERE id = ?");
    $stmt->execute([$_GET['delete_portfolio']]);
    $port = $stmt->fetch();
    if ($port && file_exists($port['image_path'])) {
        unlink($port['image_path']); 
    }
    $stmt = $pdo->prepare("DELETE FROM portfolio WHERE id = ?");
    $stmt->execute([$_GET['delete_portfolio']]);
    $message = "Foto is verwijderd!";
}

// --- 4. VERWERK USP's ---
if (isset($_POST['add_usp'])) {
    $icon = $_POST['usp_icon'];
    $title = $_POST['usp_title'];
    $desc = $_POST['usp_desc'];
    $stmt = $pdo->prepare("INSERT INTO usps (uuid, icon, title, description) VALUES (UUID(), ?, ?, ?)");
    $stmt->execute([$icon, $title, $desc]);
    $message = "USP toegevoegd!";
}
if (isset($_GET['delete_usp'])) {
    $stmt = $pdo->prepare("DELETE FROM usps WHERE uuid = ?");
    $stmt->execute([$_GET['delete_usp']]);
    $message = "USP verwijderd!";
}

// --- 5. VERWERK REVIEWS ---
if (isset($_POST['add_review'])) {
    $name = $_POST['review_name'];
    $text = $_POST['review_text'];
    $stars = $_POST['review_stars']; 
    $stmt = $pdo->prepare("INSERT INTO reviews (uuid, customer_name, review_text, stars) VALUES (UUID(), ?, ?, ?)");
    $stmt->execute([$name, $text, $stars]);
    $message = "Review toegevoegd!";
}

if (isset($_GET['delete_review'])) {
    $stmt = $pdo->prepare("DELETE FROM reviews WHERE uuid = ?");
    $stmt->execute([$_GET['delete_review']]);
    $message = "Review verwijderd!";
}

// --- 6. VERWERK FAQ ---
if (isset($_POST['add_faq'])) {
    $q = $_POST['faq_q'];
    $a = $_POST['faq_a'];
    $stmt = $pdo->prepare("INSERT INTO faqs (uuid, question, answer) VALUES (UUID(), ?, ?)");
    $stmt->execute([$q, $a]);
    $message = "FAQ toegevoegd!";
}

if (isset($_GET['delete_faq'])) {
    $stmt = $pdo->prepare("DELETE FROM faqs WHERE uuid = ?");
    $stmt->execute([$_GET['delete_faq']]);
    $message = "FAQ verwijderd!";
}

$algemeen_content = $pdo->query("SELECT * FROM site_content WHERE section_key NOT LIKE 'service_%' AND section_key NOT LIKE '%_image%' ORDER BY page, section_key")->fetchAll();
$all_usps = $pdo->query("SELECT * FROM usps ORDER BY created_at ASC")->fetchAll();
$all_reviews = $pdo->query("SELECT * FROM reviews ORDER BY created_at ASC")->fetchAll();
$all_faqs = $pdo->query("SELECT * FROM faqs ORDER BY created_at ASC")->fetchAll();
$all_portfolio = $pdo->query("SELECT * FROM portfolio ORDER BY id DESC")->fetchAll();

try {
    $all_products = $pdo->query("SELECT * FROM products ORDER BY sort_order ASC, title ASC")->fetchAll();
} catch (PDOException $e) {
    $all_products = [];
}

$placeholder_img = 'https://placehold.co/600x400/fce7f3/db2777?text=Geen+Afbeelding';
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <title>Websitebeheer - Beauty Touch by Nikki</title>
    <?php include 'includes/head.php'; ?>
</head>
<body class="bg-gray-100 font-sans pb-24">
    <?php include 'includes/header.php'; ?>
    <div class="max-w-6xl mx-auto px-4 mt-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
            <h1 class="text-3xl font-bold text-gray-800">Homepagina <?php echo get_text('hero_title'); ?></h1>
            <a href="index.php" target="_blank" class="text-pink-600 hover:text-white font-bold bg-white hover:bg-pink-600 px-6 py-2 rounded-full shadow border border-pink-100 transition-all">Bekijk website &rarr;</a>
        </div>
        <?php if ($message): ?>
            <div id="alert-message" class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 shadow-sm rounded-r-lg flex justify-between items-center transition-opacity duration-500">
                <span class="font-medium"><?php echo $message; ?></span>
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
        <div id="algemeen" class="tab-content bg-white p-6 rounded-b-xl shadow-md border border-gray-200 border-t-0 mb-6">
            <form method="POST" action="websitebeheer.php?tab=<?php echo $active_tab; ?>">
                <div class="flex justify-between items-center mb-8 border-b pb-4">
                    <h2 class="text-2xl font-bold text-gray-800">Website Teksten & Instellingen</h2>
                    <button type="submit" name="update_content" class="bg-pink-600 text-white font-bold py-2 px-6 rounded-lg hover:bg-pink-700 shadow-sm transition-colors hidden md:block">Alles Opslaan</button>
                </div>
                <?php
                $grouped_content = [
                    'hero' => ['icon' => '🏠', 'title' => 'Homepage Bovenkant (Hero)', 'desc' => 'De grote tekst bovenaan je website.', 'items' => []],
                    'about' => ['icon' => '👩', 'title' => 'Over Mij Sectie', 'desc' => 'Stel jezelf voor aan je klanten.', 'items' => []],
                    'promo' => ['icon' => '🎁', 'title' => 'Aanbieding / Actie', 'desc' => 'Licht een tijdelijke actie uit.', 'items' => []],
                    'portfolio' => ['icon' => '📸', 'title' => 'Portfolio Sectie', 'desc' => 'Instellingen voor de fotogalerij.', 'items' => []],
                    'overig' => ['icon' => '⚙️', 'title' => 'Overige Teksten', 'desc' => 'Andere instellingen op de website.', 'items' => []]
                ];
                $labels = [
                    'hero_title' => 'Grote Hoofdtitel',
                    'hero_text' => 'Korte Introductie',
                    'about_title' => 'Titel (bijv. Even voorstellen)',
                    'about_text' => 'Jouw persoonlijke verhaal',
                    'promo_title' => 'Naam van de actie',
                    'promo_text' => 'Uitleg en voorwaarden',
                    'portfolio_title' => 'Titel boven fotogalerij'
                ];
                foreach ($algemeen_content as $item) {
                    $k = $item['section_key'];
                    if (strpos($k, 'hero_') === 0) $grouped_content['hero']['items'][] = $item;
                    elseif (strpos($k, 'about_') === 0) $grouped_content['about']['items'][] = $item;
                    elseif (strpos($k, 'promo_') === 0) $grouped_content['promo']['items'][] = $item;
                    elseif (strpos($k, 'portfolio_') === 0) $grouped_content['portfolio']['items'][] = $item;
                    else $grouped_content['overig']['items'][] = $item;
                }
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
                                            $friendly_name = isset($labels[$key]) ? $labels[$key] : ucfirst(str_replace('_', ' ', $key));
                                        ?>
                                        <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm relative group">
                                            <div class="flex justify-between items-start mb-4">
                                                <div>
                                                    <label class="font-bold text-gray-800 text-sm block">
                                                        <?php echo $friendly_name; ?>
                                                    </label>
                                                </div>
                                                <label class="inline-flex items-center cursor-pointer">
                                                    <input type="checkbox" name="visible[<?php echo $key; ?>]" value="1" <?php echo $item['is_visible'] ? 'checked' : ''; ?> class="sr-only peer">
                                                    <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-pink-500"></div>
                                                </label>
                                            </div>
                                            <?php if (strpos($key, 'title') !== false): ?>
                                                <input type="text" name="content[<?php echo $key; ?>]" value="<?php echo htmlspecialchars($item['content_text']); ?>" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-pink-400 focus:border-pink-400 text-sm transition-all">
                                            <?php else: ?>
                                                <textarea name="content[<?php echo $key; ?>]" rows="4" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-pink-400 focus:border-pink-400 text-sm transition-all"><?php echo htmlspecialchars($item['content_text']); ?></textarea>
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
        <div id="images" class="tab-content hidden bg-white p-6 rounded-b-xl shadow-md border border-gray-200 border-t-0 mb-6">
            <form method="POST" action="websitebeheer.php?tab=<?php echo $active_tab; ?>" enctype="multipart/form-data">
                <div class="flex justify-between items-center mb-8 border-b pb-4">
                    <h2 class="text-2xl font-bold text-gray-800">Afbeeldingen Homepage</h2>
                    <button type="submit" name="update_images" class="bg-pink-600 text-white font-bold py-2 px-6 rounded-lg hover:bg-pink-700 shadow-sm transition-colors hidden md:block">Uploaden & Opslaan</button>
                </div>
                <div class="grid md:grid-cols-2 gap-6">
                    <!-- Hero Image -->
                    <div class="bg-gray-50 p-6 rounded-xl border border-gray-200">
                        <h3 class="font-bold text-gray-800 mb-2">Hero Afbeelding (Bovenaan)</h3>
                        <p class="text-sm text-gray-500 mb-4">De grote foto die je direct ziet als de site laadt.</p>
                        <?php 
                        $current_hero = get_image('hero_image', $placeholder_img); 
                        $has_hero = ($current_hero !== $placeholder_img);
                        ?>
                        <img src="<?php echo $current_hero; ?>" class="w-full h-32 object-cover rounded mb-4 border border-gray-200" alt="Huidige hero">
                        <div class="flex gap-2">
                            <input type="file" name="hero_image" accept="image/*" class="w-full text-sm flex-grow">
                            <?php if($has_hero): ?>
                                <a href="websitebeheer.php?tab=images&delete_image=hero_image" onclick="return confirm('Weet je zeker dat je deze afbeelding wilt verwijderen en terug wilt naar de template?');" class="bg-white border border-red-200 text-red-500 hover:bg-red-50 hover:text-red-700 px-3 py-1 rounded shadow-sm text-sm font-bold flex items-center">
                                    Verwijder
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- About Image 1 -->
                    <div class="bg-gray-50 p-6 rounded-xl border border-gray-200">
                        <h3 class="font-bold text-gray-800 mb-2">Over Mij - Grote Afbeelding</h3>
                        <p class="text-sm text-gray-500 mb-4">De eerste/bovenste afbeelding naast je verhaal.</p>
                        <?php 
                        $current_about1 = get_image('about_image_1', $placeholder_img); 
                        $has_about1 = ($current_about1 !== $placeholder_img);
                        ?>
                        <img src="<?php echo $current_about1; ?>" class="w-full h-32 object-cover rounded mb-4 border border-gray-200" alt="Huidige about 1">
                        <div class="flex gap-2">
                            <input type="file" name="about_image_1" accept="image/*" class="w-full text-sm flex-grow">
                            <?php if($has_about1): ?>
                                <a href="websitebeheer.php?tab=images&delete_image=about_image_1" onclick="return confirm('Weet je zeker dat je deze afbeelding wilt verwijderen en terug wilt naar de template?');" class="bg-white border border-red-200 text-red-500 hover:bg-red-50 hover:text-red-700 px-3 py-1 rounded shadow-sm text-sm font-bold flex items-center">
                                    Verwijder
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- About Image 2 -->
                    <div class="bg-gray-50 p-6 rounded-xl border border-gray-200">
                        <h3 class="font-bold text-gray-800 mb-2">Over Mij - Kleine Afbeelding</h3>
                        <p class="text-sm text-gray-500 mb-4">De kleinere, overlappende afbeelding naast je verhaal.</p>
                        <?php 
                        $current_about2 = get_image('about_image_2', $placeholder_img); 
                        $has_about2 = ($current_about2 !== $placeholder_img);
                        ?>
                        <img src="<?php echo $current_about2; ?>" class="w-full h-32 object-cover rounded mb-4 border border-gray-200" alt="Huidige about 2">
                        <div class="flex gap-2">
                            <input type="file" name="about_image_2" accept="image/*" class="w-full text-sm flex-grow">
                            <?php if($has_about2): ?>
                                <a href="websitebeheer.php?tab=images&delete_image=about_image_2" onclick="return confirm('Weet je zeker dat je deze afbeelding wilt verwijderen en terug wilt naar de template?');" class="bg-white border border-red-200 text-red-500 hover:bg-red-50 hover:text-red-700 px-3 py-1 rounded shadow-sm text-sm font-bold flex items-center">
                                    Verwijder
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="mt-8 flex justify-end">
                    <button type="submit" name="update_images" class="bg-pink-600 text-white font-bold py-3 px-8 rounded-full hover:bg-pink-700 shadow-xl shadow-pink-600/30 transition-all transform hover:-translate-y-1 w-full md:w-auto">Wijzigingen Opslaan</button>
                </div>
            </form>
        </div>

        <!-- PRODUCTEN TAB -->
        <div id="producten" class="tab-content hidden bg-white p-6 rounded-b-xl shadow-md border border-gray-200 border-t-0 mb-6">
            <form method="POST" action="websitebeheer.php?tab=producten">
                <div class="flex justify-between items-center mb-6 border-b pb-4">
                    <h2 class="text-2xl font-bold text-gray-800">Uitgelichte Producten op Homepage</h2>
                    <button type="submit" name="update_homepage_products" class="bg-pink-600 text-white font-bold py-2 px-6 rounded-lg hover:bg-pink-700 shadow-sm transition-colors hidden md:block">Opslaan</button>
                </div>
                
                <p class="text-gray-600 mb-8 bg-pink-50 p-4 rounded-lg border border-pink-100">Selecteer hieronder welke producten je op de homepagina wilt uitlichten door de schakelaar aan te zetten.</p>
                
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
                                        <input type="checkbox" name="show_on_home[<?php echo $product['uuid']; ?>]" value="1" <?php echo (isset($product['show_on_homepage']) && $product['show_on_homepage']) ? 'checked' : ''; ?> class="sr-only peer">
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
        <div id="portfolio" class="tab-content hidden bg-white p-6 rounded-b-xl shadow-md border border-gray-200 border-t-0 mb-6">
            <div class="grid md:grid-cols-3 gap-6 mb-10">
                <?php foreach ($all_portfolio as $port): ?>
                    <div class="bg-gray-50 rounded-lg border border-gray-200 overflow-hidden flex flex-col shadow-sm">
                        <img src="<?php echo htmlspecialchars($port['image_path']); ?>" alt="<?php echo htmlspecialchars($port['title']); ?>" class="w-full h-48 object-cover">
                        <div class="p-4 flex-grow">
                            <h3 class="font-bold text-gray-800"><?php echo htmlspecialchars($port['title']); ?></h3>
                            <p class="text-sm text-gray-600 mt-1"><?php echo htmlspecialchars($port['description']); ?></p>
                        </div>
                        <div class="p-4 border-t border-gray-200 bg-white">
                            <a href="websitebeheer.php?delete_portfolio=<?php echo $port['id']; ?>" onclick="return confirm('Zeker weten dat je deze foto wilt verwijderen?');" class="text-red-500 hover:text-red-700 text-sm font-bold bg-white px-3 py-2 rounded border border-red-100 block text-center">Verwijderen</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <form method="POST" action="websitebeheer.php?tab=<?php echo $active_tab; ?>" enctype="multipart/form-data" class="bg-pink-50 p-6 rounded-xl border border-pink-100">
                <h2 class="font-bold mb-4">Nieuwe Foto Toevoegen</h2>
                <input type="text" name="port_title" required placeholder="Titel werk" class="w-full px-4 py-2 border rounded-md mb-4">
                <textarea name="port_desc" rows="3" placeholder="Beschrijving..." class="w-full px-4 py-2 border rounded-md mb-4"></textarea>
                <input type="file" name="image" accept="image/*" required class="w-full text-gray-700 bg-white border border-gray-300 rounded-md py-2 px-3 mb-6">
                <button type="submit" name="add_portfolio" class="bg-pink-600 text-white font-bold py-3 px-6 rounded-lg hover:bg-pink-700">+ Foto Uploaden</button>
            </form>
        </div>

        <!-- USPS TAB -->
        <div id="usps" class="tab-content hidden bg-white p-6 rounded-b-xl shadow-md border border-gray-200 border-t-0 mb-6">
            <div class="grid md:grid-cols-3 gap-4 mb-10">
                <?php foreach ($all_usps as $usp): ?>
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 relative pt-8 mt-4">
                        <div class="absolute -top-4 left-4 text-3xl bg-white rounded-full px-2 shadow-sm"><?php echo htmlspecialchars($usp['icon']); ?></div>
                        <h3 class="font-bold text-gray-800"><?php echo htmlspecialchars($usp['title']); ?></h3>
                        <p class="text-sm text-gray-600 mb-4"><?php echo htmlspecialchars($usp['description']); ?></p>
                        <a href="websitebeheer.php?delete_usp=<?php echo $usp['uuid']; ?>" class="text-red-500 text-sm font-bold bg-white px-3 py-1 rounded border border-red-100">Verwijderen</a>
                    </div>
                <?php endforeach; ?>
            </div>
            <form method="POST" action="websitebeheer.php?tab=<?php echo $active_tab; ?>" class="bg-green-50 p-6 rounded-xl border border-green-100">
                <h2 class="font-bold mb-4">Nieuw Voordeel (USP) Toevoegen</h2>
                <div class="grid md:grid-cols-2 gap-4 mb-4">
                    <input type="text" name="usp_icon" required placeholder="Emoji (bijv. ⭐)" class="w-full px-4 py-2 border rounded-md">
                    <input type="text" name="usp_title" required placeholder="Titel (bijv. Ervaring)" class="w-full px-4 py-2 border rounded-md">
                </div>
                <textarea name="usp_desc" rows="2" required placeholder="Korte uitleg..." class="w-full px-4 py-2 border rounded-md mb-4"></textarea>
                <button type="submit" name="add_usp" class="bg-green-600 text-white font-bold py-2 px-6 rounded-lg">+ USP Toevoegen</button>
            </form>
        </div>

        <!-- REVIEWS TAB -->
        <div id="reviews" class="tab-content hidden bg-white p-6 rounded-b-xl shadow-md border border-gray-200 border-t-0 mb-6">
            <div class="grid md:grid-cols-2 gap-4 mb-10">
                <?php foreach ($all_reviews as $review): ?>
                    <?php $stars = isset($review['stars']) ? (int)$review['stars'] : 5; ?>
                    <div class="bg-slate-800 text-white p-6 rounded-lg relative">
                        <div class="text-pink-400 mb-2 text-sm tracking-widest">
                            <?php echo str_repeat('✦ ', $stars); ?>
                        </div>
                        <p class="italic mb-4">"<?php echo htmlspecialchars($review['review_text']); ?>"</p>
                        <div class="font-bold text-pink-400">- <?php echo htmlspecialchars($review['customer_name']); ?></div>
                        <a href="websitebeheer.php?delete_review=<?php echo $review['uuid']; ?>" class="absolute top-4 right-4 text-red-400 hover:text-red-300 text-sm bg-slate-700 px-2 py-1 rounded">X</a>
                    </div>
                <?php endforeach; ?>
            </div>
            <form method="POST" action="websitebeheer.php?tab=<?php echo $active_tab; ?>" class="bg-slate-100 p-6 rounded-xl border border-slate-200">
                <h2 class="font-bold mb-4">Nieuwe Review Toevoegen</h2>
                <div class="grid md:grid-cols-2 gap-4 mb-4">
                    <input type="text" name="review_name" required placeholder="Naam klant (bijv. Sanne)" class="w-full px-4 py-2 border rounded-md">
                    <select name="review_stars" class="w-full px-4 py-2 border rounded-md" required>
                        <option value="5">5 Sterren</option>
                        <option value="4">4 Sterren</option>
                        <option value="3">3 Sterren</option>
                        <option value="2">2 Sterren</option>
                        <option value="1">1 Ster</option>
                    </select>
                </div>
                <textarea name="review_text" rows="3" required placeholder="Wat zei de klant?..." class="w-full px-4 py-2 border rounded-md mb-4"></textarea>
                <button type="submit" name="add_review" class="bg-slate-800 text-white font-bold py-2 px-6 rounded-lg">+ Review Toevoegen</button>
            </form>
        </div>

        <!-- FAQ TAB -->
        <div id="faq" class="tab-content hidden bg-white p-6 rounded-b-xl shadow-md border border-gray-200 border-t-0 mb-6">
            <div class="space-y-4 mb-10">
                <?php foreach ($all_faqs as $faq): ?>
                    <div class="bg-white border border-gray-200 p-4 rounded-lg flex justify-between items-start">
                        <div>
                            <h3 class="font-bold text-gray-800 mb-1">Q: <?php echo htmlspecialchars($faq['question']); ?></h3>
                            <p class="text-gray-600">A: <?php echo htmlspecialchars($faq['answer']); ?></p>
                        </div>
                        <a href="websitebeheer.php?delete_faq=<?php echo $faq['uuid']; ?>" class="text-red-500 hover:text-red-700 font-bold ml-4 border p-1 rounded">X</a>
                    </div>
                <?php endforeach; ?>
            </div>
            <form method="POST" action="websitebeheer.php?tab=<?php echo $active_tab; ?>" class="bg-gray-50 p-6 rounded-xl border border-gray-200">
                <h2 class="font-bold mb-4">Nieuwe Vraag Toevoegen</h2>
                <input type="text" name="faq_q" required placeholder="De vraag?" class="w-full px-4 py-2 border rounded-md mb-4 font-bold">
                <textarea name="faq_a" rows="3" required placeholder="Het antwoord..." class="w-full px-4 py-2 border rounded-md mb-4"></textarea>
                <button type="submit" name="add_faq" class="bg-gray-800 text-white font-bold py-2 px-6 rounded-lg">+ Vraag Toevoegen</button>
            </form>
        </div>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const activeTab = "<?php echo $active_tab; ?>";
        document.querySelectorAll('.tab-content').forEach(c => c.classList.add('hidden'));
        document.getElementById(activeTab).classList.remove('hidden');
    });
</script>
</body>
</html>