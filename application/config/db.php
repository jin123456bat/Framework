<?php
$dbpass = '';

return array(
	'cloud_web_v2' => array(
		'db_type' => 'mysql',
		'db_server' => 'localhost',
		'db_dbname' => 'cloud_web_v2',
		'db_user' => 'root',
		'db_password' => $dbpass,
		'db_charset' => 'utf8',
	),
	'ordoac' => array(
		'db_type' => 'mysql',
		'db_server' => 'localhost',
		'db_dbname' => 'ordoac',
		'db_user' => 'root',
		'db_password' => $dbpass,
		'db_charset' => 'utf8',
		'default' => true,
	),
	'cds_v2' => array(
		'db_type' => 'mysql',
		'db_server' => 'localhost',
		'db_dbname' => 'cds_v2',
		'db_user' => 'root',
		'db_password' => $dbpass,
		'db_charset' => 'utf8',
		
		/* 'db_server' => '192.168.1.225',
		'db_dbname' => 'ordoac',
		'db_user' => 'admin',
		'db_password' => 'fxdata2000',
		 */
		//'default' => true,//默认的数据库连接方式
	),
	'django' => array(
		'db_type' => 'mysql',
		'db_server' => '192.168.1.225',
		'db_dbname' => 'django',
		'db_user' => 'admin',
		'db_password' => 'fxdata2000',
		'db_charset' => 'utf8',
	)
);