<?php
namespace application\control;

use application\extend\BaseControl;
use framework\core\response\json;
use framework\core\request;
use framework\core\model;
use application\entity\user;
use application\algorithm\algorithm;
use application\extend\cache;
use application\extend\BaseComponent;

/**
 * 节点管理相关接口
 * @author fx
 *
 */
class node extends BaseControl
{
	/**
	 * 缓存从云平台来源的sn号
	 */
	function cacheSnList()
	{
		$sn = BaseComponent::getSnList();
		\application\extend\cache::set('cacheSnList', $sn);
		return new json(json::OK,NULL,$sn);
	}
	
	/**
	 * 允许绑定sn的设备
	 * @return \framework\core\response\json
	 */
	function avaliable_sn()
	{
		$sn = $this->combineSns();
		$snlist = $this->model('snlist')->where('value=?',array(1))->select('sn');
		foreach ($snlist as $s)
		{
			$sn[] = $s['sn'];
		}
		$diff = array();
		$snlist = $this->model('snlist')->where('value=?',array(0))->select('sn');
		foreach ($snlist as $s)
		{
			$diff[] = $s['sn'];
		}
		$sn = array_diff($sn, $diff);
		return new json(json::OK,NULL,$sn);
	}
	
	/**
	 * 创建CDS列表的缓存数据
	 */
	function cds_cache()
	{
		//根据sn过滤
		$sns = $this->combineSns();
		$feedbackModel = $this->model('feedback');
		$feedbackModel->in('user_info.sn',$sns);
		$result = $feedbackModel
		->Join('user_info','user_info.sn=feedback.sn')
		->select(array(
			'user_info.sn',//设备SN号
				
			'user_info.company',//CDS设备名称
			'if(UNIX_TIMESTAMP(NOW())-UNIX_TIMESTAMP(feedback.update_time)<60*30,"在线","离线") as status',//CDS在线状态
			'feedback.online',//活跃用户数
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
				
			'feedback.rhelp',//登陆信息
			'feedback.update_time',//更新时间
		));
		
		
		//查找24小时内的最大值
		$timestamp = (floor(time() / (5*60)) - 1) * 5*60;
		$start_time = date('Y-m-d H:i:s',strtotime('-24 hour',$timestamp));
		$end_time = date('Y-m-d H:i:s',$timestamp);
		$duration = 5*60;
		foreach ($result as &$r)
		{
			$r['disk_detail'] = json_decode($r['disk_detail'],true);
			if (is_array($r['disk_detail']))
			{
				usort($r['disk_detail'], function($a,$b){
					return (intval($a['name']) < intval($b['name']))?-1:1;
				});
			}
				
			$r['network_detail'] = json_decode($r['network_detail'],true);
			if (is_array($r['network_detail']) && !empty($r['network_detail']))
			{
				foreach ($r['network_detail'] as $network)
				{
					if (!empty($network['bond_name']))
					{
						if ($network['link_status'] == 'no')
						{
							$r['network'] = '1';
						}
					}
				}
			}
			if (!empty($r['rhelp']))
			{
				$r['rhelp'] = explode(':', $r['rhelp']);
			}
			
			$algorithm = new algorithm($start_time,$end_time,$duration);
			$traffic_stat = $algorithm->traffic_stat_alone($r['sn']);
			
			$r['max_service'] = empty($traffic_stat['service'])?0:round(max($traffic_stat['service'])/1024);
			$r['max_cache'] = empty($traffic_stat['cache'])?0:round(max($traffic_stat['cache'])/1024);
				
			$r['service'] = round(array_pop($traffic_stat['service'])/1024);
			$r['cache'] = round(array_pop($traffic_stat['cache'])/1024);
				
			//最大值
			$max = $this->model('feedbackHistory')
			->where('sn=?',array($r['sn']))
			->where('update_time>=? and update_time<?',array($start_time,$end_time))
			->find(array(
				'max(online) as max_online',//最大活跃人数
				'max(monitor) as max_monitor',//最大镜像流速
				'max(cpu_used) as max_cpu_used',//最大cpu使用率
				'max(mem_used) as max_mem_used',//最大内存使用率
				'max(sys_disk_used) as max_sys_disk_used',//最大系统盘使用率
				'max(data_disk_used) as max_data_disk_used',//最大数据盘使用率
			));
			if (!empty($max))
			{
				$r['max_online'] = $max['max_online']<$r['online']?$r['online']:$max['max_online'];
				$r['max_monitor'] = $max['max_monitor']<$r['monitor']?$r['monitor']:$max['max_monitor'];
				$r['max_cpu_used'] = $max['max_cpu_used']<$r['cpu_used']?$r['cpu_used']:$max['max_cpu_used'];
				$r['max_mem_used'] = $max['max_mem_used']<$r['mem_used']?$r['mem_used']:$max['max_mem_used'];
				$r['max_sys_disk_used'] = $max['max_sys_disk_used']<$r['sys_disk_used']?$r['sys_disk_used']:$max['max_sys_disk_used'];
				$r['max_data_disk_used'] = $max['max_data_disk_used']<$r['data_disk_used']?$r['data_disk_used']:$max['max_data_disk_used'];
			}
				
			//子节点信息
			$sub_vpe = $this->model('cdn_node_stat')
			->where('sn like ?',array('%'.substr($r['sn'], 3)))
			->group('sn')
			->select('sn,name');
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
		
		$cache_key = 'node_cds';
		cache::set($cache_key, $result);
		return $result;
	}
	
	/**
	 * CDS列表
	 */
	function cds()
	{
		//获取全部数据
		$cache_key = 'node_cds';
		$cds_list = cache::get($cache_key);
		if (empty($cds_list))
		{
			$cds_list = $this->cds_cache();
		}
		
		//根据group过滤
		$group = request::param('group',NULL,'int','i');
		$group_sn = array();
		if (!empty($group))
		{
			$sns = $this->model('cds_group_sn')->where('cds_group_id=?',array($group))->select('sn');
			foreach ($sns as $sn)
			{
				$group_sn[] = $sn['sn'];
			}
			
			$cds_temp = array();
			foreach ($cds_list as $cds)
			{
				if (in_array($cds['sn'], $group_sn,true))
				{
					$cds_temp[] = $cds;
				}
			}
			$cds_list = $cds_temp;
		}
		
		//搜索
		$search = request::param('search','','trim');
		$cds_temp = array();
		if (!empty($search))
		{
			foreach ($cds_list as $cds)
			{
				if (strpos($cds['sn'], $search) !== false)
				{
					$cds_temp[] = $cds;
				}
				else if (strpos($cds['company'], $search) !== false)
				{
					$cds_temp[] = $cds;
				}
			}
			$cds_list = $cds_temp;
		}
		
		//排序
		$order = request::param('order','online','strtolower|trim');
		$by = request::param('by','desc','strtolower|trim');
		if (!in_array($by, array('asc','desc')))
		{
			return new json(json::FAILED,'by参数只允许asc或desc,默认为asc');
		}
		usort($cds_list, function($a,$b) use($order,$by){
			if ($by == 'asc')
			{
				return ($a[$order] < $b[$order])?-1:1;
			}
			else
			{
				return ($a[$order] < $b[$order])?1:-1;
			}
		});
		
		//计算数据总数
		$total = count($cds_list);
		
		//截取排名
		$start = request::param('start',0,'intval');
		$length = request::param('length',10,'intval');
		$cds_list = array_slice($cds_list, $start,$length);
		
		//重新获取一次rhelp，防止缓存之后更细了rhelp
		foreach ($cds_list as &$cds)
		{
			$rhelp = $this->model('feedback')->where('sn=?',array($cds['sn']))->scalar('rhelp');
			if (!empty($rhelp))
			{
				$cds['rhelp'] = explode(':', $rhelp);
			}
		}
		
		$data = array(
			'total' => $total,
			'data' => $cds_list,
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
		$sn = request::param('sn',NULL,'trim','s');
		 $cache_key = 'node_detail_'.$sn;
		/*
		if (request::php_sapi_name()=='web')
		{
			$cache = \application\extend\cache::get($cache_key);
			if (!empty($cache))
			{
				return new json(json::OK,NULL,$cache);
			}
		} */
		
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
		$m = date('i');
		$m = floor($m/30) * 30;
		$end_time = date('Y-m-d H:'.$m.':00');
		$start_time = date('Y-m-d H:i:s',strtotime($end_time) - 24*3600);
		$duration = 30*60;//半个小时
		$algorithm = new algorithm($start_time,$end_time,$duration);
		$online_user = $algorithm->USEROnlineNum($sn);
		$online_user = $online_user['detail'];
		
		
		//最近24小时的服务，回源，镜像速率
		$timestamp = (floor(time() / (5*60)) - 1) * 5*60;
		$end_time = date('Y-m-d H:i:s',$timestamp);
		$start_time = date('Y-m-d H:i:s',strtotime('-24 hour',strtotime($end_time)));
		$duration = 5*60;//5分钟
		$algorithm = new algorithm($start_time,$end_time,$duration);
		$speed = $algorithm->traffic_stat($sn);
		unset($speed['icache_cache']);
		unset($speed['vpe_cache']);
		unset($speed['max_cache']);
		$speed = array_merge($speed,array(
			'sys_disk_used' => array(),
			'data_disk_used' => array(),
		));
		
		
		
		$m = date('i');
		$m = floor($m/30) * 30;
		$end_time = date('Y-m-d H:'.$m.':00');
		$start_time = date('Y-m-d H:i:s',strtotime($end_time) - 24*3600);
		$duration = 30*60;//30分钟
		for($t_time = $start_time;strtotime($t_time) < strtotime($end_time);$t_time = date('Y-m-d H:i:s',strtotime($t_time)+$duration))
		{
			$result = $this->model('feedbackHistory')
			->where('sn=?',array($sn))
			->where('ctime>=? and ctime<?',array(
				$t_time,
				date('Y-m-d H:i:s',strtotime($t_time) + $duration)
			))
			->find(array(
				'sys_disk_used'=>'max(sys_disk_used)',
				'data_disk_used' => 'max(data_disk_used)',
			));
			
			if (!empty($result))
			{
				$speed['sys_disk_used'][$t_time] = $result['sys_disk_used']*1;
				$speed['data_disk_used'][$t_time] = $result['data_disk_used']*1;
			}
			else
			{
				$speed['sys_disk_used'][$t_time] = 0;
				$speed['data_disk_used'][$t_time] = 0;
			}
			
			$result = $this->model('traffic_stat')
			->where('sn=?',array($sn))
			->where('create_time >=? and create_time <?',array(
				$t_time,
				date('Y-m-d H:i:s',strtotime($t_time) + $duration),
			))
			->find(array(
				'cpu_used' => 'max(cpu)',
				'mem_used' => 'max(mem)',
			));
			if (!empty($result))
			{
				$speed['mem_used'][$t_time] = $result['mem_used']*1;
				$speed['cpu_used'][$t_time] = $result['cpu_used']*1;
			}
			else
			{
				$speed['mem_used'][$t_time] = 0;
				$speed['cpu_used'][$t_time] = 0;
			}
			
			$result = $this->model('cdn_traffic_stat')
			->where('sn like ?',array('%'.substr($sn, 3)))
			->where('make_time >=? and make_time<?',array(
				$t_time,
				date('Y-m-d H:i:s',strtotime($t_time) + $duration),
			))
			->find(array(
				'cpu_used' => 'max(cpu)',
				'mem_used' => 'max(mem)',
			));
			if (!empty($result))
			{
				$speed['mem_used'][$t_time] = $speed['mem_used'][$t_time]>$result['mem_used']?$speed['mem_used'][$t_time]:$result['mem_used']*1;
				$speed['cpu_used'][$t_time] = $speed['cpu_used'][$t_time]>$result['cpu_used']?$speed['cpu_used'][$t_time]:$result['cpu_used']*1;
			}
		}
		
		
		
		//运行报表  最近30天的
		$start_time = date('Y-m-d 00:00:00',strtotime('-30 day'));
		$end_time = date('Y-m-d 00:00:00');
		$duration = 3600*24;//一天为单位
		$run_report = array();
		$result = array();
		$algorithm = new algorithm($start_time,$end_time,$duration);
		$operation_stat = $algorithm->operation_stat($sn);
		
		$traffic_stat = $algorithm->traffic_stat($sn);
		$user = $algorithm->user($sn);
		for($t_time = $start_time;strtotime($t_time) < strtotime($end_time);$t_time = date('Y-m-d H:i:s',strtotime($t_time)+$duration))
		{
			$result['max_service'] = isset($traffic_stat['service'][$t_time])?$traffic_stat['service'][$t_time]:0;
			$result['max_cache'] = isset($traffic_stat['cache'][$t_time])?$traffic_stat['cache'][$t_time]:0;
			
			$result['sum_service'] = isset($operation_stat['service'][$t_time])?$operation_stat['service'][$t_time]:0;
			$result['sum_cache'] = isset($operation_stat['cache'][$t_time])?$operation_stat['cache'][$t_time]:0;
			
			$result['hit_user'] = isset($user[$t_time]['hit'])?$user[$t_time]['hit']:0;
			$result['online_user'] = isset($user[$t_time]['online'])?$user[$t_time]['online']:0;
			
			
			$result['user_percent'] = 100*number_format(division($result['hit_user'],$result['online_user']),4,'.','');
			$result['max_service_user'] = 100*number_format(division($result['max_service'],$result['hit_user']),4,'.','');
			$result['service_cache'] = 100*number_format(division($result['sum_service'],$result['sum_cache']),4,'.','');
			$result['service_power'] = number_format(division($result['max_service'], $result['online_user']),2,'.','');
			
			$avg = $this->model('report_jxreport')->where('day=? and sn=?',array($t_time,$sn))->find(array(
				'capture_avg',
				'redirect_avg',
			));
			$result['capture_avg'] = $avg['capture_avg'] * 1;
			$result['redirect_avg'] = $avg['redirect_avg'] * 1;
			
			$result['hit_user'] *= 1;
			$result['online_user'] *= 1;
			
			$run_report[$t_time] = $result;
		}
		
		$data = array(
			'info' => $cds,
			'online_user' => $online_user,
			'speed' => $speed,
			'run_report' => $run_report,
		);
		
		\application\extend\cache::set($cache_key, $data);
		
		return new json(json::OK,'ok',$data);
	}
	
	/**
	 * CDS报表
	 */
	function cds_report()
	{
		$starttime = request::param('starttime');
		$endtime = request::param('endtime');
		
		if (empty($starttime))
		{
			return new json(json::FAILED,'开始时间不能为空');
		}
		if (empty($endtime))
		{
			return new json(json::FAILED,'结束时间不能为空');
		}
		if (strtotime($endtime) - strtotime($starttime) != 24*3600)
		{
			return new json(json::FAILED,'时间间隔必须为一天');
		}
		$starttime = date('Y-m-d',strtotime($starttime));
		$endtime = date('Y-m-d',strtotime($endtime));
		if (strtotime($endtime) > strtotime(date('Y-m-d')))
		{
			return new json(json::FAILED,'结束时间不能超过今天');
		}
		
		$start = request::param('start',0,'intval');
		$length = request::param('length',10,'intval');
		
		$order = request::param('order','status');
		$by = request::param('by','asc');
		
		if (!in_array($by, array('asc','desc')))
		{
			return new json(json::FAILED,'by参数只允许asc或desc,默认为asc');
		}
		
		$nodes = $this->model('feedback')
		->Join('user_info','user_info.sn=feedback.sn')
		->select(array(
			'user_info.sn',//设备SN号
			'user_info.company',//CDS设备名称
		));
		
		$algorithm = new algorithm($starttime,$endtime,24*3600);
		foreach ($nodes as &$node)
		{
			$result = $this->model('feedbackHistory')->where('ctime>=? and ctime<? and sn=?',array(
				$starttime,$endtime,$node['sn']
			))
			->find(array(
				'max_online'=>'max(online)',//活跃用户
				'max_hit' => 'max(hit)',//服务用户
				'max_hit_online' => 'FORMAT(max(hit/online)*100,1)',//服务用户/活跃用户峰值
			));
			
			$max_service = 0;
			$max_service_time = '';
			$traffic_stat = $algorithm->traffic_stat($node['sn']);
			foreach ($traffic_stat['service'] as $time=>$service)
			{
				if ($service>=$max_service)
				{
					$max_service = $service;
					$max_service_time = $time;
				}
			}
			$node['max_service'] = $max_service;
			$node['max_service_time'] = $max_service_time;
			
			
			$node = array_merge($node,$result);
		}
		
		var_dump($nodes);
	}
	
	function __access()
	{
		return array(
			array(
				'allow',
				'actions' => array('avaliable_sn','detail'),
			),
			array(
				'deny',
				'express' => request::php_sapi_name()=='web'?\application\entity\user::getLoginUserId()===NULL:false,
				'actions' => '*',
				'message' => new json(array('code'=>2,'result'=>'尚未登陆'))
			)
		);
	}
}