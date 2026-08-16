<?php
class UploadException extends RuntimeException
{
}

// Valideert (echte mimetype, niet de browser-hint) en slaat een geuploade afbeelding
// op onder uploads/{website_id}/, met een willekeurige bestandsnaam. Geeft het
// relatieve pad terug, of null als er (nog) geen bestand is gekozen.
function save_uploaded_image(string $field, string $websiteId, int $maxBytes = 5242880): ?string
{
    if (!isset($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    $file = $_FILES[$field];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new UploadException('Upload mislukt (foutcode ' . $file['error'] . ').');
    }
    if ($file['size'] > $maxBytes) {
        throw new UploadException('Bestand is te groot (max ' . round($maxBytes / 1024 / 1024) . 'MB).');
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
    ];
    if (!isset($allowed[$mime])) {
        throw new UploadException('Alleen JPG, PNG, WEBP of GIF-afbeeldingen zijn toegestaan.');
    }

    $dir = rtrim(website_upload_dir($websiteId), '/') . '/';
    ensure_upload_dir($dir);

    $filename = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
    $target = $dir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $target)) {
        throw new UploadException('Kon het bestand niet opslaan op de server.');
    }

    return $target;
}

function website_upload_dir(string $websiteId): string
{
    return "uploads/{$websiteId}";
}

function ensure_upload_dir(string $dir): void
{
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    $htaccess = rtrim($dir, '/') . '/.htaccess';
    if (!file_exists($htaccess)) {
        // Defense-in-depth: zelfs als de mime-validatie ooit omzeild wordt, mag
        // een bestand in de uploads-map nooit als PHP worden uitgevoerd.
        file_put_contents($htaccess, "<IfModule mod_php.c>\n    php_flag engine off\n</IfModule>\n<FilesMatch \"\\.(php|phtml|php\\d)$\">\n    Require all denied\n</FilesMatch>\n");
    }
}

function delete_uploaded_file(?string $path): void
{
    if (!empty($path) && is_file($path)) {
        @unlink($path);
    }
}
