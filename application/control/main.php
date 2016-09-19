<?php
namespace application\control;
use framework\core\response\json;
use application\extend\BaseControl;

/**
 * 首页相关接口
 * @author fx
 *
 */
class main extends BaseControl
{
	function overview()
	{
		$response = $this->setTime();
		if ($response)
		{
			return $response;
		}
		
		$start_time = date('Y-m-d H:00:00',strtotime($this->_startTime));
		$end_time = date('Y-m-d H:00:00',strtotime($this->_endTime));
		
		//CDS
		$cds_max = 0;
		$cds_detail = array();
		for($t_time = $start_time;strtotime($t_time)<=strtotime($end_time);$t_time = date('Y-m-d H:i:s',strtotime($t_time)+$this->_duration_second))
		{
			//30分钟内
			$cds_detail[$t_time] = 1 * $this->model('feedback')->where('update_time > ? and update_time < ?',array(date('Y-m-d H:i:s',strtotime($t_time)-30*60),$t_time))->scalar('count(distinct(sn))');
			if ($cds_detail[$t_time] > $cds_max)
			{
				$cds_max = $cds_detail[$t_time];
			}
		}
		
		//user
		//所有节点的在线人数累加
		$user_max = 0;
		$user_detail = array();
		for($t_time = $start_time;strtotime($t_time)<=strtotime($end_time);$t_time = date('Y-m-d H:i:s',strtotime($t_time)+$this->_duration_second))
		{
			$max_online_gourp_sn = $this->model('feedback')
			->group('sn')
			->where('update_time > ? and update_time<?',array(
				date('Y-m-d H:i:s',strtotime($t_time)-30*60),
				$t_time
			))
			->select('max(online) as online');
			$user_detail[$t_time] = 0;
			foreach ($max_online_gourp_sn as $online)
			{
				$user_detail[$t_time] += $online['online'];
			}
			if ($user_detail[$t_time] > $user_max)
			{
				$user_max = $user_detail[$t_time];
			}
		}
		
		//service
		//计算每一个节点在同一个时间段的最大值，然后累加
		$service_max = 0;
		$service_detail = array();
		for($t_time = $start_time;strtotime($t_time)<=strtotime($end_time);$t_time = date('Y-m-d H:i:s',strtotime($t_time)+$this->_duration_second))
		{
			//operation_stat中的service是按照class和category分开了，所以先把他们都加到一起
			$this->model('operation_stat')
			->where('create_time > ? and create_time <?',array(
				$t_time,
				date('Y-m-d H:i:s',strtotime($t_time)+$this->_duration_second)
			))
			->group(array('class','category'))
			->select('sum(service_size)');
		}
		
		
		$data = array(
			'cds' => array(
				'max' => $cds_max,
				'detail' => $cds_detail,
			),
			'user' => array(
				'max' => $user_max,
				'detail' => $user_detail,
			),
			'service' => array(
				'max' => $service_max,
				'detail' => $service_detail,
			)
		);
		
		return new json(json::OK,NULL,$data);
	}
	
	
	
	/**
	 * 配置访问权限
	 */
	function __access()
	{
		return array(
			array(
				'deny',//deny  允许访问
				'actions' => array('overview'),
				'express' => true,//改规则是否有效
				'message' => 'oh no',
			),
			array(
				'allow',
				'actions' => '*',
				'express' => true,
				'message' => new json(array('code'=>0,'result'=>'没有权限')),
			)
			
		);
	}
}