<?php
return array(
	'vio' => array(
		'size' => 1000000, // 文件大小限制 字节 不存在则不限制
		'ext' => array(
			'mp4'
		), // 文件后缀限制 不存在则不限制
		'path' => './application/upload'
	),
	'text' => array(
		// 'default' => true,
		'size' => 1000, // 文件大小限制 字节 不存在则不限制
		                // 文件后缀限制 不存在则不限制
		'ext' => array(
			'text'
		),
		// 文件存储路径 假如不填写则不保存，函数直接返回tmp_name
		'path' => './application/upload'
	)
);