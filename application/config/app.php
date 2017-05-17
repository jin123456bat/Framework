<?php
return array(
	'charset' => 'UTF-8',
	
	'import' => array(
		'application/functions/index.php'
	),
	// 'application/algorithm/*',
	
	'query_cache' => false,
	
	'cache' => true,
	
	'errorHandler' => array(
		'class' => '/application/extend/errorHandler::run',
		'types' => E_ALL,
		'storage' => 'file'
	),
	
);
