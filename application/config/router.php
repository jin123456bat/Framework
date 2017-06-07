<?php
return array(
	'default' => array(
		'control' => 'index',
		'action' => 'index',
	),
	
	//路由绑定,key中允许正则表达式，假如有多个正则表达式匹配，第一个优先
	//值必须是数组，array('control','action')或者array('c'=>'control','a'=>'action')的形式
	'bind' => array(
		//固定式匹配,query_string必须和/about一摸一样才可以  优先级最高
		'/about' => array(
			'c'=>'index','a'=>'page',
		),
		
		//匹配式绑定  id第一个不能数字开头，可以下划线开头或字母   
		//对应的参数放在get中  可以有多个，
		//目前测试pathinfo形式的url是可以的，其他形式的url不能确定
		//假如id不存在也无法正常匹配
		//优先级其次
		'/about/{id}' => array(
			'index','page',
		),
	),
);