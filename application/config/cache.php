<?php
return array(
	'type' => 'memcached', // 缓存类型  mysql memcached
	'expires' => 0, // 默认缓存时间 永久有效
	
	//当type为memcached的时候，以下配置memcached的相关信息
	'server' => array(
		array(
			'host' => 'localhost',
			'port' => 11211,
			'weight' => 100,
		),
		array(
			'host' => '172.0.0.1',
			'port' => 11211,
			'weight' => 200,
		)
	)
);

