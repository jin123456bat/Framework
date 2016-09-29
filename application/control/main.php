<?php
namespace application\control;
use framework\core\response\json;
use application\extend\BaseControl;
use framework\core\model;
use application\entity\user;

/**
 * 首页相关接口
 * @author fx
 *
 */
class main extends BaseControl
{
	function initlize()
	{
		parent::initlize();
		return $this->setTime();
	}
	
	function overview()
	{
		$start_time = date('Y-m-d H:00:00',strtotime($this->_startTime));
		$end_time = date('Y-m-d H:00:00',strtotime($this->_endTime));
		
		//CDS
		$cds_max = 0;
		$cds_detail = array();
		for($t_time = $start_time;strtotime($t_time)<strtotime($end_time);$t_time = date('Y-m-d H:i:s',strtotime($t_time)+$this->_duration_second))
		{
			//30分钟内
			$cds_detail[$t_time] = 1 * $this->model('feedbackHistory')->where('update_time >= ? and update_time < ?',array(date('Y-m-d H:i:s',strtotime($t_time)-30*60),$t_time))->scalar('count(distinct(sn))');
			if ($cds_detail[$t_time] > $cds_max)
			{
				$cds_max = $cds_detail[$t_time];
			}
		}
		
		//user
		//所有节点的在线人数累加
		$user_max = 0;
		$user_detail = array();
		for($t_time = $start_time;strtotime($t_time)<strtotime($end_time);$t_time = date('Y-m-d H:i:s',strtotime($t_time)+$this->_duration_second))
		{
			$max_online_gourp_sn = $this->model('feedbackHistory')
			->group('sn')
			->where('update_time >= ? and update_time<?',array(
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
		
		//operation_stat  每小时的颗粒度
		//service_max
		//服务流速 同一个时间点的operation中service_size的最大值
		$service_max_max = 0;
		$service_max_detail = array();
		for($t_time = $start_time;strtotime($t_time)<strtotime($end_time);$t_time = date('Y-m-d H:i:s',strtotime($t_time)+$this->_duration_second))
		{
			$result = 1*$this->model('operation_stat')
			->where('make_time >= ? and make_time <?',array(
				$t_time,
				date('Y-m-d H:i:s',strtotime($t_time)+$this->_duration_second)
			))
			->scalar(array(
				'service_sum'=>'sum(service_size)'
			));
			
			$service_max_detail[$t_time] = $result;
			
			if ($service_max_detail[$t_time] > $service_max_max)
			{
				$service_max_max = $result;
			}
		}
		
		//service_sum
		//服务流量 时间段内所有service_size的总和
		$service_sum_sum = 1*$this->model('operation_stat')->where('make_time >= ? and make_time < ?',array($this->_startTime,$this->_endTime))->sum('service_size');
		
		
		//cp_service
		$cp_service = array();
		$category = $this->getConfig('category');
		for($t_time = $start_time;strtotime($t_time)<strtotime($end_time);$t_time = date('Y-m-d H:i:s',strtotime($t_time)+$this->_duration_second))
		{
			$result = $this->model('operation_stat')
			->where('make_time >= ? and make_time <?',array(
				$t_time,
				date('Y-m-d H:i:s',strtotime($t_time)+$this->_duration_second)
			))
			->select(array(
				'make_time',
				'service_size',
				'class',
				'category'
			));
			
			foreach ($result as $r)
			{
				switch($r['class'])
				{
					case '0':$classname = 'http';break;
					case '1':$classname = 'mobile';break;
					case '2':$classname = 'video';break;
				}
				
				if ($r['class']==2 && $r['category']>=128)
				{
					$r['category'] -= 128;
					$classname = 'videoLive';
				}
				else
				{
					$classname = 'videoDemand';
					$videoLive = false;
				}
				
				$categoryname = $category[$classname][$r['category']];
				
				if (isset($cp_service[$classname][$categoryname][$t_time]))
				{
					$cp_service[$classname][$categoryname][$t_time] += $r['service_size'];
				}
				else
				{
					$cp_service[$classname][$categoryname][$t_time] = 0;
				}
			}
		}
		
		//accessContent
		//授权内容交付
		$accessContent_max = 0;//授权内容交付峰值
		$accessContent_detail = array();
		$accessContent_sum = 0;//授权内容交付流量
		
		//合作方
		$cooperation = array();
		
		$data = array(
			'cds' => array(
				'max' => array(
					'current' => $cds_max,
					'linkRatio' => 0.2,//环比
					'sameRatio' => 0.2//同比
				),//CDS在线节点数
				'detail' => $cds_detail,
			),
			'user' => array(
				'max' => array(
					'current' => $user_max,
					'linkRatio' => 0.2,//环比
					'sameRatio' => 0.2//同比
				),//用户数
				'detail' => $user_detail,
			),
			'service_max' => array(
				'max' => array(
					'current' => $service_max_max,
					'linkRatio' => 0.2,//环比
					'sameRatio' => 0.2//同比
				),//服务流速峰值（时间点内的最大值）
				'detail' => $service_max_detail,//服务流速按照时间节点的详情
				'linkRatio' => '环比',
				'sameRatio' => '同比'
			),
			'cp_service' => array(
				'detail' => $cp_service,//分CP流量堆叠
			),
			'service_sum' => array(
				'max' => array(
					'current' => $service_sum_sum,
					'linkRatio' => 0.2,//环比
					'sameRatio' => 0.2//同比
				),//服务流量(时间点内的累加值)
				//'detail' => $service_sum_detail,//服务流量
			),
			'accessContent' => array(
				'max' => array(
					'current' => $accessContent_max,//授权内容交付峰值
					'linkRatio' => 0.2,//环比
					'sameRatio' => 0.2//同比
				),//峰值
				'detail' => $accessContent_detail,//授权内容交付流速
				'sum' => array(
					'current' => $accessContent_sum,//授权内容交付流量
					'linkRatio' => 0.2,//环比
					'sameRatio' => 0.2//同比
				),
			),
			//分合作方流速堆叠
			'cooperation' => $cooperation,
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
				'deny',
				'actions' => '*',
				'express' => user::getLoginUserId()===NULL,
				'message' => new json(array('code'=>2,'result'=>'尚未登陆')),
			)
		);
	}
}