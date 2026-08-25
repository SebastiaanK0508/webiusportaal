-- Voegt de "module"-gating toe: per website kan worden ingesteld welke
-- site-specifieke onderdelen (assortiment, cadeaukaarten, nieuws, prijsvraag,
-- geschiedenis) actief zijn. Dit is het mechanisme achter "pagina's die
-- alleen voor deze website zijn" — een module die uit staat, is nergens in
-- de navigatie zichtbaar en de bijbehorende beheerpagina weigert toegang
-- (zie includes/functions.php: module_enabled()).
--
-- Invariant: elke website heeft altijd precies 5 rijen (één per module_key).
-- Nooit "rij ontbreekt = uit" gebruiken — dat maakt de checkbox-UI in
-- super_websites.php eenduidig en module_enabled() een simpele lookup.
--
-- Veilig om te draaien: CREATE TABLE IF NOT EXISTS, raakt geen bestaande data.

CREATE TABLE IF NOT EXISTS website_modules (
    website_id  char(36) NOT NULL,
    module_key  varchar(50) NOT NULL,
    is_enabled  tinyint(1) NOT NULL DEFAULT 0,
    updated_at  timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (website_id, module_key),
    CONSTRAINT fk_website_modules_website FOREIGN KEY (website_id) REFERENCES websites(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Seed: alle bestaande websites krijgen hun 5 rijen. Primera de Bandijk
-- (de enige site die deze modules vandaag nodig heeft) krijgt ze ingeschakeld;
-- overige sites (bijv. webius.nl) krijgen ze uitgeschakeld totdat een
-- super_admin ze via super_websites.php bewust aanzet.
INSERT IGNORE INTO website_modules (website_id, module_key, is_enabled)
SELECT w.id, k.module_key, IF(w.id = '775d64f8-99a0-11f1-bc37-06bd6669bc40', 1, 0)
FROM websites w
CROSS JOIN (
    SELECT 'assortiment' AS module_key
    UNION ALL SELECT 'cadeaukaarten'
    UNION ALL SELECT 'nieuws'
    UNION ALL SELECT 'prijsvraag'
    UNION ALL SELECT 'geschiedenis'
) k;
