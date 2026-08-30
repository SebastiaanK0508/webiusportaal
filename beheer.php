<?php
require_once 'includes/init.php';
require_website_context();

$username = current_user()['username'] ?? 'Beheerder';
$website = current_website();
$site_label = $website['company_name'] ?? 'Webius Portaal';
$website_id = $website['id'];
$enabled_modules = [];
try {
    $enabled_modules = get_enabled_modules($website_id);
} catch (Throwable $e) {
}

$unread_messages = 0;
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM contact_messages WHERE website_id = ? AND is_read = 0");
    $stmt->execute([$website_id]);
    $unread_messages = (int)$stmt->fetchColumn();
} catch (Throwable $e) {
}

$module_count = function (string $table) use ($pdo, $website_id): int {
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM {$table} WHERE website_id = ?");
        $stmt->execute([$website_id]);
        return (int)$stmt->fetchColumn();
    } catch (Throwable $e) {
        return 0;
    }
};
$stat_tiles = [];
$stat_tiles[] = ['count' => $unread_messages, 'label' => 'Nieuwe berichten', 'color' => 'emerald', 'icon' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'];
if (in_array('nieuws', $enabled_modules, true)) {
    $stat_tiles[] = ['count' => $module_count('nieuws'), 'label' => 'Nieuwsartikelen', 'color' => 'indigo', 'icon' => 'M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 01-2.25 2.25M16.5 7.5V18a2.25 2.25 0 002.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 002.25 2.25h13.5M6 7.5h3v3H6v-3z'];
}
if (in_array('cadeaukaarten', $enabled_modules, true)) {
    $stat_tiles[] = ['count' => $module_count('cadeaukaarten'), 'label' => 'Cadeaukaarten', 'color' => 'amber', 'icon' => 'M21 11.25v8.25a1.5 1.5 0 01-1.5 1.5H4.5a1.5 1.5 0 01-1.5-1.5v-8.25M12 4.875A2.625 2.625 0 109.375 7.5H12m0-2.625V7.5m0-2.625A2.625 2.625 0 1114.625 7.5H12m0 0V21m-8.625-9.75h18c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125h-18c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z'];
}
if (in_array('assortiment', $enabled_modules, true)) {
    $stat_tiles[] = ['count' => $module_count('assortiment_categorieen'), 'label' => 'Assortiment categorieën', 'color' => 'pink', 'icon' => 'M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z'];
}
if (in_array('prijsvraag', $enabled_modules, true)) {
    $stat_tiles[] = ['count' => $module_count('prijsvraag_inzendingen'), 'label' => 'Prijsvraag inzendingen', 'color' => 'amber', 'icon' => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z'];
}
if (empty($enabled_modules)) {
    try {
        $stat_tiles[] = ['count' => count(get_portfolio()), 'label' => 'Portfolio Items', 'color' => 'pink', 'icon' => 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z'];
        $stat_tiles[] = ['count' => count(get_all_products_admin()), 'label' => 'Producten', 'color' => 'indigo', 'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'];
        $stat_tiles[] = ['count' => count(get_reviews()), 'label' => 'Reviews', 'color' => 'amber', 'icon' => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z'];
    } catch (Throwable $e) {
    }
}
$tile_color_classes = [
    'pink' => 'bg-pink-50 text-pink-600',
    'indigo' => 'bg-indigo-50 text-indigo-600',
    'amber' => 'bg-amber-50 text-amber-500',
    'emerald' => 'bg-emerald-50 text-emerald-600',
];
$shortcuts = [];
if (in_array('nieuws', $enabled_modules, true)) {
    $shortcuts[] = ['href' => 'nieuwsbeheer.php', 'title' => 'Nieuws', 'desc' => 'Nieuwsberichten plaatsen, bewerken of verwijderen.', 'icon' => 'M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 01-2.25 2.25M16.5 7.5V18a2.25 2.25 0 002.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 002.25 2.25h13.5M6 7.5h3v3H6v-3z'];
}
if (in_array('assortiment', $enabled_modules, true)) {
    $shortcuts[] = ['href' => 'assortimentbeheer.php', 'title' => 'Assortiment', 'desc' => 'Categorieën en merken in het assortiment beheren.', 'icon' => 'M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z'];
}
if (in_array('cadeaukaarten', $enabled_modules, true)) {
    $shortcuts[] = ['href' => 'cadeaukaartenbeheer.php', 'title' => 'Cadeaukaarten', 'desc' => 'Het aanbod cadeaukaarten bijhouden.', 'icon' => 'M21 11.25v8.25a1.5 1.5 0 01-1.5 1.5H4.5a1.5 1.5 0 01-1.5-1.5v-8.25M12 4.875A2.625 2.625 0 109.375 7.5H12m0-2.625V7.5m0-2.625A2.625 2.625 0 1114.625 7.5H12m0 0V21m-8.625-9.75h18c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125h-18c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z'];
}
if (in_array('prijsvraag', $enabled_modules, true)) {
    $shortcuts[] = ['href' => 'prijsvraagbeheer.php', 'title' => 'Prijsvraag', 'desc' => 'De actieve vraag, prijs en inzendingen beheren.', 'icon' => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z'];
}
if (in_array('geschiedenis', $enabled_modules, true)) {
    $shortcuts[] = ['href' => 'geschiedenisbeheer.php', 'title' => 'Geschiedenis', 'desc' => 'De tijdlijn met het verhaal van het bedrijf bewerken.', 'icon' => 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z'];
}
if (empty($enabled_modules)) {
    $shortcuts[] = ['href' => 'websitebeheer.php?tab=portfolio', 'title' => 'Portfolio', 'desc' => "Foto's toevoegen of verwijderen uit de inspiratie galerij.", 'icon' => 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z'];
    $shortcuts[] = ['href' => 'productbeheer.php', 'title' => 'Behandelingen', 'desc' => 'Beheer diensten, beschrijvingen en de tarievenlijst.', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'];
}
$shortcuts[] = ['href' => 'contactbeheer.php', 'title' => 'Contactberichten', 'desc' => 'Binnengekomen berichten via het contactformulier bekijken.', 'icon' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'];
$shortcuts[] = ['href' => 'websitebeheer.php', 'title' => 'Teksten', 'desc' => 'Pas algemene teksten aan.', 'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z'];
$shortcuts[] = ['href' => 'footerbeheer.php', 'title' => 'Footer', 'desc' => 'Contactgegevens en social links in de footer beheren.', 'icon' => 'M4 6h16M4 12h16M4 18h7'];
$shortcuts[] = ['href' => 'legalbeheer.php', 'title' => 'Legals', 'desc' => 'Beheer de juridische teksten.', 'icon' => 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z'];
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <title>Dashboard - Webius Portaal</title>
    <?php include 'includes/head.php'; ?>
</head>
<body class="bg-gray-100 font-sans pb-24">
    <?php include 'includes/header.php'; ?>
    <div class="max-w-6xl mx-auto px-4 mt-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Welkom terug, <?php echo htmlspecialchars($username); ?>! 👋</h1>
            <p class="text-gray-500 mt-2">Dit is je overzicht voor <strong><?php echo htmlspecialchars($site_label); ?></strong>. Kies een module om de website te beheren.</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 <?php echo count($stat_tiles) > 4 ? 'lg:grid-cols-5' : 'lg:grid-cols-4'; ?> gap-6 mb-10">
            <?php foreach ($stat_tiles as $tile): ?>
                <?php $colors = $tile_color_classes[$tile['color']] ?? $tile_color_classes['pink']; ?>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex items-center gap-4 transition-transform hover:-translate-y-1 duration-300">
                    <div class="w-14 h-14 rounded-full <?php echo $colors; ?> flex items-center justify-center flex-shrink-0">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="<?php echo $tile['icon']; ?>"></path></svg>
                    </div>
                    <div>
                        <span class="block text-2xl font-bold text-gray-900"><?php echo $tile['count']; ?></span>
                        <span class="text-sm font-medium text-gray-500"><?php echo htmlspecialchars($tile['label']); ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                        <div class="p-2 bg-pink-50 rounded-lg text-pink-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        </div>
                        <h2 class="text-lg font-bold text-gray-800">Snelkoppelingen Beheer</h2>
                    </div>
                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php foreach ($shortcuts as $sc): ?>
                            <a href="<?php echo htmlspecialchars($sc['href']); ?>" class="group flex items-start p-5 border border-gray-200 rounded-xl hover:border-pink-500 hover:shadow-md transition-all duration-300 bg-white">
                                <div class="p-3 bg-gray-50 rounded-lg group-hover:bg-pink-50 group-hover:text-pink-600 transition-colors mr-4 text-gray-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="<?php echo $sc['icon']; ?>"></path></svg>
                                </div>
                                <div>
                                    <h3 class="text-md font-bold text-gray-900 group-hover:text-pink-600 transition-colors"><?php echo htmlspecialchars($sc['title']); ?></h3>
                                    <p class="text-sm text-gray-500 mt-1"><?php echo htmlspecialchars($sc['desc']); ?></p>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div class="space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-5">Systeem Status</h3>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 bg-green-500 rounded-full shadow-[0_0_8px_rgba(34,197,94,0.6)]"></span>
                                <span class="text-sm font-medium text-gray-700">Website Online</span>
                            </div>
                            <span class="text-xs text-gray-400">Actief</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 bg-green-500 rounded-full shadow-[0_0_8px_rgba(34,197,94,0.6)]"></span>
                                <span class="text-sm font-medium text-gray-700">Database</span>
                            </div>
                            <span class="text-xs text-gray-400">Verbonden</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 bg-pink-500 rounded-full shadow-[0_0_8px_rgba(236,72,153,0.6)]"></span>
                                <span class="text-sm font-medium text-gray-700">Versie CMS</span>
                            </div>
                            <span class="text-xs font-bold text-gray-900">Oreo v2.0</span>
                        </div>
                    </div>
                </div>
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl shadow-sm border border-slate-700 p-6 text-white relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-4 opacity-10">
                        <svg class="w-24 h-24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h3 class="text-lg font-bold mb-2 relative z-10">Mijn Profiel</h3>
                    <p class="text-slate-300 text-sm mb-5 relative z-10">Je kunt je gebruikersnaam, e-mailadres en wachtwoord veilig aanpassen in je profiel instellingen.</p>
                    <a href="profielbeheer.php" class="inline-flex items-center gap-2 text-sm font-bold bg-white/10 hover:bg-white/20 px-4 py-2 rounded-lg transition-colors relative z-10 text-white">
                        Profiel beheren
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
