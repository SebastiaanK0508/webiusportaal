<?php
/**
 * Migreert de site-specifieke data van de oude, losse `debandijk`-database
 * naar de nieuwe multi-tenant `webiusportaal`-database (module-tabellen uit
 * 006_module_tables.sql). Draai dit pas nadat 005 en 006 zijn uitgevoerd en
 * nadat de vijf nieuwe beheerpagina's tegen de lege tabellen zijn getest.
 *
 * Gebruik:
 *   php migrate/007_migrate_debandijk_data.php <website_id>
 *
 * Niet idempotent — draai dit precies één keer per website_id. Het script
 * weigert te starten als er al gemigreerde assortiment-data staat voor de
 * opgegeven website_id.
 *
 * Wijzigt alleen de `webiusportaal`-database en kopieert bestanden naar
 * webiusportaal/uploads/{website_id}/legacy/... — de `debandijk`-database
 * en debandijk/beheer/afbeeldingen/ worden alleen gelezen, nooit gewijzigd.
 */

if (PHP_SAPI !== 'cli') {
    exit("Dit script moet via de command line gedraaid worden.\n");
}

$website_id = $argv[1] ?? null;
if (!$website_id || !preg_match('/^[0-9a-f-]{36}$/i', $website_id)) {
    exit("Gebruik: php migrate/007_migrate_debandijk_data.php <website_id>\n");
}

$host = 'localhost';
$user = 'webuser';
$pass = 'binck@guus2025';
$charset = 'utf8mb4';
$opts = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

$src = new PDO("mysql:host=$host;dbname=debandijk;charset=$charset", $user, $pass, $opts);
$dst = new PDO("mysql:host=$host;dbname=webiusportaal;charset=$charset", $user, $pass, $opts);

$webiusportaalRoot = dirname(__DIR__);
$debandijkRoot = dirname($webiusportaalRoot) . '/debandijk';
if (!is_dir($debandijkRoot)) {
    exit("Kan de debandijk-repo niet vinden op {$debandijkRoot} — pas \$debandijkRoot in dit script aan.\n");
}

function new_uuid(PDO $pdo): string
{
    return $pdo->query('SELECT UUID()')->fetchColumn();
}

// Kopieert een afbeelding uit debandijk/beheer/afbeeldingen/{subfolder}/{filename}
// naar webiusportaal/uploads/{website_id}/legacy/{subfolder}/{filename} en geeft
// het nieuwe relatieve pad terug (of null als er geen bestand is / het ontbreekt —
// bijv. de kolom-default 'default.webp' die nooit als los bestand bestond).
function copy_legacy_image(?string $filename, string $subfolder, string $websiteId, string $debandijkRoot, string $webiusportaalRoot): ?string
{
    if (empty($filename)) {
        return null;
    }
    $source = $debandijkRoot . '/beheer/afbeeldingen/' . $subfolder . '/' . $filename;
    if (!is_file($source)) {
        return null;
    }
    $destDir = $webiusportaalRoot . '/uploads/' . $websiteId . '/legacy/' . $subfolder;
    if (!is_dir($destDir)) {
        mkdir($destDir, 0755, true);
    }
    $dest = $destDir . '/' . $filename;
    if (!copy($source, $dest)) {
        return null;
    }
    return 'uploads/' . $websiteId . '/legacy/' . $subfolder . '/' . $filename;
}

$summary = [];

// --- Idempotentie-check ---
$check = $dst->prepare("SELECT COUNT(*) FROM assortiment_categorieen WHERE website_id = ?");
$check->execute([$website_id]);
if ((int)$check->fetchColumn() > 0) {
    exit("Er staat al gemigreerde assortiment-data voor website_id {$website_id}. Dit script is niet idempotent — er is hier niets meer te doen.\n");
}

// --- 1. assortiment_categories -> assortiment_categorieen ---
$catMap = [];
$rows = $src->query("SELECT * FROM assortiment_categories")->fetchAll();
$ins = $dst->prepare("INSERT INTO assortiment_categorieen (id, website_id, titel, beschrijving, afbeelding, badge_tekst, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)");
foreach ($rows as $r) {
    $newId = new_uuid($dst);
    $catMap[$r['id']] = $newId;
    $afbeelding = copy_legacy_image($r['afbeelding'], 'assortiment', $website_id, $debandijkRoot, $webiusportaalRoot);
    $ins->execute([$newId, $website_id, $r['titel'], $r['beschrijving'], $afbeelding, $r['badge_tekst'], (int)$r['sorteer_volgorde']]);
}
$summary['assortiment_categorieen'] = count($rows);

// --- 2. assortiment_merken -> assortiment_merken ---
$rows = $src->query("SELECT * FROM assortiment_merken ORDER BY category_id, id")->fetchAll();
$ins = $dst->prepare("INSERT INTO assortiment_merken (id, website_id, category_id, naam, logo_path, sort_order) VALUES (?, ?, ?, ?, ?, ?)");
$sortByCat = [];
$skipped_merken = 0;
foreach ($rows as $r) {
    if (!isset($catMap[$r['category_id']])) {
        $skipped_merken++;
        continue; // wees-rij: categorie bestond niet (mag niet voorkomen, maar geen data verliezen door een fatal error)
    }
    $newCatId = $catMap[$r['category_id']];
    $sortByCat[$newCatId] = ($sortByCat[$newCatId] ?? 0) + 1;
    $logoPath = copy_legacy_image($r['logo_path'], 'merken', $website_id, $debandijkRoot, $webiusportaalRoot);
    $newId = new_uuid($dst);
    $ins->execute([$newId, $website_id, $newCatId, $r['naam'], $logoPath, $sortByCat[$newCatId]]);
}
$summary['assortiment_merken'] = count($rows) - $skipped_merken;

// --- 3. cadeaukaarten -> cadeaukaarten ---
$rows = $src->query("SELECT * FROM cadeaukaarten ORDER BY id")->fetchAll();
$ins = $dst->prepare("INSERT INTO cadeaukaarten (id, website_id, kaart_naam, geschikte_winkels, tags, afbeelding, sort_order, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$i = 0;
foreach ($rows as $r) {
    $i++;
    $afbeelding = copy_legacy_image($r['afbeelding'], 'kaarten', $website_id, $debandijkRoot, $webiusportaalRoot);
    $newId = new_uuid($dst);
    $ins->execute([$newId, $website_id, $r['kaart_naam'], $r['geschikte_winkels'], $r['tags'], $afbeelding, $i, $r['created_at']]);
}
$summary['cadeaukaarten'] = count($rows);

// --- 4. news -> nieuws ---
$rows = $src->query("SELECT * FROM news ORDER BY created_at ASC")->fetchAll();
$ins = $dst->prepare("INSERT INTO nieuws (id, website_id, title, content, image_paths, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
foreach ($rows as $r) {
    $imagePath = copy_legacy_image($r['image_path'], 'nieuws', $website_id, $debandijkRoot, $webiusportaalRoot);
    $newId = new_uuid($dst);
    $ins->execute([$newId, $website_id, $r['title'], $r['content'], $imagePath, $r['status'] ?: 'published', $r['created_at']]);
}
$summary['nieuws'] = count($rows);

// --- 5. contact_messages -> contact_messages ---
$rows = $src->query("SELECT * FROM contact_messages ORDER BY created_at ASC")->fetchAll();
$ins = $dst->prepare("INSERT INTO contact_messages (id, website_id, name, email, phone, subject, message, status, is_read, created_at) VALUES (?, ?, ?, ?, NULL, ?, ?, ?, ?, ?)");
foreach ($rows as $r) {
    $newId = new_uuid($dst);
    $isRead = ($r['status'] !== 'nieuw') ? 1 : 0;
    $subject = $r['subject'] !== null ? substr($r['subject'], 0, 100) : null;
    $ins->execute([$newId, $website_id, $r['name'], $r['email'], $subject, $r['message'], $r['status'] ?: 'nieuw', $isRead, $r['created_at']]);
}
$summary['contact_messages'] = count($rows);

// --- 6. prijsvraag_instellingen (1 rij, id=1) -> prijsvraag_instellingen (website_id PK) ---
$row = $src->query("SELECT * FROM prijsvraag_instellingen WHERE id = 1")->fetch();
if ($row) {
    $afbeelding = copy_legacy_image($row['afbeelding_url'], 'prijsvraag', $website_id, $debandijkRoot, $webiusportaalRoot);
    $ins = $dst->prepare("INSERT INTO prijsvraag_instellingen (website_id, huidige_vraag, huidige_prijs, afbeelding_url, is_actief, toon_antwoorden, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $ins->execute([$website_id, $row['huidige_vraag'], $row['huidige_prijs'], $afbeelding, (int)$row['is_actief'], (int)$row['toon_antwoorden'], $row['laatst_bijgewerkt']]);
    $summary['prijsvraag_instellingen'] = 1;
} else {
    $summary['prijsvraag_instellingen'] = 0;
}

// --- 7. prijsvraag_inzendingen -> prijsvraag_inzendingen ---
$rows = $src->query("SELECT * FROM prijsvraag_inzendingen ORDER BY datum ASC")->fetchAll();
$ins = $dst->prepare("INSERT INTO prijsvraag_inzendingen (id, website_id, voornaam, achternaam, email, telefoon, antwoord, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
foreach ($rows as $r) {
    $newId = new_uuid($dst);
    $ins->execute([$newId, $website_id, $r['voornaam'], $r['achternaam'], $r['email'], $r['telefoon'], $r['antwoord'], $r['datum']]);
}
$summary['prijsvraag_inzendingen'] = count($rows);

// --- 8. opening_hours: seed met de tijden die nu hardcoded in index.php/contact.php staan ---
// day_of_week: 0=zondag .. 6=zaterdag (JS Date.getDay())
$openingHours = [
    ['day' => 0, 'opens' => null,     'closes' => null,     'closed' => 1, 'note' => null],
    ['day' => 1, 'opens' => '08:00', 'closes' => '18:00', 'closed' => 0, 'note' => null],
    ['day' => 2, 'opens' => '08:00', 'closes' => '18:00', 'closed' => 0, 'note' => null],
    ['day' => 3, 'opens' => '08:00', 'closes' => '18:00', 'closed' => 0, 'note' => null],
    ['day' => 4, 'opens' => '08:00', 'closes' => '18:00', 'closed' => 0, 'note' => null],
    ['day' => 5, 'opens' => '08:00', 'closes' => '20:00', 'closed' => 0, 'note' => 'Koopavond'],
    ['day' => 6, 'opens' => '08:00', 'closes' => '17:00', 'closed' => 0, 'note' => null],
];
$ins = $dst->prepare("INSERT INTO opening_hours (website_id, day_of_week, opens, closes, is_closed, note) VALUES (?, ?, ?, ?, ?, ?)");
foreach ($openingHours as $oh) {
    $ins->execute([$website_id, $oh['day'], $oh['opens'], $oh['closes'], $oh['closed'], $oh['note']]);
}
$summary['opening_hours'] = count($openingHours);

// --- 9. geschiedenis: de 7 tijdlijn-items uit debandijk/geschiedenis.php samengevoegd tot HTML ---
$geschiedenisContent = <<<HTML
<h3>1 april 1994 — De Start op Nummer 94</h3>
<p>Op deze dag opende René, samen met Gerri, de deur van hun winkel 'De Bandijk' aan de Grotestraat 94. De focus lag op tabak, snoep en kado's. René was 27 jaar en startte de winkel met een bijzondere motivatie: er was bij hem Retinitis pigmentosa ontdekt. Om niet afhankelijk te worden van een uitkering, besloot hij met volle gedrevenheid zelf aan de bak te gaan. Binnen een jaar bleek echter dat het pand moest wijken voor de bouw van het nieuwe gemeentehuis en politiebureau.</p>
<img src="historie/94.jpg" alt="1994">

<h3>1995 — Grotestraat 67</h3>
<p>Er werd snel een nieuwe plek gevonden op nummer 67. Ondanks de beperkte oppervlakte van 35m², groeide de klantenkring snel. De Bandijk onderscheidde zich door een enorme diversiteit in tabaksproducten. Na 7 jaar meldde het postkantoor zich: zij zochten een ondernemer om hun diensten over te nemen. Dit vroeg om een groter pand en extra hulp. René Kamphuis (schoonzoon van Gerri) werd gevraagd om bij in de zaak te komen om deze nieuwe uitdaging aan te gaan.</p>
<img src="historie/67.jpg" alt="1995">

<h3>28 nov 2002 — De stap naar Primera</h3>
<p>Aan de Grotestraat 135 openden René V. en René K. de deuren van hun nieuwe gemakswinkel met postkantoor. Er werd gekozen voor een samenwerking met de Primera-keten. Gerri bleef werkzaam in de zaak, nu ondersteund door dochter Cindy en nieuwe personeelsleden Ine en Geja. Niet veel later kwam ook de eerste zaterdagkracht Daniëlle het team versterken. Primera De Bandijk was hiermee officieel een feit op de huidige locatie.</p>
<img src="historie/vooraanzicht_2002.jpg" alt="2002">

<h3>2012 — Post &amp; Bankzaken</h3>
<p>Toen Postkantoren BV werd opgeheven, veranderde het postgedeelte in TPGpost en werden GIRO-klanten ondergebracht bij de ING Bank. Er werd een speciaal ING-servicepunt ingericht en de postzaken verhuisden naar de centrale winkelbalie. Dit zorgde voor een nieuwe dynamiek in de winkel, waarbij klanten voor verschillende diensten gezellig samen in de rij stonden te wachten.</p>

<h3>2014 — Grote Verbouwing</h3>
<p>Twee jaar na de kleine ingrepen was het tijd voor een grote verandering. De winkel ging een week dicht voor een complete metamorfose: een grotere balie, een extra kassa en een gloednieuwe vloer. Op 31 augustus werd de heropening groots gevierd met alle genodigden en klanten, waarmee de winkel weer helemaal klaar was voor de toekomst.</p>

<h3>2018 — Overdracht</h3>
<p>Door de toenemende drukte en zijn verslechterende zicht, besloot René Versteegen na jaren van trouwe dienst zijn plek in de VOF over te dragen. Sinds augustus 2018 zijn René en Cindy de eigenaren van Primera De Bandijk. Inmiddels helpt ook hun oudste zoon Bas regelmatig mee, waardoor de familiekracht in de winkel gewaarborgd blijft.</p>

<h3>2023 - Heden — Nieuwe Mogelijkheden</h3>
<p>Na het beëindigen van de samenwerking met de ING kreeg de winkel opnieuw een functionele update. De hoek van het oude servicepunt werd volledig verbouwd en getransformeerd tot een moderne ruimte voor het maken van pasfoto's. Hiermee blijft de winkel zich continu aanpassen aan de behoeften van de inwoners van Goor.</p>
<img src="historie/nieuwe_indeling.jpg" alt="Heden">
HTML;

$dst->prepare("INSERT INTO geschiedenis (website_id, title, content) VALUES (?, ?, ?)")
    ->execute([$website_id, 'Onze Geschiedenis', $geschiedenisContent]);
$summary['geschiedenis'] = 1;

// --- 10. users: Cindy en Rene migreren (Sebastiaan bestaat al in webiusportaal) ---
$existingUsernames = $dst->query("SELECT LOWER(username) FROM users")->fetchAll(PDO::FETCH_COLUMN);
$rows = $src->query("SELECT * FROM users")->fetchAll();
$migratedUsers = [];
$ins = $dst->prepare("INSERT INTO users (id, website_id, role, username, email, password_hash, is_active) VALUES (UUID(), ?, 'client', ?, ?, ?, 1)");
foreach ($rows as $r) {
    if (in_array(strtolower($r['username']), $existingUsernames, true)) {
        continue; // bestaat al (bijv. Sebastiaan)
    }
    $placeholderEmail = strtolower($r['username']) . '@primeradebandijk.nl.placeholder-update-me';
    $ins->execute([$website_id, $r['username'], $placeholderEmail, $r['password']]);
    $migratedUsers[] = ['username' => $r['username'], 'email' => $placeholderEmail];
}
$summary['users'] = count($migratedUsers);

// --- 11. Alle 5 modules inschakelen voor deze website ---
$dst->prepare("INSERT INTO website_modules (website_id, module_key, is_enabled) VALUES (?, 'assortiment', 1), (?, 'cadeaukaarten', 1), (?, 'nieuws', 1), (?, 'prijsvraag', 1), (?, 'geschiedenis', 1)
    ON DUPLICATE KEY UPDATE is_enabled = 1")
    ->execute([$website_id, $website_id, $website_id, $website_id, $website_id]);

// --- Samenvatting ---
echo "Migratie voltooid voor website_id {$website_id}:\n";
foreach ($summary as $table => $count) {
    echo "  - {$table}: {$count} rij(en)\n";
}
if ($skipped_merken > 0) {
    echo "  Let op: {$skipped_merken} merk(en) overgeslagen (categorie ontbrak).\n";
}
if (!empty($migratedUsers)) {
    echo "\nLET OP — nieuwe gebruikers met een PLACEHOLDER e-mailadres:\n";
    foreach ($migratedUsers as $u) {
        echo "  - {$u['username']}: {$u['email']}\n";
    }
    echo "Deze moeten via profielbeheer.php worden bijgewerkt naar een echt e-mailadres\n";
    echo "(nodig voor de wachtwoord-vergeten-flow).\n";
}
echo "\nVergeet niet de handmatige stap uit migrate/README.md uit te voeren:\n";
echo "  cd debandijk && ln -s ../webiusportaal/uploads uploads\n";
