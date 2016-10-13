<?php
namespace application\model;

class cdn_traffic_stat extends \application\extend\model
{
	function __config()
	{
		$db = $this->getConfig('db');
		return $db['cds_v2'];
	}
}