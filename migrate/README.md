# Migratie naar de multi-tenant portal

Deze map bevat de databasewijzigingen die nodig zijn om de herbouwde code te
laten draaien op de bestaande, live database. Niets hierin is automatisch
uitgevoerd — dit zijn scripts om zelf te bekijken en te draaien, in deze
volgorde.

## Volgorde

1. **Backup eerst.** Maak een volledige databasedump (mysqldump, of via het
   hostingpaneel) voordat je verdergaat.
2. **`001_login_attempts.sql`** — voegt de tabel toe voor login-rate-limiting.
   Veilig, additief, geen bestaande data wordt aangeraakt.
3. **`002_seed_first_website.sql`** — maakt de eerste `websites`-rij aan en
   zorgt dat die overeenkomt met de `website_id`-waarden die al in de
   bestaande content staan. **Lees de instructies in het bestand zelf** —
   het begint met een inspectiequery, en de vervolgstap hangt af van wat die
   query teruggeeft. Bepaal ook of het bestaande beheerdersaccount
   `super_admin` moet worden (kan meerdere klantwebsites beheren) of
   `client` blijft (blijft aan deze ene website gekoppeld).
4. **`003_move_uploads.php`** — verplaatst bestanden uit de platte
   `uploads/`-map naar `uploads/{website_id}/` en werkt de paden in de
   database bij. Draai dit via de command line, ná stap 3:
   ```
   php migrate/003_move_uploads.php <website_id-uit-stap-2>
   ```
5. **`004_custom_fields.sql`** — voegt de tabellen toe voor `super_velden.php`
   (het scherm waarmee een super admin nieuwe tags/keys/velden kan toevoegen
   aan de content-instellingen en aan producten/portfolio/USP's/reviews/FAQ's).
   Veilig, additief, geen bestaande data wordt aangeraakt.
6. **`005_website_modules.sql`** — voegt `website_modules` toe: het schakelbord
   waarmee per website wordt bepaald welke site-specifieke modules (assortiment,
   cadeaukaarten, nieuws, prijsvraag, geschiedenis) actief zijn. Dit is het
   mechanisme achter "pagina's die alleen voor deze website zijn" — een module
   die uit staat, is nergens in de navigatie zichtbaar en de bijbehorende
   beheerpagina (bijv. `assortimentbeheer.php`) weigert toegang. Veilig,
   additief; zet meteen alle 5 modules aan voor debandijk en uit voor overige
   websites.
7. **`006_module_tables.sql`** — voegt de tabellen toe voor die vijf modules.
   **Let op:** dit script laat eerst `assortiment_merken`, `prijsvraag_inzendingen`
   en `opening_hours` vallen — die tabellen bestonden al in deze database maar
   waren leeg en hadden een foreign key naar een tabel die niet bestond (een
   restant van een eerdere, afgebroken poging). Geverifieerd leeg vóór het
   schrijven van dit script; er gaat geen data verloren.
8. **`007_migrate_debandijk_data.php`** — kopieert de bestaande content van
   debandijk's oude, losse `debandijk`-database naar de nieuwe module-tabellen
   hierboven (assortiment, cadeaukaarten, nieuws, prijsvraag, openingstijden,
   geschiedenis, contactberichten en de beheerders-accounts Cindy/Rene — het
   account Sebastiaan bestond al). **Niet idempotent — draai dit precies één
   keer.** Vereist een `website_id`:
   ```
   php migrate/007_migrate_debandijk_data.php <website_id-van-debandijk>
   ```
   Print aan het eind een samenvatting én een waarschuwing voor eventuele
   nieuw aangemaakte gebruikers met een placeholder-e-mailadres — die moeten
   via `profielbeheer.php` naar een echt adres worden bijgewerkt (nodig voor
   de wachtwoord-vergeten-flow).
9. **Handmatige stap — uploads-symlink.** debandijk en webiusportaal zijn
   aparte document roots op dezelfde server. Nieuw geüploade afbeeldingen
   (via de nieuwe beheerpagina's) staan in `webiusportaal/uploads/{website_id}/`,
   maar debandijk's publieke pagina's laden afbeeldingen relatief vanuit hun
   eigen document root. Zonder deze stap 404'en die afbeeldingen op de
   publieke site:
   ```
   cd debandijk && ln -s ../webiusportaal/uploads uploads
   ```
   Herhaal dit ook op productie (of vervang het door een kopieerstap als
   productie ooit gescheiden hosting-accounts voor de twee repo's gebruikt).

## Na de migratie

- Log in via `login.php` en controleer dat het dashboard en
  `websitebeheer.php` de bestaande content laten zien.
- Test de super_admin-siteswitcher pas als er via `super_websites.php` een
  tweede (test)website is aangemaakt.
- Controleer de vijf nieuwe modulepagina's (`assortimentbeheer.php`,
  `cadeaukaartenbeheer.php`, `nieuwsbeheer.php`, `prijsvraagbeheer.php`,
  `geschiedenisbeheer.php`) tonen de gemigreerde data, en dat ze **niet**
  bereikbaar zijn (403) zodra hun module in `super_websites.php` wordt
  uitgeschakeld voor een website.
- Controleer dat `debandijk/beheer/` (de oude admin, los van dit alles) nog
  ongewijzigd werkt — deze migratie leest de oude `debandijk`-database
  precies één keer en verandert er verder niets aan.
- Verwijder of verplaats deze `migrate/`-map niet uit git totdat de migratie
  succesvol is uitgevoerd — de scripts zijn ook nuttig als referentie mocht
  er iets misgaan.

10. **`009_remember_tokens.sql`** — voegt de tabel toe voor de "Onthoud
    mij"-optie op `login.php` (`includes/remember_me.php`). Veilig, additief,
    geen bestaande data wordt aangeraakt. Tot deze migratie is toegepast
    werkt inloggen gewoon door, alleen staat "onthoud mij" dan stilzwijgend
    uit (zelfde fail-safe patroon als `login_attempts`).
11. **`010_editable_content_expansion.sql`** — maakt een reeks voorheen
    hardcoded teksten op debandijk bewerkbaar via `websitebeheer.php`
    (hero-teksten van assortiment/cadeaukaarten/nieuws/prijsvraag/
    geschiedenis, de wettelijke tabakstekst, de "in ontwikkeling"-melding,
    de CTA op de geschiedenispagina en de tekst van de diensten-tegels op
    de homepage), zet de 3 bestaande FAQ's op de contactpagina om naar
    rijen in `faqs` (tab FAQ), en voegt de nieuwe tabel `team_members` toe
    (tab Team) met de 6 bestaande teamleden als startdata. Idempotent —
    veilig om opnieuw te draaien, overschrijft geen latere bewerkingen.
12. **`011_footer_links_and_meta_titles.sql`** — voegt de nieuwe tabel
    `footer_links` toe (de "Services"- en "Navigatie"-links onderaan
    debandijk, nu bewerkbaar via `footerbeheer.php` tab "Links" i.p.v.
    hardcoded in `debandijk/footer.php`), geseed met de 9 links die er nu
    hardcoded stonden. Voegt ook site_content-rijen toe voor de `<title>`-tag
    van elke pagina (`meta_title`, tab "Algemene Teksten" op
    `websitebeheer.php`) en de merknaam in de footer-kop (`site_naam`).
    Idempotent — veilig om opnieuw te draaien.
13. **`012_footer_link_icons_and_service_pages.sql`** — voegt een
    `icon`-kolom toe aan `footer_links` (elke link toont weer een eigen
    icoon i.p.v. één generiek pijltje; `footerbeheer.php` kiest het icoon
    automatisch aan de hand van de gekozen pagina) en maakt de 4 losse
    services-pagina's (`debandijk/services/pasfotos.php`, `postnl.php`,
    `rdw.php`, `geldmaat.php`) bewerkbaar via `websitebeheer.php` (titel,
    `<title>`-tag en introtekst). Idempotent — veilig om opnieuw te draaien.
14. **`013_user_first_last_name.sql`** — voegt `first_name` en `last_name` toe
    aan `users` (optioneel, NULL toegestaan). Gebruikt door `profielbeheer.php`
    en de gebruikerslijst in `super_websites.php` om een volledige naam te
    tonen i.p.v. alleen de gebruikersnaam; ontbreekt de naam nog, dan valt de
    UI terug op de gebruikersnaam. Veilig, additief, geen bestaande data
    wordt aangeraakt.
