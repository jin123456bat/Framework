<?php
namespace application\algorithm;

use framework\core\base;

/**
 *
 * @author fx
 */
class ratio extends base
{

	protected $_timenode = null;

	protected $_same = array();

	protected $_link = array();

	protected $_duration_second = 0;

	protected $_algorithm = null;

	function __construct($timenode)
	{
		// 最近24小时
		// 昨天
		// 上周
		// 近7天
		// 近30天
		// 上个月
		$this->_timenode = $timenode;
		$this->_parseTime();
		$this->_algorithm = new algorithm();
	}

	private function _parseTime()
	{
		switch ($this->_timenode)
		{
			case 1:
				// 最近24小时的同比，是指跟上周的同一个时间相比
				$timestamp = (floor(time() / (5 * 60)) - 1) * 5 * 60;
				$this->_same = array(
					'starttime' => date('Y-m-d H:i:s', strtotime('-8 day', $timestamp)),
					'endtime' => date('Y-m-d H:i:s', strtotime('-7 day', $timestamp))
				);
				// 最近24小时的环比,是指
				$this->_link = array(
					'starttime' => date('Y-m-d H:i:s', strtotime('-2 day', $timestamp)),
					'endtime' => date('Y-m-d H:i:s', strtotime('-1 day', $timestamp))
				);
			break;
			case 2:
				// 昨天的同比，是指上周的同一天做对比
				$this->_same = array(
					'starttime' => date('Y-m-d 00:00:00', strtotime('-8 day')),
					'endtime' => date('Y-m-d 00:00:00', strtotime('-7 day'))
				);
				$this->_link = array(
					'starttime' => date('Y-m-d 00:00:00', strtotime('-2 day')),
					'endtime' => date('Y-m-d 00:00:00', strtotime('-1 day'))
				);
			break;
			case 3:
				// 近7天的同比 跟上个月的同样的7天
				$this->_same = array(
					'starttime' => date('Y-m-d 00:00:00', strtotime('-1 month -7day')),
					'endtime' => date('Y-m-d 00:00:00', strtotime('-1 month'))
				);
				$this->_link = array(
					'starttime' => date('Y-m-d 00:00:00', strtotime('-14 day')),
					'endtime' => date('Y-m-d 00:00:00', strtotime('-7 day'))
				);
			break;
			case 4:
				// 上周的同比 是指跟上个月的同一周做对比
				$this->_same = array(
					'starttime' => date('Y-m-d 00:00:00', strtotime('last week last month')),
					'endtime' => date('Y-m-d 00:00:00', strtotime('this week last month'))
				);
				$this->_link = array(
					'starttime' => date('Y-m-d 00:00:00', strtotime('last week last week')),
					'endtime' => date('Y-m-d 00:00:00', strtotime('last week'))
				);
			break;
			case 5:
				// 近30天
				$this->_same = array(
					'starttime' => date('Y-m-d 00:00:00', strtotime('-1 year -30 day')),
					'endtime' => date('Y-m-d 00:00:00', strtotime('-1 year'))
				);
				$this->_link = array(
					'starttime' => date('Y-m-d 00:00:00', strtotime('-60 day')),
					'endtime' => date('Y-m-d 00:00:00', strtotime('-30 day'))
				);
			break;
			case 6:
				// 上个月
				$this->_same = array(
					'starttime' => date('Y-m-1 00:00:00', strtotime('-1 year -1 month')),
					'endtime' => date('Y-m-1 00:00:00', strtotime('-1 year'))
				);
				$this->_link = array(
					'starttime' => date('Y-m-1 00:00:00', strtotime('-2 month')),
					'endtime' => date('Y-m-1 00:00:00', strtotime('-1 month'))
				);
			break;
		}
	}

	function setDuration($duration)
	{
		$this->_duration_second = $duration;
		if (! empty($this->_algorithm))
		{
			$this->_algorithm->setDuration($this->_duration_second);
		}
	}

	/**
	 * 计算CDS最大数量的同比和环比
	 * 
	 * @return number[]
	 */
	function cds($sn = array())
	{
		if (! empty($this->_same))
		{
			$this->_algorithm->setTime($this->_same['starttime'], $this->_same['endtime']);
			$same = $this->_algorithm->CDSOnlineNum($sn);
		}
		else
		{
			$same = array(
				'max' => null
			);
		}
		
		if (! empty($this->_link))
		{
			$this->_algorithm->setTime($this->_link['starttime'], $this->_link['endtime']);
			$link = $this->_algorithm->CDSOnlineNum($sn);
		}
		else
		{
			$link = array(
				'max' => null
			);
		}
		
		return array(
			'same' => $same['max'],
			'link' => $link['max']
		);
	}

	function user($sn = array())
	{
		if (! empty($this->_same))
		{
			$this->_algorithm->setTime($this->_same['starttime'], $this->_same['endtime']);
			$same = $this->_algorithm->USEROnlineNum($sn);
		}
		else
		{
			$same = array(
				'max' => null
			);
		}
		
		if (! empty($this->_link))
		{
			$this->_algorithm->setTime($this->_link['starttime'], $this->_link['endtime']);
			$link = $this->_algorithm->USEROnlineNum($sn);
		}
		else
		{
			$link = array(
				'max' => null
			);
		}
		
		return array(
			'link' => $link['max'],
			'same' => $same['max']
		);
	}

	function service_max($sn = array())
	{
		if (! empty($this->_same))
		{
			$this->_algorithm->setTime($this->_same['starttime'], $this->_same['endtime']);
			$same = $this->_algorithm->ServiceMax($sn);
		}
		else
		{
			$same = array(
				'max' => null
			);
		}
		
		if (! empty($this->_link))
		{
			$this->_algorithm->setTime($this->_link['starttime'], $this->_link['endtime']);
			$link = $this->_algorithm->ServiceMax($sn);
		}
		else
		{
			$link = array(
				'max' => null
			);
		}
		
		return array(
			'link' => $link['max'],
			'same' => $same['max']
		);
	}

	function service_sum($sn = array())
	{
		if (! empty($this->_same))
		{
			$this->_algorithm->setTime($this->_same['starttime'], $this->_same['endtime']);
			$same = $this->_algorithm->ServiceSum($sn);
		}
		else
		{
			$same = array(
				'max' => null
			);
		}
		
		if (! empty($this->_link))
		{
			$this->_algorithm->setTime($this->_link['starttime'], $this->_link['endtime']);
			$link = $this->_algorithm->ServiceSum($sn);
		}
		else
		{
			$link = array(
				'max' => null
			);
		}
		
		return array(
			'link' => $link['max'],
			'same' => $same['max']
		);
	}
}
