<?php
require_once __DIR__ . '/init.php';
require_login();

$current_user_row = current_user();
$admin_username = user_full_name($current_user_row);
$admin_email = !empty($current_user_row['email']) ? $current_user_row['email'] : 'Geen e-mailadres gekoppeld';

$active_website = current_website();

// Welke van de 5 site-specifieke modules staan aan voor de huidige website —
// bepaalt zowel welke navlinks verschijnen als (elders, in elke module-
// beheerpagina zelf) of die pagina daadwerkelijk toegankelijk is.
$enabled_modules = current_website_id() ? get_enabled_modules(current_website_id()) : [];
$module_pages = [
    'assortiment'   => ['label' => 'Assortiment',   'href' => 'assortimentbeheer.php'],
    'cadeaukaarten' => ['label' => 'Cadeaukaarten', 'href' => 'cadeaukaartenbeheer.php'],
    'nieuws'        => ['label' => 'Nieuws',        'href' => 'nieuwsbeheer.php'],
    'prijsvraag'    => ['label' => 'Prijsvraag',    'href' => 'prijsvraagbeheer.php'],
    'geschiedenis'  => ['label' => 'Geschiedenis',  'href' => 'geschiedenisbeheer.php'],
];

$switchable_websites = [];
if (is_super_admin()) {
    $switchable_websites = $pdo->query("SELECT id, company_name, domain_name FROM websites WHERE is_active = 1 ORDER BY company_name ASC")->fetchAll();
}

$unread_count = 0;
$recent_messages = [];
if (current_website_id()) {
    try {
        $stmt_count = $pdo->prepare("SELECT COUNT(*) FROM contact_messages WHERE website_id = ? AND is_read = 0");
        $stmt_count->execute([current_website_id()]);
        $unread_count = (int)$stmt_count->fetchColumn();

        $stmt_msgs = $pdo->prepare("SELECT name, subject, created_at FROM contact_messages WHERE website_id = ? AND is_read = 0 ORDER BY created_at DESC LIMIT 5");
        $stmt_msgs->execute([current_website_id()]);
        $recent_messages = $stmt_msgs->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Fout bij ophalen contactberichten: ' . $e->getMessage());
    }
}
?>
<header class="sticky top-0 z-50 bg-white border-b border-gray-200 shadow-sm transition-all duration-300">
    <!-- IDENTITEITSSTRIP: welke klantwebsite wordt nu beheerd -->
    <div class="bg-gray-50 border-b border-gray-100 px-4 sm:px-6 lg:px-8 py-1.5 flex flex-wrap items-center justify-between gap-2 text-xs">
        <div class="flex items-center gap-2 text-gray-600 min-w-0">
            <span class="text-gray-400">Je beheert nu:</span>
            <?php if ($active_website): ?>
                <?php if (!empty($active_website['logo_path'])): ?>
                    <img src="<?php echo htmlspecialchars($active_website['logo_path']); ?>" class="h-4 w-4 rounded object-cover border border-gray-200 flex-shrink-0" alt="">
                <?php endif; ?>
                <span class="font-bold text-gray-800 truncate"><?php echo htmlspecialchars($active_website['company_name']); ?></span>
                <span class="text-gray-400 truncate hidden sm:inline">(<?php echo htmlspecialchars($active_website['domain_name']); ?>)</span>
            <?php else: ?>
                <span class="italic text-amber-600">Nog geen website gekozen</span>
            <?php endif; ?>
        </div>
        <?php if (is_super_admin()): ?>
            <form method="POST" action="includes/switch_website.php" class="flex items-center gap-2">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="return_to" value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI'] ?? 'beheer.php'); ?>">
                <select name="website_id" onchange="this.form.submit()" class="text-xs border border-gray-300 rounded-md py-1 pl-2 pr-6 bg-white focus:ring-1 focus:ring-pink-500 focus:border-pink-500">
                    <option value="">Wissel van website&hellip;</option>
                    <?php foreach ($switchable_websites as $w): ?>
                        <option value="<?php echo htmlspecialchars($w['id']); ?>" <?php echo ($w['id'] === current_website_id()) ? 'selected' : ''; ?>><?php echo htmlspecialchars($w['company_name']); ?></option>
                    <?php endforeach; ?>
                </select>
                <a href="super_websites.php" class="text-pink-600 hover:text-pink-700 font-bold whitespace-nowrap">Beheer websites</a>
            </form>
        <?php endif; ?>
    </div>

    <div class="w-full px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <div class="flex items-center gap-8">
                <a href="beheer.php" class="flex items-center flex-shrink-0">
                    <img src="assets/img/webius_icon.png" alt="Webius" class="h-9 w-9 rounded-lg object-cover">
                </a>
                <nav class="hidden md:flex space-x-1 lg:space-x-2 items-center">
                    <a href="beheer.php" class="px-3 py-2 rounded-md text-gray-600 hover:text-pink-600 hover:bg-pink-50 font-medium text-sm transition-all duration-200">Dashboard</a>

                    <!-- PAGINA'S: alle content die bezoekers op de website zien — vaste
                         pagina's die elke site heeft, plus (indien ingeschakeld) de
                         site-specifieke modules. Bewust samen in één menu: het zijn voor
                         de gebruiker allemaal "pagina's die ik kan bewerken". -->
                    <div class="relative group">
                        <button class="flex items-center gap-1 px-3 py-2 rounded-md text-gray-600 hover:text-pink-600 hover:bg-pink-50 font-medium text-sm transition-all duration-200 focus:outline-none">
                            Pagina's
                            <svg class="h-4 w-4 mt-0.5 transition-transform duration-200 group-hover:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div class="absolute left-0 mt-0 w-56 bg-white rounded-lg shadow-xl border border-gray-100 py-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 transform origin-top-left z-50 translate-y-2 group-hover:translate-y-0">
                            <div class="px-4 py-2 text-xs font-bold text-gray-400 uppercase tracking-wider">Vaste pagina's</div>
                            <a href="websitebeheer.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-pink-50 hover:text-pink-600 transition-colors">Homepagina</a>
                            <a href="productbeheer.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-pink-50 hover:text-pink-600 transition-colors">Prijspagina</a>
                            <a href="contactbeheer.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-pink-50 hover:text-pink-600 transition-colors">Contactpagina</a>
                            <?php if (!empty($enabled_modules)): ?>
                                <div class="border-t border-gray-100 my-1"></div>
                                <div class="px-4 py-2 text-xs font-bold text-gray-400 uppercase tracking-wider">Site-specifiek</div>
                                <?php foreach ($module_pages as $key => $page): if (!in_array($key, $enabled_modules, true)) continue; ?>
                                    <a href="<?php echo htmlspecialchars($page['href']); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-pink-50 hover:text-pink-600 transition-colors"><?php echo htmlspecialchars($page['label']); ?></a>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- INSTELLINGEN: configuratie van de huidige website, geen content
                         die bezoekers direct als "pagina" zien. -->
                    <div class="relative group">
                        <button class="flex items-center gap-1 px-3 py-2 rounded-md text-gray-600 hover:text-pink-600 hover:bg-pink-50 font-medium text-sm transition-all duration-200 focus:outline-none">
                            Instellingen
                            <svg class="h-4 w-4 mt-0.5 transition-transform duration-200 group-hover:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div class="absolute left-0 mt-0 w-56 bg-white rounded-lg shadow-xl border border-gray-100 py-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 transform origin-top-left z-50 translate-y-2 group-hover:translate-y-0">
                            <a href="footerbeheer.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-pink-50 hover:text-pink-600 transition-colors">Footer & Socials</a>
                            <a href="legalbeheer.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-pink-50 hover:text-pink-600 transition-colors">Juridische Documenten</a>
                            <a href="app_instellingen.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-pink-50 hover:text-pink-600 transition-colors">Algemene Instellingen</a>
                        </div>
                    </div>

                    <!-- PLATFORM: alleen voor super admins — beheer over de klantwebsites
                         zelf, los van het bewerken van één specifieke website. -->
                    <?php if (is_super_admin()): ?>
                    <div class="relative group">
                        <button class="flex items-center gap-1 px-3 py-2 rounded-md text-gray-600 hover:text-pink-600 hover:bg-pink-50 font-medium text-sm transition-all duration-200 focus:outline-none">
                            Platform
                            <svg class="h-4 w-4 mt-0.5 transition-transform duration-200 group-hover:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div class="absolute left-0 mt-0 w-56 bg-white rounded-lg shadow-xl border border-gray-100 py-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 transform origin-top-left z-50 translate-y-2 group-hover:translate-y-0">
                            <a href="super_websites.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-pink-50 hover:text-pink-600 transition-colors">Klantwebsites Beheren</a>
                            <a href="super_velden.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-pink-50 hover:text-pink-600 transition-colors">Velden Beheren</a>
                        </div>
                    </div>
                    <?php endif; ?>
                </nav>
            </div>
            <div class="flex-1 flex justify-center px-6 hidden lg:flex">
                <form action="search.php" method="GET" class="relative w-full max-w-md">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="text" name="q" class="block w-full pl-10 pr-3 py-1.5 border border-gray-300 rounded-md leading-5 bg-gray-50 placeholder-gray-500 focus:outline-none focus:bg-white focus:ring-1 focus:ring-pink-500 focus:border-pink-500 sm:text-sm transition-colors" placeholder="Zoeken in portaal..." required>
                </form>
            </div>

            <div class="hidden md:flex items-center gap-4">
                <div class="relative group py-4">
                    <button class="relative p-1 text-gray-500 hover:text-pink-600 focus:outline-none transition-colors">
                        <?php if($unread_count > 0): ?>
                            <span class="absolute top-0 right-0 inline-flex items-center justify-center px-1.5 py-0.5 text-[10px] font-bold leading-none text-white transform translate-x-1/4 -translate-y-1/4 bg-red-500 rounded-full shadow-sm">
                                <?php echo $unread_count; ?>
                            </span>
                        <?php endif; ?>
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                    </button>
                    <div class="absolute right-0 mt-0 w-80 bg-white rounded-lg shadow-xl border border-gray-100 py-1 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 transform origin-top-right z-50 translate-y-2 group-hover:translate-y-0">
                        <div class="px-4 py-2 border-b border-gray-50 flex justify-between items-center">
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Recente Berichten</span>
                            <a href="contactbeheer.php" class="text-xs text-pink-600 hover:text-pink-700 font-medium">Alles bekijken</a>
                        </div>
                        <div class="max-h-64 overflow-y-auto">
                            <?php if(empty($recent_messages)): ?>
                                <p class="text-sm text-gray-500 px-4 py-4 text-center">Je hebt geen nieuwe berichten.</p>
                            <?php else: ?>
                                <?php foreach($recent_messages as $msg): ?>
                                    <a href="contactbeheer.php" class="block px-4 py-3 hover:bg-gray-50 border-b border-gray-50 last:border-0 transition-colors">
                                        <p class="text-sm font-medium text-gray-800 truncate"><?php echo htmlspecialchars($msg['name']); ?></p>
                                        <p class="text-xs text-gray-500 truncate"><?php echo htmlspecialchars($msg['subject'] ?? 'Geen onderwerp'); ?></p>
                                        <p class="text-[10px] text-gray-400 mt-1"><?php echo date('d-m-Y H:i', strtotime($msg['created_at'])); ?></p>
                                    </a>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="relative group py-4">
                    <button class="flex items-center gap-2 focus:outline-none">
                        <img class="h-8 w-8 rounded-full object-cover border border-gray-200" src="https://ui-avatars.com/api/?name=<?php echo urlencode($admin_username); ?>&background=fce7f3&color=db2777" alt="Admin Avatar">
                        <span class="text-sm font-medium text-gray-700 hidden xl:block"><?php echo htmlspecialchars($admin_username); ?></span>
                        <svg class="h-4 w-4 text-gray-500 mt-0.5 transition-transform duration-200 group-hover:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div class="absolute right-0 mt-0 w-48 bg-white rounded-md shadow-lg py-1 ring-1 ring-black ring-opacity-5 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 transform origin-top-right translate-y-2 group-hover:translate-y-0 z-50">
                        <a href="profielbeheer.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Mijn Profiel</a>
                        <a href="app_instellingen.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Algemene Instellingen</a>
                        <div class="border-t border-gray-100 mt-1"></div>
                        <a href="includes/logout.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50">Uitloggen</a>
                    </div>
                </div>
            </div>
            <div class="md:hidden flex items-center">
                <button id="mobile-menu-button" class="text-gray-500 hover:text-pink-600 focus:outline-none p-2 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path id="menu-icon-open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        <path id="menu-icon-close" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div id="mobile-menu" class="md:hidden absolute w-full bg-white border-b border-gray-200 shadow-xl overflow-y-auto transition-all duration-300 max-h-0 opacity-0 z-40">
        <div class="p-4 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <img src="assets/img/webius_icon.png" alt="Webius" class="h-9 w-9 rounded-lg object-cover flex-shrink-0">
                <div class="w-px h-8 bg-gray-200"></div>
                <img class="h-10 w-10 rounded-full object-cover border border-gray-200 bg-white" src="https://ui-avatars.com/api/?name=<?php echo urlencode($admin_username); ?>&background=fce7f3&color=db2777" alt="Admin">
                <div>
                    <div class="text-sm font-bold text-gray-800"><?php echo htmlspecialchars($admin_username); ?></div>
                    <div class="text-xs text-gray-500"><?php echo htmlspecialchars($admin_email); ?></div>
                </div>
            </div>
            <a href="contactbeheer.php" class="relative p-2 text-gray-500 hover:text-pink-600 bg-white rounded-full shadow-sm border border-gray-200 transition-colors">
                <?php if($unread_count > 0): ?>
                    <span class="absolute top-0 right-0 inline-flex items-center justify-center px-1.5 py-0.5 text-[10px] font-bold leading-none text-white transform translate-x-1/4 -translate-y-1/4 bg-red-500 rounded-full">
                        <?php echo $unread_count; ?>
                    </span>
                <?php endif; ?>
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                </svg>
            </a>
        </div>

        <!-- MOBIEL ZOEKFILTER -->
        <div class="p-4 border-b border-gray-100">
            <form action="search.php" method="GET" class="relative w-full">
                <input type="text" name="q" class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg bg-gray-50 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent focus:bg-white text-sm transition-colors" placeholder="Zoeken in portaal..." required>
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
            </form>
        </div>

        <nav class="px-4 py-4 space-y-1">
            <a href="beheer.php" class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium text-gray-700 hover:text-pink-600 hover:bg-pink-50 rounded-lg transition-colors">
                Dashboard
            </a>

            <div class="mt-4 pt-4 border-t border-gray-100">
                <p class="px-3 text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Vaste pagina's</p>
                <a href="websitebeheer.php" class="flex items-center gap-3 px-3 py-2 text-sm text-gray-600 hover:text-pink-600 hover:bg-pink-50 rounded-lg transition-colors">
                    <span class="w-1.5 h-1.5 rounded-full bg-gray-300 ml-1"></span> Homepagina
                </a>
                <a href="productbeheer.php" class="flex items-center gap-3 px-3 py-2 text-sm text-gray-600 hover:text-pink-600 hover:bg-pink-50 rounded-lg transition-colors">
                    <span class="w-1.5 h-1.5 rounded-full bg-gray-300 ml-1"></span> Prijspagina
                </a>
                <a href="contactbeheer.php" class="flex items-center justify-between px-3 py-2 text-sm text-gray-600 hover:text-pink-600 hover:bg-pink-50 rounded-lg transition-colors">
                    <span class="flex items-center gap-3"><span class="w-1.5 h-1.5 rounded-full bg-gray-300 ml-1"></span> Contactpagina</span>
                    <?php if($unread_count > 0): ?>
                        <span class="bg-pink-100 text-pink-600 py-0.5 px-2 rounded-full text-xs font-bold"><?php echo $unread_count; ?> Nieuw</span>
                    <?php endif; ?>
                </a>
            </div>
            <?php if (!empty($enabled_modules)): ?>
            <div class="mt-4 pt-4 border-t border-gray-100">
                <p class="px-3 text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Site-specifiek</p>
                <?php foreach ($module_pages as $key => $page): if (!in_array($key, $enabled_modules, true)) continue; ?>
                    <a href="<?php echo htmlspecialchars($page['href']); ?>" class="flex items-center gap-3 px-3 py-2 text-sm text-gray-600 hover:text-pink-600 hover:bg-pink-50 rounded-lg transition-colors">
                        <span class="w-1.5 h-1.5 rounded-full bg-gray-300 ml-1"></span> <?php echo htmlspecialchars($page['label']); ?>
                    </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            <div class="mt-4 pt-4 border-t border-gray-100">
                <p class="px-3 text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Instellingen</p>
                <a href="footerbeheer.php" class="flex items-center gap-3 px-3 py-2 text-sm text-gray-600 hover:text-pink-600 hover:bg-pink-50 rounded-lg transition-colors">
                    <span class="w-1.5 h-1.5 rounded-full bg-gray-300 ml-1"></span> Footer & Socials
                </a>
                <a href="legalbeheer.php" class="flex items-center gap-3 px-3 py-2 text-sm text-gray-600 hover:text-pink-600 hover:bg-pink-50 rounded-lg transition-colors">
                    <span class="w-1.5 h-1.5 rounded-full bg-gray-300 ml-1"></span> Juridische Documenten
                </a>
                <a href="app_instellingen.php" class="flex items-center gap-3 px-3 py-2 text-sm text-gray-600 hover:text-pink-600 hover:bg-pink-50 rounded-lg transition-colors">
                    <span class="w-1.5 h-1.5 rounded-full bg-gray-300 ml-1"></span> Algemene Instellingen
                </a>
            </div>
            <?php if (is_super_admin()): ?>
            <div class="mt-4 pt-4 border-t border-gray-100">
                <p class="px-3 text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Platform</p>
                <a href="super_websites.php" class="flex items-center gap-3 px-3 py-2 text-sm text-gray-600 hover:text-pink-600 hover:bg-pink-50 rounded-lg transition-colors">
                    <span class="w-1.5 h-1.5 rounded-full bg-pink-300 ml-1"></span> Klantwebsites Beheren
                </a>
                <a href="super_velden.php" class="flex items-center gap-3 px-3 py-2 text-sm text-gray-600 hover:text-pink-600 hover:bg-pink-50 rounded-lg transition-colors">
                    <span class="w-1.5 h-1.5 rounded-full bg-pink-300 ml-1"></span> Velden Beheren
                </a>
            </div>
            <?php endif; ?>
        </nav>
        <div class="p-4 border-t border-gray-100 bg-gray-50">
            <a href="profielbeheer.php" class="block px-3 py-2 text-sm font-medium text-gray-700 hover:text-pink-600 transition-colors">Mijn Profiel Instellingen</a>
            <a href="includes/logout.php" class="block px-3 py-2 text-sm font-medium text-red-600 hover:text-red-700 transition-colors mt-1">Uitloggen</a>
        </div>
    </div>
</header>

<!-- Gedeelde meldingen-UI: 1x geladen op elke beheerpagina.
     showToast(message, type) vervangt losse groene/rode banners,
     confirmSubmit(event, message) vervangt native confirm() bij verwijder-acties. -->
<div id="toast-container" class="fixed top-4 right-4 z-[200] flex flex-col gap-3 w-full max-w-sm pointer-events-none"></div>

<div id="confirm-modal" class="hidden fixed inset-0 z-[150] flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeConfirmModal()"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl max-w-sm w-full p-8 text-center">
        <div class="w-16 h-16 bg-red-50 text-red-500 rounded-full flex items-center justify-center text-3xl mx-auto mb-5">⚠️</div>
        <h3 class="text-lg font-bold text-gray-800 mb-2">Weet je het zeker?</h3>
        <p id="confirm-modal-text" class="text-sm text-gray-500 mb-6 leading-relaxed"></p>
        <div class="flex gap-3">
            <button type="button" onclick="closeConfirmModal()" class="flex-1 py-2.5 bg-gray-100 text-gray-600 rounded-lg text-sm font-bold hover:bg-gray-200 transition">Annuleren</button>
            <button type="button" id="confirm-modal-ok" class="flex-1 py-2.5 bg-red-600 text-white rounded-lg text-sm font-bold hover:bg-red-700 transition shadow">Verwijderen</button>
        </div>
    </div>
</div>

<script>
    // ---- Toasts ----
    function showToast(message, type = 'success') {
        if (!message) return;
        const styles = {
            success: { bg: 'bg-green-50', border: 'border-green-500', text: 'text-green-800', icon: '✓', iconBg: 'bg-green-500' },
            error:   { bg: 'bg-red-50',   border: 'border-red-500',   text: 'text-red-800',   icon: '!', iconBg: 'bg-red-500' },
            info:    { bg: 'bg-blue-50',  border: 'border-blue-500',  text: 'text-blue-800',   icon: 'i', iconBg: 'bg-blue-500' },
        };
        const s = styles[type] || styles.success;

        const toast = document.createElement('div');
        toast.className = `${s.bg} border-l-4 ${s.border} ${s.text} p-4 rounded-r-xl shadow-lg flex items-start gap-3 w-full pointer-events-auto transition-all duration-500 opacity-0 -translate-y-2`;

        const iconEl = document.createElement('div');
        iconEl.className = `${s.iconBg} text-white w-6 h-6 rounded-full flex items-center justify-center text-xs font-black shrink-0 mt-0.5`;
        iconEl.textContent = s.icon;

        const textEl = document.createElement('p');
        textEl.className = 'text-sm font-medium flex-1';
        textEl.textContent = message;

        const closeBtn = document.createElement('button');
        closeBtn.className = 'opacity-50 hover:opacity-100 font-bold text-lg leading-none transition ml-2';
        closeBtn.innerHTML = '&times;';
        closeBtn.onclick = () => removeToast(toast);

        toast.append(iconEl, textEl, closeBtn);
        document.getElementById('toast-container').appendChild(toast);

        requestAnimationFrame(() => toast.classList.remove('opacity-0', '-translate-y-2'));
        setTimeout(() => removeToast(toast), 5000);
    }

    function removeToast(toast) {
        toast.classList.add('opacity-0', '-translate-y-2');
        setTimeout(() => toast.remove(), 500);
    }

    // ---- Confirm-modal (vervangt window.confirm) ----
    let _confirmCallback = null;

    function showConfirmModal(message, onConfirm, options = {}) {
        document.getElementById('confirm-modal-text').textContent = message;
        const okBtn = document.getElementById('confirm-modal-ok');
        okBtn.textContent = options.confirmLabel || 'Verwijderen';
        okBtn.className = options.confirmClass || 'flex-1 py-2.5 bg-red-600 text-white rounded-lg text-sm font-bold hover:bg-red-700 transition shadow';
        _confirmCallback = onConfirm;
        document.getElementById('confirm-modal').classList.remove('hidden');
    }

    function closeConfirmModal() {
        document.getElementById('confirm-modal').classList.add('hidden');
        _confirmCallback = null;
    }

    // Gebruik: onsubmit="return confirmSubmit(event, 'Weet je het zeker?')"
    // of op een submit-knop: onclick="return confirmSubmit(event, '...')"
    function confirmSubmit(event, message) {
        event.preventDefault();
        const form = event.target.closest('form');
        showConfirmModal(message, () => form.submit());
        return false;
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('confirm-modal-ok').addEventListener('click', function() {
            if (_confirmCallback) _confirmCallback();
            closeConfirmModal();
        });

        const btn = document.getElementById('mobile-menu-button');
        const menu = document.getElementById('mobile-menu');
        const iconOpen = document.getElementById('menu-icon-open');
        const iconClose = document.getElementById('menu-icon-close');
        let isOpen = false;

        btn.addEventListener('click', () => {
            isOpen = !isOpen;
            if (isOpen) {
                menu.style.maxHeight = "2000px";
                menu.classList.remove('opacity-0');
                menu.classList.add('opacity-100');
                iconOpen.classList.add('hidden');
                iconClose.classList.remove('hidden');
            } else {
                menu.style.maxHeight = "0px";
                menu.classList.remove('opacity-100');
                menu.classList.add('opacity-0');
                iconOpen.classList.remove('hidden');
                iconClose.classList.add('hidden');
            }
        });
    });
</script>
