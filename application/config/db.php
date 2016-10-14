<?php
$dbpass = '';
$sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';

$init_command = array(
	//'set max_allowed_packet = '.(2*1024*1024*10*100),
	//'set sql_mode = '.$sql_mode,
);
return array(
	'cloud_web_v2' => array(
		'db_type' => 'mysql',
		'db_server' => 'localhost',
		'db_dbname' => 'cloud_web_v2',
		'db_user' => 'root',
		'db_password' => $dbpass,
		'db_charset' => 'utf8',
		'init_command' => $init_command,
	),
	'ordoac' => array(
		'db_type' => 'mysql',
		'db_server' => 'localhost',
		'db_dbname' => 'ordoac',
		'db_user' => 'root',
		'db_password' => $dbpass,
		'db_charset' => 'utf8',
		'default' => true,
		'init_command' => $init_command,
	),
	'cds_v2' => array(
		'db_type' => 'mysql',
		'db_server' => 'localhost',
		'db_dbname' => 'cds_v2',
		'db_user' => 'root',
		'db_password' => $dbpass,
		'db_charset' => 'utf8',
		'init_command' => $init_command,
		
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
		'init_command' => $init_command,
	)
);