<?php
require_once 'includes/init.php';

if (is_logged_in()) {
    header('Location: beheer.php');
    exit;
}

$token = $_GET['token'] ?? ($_POST['token'] ?? '');
$error = '';
$success = false;
$valid_user = null;

if ($token !== '') {
    $hashed = hash('sha256', $token);
    $stmt = $pdo->prepare("SELECT id, username FROM users WHERE reset_token = ? AND reset_expires > NOW() AND is_active = 1 LIMIT 1");
    $stmt->execute([$hashed]);
    $valid_user = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    if (!$valid_user) {
        $error = 'Deze link is ongeldig of verlopen. Vraag een nieuwe aan.';
    } else {
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (strlen($new_password) < 8) {
            $error = 'Het nieuwe wachtwoord moet minimaal 8 tekens lang zijn.';
        } elseif ($new_password !== $confirm_password) {
            $error = 'De wachtwoorden komen niet overeen.';
        } else {
            $hash = password_hash($new_password, PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE users SET password_hash = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?")
                ->execute([$hash, $valid_user['id']]);
            $success = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <title>Nieuw wachtwoord instellen - Webius Portaal</title>
    <?php include 'includes/head.php'; ?>
</head>
<body class="bg-gray-50 font-sans flex items-center justify-center min-h-screen relative overflow-hidden">
    <div class="absolute top-0 left-0 w-full h-96 bg-pink-600 transform -skew-y-6 origin-top-left -z-10 shadow-xl opacity-90"></div>
    <div class="bg-white p-10 rounded-2xl shadow-2xl w-full max-w-md border border-gray-100 relative z-10">
        <div class="text-center mb-8">
            <h2 class="text-2xl font-bold text-gray-800 tracking-tight">Nieuw wachtwoord instellen</h2>
        </div>

        <?php if ($success): ?>
            <div class="bg-green-50 border-l-4 border-green-500 text-green-800 p-4 mb-6 text-sm rounded-r-lg">
                Het wachtwoord is bijgewerkt. Er kan nu ingelogd worden met het nieuwe wachtwoord.
            </div>
            <a href="login.php" class="block w-full text-center bg-pink-600 text-white font-bold py-3.5 rounded-xl hover:bg-pink-700 transition-all">Naar inloggen</a>
        <?php elseif (!$valid_user): ?>
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 text-sm rounded-r-lg">
                Deze link is ongeldig of verlopen.
            </div>
            <a href="wachtwoord_vergeten.php" class="block w-full text-center bg-pink-600 text-white font-bold py-3.5 rounded-xl hover:bg-pink-700 transition-all">Nieuwe link aanvragen</a>
        <?php else: ?>
            <?php if ($error): ?>
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 text-sm rounded-r-lg"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form method="POST" action="wachtwoord_resetten.php" class="space-y-6">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2" for="new_password">Nieuw wachtwoord</label>
                    <input type="password" id="new_password" name="new_password" required minlength="8" autocomplete="new-password"
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:ring-2 focus:ring-pink-500 focus:border-pink-500 outline-none transition-all">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2" for="confirm_password">Bevestig nieuw wachtwoord</label>
                    <input type="password" id="confirm_password" name="confirm_password" required autocomplete="new-password"
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:ring-2 focus:ring-pink-500 focus:border-pink-500 outline-none transition-all">
                </div>
                <button type="submit" class="w-full bg-pink-600 text-white font-bold py-3.5 rounded-xl hover:bg-pink-700 transition-all">
                    Wachtwoord bijwerken
                </button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
