<?php
namespace application\model;
use application\extend\model;

class xvirt_traffic_stat extends model
{
	function __config()
	{
		$db = $this->getConfig('db');
		return $db['xvirt'];
	}
	
	function __tableName()
	{
		return 'traffic_stat';
	}
}