<?php
require_once 'includes/init.php';
$website_id = require_website_context();

$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'berichten';

if (isset($_POST['ajax_mark_read']) && isset($_POST['id'])) {
    csrf_verify();
    $stmt = $pdo->prepare("UPDATE contact_messages SET is_read = 1 WHERE id = ? AND website_id = ?");
    $stmt->execute([$_POST['id'], $website_id]);
    exit('success');
}

$message = '';
$msg_type = 'success';

if (isset($_POST['update_contact_content'])) {
    csrf_verify();
    $pdo->prepare("UPDATE site_content SET is_visible = 0 WHERE website_id = ? AND section_key LIKE 'contact\\_%'")->execute([$website_id]);
    if (isset($_POST['content'])) {
        foreach ($_POST['content'] as $key => $value) {
            $is_visible = isset($_POST['visible'][$key]) ? 1 : 0;
            $stmt = $pdo->prepare("UPDATE site_content SET content_text = ?, is_visible = ? WHERE website_id = ? AND section_key = ?");
            $stmt->execute([$value, $is_visible, $website_id, $key]);
        }
    }
    $message = "De teksten voor de contactpagina zijn succesvol bijgewerkt!";
    $msg_type = 'success';
}
if (isset($_POST['delete_message'])) {
    csrf_verify();
    $stmt = $pdo->prepare("DELETE FROM contact_messages WHERE id = ? AND website_id = ?");
    $stmt->execute([$_POST['delete_message'], $website_id]);
    $message = "Het bericht is definitief verwijderd.";
    $msg_type = 'delete';
    $active_tab = 'berichten';
}

$contact_content_stmt = $pdo->prepare("SELECT * FROM site_content WHERE website_id = ? AND section_key LIKE 'contact\\_%' ORDER BY section_key ASC");
$contact_content_stmt->execute([$website_id]);
$contact_content = $contact_content_stmt->fetchAll();

$contact_messages_stmt = $pdo->prepare("SELECT * FROM contact_messages WHERE website_id = ? ORDER BY created_at DESC");
$contact_messages_stmt->execute([$website_id]);
$contact_messages = $contact_messages_stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <title>Contactbeheer - <?php echo htmlspecialchars(current_website()['company_name'] ?? 'Webius Portaal'); ?></title>
    <?php include 'includes/head.php'; ?>
</head>
<body class="bg-gray-100 font-sans pb-24">
    <?php include 'includes/header.php'; ?>

    <div class="max-w-6xl mx-auto px-4 mt-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
            <h1 class="text-3xl font-bold text-gray-800">Contact Beheerpaneel</h1>
            <div class="flex gap-3">
                <a href="index.php" target="_blank" class="text-pink-600 hover:text-white font-bold bg-white hover:bg-pink-600 px-6 py-2 rounded-full shadow border border-pink-100 transition-all">Bekijk website &rarr;</a>
            </div>
        </div>

        <?php if ($message): ?>
            <?php
            if ($msg_type === 'delete') {
                $bg_class = 'bg-red-50 border-red-500 text-red-800';
                $icon = '<svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>';
            } else {
                $bg_class = 'bg-green-50 border-green-500 text-green-800';
                $icon = '<svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
            }
            ?>
            <div id="alert-message" class="<?php echo $bg_class; ?> border-l-4 p-4 mb-6 shadow-sm rounded-r-lg flex justify-between items-center transition-all duration-500 transform translate-y-0 opacity-100">
                <div class="flex items-center gap-3">
                    <?php echo $icon; ?>
                    <span class="font-medium"><?php echo htmlspecialchars($message); ?></span>
                </div>
                <button onclick="closeAlert()" class="rounded-lg p-1.5 focus:outline-none transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <script>
                function closeAlert() {
                    const alert = document.getElementById('alert-message');
                    if (alert) {
                        alert.classList.remove('translate-y-0', 'opacity-100');
                        alert.classList.add('-translate-y-2', 'opacity-0');
                        setTimeout(() => alert.style.display = 'none', 500);
                    }
                }
                setTimeout(closeAlert, 5000);
            </script>
        <?php endif; ?>

        <!-- Navigatie voor de Tabbladen -->
        <div class="border-b border-gray-200 mb-6 bg-white rounded-t-xl shadow-sm overflow-x-auto">
            <nav class="-mb-px flex space-x-6 px-4">
                <?php
                $tabs = ['berichten' => 'Ingekomen Berichten', 'teksten' => 'Teksten Contactpagina'];
                foreach($tabs as $key => $label):
                    $is_active = ($active_tab === $key);
                    $active_class = $is_active ? 'border-pink-600 text-pink-600 font-bold' : 'border-transparent text-gray-500 hover:text-gray-700 font-medium';
                ?>
                <a href="contactbeheer.php?tab=<?php echo $key; ?>" class="tab-btn whitespace-nowrap py-4 px-1 border-b-2 text-sm <?php echo $active_class; ?>">
                    <?php echo $label; ?>
                    <?php
                    if ($key === 'berichten') {
                        $unread_count = count(array_filter($contact_messages, function($m) { return $m['is_read'] == 0; }));
                        if($unread_count > 0):
                    ?>
                        <span id="tab-badge" class="bg-pink-100 text-pink-700 py-0.5 px-2 rounded-full text-xs ml-2"><?php echo $unread_count; ?> ongelezen</span>
                    <?php
                        endif;
                    }
                    ?>
                </a>
                <?php endforeach; ?>
            </nav>
        </div>

        <!-- ================= TAB 1: INGEKOMEN BERICHTEN ================= -->
        <div id="berichten" class="tab-content bg-white p-6 rounded-b-xl shadow-md border border-gray-200 border-t-0 mb-6 <?php echo $active_tab !== 'berichten' ? 'hidden' : ''; ?>">

            <?php if(empty($contact_messages)): ?>
                <div class="text-center py-12">
                    <div class="text-6xl mb-4">📭</div>
                    <h3 class="text-xl font-bold text-gray-800">Geen berichten</h3>
                    <p class="text-gray-500 mt-2">Er zijn nog geen contactformulieren ingevuld.</p>
                </div>
            <?php else: ?>
                <div class="grid gap-4">
                    <?php foreach ($contact_messages as $msg):
                        $is_unread = ($msg['is_read'] == 0);
                        $card_class = $is_unread ? 'border-l-4 border-pink-500 bg-white shadow-md' : 'border border-gray-200 bg-gray-50';
                    ?>
                        <div id="card-<?php echo htmlspecialchars($msg['id']); ?>" class="rounded-xl p-5 relative flex flex-col md:flex-row gap-4 items-start md:items-center transition-all duration-300 <?php echo $card_class; ?>">

                            <div class="flex items-center gap-4 w-full md:w-1/3">
                                <div class="w-12 h-12 rounded-full bg-pink-100 text-pink-600 flex items-center justify-center font-bold text-xl flex-shrink-0">
                                    <?php echo strtoupper(substr($msg['name'], 0, 1)); ?>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h3 class="font-bold text-gray-900 text-lg leading-tight"><?php echo htmlspecialchars($msg['name']); ?></h3>
                                        <?php if($is_unread): ?>
                                            <span id="badge-<?php echo htmlspecialchars($msg['id']); ?>" class="bg-pink-500 text-white text-[10px] uppercase font-bold px-1.5 py-0.5 rounded">Nieuw</span>
                                        <?php endif; ?>
                                    </div>
                                    <span class="text-xs text-gray-500 font-mono"><?php echo date('d-m-Y H:i', strtotime($msg['created_at'])); ?></span>
                                </div>
                            </div>

                            <div class="w-full md:w-1/2">
                                <div class="text-sm font-bold text-gray-800 mb-1">
                                    Onderwerp: <span class="font-normal text-gray-600"><?php echo htmlspecialchars($msg['subject'] ?: 'Geen onderwerp'); ?></span>
                                </div>
                                <div class="flex items-center gap-4 text-sm text-gray-500 mt-1">
                                    <div class="flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                        <?php echo htmlspecialchars($msg['email']); ?>
                                    </div>
                                    <?php if(!empty($msg['phone'])): ?>
                                        <div class="flex items-center gap-1.5 hidden sm:flex">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                            <?php echo htmlspecialchars($msg['phone']); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="w-full md:w-auto flex md:ml-auto gap-2 mt-2 md:mt-0">
                                <button onclick='openViewModal(<?php echo json_encode([
                                    "id" => $msg['id'],
                                    "name" => htmlspecialchars($msg['name']),
                                    "email" => htmlspecialchars($msg['email']),
                                    "phone" => htmlspecialchars($msg['phone']),
                                    "subject" => htmlspecialchars($msg['subject']),
                                    "message" => htmlspecialchars($msg['message']),
                                    "date" => date('d-m-Y H:i', strtotime($msg['created_at']))
                                ], JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'
                                class="bg-slate-800 hover:bg-slate-700 text-white text-sm font-bold px-4 py-2 rounded-lg transition-colors flex items-center gap-2">
                                    Lees bericht
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                </button>

                                <button onclick="confirmDelete('<?php echo htmlspecialchars($msg['id']); ?>', '<?php echo htmlspecialchars(addslashes($msg['name'])); ?>')" class="text-gray-400 hover:text-red-600 bg-white border border-gray-200 hover:border-red-200 hover:bg-red-50 p-2 rounded-lg transition-colors" title="Verwijderen">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </div>

                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- ================= TAB 2: TEKSTEN CONTACTPAGINA ================= -->
        <div id="teksten" class="tab-content bg-white p-6 rounded-b-xl shadow-md border border-gray-200 border-t-0 mb-6 <?php echo $active_tab !== 'teksten' ? 'hidden' : ''; ?>">
            <form method="POST" action="contactbeheer.php?tab=teksten">
                <?php echo csrf_field(); ?>
                <div class="flex justify-between items-center mb-8 border-b pb-4">
                    <h2 class="text-2xl font-bold text-gray-800">Teksten Contactpagina Aanpassen</h2>
                    <button type="submit" name="update_contact_content" class="bg-pink-600 text-white font-bold py-2 px-6 rounded-lg hover:bg-pink-700 shadow-sm transition-colors hidden md:block">Alles Opslaan</button>
                </div>
                <?php
                $contact_labels = [
                    'contact_hero_title' => 'Grote Titel (Bovenin)',
                    'contact_hero_text'  => 'Introductie tekst (onder titel)',
                    'contact_info_title' => 'Titel Contactgegevens (Linkerkant)',
                    'contact_info_text'  => 'Tekst boven de contactgegevens',
                    'contact_telefoon'   => 'Telefoonnummer',
                    'contact_email'      => 'E-mailadres'
                ];
                ?>
                <div class="grid md:grid-cols-2 gap-6">
                    <?php foreach ($contact_content as $item): ?>
                        <?php
                            $key = $item['section_key'];
                            $friendly_name = $contact_labels[$key] ?? (!empty($item['label']) ? $item['label'] : ucfirst(str_replace('_', ' ', $key)));
                        ?>
                        <div class="bg-gray-50 p-5 rounded-xl border border-gray-200 shadow-sm relative group">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <label class="font-bold text-gray-800 text-sm block"><?php echo htmlspecialchars($friendly_name); ?></label>
                                    <span class="text-[10px] text-gray-400 font-mono mt-0.5 block"><?php echo htmlspecialchars($key); ?></span>
                                </div>
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="visible[<?php echo htmlspecialchars($key); ?>]" value="1" <?php echo $item['is_visible'] ? 'checked' : ''; ?> class="sr-only peer">
                                    <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-pink-500"></div>
                                    <span class="ms-2 text-xs font-medium text-gray-500">Zichtbaar</span>
                                </label>
                            </div>

                            <?php if (strpos($key, 'title') !== false): ?>
                                <input type="text" name="content[<?php echo htmlspecialchars($key); ?>]" value="<?php echo htmlspecialchars($item['content_text']); ?>" class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-400 focus:border-pink-400 text-sm transition-all shadow-inner">
                            <?php else: ?>
                                <textarea name="content[<?php echo htmlspecialchars($key); ?>]" rows="4" class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-400 focus:border-pink-400 text-sm transition-all shadow-inner"><?php echo htmlspecialchars($item['content_text']); ?></textarea>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="mt-8 sticky bottom-6 z-10 flex justify-end">
                    <button type="submit" name="update_contact_content" class="bg-pink-600 text-white font-bold py-3 px-8 rounded-full hover:bg-pink-700 shadow-xl shadow-pink-600/30 transition-all transform hover:-translate-y-1 w-full md:w-auto flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                        Wijzigingen Opslaan
                    </button>
                </div>
            </form>
        </div>

        <!-- ================= MODAL: BERICHT BEKIJKEN ================= -->
        <div id="viewModal" class="fixed inset-0 z-50 hidden bg-gray-900 bg-opacity-60 overflow-y-auto h-full w-full flex items-center justify-center backdrop-blur-sm transition-opacity">
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl mx-4 transform scale-95 opacity-0 transition-all duration-300 flex flex-col max-h-[90vh]" id="viewModalContent">

                <div class="flex justify-between items-center p-6 border-b border-gray-100">
                    <div class="flex items-center gap-4">
                        <div id="view-avatar" class="w-12 h-12 rounded-full bg-pink-100 text-pink-600 flex items-center justify-center font-bold text-xl"></div>
                        <div>
                            <h3 id="view-name" class="font-bold text-gray-900 text-xl">Naam</h3>
                            <span id="view-date" class="text-xs text-gray-500 font-mono">Datum</span>
                        </div>
                    </div>
                    <button onclick="closeViewModal()" class="text-gray-400 hover:text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-full p-2 focus:outline-none transition-colors">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="p-6 overflow-y-auto">
                    <div class="flex flex-wrap gap-4 mb-6 p-4 bg-gray-50 rounded-xl border border-gray-200 text-sm">
                        <a id="view-email" href="#" class="flex items-center gap-2 text-gray-700 hover:text-pink-600 font-medium transition-colors">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            <span>email@adres.nl</span>
                        </a>
                        <a id="view-phone" href="#" class="flex items-center gap-2 text-gray-700 hover:text-pink-600 font-medium transition-colors">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                            <span>0612345678</span>
                        </a>
                    </div>

                    <div class="mb-4">
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Onderwerp</h4>
                        <p id="view-subject" class="font-bold text-gray-900 text-lg">Geen onderwerp</p>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Bericht</h4>
                        <div id="view-text" class="bg-white p-5 rounded-xl border border-gray-100 shadow-inner text-gray-700 leading-relaxed whitespace-pre-wrap">
                            Hier komt de tekst...
                        </div>
                    </div>
                </div>

                <div class="p-6 border-t border-gray-100 bg-gray-50 rounded-b-2xl flex justify-end gap-3">
                    <button onclick="closeViewModal()" class="px-5 py-2.5 text-gray-600 font-bold hover:bg-gray-200 rounded-lg transition-colors">Sluiten</button>
                    <a id="view-reply-btn" href="#" class="inline-flex items-center gap-2 bg-pink-600 hover:bg-pink-700 text-white font-bold px-6 py-2.5 rounded-lg shadow-md shadow-pink-600/30 transition-all transform hover:-translate-y-0.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
                        Beantwoorden
                    </a>
                </div>
            </div>
        </div>

        <!-- ================= VERWIJDER MODAL ================= -->
        <div id="deleteModal" class="fixed inset-0 z-50 hidden bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center backdrop-blur-sm transition-opacity">
            <div class="relative bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full mx-4 transform scale-95 opacity-0 transition-all duration-300" id="deleteModalContent">
                <div class="absolute top-0 right-0 pt-4 pr-4">
                    <button type="button" onclick="closeDeleteModal()" class="text-gray-400 hover:text-gray-600 focus:outline-none">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="text-center">
                    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-6">
                        <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">Bericht verwijderen</h3>
                    <p class="text-gray-500 mb-6" id="deleteModalText">Weet je zeker dat je het bericht wilt verwijderen? Dit kan niet ongedaan worden gemaakt.</p>

                    <form method="POST" action="contactbeheer.php?tab=berichten" class="flex flex-col sm:flex-row gap-3 justify-center">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="delete_message" id="deleteMessageId" value="">
                        <button type="button" onclick="closeDeleteModal()" class="w-full sm:w-auto px-6 py-3 border border-gray-300 rounded-lg text-gray-700 font-bold hover:bg-gray-50 transition-colors">
                            Annuleren
                        </button>
                        <button type="submit" class="w-full sm:w-auto px-6 py-3 bg-red-600 rounded-lg text-white font-bold hover:bg-red-700 shadow-md shadow-red-600/30 transition-all">
                            Ja, verwijder bericht
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <script>
            const CSRF_TOKEN = <?php echo json_encode(csrf_token()); ?>;

            function openViewModal(msgData) {
                document.getElementById('view-avatar').innerText = msgData.name.charAt(0).toUpperCase();
                document.getElementById('view-name').innerText = msgData.name;
                document.getElementById('view-date').innerText = msgData.date;

                const emailEl = document.getElementById('view-email');
                emailEl.href = 'mailto:' + msgData.email;
                emailEl.querySelector('span').innerText = msgData.email;

                const phoneEl = document.getElementById('view-phone');
                if(msgData.phone) {
                    phoneEl.style.display = 'flex';
                    phoneEl.href = 'tel:' + msgData.phone;
                    phoneEl.querySelector('span').innerText = msgData.phone;
                } else {
                    phoneEl.style.display = 'none';
                }

                document.getElementById('view-subject').innerText = msgData.subject || 'Geen onderwerp';
                document.getElementById('view-text').innerText = msgData.message;

                const subjectEncode = encodeURIComponent(msgData.subject ? 'Re: ' + msgData.subject : 'Reactie via website');
                document.getElementById('view-reply-btn').href = `mailto:${msgData.email}?subject=${subjectEncode}`;

                const modal = document.getElementById('viewModal');
                const content = document.getElementById('viewModalContent');
                modal.classList.remove('hidden');
                setTimeout(() => {
                    content.classList.remove('scale-95', 'opacity-0');
                    content.classList.add('scale-100', 'opacity-100');
                }, 10);

                const badge = document.getElementById('badge-' + msgData.id);
                if (badge) {
                    let formData = new FormData();
                    formData.append('ajax_mark_read', '1');
                    formData.append('id', msgData.id);
                    formData.append('csrf_token', CSRF_TOKEN);

                    fetch('contactbeheer.php', {
                        method: 'POST',
                        body: formData
                    }).then(response => {
                        if (response.ok) {
                            badge.remove();

                            const card = document.getElementById('card-' + msgData.id);
                            card.classList.remove('border-l-4', 'border-pink-500', 'bg-white', 'shadow-md');
                            card.classList.add('border', 'border-gray-200', 'bg-gray-50');

                            const tabBadge = document.getElementById('tab-badge');
                            if (tabBadge) {
                                let currentCount = parseInt(tabBadge.innerText);
                                if (currentCount > 1) {
                                    tabBadge.innerText = (currentCount - 1) + ' ongelezen';
                                } else {
                                    tabBadge.remove();
                                }
                            }
                        }
                    });
                }
            }

            function closeViewModal() {
                const modal = document.getElementById('viewModal');
                const content = document.getElementById('viewModalContent');
                content.classList.remove('scale-100', 'opacity-100');
                content.classList.add('scale-95', 'opacity-0');
                setTimeout(() => modal.classList.add('hidden'), 300);
            }

            function confirmDelete(id, name) {
                const modal = document.getElementById('deleteModal');
                document.getElementById('deleteModalText').innerHTML = `Weet je zeker dat je het bericht van <strong>${name}</strong> wilt verwijderen? Dit kan niet ongedaan worden gemaakt.`;
                document.getElementById('deleteMessageId').value = id;
                modal.classList.remove('hidden');
                setTimeout(() => {
                    document.getElementById('deleteModalContent').classList.remove('scale-95', 'opacity-0');
                    document.getElementById('deleteModalContent').classList.add('scale-100', 'opacity-100');
                }, 10);
            }

            function closeDeleteModal() {
                document.getElementById('deleteModalContent').classList.remove('scale-100', 'opacity-100');
                document.getElementById('deleteModalContent').classList.add('scale-95', 'opacity-0');
                setTimeout(() => document.getElementById('deleteModal').classList.add('hidden'), 300);
            }
        </script>
    </div>
</body>
</html>
