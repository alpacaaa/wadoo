<?php

// PHP 5.4 ROUTER
// cd public && php -S localhost:8000 router.php

$uri  = trim($_SERVER['REQUEST_URI'], '/');

if ($uri && file_exists($uri)) return false;


// This smells like shit
// I honestly don't give a fuck :)
$_GET['action'] = 'compile';
$_GET['uri']  = $uri ? $uri : 'index.html';
$_GET['echo'] = 1;

require '../index.php';