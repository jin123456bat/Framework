<?php
/*
 * 数据库配置文件
 * model 定义了哪些model使用这个配置 假如一个model在多个配置中同时存在，第一个生效
 * default 定了一个默认的配置，在没有声明model使用的配置的时候使用default配置
 * 假如上面都没有 使用第一个配置
 */
return array(
	'test' => array(
		'default' => true,
		'type' => 'mysql',
		'server' => 'localhost',
		'dbname' => 'test',
		'user' => 'root',
		'password' => '',
		'charset' => 'utf8',
		'init_command' => '',
		'model' => array(
			'',
		)
	)
);
