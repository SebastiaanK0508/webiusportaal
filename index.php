<?php
session_start();
include 'includes/init.php';

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header("Location: websitebeheer.php");
    exit();
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $gebruikersnaam = trim($_POST['username'] ?? '');
    $wachtwoord = $_POST['password'] ?? '';

    if (empty($gebruikersnaam) || empty($wachtwoord)) {
        $error = "Vul alstublieft beide velden in.";
    } else {
        $stmt = $pdo->prepare("SELECT uuid, password_hash FROM users WHERE username = ? LIMIT 1");
        $stmt->execute([$gebruikersnaam]);
        $user = $stmt->fetch();
        if ($user && password_verify($wachtwoord, $user['password_hash'])) {
        session_regenerate_id(true);
            $_SESSION['logged_in'] = true;
            $_SESSION['user_uuid'] = $user['uuid'];
            $update_stmt = $pdo->prepare("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE uuid = ?");
            $update_stmt->execute([$user['uuid']]);
            
            header("Location: beheer.php");
            exit();
        } else {
            $error = "Ongeldige gebruikersnaam of wachtwoord.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <title>Inloggen - Beauty Touch Beheer</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Montserrat', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen selection:bg-pink-200 selection:text-pink-900 relative overflow-hidden">
    <div class="absolute top-0 left-0 w-full h-96 bg-pink-600 transform -skew-y-6 origin-top-left -z-10 shadow-xl opacity-90"></div>
    <div class="absolute bottom-0 right-0 w-96 h-96 bg-pink-200 rounded-full blur-3xl -z-10 opacity-30 transform translate-x-1/2 translate-y-1/2"></div>
    <div class="bg-white p-10 rounded-2xl shadow-2xl w-full max-w-md border border-gray-100 relative z-10">
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-pink-100 text-pink-600 rounded-full flex items-center justify-center mx-auto mb-4 shadow-sm border border-pink-200">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8V7a4 4 0 00-8 0v4h8z"></path></svg>
            </div>
            <h2 class="text-3xl font-bold text-gray-800 tracking-tight">Beheerpaneel</h2>
            <p class="text-sm text-gray-500 mt-2 font-medium uppercase tracking-widest">Beauty Touch by Nikki</p>
        </div>
        <?php if ($error): ?>
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 text-sm flex items-center gap-3 rounded-r-lg" role="alert">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="font-medium"><?php echo $error; ?></span>
            </div>
        <?php endif; ?>
        <form method="POST" action="index.php" class="space-y-6">
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2" for="username">Gebruikersnaam</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    </div>
                    <input type="text" id="username" name="username" required 
                           class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:ring-2 focus:ring-pink-500 focus:border-pink-500 outline-none transition-all text-gray-700 font-medium placeholder-gray-400"
                           placeholder="Jouw gebruikersnaam" autocomplete="username">
                </div>
            </div>
            <div>
                <div class="flex justify-between items-center mb-2">
                    <label class="block text-sm font-bold text-gray-700" for="password">Wachtwoord</label>
                    <a href="wachtwoord_vergeten.php" class="text-xs font-bold text-pink-600 hover:text-pink-700 transition-colors">
                        Wachtwoord vergeten?
                    </a>
                </div>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                    </div>
                    <input type="password" id="password" name="password" required 
                           class="w-full pl-10 pr-12 py-3 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:ring-2 focus:ring-pink-500 focus:border-pink-500 outline-none transition-all text-gray-700 font-medium placeholder-gray-400"
                           placeholder="••••••••" autocomplete="current-password">
                           
                    <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-pink-600 transition-colors focus:outline-none">
                        <svg id="eye-icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                    </button>
                </div>
            </div>
            <button type="submit" class="w-full bg-pink-600 text-white font-bold py-3.5 rounded-xl hover:bg-pink-700 hover:shadow-lg hover:shadow-pink-500/30 transform hover:-translate-y-0.5 transition-all duration-200 mt-4 flex justify-center items-center gap-2">
                Veilig Inloggen
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
            </button>
        </form>
        <div class="mt-8 text-center border-t border-gray-100 pt-6">
            <a href="index.php" class="text-sm text-gray-500 hover:text-pink-600 font-medium transition-colors flex items-center justify-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Terug naar de website
            </a>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>';
            } else {
                passwordInput.type = 'password';
                eyeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>';
            }
        }
    </script>
</body>
</html>