<?php
require_once 'includes/init.php';
require_login();

$message = '';
$msg_type = 'success';
$error = '';

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
$stmt->execute([current_user_id()]);
$profile_user = $stmt->fetch();

if (!$profile_user) {
    header('Location: includes/logout.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        csrf_verify();
        $new_username = trim($_POST['username'] ?? '');
        $new_email = trim($_POST['email'] ?? '');

        if (empty($new_username)) {
            $error = "Gebruikersnaam mag niet leeg zijn.";
        } else {
            $check_stmt = $pdo->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
            $check_stmt->execute([$new_username, $new_email, current_user_id()]);

            if ($check_stmt->rowCount() > 0) {
                $error = "Deze gebruikersnaam of dit e-mailadres is al in gebruik.";
            } else {
                $update_stmt = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
                if ($update_stmt->execute([$new_username, $new_email, current_user_id()])) {
                    $message = "Je profielgegevens zijn succesvol bijgewerkt!";
                    $msg_type = 'success';
                    $profile_user['username'] = $new_username;
                    $profile_user['email'] = $new_email;
                } else {
                    $error = "Er ging iets mis bij het opslaan.";
                }
            }
        }
    }
    if (isset($_POST['update_password'])) {
        csrf_verify();
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $error = "Vul alle wachtwoordvelden in.";
        } elseif ($new_password !== $confirm_password) {
            $error = "De nieuwe wachtwoorden komen niet overeen.";
        } elseif (strlen($new_password) < 8) {
            $error = "Het nieuwe wachtwoord moet minimaal 8 tekens lang zijn.";
        } elseif (!password_verify($current_password, $profile_user['password_hash'])) {
            $error = "Je huidige wachtwoord is onjuist.";
        } else {
            $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $update_pw_stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");

            if ($update_pw_stmt->execute([$new_hash, current_user_id()])) {
                forget_all_remember_tokens_for_user(current_user_id());
                $message = "Je wachtwoord is succesvol gewijzigd!";
                $msg_type = 'success';
            } else {
                $error = "Er ging iets mis bij het wijzigen van je wachtwoord.";
            }
        }
    }
}

$role_label = $profile_user['role'] === 'super_admin' ? 'Super Admin' : 'Beheerder';
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <title>Mijn Profiel - Webius Portaal</title>
    <?php include 'includes/head.php'; ?>
</head>
<body class="bg-gray-100 font-sans pb-24">
    <?php include 'includes/header.php'; ?>

    <div class="max-w-6xl mx-auto px-4 mt-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
            <h1 class="text-3xl font-bold text-gray-800">Mijn Profiel</h1>
        </div>

        <?php if ($message): ?>
            <div id="alert-message" class="bg-green-50 border-l-4 border-green-500 text-green-800 p-4 mb-6 shadow-sm rounded-r-lg flex justify-between items-center transition-all duration-500 transform translate-y-0 opacity-100">
                <div class="flex items-center gap-3">
                    <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="font-medium"><?php echo htmlspecialchars($message); ?></span>
                </div>
                <button onclick="closeAlert()" class="text-green-500 hover:text-green-800 hover:bg-green-100 rounded-lg p-1.5 focus:outline-none transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div id="error-message" class="bg-red-50 border-l-4 border-red-500 text-red-800 p-4 mb-6 shadow-sm rounded-r-lg flex justify-between items-center transition-all duration-500 transform translate-y-0 opacity-100">
                <div class="flex items-center gap-3">
                    <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="font-medium"><?php echo htmlspecialchars($error); ?></span>
                </div>
                <button onclick="document.getElementById('error-message').style.display='none'" class="text-red-500 hover:text-red-800 hover:bg-red-100 rounded-lg p-1.5 focus:outline-none transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <div class="space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="h-24 bg-gradient-to-r from-pink-400 to-pink-600"></div>
                    <div class="px-6 pb-6 relative">
                        <div class="flex justify-center -mt-12 mb-4">
                            <div class="w-24 h-24 bg-white p-1 rounded-full shadow-lg">
                                <img class="w-full h-full rounded-full object-cover" src="https://ui-avatars.com/api/?name=<?php echo urlencode($profile_user['username'] ?? 'Beheerder'); ?>&background=fce7f3&color=db2777&size=128" alt="Profiel Avatar">
                            </div>
                        </div>
                        <div class="text-center mb-6">
                            <h2 class="text-xl font-bold text-gray-900"><?php echo htmlspecialchars($profile_user['username'] ?? 'Onbekend'); ?></h2>
                            <p class="text-gray-500 text-sm font-medium"><?php echo htmlspecialchars($profile_user['email'] ?? 'Geen e-mailadres ingesteld'); ?></p>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-pink-50 text-pink-700 text-xs font-bold uppercase tracking-wide mt-3 border border-pink-100">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                                <?php echo htmlspecialchars($role_label); ?>
                            </span>
                        </div>

                        <div class="border-t border-gray-100 pt-4 space-y-3">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Account aangemaakt</span>
                                <span class="font-medium text-gray-900"><?php echo !empty($profile_user['created_at']) ? date('d-m-Y', strtotime($profile_user['created_at'])) : 'Onbekend'; ?></span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Laatst ingelogd</span>
                                <span class="font-medium text-gray-900"><?php echo !empty($profile_user['last_login']) ? date('d-m-Y H:i', strtotime($profile_user['last_login'])) : 'Nooit'; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-2 space-y-6">

                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                        <div class="p-2 bg-pink-50 rounded-lg text-pink-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                        <h2 class="text-lg font-bold text-gray-800">Persoonlijke Gegevens</h2>
                    </div>
                    <div class="p-6">
                        <form action="profielbeheer.php" method="POST" class="space-y-5">
                            <?php echo csrf_field(); ?>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <label for="username" class="block text-sm font-bold text-gray-700 mb-1.5">Gebruikersnaam</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                        </div>
                                        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($profile_user['username'] ?? ''); ?>" required class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-pink-500 focus:border-pink-500 outline-none transition-all text-sm font-medium">
                                    </div>
                                </div>

                                <div>
                                    <label for="email" class="block text-sm font-bold text-gray-700 mb-1.5">E-mailadres</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                        </div>
                                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($profile_user['email'] ?? ''); ?>" placeholder="naam@domein.nl" class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-pink-500 focus:border-pink-500 outline-none transition-all text-sm font-medium">
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-end pt-2">
                                <button type="submit" name="update_profile" class="bg-slate-800 text-white font-bold py-2.5 px-6 rounded-lg hover:bg-slate-700 shadow-sm transition-all transform hover:-translate-y-0.5 text-sm flex items-center gap-2">
                                    Gegevens Opslaan
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                        <div class="p-2 bg-pink-50 rounded-lg text-pink-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8V7a4 4 0 00-8 0v4h8z"></path></svg>
                        </div>
                        <h2 class="text-lg font-bold text-gray-800">Wachtwoord Wijzigen</h2>
                    </div>
                    <div class="p-6">
                        <form action="profielbeheer.php" method="POST" class="space-y-5">
                            <?php echo csrf_field(); ?>
                            <div>
                                <label for="current_password" class="block text-sm font-bold text-gray-700 mb-1.5">Huidig Wachtwoord</label>
                                <div class="relative max-w-md">
                                    <input type="password" id="current_password" name="current_password" required autocomplete="current-password" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-pink-500 focus:border-pink-500 outline-none transition-all text-sm">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 border-t border-gray-100 pt-5">
                                <div>
                                    <label for="new_password" class="block text-sm font-bold text-gray-700 mb-1.5">Nieuw Wachtwoord</label>
                                    <div class="relative">
                                        <input type="password" id="new_password" name="new_password" required minlength="8" autocomplete="new-password" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-pink-500 focus:border-pink-500 outline-none transition-all text-sm">
                                        <p class="text-xs text-gray-500 mt-1.5">Minimaal 8 tekens lang.</p>
                                    </div>
                                </div>

                                <div>
                                    <label for="confirm_password" class="block text-sm font-bold text-gray-700 mb-1.5">Bevestig Nieuw Wachtwoord</label>
                                    <div class="relative">
                                        <input type="password" id="confirm_password" name="confirm_password" required autocomplete="new-password" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-pink-500 focus:border-pink-500 outline-none transition-all text-sm">
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-end pt-2">
                                <button type="submit" name="update_password" class="bg-pink-600 text-white font-bold py-2.5 px-6 rounded-lg hover:bg-pink-700 shadow-md shadow-pink-600/20 transition-all transform hover:-translate-y-0.5 text-sm flex items-center gap-2">
                                    Wachtwoord Bijwerken
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
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
        if(document.getElementById('alert-message')) {
            setTimeout(closeAlert, 5000);
        }
    </script>
</body>
</html>
