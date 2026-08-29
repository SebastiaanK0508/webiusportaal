<?php
require_once 'includes/init.php';
$website_id = require_website_context();
require_module($website_id, 'cadeaukaarten');

// --- AJAX SORTERING OPSLAAN ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_action']) && $_POST['ajax_action'] === 'update_order') {
    header('Content-Type: application/json');
    csrf_verify();
    $items = json_decode($_POST['items'], true);
    $stmt = $pdo->prepare("UPDATE cadeaukaarten SET sort_order = ? WHERE id = ? AND website_id = ?");
    foreach ($items as $item) {
        $stmt->execute([$item['order'], $item['id'], $website_id]);
    }
    echo json_encode(['status' => 'success']);
    exit;
}

// --- CADEAUKAART TOEVOEGEN ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_kaart') {
    csrf_verify();
    $kaart_naam = trim($_POST['kaart_naam'] ?? '');
    $geschikte_winkels = trim($_POST['geschikte_winkels'] ?? '');
    $tags = trim($_POST['tags'] ?? '');

    if ($kaart_naam === '') {
        header("Location: cadeaukaartenbeheer.php?msg=invalid");
        exit;
    }

    try {
        $afbeelding = save_uploaded_image('afbeelding', $website_id);
    } catch (UploadException $e) {
        header("Location: cadeaukaartenbeheer.php?msg=upload_error");
        exit;
    }

    $stmt = $pdo->prepare("SELECT MAX(sort_order) FROM cadeaukaarten WHERE website_id = ?");
    $stmt->execute([$website_id]);
    $max = $stmt->fetchColumn();
    $sort_order = $max ? $max + 1 : 1;

    $new_id = $pdo->query('SELECT UUID()')->fetchColumn();
    $stmt = $pdo->prepare("INSERT INTO cadeaukaarten (id, website_id, kaart_naam, geschikte_winkels, tags, afbeelding, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$new_id, $website_id, $kaart_naam, $geschikte_winkels, $tags, $afbeelding, $sort_order]);
    header("Location: cadeaukaartenbeheer.php?msg=added");
    exit;
}

// --- CADEAUKAART BEWERKEN ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_kaart') {
    csrf_verify();
    $id = $_POST['edit_id'] ?? '';
    $kaart_naam = trim($_POST['kaart_naam'] ?? '');
    $geschikte_winkels = trim($_POST['geschikte_winkels'] ?? '');
    $tags = trim($_POST['tags'] ?? '');

    $check = $pdo->prepare("SELECT afbeelding FROM cadeaukaarten WHERE id = ? AND website_id = ?");
    $check->execute([$id, $website_id]);
    $bestaande = $check->fetch();

    if (!$bestaande || $kaart_naam === '') {
        header("Location: cadeaukaartenbeheer.php?msg=invalid");
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
            header("Location: cadeaukaartenbeheer.php?msg=upload_error");
            exit;
        }
    }

    $stmt = $pdo->prepare("UPDATE cadeaukaarten SET kaart_naam = ?, geschikte_winkels = ?, tags = ?, afbeelding = ? WHERE id = ? AND website_id = ?");
    $stmt->execute([$kaart_naam, $geschikte_winkels, $tags, $afbeelding, $id, $website_id]);
    header("Location: cadeaukaartenbeheer.php?msg=updated");
    exit;
}

// --- CADEAUKAART VERWIJDEREN ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_kaart'])) {
    csrf_verify();
    $stmt = $pdo->prepare("SELECT afbeelding FROM cadeaukaarten WHERE id = ? AND website_id = ?");
    $stmt->execute([$_POST['delete_kaart'], $website_id]);
    delete_uploaded_file($stmt->fetchColumn() ?: null);
    $pdo->prepare("DELETE FROM cadeaukaarten WHERE id = ? AND website_id = ?")->execute([$_POST['delete_kaart'], $website_id]);
    header("Location: cadeaukaartenbeheer.php?msg=deleted");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM cadeaukaarten WHERE website_id = ? ORDER BY sort_order ASC, kaart_naam ASC");
$stmt->execute([$website_id]);
$kaarten = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <title>Cadeaukaarten Beheer - <?php echo htmlspecialchars(current_website()['company_name'] ?? 'Webius Portaal'); ?></title>
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
                <h1 class="text-3xl font-extrabold text-slate-900">Cadeaukaarten Beheer</h1>
                <p class="text-gray-500 mt-1">Beheer hier het aanbod cadeaukaarten.</p>
            </div>
            <div class="flex items-center gap-4 mt-4 md:mt-0">
                <button id="toggleDragBtn" class="flex items-center gap-2 bg-slate-200 text-slate-700 font-bold py-2 px-6 rounded-full hover:bg-slate-300 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                    <span>Sleep-modus: UIT</span>
                </button>
                <a href="cadeaukaarten.php" target="_blank" class="bg-pink-100 text-pink-700 font-bold py-2 px-6 rounded-full hover:bg-pink-200 transition-colors">
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
                <?php echo (strpos($_GET['msg'], 'deleted') !== false || strpos($_GET['msg'], 'error') !== false || $_GET['msg'] === 'invalid') ? 'bg-red-100 text-red-800 border border-red-200' : 'bg-green-100 text-green-800 border border-green-200'; ?>">
                <?php
                    if ($_GET['msg'] === 'added') echo 'Cadeaukaart succesvol toegevoegd!';
                    if ($_GET['msg'] === 'updated') echo 'Cadeaukaart succesvol bijgewerkt!';
                    if ($_GET['msg'] === 'deleted') echo 'Cadeaukaart verwijderd!';
                    if ($_GET['msg'] === 'invalid') echo 'Vul minimaal een naam in.';
                    if ($_GET['msg'] === 'upload_error') echo 'De afbeelding kon niet worden geupload.';
                ?>
            </div>
        <?php endif; ?>

        <div class="grid lg:grid-cols-3 gap-8">
            <div class="lg:col-span-1 bg-white p-6 rounded-xl shadow-sm border border-gray-100 h-fit">
                <h3 class="text-lg font-bold mb-4">Nieuwe Cadeaukaart</h3>
                <form method="POST" action="cadeaukaartenbeheer.php" enctype="multipart/form-data">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="action" value="add_kaart">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Naam kaart</label>
                        <input type="text" name="kaart_naam" placeholder="bijv. HEMA Cadeaubon" required class="w-full border-gray-300 rounded-md border p-2 focus:border-pink-500 focus:ring-pink-500">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Geschikte winkels</label>
                        <input type="text" name="geschikte_winkels" placeholder="bijv. Alle HEMA filialen en online" class="w-full border-gray-300 rounded-md border p-2 focus:border-pink-500 focus:ring-pink-500">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tags (kommagescheiden)</label>
                        <input type="text" name="tags" placeholder="bijv. wonen, kleding, huishouden" class="w-full border-gray-300 rounded-md border p-2 focus:border-pink-500 focus:ring-pink-500">
                    </div>
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Afbeelding</label>
                        <input type="file" name="afbeelding" accept="image/jpeg,image/png,image/webp,image/gif" class="w-full text-sm">
                    </div>
                    <button type="submit" class="w-full bg-slate-900 text-white font-bold py-2 px-4 rounded-lg hover:bg-slate-800 transition-colors">
                        Cadeaukaart Toevoegen
                    </button>
                </form>
            </div>

            <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold">Bestaande Cadeaukaarten (<?php echo count($kaarten); ?>)</h3>
                    <span class="text-sm text-gray-400 italic hidden drag-instruction">Sleep aan de lijntjes om te verplaatsen</span>
                </div>

                <?php if (empty($kaarten)): ?>
                    <p class="text-gray-500 italic">Nog geen cadeaukaarten toegevoegd.</p>
                <?php else: ?>
                    <div class="overflow-x-auto border rounded-lg border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="drag-column hidden w-12 px-4 py-3 bg-pink-50"></th>
                                    <th class="w-16 px-4 py-3"></th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Naam</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden md:table-cell">Winkels</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actie</th>
                                </tr>
                            </thead>
                            <tbody id="kaarten-list" class="divide-y divide-gray-200 bg-white">
                                <?php foreach ($kaarten as $k): ?>
                                    <tr data-id="<?php echo htmlspecialchars($k['id']); ?>" class="hover:bg-gray-50">
                                        <td class="drag-column hidden px-4 py-3 drag-handle text-gray-400 hover:text-pink-500 bg-pink-50">☰</td>
                                        <td class="px-4 py-3">
                                            <?php if (!empty($k['afbeelding'])): ?>
                                                <img src="<?php echo htmlspecialchars($k['afbeelding']); ?>" class="w-10 h-10 rounded object-cover border border-gray-200">
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-4 py-3 text-sm font-medium text-slate-900"><?php echo htmlspecialchars($k['kaart_naam']); ?></td>
                                        <td class="px-4 py-3 text-sm text-gray-500 hidden md:table-cell"><?php echo htmlspecialchars($k['geschikte_winkels']); ?></td>
                                        <td class="px-4 py-3 text-right whitespace-nowrap">
                                            <button type="button" class="edit-kaart-btn text-blue-600 hover:text-blue-800 font-medium text-sm mr-3"
                                                data-id="<?php echo htmlspecialchars($k['id']); ?>"
                                                data-naam="<?php echo htmlspecialchars($k['kaart_naam']); ?>"
                                                data-winkels="<?php echo htmlspecialchars($k['geschikte_winkels']); ?>"
                                                data-tags="<?php echo htmlspecialchars($k['tags']); ?>"
                                                data-afbeelding="<?php echo htmlspecialchars($k['afbeelding'] ?? ''); ?>">Bewerk</button>
                                            <button type="submit" form="delete-kaart-<?php echo htmlspecialchars($k['id']); ?>" class="text-red-500 hover:text-red-700 font-medium text-sm">Verwijder</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php foreach ($kaarten as $k): ?>
                        <form method="POST" action="cadeaukaartenbeheer.php" id="delete-kaart-<?php echo htmlspecialchars($k['id']); ?>" onsubmit="return confirm('Weet je zeker dat je deze cadeaukaart wilt verwijderen?');" class="hidden">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="delete_kaart" value="<?php echo htmlspecialchars($k['id']); ?>">
                        </form>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div id="editKaartModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold">Cadeaukaart Bewerken</h3>
                <button type="button" id="closeEditKaartModal" class="text-gray-400 hover:text-gray-700 text-2xl leading-none">&times;</button>
            </div>
            <form method="POST" action="cadeaukaartenbeheer.php" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="action" value="edit_kaart">
                <input type="hidden" name="edit_id" id="edit_kaart_id">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Naam kaart</label>
                    <input type="text" name="kaart_naam" id="edit_kaart_naam" required class="w-full border-gray-300 rounded-md border p-2 focus:border-pink-500 focus:ring-pink-500">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Geschikte winkels</label>
                    <input type="text" name="geschikte_winkels" id="edit_kaart_winkels" class="w-full border-gray-300 rounded-md border p-2 focus:border-pink-500 focus:ring-pink-500">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tags (kommagescheiden)</label>
                    <input type="text" name="tags" id="edit_kaart_tags" class="w-full border-gray-300 rounded-md border p-2 focus:border-pink-500 focus:ring-pink-500">
                </div>
                <div class="mb-2">
                    <img id="edit_kaart_preview" src="" class="w-16 h-16 rounded object-cover border border-gray-200 hidden">
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

    <script>
        const CSRF_TOKEN = <?php echo json_encode(csrf_token()); ?>;
        document.addEventListener('DOMContentLoaded', function () {
            const toggleBtn = document.getElementById('toggleDragBtn');
            const dragColumns = document.querySelectorAll('.drag-column');
            const dragInstructions = document.querySelectorAll('.drag-instruction');
            let isDragMode = false;
            let sortable = null;

            const list = document.getElementById('kaarten-list');
            if (list) {
                sortable = new Sortable(list, {
                    animation: 150,
                    handle: '.drag-handle',
                    disabled: true,
                    onEnd: function (evt) { saveNewOrder(evt.to); }
                });
            }

            toggleBtn.addEventListener('click', function() {
                isDragMode = !isDragMode;
                if (sortable) sortable.option("disabled", !isDragMode);

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

            function saveNewOrder(tableBody) {
                const rows = tableBody.querySelectorAll('tr[data-id]');
                const orderData = [];
                rows.forEach((row, index) => orderData.push({ id: row.getAttribute('data-id'), order: index + 1 }));
                if (orderData.length === 0) return;

                const formData = new FormData();
                formData.append('ajax_action', 'update_order');
                formData.append('items', JSON.stringify(orderData));
                formData.append('csrf_token', CSRF_TOKEN);

                fetch('cadeaukaartenbeheer.php', { method: 'POST', body: formData })
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

            const editModal = document.getElementById('editKaartModal');
            document.querySelectorAll('.edit-kaart-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    document.getElementById('edit_kaart_id').value = this.dataset.id;
                    document.getElementById('edit_kaart_naam').value = this.dataset.naam;
                    document.getElementById('edit_kaart_winkels').value = this.dataset.winkels;
                    document.getElementById('edit_kaart_tags').value = this.dataset.tags;
                    const preview = document.getElementById('edit_kaart_preview');
                    if (this.dataset.afbeelding) {
                        preview.src = this.dataset.afbeelding;
                        preview.classList.remove('hidden');
                    } else {
                        preview.classList.add('hidden');
                    }
                    editModal.classList.remove('hidden');
                });
            });
            document.getElementById('closeEditKaartModal').addEventListener('click', () => editModal.classList.add('hidden'));
            editModal.addEventListener('click', function (e) { if (e.target === this) this.classList.add('hidden'); });
        });
    </script>
</body>
</html>
