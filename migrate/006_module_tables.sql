-- Voegt de tabellen toe voor de vijf site-specifieke modules (zie
-- 005_website_modules.sql voor de aan/uit-schakelaar per website).
--
-- assortiment_merken en prijsvraag_inzendingen bestaan al in deze database,
-- maar zijn leeg (0 rijen, geverifieerd) en hebben foreign keys naar
-- tabellen die niet bestaan (assortiment_categories, prijsvraag_instellingen)
-- — restanten van een eerdere, afgebroken poging. opening_hours bestaat ook
-- al, leeg, zonder website_id. Voor alle drie is DROP + CREATE veiliger en
-- simpeler dan een ALTER die eerst de kapotte constraint zou moeten
-- opruimen; er gaat geen data verloren.

DROP TABLE IF EXISTS assortiment_merken;
DROP TABLE IF EXISTS prijsvraag_inzendingen;
DROP TABLE IF EXISTS opening_hours;

CREATE TABLE IF NOT EXISTS assortiment_categorieen (
    id          char(36) NOT NULL PRIMARY KEY,
    website_id  char(36) NOT NULL,
    titel       varchar(255) NOT NULL,
    beschrijving text NOT NULL,
    afbeelding  varchar(255) DEFAULT NULL,
    badge_tekst varchar(100) DEFAULT NULL,
    sort_order  int(11) DEFAULT 0,
    created_at  timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_assortiment_categorieen_website (website_id),
    CONSTRAINT fk_assortiment_categorieen_website FOREIGN KEY (website_id) REFERENCES websites(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE assortiment_merken (
    id          char(36) NOT NULL PRIMARY KEY,
    website_id  char(36) NOT NULL,
    category_id char(36) NOT NULL,
    naam        varchar(100) NOT NULL,
    logo_path   varchar(255) NOT NULL,
    sort_order  int(11) DEFAULT 0,
    KEY idx_assortiment_merken_website (website_id),
    KEY idx_assortiment_merken_category (category_id),
    CONSTRAINT fk_assortiment_merken_website  FOREIGN KEY (website_id)  REFERENCES websites(id) ON DELETE CASCADE,
    CONSTRAINT fk_assortiment_merken_category FOREIGN KEY (category_id) REFERENCES assortiment_categorieen(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS cadeaukaarten (
    id                char(36) NOT NULL PRIMARY KEY,
    website_id        char(36) NOT NULL,
    kaart_naam        varchar(100) NOT NULL,
    geschikte_winkels text NOT NULL,
    tags              text NOT NULL,
    afbeelding        varchar(255) DEFAULT NULL,
    sort_order        int(11) DEFAULT 0,
    created_at        timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_cadeaukaarten_website (website_id),
    CONSTRAINT fk_cadeaukaarten_website FOREIGN KEY (website_id) REFERENCES websites(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS nieuws (
    id          char(36) NOT NULL PRIMARY KEY,
    website_id  char(36) NOT NULL,
    title       varchar(255) NOT NULL,
    content     text NOT NULL,
    image_paths text DEFAULT NULL,
    status      enum('draft','published') NOT NULL DEFAULT 'published',
    created_at  timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_nieuws_website_status_created (website_id, status, created_at),
    CONSTRAINT fk_nieuws_website FOREIGN KEY (website_id) REFERENCES websites(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS prijsvraag_instellingen (
    website_id       char(36) NOT NULL PRIMARY KEY,
    huidige_vraag    text NOT NULL,
    huidige_prijs    varchar(255) NOT NULL,
    afbeelding_url   varchar(255) DEFAULT NULL,
    is_actief        tinyint(1) NOT NULL DEFAULT 0,
    toon_antwoorden  tinyint(1) NOT NULL DEFAULT 0,
    updated_at       timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_prijsvraag_instellingen_website FOREIGN KEY (website_id) REFERENCES websites(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE prijsvraag_inzendingen (
    id          char(36) NOT NULL PRIMARY KEY,
    website_id  char(36) NOT NULL,
    voornaam    varchar(50) NOT NULL,
    achternaam  varchar(50) NOT NULL,
    email       varchar(100) NOT NULL,
    telefoon    varchar(20) DEFAULT NULL,
    antwoord    text NOT NULL,
    created_at  timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_prijsvraag_inzendingen_website (website_id),
    UNIQUE KEY uq_inzending_website_email (website_id, email),
    CONSTRAINT fk_inzendingen_website FOREIGN KEY (website_id) REFERENCES websites(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE opening_hours (
    website_id   char(36) NOT NULL,
    day_of_week  tinyint(4) NOT NULL, -- 0=zondag .. 6=zaterdag (JS Date.getDay())
    opens        time DEFAULT NULL,
    closes       time DEFAULT NULL,
    is_closed    tinyint(1) NOT NULL DEFAULT 0,
    note         varchar(100) DEFAULT NULL, -- bijv. "Koopavond"
    PRIMARY KEY (website_id, day_of_week),
    CONSTRAINT fk_opening_hours_website FOREIGN KEY (website_id) REFERENCES websites(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS geschiedenis (
    website_id  char(36) NOT NULL PRIMARY KEY,
    title       varchar(255) NOT NULL DEFAULT 'Onze Geschiedenis',
    content     text NOT NULL,
    updated_at  timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_geschiedenis_website FOREIGN KEY (website_id) REFERENCES websites(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
