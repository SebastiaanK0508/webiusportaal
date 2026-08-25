<?php
require_once 'includes/init.php';
$website_id = require_website_context();
require_module($website_id, 'geschiedenis');

$message = '';

if (isset($_POST['update_geschiedenis'])) {
    csrf_verify();
    $title = trim($_POST['title'] ?? '') ?: 'Onze Geschiedenis';
    $content = $_POST['content'] ?? '';

    $stmt = $pdo->prepare("SELECT website_id FROM geschiedenis WHERE website_id = ?");
    $stmt->execute([$website_id]);
    if ($stmt->fetchColumn()) {
        $pdo->prepare("UPDATE geschiedenis SET title = ?, content = ? WHERE website_id = ?")->execute([$title, $content, $website_id]);
    } else {
        $pdo->prepare("INSERT INTO geschiedenis (website_id, title, content) VALUES (?, ?, ?)")->execute([$website_id, $title, $content]);
    }
    $message = "Geschiedenis-pagina succesvol bijgewerkt!";
}

$stmt = $pdo->prepare("SELECT * FROM geschiedenis WHERE website_id = ?");
$stmt->execute([$website_id]);
$geschiedenis = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <title>Geschiedenis Beheer - <?php echo htmlspecialchars(current_website()['company_name'] ?? 'Webius Portaal'); ?></title>
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
                <h1 class="text-3xl font-bold text-gray-800">Geschiedenis</h1>
                <p class="text-gray-500 mt-1">Beheer hier de tekst van de geschiedenis-pagina. Deze pagina is alleen zichtbaar op websites waar de module "Geschiedenis" is ingeschakeld.</p>
            </div>
            <a href="geschiedenis.php" target="_blank" class="text-pink-600 hover:text-white font-bold bg-white hover:bg-pink-600 px-6 py-2 rounded-full shadow border border-pink-100 transition-all">Bekijk pagina &rarr;</a>
        </div>

        <?php if ($message): ?>
            <div id="alert-message" class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 shadow-sm rounded-r-lg"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <div class="bg-white p-6 rounded-xl shadow-md border border-gray-200">
            <form method="POST" action="geschiedenisbeheer.php">
                <?php echo csrf_field(); ?>
                <div class="flex justify-between items-center mb-6 border-b pb-4">
                    <h2 class="text-2xl font-bold text-gray-800">Geschiedenis-pagina</h2>
                    <button type="submit" name="update_geschiedenis" class="bg-pink-600 text-white font-bold py-2 px-6 rounded-lg hover:bg-pink-700 shadow-sm transition-colors">Opslaan</button>
                </div>
                <div class="mb-4">
                    <label class="font-bold text-gray-800 text-sm block mb-2">Titel van de pagina</label>
                    <input type="text" name="title" value="<?php echo htmlspecialchars($geschiedenis['title'] ?? 'Onze Geschiedenis'); ?>" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-pink-400 focus:border-pink-400 text-sm transition-all">
                </div>
                <div class="mb-4">
                    <label class="font-bold text-gray-800 text-sm block mb-2">Inhoud</label>
                    <textarea name="content" class="rich-editor w-full"><?php echo htmlspecialchars($geschiedenis['content'] ?? ''); ?></textarea>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
