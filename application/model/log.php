<?php
namespace application\model;

use framework\core\model;
use framework\core\request;

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
			'ip' => request::php_sapi_name()=='web'?$_SERVER['REMOTE_ADDR']:'cli',
		));
	}
}