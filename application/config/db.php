<?php
$dbuser = 'root';

switch($_SERVER['HTTP_HOST'])
{
	case '192.168.1.225':
		$dbhost = 'localhost';
		$dbpass = '';
		$dbuser = 'root';break;
	case 'localhost':
		$dbhost = '192.168.1.225';
		$dbpass = 'fxdata2000';
		$dbuser = 'admin';
	break;
	case '192.168.0.73':$dbpass = 'jin2164389';$dbuser = 'root';break;
}
$sql_mode = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';

$init_command = array(
	'set sql_mode = "'.$sql_mode.'"',
);
return array(
	'cloud_web_v2' => array(
		'db_type' => 'mysql',
		'db_server' => $dbhost,
		'db_dbname' => 'cloud_web_v2',
		'db_user' => $dbuser,
		'db_password' => $dbpass,
		'db_charset' => 'utf8',
		'init_command' => $init_command,
	),
	'ordoac' => array(
		'db_type' => 'mysql',
		'db_server' => $dbhost,
		'db_dbname' => 'ordoac',
		'db_user' => $dbuser,
		'db_password' => $dbpass,
		'db_charset' => 'utf8',
		'default' => true,
		'init_command' => $init_command,
	),
	'cds_v2' => array(
		'db_type' => 'mysql',
		'db_server' => $dbhost,
		'db_dbname' => 'cds_v2',
		'db_user' => $dbuser,
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
		'db_server' => $dbhost,
		'db_dbname' => 'django',
		'db_user' => $dbuser,
		'db_password' => $dbpass,
		'db_charset' => 'utf8',
		'init_command' => $init_command,
	),
	'xvirt' => array(
		'db_type' => 'mysql',
		'db_server' => $dbhost,
		'db_dbname' => 'xvirt',
		'db_user' => $dbuser,
		'db_password' => $dbpass,
		'db_charset' => 'utf8',
		'init_command' => $init_command,
	),
);