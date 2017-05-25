<?php
$sql_mode = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
$init_command = array(
	'set sql_mode = "' . $sql_mode . '"'
);
// 'set max_allowed_packet = 20*1024*1024'

/*
 * 数据库配置文件
 * model 定义了哪些model使用这个配置  假如一个model在多个配置中同时存在，第一个生效
 * default 定了一个默认的配置，在没有声明model使用的配置的时候使用default配置
 * 假如上面都没有 使用第一个配置
 */
return array(
	'cloud_web_v2' => array(
		'model' => array(
			//定义了哪些model使用这个配置
		),
		'type' => 'mysql',
		'server' => '192.168.1.225',
		'dbname' => 'cloud_web_v2',
		'user' => 'cm2_admin',
		'password' => 'fxd^CM2-2016',
		'charset' => 'utf8',
		'init_command' => $init_command
	),
	'ordoac' => array(
		'type' => 'mysql',
		'server' => '192.168.1.225',
		'dbname' => 'ordoac',
		'user' => 'selecter',
		'password' => 'fxdata_Select-2016',
		'charset' => 'utf8',
		'init_command' => $init_command
	),
	'cds_v2' => array(
		'type' => 'mysql',
		'server' => '192.168.1.12',
		'dbname' => 'cds_v2',
		'user' => 'selector',
		'port' => 3321,
		'password' => 'fxdata_Select-2016',
		'charset' => 'utf8',
		'init_command' => $init_command
	),
	'django' => array(
		'type' => 'mysql',
		'server' => '192.168.1.225',
		'dbname' => 'django',
		'user' => 'selecter',
		'password' => 'fxdata_Select-2016',
		'charset' => 'utf8',
		'init_command' => $init_command
	),
	'test' => array(
		'default' => true,
		'type' => 'mysql',
		'server' => 'localhost',
		'dbname' => 'test',
		'user' => 'root',
		'password' => '',
		'charset' => 'utf8',
	)
);
