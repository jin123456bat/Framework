<?php
namespace application\control;

use application\extend\BaseControl;
use framework\core\database\sql;
class index extends BaseControl
{
	function index()
	{
		$starttime = '2016-11-21 00:00:00';
		$endtime = '2016-11-22 00:00:00';
		
		$sn = $this->combineSns();
		
		$traffic_stat = new sql();
		$cdn_traffic_stat = new sql();
		$xvirt_traffic_stat = new sql();
		
		$xvirt_traffic_stat->from('cds_v2.xvirt_traffic_stat')
		->in('sn',$sn)
		->where('make_time>=? and make_time<?',array(
			$starttime,$endtime
		))
		->select(array(
			'time' => 'date_format(make_time,"%Y-%m-%d %H:%i")',
			'service' => '-1*service',
			'cache' => 0,
			'monitor' => 0,
		));
		
		
		$traffic_stat->from('ordoac.traffic_stat')
		->in('sn',$sn)
		->where('create_time>=? and create_time<?',array(
			$starttime,
			$endtime
		))
		->select(array(
			'time'=>'date_format(create_time,"%Y-%m-%d %H:%i")',
			'service'=>'1024*service',
			'cache' => '1024*cache',
			'monitor' => '1024*monitor',
		));
		
		$sn = array_map(function($s){
			return '%'.substr($s, 3);
		}, $sn);
		$cdn_traffic_stat->from('cds_v2.cdn_traffic_stat')
		->likein('sn',$sn)
		->where('make_time>=? and make_time<?',array(
			$starttime,$endtime
		))
		->select(array(
			'time' => 'date_format(make_time,"%Y-%m-%d %H:%i")',
			'service',
			'cache',
			'monitor',
		));
		
		$xvirt_traffic_stat->union(true, $cdn_traffic_stat, $traffic_stat);
		
		$t = new sql();
		$t->setFrom($xvirt_traffic_stat,'t');
		$t->group('time');
		$t->select(array(
			'time',
			'service' => 'sum(service)',
			'cache' => 'sum(cache)',
			'monitor' => 'sum(monitor)'
		));
		
		
		$result = $this->model('traffic_stat')
		->setFrom($t,'a')
		->group('timenode')
		->select(array(
			'timenode'=>'concat(date_format(time,"%Y-%m-%d %H"),":",LPAD(floor(date_format(time,"%i")/5)*5,2,0))',
			'service'=>'max(service)',
			'cache'=>'max(cache)',
			'monitor'=>'max(monitor)'
		));
		
		var_dump($result);
	}
}