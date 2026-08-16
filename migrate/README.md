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

## Na de migratie

- Log in via `login.php` en controleer dat het dashboard en
  `websitebeheer.php` de bestaande content laten zien.
- Test de super_admin-siteswitcher pas als er via `super_websites.php` een
  tweede (test)website is aangemaakt.
- Verwijder of verplaats deze `migrate/`-map niet uit git totdat de migratie
  succesvol is uitgevoerd — de scripts zijn ook nuttig als referentie mocht
  er iets misgaan.
