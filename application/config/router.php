<?php
return array(
	'default' => array(
		'control' => 'index',
		'action' => 'index'
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
	'class' => array(
		'alias'=>'/framework/vendor/captcha',
	),
	
	// 路由绑定,key中允许正则表达式，假如有多个正则表达式匹配，第一个优先
	// 值必须是数组，array('control','action')或者array('c'=>'control','a'=>'action')的形式
	'bind' => array(
		// 固定式匹配,query_string必须和/about一摸一样才可以 优先级最高
		'/about' => array(
			'c' => 'index',
			'a' => 'page'
		),
		
		// 匹配式绑定 id第一个不能数字开头，可以下划线开头或字母
		// 对应的参数放在get中 可以有多个，
		// 目前测试pathinfo形式的url是可以的，其他形式的url不能确定
		// 假如id不存在也无法正常匹配
		// 优先级其次
		'/about/{id}' => array(
			'index',
			'page'
		)
	),
);