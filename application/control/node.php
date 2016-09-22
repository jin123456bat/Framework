<?php
namespace application\control;

use application\extend\BaseControl;
use framework\core\response\json;
use framework\core\request;
use framework\core\database\sql;
use framework\core\model;

/**
 * 节点管理相关接口
 * @author fx
 *
 */
class node extends BaseControl
{
	/**
	 * CDS列表
	 */
	function cds()
	{
		$start = request::get('start',0,'intval');
		$length = request::get('length',10,'intval');
		
		
		
		$data = array(
			'group'
		);
		
		return new json(json::OK,'ok',$data);
	}
	
	function icache()
	{
		
	}
	
	function vpe()
	{
		
	}
	
	/**
	 * 子节点详情
	 */
	function detail()
	{
		$sn = request::param('sn');
		$cds = $this->model('user_info')->where('sn=?',array($sn))->find(array(
			'sn',//CDS的sn号
			'company',//CDS的名称
		));
		if (empty($cds))
		{
			return new json(json::FAILED,'CDS不存在');
		}
		
		$info = $this->model('feedback')->where('sn=?',array($sn))->find(array(
			//CDS在线状态(在线，离线)
			'status'=>'if(UNIX_TIMESTAMP(NOW())-UNIX_TIMESTAMP(feedback.update_time)<60*30,"在线","离线")',
			'data_disk_status',//数据盘状态
			'network=1',//网卡状态
			'version',//系统版本号
			'cpu_type',//CPU规格，
			'concat(mem_size/1024,"GB") as mem_size',//内存大小
			'concat(convert(sys_disk_size/1024/1024/1024,decimal),"GB") as sys_disk_size',//系统盘大小
			'concat(convert(data_disk_size/1024/1024/1024/1024,decimal),"TB") as data_disk_size',//数据盘大小
		));
		$cds = array_merge($cds,$info);
		
		
		//最近24小时用户数量
		$start_time = date('Y-m-d H:00:00',strtotime('-1 day'));
		$end_time = date('Y-m-d H:00:00');
		$duration = 3600;//一个小时为单位
		$online_user = array();
		for($t_time = $start_time;strtotime($t_time) < strtotime($end_time);$t_time = date('Y-m-d H:i:s',strtotime($t_time)+$duration))
		{
			$online_user[$t_time] = $this->model('feedbackHistory')
			->where('update_time>? and update_time<?',array(
				date('Y-m-d H:i:s',strtotime($t_time) - 30*60),
				$t_time
			))
			->where('sn=?',array($sn))
			->max('online') * 1;
		}
		
		
		//最近24小时的服务，回源，镜像速率,分别取时间之内的最大值，
		$speed = array(
			'service' => array(),
			'cache' => array(),
			'monitor' => array(),
			'cpu' => array(),
			'mem' => array(),
			'sys_disk_used' => array(),
			'data_disk_used' => array(),
		);
		for($t_time = $start_time;strtotime($t_time) < strtotime($end_time);$t_time = date('Y-m-d H:i:s',strtotime($t_time)+$duration))
		{
			$result = $this->model('traffic_stat')
			->where('sn=?',array($sn))
			->where('create_time>=? and create_time<?',array(
				$t_time,
				date('Y-m-d H:i:s',strtotime($t_time)+$duration)
			))
			->scalar(array(
				'max(concat(lpad(service,20,0),"-",lpad(cache,20,0),"-",lpad(monitor,20,0)))',
			));
			
			list($service,$cache,$monitor) = explode('-', $result);
			
			$speed['service'][$t_time] = $service *1;
			$speed['cache'][$t_time] = $cache*1;
			$speed['monitor'][$t_time] = $monitor*1;
			
			
			$result = $this->model('feedbackHistory')
			->where('sn=?',array($sn))
			->where('update_time>? and update_time<?',array(
				date('Y-m-d H:i:s',strtotime($t_time) - 30*60),
				$t_time
			))
			->find(array(
				'sys_disk_used'=>'avg(sys_disk_used)',
				'data_disk_used' => 'avg(data_disk_used)',
				'mem_used' => 'avg(mem_used)',
				'cpu_used' => 'avg(cpu_used)',
			));
			
			if (!empty($result))
			{
				$speed['sys_disk_used'][$t_time] = $result['sys_disk_used']*1;
				$speed['data_disk_used'][$t_time] = $result['data_disk_used']*1;
				$speed['mem_used'][$t_time] = $result['mem_used']*1;
				$speed['cpu_used'][$t_time] = $result['cpu_used']*1;
			}
			else
			{
				$speed['sys_disk_used'][$t_time] = 0;
				$speed['data_disk_used'][$t_time] = 0;
				$speed['mem_used'][$t_time] = 0;
				$speed['cpu_used'][$t_time] = 0;
			}
		}
		
		
		//运行报表  最近30天的
		$start_time = date('Y-m-d 00:00:00',strtotime('-30 day'));
		$end_time = date('Y-m-d 00:00:00');
		$duration = 3600*24;//一天为单位
		$run_report = array();
		for($t_time = $start_time;strtotime($t_time) < strtotime($end_time);$t_time = date('Y-m-d H:i:s',strtotime($t_time)+$duration))
		{
			$result = $this->model('traffic_stat_hour')
			->where('create_time>=? and create_time<?',array(
				$t_time,
				date('Y-m-d H:i:s',strtotime($t_time)+$duration)
			))
			->where('sn=?',array($sn))
			->find(array(
				'max_service'=>'max(max_service)',
				'max_cache'=>'max(max_cache)',
				'sum_service'=>'sum(service)',
				'sum_cache'=>'sum(cache)',
				'hit_user'=>'max(hit_user)',//服务用户
				'online_user'=>'max(online_user)',//活跃用户
			));
			$result['max_service'] *= 1;
			$result['max_cache'] *= 1;
			$result['hit_user'] *= 1;
			$result['online_user'] *= 1;
			$result['sum_service'] *= 1;
			$result['sum_cache'] *= 1;
			
			$result['user_percent'] = 100*number_format($result['hit_user']/$result['online_user'],4,'.','');
			$result['max_service_user'] = 100*number_format($result['max_service']/$result['hit_user'],4,'.','');
			$result['service_cache'] = 100*number_format($result['sum_service']/$result['sum_cache'],4,'.','');
			
			
			$avg = $this->model('report_jxreport')->where('day=? and sn=?',array($t_time,$sn))->find(array(
				'capture_avg',
				'redirect_avg',
			));
			$result['capture_avg'] = $avg['capture_avg'] * 1;
			$result['redirect_avg'] = $avg['redirect_avg'] * 1;
			
			$run_report[$t_time] = $result;
		}
		
		
		$data = array(
			'info' => $cds,
			'online_user' => $online_user,
			'speed' => $speed,
			'run_report' => $run_report,
		);
		
		return new json(json::OK,'ok',$data);
	}
}