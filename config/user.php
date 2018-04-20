<?php
return array(
	'model' => 'admin',
	'entity' => 'admin',
	
	//用户名的字段
	'verify_key' => array(
		'username',
		'email',
		'telephone',
	),
	
	//用户信息通过cookie保存
	'use_cookie' => false,
	//用户信息通过session保存
	'use_session' => true,
	
	
	//密码的字段
	'password_key' => 'password',
	
	//主键字段
	'primary_key' => 'id',
);