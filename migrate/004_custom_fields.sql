-- Voegt de tabellen toe voor super_velden.php: het beheren van extra
-- tags/keys/velden vanuit het portaal, zowel voor de content-instellingen
-- (site_content/footer/app_settings) als voor losse "extra eigenschappen"
-- op producten, categorieën, portfolio, USP's, reviews en FAQ's.
--
-- Veilig om te draaien: CREATE TABLE IF NOT EXISTS, raakt geen bestaande data.
-- De site_content/footer/app_settings-tabellen zelf hoeven niet aangepast te
-- worden — een "nieuw veld" daar is gewoon een nieuwe rij met een sleutel die
-- nog niet bestond, wat de bestaande tabelstructuur al ondersteunt.

CREATE TABLE IF NOT EXISTS custom_field_definitions (
    id char(36) NOT NULL PRIMARY KEY,
    website_id char(36) NOT NULL,
    entity_type varchar(50) NOT NULL,   -- 'product', 'category', 'portfolio', 'usp', 'review', 'faq'
    field_key varchar(100) NOT NULL,
    label varchar(150) NOT NULL,
    field_type varchar(20) NOT NULL DEFAULT 'text',  -- text, textarea, number, boolean, url
    sort_order int(11) DEFAULT 0,
    created_at timestamp DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_cfd_website_entity (website_id, entity_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS custom_field_values (
    id char(36) NOT NULL PRIMARY KEY,
    field_id char(36) NOT NULL,   -- verwijst naar custom_field_definitions.id
    entity_id char(36) NOT NULL,  -- verwijst naar het id van het product/de categorie/etc.
    value text,
    INDEX idx_cfv_field (field_id),
    INDEX idx_cfv_entity (entity_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
