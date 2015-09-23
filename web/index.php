<?php

// error_reporting(0);

use Respect\Rest\Router;
use Icecave\SemVer\Version;

$packageRoot = dirname(__DIR__);
$matches = array();
if (preg_match('{^(.*)/vendor/.+/.+$}', $packageRoot, $matches)) {
	require $matches[1] . '/vendor/autoload.php';
} else {
	require $packageRoot . '/vendor/autoload.php';
}

if ( 
	(basename($_SERVER['SCRIPT_NAME']) == basename($_SERVER['SCRIPT_FILENAME'])) and 
	( ($basedir = dirname($_SERVER['SCRIPT_NAME'])) != "/")) 
{
	$router = new Router($basedir);
} else {
	$router = new Router();
}

// $router->exceptionRoute('InvalidArgumentException', function (InvalidArgumentException $e) {
// 	return '[InvalidArgumentException] Sorry, this error happened: '.$e->getMessage();
// });

$router->exceptionRoute('\\Exception', function (Exception $e) {
	return '[Exception] Sorry, this error happened: '.$e->getMessage();
});

$router->get('/js/**', function($jsPath) {
    return readfile(__DIR__. '/js/' . $jsPath[0]);
});

$router->any('/**', 'Koshatul\\HAProxyWeb\\HTML\\v1_0_0\\Stats');


