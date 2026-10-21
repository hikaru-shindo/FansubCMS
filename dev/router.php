<?php
/**
 * Router script for PHP's built-in web server (development only).
 *
 * Serves existing files from the document root directly and sends every other
 * request to public/index.php, with $_SERVER set up the way Apache or nginx would,
 * so Zend Framework 1 detects the base URL correctly.
 *
 * Usage: php -S 0.0.0.0:8080 -t public docker/router.php
 */

$documentRoot = rtrim($_SERVER['DOCUMENT_ROOT'], '/');
$path = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/');

// Existing files (CSS, JS, images, ...) are served by the built-in server itself.
$file = realpath($documentRoot . $path);
if ($path !== '/'
    && $file !== false
    && str_starts_with($file, $documentRoot . '/')
    && is_file($file)
) {
    return false;
}

$_SERVER['SCRIPT_FILENAME'] = $documentRoot . '/index.php';
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['PHP_SELF'] = '/index.php';
unset($_SERVER['PATH_INFO'], $_SERVER['ORIG_PATH_INFO']);

chdir($documentRoot);
require $documentRoot . '/index.php';
