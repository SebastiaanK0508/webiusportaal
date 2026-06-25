<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['user_uuid'])) {
    header('Location: login.php');
    exit();
}
include 'includes/init.php';
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'tab-voorwaarden';
$message = '';

if (isset($_POST['update_legal'])) {
    $type = $_POST['legal_type'];
    $title = $_POST['legal_title'];
    $content = $_POST['legal_content']; 
    
    $stmt = $pdo->prepare("UPDATE legals SET title = ?, content = ? WHERE type = ?");
    $stmt->execute([$title, $content, $type]);
    
    $message = "Document succesvol bijgewerkt!";
}

$voorwaarden = $pdo->query("SELECT * FROM legals WHERE type = 'voorwaarden'")->fetch();
$privacy = $pdo->query("SELECT * FROM legals WHERE type = 'privacy'")->fetch();
$cookies = $pdo->query("SELECT * FROM legals WHERE type = 'cookies'")->fetch();
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <title>Juridisch Beheer - <?php echo htmlspecialchars('hero_title')?> </title>
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
                <p class="text-gray-500 mt-1">Beheer hier je algemene voorwaarden, privacybeleid en cookiebeleid.</p>
            </div>
            <div class="flex gap-3">
                <a href="websitebeheer.php" class="text-gray-600 hover:text-gray-900 font-bold bg-white px-6 py-2 rounded-full shadow border border-gray-200 transition-all">&larr; Terug naar Websitebeheer</a>
            </div>
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