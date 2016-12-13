<?php
namespace application\control;

use application\extend\BaseControl;
class index extends BaseControl
{
	function index()
	{
		$this->model('traffic_stat_1800')->startCompress();
		$this->model('traffic_stat_1800')->insert(array(
			'time'=>'2016-12-01 16:00:00',
			'service' => 0,
			'cache' => 0,
			'monitor' => 0,
			'max_cache' => 0,
			'icache_cache' => 0,
			'vpe_cache' => 0,
		));
	}
}