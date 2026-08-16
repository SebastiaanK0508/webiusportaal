-- Voegt de tabel toe die includes/rate_limit.php gebruikt om herhaalde
-- mislukte inlogpogingen te herkennen en tijdelijk te blokkeren.
-- Veilig om te draaien: CREATE TABLE IF NOT EXISTS doet niets als de tabel
-- al bestaat.

CREATE TABLE IF NOT EXISTS login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    success TINYINT(1) NOT NULL DEFAULT 0,
    attempted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_login_attempts_username (username),
    INDEX idx_login_attempts_ip (ip_address),
    INDEX idx_login_attempts_attempted_at (attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
