<?php
// Centrale include-hub: elke pagina hoeft alleen dit bestand te includen om
// een sessie, databaseverbinding, auth-helpers, CSRF-helpers en de
// content-functies tot haar beschikking te hebben.
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/db_config.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/upload.php';
require_once __DIR__ . '/rate_limit.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/custom_fields.php';
