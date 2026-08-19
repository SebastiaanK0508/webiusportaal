SELECT 
    table_name AS 'Tabel', 
    column_name AS 'Kolom', 
    data_type AS 'Type', 
    column_type AS 'Detail', 
    is_nullable AS 'Null', 
    column_default AS 'Default'
FROM 
    information_schema.columns 
WHERE 
    table_schema = DATABASE()
ORDER BY 
    table_name, ordinal_position;


+------------------------+------------------+-----------+----------------------------------------------------+------+---------------------+
| Tabel                  | Kolom            | Type      | Detail                                             | Null | Default             |
+------------------------+------------------+-----------+----------------------------------------------------+------+---------------------+
| app_settings           | id               | char      | char(36)                                           | NO   | NULL                |
| app_settings           | website_id       | char      | char(36)                                           | NO   | NULL                |
| app_settings           | setting_key      | varchar   | varchar(100)                                       | NO   | NULL                |
| app_settings           | setting_value    | text      | text                                               | YES  | NULL                |
| app_settings           | updated_at       | timestamp | timestamp                                          | YES  | current_timestamp() |
| assortiment_merken     | id               | char      | char(36)                                           | NO   | NULL                |
| assortiment_merken     | category_id      | char      | char(36)                                           | NO   | NULL                |
| assortiment_merken     | naam             | varchar   | varchar(100)                                       | NO   | NULL                |
| assortiment_merken     | logo_path        | varchar   | varchar(255)                                       | NO   | NULL                |
| categories             | id               | char      | char(36)                                           | NO   | NULL                |
| categories             | website_id       | char      | char(36)                                           | YES  | NULL                |
| categories             | name             | varchar   | varchar(255)                                       | NO   | NULL                |
| categories             | sort_order       | int       | int(11)                                            | YES  | 0                   |
| contact_messages       | id               | char      | char(36)                                           | NO   | NULL                |
| contact_messages       | website_id       | char      | char(36)                                           | NO   | NULL                |
| contact_messages       | name             | varchar   | varchar(255)                                       | NO   | NULL                |
| contact_messages       | email            | varchar   | varchar(255)                                       | NO   | NULL                |
| contact_messages       | phone            | varchar   | varchar(50)                                        | YES  | NULL                |
| contact_messages       | subject          | varchar   | varchar(100)                                       | YES  | NULL                |
| contact_messages       | message          | text      | text                                               | NO   | NULL                |
| contact_messages       | status           | enum      | enum('nieuw','gelezen','beantwoord')               | YES  | 'nieuw'             |
| contact_messages       | is_read          | tinyint   | tinyint(1)                                         | YES  | 0                   |
| contact_messages       | created_at       | timestamp | timestamp                                          | YES  | current_timestamp() |
| faqs                   | id               | char      | char(36)                                           | NO   | NULL                |
| faqs                   | website_id       | char      | char(36)                                           | NO   | NULL                |
| faqs                   | question         | varchar   | varchar(255)                                       | NO   | NULL                |
| faqs                   | answer           | text      | text                                               | NO   | NULL                |
| faqs                   | created_at       | timestamp | timestamp                                          | YES  | current_timestamp() |
| footer                 | id               | char      | char(36)                                           | NO   | NULL                |
| footer                 | website_id       | char      | char(36)                                           | NO   | NULL                |
| footer                 | sleutel          | varchar   | varchar(50)                                        | NO   | NULL                |
| footer                 | waarde           | text      | text                                               | YES  | NULL                |
| legals                 | id               | char      | char(36)                                           | NO   | NULL                |
| legals                 | website_id       | char      | char(36)                                           | NO   | NULL                |
| legals                 | type             | varchar   | varchar(50)                                        | NO   | NULL                |
| legals                 | title            | varchar   | varchar(255)                                       | NO   | NULL                |
| legals                 | content          | longtext  | longtext                                           | YES  | NULL                |
| legals                 | updated_at       | timestamp | timestamp                                          | YES  | current_timestamp() |
| portfolio              | id               | char      | char(36)                                           | NO   | NULL                |
| portfolio              | website_id       | char      | char(36)                                           | NO   | NULL                |
| portfolio              | title            | varchar   | varchar(255)                                       | NO   | NULL                |
| portfolio              | description      | text      | text                                               | YES  | NULL                |
| portfolio              | image_path       | varchar   | varchar(255)                                       | NO   | NULL                |
| portfolio              | created_at       | timestamp | timestamp                                          | YES  | current_timestamp() |
| prijsvraag_inzendingen | id               | char      | char(36)                                           | NO   | NULL                |
| prijsvraag_inzendingen | prijsvraag_id    | char      | char(36)                                           | NO   | NULL                |
| prijsvraag_inzendingen | voornaam         | varchar   | varchar(50)                                        | NO   | NULL                |
| prijsvraag_inzendingen | achternaam       | varchar   | varchar(50)                                        | NO   | NULL                |
| prijsvraag_inzendingen | email            | varchar   | varchar(100)                                       | NO   | NULL                |
| prijsvraag_inzendingen | telefoon         | varchar   | varchar(20)                                        | YES  | NULL                |
| prijsvraag_inzendingen | antwoord         | text      | text                                               | NO   | NULL                |
| prijsvraag_inzendingen | datum            | timestamp | timestamp                                          | YES  | current_timestamp() |
| products               | id               | char      | char(36)                                           | NO   | NULL                |
| products               | website_id       | char      | char(36)                                           | NO   | NULL                |
| products               | category_id      | char      | char(36)                                           | YES  | NULL                |
| products               | title            | varchar   | varchar(255)                                       | NO   | NULL                |
| products               | description      | text      | text                                               | YES  | NULL                |
| products               | price            | decimal   | decimal(10,2)                                      | NO   | NULL                |
| products               | sort_order       | int       | int(11)                                            | YES  | 0                   |
| products               | show_on_homepage | tinyint   | tinyint(1)                                         | YES  | 0                   |
| reviews                | id               | char      | char(36)                                           | NO   | NULL                |
| reviews                | website_id       | char      | char(36)                                           | NO   | NULL                |
| reviews                | customer_name    | varchar   | varchar(255)                                       | NO   | NULL                |
| reviews                | review_text      | text      | text                                               | NO   | NULL                |
| reviews                | stars            | int       | int(11)                                            | YES  | 5                   |
| reviews                | created_at       | timestamp | timestamp                                          | YES  | current_timestamp() |
| services               | id               | char      | char(36)                                           | NO   | NULL                |
| services               | website_id       | char      | char(36)                                           | NO   | NULL                |
| services               | title            | varchar   | varchar(255)                                       | NO   | NULL                |
| services               | description      | text      | text                                               | NO   | NULL                |
| services               | price            | decimal   | decimal(10,2)                                      | NO   | NULL                |
| services               | created_at       | timestamp | timestamp                                          | YES  | current_timestamp() |
| site_content           | id               | char      | char(36)                                           | NO   | NULL                |
| site_content           | website_id       | char      | char(36)                                           | NO   | NULL                |
| site_content           | page             | varchar   | varchar(50)                                        | NO   | NULL                |
| site_content           | section_key      | varchar   | varchar(50)                                        | NO   | NULL                |
| site_content           | content_text     | text      | text                                               | NO   | NULL                |
| site_content           | type             | enum      | enum('text','textarea','image','number','boolean') | NO   | 'text'              |
| site_content           | label            | varchar   | varchar(150)                                       | YES  | NULL                |
| site_content           | group_name       | varchar   | varchar(50)                                        | YES  | NULL                |
| site_content           | is_visible       | tinyint   | tinyint(1)                                         | YES  | 1                   |
| site_content           | updated_at       | timestamp | timestamp                                          | YES  | current_timestamp() |
| users                  | id               | char      | char(36)                                           | NO   | NULL                |
| users                  | website_id       | char      | char(36)                                           | YES  | NULL                |
| users                  | role             | enum      | enum('super_admin','client')                       | NO   | 'client'            |
| users                  | username         | varchar   | varchar(50)                                        | NO   | NULL                |
| users                  | email            | varchar   | varchar(255)                                       | NO   | NULL                |
| users                  | password_hash    | varchar   | varchar(255)                                       | NO   | NULL                |
| users                  | is_active        | tinyint   | tinyint(1)                                         | NO   | 1                   |
| users                  | reset_token      | varchar   | varchar(64)                                        | YES  | NULL                |
| users                  | reset_expires    | datetime  | datetime                                           | YES  | NULL                |
| users                  | last_login       | timestamp | timestamp                                          | YES  | NULL                |
| users                  | created_at       | timestamp | timestamp                                          | YES  | current_timestamp() |
| usps                   | id               | char      | char(36)                                           | NO   | NULL                |
| usps                   | website_id       | char      | char(36)                                           | NO   | NULL                |
| usps                   | icon             | varchar   | varchar(50)                                        | NO   | NULL                |
| usps                   | title            | varchar   | varchar(255)                                       | NO   | NULL                |
| usps                   | description      | text      | text                                               | NO   | NULL                |
| usps                   | created_at       | timestamp | timestamp                                          | YES  | current_timestamp() |
| websites               | id               | char      | char(36)                                           | NO   | NULL                |
| websites               | domain_name      | varchar   | varchar(255)                                       | NO   | NULL                |
| websites               | company_name     | varchar   | varchar(255)                                       | NO   | NULL                |
| websites               | template_key     | varchar   | varchar(50)                                        | NO   | 'default'           |
| websites               | logo_path        | varchar   | varchar(255)                                       | YES  | NULL                |
| websites               | color_primary    | varchar   | varchar(7)                                         | YES  | NULL                |
| websites               | color_secondary  | varchar   | varchar(7)                                         | YES  | NULL                |
| websites               | color_accent     | varchar   | varchar(7)                                         | YES  | NULL                |
| websites               | font_family      | varchar   | varchar(100)                                       | YES  | 'Outfit'            |
| websites               | is_active        | tinyint   | tinyint(1)                                         | YES  | 1                   |
| websites               | created_at       | timestamp | timestamp                                          | YES  | current_timestamp() |
+------------------------+------------------+-----------+----------------------------------------------------+------+---------------------+


SET @website_id = '775d64f8-99a0-11f1-bc37-06bd6669bc40';

INSERT INTO site_content (id, website_id, page, section_key, content_text, type, label, group_name, is_visible, updated_at) VALUES
('ab8169e4-6b0e-4a27-a9bd-2cfb41ecc0bd', @website_id, 'algemeen', 'bedrijf_btw', 'NL123456789B01', 'text', 'BTW-nummer', 'bedrijfsgegevens', 1, '2026-06-21 10:19:13'),
('a2b3dbbc-bcd7-40df-b1d3-cb29c3e356d8', @website_id, 'algemeen', 'bedrijf_kvk', '12345678', 'text', 'KVK-nummer', 'bedrijfsgegevens', 1, '2026-06-21 10:19:13'),
('698d3369-0155-4fd7-b0da-89c72c2dd859', @website_id, 'algemeen', 'contact_email', 'info@beautytouchbynikki.nl', 'text', 'E-mailadres', 'contact', 1, '2026-06-21 10:19:13'),
('4107e476-4b11-495c-9302-dab4ad5c279a', @website_id, 'algemeen', 'contact_regio', 'Regio Goor', 'text', 'Regio', 'contact', 1, '2026-06-21 10:19:13'),
('b2dc8b86-3309-4213-96f9-973f97f26bd9', @website_id, 'algemeen', 'contact_telefoon', '06 - 12 34 56 78', 'text', 'Telefoonnummer', 'contact', 1, '2026-06-21 10:19:13'),
('da926d79-2d9f-40a1-8352-fba21c9fb489', @website_id, 'algemeen', 'footer_text', 'Exclusieve haarverzorging en Beauty, gewoon bij jou thuis. Ervaar luxe, persoonlijke aandacht en ultiem vakmanschap in je eigen vertrouwde omgeving.', 'textarea', 'Footer tekst', 'footer', 1, '2026-06-21 10:19:13'),
('8d58dd39-99f8-4d91-8298-4ebaca0b6473', @website_id, 'algemeen', 'social_facebook', 'https://facebook.com/jouwpagina', 'text', 'Facebook URL', 'social', 1, '2026-06-21 10:19:13'),
('bc24b5fe-cdf8-4e2a-85cf-c39439428eb6', @website_id, 'algemeen', 'social_facebook_actief', '1', 'boolean', 'Facebook actief', 'social', 1, '2026-06-21 10:19:13'),
('f952102a-8df8-4be1-9057-e781c1b3e22c', @website_id, 'algemeen', 'social_instagram', 'https://instagram.com/jouwpagina', 'text', 'Instagram URL', 'social', 1, '2026-06-21 10:19:13'),
('b05f44fa-2665-44d0-8de8-a04831af3258', @website_id, 'algemeen', 'social_instagram_actief', '1', 'boolean', 'Instagram actief', 'social', 1, '2026-06-21 10:19:13'),
('155542ec-4b91-4508-be1a-cf542905910b', @website_id, 'algemeen', 'social_tiktok', 'https://tiktok.com/@jouwpagina', 'text', 'TikTok URL', 'social', 1, '2026-06-21 10:19:13'),
('606a78e1-3761-4094-9b76-f511d0cec2e3', @website_id, 'algemeen', 'social_tiktok_actief', '1', 'boolean', 'TikTok actief', 'social', 1, '2026-06-21 10:19:13'),
('20d22b6e-8c17-4aa1-ba21-f8a82947b88d', @website_id, 'home', 'hero_title', 'Beauty Touch by Nikki', 'text', 'Hero titel', 'hero', 1, '2026-06-21 10:19:13'),
('cee03419-9fb5-46b4-8b75-b28be058e184', @website_id, 'home', 'hero_text', 'Welkom bij mijn Kapperservice. Geniet van salonkwaliteit, gewoon in mijn salon.', 'textarea', 'Hero tekst', 'hero', 1, '2026-06-21 10:19:13'),
('80437335-1091-40e6-96f5-953e0ec4111b', @website_id, 'home', 'about_title', 'Over Mijn Service', 'text', 'Over titel', 'about', 1, '2026-06-21 10:19:13'),
('3b203950-18e6-4c71-889e-023da89f199f', @website_id, 'home', 'about_text', 'Met passie voor het vak bied ik professionele haarverzorging bij u thuis. Geen wachttijden en persoonlijke aandacht.', 'textarea', 'Over tekst', 'about', 1, '2026-06-21 10:19:13'),
('f2f63bec-7de9-4201-856f-15ea8a67bc93', @website_id, 'home', 'about_1', 'Met passie voor knippen', 'text', 'Kenmerk 1', 'about', 1, '2026-06-21 10:19:13'),
('fef73fbc-7e90-4b06-89ff-f26162c6d0ee', @website_id, 'home', 'about_2', 'Met passie voor fönen', 'text', 'Kenmerk 2', 'about', 1, '2026-06-21 10:19:13'),
('79c7bfce-828c-4332-8c10-e5551f0f7331', @website_id, 'home', 'about_3', 'Met passie voor knippen', 'text', 'Kenmerk 3', 'about', 1, '2026-06-21 10:19:13'),
('68fabfb7-b34c-4339-9f45-9ac189eb1f88', @website_id, 'home', 'hero_image', 'uploads/1780233018_logo.png', 'image', 'Hero afbeelding', 'hero', 0, '2026-06-21 10:19:13'),
('a26f94a1-ee89-4755-9576-406a0476205d', @website_id, 'home', 'about_image_1', '', 'image', 'Over afbeelding 1', 'about', 0, '2026-06-21 10:19:13'),
('ffd7dc65-0815-484c-8f93-3f37cf9d8e8f', @website_id, 'home', 'about_image_2', 'uploads/1781122080_Schermafbeelding 2026-05-26 om 19.12.39.png', 'image', 'Over afbeelding 2', 'about', 0, '2026-06-21 10:19:13'),
('edd843b5-28c2-4b44-8597-23b65e9efc47', @website_id, 'contact', 'contact_hero_title', 'Contact', 'text', 'Contact hero titel', 'hero', 1, '2026-06-21 10:19:13'),
('2d417237-6183-4a17-bcf1-a82006582c43', @website_id, 'contact', 'contact_hero_text', 'Heb je een vraag of wil je een afspraak maken? Stuur me gerust een berichtje of bel direct.', 'textarea', 'Contact hero tekst', 'hero', 1, '2026-06-21 10:19:13'),
('01e1f81f-0de9-4ce4-b334-953fc406f12b', @website_id, 'contact', 'contact_info_title', 'Contactgegevens', 'text', 'Contact info titel', 'info', 1, '2026-06-21 10:19:13'),
('9cc1ac24-ab0e-4e3b-84d3-6eea62ef3306', @website_id, 'contact', 'contact_info_text', 'Vul het formulier in of neem direct contact op via onderstaande gegevens. Ik probeer altijd binnen 24 uur te reageren op je bericht.', 'textarea', 'Contact info tekst', 'info', 1, '2026-06-21 10:19:13'),
('ae80012a-d265-4dbe-ba48-e23f12456e9d', @website_id, 'contact', 'contact_telefoon', '0612345678', 'text', 'Telefoonnummer', 'info', 1, '2026-06-21 10:19:13'),
('eb74b456-d950-43f8-9d50-d3ed8324dea2', @website_id, 'contact', 'contact_email', 'info@email.com', 'text', 'E-mailadres', 'info', 1, '2026-06-21 10:19:13'),
('7327931f-2e40-4f50-8f2d-c5a293adc0ba', @website_id, 'contact', 'contact_adres', 'Grotestraat 149 - 17', 'text', 'Adres', 'info', 1, '2026-06-21 10:19:13'),
('2e8c678b-e1bc-4b3d-86a3-efe0942806d7', @website_id, 'contact', 'contact_plaats', 'Goor', 'text', 'Plaats', 'info', 1, '2026-06-21 10:19:13'),
('d08e44d3-b7b4-40a1-bba1-cdfe62430aca', @website_id, 'contact', 'contact_postcode', '7471 BN', 'text', 'Postcode', 'info', 1, '2026-06-21 10:19:13');