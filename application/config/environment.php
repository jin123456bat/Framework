<?php
return array(
	'display_errors' => 'On',
	'error_reporting' => E_ALL,
	
	'max_execution_time' => 0,
	'session' => array(
		'cookie_lifetime' => 0,
		'gc_maxlifetime' => 3600,
		'use_cookies' => 1,
		'name' => 'FXDATA',
		'use_trans_sid' => 0,//禁止url中的session_id
		'use_only_cookies' => 1,//只使用cookie中的session_id
		'cookie_httponly' => 'On',//禁止js读取cookie
		//'cookie_secure' => 'On',//https
		'hash_function'=> 'sha256'
	)
);