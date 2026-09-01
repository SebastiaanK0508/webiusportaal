-- Twee toevoegingen bovenop migratie 011:
--
-- 1. Een `icon`-kolom op `footer_links`, zodat elke link weer een eigen
--    icoon toont (net als in de hoofdnavigatie) i.p.v. één generiek pijltje
--    voor alle links. footerbeheer.php kiest het icoon automatisch aan de
--    hand van de gekozen pagina (zie FOOTER_LINK_PAGES in footerbeheer.php).
-- 2. site_content-rijen voor de 4 losse services-pagina's
--    (debandijk/services/*.php), zodat hun titel, <title>-tag en
--    introtekst bewerkbaar worden via websitebeheer.php — precies zoals
--    dat al werkt voor assortiment/cadeaukaarten/nieuws/etc.
--
-- Idempotent: opnieuw draaien voegt de kolom niet dubbel toe en
-- overschrijft geen bestaande content_text-waarden.

ALTER TABLE footer_links ADD COLUMN IF NOT EXISTS icon varchar(30) NOT NULL DEFAULT 'link' AFTER url;

UPDATE footer_links SET icon = 'camera' WHERE website_id = '775d64f8-99a0-11f1-bc37-06bd6669bc40' AND kolom = 'services' AND label = "Pasfoto's";
UPDATE footer_links SET icon = 'package' WHERE website_id = '775d64f8-99a0-11f1-bc37-06bd6669bc40' AND kolom = 'services' AND label = 'PostNL';
UPDATE footer_links SET icon = 'check-circle' WHERE website_id = '775d64f8-99a0-11f1-bc37-06bd6669bc40' AND kolom = 'services' AND label = 'RDW';
UPDATE footer_links SET icon = 'cash' WHERE website_id = '775d64f8-99a0-11f1-bc37-06bd6669bc40' AND kolom = 'services' AND label = 'Geldmaat';
UPDATE footer_links SET icon = 'home' WHERE website_id = '775d64f8-99a0-11f1-bc37-06bd6669bc40' AND kolom = 'navigatie' AND label = 'Home';
UPDATE footer_links SET icon = 'clock' WHERE website_id = '775d64f8-99a0-11f1-bc37-06bd6669bc40' AND kolom = 'navigatie' AND label = 'Openingstijden';
UPDATE footer_links SET icon = 'newspaper' WHERE website_id = '775d64f8-99a0-11f1-bc37-06bd6669bc40' AND kolom = 'navigatie' AND label = 'Nieuws';
UPDATE footer_links SET icon = 'chat' WHERE website_id = '775d64f8-99a0-11f1-bc37-06bd6669bc40' AND kolom = 'navigatie' AND label = 'Contact';
UPDATE footer_links SET icon = 'people' WHERE website_id = '775d64f8-99a0-11f1-bc37-06bd6669bc40' AND kolom = 'navigatie' AND label = 'Over Ons';

INSERT INTO site_content (id, website_id, page, section_key, content_text, type, label, group_name, is_visible)
SELECT UUID(), '775d64f8-99a0-11f1-bc37-06bd6669bc40', v.page, v.section_key, v.content_text, v.type, v.label, v.group_name, 1
FROM (
    SELECT 'pasfotos' AS page, 'meta_title' AS section_key, 'Pasfoto laten maken Goor | RDW Erkend | Primera de Bandijk' AS content_text, 'text' AS type, 'Titel in de browsertab' AS label, 'seo' AS group_name
    UNION ALL SELECT 'pasfotos', 'hero_title', "Pasfoto's klaar terwijl u wacht", 'text', 'Grote Hoofdtitel', 'hero'
    UNION ALL SELECT 'pasfotos', 'hero_text', "Nieuw paspoort, ID-kaart of rijbewijs nodig? Wij maken officiële pasfoto's die voldoen aan alle wettelijke eisen. Direct klaar, zonder afspraak!", 'textarea', 'Korte Introductie', 'hero'

    UNION ALL SELECT 'postnl', 'meta_title', 'PostNL Pakketpunt Goor | Pakket ophalen & versturen | Primera de Bandijk', 'text', 'Titel in de browsertab', 'seo'
    UNION ALL SELECT 'postnl', 'hero_title', 'PostNL Servicepunt', 'text', 'Grote Hoofdtitel', 'hero'
    UNION ALL SELECT 'postnl', 'hero_text', 'Pakket versturen, ophalen of een brief posten? Bij Primera de Bandijk regelt u uw postzaken snel en efficiënt, met de vertrouwde service van uw buurtwinkel.', 'textarea', 'Korte Introductie', 'hero'

    UNION ALL SELECT 'rdw', 'meta_title', 'RDW Kenteken Overschrijven Goor | Primera de Bandijk', 'text', 'Titel in de browsertab', 'seo'
    UNION ALL SELECT 'rdw', 'hero_title', 'RDW Diensten', 'text', 'Grote Hoofdtitel', 'hero'
    UNION ALL SELECT 'rdw', 'hero_text', 'Een voertuig gekocht, verkocht of wilt u uw rijbewijs verlengen? Kom langs en vraag naar de mogelijkheden.', 'textarea', 'Korte Introductie', 'hero'

    UNION ALL SELECT 'geldmaat', 'meta_title', 'Geldmaat Locatie Goor | Geld opnemen & storten bij Primera de Bandijk', 'text', 'Titel in de browsertab', 'seo'
    UNION ALL SELECT 'geldmaat', 'hero_title', 'Geldmaat Locatie', 'text', 'Grote Hoofdtitel', 'hero'
    UNION ALL SELECT 'geldmaat', 'hero_text', 'Veilig, droog en discreet bankzaken regelen? Bij Primera de Bandijk vindt u een Geldmaat-automaat voor uw dagelijkse contante transacties.', 'textarea', 'Korte Introductie', 'hero'
) v
ON DUPLICATE KEY UPDATE site_content.id = site_content.id;
