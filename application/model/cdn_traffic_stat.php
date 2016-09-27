<?php
namespace application\model;
use framework\core\model;

class cdn_traffic_stat extends model
{
	function __config()
	{
		$db = $this->getConfig('db');
		return $db['cds_v2'];
	}
}