<?php
return array(
	'logger' => 'file',//记录日志的方式
	
	//当logger为file的时候有效
	'file' => array(
		'path' => APP_ROOT.'/log',//文件日志记录的未知
		'prefix' => '',//日志文件前缀
		'max_size' => 50*1024*1024
	),
	
	//当logger为db的时候有效
	'db' => array(
		'model' => 'log',//使用哪个model来记录日志
	)
);