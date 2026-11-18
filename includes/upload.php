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
    if (in_array($file['error'], [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)) {
        throw new UploadException('"' . $file['name'] . '" is te groot (max ' . ini_get('upload_max_filesize') . ' per afbeelding).');
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new UploadException('Upload mislukt (foutcode ' . $file['error'] . ').');
    }
    if ($file['size'] > $maxBytes) {
        throw new UploadException('Bestand is te groot (max ' . round($maxBytes / 1024 / 1024) . 'MB).');
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);

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

// Zelfde als save_uploaded_image(), maar voor een <input type="file" name="x[]" multiple>.
// Geeft de paden van alle opgeslagen afbeeldingen terug; mislukt er één, dan
// worden de al opgeslagen bestanden van deze aanroep weer verwijderd.
function save_uploaded_images(string $field, string $websiteId): array
{
    $paths = [];
    if (empty($_FILES[$field]['name']) || !is_array($_FILES[$field]['name'])) {
        return $paths;
    }
    $set = $_FILES[$field];
    try {
        foreach (array_keys($set['name']) as $i) {
            $_FILES['__single_image'] = [
                'name' => $set['name'][$i],
                'type' => $set['type'][$i],
                'tmp_name' => $set['tmp_name'][$i],
                'error' => $set['error'][$i],
                'size' => $set['size'][$i],
            ];
            $path = save_uploaded_image('__single_image', $websiteId);
            if ($path !== null) {
                $paths[] = $path;
            }
        }
    } catch (UploadException $e) {
        foreach ($paths as $p) {
            delete_uploaded_file($p);
        }
        throw $e;
    } finally {
        unset($_FILES['__single_image']);
    }
    return $paths;
}

// '8M' / '2G' / '512K' uit php.ini naar bytes.
function ini_size_bytes(string $value): int
{
    $value = trim($value);
    $num = (int)$value;
    switch (strtoupper(substr($value, -1))) {
        case 'G': return $num * 1024 ** 3;
        case 'M': return $num * 1024 ** 2;
        case 'K': return $num * 1024;
        default:  return $num;
    }
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
