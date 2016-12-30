<?php
namespace application\control;

use application\extend\BaseControl;
use framework\core\debugger;
class index extends BaseControl
{
	function index()
	{
		$this->model('combined_sn_data_container_86400')->truncate();
		$this->model('operation_stat_86400')->truncate();
		$this->model('traffic_stat_86400')->truncate();
		$this->model('traffic_stat_sn_86400')->truncate();
		$this->model('user_online_86400')->truncate();
		$this->model('user_online_sn_86400')->truncate();
		$this->model('operation_stat_class_category_86400')->truncate();
		$this->model('operation_stat_sn_86400')->truncate();
		$this->model('operation_stat_sn_class_category_86400')->truncate();
		
		$this->model('build_data_log')->where('duration=?',array(86400))->delete();
	}
}