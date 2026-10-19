<?php

declare(strict_types=1);

/**
 * HTTP front controller.
 *
 *   /avatar/{hash}.png?type=anime&s=80      (also ?size=, ?nocache=1)
 *   /{hash}.png?type=anime
 *
 * Point the web server's rewrite at this file. `hash` is typically md5(lowercase(email)).
 */

$root = dirname(__DIR__);
$composer = $root . '/vendor/autoload.php';
require is_file($composer) ? $composer : $root . '/src/autoload.php';

use Dramatar\Avatar;

const DEFAULT_TYPE = 'anime';
const DEFAULT_SIZE = 80;
const FORMATS = ['png' => 'image/png', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'webp' => 'image/webp', 'avif' => 'image/avif', 'gif' => 'image/gif'];

$path = basename((string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));
$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
$format = isset(FORMATS[$ext]) ? $ext : 'png';
$hash = preg_replace('/[^a-zA-Z0-9]/', '', pathinfo($path, PATHINFO_FILENAME)) ?? '';
$type = preg_replace('/[^a-z]/', '', strtolower((string) ($_GET['type'] ?? DEFAULT_TYPE))) ?: DEFAULT_TYPE;
$size = (int) ($_GET['size'] ?? $_GET['s'] ?? DEFAULT_SIZE) ?: DEFAULT_SIZE;
$size = max(8, min(512, $size));

$avatar = new Avatar();
if ($hash === '' || !$avatar->has($type)) {
    http_response_code(404);
    header('Content-Type: text/plain');
    exit('Unknown avatar type or missing hash. Types: ' . implode(', ', $avatar->types()));
}

$cacheFile = sprintf('%s/cache/%s/%s/%s_%s_%d.%s', $root, $hash[0], $hash[1] ?? '_', $hash, $type, $size, $format);
$etag = '"' . md5($cacheFile) . '"';
$ttl = 31536000;

header('Content-Type: ' . FORMATS[$format]);
header("Cache-Control: public, max-age=$ttl, immutable");
header('ETag: ' . $etag);

if (($_SERVER['HTTP_IF_NONE_MATCH'] ?? '') === $etag) {
    http_response_code(304);
    exit;
}

if (!is_file($cacheFile) || isset($_GET['nocache'])) {
    $bytes = $avatar->render($type, $hash, $size, $format);
    if (!is_dir(dirname($cacheFile))) {
        mkdir(dirname($cacheFile), 0755, true);
    }
    file_put_contents($cacheFile, $bytes, LOCK_EX);
}

header('Content-Length: ' . filesize($cacheFile));
readfile($cacheFile);
