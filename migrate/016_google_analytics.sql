-- Voegt per website een Google Analytics 4 property-ID toe, voor de
-- bezoekersgrafiek op het dashboard (beheer.php). Leeg (NULL) = geen
-- analytics-blok op het dashboard voor die website.
--
-- Alle websites gebruiken hetzelfde service-account
-- (credentials/analytics-key.json, of het pad in GA_CREDENTIALS_FILE in .env).
-- Geef dat service-account in GA4 "Viewer"-toegang tot de property van elke
-- extra website en vul daarna het property-ID in via super_websites.php.
--
-- Veilig, additief; idempotent. Zet meteen het property-ID van debandijk
-- (overgenomen uit debandijk/beheer/dashboard.php), maar overschrijft geen
-- waarde die al is ingevuld.

ALTER TABLE websites
    ADD COLUMN IF NOT EXISTS ga_property_id varchar(32) DEFAULT NULL AFTER font_family;

UPDATE websites
   SET ga_property_id = '522235041'
 WHERE id = '775d64f8-99a0-11f1-bc37-06bd6669bc40'
   AND (ga_property_id IS NULL OR ga_property_id = '');
