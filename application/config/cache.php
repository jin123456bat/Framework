<?php
return array(
	'type' => 'apc', // 缓存类型  mysql memcached
	'expires' => 0, // 默认缓存时间 永久有效
	
	//当type为memcached的时候，以下配置memcached的相关信息
	'server' => array(
		array(
			'host' => 'localhost',
			'port' => 11211,
			'weight' => 100,
		)
	),
	
	//当type为mysql的时候，以下配置mysql的相关信息，假如没有使用db中的配置
	'server' => array(
		array(
			'host' => 'localhost',
			'port' => 3306,
			'user' => 'root',
			'password' => '',
			'dbname' => 'test',
			'charset' => 'utf8',
			'init_command' => '',
		)
	),
	
);

