<?php
namespace application\model;

use framework\core\model;

class log extends model
{
	function __config()
	{
		$db = $this->getConfig('db');
		return $db['cloud_web_v2'];
	}
	
	function __tableName()
	{
		return 'admin_log';
	}
	
	function add($uid,$message)
	{
		return parent::insert(array(
			'uid' => $uid,
			'content' => $message,
			'ip' => $_SERVER['REMOTE_ADDR'],
		));
	}
}