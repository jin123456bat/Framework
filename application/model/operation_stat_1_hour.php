<?php
namespace application\model;
use application\extend\model;

class operation_stat_1_hour extends model
{
	function __config()
	{
		$db = $this->getConfig('db');
		return $db['cloud_web_v2'];
	}
}