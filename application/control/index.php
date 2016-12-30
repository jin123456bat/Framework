<?php
namespace application\control;

use application\extend\BaseControl;
class index extends BaseControl
{
	function index()
	{
		$cache = new \application\algorithm\cache();
		$cache->api_cds_online(86400,'2016-11-01 00:00:00','2016-12-30 00:00:00');
		$cache->api_user_online_traffic_stat(86400,'2016-11-01 00:00:00','2016-12-30 00:00:00');
	}
}