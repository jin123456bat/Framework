<?php
namespace application\control;

use application\extend\BaseControl;
use application\algorithm\cacheAlgorithm;
class index extends BaseControl
{
	function index()
	{
		/* $starttime = time();
		$cacheAlgorithm = new cacheAlgorithm(300, '2016-12-23 12:00:00', '2016-12-23 12:30:00');
		$result = $cacheAlgorithm->operation_stat();
		$endtime = time();
		//var_dump($endtime - $starttime);
		echo json_encode($result); */
		
		$cache = new \application\algorithm\cache();
		$cache->traffic_stat(1800,'2016-12-26 15:00:00','2016-12-26 17:00:00');
	}
}