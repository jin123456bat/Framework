<?php
return array(
	/*
	 * 当用户在浏览器中打开多个页面的时候，不同的页面生成的csrf_token不一样
	 * 假如只使用一个的话就会出现第二次打开的页面把第一次打开的页面生成的token覆盖掉
	 * 导致第一个页面上无法进行任何请求操作，
	 * 
	 * 因为csrf_token是存储在cookie中的 而cookie存在4KB的最大值限制  所以最大值也不应该设置太大，导致csrf_token丢失
	 * 同时也会导致其他的数据无法设置进入cookie
	 * 
	 * 想象一下 用户同时打开了100个标签页，cookie中存储了100个token，直接超过4KB，
	 * 新的页面中假如有任何设置cookie的操作，虽然返回值为true，但是实际上任何值都无法存入cookie
	 * 
	 * 虽然session可以解决最大值的问题，但是同样，过多的页面会导致服务器的可用空间下降
	 * 因此，开发者需要根据业务需求合理的设置token的备用验证数量
	 */
	'max_token_num' => 10,
	
	'storage' => 'session',//将csrf存储在cookie中，其他的配置项目还有cache|session
	
	//当storage = cookie的时候有效
	'cookie' => array(
		'expire' => 0,
		'secure' => false,
		'httponly' => true, // 禁止ajax通过cooke访问，假如是ajax请求，请将csrf存储在header中
		'domain' => '', // $_SERVER['HTTP_HOST'],//假如不限制域名请使用空字符串，否则请使用域名（域名前最好有一个. 对旧版浏览器的支持）
		'path' => '/' // 对整个服务器路径有效
	),
	
	//当storage = cache的时候有效
	'cache' => array(
		'expires' => 0,
		'store' => 'mysql',//使用哪个存储引擎  存储引擎的配置必须先在cache的config文件中配置好  这里不在重复配置
	),
);