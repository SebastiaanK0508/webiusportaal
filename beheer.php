<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['user_uuid'])) {
    header('Location: login.php');
    exit();
}
require_once 'includes/functions.php';
$stmt = $pdo->prepare("SELECT username FROM users WHERE id = ? LIMIT 1");
$stmt->execute([$_SESSION['user_uuid']]);
$user = $stmt->fetch();
$username = $user ? $user['username'] : 'Beheerder';
$stats = [
    'portfolio' => 0,
    'products'  => 0,
    'reviews'   => 0,
    'messages'  => 0
];

try {
    $stats['portfolio'] = count(get_portfolio());
    $stats['products']  = count(get_all_products_admin());
    $stats['reviews']   = count(get_reviews());
    $stats['messages']  = count(get_messages());
} catch (Throwable $e) {
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <title>Dashboard - Beauty Touch by Nikki</title>
    <?php include 'includes/head.php'; ?>
</head>
<body class="bg-gray-100 font-sans pb-24">
    <?php include 'includes/header.php'; ?>
    <div class="max-w-6xl mx-auto px-4 mt-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Welkom terug, <?php echo htmlspecialchars($username); ?>! 👋</h1>
            <p class="text-gray-500 mt-2">Dit is je overzicht. Kies een module om je website te beheren.</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex items-center gap-4 transition-transform hover:-translate-y-1 duration-300">
                <div class="w-14 h-14 rounded-full bg-pink-50 flex items-center justify-center text-pink-600 flex-shrink-0">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
                <div>
                    <span class="block text-2xl font-bold text-gray-900"><?php echo $stats['portfolio']; ?></span>
                    <span class="text-sm font-medium text-gray-500">Portfolio Items</span>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex items-center gap-4 transition-transform hover:-translate-y-1 duration-300">
                <div class="w-14 h-14 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600 flex-shrink-0">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879M12 12L9.121 9.121m0 5.758a3 3 0 10-4.243 4.243 3 3 0 004.243-4.243zm0-5.758a3 3 0 10-4.243-4.243 3 3 0 004.243 4.243z"></path></svg>
                </div>
                <div>
                    <span class="block text-2xl font-bold text-gray-900"><?php echo $stats['products']; ?></span>
                    <span class="text-sm font-medium text-gray-500">Behandelingen</span>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex items-center gap-4 transition-transform hover:-translate-y-1 duration-300">
                <div class="w-14 h-14 rounded-full bg-amber-50 flex items-center justify-center text-amber-500 flex-shrink-0">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
                </div>
                <div>
                    <span class="block text-2xl font-bold text-gray-900"><?php echo $stats['reviews']; ?></span>
                    <span class="text-sm font-medium text-gray-500">Reviews</span>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex items-center gap-4 transition-transform hover:-translate-y-1 duration-300">
                <div class="w-14 h-14 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-600 flex-shrink-0">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div>
                    <span class="block text-2xl font-bold text-gray-900"><?php echo $stats['messages']; ?></span>
                    <span class="text-sm font-medium text-gray-500">Berichten</span>
                </div>
            </div>
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
                        <a href="websitebeheer.php?tab=portfolio" class="group flex items-start p-5 border border-gray-200 rounded-xl hover:border-pink-500 hover:shadow-md transition-all duration-300 bg-white">
                            <div class="p-3 bg-gray-50 rounded-lg group-hover:bg-pink-50 group-hover:text-pink-600 transition-colors mr-4 text-gray-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </div>
                            <div>
                                <h3 class="text-md font-bold text-gray-900 group-hover:text-pink-600 transition-colors">Portfolio</h3>
                                <p class="text-sm text-gray-500 mt-1">Foto's toevoegen of verwijderen uit je inspiratie galerij.</p>
                            </div>
                        </a>
                        <a href="productbeheer.php" class="group flex items-start p-5 border border-gray-200 rounded-xl hover:border-pink-500 hover:shadow-md transition-all duration-300 bg-white">
                            <div class="p-3 bg-gray-50 rounded-lg group-hover:bg-pink-50 group-hover:text-pink-600 transition-colors mr-4 text-gray-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                            </div>
                            <div>
                                <h3 class="text-md font-bold text-gray-900 group-hover:text-pink-600 transition-colors">Behandelingen</h3>
                                <p class="text-sm text-gray-500 mt-1">Beheer je diensten, beschrijvingen en de tarievenlijst.</p>
                            </div>
                        </a>
                        <a href="legalbeheer.php" class="group flex items-start p-5 border border-gray-200 rounded-xl hover:border-pink-500 hover:shadow-md transition-all duration-300 bg-white">
                            <div class="p-3 bg-gray-50 rounded-lg group-hover:bg-pink-50 group-hover:text-pink-600 transition-colors mr-4 text-gray-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                            </div>
                            <div>
                                <h3 class="text-md font-bold text-gray-900 group-hover:text-pink-600 transition-colors">Legals</h3>
                                <p class="text-sm text-gray-500 mt-1">Beheer je Juridische teksten aan.</p>
                            </div>
                        </a>
                        <a href="websitebeheer.php" class="group flex items-start p-5 border border-gray-200 rounded-xl hover:border-pink-500 hover:shadow-md transition-all duration-300 bg-white">
                            <div class="p-3 bg-gray-50 rounded-lg group-hover:bg-pink-50 group-hover:text-pink-600 transition-colors mr-4 text-gray-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            </div>
                            <div>
                                <h3 class="text-md font-bold text-gray-900 group-hover:text-pink-600 transition-colors">Teksten</h3>
                                <p class="text-sm text-gray-500 mt-1">Pas algemene teksten aan.</p>
                            </div>
                        </a>
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
                            <span class="text-xs font-bold text-gray-900">v1.2</span>
                        </div>
                    </div>
                </div>
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl shadow-sm border border-slate-700 p-6 text-white relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-4 opacity-10">
                        <svg class="w-24 h-24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h3 class="text-lg font-bold mb-2 relative z-10">Mijn Profiel</h3>
                    <p class="text-slate-300 text-sm mb-5 relative z-10">Je kunt je gebruikersnaam, e-mailadres en wachtwoord veilig aanpassen in je profiel instellingen.</p>
                    <a href="profiel.php" class="inline-flex items-center gap-2 text-sm font-bold bg-white/10 hover:bg-white/20 px-4 py-2 rounded-lg transition-colors relative z-10 text-white">
                        Profiel beheren
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>