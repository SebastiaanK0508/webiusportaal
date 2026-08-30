<?php
// "Onthoud mij" op login.php: een losstaand cookie (selector:validator),
// niet de PHP-sessie zelf verlengen. Alleen een hash van de validator staat
// in de database (zelfde principe als password_hash) en elk gebruik van het
// cookie rot direct een nieuwe token uit (sliding expiration) — een gestolen
// cookie werkt dus maar één keer voordat de rechtmatige gebruiker hem
// (bij zijn eigen volgende bezoek) ongeldig maakt.

const REMEMBER_COOKIE_NAME = 'webius_remember';
const REMEMBER_TTL_DAYS = 30;

function remember_cookie_params(): array
{
    return [
        'path'     => '/',
        'domain'   => '',
        'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? null) == 443,
        'httponly' => true,
        'samesite' => 'Lax',
    ];
}

// Zet een nieuw token in de database en stuurt het bijbehorende cookie mee.
function issue_remember_token(string $user_id): void
{
    global $pdo;
    try {
        $selector  = bin2hex(random_bytes(12));
        $validator = bin2hex(random_bytes(32));
        $hash      = hash('sha256', $validator);
        $expires   = date('Y-m-d H:i:s', time() + REMEMBER_TTL_DAYS * 86400);

        $stmt = $pdo->prepare("INSERT INTO remember_tokens (user_id, selector, validator_hash, expires_at) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $selector, $hash, $expires]);

        $params = remember_cookie_params();
        setcookie(REMEMBER_COOKIE_NAME, $selector . ':' . $validator, [
            'expires'  => time() + REMEMBER_TTL_DAYS * 86400,
            'path'     => $params['path'],
            'domain'   => $params['domain'],
            'secure'   => $params['secure'],
            'httponly' => $params['httponly'],
            'samesite' => $params['samesite'],
        ]);
    } catch (PDOException $e) {
        // migratie nog niet toegepast; "onthoud mij" staat dan simpelweg uit
    }
}

// Verwijdert het huidige cookie + de bijbehorende databaserij (uitloggen).
function forget_remember_token(): void
{
    global $pdo;
    if (!empty($_COOKIE[REMEMBER_COOKIE_NAME])) {
        [$selector] = array_pad(explode(':', $_COOKIE[REMEMBER_COOKIE_NAME], 2), 1, '');
        if ($selector !== '') {
            try {
                $stmt = $pdo->prepare("DELETE FROM remember_tokens WHERE selector = ?");
                $stmt->execute([$selector]);
            } catch (PDOException $e) {
                // tabel bestaat nog niet; niets te verwijderen
            }
        }
        $params = remember_cookie_params();
        setcookie(REMEMBER_COOKIE_NAME, '', [
            'expires'  => time() - 42000,
            'path'     => $params['path'],
            'domain'   => $params['domain'],
            'secure'   => $params['secure'],
            'httponly' => $params['httponly'],
            'samesite' => $params['samesite'],
        ]);
    }
    unset($_COOKIE[REMEMBER_COOKIE_NAME]);
}

// Trekt alle "onthoud mij"-tokens van een gebruiker in, bv. na een
// wachtwoordwijziging — zodat een oud gelekt cookie daarna niets meer waard is.
function forget_all_remember_tokens_for_user(string $user_id): void
{
    global $pdo;
    try {
        $stmt = $pdo->prepare("DELETE FROM remember_tokens WHERE user_id = ?");
        $stmt->execute([$user_id]);
    } catch (PDOException $e) {
        // tabel bestaat nog niet
    }
}

// Wordt bij elke pageload vanuit init.php aangeroepen zolang er nog geen
// sessie is. Bij een geldig cookie wordt de gebruiker automatisch ingelogd
// en rot het token meteen door (oude rij weg, nieuwe rij + nieuw cookie).
function attempt_remember_login(): void
{
    global $pdo;
    if (is_logged_in() || empty($_COOKIE[REMEMBER_COOKIE_NAME]) || !isset($pdo)) {
        return;
    }

    $parts = explode(':', $_COOKIE[REMEMBER_COOKIE_NAME], 2);
    if (count($parts) !== 2) {
        forget_remember_token();
        return;
    }
    [$selector, $validator] = $parts;

    try {
        $stmt = $pdo->prepare("SELECT * FROM remember_tokens WHERE selector = ? LIMIT 1");
        $stmt->execute([$selector]);
        $token = $stmt->fetch();
    } catch (PDOException $e) {
        return; // migratie nog niet toegepast
    }

    if (!$token || strtotime($token['expires_at']) < time() || !hash_equals($token['validator_hash'], hash('sha256', $validator))) {
        forget_remember_token();
        return;
    }

    $user_stmt = $pdo->prepare("SELECT id, website_id, role, is_active FROM users WHERE id = ? LIMIT 1");
    $user_stmt->execute([$token['user_id']]);
    $user = $user_stmt->fetch();

    // Token direct opruimen, ook als inloggen hierna niet doorgaat: elk
    // token is maar één keer bruikbaar (voorkomt herhaald misbruik van een
    // onderschepte cookie-waarde).
    $del = $pdo->prepare("DELETE FROM remember_tokens WHERE id = ?");
    $del->execute([$token['id']]);

    if (!$user || (int)$user['is_active'] !== 1) {
        forget_remember_token();
        return;
    }

    session_regenerate_id(true);
    $_SESSION['logged_in']  = true;
    $_SESSION['user_id']    = $user['id'];
    $_SESSION['role']       = $user['role'];
    $_SESSION['website_id'] = $user['website_id'];

    issue_remember_token($user['id']);
}
