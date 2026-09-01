-- Voegt voornaam/achternaam toe aan users, voor profielbeheer.php en de
-- gebruikerslijst in super_websites.php. Optioneel (NULL toegestaan) zodat
-- bestaande accounts blijven werken zonder dat iemand deze meteen invult.
-- Veilig, additief: ADD COLUMN IF NOT EXISTS doet niets als de kolom al
-- bestaat.

ALTER TABLE users
    ADD COLUMN IF NOT EXISTS first_name varchar(100) DEFAULT NULL AFTER username,
    ADD COLUMN IF NOT EXISTS last_name varchar(100) DEFAULT NULL AFTER first_name;
