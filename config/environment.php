<?php
use framework\core\request;

return array(
	'display_errors' => 'On',
	'error_reporting' => E_ALL,
	'memory_limit' => '128M',
	
	'max_execution_time' => 60,
	'session' => array(
		'use_cookies' => 1, // session的传递通过cookie实现
		'use_only_cookies' => 1, // 只使用cookie中的session_id
		
		//下面的参数只有当use_cookies为1的时候有效
		'cookie_lifetime' => 0,//当sessionid通过cookie来传递的话，这个参数代表cookie的有效期，0代表一直到浏览器关闭
		'cookie_domain' => '',//当sessionid通过cookie来传递的话
		'cookie_httponly' => 'On', // 禁止js读取cookie
		'cookie_secure' => request::isHttps()?'On':'Off',//https
		
		
		'use_trans_sid' => 0, // 禁止url中的session_id
		
		
		//下面的东西是全局的
		'gc_maxlifetime' => 3600,
		'name' => 'framework',//sessionid的名称
		'hash_function' => 'sha256',
		
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
