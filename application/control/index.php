<?php
namespace application\control;

use application\extend\BaseControl;
class index extends BaseControl
{
	function index()
	{
		/* $this->model('combined_sn_data_container_86400')->truncate();
		$this->model('operation_stat_86400')->truncate();
		$this->model('traffic_stat_86400')->truncate();
		$this->model('traffic_stat_sn_86400')->truncate();
		$this->model('user_online_86400')->truncate();
		$this->model('user_online_sn_86400')->truncate();
		$this->model('operation_stat_class_category_86400')->truncate();
		$this->model('operation_stat_sn_86400')->truncate();
		$this->model('operation_stat_sn_class_category_86400')->truncate();
		
		$this->model('build_data_log')->where('duration=?',array(86400))->delete(); */
		$cache = new \application\algorithm\cache();
		$cache->api_cds_online(3600,date('Y-m-d H:i:s',strtotime('-14 day')),date('Y-m-d H:i:s'),'CAS0530000152,CAS0530000157');
		$cache->api_user_online_traffic_stat(3600,date('Y-m-d H:i:s',strtotime('-14 day')),date('Y-m-d H:i:s'),'CAS0530000152,CAS0530000157');
		//$cache->api_cds_online(86400,date('Y-m-d H:i:s',strtotime('-2 month')),date('Y-m-d H:i:s'),'CAS0530000152,CAS0530000157');
		//$cache->api_user_online_traffic_stat(86400,date('Y-m-d H:i:s',strtotime('-2 month')),date('Y-m-d H:i:s'),'CAS0530000152,CAS0530000157');
	}
}