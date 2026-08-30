<?php
require_once 'includes/init.php';
$website_id = require_website_context();

// --- 1. AJAX SORTERING OPSLAAN ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_action']) && $_POST['ajax_action'] == 'update_order') {
    header('Content-Type: application/json');
    csrf_verify();
    $type = $_POST['type'];
    $items = json_decode($_POST['items'], true);

    if ($type === 'categories') {
        $stmt = $pdo->prepare("UPDATE categories SET sort_order = ? WHERE id = ? AND website_id = ?");
    } else {
        $stmt = $pdo->prepare("UPDATE products SET sort_order = ? WHERE id = ? AND website_id = ?");
    }

    foreach ($items as $item) {
        $stmt->execute([$item['order'], $item['id'], $website_id]);
    }

    echo json_encode(['status' => 'success']);
    exit;
}

// --- 2. CATEGORIE TOEVOEGEN ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_category') {
    csrf_verify();
    $name = trim($_POST['name'] ?? '');
    $stmt = $pdo->prepare("SELECT MAX(sort_order) FROM categories WHERE website_id = ?");
    $stmt->execute([$website_id]);
    $max = $stmt->fetchColumn();
    $sort_order = $max ? $max + 1 : 1;
    $new_cat_id = $pdo->query('SELECT UUID()')->fetchColumn();
    $stmt = $pdo->prepare("INSERT INTO categories (id, website_id, name, sort_order) VALUES (?, ?, ?, ?)");
    $stmt->execute([$new_cat_id, $website_id, $name, $sort_order]);
    save_custom_field_values('category', $new_cat_id, $_POST['custom_fields'] ?? []);
    header("Location: productbeheer.php?msg=cat_added");
    exit;
}

// --- 3. CATEGORIEËN UPDATEN (NAMEN) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'update_categories') {
    csrf_verify();
    if (isset($_POST['categories']) && is_array($_POST['categories'])) {
        $stmt = $pdo->prepare("UPDATE categories SET name = ? WHERE id = ? AND website_id = ?");
        foreach ($_POST['categories'] as $cat_id => $cat_data) {
            $stmt->execute([$cat_data['name'], $cat_id, $website_id]);
        }
    }
    header("Location: productbeheer.php?msg=cat_updated");
    exit;
}

// --- 4. CATEGORIE VERWIJDEREN ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_cat'])) {
    csrf_verify();
    $cat_id = $_POST['delete_cat'];
    $stmt_prod_ids = $pdo->prepare("SELECT id FROM products WHERE category_id = ? AND website_id = ?");
    $stmt_prod_ids->execute([$cat_id, $website_id]);
    foreach ($stmt_prod_ids->fetchAll(PDO::FETCH_COLUMN) as $prod_id) {
        delete_custom_field_values_for_entity($prod_id);
    }
    $stmt_prod = $pdo->prepare("DELETE FROM products WHERE category_id = ? AND website_id = ?");
    $stmt_prod->execute([$cat_id, $website_id]);
    $stmt_cat = $pdo->prepare("DELETE FROM categories WHERE id = ? AND website_id = ?");
    $stmt_cat->execute([$cat_id, $website_id]);
    delete_custom_field_values_for_entity($cat_id);
    header("Location: productbeheer.php?msg=cat_deleted");
    exit;
}

// --- 5. PRODUCT/DIENST TOEVOEGEN ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_product') {
    csrf_verify();
    $category_id = $_POST['category_id'] ?? '';
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = str_replace(',', '.', $_POST['price'] ?? '0');

    // De gekozen categorie moet echt van deze website zijn, anders kan een
    // product per ongeluk (of moedwillig) aan een andere tenant gekoppeld worden.
    $cat_check = $pdo->prepare("SELECT id FROM categories WHERE id = ? AND website_id = ?");
    $cat_check->execute([$category_id, $website_id]);
    if ($cat_check->fetchColumn()) {
        $stmt = $pdo->prepare("SELECT MAX(sort_order) FROM products WHERE website_id = ?");
        $stmt->execute([$website_id]);
        $max = $stmt->fetchColumn();
        $sort_order = $max ? $max + 1 : 1;
        $new_prod_id = $pdo->query('SELECT UUID()')->fetchColumn();
        $stmt = $pdo->prepare("INSERT INTO products (id, website_id, category_id, title, description, price, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$new_prod_id, $website_id, $category_id, $title, $description, $price, $sort_order]);
        save_custom_field_values('product', $new_prod_id, $_POST['custom_fields'] ?? []);
        header("Location: productbeheer.php?msg=prod_added");
    } else {
        header("Location: productbeheer.php?msg=invalid_category");
    }
    exit;
}

// --- 6. PRODUCT/DIENST VERWIJDEREN ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_prod'])) {
    csrf_verify();
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ? AND website_id = ?");
    $stmt->execute([$_POST['delete_prod'], $website_id]);
    delete_custom_field_values_for_entity($_POST['delete_prod']);
    header("Location: productbeheer.php?msg=prod_deleted");
    exit;
}

$categories = get_categories();
$products = get_all_products_admin();
$productsByCategory = [];
if (!empty($products)) {
    foreach ($products as $p) {
        $catId = !empty($p['category_id']) ? $p['category_id'] : 'geen';
        $productsByCategory[$catId][] = $p;
    }
}
$category_custom_values = get_custom_field_value_map('category', array_column($categories, 'id'));
$product_custom_values = get_custom_field_value_map('product', array_column($products, 'id'));

function render_custom_field_badges(array $items)
{
    if (empty($items)) {
        return;
    }
    echo '<div class="flex flex-wrap gap-1.5 mt-1.5">';
    foreach ($items as $item) {
        echo '<span class="inline-flex items-center gap-1 bg-gray-100 text-gray-600 text-[11px] font-medium px-2 py-0.5 rounded-full">';
        echo '<strong class="text-gray-700">' . htmlspecialchars($item['label']) . ':</strong> ' . htmlspecialchars($item['value']);
        echo '</span>';
    }
    echo '</div>';
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <title>Beheer Diensten & Categorieën - <?php echo htmlspecialchars(current_website()['company_name'] ?? 'Webius Portaal'); ?></title>
    <?php include 'includes/head.php'; ?>
    <style>
        .sortable-ghost { opacity: 0.4; background-color: #fce7f3; }
        .drag-handle { cursor: grab; }
        .drag-handle:active { cursor: grabbing; }
    </style>
</head>
<body class="bg-gray-50 font-sans pb-20 text-gray-800">
    <?php include 'includes/header.php'; ?>
    <div class="max-w-7xl p-8 mx-auto">
        <div class="flex flex-col md:flex-row justify-between items-center mb-8 bg-white p-6 rounded-xl shadow-sm border border-gray-200">
            <div>
                <h1 class="text-3xl font-extrabold text-slate-900">Assortiment Beheer</h1>
                <p class="text-gray-500 mt-1">Beheer hier de categorieën en de bijbehorende behandelingen.</p>
            </div>
            <div class="flex items-center gap-4 mt-4 md:mt-0">
                <button id="toggleDragBtn" class="flex items-center gap-2 bg-slate-200 text-slate-700 font-bold py-2 px-6 rounded-full hover:bg-slate-300 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                    <span>Sleep-modus: UIT</span>
                </button>
                <a href="<?php echo htmlspecialchars(public_site_url('index.php')); ?>" target="_blank" class="bg-pink-100 text-pink-700 font-bold py-2 px-6 rounded-full hover:bg-pink-200 transition-colors">
                    Bekijk publiek &rarr;
                </a>
            </div>
        </div>

        <div id="saveNotification" class="fixed bottom-4 right-4 bg-gray-900 text-white px-6 py-3 rounded-lg shadow-xl opacity-0 transition-opacity duration-300 z-50 flex items-center gap-3 pointer-events-none">
            <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            Volgorde opgeslagen!
        </div>

        <?php if(isset($_GET['msg'])): ?>
            <div class="mb-8 p-4 rounded-lg font-medium
                <?php
                    echo (strpos($_GET['msg'], 'deleted') !== false || $_GET['msg'] === 'invalid_category')
                    ? 'bg-red-100 text-red-800 border border-red-200'
                    : 'bg-green-100 text-green-800 border border-green-200';
                ?>">
                <?php
                    if($_GET['msg'] == 'cat_added') echo 'Categorie succesvol toegevoegd!';
                    if($_GET['msg'] == 'cat_updated') echo 'Categorie namen succesvol bijgewerkt!';
                    if($_GET['msg'] == 'cat_deleted') echo 'Categorie verwijderd!';
                    if($_GET['msg'] == 'prod_added') echo 'Dienst succesvol toegevoegd!';
                    if($_GET['msg'] == 'prod_deleted') echo 'Dienst verwijderd!';
                    if($_GET['msg'] == 'invalid_category') echo 'Ongeldige categorie gekozen.';
                ?>
            </div>
        <?php endif; ?>

        <div class="mb-16">
            <h2 class="text-2xl font-bold text-slate-900 mb-6 flex items-center">
                <span class="bg-pink-500 text-white w-8 h-8 flex items-center justify-center rounded-full mr-3 text-sm">1</span>
                Categorieën
            </h2>

            <div class="grid lg:grid-cols-3 gap-8">
                <div class="lg:col-span-1 bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                    <h3 class="text-lg font-bold mb-4">Nieuwe Categorie</h3>
                    <form method="POST" action="productbeheer.php">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="action" value="add_category">
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Naam categorie</label>
                            <input type="text" name="name" placeholder="bijv. Knippen & Drogen" required class="w-full border-gray-300 rounded-md border p-2 focus:border-pink-500 focus:ring-pink-500">
                        </div>
                        <?php render_custom_field_inputs('category'); ?>
                        <button type="submit" class="w-full bg-slate-900 text-white font-bold py-2 px-4 rounded-lg hover:bg-slate-800 transition-colors mt-4">
                            Categorie Toevoegen
                        </button>
                    </form>
                </div>
                <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold">Beheer Bestaande Categorieën</h3>
                        <span class="text-sm text-gray-400 italic hidden drag-instruction">Sleep aan de lijntjes om te verplaatsen</span>
                    </div>

                    <?php if(empty($categories)): ?>
                        <p class="text-gray-500 italic">Nog geen categorieën aangemaakt. Maak er eerst een aan.</p>
                    <?php else: ?>
                        <form method="POST" action="productbeheer.php">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="action" value="update_categories">

                            <div class="overflow-x-auto mb-4 border rounded-lg border-gray-200">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="drag-column hidden w-12 px-4 py-3 bg-pink-50"></th>
                                            <th class="order-column w-12 px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">#</th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Naam Categorie (pas direct aan)</th>
                                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actie</th>
                                        </tr>
                                    </thead>
                                    <tbody id="categories-list" class="divide-y divide-gray-200 bg-white">
                                        <?php foreach ($categories as $index => $cat): ?>
                                            <tr data-id="<?php echo htmlspecialchars($cat['id']); ?>">
                                                <td class="drag-column hidden px-4 py-3 drag-handle text-gray-400 hover:text-pink-500 bg-pink-50">☰</td>
                                                <td class="order-column px-4 py-3 text-sm text-gray-500 font-bold"><?php echo $index + 1; ?></td>
                                                <td class="px-4 py-3">
                                                    <input type="text" name="categories[<?php echo htmlspecialchars($cat['id']); ?>][name]" value="<?php echo htmlspecialchars($cat['name']); ?>" class="w-full border-transparent hover:border-gray-300 rounded p-1.5 focus:border-pink-500 focus:ring-1 focus:ring-pink-500 transition-colors">
                                                    <?php render_custom_field_badges($category_custom_values[$cat['id']] ?? []); ?>
                                                </td>
                                                <td class="px-4 py-3 text-right">
                                                    <button type="submit" form="delete-cat-<?php echo htmlspecialchars($cat['id']); ?>" class="text-red-500 hover:text-red-700 font-medium text-sm">Verwijder</button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <button type="submit" class="bg-white border-2 border-slate-900 text-slate-900 font-bold py-2 px-6 rounded-lg hover:bg-slate-900 hover:text-white transition-colors">
                                Namen Opslaan
                            </button>
                        </form>
                        <?php foreach ($categories as $cat): ?>
                            <form method="POST" action="productbeheer.php" id="delete-cat-<?php echo htmlspecialchars($cat['id']); ?>" onsubmit="return confirm('Weet je het zeker? LET OP: Alle behandelingen in deze categorie worden ook verwijderd!');" class="hidden">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="delete_cat" value="<?php echo htmlspecialchars($cat['id']); ?>">
                            </form>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <hr class="border-t-2 border-gray-200 mb-16">

        <div>
            <h2 class="text-2xl font-bold text-slate-900 mb-6 flex items-center">
                <span class="bg-pink-500 text-white w-8 h-8 flex items-center justify-center rounded-full mr-3 text-sm">2</span>
                Behandelingen & Producten
            </h2>

            <div class="grid lg:grid-cols-3 gap-8">
                <div class="lg:col-span-1 bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                    <h3 class="text-lg font-bold mb-4">Nieuwe Dienst Toevoegen</h3>

                    <?php if(empty($categories)): ?>
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                            <p class="text-sm text-yellow-700">Er moet eerst een categorie aangemaakt worden in de sectie hierboven.</p>
                        </div>
                    <?php else: ?>
                        <form method="POST" action="productbeheer.php">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="action" value="add_product">

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Kies Categorie</label>
                                <select name="category_id" required class="w-full border-gray-300 rounded-md border p-2 focus:border-pink-500 focus:ring-pink-500 bg-white">
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo htmlspecialchars($cat['id']); ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Titel Behandeling</label>
                                <input type="text" name="title" required class="w-full border-gray-300 rounded-md border p-2 focus:border-pink-500 focus:ring-pink-500">
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Omschrijving (optioneel)</label>
                                <textarea name="description" rows="3" class="w-full border-gray-300 rounded-md border p-2 focus:border-pink-500 focus:ring-pink-500"></textarea>
                            </div>

                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Prijs (€)</label>
                                <input type="number" step="0.01" min="0" name="price" required class="w-full border-gray-300 rounded-md border p-2 focus:border-pink-500 focus:ring-pink-500">
                            </div>

                            <?php render_custom_field_inputs('product'); ?>

                            <button type="submit" class="w-full bg-slate-900 text-white font-bold py-2 px-4 rounded-lg hover:bg-slate-800 transition-colors mt-4">
                                Dienst Toevoegen
                            </button>
                        </form>
                    <?php endif; ?>
                </div>

                <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                    <div class="flex justify-between items-center mb-6 border-b border-gray-100 pb-4">
                        <h3 class="text-lg font-bold">Huidige Diensten (Per Categorie)</h3>
                        <span class="text-sm text-gray-400 italic hidden drag-instruction">Sleep aan de lijntjes om te verplaatsen binnen de categorie</span>
                    </div>

                    <?php if(empty($categories) && empty($products)): ?>
                        <p class="text-gray-500 italic">Er zijn nog geen behandelingen toegevoegd.</p>
                    <?php else: ?>

                        <?php foreach ($categories as $cat): ?>
                            <div class="mb-8">
                                <h4 class="text-md font-bold text-pink-600 mb-3 flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </h4>

                                <div class="overflow-x-auto border rounded-lg border-gray-200">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="drag-column hidden w-12 px-4 py-3 bg-pink-50"></th>
                                                <th class="order-column w-12 px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">#</th>
                                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Dienst</th>
                                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Prijs</th>
                                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actie</th>
                                            </tr>
                                        </thead>
                                        <tbody class="products-list divide-y divide-gray-200 bg-white">
                                            <?php if (!empty($productsByCategory[$cat['id']])): ?>
                                                <?php foreach ($productsByCategory[$cat['id']] as $index => $p): ?>
                                                    <tr data-id="<?php echo htmlspecialchars($p['id']); ?>" class="hover:bg-gray-50">
                                                        <td class="drag-column hidden px-4 py-3 drag-handle text-gray-400 hover:text-pink-500 bg-pink-50">☰</td>
                                                        <td class="order-column px-4 py-3 text-sm text-gray-500 font-bold"><?php echo $index + 1; ?></td>
                                                        <td class="px-4 py-3 text-sm font-medium text-slate-900">
                                                            <?php echo htmlspecialchars($p['title']); ?>
                                                            <?php render_custom_field_badges($product_custom_values[$p['id']] ?? []); ?>
                                                        </td>
                                                        <td class="px-4 py-3 text-sm text-gray-600">€ <?php echo number_format($p['price'], 2, ',', '.'); ?></td>
                                                        <td class="px-4 py-3 text-right">
                                                            <button type="submit" form="delete-prod-<?php echo htmlspecialchars($p['id']); ?>" class="text-red-500 hover:text-red-700 font-medium text-sm">Verwijder</button>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="5" class="px-4 py-4 text-sm text-gray-500 italic text-center">Nog geen diensten in deze categorie.</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php if(!empty($productsByCategory['geen'])): ?>
                            <div class="mb-8">
                                <h4 class="text-md font-bold text-gray-600 mb-3 flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path></svg>
                                    Overig / Geen categorie
                                </h4>
                                <div class="overflow-x-auto border rounded-lg border-gray-200">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="w-12 px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">#</th>
                                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Dienst</th>
                                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Prijs</th>
                                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actie</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200 bg-white">
                                            <?php foreach ($productsByCategory['geen'] as $index => $p): ?>
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-4 py-3 text-sm text-gray-500 font-bold"><?php echo $index + 1; ?></td>
                                                    <td class="px-4 py-3 text-sm font-medium text-slate-900"><?php echo htmlspecialchars($p['title']); ?></td>
                                                    <td class="px-4 py-3 text-sm text-gray-600">€ <?php echo number_format($p['price'], 2, ',', '.'); ?></td>
                                                    <td class="px-4 py-3 text-right">
                                                        <button type="submit" form="delete-prod-<?php echo htmlspecialchars($p['id']); ?>" class="text-red-500 hover:text-red-700 font-medium text-sm">Verwijder</button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php foreach ($products as $p): ?>
                            <form method="POST" action="productbeheer.php" id="delete-prod-<?php echo htmlspecialchars($p['id']); ?>" onsubmit="return confirm('Weet je zeker dat je deze dienst wilt verwijderen?');" class="hidden">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="delete_prod" value="<?php echo htmlspecialchars($p['id']); ?>">
                            </form>
                        <?php endforeach; ?>

                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        const CSRF_TOKEN = <?php echo json_encode(csrf_token()); ?>;

        document.addEventListener('DOMContentLoaded', function () {
            const toggleBtn = document.getElementById('toggleDragBtn');
            const dragColumns = document.querySelectorAll('.drag-column');
            const orderColumns = document.querySelectorAll('.order-column');
            const dragInstructions = document.querySelectorAll('.drag-instruction');
            let isDragMode = false;
            let sortableCategories = null;
            let sortableProductInstances = [];

            const sortableOptions = (type) => ({
                animation: 150,
                handle: '.drag-handle',
                disabled: true,
                onEnd: function (evt) {
                    saveNewOrder(type, evt.to);
                }
            });

            const catList = document.getElementById('categories-list');
            if(catList) {
                sortableCategories = new Sortable(catList, sortableOptions('categories'));
            }

            const prodLists = document.querySelectorAll('.products-list');
            prodLists.forEach(list => {
                if(list.querySelector('tr[data-id]')) {
                    sortableProductInstances.push(new Sortable(list, sortableOptions('products')));
                }
            });

            toggleBtn.addEventListener('click', function() {
                isDragMode = !isDragMode;

                if(sortableCategories) sortableCategories.option("disabled", !isDragMode);

                sortableProductInstances.forEach(instance => {
                    instance.option("disabled", !isDragMode);
                });

                if (isDragMode) {
                    toggleBtn.classList.replace('bg-slate-200', 'bg-green-500');
                    toggleBtn.classList.replace('text-slate-700', 'text-white');
                    toggleBtn.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg> <span>Sleep-modus: AAN</span>';

                    dragColumns.forEach(el => el.classList.remove('hidden'));
                    orderColumns.forEach(el => el.classList.add('hidden'));
                    dragInstructions.forEach(el => el.classList.remove('hidden'));
                } else {
                    toggleBtn.classList.replace('bg-green-500', 'bg-slate-200');
                    toggleBtn.classList.replace('text-white', 'text-slate-700');
                    toggleBtn.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg> <span>Sleep-modus: UIT</span>';

                    dragColumns.forEach(el => el.classList.add('hidden'));
                    orderColumns.forEach(el => el.classList.remove('hidden'));
                    dragInstructions.forEach(el => el.classList.add('hidden'));
                    window.location.reload();
                }
            });

            function saveNewOrder(type, tableBody) {
                const rows = tableBody.querySelectorAll('tr[data-id]');
                const orderData = [];
                rows.forEach((row, index) => {
                    orderData.push({
                        id: row.getAttribute('data-id'),
                        order: index + 1
                    });
                });

                if(orderData.length === 0) return;

                const formData = new FormData();
                formData.append('ajax_action', 'update_order');
                formData.append('type', type);
                formData.append('items', JSON.stringify(orderData));
                formData.append('csrf_token', CSRF_TOKEN);

                fetch('productbeheer.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if(data.status === 'success') {
                        showNotification();
                    }
                });
            }

            function showNotification() {
                const notif = document.getElementById('saveNotification');
                notif.classList.remove('opacity-0');
                notif.classList.add('opacity-100');

                setTimeout(() => {
                    notif.classList.remove('opacity-100');
                    notif.classList.add('opacity-0');
                }, 2500);
            }
        });
    </script>
</body>
</html>
