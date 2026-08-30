-- Voegt de tabel toe die includes/remember_me.php gebruikt voor de
-- "Onthoud mij"-optie op login.php. Er wordt nooit de ruwe cookie-waarde
-- opgeslagen, alleen een hash daarvan (zelfde principe als password_hash) —
-- lekken van de database geeft dus geen bruikbare login-cookies.
-- Veilig om te draaien: CREATE TABLE IF NOT EXISTS doet niets als de tabel
-- al bestaat.

CREATE TABLE IF NOT EXISTS remember_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id CHAR(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    selector VARCHAR(24) NOT NULL,
    validator_hash VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_remember_selector (selector),
    KEY idx_remember_user (user_id),
    CONSTRAINT fk_remember_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
