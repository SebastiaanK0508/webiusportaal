<?php
require_once 'includes/init.php';
$website_id = require_website_context();
require_module($website_id, 'nieuws');

// --- ARTIKEL TOEVOEGEN ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_artikel') {
    csrf_verify();
    $title = trim($_POST['title'] ?? '');
    $content = $_POST['content'] ?? '';
    $status = in_array($_POST['status'] ?? '', ['draft', 'published'], true) ? $_POST['status'] : 'published';

    if ($title === '' || trim(strip_tags($content)) === '') {
        header("Location: nieuwsbeheer.php?msg=invalid");
        exit;
    }

    $image_paths = [];
    if (!empty($_FILES['images']['name'][0])) {
        $count = count($_FILES['images']['name']);
        for ($i = 0; $i < $count; $i++) {
            if ($_FILES['images']['error'][$i] === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            $single = [
                'name' => $_FILES['images']['name'][$i],
                'type' => $_FILES['images']['type'][$i],
                'tmp_name' => $_FILES['images']['tmp_name'][$i],
                'error' => $_FILES['images']['error'][$i],
                'size' => $_FILES['images']['size'][$i],
            ];
            $_FILES['__single_image'] = $single;
            try {
                $path = save_uploaded_image('__single_image', $website_id);
                if ($path) {
                    $image_paths[] = $path;
                }
            } catch (UploadException $e) {
                header("Location: nieuwsbeheer.php?msg=upload_error");
                exit;
            }
        }
    }

    $new_id = $pdo->query('SELECT UUID()')->fetchColumn();
    $stmt = $pdo->prepare("INSERT INTO nieuws (id, website_id, title, content, image_paths, status) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$new_id, $website_id, $title, $content, $image_paths ? implode(',', $image_paths) : null, $status]);
    header("Location: nieuwsbeheer.php?msg=added");
    exit;
}

// --- ARTIKEL VERWIJDEREN ---
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

// --- STATUS WISSELEN (draft/published) ---
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
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <title>Nieuws Beheer - <?php echo htmlspecialchars(current_website()['company_name'] ?? 'Webius Portaal'); ?></title>
    <?php include 'includes/head.php'; ?>

<script src="https://cdn.ckeditor.com/ckeditor5/41.1.0/classic/ckeditor.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        document.querySelectorAll('.rich-editor').forEach(element => {
            ClassicEditor.create(element).catch(error => console.error(error));
        });
    });
</script>
</head>
<body class="bg-gray-50 font-sans pb-20 text-gray-800">
    <?php include 'includes/header.php'; ?>
    <div class="max-w-7xl p-8 mx-auto">
        <div class="flex flex-col md:flex-row justify-between items-center mb-8 bg-white p-6 rounded-xl shadow-sm border border-gray-200">
            <div>
                <h1 class="text-3xl font-extrabold text-slate-900">Nieuws Beheer</h1>
                <p class="text-gray-500 mt-1">Plaats nieuwsartikelen op de website.</p>
            </div>
            <a href="<?php echo htmlspecialchars(public_site_url('nieuws.php')); ?>" target="_blank" class="bg-pink-100 text-pink-700 font-bold py-2 px-6 rounded-full hover:bg-pink-200 transition-colors mt-4 md:mt-0">
                Bekijk publiek &rarr;
            </a>
        </div>

        <?php if (isset($_GET['msg'])): ?>
            <div class="mb-8 p-4 rounded-lg font-medium
                <?php echo (strpos($_GET['msg'], 'deleted') !== false || strpos($_GET['msg'], 'invalid') !== false || strpos($_GET['msg'], 'error') !== false) ? 'bg-red-100 text-red-800 border border-red-200' : 'bg-green-100 text-green-800 border border-green-200'; ?>">
                <?php
                    if ($_GET['msg'] === 'added') echo 'Artikel succesvol geplaatst!';
                    if ($_GET['msg'] === 'deleted') echo 'Artikel verwijderd!';
                    if ($_GET['msg'] === 'status_updated') echo 'Status bijgewerkt!';
                    if ($_GET['msg'] === 'invalid') echo 'Vul een titel en inhoud in.';
                    if ($_GET['msg'] === 'upload_error') echo 'Eén van de afbeeldingen kon niet worden geupload.';
                ?>
            </div>
        <?php endif; ?>

        <div class="grid lg:grid-cols-3 gap-8">
            <div class="lg:col-span-1 bg-white p-6 rounded-xl shadow-sm border border-gray-100 h-fit">
                <h3 class="text-lg font-bold mb-4">Nieuw Artikel</h3>
                <form method="POST" action="nieuwsbeheer.php" enctype="multipart/form-data">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="action" value="add_artikel">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Titel</label>
                        <input type="text" name="title" required class="w-full border-gray-300 rounded-md border p-2 focus:border-pink-500 focus:ring-pink-500">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Inhoud</label>
                        <textarea name="content" class="rich-editor w-full"></textarea>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Afbeelding(en)</label>
                        <input type="file" name="images[]" accept="image/jpeg,image/png,image/webp,image/gif" multiple class="w-full text-sm">
                    </div>
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" class="w-full border-gray-300 rounded-md border p-2 bg-white">
                            <option value="published">Gepubliceerd</option>
                            <option value="draft">Concept (niet zichtbaar)</option>
                        </select>
                    </div>
                    <button type="submit" class="w-full bg-slate-900 text-white font-bold py-2 px-4 rounded-lg hover:bg-slate-800 transition-colors">
                        Artikel Plaatsen
                    </button>
                </form>
            </div>

            <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                <h3 class="text-lg font-bold mb-4">Bestaande Artikelen (<?php echo count($artikelen); ?>)</h3>
                <?php if (empty($artikelen)): ?>
                    <p class="text-gray-500 italic">Nog geen artikelen geplaatst.</p>
                <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach ($artikelen as $a): $first_image = $a['image_paths'] ? explode(',', $a['image_paths'])[0] : null; ?>
                            <div class="flex items-center gap-4 border border-gray-200 rounded-lg p-3 hover:bg-gray-50">
                                <?php if ($first_image): ?>
                                    <img src="<?php echo htmlspecialchars($first_image); ?>" class="w-14 h-14 rounded object-cover border border-gray-200 flex-shrink-0">
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
                                <button type="submit" form="delete-artikel-<?php echo htmlspecialchars($a['id']); ?>" class="text-red-500 hover:text-red-700 font-medium text-sm">Verwijder</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php foreach ($artikelen as $a): ?>
                        <form method="POST" action="nieuwsbeheer.php" id="delete-artikel-<?php echo htmlspecialchars($a['id']); ?>" onsubmit="return confirm('Weet je zeker dat je dit artikel wilt verwijderen?');" class="hidden">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="delete_artikel" value="<?php echo htmlspecialchars($a['id']); ?>">
                        </form>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
