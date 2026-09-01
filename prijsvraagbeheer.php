<?php
require_once 'includes/init.php';
$website_id = require_website_context();
require_module($website_id, 'prijsvraag');

$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'instellingen';
$message = '';

// --- INSTELLINGEN OPSLAAN ---
if (isset($_POST['update_instellingen'])) {
    csrf_verify();
    $huidige_vraag = trim($_POST['huidige_vraag'] ?? '');
    $huidige_prijs = trim($_POST['huidige_prijs'] ?? '');
    $is_actief = isset($_POST['is_actief']) ? 1 : 0;
    $toon_antwoorden = isset($_POST['toon_antwoorden']) ? 1 : 0;

    try {
        $nieuwe_afbeelding = save_uploaded_image('afbeelding', $website_id);
    } catch (UploadException $e) {
        $nieuwe_afbeelding = null;
    }

    $stmt = $pdo->prepare("SELECT afbeelding_url FROM prijsvraag_instellingen WHERE website_id = ?");
    $stmt->execute([$website_id]);
    $bestaat = $stmt->fetchColumn();

    if ($bestaat !== false) {
        if ($nieuwe_afbeelding !== null) {
            delete_uploaded_file($bestaat ?: null);
            $pdo->prepare("UPDATE prijsvraag_instellingen SET afbeelding_url = ? WHERE website_id = ?")->execute([$nieuwe_afbeelding, $website_id]);
        }
        $pdo->prepare("UPDATE prijsvraag_instellingen SET huidige_vraag = ?, huidige_prijs = ?, is_actief = ?, toon_antwoorden = ? WHERE website_id = ?")
            ->execute([$huidige_vraag, $huidige_prijs, $is_actief, $toon_antwoorden, $website_id]);
    } else {
        $pdo->prepare("INSERT INTO prijsvraag_instellingen (website_id, huidige_vraag, huidige_prijs, afbeelding_url, is_actief, toon_antwoorden) VALUES (?, ?, ?, ?, ?, ?)")
            ->execute([$website_id, $huidige_vraag, $huidige_prijs, $nieuwe_afbeelding, $is_actief, $toon_antwoorden]);
    }

    $message = "Prijsvraag-instellingen bijgewerkt!";
    $active_tab = 'instellingen';
}

// --- INZENDING VERWIJDEREN ---
if (isset($_POST['delete_inzending'])) {
    csrf_verify();
    $pdo->prepare("DELETE FROM prijsvraag_inzendingen WHERE id = ? AND website_id = ?")->execute([$_POST['delete_inzending'], $website_id]);
    $message = "Inzending verwijderd.";
    $active_tab = 'inzendingen';
}

$stmt = $pdo->prepare("SELECT * FROM prijsvraag_instellingen WHERE website_id = ?");
$stmt->execute([$website_id]);
$instellingen = $stmt->fetch();

$stmt = $pdo->prepare("SELECT * FROM prijsvraag_inzendingen WHERE website_id = ? ORDER BY created_at DESC");
$stmt->execute([$website_id]);
$inzendingen = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <title>Prijsvraag Beheer - <?php echo htmlspecialchars(current_website()['company_name'] ?? 'Webius Portaal'); ?></title>
    <?php include 'includes/head.php'; ?>
</head>
<body class="bg-gray-100 font-sans pb-24">
    <?php include 'includes/header.php'; ?>

    <div class="max-w-6xl mx-auto px-4 mt-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
            <h1 class="text-3xl font-bold text-gray-800">Prijsvraag Beheer</h1>
            <a href="<?php echo htmlspecialchars(public_site_url('prijsvraag.php')); ?>" target="_blank" class="text-pink-600 hover:text-white font-bold bg-white hover:bg-pink-600 px-6 py-2 rounded-full shadow border border-pink-100 transition-all">Bekijk pagina &rarr;</a>
        </div>

        <?php if ($message): ?>
            <script>showToast(<?php echo json_encode($message); ?>, 'success');</script>
        <?php endif; ?>

        <div class="border-b border-gray-200 mb-6 bg-white rounded-t-xl shadow-sm overflow-x-auto">
            <nav class="-mb-px flex space-x-6 px-4">
                <?php
                $tabs = ['instellingen' => 'Instellingen', 'inzendingen' => 'Inzendingen'];
                foreach ($tabs as $key => $label):
                    $is_active = ($active_tab === $key);
                    $active_class = $is_active ? 'border-pink-600 text-pink-600 font-bold' : 'border-transparent text-gray-500 hover:text-gray-700 font-medium';
                ?>
                <a href="prijsvraagbeheer.php?tab=<?php echo $key; ?>" class="whitespace-nowrap py-4 px-1 border-b-2 text-sm <?php echo $active_class; ?>">
                    <?php echo $label; ?>
                    <?php if ($key === 'inzendingen' && count($inzendingen) > 0): ?>
                        <span class="bg-pink-100 text-pink-700 py-0.5 px-2 rounded-full text-xs ml-2"><?php echo count($inzendingen); ?></span>
                    <?php endif; ?>
                </a>
                <?php endforeach; ?>
            </nav>
        </div>

        <!-- ================= TAB 1: INSTELLINGEN ================= -->
        <div id="instellingen" class="<?php echo $active_tab !== 'instellingen' ? 'hidden' : ''; ?> bg-white p-6 rounded-b-xl shadow-md border border-gray-200 border-t-0 mb-6">
            <form method="POST" action="prijsvraagbeheer.php?tab=instellingen" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <div class="flex justify-between items-center mb-6 border-b pb-4">
                    <h2 class="text-2xl font-bold text-gray-800">Huidige Prijsvraag</h2>
                    <button type="submit" name="update_instellingen" class="bg-pink-600 text-white font-bold py-2 px-6 rounded-lg hover:bg-pink-700 shadow-sm transition-colors">Opslaan</button>
                </div>
                <div class="mb-4">
                    <label class="font-bold text-gray-800 text-sm block mb-2">Vraag</label>
                    <textarea name="huidige_vraag" rows="4" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-pink-400 focus:border-pink-400 text-sm transition-all"><?php echo htmlspecialchars($instellingen['huidige_vraag'] ?? ''); ?></textarea>
                </div>
                <div class="mb-4">
                    <label class="font-bold text-gray-800 text-sm block mb-2">Prijs</label>
                    <input type="text" name="huidige_prijs" value="<?php echo htmlspecialchars($instellingen['huidige_prijs'] ?? ''); ?>" placeholder="bijv. Heel Koningsdaglot Staatsloterij t.w.v. 20,-" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-pink-400 focus:border-pink-400 text-sm transition-all">
                </div>
                <div class="mb-4">
                    <label class="font-bold text-gray-800 text-sm block mb-2">Afbeelding</label>
                    <?php if (!empty($instellingen['afbeelding_url'])): ?>
                        <img src="<?php echo htmlspecialchars($instellingen['afbeelding_url']); ?>" class="w-24 h-24 rounded-lg object-cover border border-gray-200 mb-2">
                    <?php endif; ?>
                    <input type="file" name="afbeelding" accept="image/jpeg,image/png,image/webp,image/gif" class="w-full text-sm">
                </div>
                <div class="flex flex-col sm:flex-row gap-6 mt-6">
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_actief" value="1" <?php echo !empty($instellingen['is_actief']) ? 'checked' : ''; ?> class="rounded border-gray-300 text-pink-600 focus:ring-pink-500">
                        <span class="text-sm font-medium text-gray-700">Prijsvraag actief (zichtbaar op de website)</span>
                    </label>
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="toon_antwoorden" value="1" <?php echo !empty($instellingen['toon_antwoorden']) ? 'checked' : ''; ?> class="rounded border-gray-300 text-pink-600 focus:ring-pink-500">
                        <span class="text-sm font-medium text-gray-700">Toon aantal inzendingen op de website</span>
                    </label>
                </div>
            </form>
        </div>

        <!-- ================= TAB 2: INZENDINGEN ================= -->
        <div id="inzendingen" class="<?php echo $active_tab !== 'inzendingen' ? 'hidden' : ''; ?> bg-white p-6 rounded-b-xl shadow-md border border-gray-200 border-t-0 mb-6">
            <?php if (empty($inzendingen)): ?>
                <div class="text-center py-12">
                    <div class="text-6xl mb-4">🎟️</div>
                    <h3 class="text-xl font-bold text-gray-800">Geen inzendingen</h3>
                    <p class="text-gray-500 mt-2">Er zijn nog geen inzendingen voor de huidige prijsvraag.</p>
                </div>
            <?php else: ?>
                <div class="flex flex-col lg:flex-row gap-3 mb-4">
                    <input
                        type="text"
                        id="searchInput"
                        oninput="filterInzendingen()"
                        placeholder="Zoek op naam, e-mail of antwoord..."
                        class="flex-grow px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-pink-400 focus:border-pink-400 text-sm transition-all"
                    >
                    <input
                        type="text"
                        id="answerFilter"
                        oninput="filterInzendingen()"
                        placeholder="Juiste antwoord (exact)..."
                        class="lg:w-72 px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-pink-400 focus:border-pink-400 text-sm transition-all"
                    >
                    <button type="button" onclick="pickWinner()" class="shrink-0 bg-pink-600 text-white font-bold px-6 py-2.5 rounded-lg hover:bg-pink-700 shadow-sm transition-colors">🏆 Winnaar trekken</button>
                </div>
                <p id="eligibleCount" class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3"></p>

                <div class="overflow-x-auto border rounded-lg border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Naam</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Contact</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Antwoord</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Datum</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Actie</th>
                            </tr>
                        </thead>
                        <tbody id="inzendingenBody" class="divide-y divide-gray-200 bg-white">
                            <?php foreach ($inzendingen as $i): ?>
                                <tr class="inzending-row hover:bg-gray-50"
                                    data-search="<?php echo strtolower(htmlspecialchars($i['voornaam'] . ' ' . $i['achternaam'] . ' ' . $i['email'] . ' ' . $i['antwoord'])); ?>"
                                    data-antwoord="<?php echo strtolower(trim(htmlspecialchars($i['antwoord']))); ?>"
                                    data-antwoord-origineel="<?php echo htmlspecialchars($i['antwoord']); ?>"
                                    data-naam="<?php echo htmlspecialchars($i['voornaam'] . ' ' . $i['achternaam']); ?>"
                                    data-email="<?php echo htmlspecialchars($i['email']); ?>">
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900"><?php echo htmlspecialchars($i['voornaam'] . ' ' . $i['achternaam']); ?></td>
                                    <td class="px-4 py-3 text-sm text-gray-600">
                                        <div><?php echo htmlspecialchars($i['email']); ?></div>
                                        <?php if (!empty($i['telefoon'])): ?><div class="text-xs text-gray-400"><?php echo htmlspecialchars($i['telefoon']); ?></div><?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600"><?php echo htmlspecialchars($i['antwoord']); ?></td>
                                    <td class="px-4 py-3 text-xs text-gray-400 font-mono"><?php echo date('d-m-Y H:i', strtotime($i['created_at'])); ?></td>
                                    <td class="px-4 py-3 text-right">
                                        <form method="POST" action="prijsvraagbeheer.php?tab=inzendingen" onsubmit="return confirmSubmit(event, 'Deze inzending definitief verwijderen?');" class="inline">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="delete_inzending" value="<?php echo htmlspecialchars($i['id']); ?>">
                                            <button type="submit" class="text-red-500 hover:text-red-700 font-medium text-sm">Verwijder</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <div id="winnerModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeWinnerModal()"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden transform transition-all">
                <div class="h-2 w-full bg-gradient-to-r from-pink-500 via-yellow-400 to-pink-500"></div>
                <div class="p-8 text-center">
                    <div class="text-6xl mb-4">🏆</div>
                    <p class="text-xs font-bold uppercase tracking-widest text-pink-600 mb-1">De winnaar is</p>
                    <h3 id="winnerName" class="text-2xl font-extrabold text-gray-800 mb-1"></h3>
                    <p id="winnerEmail" class="text-sm text-gray-500 mb-1"></p>
                    <p id="winnerAnswer" class="text-sm text-gray-400 italic mb-4"></p>
                    <p id="winnerPool" class="text-xs text-gray-400 mb-6"></p>
                    <button type="button" onclick="closeWinnerModal()" class="w-full bg-pink-600 text-white font-bold py-2.5 rounded-lg hover:bg-pink-700 shadow-sm transition-colors">Sluiten</button>
                </div>
            </div>
        </div>

        <script>
            function closeWinnerModal() {
                document.getElementById('winnerModal').classList.add('hidden');
            }

            function showWinnerModal(row, poolSize) {
                document.getElementById('winnerName').textContent = row.getAttribute('data-naam');
                document.getElementById('winnerEmail').textContent = row.getAttribute('data-email');
                document.getElementById('winnerAnswer').textContent = '"' + row.getAttribute('data-antwoord-origineel') + '"';
                document.getElementById('winnerPool').textContent = 'Getrokken uit ' + poolSize + ' inzending(en)';
                document.getElementById('winnerModal').classList.remove('hidden');
            }

            function filterInzendingen() {
                const search = document.getElementById('searchInput').value.toLowerCase().trim();
                const antwoord = document.getElementById('answerFilter').value.toLowerCase().trim();
                const rows = document.getElementsByClassName('inzending-row');
                let eligible = 0;
                for (const row of rows) {
                    const matchesSearch = row.getAttribute('data-search').includes(search);
                    const matchesAnswer = antwoord === '' || row.getAttribute('data-antwoord') === antwoord;
                    const visible = matchesSearch && matchesAnswer;
                    row.style.display = visible ? '' : 'none';
                    if (visible) eligible++;
                }
                document.getElementById('eligibleCount').textContent = antwoord === '' ? '' : eligible + ' inzending(en) met dit antwoord';
            }

            function pickWinner() {
                const antwoord = document.getElementById('answerFilter').value.trim();
                if (antwoord === '') {
                    showConfirmModal(
                        'Er is geen juist antwoord ingevuld, er wordt uit ALLE zichtbare inzendingen getrokken. Doorgaan?',
                        drawWinner,
                        { confirmLabel: 'Doorgaan', confirmClass: 'flex-1 py-2.5 bg-pink-600 text-white rounded-lg text-sm font-bold hover:bg-pink-700 transition shadow' }
                    );
                    return;
                }
                drawWinner();
            }

            function drawWinner() {
                const kandidaten = Array.from(document.getElementsByClassName('inzending-row')).filter(row => row.style.display !== 'none');
                if (kandidaten.length === 0) {
                    showToast('Geen inzendingen gevonden met dit antwoord.', 'error');
                    return;
                }

                kandidaten.forEach(row => row.classList.remove('bg-yellow-100'));
                let cycles = 0;
                const maxCycles = 20;
                const interval = setInterval(() => {
                    kandidaten.forEach(row => row.classList.remove('bg-yellow-100'));
                    const randomRow = kandidaten[Math.floor(Math.random() * kandidaten.length)];
                    randomRow.classList.add('bg-yellow-100');
                    randomRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    cycles++;
                    if (cycles > maxCycles) {
                        clearInterval(interval);
                        const winnaar = kandidaten[Math.floor(Math.random() * kandidaten.length)];
                        kandidaten.forEach(row => row.classList.remove('bg-yellow-100'));
                        winnaar.classList.add('bg-yellow-100');
                        winnaar.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        setTimeout(() => {
                            showWinnerModal(winnaar, kandidaten.length);
                        }, 300);
                    }
                }, 100);
            }
        </script>
    </div>
</body>
</html>
