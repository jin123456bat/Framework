<?php
return array(
	'import' => array(
		'application/functions/index.php',
		//'application/algorithm/*',
	),
	
	'query_cache' => false,
	
	'layout' => 'layout',
	
	'cache' => false,
	
	'errorHandler' => array(
		'class' => '/application/extend/errorHandler::run',
		'types' => E_ALL,
		'storage' => 'file',
	),
);