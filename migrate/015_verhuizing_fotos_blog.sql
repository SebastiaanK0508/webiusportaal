-- Breidt de verhuismodule uit met foto's en een blog.
--
-- 1. Tabel verhuizing_blog: berichten om bezoekers te laten volgen hoe de
--    verhuizing gaat. Elk bericht heeft een titel, datum, tekst, nul of meer
--    afbeeldingen (komma-gescheiden paden, zelfde opzet als nieuws.image_paths)
--    en een eigen aan/uit-schakelaar (is_visible).
-- 2. site_content-rijen:
--    - 'fotos'        komma-gescheiden paden van de fotogalerij op de pagina
--    - 'fotos_title'  kop boven de galerij; is_visible = galerij aan/uit
--    - 'blog_title'   kop boven de blog;    is_visible = hele blog aan/uit
--    - 'blog_text'    korte intro onder de blogkop
--    Zonder foto's resp. zichtbare berichten wordt het onderdeel niet getoond.
--
-- Idempotent: opnieuw draaien overschrijft geen bewerkte teksten.

CREATE TABLE IF NOT EXISTS verhuizing_blog (
    id           char(36) NOT NULL,
    website_id   char(36) NOT NULL,
    titel        varchar(255) NOT NULL,
    tekst        text NOT NULL,
    image_paths  text NULL,
    datum        date NOT NULL,
    is_visible   tinyint(1) NOT NULL DEFAULT 1,
    created_at   timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_verhuizing_blog_website (website_id, datum),
    CONSTRAINT fk_verhuizing_blog_website FOREIGN KEY (website_id) REFERENCES websites(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO site_content (id, website_id, page, section_key, content_text, type, label, group_name, is_visible)
SELECT UUID(), '775d64f8-99a0-11f1-bc37-06bd6669bc40', v.page, v.section_key, v.content_text, v.type, v.label, v.group_name, 1
FROM (
    SELECT 'verhuizing' AS page, 'fotos' AS section_key, '' AS content_text, 'text' AS type, 'Foto\'s op de verhuispagina' AS label, 'fotos' AS group_name
    UNION ALL SELECT 'verhuizing', 'fotos_title', 'Een kijkje bij onze nieuwe winkel', 'text', 'Kop boven de foto\'s', 'fotos'
    UNION ALL SELECT 'verhuizing', 'blog_title', 'Volg de verhuizing', 'text', 'Kop boven de blog', 'blog'
    UNION ALL SELECT 'verhuizing', 'blog_text', 'Hier houden wij u op de hoogte van hoe het gaat met de verhuizing.', 'textarea', 'Intro van de blog', 'blog'
) v
ON DUPLICATE KEY UPDATE site_content.id = site_content.id;
