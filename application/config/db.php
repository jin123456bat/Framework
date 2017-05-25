<?php
$sql_mode = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
$init_command = array(
	'set sql_mode = "' . $sql_mode . '"'
);
// 'set max_allowed_packet = 20*1024*1024'

return array(
	'cloud_web_v2' => array(
		'type' => 'mysql',
		'server' => array(
			//配置读写分离
			'read' => array(
				'server'=>'192.168.1.225',
			),
			'write' => '192.168.1.225',
		),
		'dbname' => 'cloud_web_v2',
		'user' => 'cm2_admin',
		'password' => 'fxd^CM2-2016',
		'charset' => 'utf8',
		'init_command' => $init_command
	),
	'ordoac' => array(
		'db_type' => 'mysql',
		'db_server' => '192.168.1.225',
		'db_dbname' => 'ordoac',
		'db_user' => 'selecter',
		'db_password' => 'fxdata_Select-2016',
		'db_charset' => 'utf8',
		'init_command' => $init_command
	),
	'cds_v2' => array(
		'db_type' => 'mysql',
		'db_server' => '192.168.1.12',
		'db_dbname' => 'cds_v2',
		'db_user' => 'selector',
		'db_port' => 3321,
		'db_password' => 'fxdata_Select-2016',
		'db_charset' => 'utf8',
		'init_command' => $init_command
	),
	'django' => array(
		'db_type' => 'mysql',
		'db_server' => '192.168.1.225',
		'db_dbname' => 'django',
		'db_user' => 'selecter',
		'db_password' => 'fxdata_Select-2016',
		'db_charset' => 'utf8',
		'init_command' => $init_command
	),
	'test' => array(
		'default' => true,
		'db_type' => 'mysql',
		'db_server' => 'localhost',
		'db_dbname' => 'test',
		'db_user' => 'root',
		'db_password' => '',
		'db_charset' => 'utf8',
	)
);
