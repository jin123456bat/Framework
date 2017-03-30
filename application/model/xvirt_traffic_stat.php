<?php
namespace application\model;

use application\extend\model;

class xvirt_traffic_stat extends model
{

	function __config()
	{
		$db = $this->getConfig('db');
		return $db['cds_v2'];
	}

	function __tableName()
	{
		return 'xvirt_traffic_stat';
	}
}
