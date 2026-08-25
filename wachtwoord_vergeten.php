<?php
require_once 'includes/init.php';

if (is_logged_in()) {
    header('Location: beheer.php');
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $identifier = trim($_POST['identifier'] ?? '');

    if ($identifier !== '') {
        $stmt = $pdo->prepare("SELECT id, username, email FROM users WHERE (username = ? OR email = ?) AND is_active = 1 LIMIT 1");
        $stmt->execute([$identifier, $identifier]);
        $user = $stmt->fetch();

        if ($user && !empty($user['email'])) {
            $raw_token = bin2hex(random_bytes(32));
            $hashed_token = hash('sha256', $raw_token);
            $expires = date('Y-m-d H:i:s', time() + 3600);

            $pdo->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?")
                ->execute([$hashed_token, $expires, $user['id']]);

            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $base = $scheme . '://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
            $link = $base . '/wachtwoord_resetten.php?token=' . urlencode($raw_token);

            $subject = 'Wachtwoord resetten - Webius Portaal';
            $body = "Hoi {$user['username']},\n\nEr is een verzoek gedaan om het wachtwoord van dit account te resetten.\n\nKlik op onderstaande link om een nieuw wachtwoord in te stellen (1 uur geldig):\n{$link}\n\nHeb je dit niet zelf aangevraagd? Dan kun je dit e-mailbericht negeren.";
            $headers = 'From: no-reply@' . preg_replace('/^www\./', '', $_SERVER['HTTP_HOST']);

            @mail($user['email'], $subject, $body, $headers);
        }
    }

    // Altijd dezelfde melding tonen, ongeacht of het account bestaat, om te
    // voorkomen dat dit formulier gebruikt kan worden om accounts te raden.
    $message = 'Als dit account bekend is, is er een link naar het gekoppelde e-mailadres verstuurd.';
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <title>Wachtwoord vergeten - Webius Portaal</title>
    <?php include 'includes/head.php'; ?>
</head>
<body class="bg-gray-50 font-sans flex items-center justify-center min-h-screen relative overflow-hidden">
    <div class="absolute top-0 left-0 w-full h-96 bg-pink-600 transform -skew-y-6 origin-top-left -z-10 shadow-xl opacity-90"></div>
    <div class="bg-white p-10 rounded-2xl shadow-2xl w-full max-w-md border border-gray-100 relative z-10">
        <div class="text-center mb-8">
            <h2 class="text-2xl font-bold text-gray-800 tracking-tight">Wachtwoord vergeten?</h2>
            <p class="text-sm text-gray-500 mt-2">Vul de gebruikersnaam of het e-mailadres van het account in.</p>
        </div>
        <?php if ($message): ?>
            <div class="bg-blue-50 border-l-4 border-blue-500 text-blue-800 p-4 mb-6 text-sm rounded-r-lg">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        <form method="POST" action="wachtwoord_vergeten.php" class="space-y-6">
            <?php echo csrf_field(); ?>
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2" for="identifier">Gebruikersnaam of e-mailadres</label>
                <input type="text" id="identifier" name="identifier" required
                       class="w-full px-4 py-3 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:ring-2 focus:ring-pink-500 focus:border-pink-500 outline-none transition-all text-gray-700 font-medium">
            </div>
            <button type="submit" class="w-full bg-pink-600 text-white font-bold py-3.5 rounded-xl hover:bg-pink-700 transition-all">
                Reset-link versturen
            </button>
        </form>
        <div class="mt-8 text-center border-t border-gray-100 pt-6">
            <a href="login.php" class="text-sm text-gray-500 hover:text-pink-600 font-medium transition-colors">
                &larr; Terug naar inloggen
            </a>
        </div>
    </div>
</body>
</html>
