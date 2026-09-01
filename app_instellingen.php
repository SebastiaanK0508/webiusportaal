<?php
require_once 'includes/init.php';
require_website_context();

$message = '';

if (isset($_POST['save_app_settings'])) {
    csrf_verify();
    if (isset($_POST['settings']) && is_array($_POST['settings'])) {
        foreach ($_POST['settings'] as $key => $value) {
            set_app_setting($key, trim($value));
        }
    }
    $toggles = ['enable_email'];
    foreach ($toggles as $toggle) {
        set_app_setting($toggle, isset($_POST['toggles'][$toggle]) ? '1' : '0');
    }
    $message = "E-mailinstellingen zijn succesvol opgeslagen!";
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <title>Instellingen - <?php echo htmlspecialchars(current_website()['company_name'] ?? 'Webius Portaal'); ?></title>
    <?php include 'includes/head.php'; ?>
</head>
<body class="bg-gray-50 font-sans pb-24">
    <?php include 'includes/header.php'; ?>

    <div class="max-w-7xl mx-auto px-4 mt-8">

        <div class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-center border-b border-gray-200 pb-5">
            <div>
                <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">E-mail Configuratie</h1>
                <p class="text-gray-500 mt-2 text-sm">Beheer hier de instellingen voor het contactformulier van deze website.</p>
            </div>
        </div>

        <?php if ($message): ?>
            <script>showToast(<?php echo json_encode($message); ?>, 'success');</script>
        <?php endif; ?>

        <form method="POST" action="app_instellingen.php">
            <?php echo csrf_field(); ?>

            <div class="bg-white p-6 md:p-8 rounded-xl shadow-sm border border-gray-200 mb-6">
                <h2 class="text-xl font-bold text-gray-800 mb-6 border-b pb-2">SMTP Mailconfiguratie</h2>
                <div class="bg-pink-50 p-5 rounded-xl border border-pink-200 mb-8 flex justify-between items-center shadow-sm">
                    <div>
                        <h3 class="font-bold text-pink-800 text-lg">Contactformulier E-mails Inschakelen</h3>
                        <p class="text-sm text-pink-700 mt-1">Wanneer ingeschakeld worden nieuwe contactaanvragen direct naar het opgegeven e-mailadres gestuurd (anders alleen in de database).</p>
                    </div>
                    <label class="inline-flex items-center cursor-pointer ml-4">
                        <input type="checkbox" name="toggles[enable_email]" value="1" <?php echo is_toggled('enable_email') ? 'checked' : ''; ?> class="sr-only peer">
                        <div class="relative w-14 h-7 bg-gray-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-pink-600"></div>
                    </label>
                </div>

                <div class="space-y-4">
                    <h3 class="font-bold text-gray-700 text-sm uppercase tracking-wide">Server Gegevens</h3>
                    <div class="bg-blue-50 border border-blue-100 p-4 rounded-lg mb-4">
                        <h4 class="text-sm font-bold text-blue-800 mb-1 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Gebruik je Gmail of Office365?
                        </h4>
                        <p class="text-xs text-blue-700 leading-relaxed">
                            <strong>Gmail:</strong> Host is <code class="bg-white px-1 rounded">smtp.gmail.com</code> (Poort 465).<br>
                            Let op: het gewone wachtwoord werkt hier niet. Zet 2-staps verificatie aan in het Google account en genereer een <strong>App-wachtwoord</strong>.
                        </p>
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-gray-500 mb-1">Host (bijv. smtp.gmail.com)</label>
                            <input type="text" name="settings[smtp_host]" value="<?php echo get_app_setting('smtp_host'); ?>" placeholder="mail.domein.nl" class="w-full px-4 py-2 bg-gray-50 border border-gray-300 rounded-lg focus:bg-white focus:ring-2 focus:ring-pink-400 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1">Poort (465 of 587)</label>
                            <input type="number" name="settings[smtp_port]" value="<?php echo get_app_setting('smtp_port', '465'); ?>" class="w-full px-4 py-2 bg-gray-50 border border-gray-300 rounded-lg focus:bg-white focus:ring-2 focus:ring-pink-400 outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1">Gebruikersnaam</label>
                            <input type="text" name="settings[smtp_user]" value="<?php echo get_app_setting('smtp_user'); ?>" class="w-full px-4 py-2 bg-gray-50 border border-gray-300 rounded-lg focus:bg-white focus:ring-2 focus:ring-pink-400 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1">Wachtwoord (of App-wachtwoord)</label>
                            <input type="password" name="settings[smtp_pass]" value="<?php echo get_app_setting('smtp_pass'); ?>" class="w-full px-4 py-2 bg-gray-50 border border-gray-300 rounded-lg focus:bg-white focus:ring-2 focus:ring-pink-400 outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 pt-2">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1">Afzender E-mail</label>
                            <input type="email" name="settings[mail_from_address]" value="<?php echo get_app_setting('mail_from_address'); ?>" class="w-full px-4 py-2 bg-gray-50 border border-gray-300 rounded-lg focus:bg-white focus:ring-2 focus:ring-pink-400 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1">Afzender Naam</label>
                            <input type="text" name="settings[mail_from_name]" value="<?php echo get_app_setting('mail_from_name', 'Webius Portaal'); ?>" class="w-full px-4 py-2 bg-gray-50 border border-gray-300 rounded-lg focus:bg-white focus:ring-2 focus:ring-pink-400 outline-none">
                        </div>
                    </div>

                    <div class="pt-2 border-t border-gray-100 mt-2">
                        <label class="block text-xs font-bold text-gray-500 mb-1">Ontvanger E-mailadres (hier komen de formulieren binnen)</label>
                        <input type="email" name="settings[mail_to_address]" value="<?php echo get_app_setting('mail_to_address'); ?>" placeholder="info@klant.nl" class="w-full px-4 py-2 bg-gray-50 border border-gray-300 rounded-lg focus:bg-white focus:ring-2 focus:ring-pink-400 outline-none border-l-4 border-l-pink-500">
                    </div>
                </div>
            </div>

            <div class="sticky bottom-4 z-50 flex justify-end mt-8">
                <button type="submit" name="save_app_settings" class="bg-pink-600 text-white font-extrabold py-3 px-10 rounded-full shadow-lg shadow-pink-600/40 hover:bg-pink-700 hover:-translate-y-1 transform transition-all w-full md:w-auto text-lg flex items-center justify-center gap-2 border-2 border-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                    Instellingen Opslaan
                </button>
            </div>

        </form>
    </div>
</body>
</html>
