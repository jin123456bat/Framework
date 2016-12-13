<?php
namespace application\model;
use application\extend\model;

class user_info extends model
{
	function __config()
	{
		$db = $this->getConfig('db');
		return $db['ordoac'];
	}
}