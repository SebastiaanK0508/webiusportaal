<?php
require_once 'includes/init.php';
$website_id = require_website_context();

$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'tab-voorwaarden';
$message = '';

if (isset($_POST['update_legal'])) {
    csrf_verify();
    $type = $_POST['legal_type'];
    $title = trim($_POST['legal_title'] ?? '');
    $content = $_POST['legal_content'] ?? '';

    $stmt = $pdo->prepare("SELECT id FROM legals WHERE website_id = ? AND type = ?");
    $stmt->execute([$website_id, $type]);
    if ($stmt->fetchColumn()) {
        $stmt = $pdo->prepare("UPDATE legals SET title = ?, content = ? WHERE website_id = ? AND type = ?");
        $stmt->execute([$title, $content, $website_id, $type]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO legals (id, website_id, type, title, content) VALUES (UUID(), ?, ?, ?, ?)");
        $stmt->execute([$website_id, $type, $title, $content]);
    }

    $message = "Document succesvol bijgewerkt!";
    $active_tab = 'tab-' . $type;
}

$voorwaarden_stmt = $pdo->prepare("SELECT * FROM legals WHERE website_id = ? AND type = 'voorwaarden'");
$voorwaarden_stmt->execute([$website_id]);
$voorwaarden = $voorwaarden_stmt->fetch();

$privacy_stmt = $pdo->prepare("SELECT * FROM legals WHERE website_id = ? AND type = 'privacy'");
$privacy_stmt->execute([$website_id]);
$privacy = $privacy_stmt->fetch();

$cookies_stmt = $pdo->prepare("SELECT * FROM legals WHERE website_id = ? AND type = 'cookies'");
$cookies_stmt->execute([$website_id]);
$cookies = $cookies_stmt->fetch();
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <title>Juridisch Beheer - <?php echo htmlspecialchars(current_website()['company_name'] ?? 'Webius Portaal'); ?></title>
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
<body class="bg-gray-100 font-sans pb-24">
    <?php include 'includes/header.php'; ?>

    <div class="max-w-6xl mx-auto px-4 mt-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Juridische Documenten</h1>
                <p class="text-gray-500 mt-1">Beheer hier de algemene voorwaarden, het privacybeleid en het cookiebeleid.</p>
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
                    'tab-voorwaarden' => 'Algemene Voorwaarden',
                    'tab-privacy' => 'Privacybeleid',
                    'tab-cookies' => 'Cookiebeleid'
                ];
                foreach($tabs as $key => $label):
                    $is_active = ($active_tab === $key);
                    $active_class = $is_active ? 'border-pink-600 text-pink-600 font-bold' : 'border-transparent text-gray-500 hover:text-gray-700 font-medium';
                ?>
                <a href="legalbeheer.php?tab=<?php echo $key; ?>" class="tab-btn whitespace-nowrap py-4 px-1 border-b-2 text-sm <?php echo $active_class; ?>">
                    <?php echo $label; ?>
                </a>
                <?php endforeach; ?>
            </nav>
        </div>

        <!-- ================= TAB 1: ALGEMENE VOORWAARDEN ================= -->
        <div id="tab-voorwaarden" class="tab-content bg-white p-6 rounded-b-xl shadow-md border border-gray-200 border-t-0 mb-6 <?php echo $active_tab !== 'tab-voorwaarden' ? 'hidden' : ''; ?>">
            <form method="POST" action="legalbeheer.php?tab=tab-voorwaarden">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="legal_type" value="voorwaarden">
                <div class="flex justify-between items-center mb-6 border-b pb-4">
                    <h2 class="text-2xl font-bold text-gray-800">Algemene Voorwaarden</h2>
                    <button type="submit" name="update_legal" class="bg-pink-600 text-white font-bold py-2 px-6 rounded-lg hover:bg-pink-700 shadow-sm transition-colors">Opslaan</button>
                </div>
                <div class="mb-4">
                    <label class="font-bold text-gray-800 text-sm block mb-2">Titel van de pagina</label>
                    <input type="text" name="legal_title" value="<?php echo htmlspecialchars($voorwaarden['title'] ?? ''); ?>" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-pink-400 focus:border-pink-400 text-sm transition-all">
                </div>
                <div class="mb-4">
                    <label class="font-bold text-gray-800 text-sm block mb-2">Inhoud</label>
                    <textarea name="legal_content" class="rich-editor w-full"><?php echo htmlspecialchars($voorwaarden['content'] ?? ''); ?></textarea>
                </div>
            </form>
        </div>

        <!-- ================= TAB 2: PRIVACYBELEID ================= -->
        <div id="tab-privacy" class="tab-content bg-white p-6 rounded-b-xl shadow-md border border-gray-200 border-t-0 mb-6 <?php echo $active_tab !== 'tab-privacy' ? 'hidden' : ''; ?>">
            <form method="POST" action="legalbeheer.php?tab=tab-privacy">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="legal_type" value="privacy">
                <div class="flex justify-between items-center mb-6 border-b pb-4">
                    <h2 class="text-2xl font-bold text-gray-800">Privacybeleid</h2>
                    <button type="submit" name="update_legal" class="bg-pink-600 text-white font-bold py-2 px-6 rounded-lg hover:bg-pink-700 shadow-sm transition-colors">Opslaan</button>
                </div>
                <div class="mb-4">
                    <label class="font-bold text-gray-800 text-sm block mb-2">Titel van de pagina</label>
                    <input type="text" name="legal_title" value="<?php echo htmlspecialchars($privacy['title'] ?? ''); ?>" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-pink-400 focus:border-pink-400 text-sm transition-all">
                </div>
                <div class="mb-4">
                    <label class="font-bold text-gray-800 text-sm block mb-2">Inhoud</label>
                    <textarea name="legal_content" class="rich-editor w-full"><?php echo htmlspecialchars($privacy['content'] ?? ''); ?></textarea>
                </div>
            </form>
        </div>

        <!-- ================= TAB 3: COOKIEBELEID ================= -->
        <div id="tab-cookies" class="tab-content bg-white p-6 rounded-b-xl shadow-md border border-gray-200 border-t-0 mb-6 <?php echo $active_tab !== 'tab-cookies' ? 'hidden' : ''; ?>">
            <form method="POST" action="legalbeheer.php?tab=tab-cookies">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="legal_type" value="cookies">
                <div class="flex justify-between items-center mb-6 border-b pb-4">
                    <h2 class="text-2xl font-bold text-gray-800">Cookiebeleid</h2>
                    <button type="submit" name="update_legal" class="bg-pink-600 text-white font-bold py-2 px-6 rounded-lg hover:bg-pink-700 shadow-sm transition-colors">Opslaan</button>
                </div>
                <div class="mb-4">
                    <label class="font-bold text-gray-800 text-sm block mb-2">Titel van de pagina</label>
                    <input type="text" name="legal_title" value="<?php echo htmlspecialchars($cookies['title'] ?? ''); ?>" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-pink-400 focus:border-pink-400 text-sm transition-all">
                </div>
                <div class="mb-4">
                    <label class="font-bold text-gray-800 text-sm block mb-2">Inhoud</label>
                    <textarea name="legal_content" class="rich-editor w-full"><?php echo htmlspecialchars($cookies['content'] ?? ''); ?></textarea>
                </div>
            </form>
        </div>
    </div>

</body>
</html>
