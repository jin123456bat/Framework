<?php
namespace application\control;

use application\extend\BaseControl;
use application\algorithm\cacheAlgorithm;
class index extends BaseControl
{
	function index()
	{
		$cacheAlgorithm = new cacheAlgorithm(300, '2016-12-22 12:00:00', '2016-12-23 12:30:00');
		$result = $cacheAlgorithm->traffic_stat();
		var_dump($result);
	}
}