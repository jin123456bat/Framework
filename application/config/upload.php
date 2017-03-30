<?php
return array(
	'size' => 1000000, // 文件大小限制 字节 不存在则不限制
	'ext' => array(
		'jpg',
		'text',
		'php'
	) // 文件后缀限制 不存在则不限制
,
	'path' => './application/upload'
) // 文件存储路径 假如不填写则不保存，函数直接返回tmp_name
                                  // 'default' => true,//是否是默认的上传配置
;
