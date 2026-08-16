<?php
require_once 'includes/init.php';
$website_id = require_website_context();

$q = trim($_GET['q'] ?? '');
$results = [];

if ($q !== '') {
    $like = '%' . $q . '%';

    $stmt = $pdo->prepare("SELECT section_key, content_text FROM site_content WHERE website_id = ? AND content_text LIKE ? LIMIT 10");
    $stmt->execute([$website_id, $like]);
    foreach ($stmt->fetchAll() as $row) {
        $results[] = ['type' => 'Tekst', 'title' => $row['section_key'], 'snippet' => mb_substr($row['content_text'], 0, 120), 'link' => 'websitebeheer.php'];
    }

    $stmt = $pdo->prepare("SELECT id, title, description FROM products WHERE website_id = ? AND (title LIKE ? OR description LIKE ?) LIMIT 10");
    $stmt->execute([$website_id, $like, $like]);
    foreach ($stmt->fetchAll() as $row) {
        $results[] = ['type' => 'Product', 'title' => $row['title'], 'snippet' => mb_substr((string)$row['description'], 0, 120), 'link' => 'productbeheer.php'];
    }

    $stmt = $pdo->prepare("SELECT id, question, answer FROM faqs WHERE website_id = ? AND (question LIKE ? OR answer LIKE ?) LIMIT 10");
    $stmt->execute([$website_id, $like, $like]);
    foreach ($stmt->fetchAll() as $row) {
        $results[] = ['type' => 'FAQ', 'title' => $row['question'], 'snippet' => mb_substr($row['answer'], 0, 120), 'link' => 'websitebeheer.php?tab=faq'];
    }

    $stmt = $pdo->prepare("SELECT id, title, description FROM portfolio WHERE website_id = ? AND (title LIKE ? OR description LIKE ?) LIMIT 10");
    $stmt->execute([$website_id, $like, $like]);
    foreach ($stmt->fetchAll() as $row) {
        $results[] = ['type' => 'Portfolio', 'title' => $row['title'], 'snippet' => mb_substr((string)$row['description'], 0, 120), 'link' => 'websitebeheer.php?tab=portfolio'];
    }

    $stmt = $pdo->prepare("SELECT id, name, subject, message FROM contact_messages WHERE website_id = ? AND (name LIKE ? OR subject LIKE ? OR message LIKE ?) LIMIT 10");
    $stmt->execute([$website_id, $like, $like, $like]);
    foreach ($stmt->fetchAll() as $row) {
        $results[] = ['type' => 'Bericht', 'title' => $row['name'] . ' — ' . ($row['subject'] ?: 'Geen onderwerp'), 'snippet' => mb_substr($row['message'], 0, 120), 'link' => 'contactbeheer.php'];
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <title>Zoeken - <?php echo htmlspecialchars(current_website()['company_name'] ?? 'Webius Portaal'); ?></title>
    <?php include 'includes/head.php'; ?>
</head>
<body class="bg-gray-100 font-sans pb-24">
    <?php include 'includes/header.php'; ?>
    <div class="max-w-3xl mx-auto px-4 mt-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Zoekresultaten</h1>
        <p class="text-gray-500 mb-6"><?php echo $q !== '' ? 'Resultaten voor "' . htmlspecialchars($q) . '"' : 'Vul een zoekterm in via de zoekbalk hierboven.'; ?></p>

        <?php if ($q !== '' && empty($results)): ?>
            <div class="bg-white p-6 rounded-xl border border-gray-200 text-gray-500 text-center">Niets gevonden voor deze zoekterm.</div>
        <?php endif; ?>

        <div class="space-y-3">
            <?php foreach ($results as $r): ?>
                <a href="<?php echo htmlspecialchars($r['link']); ?>" class="block bg-white p-4 rounded-xl border border-gray-200 hover:border-pink-300 hover:shadow-sm transition-all">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-xs font-bold uppercase tracking-wide text-pink-600 bg-pink-50 px-2 py-0.5 rounded-full"><?php echo htmlspecialchars($r['type']); ?></span>
                        <span class="font-bold text-gray-800"><?php echo htmlspecialchars($r['title']); ?></span>
                    </div>
                    <?php if ($r['snippet']): ?>
                        <p class="text-sm text-gray-500"><?php echo htmlspecialchars($r['snippet']); ?>&hellip;</p>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
