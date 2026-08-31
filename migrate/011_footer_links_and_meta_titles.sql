-- Maakt de footer-navigatielinks (kolommen "Services" en "Navigatie") en de
-- <title>-tags van elke pagina bewerkbaar via het portaal, plus de merknaam
-- die in de footer-kop wordt getoond.
--
-- 1. Nieuwe tabel `footer_links`: losse, herordenbare links per kolom,
--    beheerd via footerbeheer.php (tab "Links"). Geseed met de links die nu
--    hardcoded in debandijk/footer.php staan.
-- 2. Nieuwe site_content-rijen (page/section_key 'meta_title') voor de
--    <title>-tag van elke pagina, en 'algemeen'/'site_naam' voor de
--    merknaam in de footer-heading. Bewerkbaar via websitebeheer.php
--    (tab "Algemene Teksten").
--
-- Idempotent: opnieuw draaien voegt geen dubbele links toe en overschrijft
-- geen bestaande site_content-waarden (ON DUPLICATE KEY UPDATE is een no-op).

CREATE TABLE IF NOT EXISTS footer_links (
    id          char(36) NOT NULL PRIMARY KEY,
    website_id  char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    kolom       varchar(20) NOT NULL,
    label       varchar(100) NOT NULL,
    url         varchar(255) NOT NULL,
    sort_order  int(11) DEFAULT 0,
    created_at  timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_footer_links_website (website_id, kolom),
    CONSTRAINT fk_footer_links_website FOREIGN KEY (website_id) REFERENCES websites(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- De 4 "Services"-links en 5 "Navigatie"-links die nu hardcoded in
-- debandijk/footer.php staan, als startdata.
INSERT INTO footer_links (id, website_id, kolom, label, url, sort_order)
SELECT UUID(), '775d64f8-99a0-11f1-bc37-06bd6669bc40', v.kolom, v.label, v.url, v.sort_order
FROM (
    SELECT 'services' AS kolom, "Pasfoto's" AS label, 'services/pasfotos.php' AS url, 1 AS sort_order
    UNION ALL SELECT 'services', 'PostNL', 'services/postnl.php', 2
    UNION ALL SELECT 'services', 'RDW', 'services/rdw.php', 3
    UNION ALL SELECT 'services', 'Geldmaat', 'services/geldmaat.php', 4
    UNION ALL SELECT 'navigatie', 'Home', 'index.php', 1
    UNION ALL SELECT 'navigatie', 'Openingstijden', 'index.php#openingstijden', 2
    UNION ALL SELECT 'navigatie', 'Nieuws', '#nieuws', 3
    UNION ALL SELECT 'navigatie', 'Contact', 'contact.php', 4
    UNION ALL SELECT 'navigatie', 'Over Ons', 'about.php', 5
) v
WHERE NOT EXISTS (SELECT 1 FROM footer_links WHERE website_id = '775d64f8-99a0-11f1-bc37-06bd6669bc40');

INSERT INTO site_content (id, website_id, page, section_key, content_text, type, label, group_name, is_visible)
SELECT UUID(), w.id, v.page, v.section_key, v.content_text, v.type, v.label, v.group_name, 1
FROM websites w
CROSS JOIN (
    SELECT 'algemeen' AS page, 'site_naam' AS section_key, 'De Bandijk' AS content_text, 'text' AS type, 'Merknaam (footer-kop)' AS label, 'branding' AS group_name

    UNION ALL SELECT 'home', 'meta_title', 'Primera de Bandijk | Handig in de buurt', 'text', 'Titel in de browsertab', 'seo'
    UNION ALL SELECT 'about', 'meta_title', 'Over Primera de Bandijk | Het verhaal van onze winkel in Goor', 'text', 'Titel in de browsertab', 'seo'
    UNION ALL SELECT 'contact', 'meta_title', 'Contact & Openingstijden | Primera de Bandijk Goor', 'text', 'Titel in de browsertab', 'seo'
    UNION ALL SELECT 'assortiment', 'meta_title', 'Ons Assortiment | Primera de Bandijk Goor', 'text', 'Titel in de browsertab', 'seo'
    UNION ALL SELECT 'cadeaukaarten', 'meta_title', 'Cadeaukaart Zoeker Goor | +100 kaarten bij Primera de Bandijk', 'text', 'Titel in de browsertab', 'seo'
    UNION ALL SELECT 'nieuws', 'meta_title', 'Nieuws, Acties & Winacties | Primera de Bandijk Goor', 'text', 'Titel in de browsertab', 'seo'
    UNION ALL SELECT 'prijsvraag', 'meta_title', 'Prijsvraag | Primera de Bandijk', 'text', 'Titel in de browsertab', 'seo'
    UNION ALL SELECT 'geschiedenis', 'meta_title', 'Onze Geschiedenis | 30+ jaar Primera de Bandijk Goor', 'text', 'Titel in de browsertab', 'seo'
) v
WHERE w.id IN ('5fd921b6-9a75-11f1-b88b-06bd6669bc40', '775d64f8-99a0-11f1-bc37-06bd6669bc40')
ON DUPLICATE KEY UPDATE site_content.id = site_content.id;
