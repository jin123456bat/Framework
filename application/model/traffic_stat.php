<?php
namespace application\model;
use application\extend\model;

class traffic_stat extends model
{
	function __config()
	{
		$db = $this->getConfig('db');
		return $db['ordoac'];
	}
}