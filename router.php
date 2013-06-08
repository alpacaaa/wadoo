<?php

// PHP 5.4 ROUTER
// php -S localhost:8000 public/router.php

$uri  = trim($_SERVER['REQUEST_URI'], '/');
$file = 'public/'. $uri;

if ($uri !== '/' && file_exists($file)) {
	$finfo = finfo_open(FILEINFO_MIME_TYPE);
	header('Content-type: '. finfo_file($finfo, $file));
	ob_clean();
    flush();
    readfile($file);
	die();
}


// This smells like shit
// I honestly don't give a fuck :)
$_GET['action'] = 'compile';
$_GET['uri']  = $uri;
$_GET['echo'] = 1;

require 'index.php';