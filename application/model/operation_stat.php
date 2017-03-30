<?php
namespace application\model;

use \application\extend\model;

class operation_stat extends model
{

	function __config()
	{
		$db = $this->getConfig('db');
		return $db['cds_v2'];
	}
}
