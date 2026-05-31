<?php
// Strip the /bot prefix from the request URI
$uri = $_SERVER['REQUEST_URI'];
$uri = preg_replace('#^/bot#', '', $uri);
$uri = strtok($uri, '?');

if ($uri === '' || $uri === '/') {
    $uri = '/bot.php';
}

$file = __DIR__ . $uri;

// Read php://input once and share via global so bot.php can read it
$GLOBALS['_RAW_INPUT'] = file_get_contents('php://input');

if (is_file($file) && pathinfo($file, PATHINFO_EXTENSION) !== 'php') {
    return false; // serve static files directly
}

// Route everything to bot.php
$_SERVER['REQUEST_URI'] = $uri;
include __DIR__ . '/bot.php';
