<?php

	namespace Wadoo;

	require_once 'vendor/autoload.php';

	libxml_use_internal_errors(true);

	$app = new App();
	$app->registerFilters(array(
		new Filters\HTML5(),
		new Filters\Markdown()
	));

	try {
		$action = isset($_GET['action']) ? $_GET['action'] : 'index';
		echo $app->run($action);
	}
	catch (Exception $ex) {
		echo $ex->render();
	}
