<?php
return array(
	'logger' => 'file',//记录日志的方式
	
	//当logger为file的时候有效
	'file' => array(
		'path' => '',//文件日志记录的未知
	),
	
	//当logger为db的时候有效
	'db' => array(
		'model' => 'log',//使用哪个model来记录日志
	)
);