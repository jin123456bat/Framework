<?php
namespace application\control;

use application\extend\BaseControl;
use framework\core\database\driver\mysql;
use framework\core\model;
use application\algorithm\apiCache;
class index extends BaseControl
{
	function index()
	{
		/* $starttime = date('Y-m-d H:i:s');
		$datadebugger = new debugger();
		$cacheComponent = new \application\algorithm\cache();
		$time = $cacheComponent->operation_stat(3600);;
		$datadebugger->stop();
		$this->model('build_data_log')->insert(array(
			'name' => 'operation_stat',
			'duration'=>3600,
			'run_starttime' => $starttime,
			'run_endtime' => date('Y-m-d H:i:s'),
			'data_starttime' => $time['starttime'],
			'data_endtime' => $time['endtime'],
			'runtime' => $datadebugger->getTime(),
		)); */
		
		$cache = new \application\algorithm\cache();
		$cache->api_cds_online(3600,'2016-12-01 00:00:00','2016-12-29 00:00:00','CAS0530000232,CAS0530000244');
		$cache->api_user_online_traffic_stat(3600,'2016-12-01 00:00:00','2016-12-29 00:00:00','CAS0530000232,CAS0530000244');
		$cache->api_cds_online(86400,'2016-11-01 00:00:00','2016-12-29 00:00:00','CAS0530000232,CAS0530000244');
		$cache->api_user_online_traffic_stat(86400,'2016-11-01 00:00:00','2016-12-29 00:00:00','CAS0530000232,CAS0530000244');
	}
}