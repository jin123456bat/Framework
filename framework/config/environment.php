<?php
return array(
	'display_errors' => 'On',
	'error_reporting' => E_ALL,
	'memory_limit' => '128M',
	
	'max_execution_time' => 60,
	'session' => array(
		'cookie_lifetime' => 0,
		'gc_maxlifetime' => 3600,
		'use_cookies' => 1, // session的传递通过cookie实现
		'name' => 'framework',
		'use_trans_sid' => 0, // 禁止url中的session_id
		'use_only_cookies' => 1, // 只使用cookie中的session_id
		'cookie_httponly' => 'On', // 禁止js读取cookie
		                           // 'cookie_secure' => 'On',//https
		'hash_function' => 'sha256'
		
		// 只需要配置这2个东西就可以实现session存储在memcached中
		// 'save_handler' => 'memcached',
		// 'save_path' => 'localhost:11211',
		
	// 只需要配置这2个东西就可以实现session存储在redis中
		// session.save_handler => 'redis',
		// session.save_path => "tcp://host1:6379?weight=1, tcp://host2:6379?weight=2&timeout=2.5, tcp://host3:6379?weight=2&read_timeout=2.5"
	),
	//设置时区
	'date' => array(
		'timezone' => 'Asia/Shanghai'
	),
	//开启Gzip
	'zlib' => array(
		'output_compression' => 'On',
		'output_compression_level' => -1,
	),
);
