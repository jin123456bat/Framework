<?php
return array(
	//配置默认的控制器名称和方法名称
	'default' => array(
		'control' => 'index',
		'action' => 'index'
	),
	
	//web访问模式的路由解析规则
	'web_parser' => array(
		'bind',
		'common',
		'pathinfo',
		'preg',
	),
	
	//cli访问模式的路由解析规则
	'cli_parser' => array(
		'cliParser',
	),
	
	//server访问模式的路由解析规则
	'server_parser' => array(
		'serverParser',
	),
	
	//静态绑定
	//值必须是数组，array('control','action')或者array('c'=>'control','a'=>'action')的形式
	'bind' => array(
		'/about' => array('index','index',),
	),
	
	//正则匹配
	//假如id不存在也无法正常匹配
	//id第一个不能数字开头，可以下划线开头或字母
	//这个是错误的 '/about/{4ab}'
	'preg' => array(
		'/page/{id}' => array(
			'index','index',
		)
	),
	
	//直接将某个类做为controller，需要遵循以下原则
	//1、类必须继承于/framework/core/response类
	//2、响应内容由类中的getBody方法定义
	//3、类的构造函数必须支持无参数类型，
	//4、调用方法是c=类名，类名为不带命名空间的类名 如下为index.php?c=captcha
	//pathinfo模式调用方法为index.php/captcha
	//因此，假如有多个相同的类名，以第一个为准，第二个不会调用
	//5、不允许声明同样的control，否则会被control覆盖
	//6、key是别名，假如被control覆盖，可以通过别名来调用
	//7、别名的优先级要高一些
	/*
	 * 2个都是alias，根据第4规则，应该调用第一个类，但是事实上应该调用第二个类
	 *  array(
		'/framework/vendor/alias',
		'alias' => '/framework/vendor/content',
	) */
	'class' => array(
		'alias'=>'\framework\vendor\captcha',
	),
);