<?php
const LOGIN_MAX_ATTEMPTS = 5;
const LOGIN_LOCKOUT_MINUTES = 15;

// Deze functies raken de `login_attempts`-tabel, die pas bestaat nadat
// migrate/001_login_attempts.sql is uitgevoerd. Tot die tijd faalt dat
// stilzwijgend (rate limiting staat dan simpelweg nog uit) zodat inloggen
// zelf nooit kapot gaat door een migratie die nog niet is toegepast.

function is_login_locked_out(string $username): bool
{
    global $pdo;
    try {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM login_attempts
             WHERE success = 0
               AND attempted_at > (NOW() - INTERVAL " . LOGIN_LOCKOUT_MINUTES . " MINUTE)
               AND (username = ? OR ip_address = ?)"
        );
        $stmt->execute([$username, $ip]);
        return (int)$stmt->fetchColumn() >= LOGIN_MAX_ATTEMPTS;
    } catch (PDOException $e) {
        return false;
    }
}

function register_login_attempt(string $username, bool $success): void
{
    global $pdo;
    try {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $stmt = $pdo->prepare("INSERT INTO login_attempts (username, ip_address, success) VALUES (?, ?, ?)");
        $stmt->execute([$username, $ip, $success ? 1 : 0]);
        if ($success) {
            $del = $pdo->prepare("DELETE FROM login_attempts WHERE username = ? AND success = 0");
            $del->execute([$username]);
        }
    } catch (PDOException $e) {
        // migratie nog niet toegepast; niets te loggen
    }
}
