<?php
return array(
	'sphinxapi' => '',//sphinxapi文件的路径，SphinxClient类有2种，一种是通过扩展安装的，一种是官方sphinx文件内的sphinxapi.php内的，假如没有安装扩展，则必须生命sphinxapi.php文件的完整路径
	
	'host' => 'localhost',
	'port' => 9312,
	'indexes' => array(
		'index_name' => array('table'=>'mysql_table','column' => 'id'),
	)
	
);