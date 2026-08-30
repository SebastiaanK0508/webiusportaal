-- Maakt een reeks tot nu toe hardcoded teksten op de publieke website bewerkbaar
-- via het portaal: hero-teksten van assortiment/cadeaukaarten/nieuws/prijsvraag/
-- geschiedenis, de wettelijke tabakstekst, de "in ontwikkeling"-melding op de
-- cadeaukaartenpagina, de CTA op de geschiedenispagina, en de tekst van de
-- diensten-tegels op de homepage. Voegt ook de tabel toe voor het (nieuwe)
-- teambeheer-onderdeel.
--
-- Idempotent: opnieuw draaien overschrijft geen bestaande content_text (de
-- ON DUPLICATE KEY UPDATE is een no-op) en de tabel wordt alleen aangemaakt
-- als hij nog niet bestaat.

INSERT INTO site_content (id, website_id, page, section_key, content_text, type, label, group_name, is_visible)
SELECT UUID(), w.id, v.page, v.section_key, v.content_text, v.type, v.label, v.group_name, 1
FROM websites w
CROSS JOIN (
    SELECT 'assortiment' AS page, 'hero_title' AS section_key, 'Ons assortiment' AS content_text, 'text' AS type, 'Titel bovenaan' AS label, 'hero' AS group_name
    UNION ALL SELECT 'assortiment', 'hero_text', 'Van de dagelijkse krant tot dat ene speciale cadeau — de vertrouwde kwaliteit van De Bandijk.', 'textarea', 'Introductietekst', 'hero'
    UNION ALL SELECT 'assortiment', 'tabak_title', 'Tabak & rookwaren', 'text', 'Titel wettelijke tekst', 'wettelijk'
    UNION ALL SELECT 'assortiment', 'tabak_text', 'Wij verkopen geen tabaksproducten aan personen onder de 18 jaar. Ben je jonger dan 25 jaar? Laat dan uit eigen beweging je legitimatiebewijs zien. Zonder geldig ID-bewijs mogen wij de verkoop niet voltooien.', 'textarea', 'Wettelijke tabak/rookwaren-tekst', 'wettelijk'

    UNION ALL SELECT 'cadeaukaarten', 'hero_title', 'Cadeaukaart zoeker', 'text', 'Titel bovenaan', 'hero'
    UNION ALL SELECT 'cadeaukaarten', 'hero_text', 'Voor elke winkel of hobby een kaart. Zoek direct in ons assortiment van +100 kaarten.', 'textarea', 'Introductietekst', 'hero'
    UNION ALL SELECT 'cadeaukaarten', 'waarschuwing_text', 'Let op: Deze tool is momenteel in ontwikkeling. De getoonde gegevens en voorraden kunnen afwijken van de werkelijkheid. Kom naar de winkel voor het actuele aanbod.', 'textarea', '"In ontwikkeling"-melding (zet uit zodra klaar)', 'waarschuwing'

    UNION ALL SELECT 'nieuws', 'hero_title', 'Nieuws & acties', 'text', 'Titel bovenaan', 'hero'
    UNION ALL SELECT 'nieuws', 'hero_text', 'Ontdek de laatste nieuwtjes, winacties en lokale updates van Primera de Bandijk in Goor.', 'textarea', 'Introductietekst', 'hero'

    UNION ALL SELECT 'prijsvraag', 'hero_title', 'Doe mee & win!', 'text', 'Titel bovenaan', 'hero'
    UNION ALL SELECT 'prijsvraag', 'hero_text', 'Beantwoord de vraag hieronder en wie weet ben jij onze volgende gelukkige winnaar.', 'textarea', 'Introductietekst', 'hero'
    UNION ALL SELECT 'prijsvraag', 'geen_vraag_text', 'Op dit moment bereiden we een nieuwe winactie voor. Houd onze website of Facebook in de gaten!', 'textarea', 'Tekst als er geen actieve prijsvraag is', 'overig'
    UNION ALL SELECT 'prijsvraag', 'voorwaarden_text', 'Ik ga akkoord met de actievoorwaarden en geef toestemming om op Facebook en/of onze website te verschijnen met een foto (bij winst).', 'textarea', 'Tekst bij het akkoord-vakje', 'overig'

    UNION ALL SELECT 'geschiedenis', 'cta_title', 'Bezoek onze winkel in Goor', 'text', 'Titel van het CTA-blok', 'cta'
    UNION ALL SELECT 'geschiedenis', 'cta_text', 'Al meer dan 30 jaar een vertrouwd gezicht aan de Grotestraat.', 'textarea', 'Tekst van het CTA-blok', 'cta'

    UNION ALL SELECT 'home', 'diensten_title', 'Alles onder één dak', 'text', 'Titel diensten-sectie', 'diensten'
    UNION ALL SELECT 'home', 'diensten_text', 'Van officiële overheidsdiensten tot uw dagelijkse boodschap.', 'textarea', 'Introtekst diensten-sectie', 'diensten'
    UNION ALL SELECT 'home', 'tegel_postnl_title', 'PostNL Punt', 'text', 'Tegel 1 - Titel', 'diensten'
    UNION ALL SELECT 'home', 'tegel_postnl_desc', 'Snel pakketten versturen of ophalen.', 'textarea', 'Tegel 1 - Beschrijving', 'diensten'
    UNION ALL SELECT 'home', 'tegel_geldmaat_title', 'Geldmaat', 'text', 'Tegel 2 - Titel', 'diensten'
    UNION ALL SELECT 'home', 'tegel_geldmaat_desc', 'Veilig contant geld opnemen.', 'textarea', 'Tegel 2 - Beschrijving', 'diensten'
    UNION ALL SELECT 'home', 'tegel_rdw_title', 'RDW Diensten', 'text', 'Tegel 3 - Titel', 'diensten'
    UNION ALL SELECT 'home', 'tegel_rdw_desc', 'Overschrijven en rijbewijs verlengen.', 'textarea', 'Tegel 3 - Beschrijving', 'diensten'
    UNION ALL SELECT 'home', 'tegel_cadeaukaarten_title', 'Cadeaukaarten', 'text', 'Tegel 4 - Titel (alleen zichtbaar als module aan staat)', 'diensten'
    UNION ALL SELECT 'home', 'tegel_cadeaukaarten_desc', '100+ merken, direct leverbaar.', 'textarea', 'Tegel 4 - Beschrijving', 'diensten'
    UNION ALL SELECT 'home', 'tegel_pasfotos_title', 'Pasfoto''s', 'text', 'Tegel 5 - Titel', 'diensten'
    UNION ALL SELECT 'home', 'tegel_pasfotos_desc', 'Officieel erkend, ook RDW. Direct klaar.', 'textarea', 'Tegel 5 - Beschrijving', 'diensten'
    UNION ALL SELECT 'home', 'tegel_assortiment_title', 'Assortiment', 'text', 'Tegel 6 - Titel (alleen zichtbaar als module aan staat)', 'diensten'
    UNION ALL SELECT 'home', 'tegel_assortiment_desc', 'Tabak, snoep, boeken en meer.', 'textarea', 'Tegel 6 - Beschrijving', 'diensten'
) v
WHERE w.id IN ('5fd921b6-9a75-11f1-b88b-06bd6669bc40', '775d64f8-99a0-11f1-bc37-06bd6669bc40')
ON DUPLICATE KEY UPDATE site_content.id = site_content.id;

-- De 3 bestaande FAQ's op de contactpagina (nu hardcoded in debandijk/contact.php)
-- als losse rijen, zodat ze via websitebeheer.php (tab FAQ) beheerd kunnen
-- worden zodra contact.php ze uit de database leest i.p.v. hardcoded toont.
INSERT INTO faqs (id, website_id, question, answer)
SELECT UUID(), '775d64f8-99a0-11f1-bc37-06bd6669bc40', v.question, v.answer
FROM (
    SELECT 'Maken jullie pasfoto''s?' AS question, 'Ja! Wij maken officiële pasfoto''s voor paspoort, ID-kaart en rijbewijs (ook RDW digitaal verlengen). Geen afspraak nodig.' AS answer
    UNION ALL SELECT 'PostNL Pakket ophalen?', 'Je kunt bij ons terecht voor het versturen en ophalen van PostNL pakketten. Vergeet je legitimatiebewijs niet mee te nemen!'
    UNION ALL SELECT 'Zijn jullie op zondag open?', 'Nee, op zondag is onze winkel gesloten. Bekijk onze actuele openingstijden hierboven voor de rest van de week.'
) v
WHERE NOT EXISTS (SELECT 1 FROM faqs WHERE website_id = '775d64f8-99a0-11f1-bc37-06bd6669bc40');

CREATE TABLE IF NOT EXISTS team_members (
    id          char(36) NOT NULL PRIMARY KEY,
    website_id  char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    naam        varchar(150) NOT NULL,
    functie     varchar(150) NOT NULL,
    sort_order  int(11) DEFAULT 0,
    created_at  timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_team_members_website (website_id),
    CONSTRAINT fk_team_members_website FOREIGN KEY (website_id) REFERENCES websites(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- De 6 bestaande teamleden (nu hardcoded in debandijk/about.php) als losse rijen.
INSERT INTO team_members (id, website_id, naam, functie, sort_order)
SELECT UUID(), '775d64f8-99a0-11f1-bc37-06bd6669bc40', v.naam, v.functie, v.sort_order
FROM (
    SELECT 'Cindy' AS naam, 'Eigenaar' AS functie, 1 AS sort_order
    UNION ALL SELECT 'René', 'Eigenaar', 2
    UNION ALL SELECT 'Lysanne', 'Medewerker', 3
    UNION ALL SELECT 'Wendy', 'Medewerker', 4
    UNION ALL SELECT 'Bas', 'Medewerker', 5
    UNION ALL SELECT 'Iris', 'Medewerker', 6
) v
WHERE NOT EXISTS (SELECT 1 FROM team_members WHERE website_id = '775d64f8-99a0-11f1-bc37-06bd6669bc40');
