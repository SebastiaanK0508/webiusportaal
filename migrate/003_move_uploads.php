<?php
// Verplaatst bestaande bestanden in de platte uploads/-map naar
// uploads/{website_id}/ en herschrijft de bijbehorende paden in de database.
// Draai dit pas NADAT migrate/002_seed_first_website.sql is uitgevoerd.
//
// Gebruik (vanaf de projectroot):
//   php migrate/003_move_uploads.php <website_id>

if (PHP_SAPI !== 'cli') {
    exit("Dit script is alleen bedoeld om via de command line te draaien.\n");
}

require_once __DIR__ . '/../includes/db_config.php';

$website_id = $argv[1] ?? null;
if (!$website_id) {
    exit("Gebruik: php migrate/003_move_uploads.php <website_id>\n");
}

$check = $pdo->prepare("SELECT id, company_name FROM websites WHERE id = ?");
$check->execute([$website_id]);
$website = $check->fetch();
if (!$website) {
    exit("Geen website gevonden met id {$website_id}. Draai eerst migrate/002_seed_first_website.sql.\n");
}

echo "Bestanden verplaatsen voor: {$website['company_name']} ({$website_id})\n";

$target_dir = __DIR__ . '/../uploads/' . $website_id . '/';
if (!is_dir($target_dir)) {
    mkdir($target_dir, 0755, true);
}

$tables_with_paths = [
    ['table' => 'site_content', 'column' => 'content_text', 'where' => "website_id = ? AND type = 'image'", 'params' => [$website_id]],
    ['table' => 'portfolio', 'column' => 'image_path', 'where' => 'website_id = ?', 'params' => [$website_id]],
    ['table' => 'websites', 'column' => 'logo_path', 'where' => 'id = ?', 'params' => [$website_id]],
];

$moved = 0;
$skipped = 0;

foreach ($tables_with_paths as $spec) {
    $sql = "SELECT id, {$spec['column']} AS path FROM {$spec['table']} WHERE {$spec['where']} AND {$spec['column']} IS NOT NULL AND {$spec['column']} != ''";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($spec['params']);

    foreach ($stmt->fetchAll() as $row) {
        $old_path = $row['path'];

        if (strpos($old_path, 'uploads/' . $website_id . '/') === 0) {
            continue; // al verplaatst
        }

        $old_full = __DIR__ . '/../' . ltrim($old_path, '/');
        if (!is_file($old_full)) {
            echo "  Overslaan (bestand niet gevonden): {$old_path}\n";
            $skipped++;
            continue;
        }

        $filename = basename($old_path);
        $new_path = 'uploads/' . $website_id . '/' . $filename;
        $new_full = __DIR__ . '/../' . $new_path;

        if (rename($old_full, $new_full)) {
            $upd = $pdo->prepare("UPDATE {$spec['table']} SET {$spec['column']} = ? WHERE id = ?");
            $upd->execute([$new_path, $row['id']]);
            echo "  Verplaatst: {$old_path} -> {$new_path}\n";
            $moved++;
        } else {
            echo "  MISLUKT om te verplaatsen: {$old_path}\n";
            $skipped++;
        }
    }
}

echo "Klaar. {$moved} bestand(en) verplaatst, {$skipped} overgeslagen.\n";
