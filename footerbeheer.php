<?php
require_once 'includes/init.php';
$website_id = require_website_context();

$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'tab-socials';
$message = '';

if (isset($_POST['update_socials'])) {
    csrf_verify();
    $socials = ['social_instagram', 'social_facebook', 'social_tiktok'];
    foreach ($socials as $social) {
        set_footer_setting($social, trim($_POST[$social] ?? ''));
        set_footer_setting($social . '_actief', isset($_POST[$social . '_actief']) ? '1' : '0');
    }
    $message = "Social media links succesvol bijgewerkt!";
}
if (isset($_POST['update_contact'])) {
    csrf_verify();
    $contacts = ['contact_telefoon', 'contact_email', 'contact_regio'];
    foreach ($contacts as $contact) {
        set_footer_setting($contact, trim($_POST[$contact] ?? ''));
    }
    $message = "Contactgegevens succesvol bijgewerkt!";
}
if (isset($_POST['update_bedrijf'])) {
    csrf_verify();
    $bedrijf = ['bedrijf_kvk', 'bedrijf_btw'];
    foreach ($bedrijf as $gegeven) {
        set_footer_setting($gegeven, trim($_POST[$gegeven] ?? ''));
    }
    $message = "Bedrijfsgegevens succesvol bijgewerkt!";
}
if (isset($_POST['update_info'])) {
    csrf_verify();
    set_footer_setting('footer_text', trim($_POST['footer_text'] ?? ''));
    $message = "Informatie tekst succesvol bijgewerkt!";
}

$footer_data = [];
$stmt = $pdo->prepare("SELECT sleutel, waarde FROM footer WHERE website_id = ?");
$stmt->execute([$website_id]);
while ($row = $stmt->fetch()) {
    $footer_data[$row['sleutel']] = $row['waarde'];
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <title>Footer Beheer - <?php echo htmlspecialchars(current_website()['company_name'] ?? 'Webius Portaal'); ?></title>
    <?php include 'includes/head.php'; ?>
</head>
<body class="bg-gray-100 font-sans pb-24">
    <?php include 'includes/header.php'; ?>

    <div class="max-w-6xl mx-auto px-4 mt-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Footer & Contact</h1>
                <p class="text-gray-500 mt-1">Beheer hier de social media links, contactgegevens en bedrijfsinfo.</p>
            </div>
            <div class="flex gap-3">
                <a href="websitebeheer.php" class="text-gray-600 hover:text-gray-900 font-bold bg-white px-6 py-2 rounded-full shadow border border-gray-200 transition-all">&larr; Terug naar Websitebeheer</a>
            </div>
        </div>

        <?php if ($message): ?>
            <div id="alert-message" class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 shadow-sm rounded-r-lg flex justify-between items-center transition-opacity duration-500">
                <span class="font-medium"><?php echo htmlspecialchars($message); ?></span>
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
                    'tab-socials' => 'Social Media',
                    'tab-info' => 'Tekst',
                    'tab-contact' => 'Contactgegevens',
                    'tab-bedrijf' => 'Bedrijfsgegevens'
                ];
                foreach($tabs as $key => $label):
                    $is_active = ($active_tab === $key);
                    $active_class = $is_active ? 'border-pink-600 text-pink-600 font-bold' : 'border-transparent text-gray-500 hover:text-gray-700 font-medium';
                ?>
                <a href="footerbeheer.php?tab=<?php echo $key; ?>" class="tab-btn whitespace-nowrap py-4 px-1 border-b-2 text-sm <?php echo $active_class; ?>">
                    <?php echo $label; ?>
                </a>
                <?php endforeach; ?>
            </nav>
        </div>
        <div id="tab-socials" class="tab-content bg-white p-6 rounded-b-xl shadow-md border border-gray-200 border-t-0 mb-6 <?php echo $active_tab !== 'tab-socials' ? 'hidden' : ''; ?>">
            <form method="POST" action="footerbeheer.php?tab=tab-socials">
                <?php echo csrf_field(); ?>
                <div class="flex justify-between items-center mb-6 border-b pb-4">
                    <h2 class="text-2xl font-bold text-gray-800">Social Media Links</h2>
                    <button type="submit" name="update_socials" class="bg-pink-600 text-white font-bold py-2 px-6 rounded-lg hover:bg-pink-700 shadow-sm transition-colors">Opslaan</button>
                </div>
                <p class="text-gray-500 text-sm mb-8">Zet een kanaal uit met de schakelaar als deze tijdelijk (of helemaal niet) getoond moet worden op de website.</p>
                <div class="mb-6 p-4 bg-gray-50 border border-gray-200 rounded-xl">
                    <div class="flex justify-between items-center mb-3">
                        <label class="font-bold text-gray-800 flex items-center gap-2">
                            <span class="text-pink-600">📸</span> Instagram URL
                        </label>
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="social_instagram_actief" value="1" <?php echo ($footer_data['social_instagram_actief'] ?? '0') == '1' ? 'checked' : ''; ?> class="sr-only peer">
                            <div class="relative w-11 h-6 bg-gray-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-pink-500"></div>
                            <span class="ms-3 text-sm font-medium text-gray-600">Zichtbaar</span>
                        </label>
                    </div>
                    <input type="text" name="social_instagram" placeholder="https://instagram.com/paginanaam" value="<?php echo htmlspecialchars($footer_data['social_instagram'] ?? ''); ?>" class="w-full px-4 py-2 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-pink-400 focus:border-pink-400 text-sm transition-all">
                </div>
                <div class="mb-6 p-4 bg-gray-50 border border-gray-200 rounded-xl">
                    <div class="flex justify-between items-center mb-3">
                        <label class="font-bold text-gray-800 flex items-center gap-2">
                            <span class="text-blue-600">📘</span> Facebook URL
                        </label>
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="social_facebook_actief" value="1" <?php echo ($footer_data['social_facebook_actief'] ?? '0') == '1' ? 'checked' : ''; ?> class="sr-only peer">
                            <div class="relative w-11 h-6 bg-gray-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-pink-500"></div>
                            <span class="ms-3 text-sm font-medium text-gray-600">Zichtbaar</span>
                        </label>
                    </div>
                    <input type="text" name="social_facebook" placeholder="https://facebook.com/paginanaam" value="<?php echo htmlspecialchars($footer_data['social_facebook'] ?? ''); ?>" class="w-full px-4 py-2 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-pink-400 focus:border-pink-400 text-sm transition-all">
                </div>
                <div class="mb-6 p-4 bg-gray-50 border border-gray-200 rounded-xl">
                    <div class="flex justify-between items-center mb-3">
                        <label class="font-bold text-gray-800 flex items-center gap-2">
                            <span class="text-black">🎵</span> TikTok URL
                        </label>
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="social_tiktok_actief" value="1" <?php echo ($footer_data['social_tiktok_actief'] ?? '0') == '1' ? 'checked' : ''; ?> class="sr-only peer">
                            <div class="relative w-11 h-6 bg-gray-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-pink-500"></div>
                            <span class="ms-3 text-sm font-medium text-gray-600">Zichtbaar</span>
                        </label>
                    </div>
                    <input type="text" name="social_tiktok" placeholder="https://tiktok.com/@paginanaam" value="<?php echo htmlspecialchars($footer_data['social_tiktok'] ?? ''); ?>" class="w-full px-4 py-2 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-pink-400 focus:border-pink-400 text-sm transition-all">
                </div>
            </form>
        </div>
        <div id="tab-info" class="tab-content bg-white p-6 rounded-b-xl shadow-md border border-gray-200 border-t-0 mb-6 <?php echo $active_tab !== 'tab-info' ? 'hidden' : ''; ?>">
            <form method="POST" action="footerbeheer.php?tab=tab-info">
                <?php echo csrf_field(); ?>
                <div class="flex justify-between items-center mb-6 border-b pb-4">
                    <h2 class="text-2xl font-bold text-gray-800">Tekst</h2>
                    <button type="submit" name="update_info" class="bg-pink-600 text-white font-bold py-2 px-6 rounded-lg hover:bg-pink-700 shadow-sm transition-colors">Opslaan</button>
                </div>

                <div class="grid md:grid-cols-2 gap-6">
                    <div class="mb-4">
                        <label class="font-bold text-gray-800 text-sm block mb-2">Tekst onder Titel</label>
                        <input type="text" name="footer_text" placeholder="Een korte omschrijving..." value="<?php echo htmlspecialchars($footer_data['footer_text'] ?? ''); ?>" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-pink-400 focus:border-pink-400 text-sm transition-all">
                    </div>
                </div>
            </form>
        </div>
        <div id="tab-contact" class="tab-content bg-white p-6 rounded-b-xl shadow-md border border-gray-200 border-t-0 mb-6 <?php echo $active_tab !== 'tab-contact' ? 'hidden' : ''; ?>">
            <form method="POST" action="footerbeheer.php?tab=tab-contact">
                <?php echo csrf_field(); ?>
                <div class="flex justify-between items-center mb-6 border-b pb-4">
                    <h2 class="text-2xl font-bold text-gray-800">Contactgegevens</h2>
                    <button type="submit" name="update_contact" class="bg-pink-600 text-white font-bold py-2 px-6 rounded-lg hover:bg-pink-700 shadow-sm transition-colors">Opslaan</button>
                </div>

                <div class="grid md:grid-cols-2 gap-6">
                    <div class="mb-4">
                        <label class="font-bold text-gray-800 text-sm block mb-2">Telefoonnummer</label>
                        <input type="text" name="contact_telefoon" placeholder="06 - 12 34 56 78" value="<?php echo htmlspecialchars($footer_data['contact_telefoon'] ?? ''); ?>" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-pink-400 focus:border-pink-400 text-sm transition-all">
                    </div>
                    <div class="mb-4">
                        <label class="font-bold text-gray-800 text-sm block mb-2">E-mailadres</label>
                        <input type="email" name="contact_email" placeholder="info@klant.nl" value="<?php echo htmlspecialchars($footer_data['contact_email'] ?? ''); ?>" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-pink-400 focus:border-pink-400 text-sm transition-all">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="font-bold text-gray-800 text-sm block mb-2">Regio & Werkgebied (HTML toegestaan, bijv. &lt;br&gt;)</label>
                    <textarea name="contact_regio" rows="3" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-pink-400 focus:border-pink-400 text-sm transition-all leading-relaxed"><?php echo htmlspecialchars($footer_data['contact_regio'] ?? ''); ?></textarea>
                </div>
            </form>
        </div>
        <div id="tab-bedrijf" class="tab-content bg-white p-6 rounded-b-xl shadow-md border border-gray-200 border-t-0 mb-6 <?php echo $active_tab !== 'tab-bedrijf' ? 'hidden' : ''; ?>">
            <form method="POST" action="footerbeheer.php?tab=tab-bedrijf">
                <?php echo csrf_field(); ?>
                <div class="flex justify-between items-center mb-6 border-b pb-4">
                    <h2 class="text-2xl font-bold text-gray-800">Bedrijfsgegevens</h2>
                    <button type="submit" name="update_bedrijf" class="bg-pink-600 text-white font-bold py-2 px-6 rounded-lg hover:bg-pink-700 shadow-sm transition-colors">Opslaan</button>
                </div>

                <div class="grid md:grid-cols-2 gap-6">
                    <div class="mb-4">
                        <label class="font-bold text-gray-800 text-sm block mb-2">KVK Nummer</label>
                        <input type="text" name="bedrijf_kvk" placeholder="12345678" value="<?php echo htmlspecialchars($footer_data['bedrijf_kvk'] ?? ''); ?>" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-pink-400 focus:border-pink-400 text-sm transition-all">
                    </div>
                    <div class="mb-4">
                        <label class="font-bold text-gray-800 text-sm block mb-2">BTW Nummer</label>
                        <input type="text" name="bedrijf_btw" placeholder="NL123456789B01" value="<?php echo htmlspecialchars($footer_data['bedrijf_btw'] ?? ''); ?>" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-pink-400 focus:border-pink-400 text-sm transition-all">
                    </div>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
