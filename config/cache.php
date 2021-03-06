<?php
return array(
	'type' => 'mysql', // 默认的缓存类型 mysql memcache file redis apc
	
	// 当type为memcache的时候，以下配置memcache的相关信息
	//考虑到windows的兼容性
	'memcache' => array(
		array(
			'host' => '192.168.9.58',
			'port' => 11211,
			'weight' => 100
		)
	),
	
	// 当type为mysql的时候，以下配置mysql的相关信息，假如没有使用db中的配置，暂时还无法使用自定义的配置
	'mysql' => array(
		array(
			'host' => 'localhost',
			'port' => 3306,
			'user' => 'root',
			'password' => '',
			'dbname' => 'test',
			'charset' => 'utf8',
			'init_command' => ''
		)
	),
	
	// 当type为redis的时候，一下配置redis的相关信息 尚未实现
	'redis' => array(
		array(
			'host' => '192.168.9.58',
			'port' => 6379,
			'timeout' => 1, // 超时时间
			'password' => 'jin2164389',
			'database' => 0
		)
	),
	
	// 当type为file的时候，以下配置生效
	'file' => array(
		'path' => APP_ROOT.'/cache/file' // 文件路径 注意 这必须是一个文件夹的路径 并且存在
	)
);

