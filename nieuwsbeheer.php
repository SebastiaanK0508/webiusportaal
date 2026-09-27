<?php
require_once 'includes/init.php';
$website_id = require_website_context();
require_module($website_id, 'nieuws');

function handle_image_uploads($website_id)
{
    $image_paths = [];
    if (empty($_FILES['images']['name'][0])) {
        return $image_paths;
    }
    $count = count($_FILES['images']['name']);
    for ($i = 0; $i < $count; $i++) {
        if ($_FILES['images']['error'][$i] === UPLOAD_ERR_NO_FILE) {
            continue;
        }
        $_FILES['__single_image'] = [
            'name' => $_FILES['images']['name'][$i],
            'type' => $_FILES['images']['type'][$i],
            'tmp_name' => $_FILES['images']['tmp_name'][$i],
            'error' => $_FILES['images']['error'][$i],
            'size' => $_FILES['images']['size'][$i],
        ];
        try {
            $path = save_uploaded_image('__single_image', $website_id);
            if ($path) {
                $image_paths[] = $path;
            }
        } catch (UploadException $e) {
            foreach ($image_paths as $p) {
                delete_uploaded_file($p);
            }
            throw $e;
        }
    }
    return $image_paths;
}

function valid_artikel_input(&$title, &$content, &$status)
{
    $title = trim($_POST['title'] ?? '');
    $content = $_POST['content'] ?? '';
    $status = in_array($_POST['status'] ?? '', ['draft', 'published'], true) ? $_POST['status'] : 'published';
    return $title !== '' && trim(strip_tags($content)) !== '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_artikel') {
    csrf_verify();
    if (!valid_artikel_input($title, $content, $status)) {
        header("Location: nieuwsbeheer.php?msg=invalid");
        exit;
    }

    try {
        $image_paths = handle_image_uploads($website_id);
    } catch (UploadException $e) {
        header("Location: nieuwsbeheer.php?msg=upload_error");
        exit;
    }

    $new_id = $pdo->query('SELECT UUID()')->fetchColumn();
    $stmt = $pdo->prepare("INSERT INTO nieuws (id, website_id, title, content, image_paths, status) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$new_id, $website_id, $title, $content, $image_paths ? implode(',', $image_paths) : null, $status]);
    header("Location: nieuwsbeheer.php?msg=added");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'edit_artikel') {
    csrf_verify();
    $id = $_POST['id'] ?? '';

    $stmt = $pdo->prepare("SELECT image_paths FROM nieuws WHERE id = ? AND website_id = ?");
    $stmt->execute([$id, $website_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        header("Location: nieuwsbeheer.php?msg=notfound");
        exit;
    }

    if (!valid_artikel_input($title, $content, $status)) {
        header("Location: nieuwsbeheer.php?msg=invalid");
        exit;
    }
    $old_paths = $row['image_paths'] ? array_values(array_filter(explode(',', $row['image_paths']))) : [];
    $requested_keep = (array)($_POST['keep_images'] ?? []);
    $keep_paths = array_values(array_intersect($old_paths, $requested_keep));

    try {
        $new_paths = handle_image_uploads($website_id);
    } catch (UploadException $e) {
        header("Location: nieuwsbeheer.php?msg=upload_error");
        exit;
    }

    $final_paths = array_merge($keep_paths, $new_paths);
    $pdo->prepare("UPDATE nieuws SET title = ?, content = ?, image_paths = ?, status = ? WHERE id = ? AND website_id = ?")
        ->execute([$title, $content, $final_paths ? implode(',', $final_paths) : null, $status, $id, $website_id]);
    foreach (array_diff($old_paths, $keep_paths) as $p) {
        delete_uploaded_file($p);
    }

    header("Location: nieuwsbeheer.php?msg=updated");
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_artikel'])) {
    csrf_verify();
    $stmt = $pdo->prepare("SELECT image_paths FROM nieuws WHERE id = ? AND website_id = ?");
    $stmt->execute([$_POST['delete_artikel'], $website_id]);
    $paths = $stmt->fetchColumn();
    if ($paths) {
        foreach (explode(',', $paths) as $p) {
            delete_uploaded_file($p);
        }
    }
    $pdo->prepare("DELETE FROM nieuws WHERE id = ? AND website_id = ?")->execute([$_POST['delete_artikel'], $website_id]);
    header("Location: nieuwsbeheer.php?msg=deleted");
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_status'])) {
    csrf_verify();
    $pdo->prepare("UPDATE nieuws SET status = IF(status = 'published', 'draft', 'published') WHERE id = ? AND website_id = ?")
        ->execute([$_POST['toggle_status'], $website_id]);
    header("Location: nieuwsbeheer.php?msg=status_updated");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM nieuws WHERE website_id = ? ORDER BY created_at DESC");
$stmt->execute([$website_id]);
$artikelen = $stmt->fetchAll(PDO::FETCH_ASSOC);
$artikelen_js = [];
foreach ($artikelen as $a) {
    $artikelen_js[$a['id']] = [
        'id' => $a['id'],
        'title' => $a['title'],
        'content' => $a['content'],
        'status' => $a['status'],
        'images' => $a['image_paths'] ? array_values(array_filter(explode(',', $a['image_paths']))) : [],
    ];
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <title>Nieuws Beheer - <?php echo htmlspecialchars(current_website()['company_name'] ?? 'Webius Portaal'); ?></title>
    <?php include 'includes/head.php'; ?>
    <script src="https://cdn.ckeditor.com/ckeditor5/41.1.0/classic/ckeditor.js"></script>
    <style>
        .ck-editor__editable { min-height: 220px; }
        /* CKEditor-popups (link, etc.) boven de modal tonen */
        .ck-body-wrapper .ck-balloon-panel { z-index: 10000 !important; }
    </style>
</head>
<body class="bg-gray-50 font-sans pb-20 text-gray-800">
    <?php include 'includes/header.php'; ?>
    <div class="max-w-7xl p-8 mx-auto">
        <div class="flex flex-col md:flex-row justify-between items-center mb-8 bg-white p-6 rounded-xl shadow-sm border border-gray-200">
            <div>
                <h1 class="text-3xl font-extrabold text-slate-900">Nieuws Beheer</h1>
                <p class="text-gray-500 mt-1">Plaats nieuwsartikelen op de website.</p>
            </div>
            <div class="flex items-center gap-3 mt-4 md:mt-0">
                <a href="<?php echo htmlspecialchars(public_site_url('nieuws.php')); ?>" target="_blank" class="bg-pink-100 text-pink-700 font-bold py-2 px-6 rounded-full hover:bg-pink-200 transition-colors">
                    Bekijk publiek &rarr;
                </a>
                <button type="button" onclick="openArtikelModal()" class="bg-slate-900 text-white font-bold py-2 px-6 rounded-full hover:bg-slate-800 transition-colors">
                    + Nieuw artikel
                </button>
            </div>
        </div>

        <?php if (isset($_GET['msg'])):
            $__msg_map = [
                'added' => 'Artikel succesvol geplaatst!',
                'updated' => 'Artikel bijgewerkt!',
                'deleted' => 'Artikel verwijderd!',
                'status_updated' => 'Status bijgewerkt!',
                'invalid' => 'Vul een titel en inhoud in.',
                'notfound' => 'Artikel niet gevonden.',
                'upload_error' => 'Eén van de afbeeldingen kon niet worden geüpload.',
            ];
            $__msg_text = $__msg_map[$_GET['msg']] ?? null;
            $__msg_type = in_array($_GET['msg'], ['deleted', 'invalid', 'notfound', 'upload_error'], true) ? 'error' : 'success';
        ?>
            <?php if ($__msg_text): ?>
                <script>showToast(<?php echo json_encode($__msg_text); ?>, <?php echo json_encode($__msg_type); ?>);</script>
            <?php endif; ?>
        <?php endif; ?>

        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <h3 class="text-lg font-bold mb-4">Bestaande Artikelen (<?php echo count($artikelen); ?>)</h3>
            <?php if (empty($artikelen)): ?>
                <p class="text-gray-500 italic">Nog geen artikelen geplaatst.</p>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($artikelen as $a): $first_image = $a['image_paths'] ? explode(',', $a['image_paths'])[0] : null; ?>
                        <div class="flex items-center gap-4 border border-gray-200 rounded-lg p-3 hover:bg-gray-50">
                            <?php if ($first_image): ?>
                                <img src="<?php echo htmlspecialchars($first_image); ?>" class="w-14 h-14 rounded object-cover border border-gray-200 flex-shrink-0" alt="">
                            <?php else: ?>
                                <div class="w-14 h-14 rounded bg-gray-100 flex-shrink-0"></div>
                            <?php endif; ?>
                            <div class="flex-1 min-w-0">
                                <div class="font-medium text-slate-900 truncate"><?php echo htmlspecialchars($a['title']); ?></div>
                                <div class="text-xs text-gray-400"><?php echo date('d-m-Y H:i', strtotime($a['created_at'])); ?></div>
                            </div>
                            <form method="POST" action="nieuwsbeheer.php">
                                <?php echo csrf_field(); ?>
                                <button type="submit" name="toggle_status" value="<?php echo htmlspecialchars($a['id']); ?>" class="text-xs font-bold px-2 py-1 rounded-full <?php echo $a['status'] === 'published' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'; ?>">
                                    <?php echo $a['status'] === 'published' ? 'Gepubliceerd' : 'Concept'; ?>
                                </button>
                            </form>
                            <button type="button" data-edit-id="<?php echo htmlspecialchars($a['id']); ?>" class="js-edit-artikel text-slate-600 hover:text-slate-900 font-medium text-sm">Bewerk</button>
                            <button type="submit" form="delete-artikel-<?php echo htmlspecialchars($a['id']); ?>" class="text-red-500 hover:text-red-700 font-medium text-sm">Verwijder</button>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php foreach ($artikelen as $a): ?>
                    <form method="POST" action="nieuwsbeheer.php" id="delete-artikel-<?php echo htmlspecialchars($a['id']); ?>" onsubmit="return confirmSubmit(event, 'Weet je zeker dat je dit artikel wilt verwijderen?');" class="hidden">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="delete_artikel" value="<?php echo htmlspecialchars($a['id']); ?>">
                    </form>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    <div id="artikel-modal" class="fixed inset-0 z-50 hidden" aria-hidden="true">
        <div class="absolute inset-0 bg-slate-900/50" data-close-modal></div>
        <div class="relative flex min-h-full items-start justify-center p-4 md:p-10 pointer-events-none">
            <div class="pointer-events-auto w-full max-w-3xl bg-white rounded-xl shadow-xl border border-gray-200 flex flex-col max-h-[calc(100vh-2rem)] md:max-h-[calc(100vh-5rem)]" role="dialog" aria-modal="true" aria-labelledby="artikel-modal-title">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <h3 id="artikel-modal-title" class="text-lg font-bold">Nieuw artikel</h3>
                    <button type="button" data-close-modal class="text-gray-400 hover:text-gray-700 text-2xl leading-none" aria-label="Sluiten">&times;</button>
                </div>

                <form id="artikel-form" method="POST" action="nieuwsbeheer.php" enctype="multipart/form-data" class="flex flex-col min-h-0 flex-1">
                    <div class="px-6 py-5 overflow-y-auto flex-1">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="action" id="artikel-action" value="add_artikel">
                        <input type="hidden" name="id" id="artikel-id" value="">

                        <div class="mb-4">
                            <label for="artikel-title" class="block text-sm font-medium text-gray-700 mb-1">Titel</label>
                            <input type="text" name="title" id="artikel-title" required class="w-full border-gray-300 rounded-md border p-2 focus:border-pink-500 focus:ring-pink-500">
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Inhoud</label>
                            <textarea name="content" id="artikel-content"></textarea>
                            <p id="artikel-content-error" class="hidden text-sm text-red-600 mt-1">Vul de inhoud in.</p>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Afbeelding(en)</label>
                            <p class="text-xs text-gray-400 mb-2">De eerste afbeelding wordt gebruikt als hoofdafbeelding. Klik op &times; om een afbeelding te verwijderen.</p>

                            <div id="artikel-images" class="grid grid-cols-3 sm:grid-cols-4 gap-3 mb-3"></div>
                            <div id="artikel-keep-inputs"></div>

                            <label class="inline-flex items-center gap-2 cursor-pointer bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium py-2 px-4 rounded-lg transition-colors">
                                + Afbeeldingen toevoegen
                                <input type="file" id="artikel-file-picker" accept="image/jpeg,image/png,image/webp,image/gif" multiple class="hidden">
                            </label>
                            <input type="file" name="images[]" id="artikel-files" multiple class="hidden">
                        </div>

                        <div class="mb-2">
                            <label for="artikel-status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select name="status" id="artikel-status" class="w-full border-gray-300 rounded-md border p-2 bg-white">
                                <option value="published">Gepubliceerd</option>
                                <option value="draft">Concept (niet zichtbaar)</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 px-6 py-4 border-t border-gray-100 bg-gray-50 rounded-b-xl">
                        <button type="button" data-close-modal class="py-2 px-4 rounded-lg text-gray-600 hover:bg-gray-200 font-medium transition-colors">Annuleren</button>
                        <button type="submit" id="artikel-submit" class="bg-slate-900 text-white font-bold py-2 px-5 rounded-lg hover:bg-slate-800 transition-colors">Artikel plaatsen</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<script>
(function () {
    const ARTIKELEN = <?php echo json_encode($artikelen_js, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

    const modal       = document.getElementById('artikel-modal');
    const form        = document.getElementById('artikel-form');
    const titleEl     = document.getElementById('artikel-modal-title');
    const actionEl    = document.getElementById('artikel-action');
    const idEl        = document.getElementById('artikel-id');
    const titleInput  = document.getElementById('artikel-title');
    const statusEl    = document.getElementById('artikel-status');
    const submitBtn   = document.getElementById('artikel-submit');
    const imagesGrid  = document.getElementById('artikel-images');
    const keepInputs  = document.getElementById('artikel-keep-inputs');
    const picker      = document.getElementById('artikel-file-picker');
    const filesInput  = document.getElementById('artikel-files');
    const contentErr  = document.getElementById('artikel-content-error');

    let editor = null;
    let existingImages = []; // [{path, removed}]
    let newFiles = [];       // [{file, url}]

    ClassicEditor.create(document.getElementById('artikel-content'))
        .then(e => { editor = e; })
        .catch(err => console.error(err));

    function renderImages() {
        imagesGrid.innerHTML = '';
        keepInputs.innerHTML = '';

        existingImages.forEach((img, i) => {
            imagesGrid.appendChild(makeTile(img.path, img.removed, () => {
                img.removed = !img.removed;
                renderImages();
            }, img.removed ? 'Terugzetten' : 'Verwijderen'));

            if (!img.removed) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'keep_images[]';
                input.value = img.path;
                keepInputs.appendChild(input);
            }
        });

        newFiles.forEach((nf, i) => {
            const tile = makeTile(nf.url, false, () => {
                URL.revokeObjectURL(nf.url);
                newFiles.splice(i, 1);
                renderImages();
            }, 'Verwijderen');
            const badge = document.createElement('span');
            badge.className = 'absolute bottom-1 left-1 text-[10px] font-bold bg-pink-600 text-white px-1.5 py-0.5 rounded';
            badge.textContent = 'Nieuw';
            tile.appendChild(badge);
            imagesGrid.appendChild(tile);
        });

        imagesGrid.classList.toggle('hidden', existingImages.length + newFiles.length === 0);
    }

    function makeTile(src, removed, onToggle, label) {
        const tile = document.createElement('div');
        tile.className = 'relative aspect-square rounded-lg overflow-hidden border border-gray-200 bg-gray-50';

        const img = document.createElement('img');
        img.src = src;
        img.alt = '';
        img.className = 'w-full h-full object-cover' + (removed ? ' opacity-30 grayscale' : '');
        tile.appendChild(img);

        const btn = document.createElement('button');
        btn.type = 'button';
        btn.title = label;
        btn.setAttribute('aria-label', label);
        btn.className = 'absolute top-1 right-1 w-7 h-7 rounded-full text-sm font-bold shadow flex items-center justify-center ' +
            (removed ? 'bg-white text-slate-700 hover:bg-gray-100' : 'bg-red-600 text-white hover:bg-red-700');
        btn.innerHTML = removed ? '&#8634;' : '&times;';
        btn.addEventListener('click', onToggle);
        tile.appendChild(btn);

        if (removed) {
            const note = document.createElement('span');
            note.className = 'absolute inset-x-0 bottom-1 text-center text-[11px] font-semibold text-red-600';
            note.textContent = 'Wordt verwijderd';
            tile.appendChild(note);
        }
        return tile;
    }

    picker.addEventListener('change', () => {
        Array.from(picker.files).forEach(file => {
            if (!file.type.startsWith('image/')) return;
            newFiles.push({ file, url: URL.createObjectURL(file) });
        });
        picker.value = '';
        renderImages();
    });

    function resetNewFiles() {
        newFiles.forEach(nf => URL.revokeObjectURL(nf.url));
        newFiles = [];
    }

    window.openArtikelModal = function (id) {
        const artikel = id ? ARTIKELEN[id] : null;
        resetNewFiles();
        contentErr.classList.add('hidden');

        if (artikel) {
            titleEl.textContent = 'Artikel bewerken';
            submitBtn.textContent = 'Wijzigingen opslaan';
            actionEl.value = 'edit_artikel';
            idEl.value = artikel.id;
            titleInput.value = artikel.title;
            statusEl.value = artikel.status;
            existingImages = artikel.images.map(path => ({ path, removed: false }));
            if (editor) editor.setData(artikel.content || '');
        } else {
            titleEl.textContent = 'Nieuw artikel';
            submitBtn.textContent = 'Artikel plaatsen';
            actionEl.value = 'add_artikel';
            idEl.value = '';
            titleInput.value = '';
            statusEl.value = 'published';
            existingImages = [];
            if (editor) editor.setData('');
        }

        renderImages();
        modal.classList.remove('hidden');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('overflow-hidden');
        setTimeout(() => titleInput.focus(), 50);
    };

    function closeModal() {
        modal.classList.add('hidden');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('overflow-hidden');
        resetNewFiles();
    }

    modal.querySelectorAll('[data-close-modal]').forEach(el => el.addEventListener('click', closeModal));
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal();
    });

    document.querySelectorAll('.js-edit-artikel').forEach(btn => {
        btn.addEventListener('click', () => openArtikelModal(btn.dataset.editId));
    });

    form.addEventListener('submit', e => {
        if (editor) {
            editor.updateSourceElement();
            const text = editor.getData().replace(/<[^>]*>/g, '').replace(/&nbsp;/g, ' ').trim();
            if (text === '') {
                e.preventDefault();
                contentErr.classList.remove('hidden');
                editor.editing.view.focus();
                return;
            }
        }

        // Nieuwe bestanden in het echte upload-veld zetten
        const dt = new DataTransfer();
        newFiles.forEach(nf => dt.items.add(nf.file));
        filesInput.files = dt.files;

        submitBtn.disabled = true;
        submitBtn.classList.add('opacity-60');
    });
})();
</script>
</body>
</html>