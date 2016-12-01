<?php
namespace application\control;

use application\extend\BaseControl;
use framework\core\database\sql;
class index extends BaseControl
{
	function index()
	{
		$sn = $this->combineSns();
		
		/* $sn = array_map(function($s){
			return '%'.substr($s, 3);
		},$sn); */
		
		$sql = new sql();
		$sql->from('_feedback_history');
		//$sql->likein('sn',$sn);
		$sql->in('sn',$sn);
		$sql = $sql->where('update_time >= ? and update_time < ?',array(
			'2016-11-29 18:00',
			'2016-11-30 18:00'
		))
		->group('time,sn')
		->select(array(
			'time' => 'date_format(update_time,"%Y-%m-%d 00:00:00")',
			'online'=>'max(online)'
		));
		
		$result = $this->model('feedbackHistory')->setFrom($sql,'a')->group('time')->select('time,sum(online) as online');
		var_dump($result);
	}
}