<?php

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$file = __DIR__ . $uri;

/*
 * Let PHP's built-in server serve real static files directly.
 * Everything else goes through the application's root index.php.
 */
if ($uri !== '/' && is_file($file)) {
    return false;
}

require __DIR__ . '/index.php';