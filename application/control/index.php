<?php
namespace application\control;

use application\extend\BaseControl;
class index extends BaseControl
{
	function index()
	{
		$this->model('traffic_stat_5_minute')
		->duplicate('service',3)
		->insert(array(
			'time' => '2016-1-1 00:00:00',
			'sn' => 1,
			'service' => 2,
		));
	}
}