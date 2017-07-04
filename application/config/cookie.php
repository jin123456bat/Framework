<?php
return array(
	
	//可以在程序运行过程中使用指定的配置来使用cookie
	'cookie_name1' => array(
		'default'=>true,//指定这个则为默认的配置，假如不指定则使用第一个配置组
		'expire' => 0,
		'secure' => false,
		'httponly' => true,//禁止ajax通过cooke访问，假如是ajax请求，请将csrf存储在header中
		'domain' => '',//$_SERVER['HTTP_HOST'],//假如不限制域名请使用空字符串，否则请使用域名（域名前最好有一个. 对旧版浏览器的支持）
		'path' => '/',//对整个服务器路径有效
	),
	
	/*
	 * 下标为__csrf的cookie配置为csrf存储用的cookie配置
	 */
	'__csrf' => array(
		'expire' => 0,
		'secure' => false,
		'httponly' => true,//禁止ajax通过cooke访问，假如是ajax请求，请将csrf存储在header中
		'domain' => '',//$_SERVER['HTTP_HOST'],//假如不限制域名请使用空字符串，否则请使用域名（域名前最好有一个. 对旧版浏览器的支持）
		'path' => '/',//对整个服务器路径有效
	),
);