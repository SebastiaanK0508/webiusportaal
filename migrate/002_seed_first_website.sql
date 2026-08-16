-- ============================================================================
-- STAP 0 — VERPLICHT: maak eerst een volledige databasebackup (mysqldump of via
-- het hostingpaneel) voordat je hier iets van uitvoert. Dit script wijzigt
-- productiedata.
-- ============================================================================
--
-- Waarom dit script nodig is:
-- De tabellen (site_content, products, categories, portfolio, usps, reviews,
-- faqs, contact_messages, legals, footer, app_settings, users) hebben allemaal
-- een NOT NULL kolom `website_id`, maar er bestaat nog geen rij in de
-- `websites`-tabel om naar te verwijzen. De applicatie (websitebeheer.php,
-- productbeheer.php, enz.) filtert nu overal op `website_id`, dus zonder een
-- geldige `websites`-rij die overeenkomt met de bestaande `website_id`-waarden
-- toont de portal straks een lege boel in plaats van de huidige content.
--
-- ============================================================================
-- STAP 1 — INSPECTEREN: welke website_id-waarde(n) staan er nu al in de data?
-- ============================================================================
-- Omdat `website_id` NOT NULL is, moet elke bestaande rij al een waarde hebben
-- (vermoedelijk ingevuld met een vaste DEFAULT toen de kolom is toegevoegd).
-- Draai onderstaande query en kijk of er precies ÉÉN website_id-waarde
-- consistent terugkomt over alle tabellen:

SELECT 'site_content' AS tabel, website_id, COUNT(*) AS aantal FROM site_content GROUP BY website_id
UNION ALL SELECT 'categories', website_id, COUNT(*) FROM categories GROUP BY website_id
UNION ALL SELECT 'products', website_id, COUNT(*) FROM products GROUP BY website_id
UNION ALL SELECT 'portfolio', website_id, COUNT(*) FROM portfolio GROUP BY website_id
UNION ALL SELECT 'usps', website_id, COUNT(*) FROM usps GROUP BY website_id
UNION ALL SELECT 'reviews', website_id, COUNT(*) FROM reviews GROUP BY website_id
UNION ALL SELECT 'faqs', website_id, COUNT(*) FROM faqs GROUP BY website_id
UNION ALL SELECT 'contact_messages', website_id, COUNT(*) FROM contact_messages GROUP BY website_id
UNION ALL SELECT 'legals', website_id, COUNT(*) FROM legals GROUP BY website_id
UNION ALL SELECT 'footer', website_id, COUNT(*) FROM footer GROUP BY website_id
UNION ALL SELECT 'app_settings', website_id, COUNT(*) FROM app_settings GROUP BY website_id
UNION ALL SELECT 'users', website_id, COUNT(*) FROM users GROUP BY website_id;

-- Controleer ook of er al een `websites`-rij bestaat met die waarde
-- (vervang <uuid> door wat je in stap 1 zag):
SELECT * FROM websites WHERE id = '<uuid>';

-- ============================================================================
-- STAP 2A — als Stap 1 ÉÉN consistente, bestaande website_id opleverde en er
-- nog GEEN bijbehorende `websites`-rij bestaat: gebruik gewoon die waarde.
-- Vervang '<uuid-uit-stap-1>' hieronder en pas domeinnaam/bedrijfsnaam aan.
-- ============================================================================

-- INSERT INTO websites (id, domain_name, company_name, template_key, is_active)
-- VALUES ('', 'primeradebandijk.nl', 'Primera de Bandijk', 'default', 1);

-- ============================================================================
-- STAP 2B — ALTERNATIEF: alleen gebruiken als Stap 1 GEEN bruikbare/consistente
-- website_id opleverde (bijv. NULL of leeg, wat gezien de NOT NULL-constraint
-- niet verwacht wordt, maar voor de zekerheid hieronder toch als fallback).
-- Dit genereert een NIEUWE uuid en koppelt alle bestaande rijen daaraan.
-- ============================================================================

SET @new_website_id = UUID();

INSERT INTO websites (id, domain_name, company_name, template_key, is_active)
VALUES (@new_website_id, 'primeradebandijk.nl', 'Primera de Bandijk', 'default', 1);
--
-- UPDATE site_content     SET website_id = @new_website_id WHERE website_id IS NULL OR website_id = '';
-- UPDATE categories       SET website_id = @new_website_id WHERE website_id IS NULL OR website_id = '';
-- UPDATE products         SET website_id = @new_website_id WHERE website_id IS NULL OR website_id = '';
-- UPDATE portfolio        SET website_id = @new_website_id WHERE website_id IS NULL OR website_id = '';
-- UPDATE usps             SET website_id = @new_website_id WHERE website_id IS NULL OR website_id = '';
-- UPDATE reviews          SET website_id = @new_website_id WHERE website_id IS NULL OR website_id = '';
-- UPDATE faqs             SET website_id = @new_website_id WHERE website_id IS NULL OR website_id = '';
-- UPDATE contact_messages SET website_id = @new_website_id WHERE website_id IS NULL OR website_id = '';
-- UPDATE legals           SET website_id = @new_website_id WHERE website_id IS NULL OR website_id = '';
-- UPDATE footer           SET website_id = @new_website_id WHERE website_id IS NULL OR website_id = '';
-- UPDATE app_settings     SET website_id = @new_website_id WHERE website_id IS NULL OR website_id = '';
-- UPDATE users            SET website_id = @new_website_id WHERE website_id IS NULL OR website_id = '';

-- ============================================================================
-- STAP 3 — rol van de bestaande beheerder-user bepalen.
-- ============================================================================
-- `role` staat waarschijnlijk nog op de default 'client'. Zet de bestaande
-- inlog om tot 'super_admin' als dit account straks meerdere klantwebsites
-- moet kunnen beheren (de siteswitcher in de header wordt dan zichtbaar).
-- Laat 'client' staan als dit account alleen deze ene website mag beheren.
-- Vervang '<gebruikersnaam>' door de bestaande inlognaam.

-- UPDATE users SET role = 'super_admin' WHERE username = '<gebruikersnaam>';

-- ============================================================================
-- STAP 4 — controleren
-- ============================================================================
-- SELECT * FROM websites;
-- SELECT id, username, role, website_id FROM users;
-- Log daarna in via login.php en controleer dat het dashboard de bestaande
-- content laat zien.
