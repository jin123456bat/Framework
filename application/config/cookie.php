<?php
return array(
	/*
	 * 下标为__csrf的cookie配置为csrf存储用的cookie配置
	 */
	'__csrf' => array(
		'expire' => 0,
		'secure' => false,
		'httponly' => true,//禁止ajax通过cooke访问，假如是ajax请求，请将csrf存储在header中
		'domain' => $_SERVER['HTTP_HOST'],//假如不限制域名请使用空字符串，否则请使用域名（域名前最好有一个. 对旧版浏览器的支持）
		'path' => '/',//对整个服务器路径有效
	),
);