-- Voegt de module "verhuizing" toe: een tijdelijke pagina (debandijk/verhuizing.php)
-- met een link in de navigatie, een gele melding op de homepage, uitlegblokken,
-- een FAQ en een klein contactformulier. Een super_admin zet de module per
-- website aan/uit in super_websites.php; de inhoud wordt beheerd op
-- verhuizingbeheer.php.
--
-- 1. Elke website krijgt een website_modules-rij voor 'verhuizing',
--    standaard UIT — ook debandijk. Pas zichtbaar zodra iemand hem aanzet.
-- 2. Tabel verhuizing_items: de uitlegblokken (soort 'uitleg') en de
--    veelgestelde vragen (soort 'faq'), elk met titel/vraag + tekst/antwoord.
-- 3. site_content-rijen voor de vaste teksten van de pagina, plus 'actief'
--    ('1'/'0'): de hoofdschakelaar op verhuizingbeheer.php. Standaard '0',
--    dus ook met de module aan is er pas iets zichtbaar na "tonen".
--    Het contactformulier is aan/uit via is_visible van 'form_title'.
--    Berichten uit het formulier komen in contact_messages (onderwerp
--    "Verhuizing") en zijn dus te lezen in contactbeheer.php.
--
-- Idempotent: opnieuw draaien zet een al ingeschakelde module niet terug
-- op uit en overschrijft geen bewerkte teksten.

INSERT IGNORE INTO website_modules (website_id, module_key, is_enabled)
SELECT w.id, 'verhuizing', 0 FROM websites w;

CREATE TABLE IF NOT EXISTS verhuizing_items (
    id          char(36) NOT NULL,
    website_id  char(36) NOT NULL,
    soort       enum('uitleg','faq') NOT NULL,
    titel       varchar(255) NOT NULL,
    tekst       text NOT NULL,
    sort_order  int NOT NULL DEFAULT 0,
    created_at  timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_verhuizing_items_website (website_id, soort, sort_order),
    CONSTRAINT fk_verhuizing_items_website FOREIGN KEY (website_id) REFERENCES websites(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO site_content (id, website_id, page, section_key, content_text, type, label, group_name, is_visible)
SELECT UUID(), '775d64f8-99a0-11f1-bc37-06bd6669bc40', v.page, v.section_key, v.content_text, v.type, v.label, v.group_name, 1
FROM (
    SELECT 'verhuizing' AS page, 'meta_title' AS section_key, 'Wij verhuizen | Primera de Bandijk Goor' AS content_text, 'text' AS type, 'Titel in de browsertab' AS label, 'pagina' AS group_name
    UNION ALL SELECT 'verhuizing', 'hero_title', 'Wij verhuizen!', 'text', 'Hoofdtitel', 'pagina'
    UNION ALL SELECT 'verhuizing', 'hero_text', 'Primera de Bandijk gaat verhuizen. Hieronder leest u wanneer en waar u ons straks kunt vinden.', 'textarea', 'Introductie', 'pagina'
    UNION ALL SELECT 'verhuizing', 'verhuis_datum', '', 'text', 'Verhuisdatum', 'gegevens'
    UNION ALL SELECT 'verhuizing', 'nieuw_adres', '', 'text', 'Nieuw adres', 'gegevens'
    UNION ALL SELECT 'verhuizing', 'nieuw_plaats', '', 'text', 'Postcode en plaats', 'gegevens'
    UNION ALL SELECT 'verhuizing', 'details_title', 'Wat verandert er voor u?', 'text', 'Kop boven de uitlegblokken', 'uitleg'
    UNION ALL SELECT 'verhuizing', 'faq_title', 'Veelgestelde vragen', 'text', 'Kop boven de veelgestelde vragen', 'faq'
    UNION ALL SELECT 'verhuizing', 'form_title', 'Vraag of opmerking over de verhuizing?', 'text', 'Kop boven het formulier', 'formulier'
    UNION ALL SELECT 'verhuizing', 'form_text', 'Laat hier een bericht achter, wij nemen zo snel mogelijk contact met u op.', 'textarea', 'Tekst boven het formulier', 'formulier'
    UNION ALL SELECT 'verhuizing', 'cta_title', 'Vragen over de verhuizing?', 'text', 'Titel onderaan', 'pagina'
    UNION ALL SELECT 'verhuizing', 'cta_text', 'Loop gerust binnen of neem contact met ons op, wij helpen u graag.', 'textarea', 'Tekst onderaan', 'pagina'
    UNION ALL SELECT 'verhuizing', 'actief', '0', 'text', 'Verhuizing tonen op de website', 'status'
    UNION ALL SELECT 'verhuizing', 'banner_text', 'Wij verhuizen! Lees meer', 'text', 'Tekst van de gele balk', 'melding'
) v
ON DUPLICATE KEY UPDATE site_content.id = site_content.id;
