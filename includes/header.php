<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['user_uuid'])) {
    header('Location: login.php');
    exit();
}
$admin_username = 'Beheerder';
$admin_email = 'admin@beautytouchbynikki.nl';

$unread_count = 0;
$recent_messages = [];

if (isset($pdo)) {
    $stmt = $pdo->prepare("SELECT username, email FROM users WHERE uuid = ? LIMIT 1");
    $stmt->execute([$_SESSION['user_uuid']]);
    $user = $stmt->fetch();
    
    if ($user) {
        $admin_username = $user['username'];
        $admin_email = !empty($user['email']) ? $user['email'] : 'Geen e-mailadres gekoppeld';
    }
    
    $table_name = 'contact_messages';     
    try {
        $stmt_count = $pdo->query("SELECT COUNT(*) FROM {$table_name} WHERE is_read = 0");
        $unread_count = (int)$stmt_count->fetchColumn();
        $stmt_msgs = $pdo->query("SELECT name, subject, created_at FROM {$table_name} WHERE is_read = 0 ORDER BY created_at DESC LIMIT 5");
        $recent_messages = $stmt_msgs->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Fout bij ophalen contactberichten: " . $e->getMessage());
    }
}
?>
<head>
    <?php include 'head.php'; ?>
</head>

<header class="sticky top-0 z-50 bg-white border-b border-gray-200 shadow-sm transition-all duration-300">
    <div class="w-full px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <div class="flex items-center gap-8">
                <nav class="hidden md:flex space-x-1 lg:space-x-2 items-center">
                    <a href="beheer.php" class="px-3 py-2 rounded-md text-gray-600 hover:text-pink-600 hover:bg-pink-50 font-medium text-sm transition-all duration-200">Dashboard</a>
                    <a href="websitebeheer.php" class="px-3 py-2 rounded-md text-gray-600 hover:text-pink-600 hover:bg-pink-50 font-medium text-sm transition-all duration-200">Homepagina</a>
                    <a href="productbeheer.php" class="px-3 py-2 rounded-md text-gray-600 hover:text-pink-600 hover:bg-pink-50 font-medium text-sm transition-all duration-200">Prijspagina</a>
                    <a href="contactbeheer.php" class="px-3 py-2 rounded-md text-gray-600 hover:text-pink-600 hover:bg-pink-50 font-medium text-sm transition-all duration-200">Contactpagina</a>
                    <div class="relative group">
                        <button class="flex items-center gap-1 px-3 py-2 rounded-md text-gray-600 hover:text-pink-600 hover:bg-pink-50 font-medium text-sm transition-all duration-200 focus:outline-none">
                            Weergave & Info
                            <svg class="h-4 w-4 mt-0.5 transition-transform duration-200 group-hover:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div class="absolute left-0 mt-0 w-56 bg-white rounded-lg shadow-xl border border-gray-100 py-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 transform origin-top-left z-50 translate-y-2 group-hover:translate-y-0">
                            <div class="px-4 py-2 text-xs font-bold text-gray-400 uppercase tracking-wider">Website Instellingen</div>
                            <a href="footerbeheer.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-pink-50 hover:text-pink-600 transition-colors">Footer & Socials</a>
                            <a href="legalbeheer.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-pink-50 hover:text-pink-600 transition-colors">Juridische Documenten</a>
                            <div class="border-t border-gray-100 my-1"></div>
                            <a href="app_instellingen.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-pink-50 hover:text-pink-600 transition-colors">Algemene Instellingen</a>
                        </div>
                    </div>
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
                        <a href="app_instellingen.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Instellingen</a>
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
            <a href="websitebeheer.php" class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium text-gray-700 hover:text-pink-600 hover:bg-pink-50 rounded-lg transition-colors">
                Homepagina
            </a>
            <a href="productbeheer.php" class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium text-gray-700 hover:text-pink-600 hover:bg-pink-50 rounded-lg transition-colors">
                Prijzen
            </a>
            <a href="contactbeheer.php" class="flex items-center justify-between px-3 py-2.5 text-sm font-medium text-gray-700 hover:text-pink-600 hover:bg-pink-50 rounded-lg transition-colors">
                <div class="flex items-center gap-3">
                    Contactpagina
                </div>
                <?php if($unread_count > 0): ?>
                    <span class="bg-pink-100 text-pink-600 py-0.5 px-2 rounded-full text-xs font-bold"><?php echo $unread_count; ?> Nieuw</span>
                <?php endif; ?>
            </a>
            <div class="mt-4 pt-4 border-t border-gray-100">
                <p class="px-3 text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Weergave & Info</p>
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
        </nav>
        <div class="p-4 border-t border-gray-100 bg-gray-50">
            <a href="profielbeheer.php" class="block px-3 py-2 text-sm font-medium text-gray-700 hover:text-pink-600 transition-colors">Mijn Profiel Instellingen</a>
            <a href="includes/logout.php" class="block px-3 py-2 text-sm font-medium text-red-600 hover:text-red-700 transition-colors mt-1">Uitloggen</a>
        </div>
    </div>
</header>

<script>
    document.addEventListener('DOMContentLoaded', function() {
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