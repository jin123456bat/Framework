<?php
return array(
	//这里可以配置全局的js和css 通过
	'global' => array(
		//head默认在</head>标签前面
		'head' => array(
			'css' => array(
				//这里直接写名字就可以了，路径会自动从下面配置的路径中查找
			),
			'js' => array(
				
			),
		),
		//head默认在</head>标签前面
		'end' => array(
			'js' => array(
				
			),
		)
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