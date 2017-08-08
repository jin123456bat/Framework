<?php
namespace application\control;

use framework\core\response\json;
use application\extend\BaseControl;
use application\entity\user;
use application\algorithm\ratio;
use application\algorithm\algorithm;
use framework\core\request;
use application\extend\cache;
use framework\lib\data;

/**
 * 首页相关接口
 * 
 * @author fx
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
		if (! empty($this->_timemode))
		{
			$cache_key = 'main_overview_' . $this->_timemode;
			if (request::php_sapi_name() == 'web')
			{
				$response = cache::get($cache_key);
				if (! empty($response))
				{
					return new json(json::OK, null, $response);
				}
			}
		}
		
		$algorithm = new algorithm($this->_startTime, $this->_endTime, $this->_duration_second);
		
		switch ($this->_duration)
		{
			case 'minutely':
				$algorithm->setDuration(30 * 60);
				$algorithm->setTime(date('Y-m-d H:i:s', floor(strtotime($this->_startTime) / (30 * 60)) * 30 * 60), date('Y-m-d H:i:s', floor(strtotime($this->_endTime) / (30 * 60)) * 30 * 60));
			break;
			case 'hourly':
				$algorithm->setDuration(2 * 60 * 60);
			break;
			case 'daily':
				$algorithm->setDuration(24 * 60 * 60);
			break;
		}
		
		$cds = $algorithm->CDSOnlineNum();
		// user
		// 所有节点的在线人数累加
		$user = $algorithm->USEROnlineNum();
		
		switch ($this->_duration)
		{
			case 'minutely':
				$algorithm->setDuration(5 * 60);
				$algorithm->setTime($this->_startTime, $this->_endTime);
			break;
			case 'hourly':
				$algorithm->setDuration(2 * 60 * 60);
			break;
			case 'daily':
				$algorithm->setDuration(24 * 60 * 60);
			break;
		}
		$service_max = $algorithm->ServiceMax();
		
		// service_sum
		// 服务流量 时间段内所有service_size的总和
		$service_sum = $algorithm->ServiceSum();
		
		// cp_service
		switch ($this->_duration)
		{
			case 'minutely':
				$algorithm->setDuration(30 * 60);
				$algorithm->setTime(date('Y-m-d H:i:s', floor(strtotime($this->_startTime) / (30 * 60)) * 30 * 60), date('Y-m-d H:i:s', floor(strtotime($this->_endTime) / (30 * 60)) * 30 * 60));
			break;
			case 'hourly':
				$algorithm->setDuration(2 * 60 * 60);
			break;
			case 'daily':
				$algorithm->setDuration(24 * 60 * 60);
			break;
		}
		$cp_service = $algorithm->CPService();
		
		$ratio = new ratio($this->_timemode);
		switch ($this->_duration)
		{
			case 'minutely':
				$ratio->setDuration(30 * 60);
			break;
			case 'hourly':
				$ratio->setDuration(2 * 60 * 60);
			break;
			case 'daily':
				$ratio->setDuration(24 * 60 * 60);
			break;
			default:
				$ratio->setDuration($this->_duration_second);
		}
		$cds_ratio = $ratio->cds();
		$user_ratio = $ratio->user();
		
		switch ($this->_duration)
		{
			case 'minutely':
				$ratio->setDuration(5 * 60);
			break;
			case 'hourly':
				$ratio->setDuration(2 * 60 * 60);
			break;
			case 'daily':
				$ratio->setDuration(24 * 60 * 60);
			break;
			default:
				$ratio->setDuration($this->_duration_second);
		}
		$service_max_ratio = $ratio->service_max();
		$service_sum_ratio = $ratio->service_sum();
		
		$data = array(
			'cds' => array(
				'max' => array(
					'current' => $cds['max'],
					'linkRatio' => $cds_ratio['link'] === null ? null : 1 * number_format(division($cds['max'] - $cds_ratio['link'], $cds_ratio['link']), 2, '.', ''), // 环比
					'sameRatio' => $cds_ratio['same'] === null ? null : 1 * number_format(division($cds['max'] - $cds_ratio['same'], $cds_ratio['same']), 2, '.', '')
				), // 同比
				   // CDS在线节点数
				'detail' => $cds['detail']
			),
			'user' => array(
				'max' => array(
					'current' => $user['max'],
					'linkRatio' => $user_ratio['link'] === null ? null : 1 * number_format(division($user['max'] - $user_ratio['link'], $user_ratio['link']), 2, '.', ''), // 环比
					'sameRatio' => $user_ratio['same'] === null ? null : 1 * number_format(division($user['max'] - $user_ratio['same'], $user_ratio['same']), 2, '.', '')
				), // 同比
				   // 用户数
				'detail' => $user['detail']
			),
			'service_max' => array(
				'max' => array(
					'current' => $service_max['max'],
					'linkRatio' => $service_max_ratio['link'] === null ? null : 1 * number_format(division($service_max['max'] - $service_max_ratio['link'], $service_max_ratio['link']), 2, '.', ''), // 环比
					'sameRatio' => $service_max_ratio['same'] === null ? null : 1 * number_format(division($service_max['max'] - $service_max_ratio['same'], $service_max_ratio['same']), 2, '.', '')
				), // 同比
				   // 服务流速峰值（时间点内的最大值）
				'detail' => $service_max['detail']
			), // 服务流速按照时间节点的详情
			'cp_service' => array(
				'detail' => $cp_service['detail']
			), // 分CP流量堆叠
			'service_sum' => array(
				'max' => array(
					'current' => $service_sum['max'],
					'linkRatio' => $service_sum_ratio['link'] === null ? null : 1 * number_format(division($service_sum['max'] - $service_sum_ratio['link'], $service_sum_ratio['link']), 2, '.', ''), // 环比
					'sameRatio' => $service_sum_ratio['same'] === null ? null : 1 * number_format(division($service_sum['max'] - $service_sum_ratio['same'], $service_sum_ratio['same']), 2, '.', '')
				)
			) // 同比
		
		); // 服务流量(时间点内的累加值)
		   // 'detail' => $service_sum_detail,//服务流量
		
		if (! empty($this->_timemode))
		{
			cache::set($cache_key, $data);
		}
		
		return new json(json::OK, null, $data);
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
				'express' => request::php_sapi_name() == 'web' ? \application\entity\user::getLoginUserId() === null : false,
				'message' => new json(array(
					'code' => 2,
					'result' => '尚未登陆'
				))
			)
		);
	}
}
