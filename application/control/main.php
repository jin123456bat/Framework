<?php
namespace application\control;
use framework\core\response\json;
use application\extend\BaseControl;
use application\entity\user;
use application\algorithm\ratio;
use application\algorithm\algorithm;
use framework\core\model;
use framework\core\request;

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
		var_dump(request::php_sapi_name());
		exit();
		
		$start_time = date('Y-m-d H:00:00',strtotime($this->_startTime));
		$end_time = date('Y-m-d H:00:00',strtotime($this->_endTime));
		
		$algorithm = new algorithm($start_time, $end_time, $this->_duration_second);
		
		switch ($this->_duration)
		{
			case 'minutely':$algorithm->setDuration(30*60);break;
			case 'hourly':$algorithm->setDuration(2*60*60);break;
			case 'daily':$algorithm->setDuration(24*60*60);break;
		}
		
		$cds = $algorithm->CDSOnlineNum();
		
		//user
		//所有节点的在线人数累加
		$user = $algorithm->USEROnlineNum();
		
		//operation_stat  每小时的颗粒度
		//service_max
		//服务流速 同一个时间点的operation中service_size的最大值
		switch ($this->_duration)
		{
			case 'minutely':$algorithm->setDuration(5*60);break;
			case 'hourly':$algorithm->setDuration(2*60*60);break;
			case 'daily':$algorithm->setDuration(24*60*60);break;
		}
		$service_max = $algorithm->ServiceMax();
		
		//service_sum
		//服务流量 时间段内所有service_size的总和
		$service_sum = $algorithm->ServiceSum();
		
		//cp_service
		switch ($this->_duration)
		{
			case 'minutely':$algorithm->setDuration(30*60);break;
			case 'hourly':$algorithm->setDuration(2*60*60);break;
			case 'daily':$algorithm->setDuration(24*60*60);break;
		}
		$cp_service = $algorithm->CPService();
		
		$ratio = new ratio($this->_timemode);
		
		switch ($this->_duration)
		{
			case 'minutely':$ratio->setDuration(5*60);break;
			case 'hourly':$ratio->setDuration(2*60*60);break;
			case 'daily':$ratio->setDuration(24*60*60);break;
		}
		$cds_ratio = $ratio->cds();
		$user_ratio = $ratio->user();
		$service_max_ratio = $ratio->service_max();
		$service_sum_ratio = $ratio->service_sum();
		
		$data = array(
			'cds' => array(
				'max' => array(
					'current' => $cds['max'],
					'linkRatio' => $cds_ratio['link']===NULL?NULL:1*number_format(division($cds['max'] - $cds_ratio['link'], $cds['max']),2,'.',''),//环比
					'sameRatio' => $cds_ratio['same']===NULL?NULL:1*number_format(division($cds['max'] - $cds_ratio['same'], $cds['max']),2,'.','')//同比
				),//CDS在线节点数
				'detail' => $cds['detail'],
			),
			'user' => array(
				'max' => array(
					'current' => $user['max'],
					'linkRatio' => $user_ratio['link']===NULL?NULL:1*number_format(division($user['max'] - $user_ratio['link'], $user['max']),2,'.',''),//环比
					'sameRatio' => $user_ratio['same']===NULL?NULL:1*number_format(division($user['max'] - $user_ratio['same'], $user['max']),2,'.','')//同比
				),//用户数
				'detail' => $user['detail'],
			),
			'service_max' => array(
				'max' => array(
					'current' => $service_max['max'],
					//'linkRatio' => $service_max_ratio['link']===NULL?NULL:1*number_format(division($service_max['max'] - $service_max_ratio['link'], $service_max['max']),2,'.',''),//环比
					//'sameRatio' => $service_max_ratio['same']===NULL?NULL:1*number_format(division($service_max['max'] - $service_max_ratio['same'], $service_max['max']),2,'.',''),//同比
				),//服务流速峰值（时间点内的最大值）
				'detail' => $service_max['detail'],//服务流速按照时间节点的详情
			),
			'cp_service' => array(
				'detail' => $cp_service['detail'],//分CP流量堆叠
			),
			'service_sum' => array(
				'max' => array(
					'current' => $service_sum['max'],
					'linkRatio' => $service_sum_ratio['link']===NULL?NULL:1*number_format(division($service_sum['max'] - $service_sum_ratio['link'], $service_sum['max']),2,'.',''),//环比
					'sameRatio' => $service_sum_ratio['same']===NULL?NULL:1*number_format(division($service_sum['max'] - $service_sum_ratio['same'], $service_sum['max']),2,'.','')//同比
				),//服务流量(时间点内的累加值)
				//'detail' => $service_sum_detail,//服务流量
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
				'deny',
				'actions' => '*',
				'express' => \application\entity\user::getLoginUserId()===NULL,
				'message' => new json(array('code'=>2,'result'=>'尚未登陆')),
			)
		);
	}
}