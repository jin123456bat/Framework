<?php
namespace application\control;

use application\extend\BaseControl;
use framework\core\response\json;
use framework\core\request;
use framework\core\database\sql;
use framework\core\model;
use application\entity\user;

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
		$group = request::param('group',NULL);
		$group_sn = array();
		if (!empty($group))
		{
			$sns = $this->model('cds_group_sn')->where('cds_group_id=?',array($group))->select('sn');
			if (!empty($sns))
			{
				foreach ($sns as $sn)
				{
					$group_sn[] = $sn['sn'];
				}
			}
		}
		
		$start = request::param('start',0,'intval');
		$length = request::param('length',10,'intval');
		
		$order = request::param('order','status');
		$by = request::param('by','asc');
		
		if (!in_array($by, array('asc','desc')))
		{
			return new json(json::FAILED,'by参数只允许asc或desc,默认为asc');
		}
		
		$feedbackModel = $this->model('feedback');
		if (!empty($group_sn))
		{
			$feedbackModel->in('user_info.sn',$group_sn);
		}
		$result = $feedbackModel
		->Join('user_info','user_info.sn=feedback.sn')
		->limit($start,$length)
		->order($order,$by)
		->select(array(
			'user_info.sn',//设备SN号
			
			'user_info.company',//CDS设备名称
			'if(TIMESTAMPDIFF(MINUTE,now(),feedback.update_time)>30,"离线","在线") as status',//CDS在线状态
			
			'feedback.online',//活跃用户数
			'feedback.cache',//回源流速，kbp/s
			'feedback.service',//服务速率
			'feedback.monitor',//镜像速率
			'feedback.cpu_used',//CPU使用率
			'feedback.mem_used',//内存使用率
			'feedback.sys_disk_used',//系统盘使用率
			'feedback.data_disk_used',//数据盘使用率
			
			'feedback.data_disk_status',//数据盘状态
			'feedback.network',//网卡状态
			'feedback.version',//系统版本号
			'feedback.disk_detail',//硬盘详情
			'feedback.network_detail',//网卡详情
		));
		
		
		//查找24小时内的最大值
		$start_time = date('Y-m-d H:00:00',strtotime('-24 hour'));
		$end_time = date('Y-m-d H:00:00',strtotime('-24 hour'));
		foreach ($result as &$r)
		{
			$r['disk_detail'] = json_decode($r['disk_detail'],true);
			$r['network_detail'] = json_decode($r['network_detail'],true);
			
			//最大值
			$max = $this->model('feedbackHistory')
			->where('sn=?',array($r['sn']))
			->where('update_time>=? and update_time<?',array($start_time,$end_time))
			->find(array(
				'max(online) as max_online',//最大活跃人数
				'max(cache) as max_cache',//最大回源流速
				'max(service) as max_service',//最大服务流速,
				'max(monitor) as max_monitor',//最大镜像流速
				'max(cpu_used) as max_cpu_used',//最大cpu使用率
				'max(mem_used) as max_mem_used',//最大内存使用率
				'max(sys_disk_used) as max_sys_disk_used',//最大系统盘使用率
				'max(data_disk_used) as max_data_disk_used',//最大数据盘使用率
			));
			if (!empty($max))
			{
				$r['max_online'] = $max['max_online']<$r['online']?$r['online']:$max['max_online'];
				$r['max_cache'] = $max['max_cache']<$r['cache']?$r['cache']:$max['max_cache'];
				$r['max_service'] = $max['max_service']<$r['service']?$r['service']:$max['max_service'];
				$r['max_monitor'] = $max['max_monitor']<$r['monitor']?$r['monitor']:$max['max_monitor'];
				$r['max_cpu_used'] = $max['max_cpu_used']<$r['cpu_used']?$r['cpu_used']:$max['max_cpu_used'];
				$r['max_mem_used'] = $max['max_mem_used']<$r['mem_used']?$r['mem_used']:$max['max_mem_used'];
				$r['max_sys_disk_used'] = $max['max_sys_disk_used']<$r['sys_disk_used']?$r['sys_disk_used']:$max['max_sys_disk_used'];
				$r['max_data_disk_used'] = $max['max_data_disk_used']<$r['data_disk_used']?$r['data_disk_used']:$max['max_data_disk_used'];
			}
			
			//子节点信息
			$sub_vpe = $this->model('cdn_node_stat')->where('sn like ?',array('%'.substr($r['sn'], 3)))->select('sn,name');
			foreach ($sub_vpe as &$vpe)
			{
				$t = $this->model('cdn_traffic_stat')
				->where('sn=?',array($vpe['sn']))
				->order('make_time','desc')
				->find(array(
					'cache',//缓存 kbps
					'service',//服务 kbps
					'cpu',//cpu使用
					'mem',//mem使用
				));
				
				$vpe['cache'] = $t['cache'] * 1;
				$vpe['service'] = $t['service'] * 1;
				$vpe['cpu'] = $t['cpu'] * 1;
				$vpe['mem'] = $t['mem'] * 1;
			}
			$r['sub_vpe'] = $sub_vpe;
		}
		
		$total = $this->model('feedback')
		->Join('user_info','user_info.sn=feedback.sn')->count('*');
		
		$data = array(
			'total' => $total,
			'data' => $result,
			'start' => $start,
			'length' => $length,
		);
		
		return new json(json::OK,'ok',$data);
	}
	
	/**
	 * 子节点详情
	 */
	function detail()
	{
		$sn = request::param('sn');
		$info = $this->model('feedback')->where('sn=?',array($sn))->find(array(
			//CDS在线状态(在线，离线)
			'status'=>'if(UNIX_TIMESTAMP(NOW())-UNIX_TIMESTAMP(feedback.update_time)<60*30,"在线","离线")',
			'data_disk_status',//数据盘状态
			'network',//网卡状态
			'version',//系统版本号
			'cpu_type',//CPU规格，
			'concat(mem_size/1024,"GB") as mem_size',//内存大小
			'concat(convert(sys_disk_size/1024/1024/1024,decimal),"GB") as sys_disk_size',//系统盘大小
			'concat(convert(data_disk_size/1024/1024/1024/1024,decimal),"TB") as data_disk_size',//数据盘大小
		));
		if (empty($info))
		{
			return new json(json::FAILED,'CDS不存在');
		}
		$cds = $this->model('user_info')->where('sn=?',array($sn))->find(array(
			'sn',//CDS的sn号
			'company',//CDS的名称
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
			
			if (!empty($result))
			{
				list($service,$cache,$monitor) = explode('-', $result);
				
				$speed['service'][$t_time] = $service *1;
				$speed['cache'][$t_time] = $cache*1;
				$speed['monitor'][$t_time] = $monitor*1;
			}
			else
			{
				$speed['service'][$t_time] = 0;
				$speed['cache'][$t_time] = 0;
				$speed['monitor'][$t_time] = 0;
			}
			
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
			$result = $this->model('traffic_stat')
			->where('create_time>=? and create_time<?',array(
				$t_time,
				date('Y-m-d H:i:s',strtotime($t_time)+$duration)
			))
			->where('sn=?',array($sn))
			->find(array(
				'max_service'=>'max(service)',
				'max_cache'=>'max(cache)',
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
			
			$result['user_percent'] = 100*number_format(division($result['hit_user'],$result['online_user']),4,'.','');
			$result['max_service_user'] = 100*number_format(division($result['max_service'],$result['hit_user']),4,'.','');
			$result['service_cache'] = 100*number_format(division($result['sum_service'],$result['sum_cache']),4,'.','');
			
			
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
	
	function __access()
	{
		return array(
			array(
				'deny',
				'express' => user::getLoginUserId()===NULL,
				'actions' => '*',
				'message' => new json(array('code'=>2,'result'=>'尚未登陆'))
			)
		);
	}
}