<?php
return array(
	//这里可以配置全局的js和css 通过
	'global' => array(
		'css' => array(
			array('head',''),//可以通过head或者end来声明位置
			array('end',''),
			'',//默认情况下css都是head
		),
		'js' => array(
			array('end',''),
			array('head',''),
			'',//默认情况下js都是end
		),
	),
	
	'host' => '',//这个可以配置资源的域名  为空的话生成的url是./XX/XXX/XX
	
	//下面的资源可以通过assets::css|js|image方法引用
	'css' => array(
		//css的路径 多个目录 逐个查找
		'path' => array(
			SYSTEM_ROOT.'/assets/css',
		),
		'host' => '',
	),
	'js' => array(
		//js文件的路径 多个目录 逐个查找
		'path' => array(
			SYSTEM_ROOT.'/assets/js',
		),
	),
	'image' => array(
		//图像的路径 多个目录 逐个查找
		'path' => array(
			SYSTEM_ROOT.'/assets/image',
		),
	)
);