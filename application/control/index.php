<?php
namespace application\control;

use application\extend\BaseControl;
use framework\core\database\driver\mysql;
use framework\core\model;
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
		
		$tables = $this->model('accounts')->query('show tables');
		foreach ($tables as $table)
		{
			var_dump($table['Tables_in_cloud_web_v2']);
		}
	}
}