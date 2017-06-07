<?php
return array(
	'charset' => 'UTF-8',
	
	//自定义的文件导入  可以通篇使用的代码
	'import' => array(
		'/functions/index.php'
	),
	
	'query_cache' => false,
	
	'cache' => true,
	
	'errorHandler' => array(
		'class' => '/application/extend/errorHandler::run',
		'types' => E_ALL,
		'storage' => 'file'
	),
	
);
