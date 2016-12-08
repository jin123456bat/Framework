<?php
namespace application\model;
use application\extend\model;

class build_data_log extends model
{
	function __config()
	{
		$db = $this->getConfig('db');
		return $db['cloud_web_v2'];
	}
}