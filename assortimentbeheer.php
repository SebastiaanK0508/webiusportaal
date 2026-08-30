<?php
require_once 'includes/init.php';
$website_id = require_website_context();
require_module($website_id, 'assortiment');

// --- 1. AJAX SORTERING OPSLAAN ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_action']) && $_POST['ajax_action'] == 'update_order') {
    header('Content-Type: application/json');
    csrf_verify();
    $type = $_POST['type'];
    $items = json_decode($_POST['items'], true);

    if ($type === 'categorieen') {
        $stmt = $pdo->prepare("UPDATE assortiment_categorieen SET sort_order = ? WHERE id = ? AND website_id = ?");
    } else {
        $stmt = $pdo->prepare("UPDATE assortiment_merken SET sort_order = ? WHERE id = ? AND website_id = ?");
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
    $titel = trim($_POST['titel'] ?? '');
    $beschrijving = trim($_POST['beschrijving'] ?? '');
    $badge_tekst = trim($_POST['badge_tekst'] ?? '');

    if ($titel === '') {
        header("Location: assortimentbeheer.php?msg=invalid_category");
        exit;
    }

    try {
        $afbeelding = save_uploaded_image('afbeelding', $website_id);
    } catch (UploadException $e) {
        header("Location: assortimentbeheer.php?msg=upload_error");
        exit;
    }

    $stmt = $pdo->prepare("SELECT MAX(sort_order) FROM assortiment_categorieen WHERE website_id = ?");
    $stmt->execute([$website_id]);
    $max = $stmt->fetchColumn();
    $sort_order = $max ? $max + 1 : 1;

    $new_cat_id = $pdo->query('SELECT UUID()')->fetchColumn();
    $stmt = $pdo->prepare("INSERT INTO assortiment_categorieen (id, website_id, titel, beschrijving, afbeelding, badge_tekst, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$new_cat_id, $website_id, $titel, $beschrijving, $afbeelding, $badge_tekst ?: null, $sort_order]);
    header("Location: assortimentbeheer.php?msg=cat_added");
    exit;
}

// --- 2B. CATEGORIE BEWERKEN ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'edit_category') {
    csrf_verify();
    $id = $_POST['edit_id'] ?? '';
    $titel = trim($_POST['titel'] ?? '');
    $beschrijving = trim($_POST['beschrijving'] ?? '');
    $badge_tekst = trim($_POST['badge_tekst'] ?? '');

    $check = $pdo->prepare("SELECT afbeelding FROM assortiment_categorieen WHERE id = ? AND website_id = ?");
    $check->execute([$id, $website_id]);
    $bestaande = $check->fetch();

    if (!$bestaande || $titel === '') {
        header("Location: assortimentbeheer.php?msg=invalid_category");
        exit;
    }

    $afbeelding = $bestaande['afbeelding'];
    if (!empty($_FILES['afbeelding']['name'])) {
        try {
            $nieuwe_afbeelding = save_uploaded_image('afbeelding', $website_id);
            if ($nieuwe_afbeelding !== null) {
                delete_uploaded_file($afbeelding ?: null);
                $afbeelding = $nieuwe_afbeelding;
            }
        } catch (UploadException $e) {
            header("Location: assortimentbeheer.php?msg=upload_error");
            exit;
        }
    }

    $stmt = $pdo->prepare("UPDATE assortiment_categorieen SET titel = ?, beschrijving = ?, badge_tekst = ?, afbeelding = ? WHERE id = ? AND website_id = ?");
    $stmt->execute([$titel, $beschrijving, $badge_tekst ?: null, $afbeelding, $id, $website_id]);
    header("Location: assortimentbeheer.php?msg=cat_updated");
    exit;
}

// --- 3. CATEGORIE VERWIJDEREN ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_cat'])) {
    csrf_verify();
    $cat_id = $_POST['delete_cat'];
    $stmt = $pdo->prepare("SELECT afbeelding FROM assortiment_categorieen WHERE id = ? AND website_id = ?");
    $stmt->execute([$cat_id, $website_id]);
    delete_uploaded_file($stmt->fetchColumn() ?: null);
    // assortiment_merken heeft ON DELETE CASCADE op category_id, dus die rijen verdwijnen automatisch.
    $pdo->prepare("DELETE FROM assortiment_categorieen WHERE id = ? AND website_id = ?")->execute([$cat_id, $website_id]);
    header("Location: assortimentbeheer.php?msg=cat_deleted");
    exit;
}

// --- 4. MERK TOEVOEGEN ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_merk') {
    csrf_verify();
    $category_id = $_POST['category_id'] ?? '';
    $naam = trim($_POST['naam'] ?? '');

    $cat_check = $pdo->prepare("SELECT id FROM assortiment_categorieen WHERE id = ? AND website_id = ?");
    $cat_check->execute([$category_id, $website_id]);

    if (!$cat_check->fetchColumn() || $naam === '') {
        header("Location: assortimentbeheer.php?msg=invalid_merk");
        exit;
    }

    try {
        $logo_path = save_uploaded_image('logo', $website_id);
    } catch (UploadException $e) {
        header("Location: assortimentbeheer.php?msg=upload_error");
        exit;
    }
    if (!$logo_path) {
        header("Location: assortimentbeheer.php?msg=invalid_merk");
        exit;
    }

    $stmt = $pdo->prepare("SELECT MAX(sort_order) FROM assortiment_merken WHERE website_id = ? AND category_id = ?");
    $stmt->execute([$website_id, $category_id]);
    $max = $stmt->fetchColumn();
    $sort_order = $max ? $max + 1 : 1;

    $new_id = $pdo->query('SELECT UUID()')->fetchColumn();
    $stmt = $pdo->prepare("INSERT INTO assortiment_merken (id, website_id, category_id, naam, logo_path, sort_order) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$new_id, $website_id, $category_id, $naam, $logo_path, $sort_order]);
    header("Location: assortimentbeheer.php?msg=merk_added");
    exit;
}

// --- 4B. MERK BEWERKEN ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'edit_merk') {
    csrf_verify();
    $id = $_POST['edit_id'] ?? '';
    $category_id = $_POST['category_id'] ?? '';
    $naam = trim($_POST['naam'] ?? '');

    $check = $pdo->prepare("SELECT logo_path FROM assortiment_merken WHERE id = ? AND website_id = ?");
    $check->execute([$id, $website_id]);
    $bestaande = $check->fetch();

    $cat_check = $pdo->prepare("SELECT id FROM assortiment_categorieen WHERE id = ? AND website_id = ?");
    $cat_check->execute([$category_id, $website_id]);

    if (!$bestaande || !$cat_check->fetchColumn() || $naam === '') {
        header("Location: assortimentbeheer.php?msg=invalid_merk");
        exit;
    }

    $logo_path = $bestaande['logo_path'];
    if (!empty($_FILES['logo']['name'])) {
        try {
            $nieuw_logo = save_uploaded_image('logo', $website_id);
            if ($nieuw_logo !== null) {
                delete_uploaded_file($logo_path ?: null);
                $logo_path = $nieuw_logo;
            }
        } catch (UploadException $e) {
            header("Location: assortimentbeheer.php?msg=upload_error");
            exit;
        }
    }

    $stmt = $pdo->prepare("UPDATE assortiment_merken SET category_id = ?, naam = ?, logo_path = ? WHERE id = ? AND website_id = ?");
    $stmt->execute([$category_id, $naam, $logo_path, $id, $website_id]);
    header("Location: assortimentbeheer.php?msg=merk_updated");
    exit;
}

// --- 5. MERK VERWIJDEREN ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_merk'])) {
    csrf_verify();
    $stmt = $pdo->prepare("SELECT logo_path FROM assortiment_merken WHERE id = ? AND website_id = ?");
    $stmt->execute([$_POST['delete_merk'], $website_id]);
    delete_uploaded_file($stmt->fetchColumn() ?: null);
    $pdo->prepare("DELETE FROM assortiment_merken WHERE id = ? AND website_id = ?")->execute([$_POST['delete_merk'], $website_id]);
    header("Location: assortimentbeheer.php?msg=merk_deleted");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM assortiment_categorieen WHERE website_id = ? ORDER BY sort_order ASC, titel ASC");
$stmt->execute([$website_id]);
$categorieen = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT * FROM assortiment_merken WHERE website_id = ? ORDER BY sort_order ASC, naam ASC");
$stmt->execute([$website_id]);
$merken = $stmt->fetchAll(PDO::FETCH_ASSOC);
$merkenByCategory = [];
foreach ($merken as $m) {
    $merkenByCategory[$m['category_id']][] = $m;
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <title>Assortiment Beheer - <?php echo htmlspecialchars(current_website()['company_name'] ?? 'Webius Portaal'); ?></title>
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
                <p class="text-gray-500 mt-1">Beheer hier de categorieën en de merken per categorie.</p>
            </div>
            <div class="flex items-center gap-4 mt-4 md:mt-0">
                <button id="toggleDragBtn" class="flex items-center gap-2 bg-slate-200 text-slate-700 font-bold py-2 px-6 rounded-full hover:bg-slate-300 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                    <span>Sleep-modus: UIT</span>
                </button>
                <a href="<?php echo htmlspecialchars(public_site_url('assortiment.php')); ?>" target="_blank" class="bg-pink-100 text-pink-700 font-bold py-2 px-6 rounded-full hover:bg-pink-200 transition-colors">
                    Bekijk publiek &rarr;
                </a>
            </div>
        </div>

        <div id="saveNotification" class="fixed bottom-4 right-4 bg-gray-900 text-white px-6 py-3 rounded-lg shadow-xl opacity-0 transition-opacity duration-300 z-50 flex items-center gap-3 pointer-events-none">
            <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            Volgorde opgeslagen!
        </div>

        <?php if (isset($_GET['msg'])): ?>
            <div class="mb-8 p-4 rounded-lg font-medium
                <?php echo (strpos($_GET['msg'], 'deleted') !== false || strpos($_GET['msg'], 'invalid') !== false || strpos($_GET['msg'], 'error') !== false) ? 'bg-red-100 text-red-800 border border-red-200' : 'bg-green-100 text-green-800 border border-green-200'; ?>">
                <?php
                    if ($_GET['msg'] == 'cat_added') echo 'Categorie succesvol toegevoegd!';
                    if ($_GET['msg'] == 'cat_updated') echo 'Categorie succesvol bijgewerkt!';
                    if ($_GET['msg'] == 'cat_deleted') echo 'Categorie (en bijbehorende merken) verwijderd!';
                    if ($_GET['msg'] == 'merk_added') echo 'Merk succesvol toegevoegd!';
                    if ($_GET['msg'] == 'merk_updated') echo 'Merk succesvol bijgewerkt!';
                    if ($_GET['msg'] == 'merk_deleted') echo 'Merk verwijderd!';
                    if ($_GET['msg'] == 'invalid_category') echo 'Vul minimaal een titel in.';
                    if ($_GET['msg'] == 'invalid_merk') echo 'Kies een categorie, vul een naam in en upload een logo.';
                    if ($_GET['msg'] == 'upload_error') echo 'De afbeelding kon niet worden geupload.';
                ?>
            </div>
        <?php endif; ?>

        <div class="mb-16">
            <h2 class="text-2xl font-bold text-slate-900 mb-6 flex items-center">
                <span class="bg-pink-500 text-white w-8 h-8 flex items-center justify-center rounded-full mr-3 text-sm">1</span>
                Categorieën
            </h2>
            <div class="grid lg:grid-cols-3 gap-8">
                <div class="lg:col-span-1 bg-white p-6 rounded-xl shadow-sm border border-gray-100 h-fit">
                    <h3 class="text-lg font-bold mb-4">Nieuwe Categorie</h3>
                    <form method="POST" action="assortimentbeheer.php" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="action" value="add_category">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Titel</label>
                            <input type="text" name="titel" placeholder="bijv. Wenskaarten" required class="w-full border-gray-300 rounded-md border p-2 focus:border-pink-500 focus:ring-pink-500">
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Beschrijving</label>
                            <textarea name="beschrijving" rows="3" class="w-full border-gray-300 rounded-md border p-2 focus:border-pink-500 focus:ring-pink-500"></textarea>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Badge-tekst (optioneel)</label>
                            <input type="text" name="badge_tekst" placeholder="bijv. Grootste Keuze" class="w-full border-gray-300 rounded-md border p-2 focus:border-pink-500 focus:ring-pink-500">
                        </div>
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Afbeelding</label>
                            <input type="file" name="afbeelding" accept="image/jpeg,image/png,image/webp,image/gif" class="w-full text-sm">
                        </div>
                        <button type="submit" class="w-full bg-slate-900 text-white font-bold py-2 px-4 rounded-lg hover:bg-slate-800 transition-colors">
                            Categorie Toevoegen
                        </button>
                    </form>
                </div>
                <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold">Bestaande Categorieën</h3>
                        <span class="text-sm text-gray-400 italic hidden drag-instruction-cat">Sleep aan de lijntjes om te verplaatsen</span>
                    </div>
                    <?php if (empty($categorieen)): ?>
                        <p class="text-gray-500 italic">Nog geen categorieën aangemaakt.</p>
                    <?php else: ?>
                        <div class="overflow-x-auto border rounded-lg border-gray-200">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="drag-column-cat hidden w-12 px-4 py-3 bg-pink-50"></th>
                                        <th class="w-16 px-4 py-3"></th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Titel</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actie</th>
                                    </tr>
                                </thead>
                                <tbody id="categorieen-list" class="divide-y divide-gray-200 bg-white">
                                    <?php foreach ($categorieen as $cat): ?>
                                        <tr data-id="<?php echo htmlspecialchars($cat['id']); ?>" class="hover:bg-gray-50">
                                            <td class="drag-column-cat hidden px-4 py-3 drag-handle text-gray-400 hover:text-pink-500 bg-pink-50">☰</td>
                                            <td class="px-4 py-3">
                                                <?php if (!empty($cat['afbeelding'])): ?>
                                                    <img src="<?php echo htmlspecialchars($cat['afbeelding']); ?>" class="w-10 h-10 rounded object-cover border border-gray-200">
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-4 py-3 text-sm font-medium text-slate-900">
                                                <?php echo htmlspecialchars($cat['titel']); ?>
                                                <?php if (!empty($cat['badge_tekst'])): ?>
                                                    <span class="inline-block ml-2 bg-pink-100 text-pink-700 text-[10px] font-bold px-2 py-0.5 rounded-full"><?php echo htmlspecialchars($cat['badge_tekst']); ?></span>
                                                <?php endif; ?>
                                                <div class="text-xs text-gray-400 font-normal mt-0.5"><?php echo count($merkenByCategory[$cat['id']] ?? []); ?> merken</div>
                                            </td>
                                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                                <button type="button" class="edit-cat-btn text-blue-600 hover:text-blue-800 font-medium text-sm mr-3"
                                                    data-id="<?php echo htmlspecialchars($cat['id']); ?>"
                                                    data-titel="<?php echo htmlspecialchars($cat['titel']); ?>"
                                                    data-beschrijving="<?php echo htmlspecialchars($cat['beschrijving']); ?>"
                                                    data-badge="<?php echo htmlspecialchars($cat['badge_tekst'] ?? ''); ?>"
                                                    data-afbeelding="<?php echo htmlspecialchars($cat['afbeelding'] ?? ''); ?>">Bewerk</button>
                                                <button type="submit" form="delete-cat-<?php echo htmlspecialchars($cat['id']); ?>" class="text-red-500 hover:text-red-700 font-medium text-sm">Verwijder</button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php foreach ($categorieen as $cat): ?>
                            <form method="POST" action="assortimentbeheer.php" id="delete-cat-<?php echo htmlspecialchars($cat['id']); ?>" onsubmit="return confirm('Weet je het zeker? LET OP: Alle merken in deze categorie worden ook verwijderd!');" class="hidden">
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
                Merken
            </h2>
            <div class="grid lg:grid-cols-3 gap-8">
                <div class="lg:col-span-1 bg-white p-6 rounded-xl shadow-sm border border-gray-100 h-fit">
                    <h3 class="text-lg font-bold mb-4">Nieuw Merk Toevoegen</h3>
                    <?php if (empty($categorieen)): ?>
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                            <p class="text-sm text-yellow-700">Er moet eerst een categorie aangemaakt worden hierboven.</p>
                        </div>
                    <?php else: ?>
                        <form method="POST" action="assortimentbeheer.php" enctype="multipart/form-data">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="action" value="add_merk">
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Categorie</label>
                                <select name="category_id" required class="w-full border-gray-300 rounded-md border p-2 bg-white focus:border-pink-500 focus:ring-pink-500">
                                    <?php foreach ($categorieen as $cat): ?>
                                        <option value="<?php echo htmlspecialchars($cat['id']); ?>"><?php echo htmlspecialchars($cat['titel']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Naam merk</label>
                                <input type="text" name="naam" required class="w-full border-gray-300 rounded-md border p-2 focus:border-pink-500 focus:ring-pink-500">
                            </div>
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Logo</label>
                                <input type="file" name="logo" accept="image/jpeg,image/png,image/webp,image/gif" required class="w-full text-sm">
                            </div>
                            <button type="submit" class="w-full bg-slate-900 text-white font-bold py-2 px-4 rounded-lg hover:bg-slate-800 transition-colors">
                                Merk Toevoegen
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
                <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                    <?php if (empty($categorieen)): ?>
                        <p class="text-gray-500 italic">Nog geen merken.</p>
                    <?php else: ?>
                        <?php foreach ($categorieen as $cat): ?>
                            <div class="mb-8">
                                <h4 class="text-md font-bold text-pink-600 mb-3"><?php echo htmlspecialchars($cat['titel']); ?></h4>
                                <?php if (empty($merkenByCategory[$cat['id']])): ?>
                                    <p class="text-sm text-gray-400 italic">Nog geen merken in deze categorie.</p>
                                <?php else: ?>
                                    <div class="overflow-x-auto border rounded-lg border-gray-200">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <tbody class="merken-list divide-y divide-gray-200 bg-white" data-category="<?php echo htmlspecialchars($cat['id']); ?>">
                                                <?php foreach ($merkenByCategory[$cat['id']] as $m): ?>
                                                    <tr data-id="<?php echo htmlspecialchars($m['id']); ?>" class="hover:bg-gray-50">
                                                        <td class="drag-column-merk hidden w-12 px-4 py-3 drag-handle text-gray-400 hover:text-pink-500 bg-pink-50">☰</td>
                                                        <td class="w-16 px-4 py-3"><img src="<?php echo htmlspecialchars($m['logo_path']); ?>" class="w-10 h-10 rounded object-cover border border-gray-200"></td>
                                                        <td class="px-4 py-3 text-sm font-medium text-slate-900"><?php echo htmlspecialchars($m['naam']); ?></td>
                                                        <td class="px-4 py-3 text-right whitespace-nowrap">
                                                            <button type="button" class="edit-merk-btn text-blue-600 hover:text-blue-800 font-medium text-sm mr-3"
                                                                data-id="<?php echo htmlspecialchars($m['id']); ?>"
                                                                data-naam="<?php echo htmlspecialchars($m['naam']); ?>"
                                                                data-category="<?php echo htmlspecialchars($m['category_id']); ?>"
                                                                data-logo="<?php echo htmlspecialchars($m['logo_path'] ?? ''); ?>">Bewerk</button>
                                                            <button type="submit" form="delete-merk-<?php echo htmlspecialchars($m['id']); ?>" class="text-red-500 hover:text-red-700 font-medium text-sm">Verwijder</button>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                        <?php foreach ($merken as $m): ?>
                            <form method="POST" action="assortimentbeheer.php" id="delete-merk-<?php echo htmlspecialchars($m['id']); ?>" onsubmit="return confirm('Weet je zeker dat je dit merk wilt verwijderen?');" class="hidden">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="delete_merk" value="<?php echo htmlspecialchars($m['id']); ?>">
                            </form>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div id="editCatModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold">Categorie Bewerken</h3>
                <button type="button" id="closeEditCatModal" class="text-gray-400 hover:text-gray-700 text-2xl leading-none">&times;</button>
            </div>
            <form method="POST" action="assortimentbeheer.php" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="action" value="edit_category">
                <input type="hidden" name="edit_id" id="edit_cat_id">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Titel</label>
                    <input type="text" name="titel" id="edit_cat_titel" required class="w-full border-gray-300 rounded-md border p-2 focus:border-pink-500 focus:ring-pink-500">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Beschrijving</label>
                    <textarea name="beschrijving" id="edit_cat_beschrijving" rows="3" class="w-full border-gray-300 rounded-md border p-2 focus:border-pink-500 focus:ring-pink-500"></textarea>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Badge-tekst (optioneel)</label>
                    <input type="text" name="badge_tekst" id="edit_cat_badge" class="w-full border-gray-300 rounded-md border p-2 focus:border-pink-500 focus:ring-pink-500">
                </div>
                <div class="mb-2">
                    <img id="edit_cat_preview" src="" class="w-16 h-16 rounded object-cover border border-gray-200 hidden">
                </div>
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nieuwe afbeelding (optioneel, laat leeg om te behouden)</label>
                    <input type="file" name="afbeelding" accept="image/jpeg,image/png,image/webp,image/gif" class="w-full text-sm">
                </div>
                <button type="submit" class="w-full bg-slate-900 text-white font-bold py-2 px-4 rounded-lg hover:bg-slate-800 transition-colors">
                    Wijzigingen Opslaan
                </button>
            </form>
        </div>
    </div>

    <div id="editMerkModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold">Merk Bewerken</h3>
                <button type="button" id="closeEditMerkModal" class="text-gray-400 hover:text-gray-700 text-2xl leading-none">&times;</button>
            </div>
            <form method="POST" action="assortimentbeheer.php" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="action" value="edit_merk">
                <input type="hidden" name="edit_id" id="edit_merk_id">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Categorie</label>
                    <select name="category_id" id="edit_merk_category" required class="w-full border-gray-300 rounded-md border p-2 bg-white focus:border-pink-500 focus:ring-pink-500">
                        <?php foreach ($categorieen as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat['id']); ?>"><?php echo htmlspecialchars($cat['titel']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Naam merk</label>
                    <input type="text" name="naam" id="edit_merk_naam" required class="w-full border-gray-300 rounded-md border p-2 focus:border-pink-500 focus:ring-pink-500">
                </div>
                <div class="mb-2">
                    <img id="edit_merk_preview" src="" class="w-16 h-16 rounded object-cover border border-gray-200 hidden">
                </div>
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nieuw logo (optioneel, laat leeg om te behouden)</label>
                    <input type="file" name="logo" accept="image/jpeg,image/png,image/webp,image/gif" class="w-full text-sm">
                </div>
                <button type="submit" class="w-full bg-slate-900 text-white font-bold py-2 px-4 rounded-lg hover:bg-slate-800 transition-colors">
                    Wijzigingen Opslaan
                </button>
            </form>
        </div>
    </div>

    <script>
        const CSRF_TOKEN = <?php echo json_encode(csrf_token()); ?>;

        document.addEventListener('DOMContentLoaded', function () {
            const toggleBtn = document.getElementById('toggleDragBtn');
            const dragColumns = document.querySelectorAll('.drag-column-cat, .drag-column-merk');
            const dragInstructions = document.querySelectorAll('.drag-instruction-cat');
            let isDragMode = false;
            const sortableInstances = [];

            const catList = document.getElementById('categorieen-list');
            if (catList) {
                sortableInstances.push(new Sortable(catList, {
                    animation: 150, handle: '.drag-handle', disabled: true,
                    onEnd: function (evt) { saveNewOrder('categorieen', evt.to); }
                }));
            }
            document.querySelectorAll('.merken-list').forEach(list => {
                if (list.querySelector('tr[data-id]')) {
                    sortableInstances.push(new Sortable(list, {
                        animation: 150, handle: '.drag-handle', disabled: true,
                        onEnd: function (evt) { saveNewOrder('merken', evt.to); }
                    }));
                }
            });

            toggleBtn.addEventListener('click', function() {
                isDragMode = !isDragMode;
                sortableInstances.forEach(instance => instance.option('disabled', !isDragMode));

                if (isDragMode) {
                    toggleBtn.classList.replace('bg-slate-200', 'bg-green-500');
                    toggleBtn.classList.replace('text-slate-700', 'text-white');
                    toggleBtn.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg> <span>Sleep-modus: AAN</span>';
                    dragColumns.forEach(el => el.classList.remove('hidden'));
                    dragInstructions.forEach(el => el.classList.remove('hidden'));
                } else {
                    toggleBtn.classList.replace('bg-green-500', 'bg-slate-200');
                    toggleBtn.classList.replace('text-white', 'text-slate-700');
                    toggleBtn.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg> <span>Sleep-modus: UIT</span>';
                    dragColumns.forEach(el => el.classList.add('hidden'));
                    dragInstructions.forEach(el => el.classList.add('hidden'));
                    window.location.reload();
                }
            });

            function saveNewOrder(type, tableBody) {
                const rows = tableBody.querySelectorAll('tr[data-id]');
                const orderData = [];
                rows.forEach((row, index) => orderData.push({ id: row.getAttribute('data-id'), order: index + 1 }));
                if (orderData.length === 0) return;

                const formData = new FormData();
                formData.append('ajax_action', 'update_order');
                formData.append('type', type);
                formData.append('items', JSON.stringify(orderData));
                formData.append('csrf_token', CSRF_TOKEN);

                fetch('assortimentbeheer.php', { method: 'POST', body: formData })
                    .then(response => response.json())
                    .then(data => { if (data.status === 'success') showNotification(); });
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

            const editCatModal = document.getElementById('editCatModal');
            document.querySelectorAll('.edit-cat-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    document.getElementById('edit_cat_id').value = this.dataset.id;
                    document.getElementById('edit_cat_titel').value = this.dataset.titel;
                    document.getElementById('edit_cat_beschrijving').value = this.dataset.beschrijving;
                    document.getElementById('edit_cat_badge').value = this.dataset.badge;
                    const preview = document.getElementById('edit_cat_preview');
                    if (this.dataset.afbeelding) {
                        preview.src = this.dataset.afbeelding;
                        preview.classList.remove('hidden');
                    } else {
                        preview.classList.add('hidden');
                    }
                    editCatModal.classList.remove('hidden');
                });
            });
            document.getElementById('closeEditCatModal').addEventListener('click', () => editCatModal.classList.add('hidden'));
            editCatModal.addEventListener('click', function (e) { if (e.target === this) this.classList.add('hidden'); });

            const editMerkModal = document.getElementById('editMerkModal');
            document.querySelectorAll('.edit-merk-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    document.getElementById('edit_merk_id').value = this.dataset.id;
                    document.getElementById('edit_merk_naam').value = this.dataset.naam;
                    document.getElementById('edit_merk_category').value = this.dataset.category;
                    const preview = document.getElementById('edit_merk_preview');
                    if (this.dataset.logo) {
                        preview.src = this.dataset.logo;
                        preview.classList.remove('hidden');
                    } else {
                        preview.classList.add('hidden');
                    }
                    editMerkModal.classList.remove('hidden');
                });
            });
            document.getElementById('closeEditMerkModal').addEventListener('click', () => editMerkModal.classList.add('hidden'));
            editMerkModal.addEventListener('click', function (e) { if (e.target === this) this.classList.add('hidden'); });
        });
    </script>
</body>
</html>
