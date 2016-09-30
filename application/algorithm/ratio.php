<?php
namespace application\algorithm;

use framework\core\base;

/**
 * @author fx
 *
 */
class ratio extends base
{
	private $_timenode = NULL;
	
	private $_same = array();
	
	private $_link = array();
	
	private $_duration_second = 0;
	
	private $_algorithm = NULL;
	
	function __construct($timenode)
	{
		//最近24小时
		//昨天
		//上周
		if (in_array($timenode, array(1,2,3)))
		{
			$this->_timenode = $timenode;
			$this->_parseTime();
			$this->_algorithm = new algorithm();
		}
	}
	
	private function _parseTime()
	{
		switch ($this->_timenode)
		{
			case 1:
				//最近24小时的同比，是指跟上周的同一个时间相比
				$this->_same = array(
					'starttime' => date('Y-m-d H:00:00',strtotime('-8 day')),
					'endtime' => date('Y-m-d H:00:00',strtotime('-7 day')),
				);
				//最近24小时的环比,是指
				$this->_link = array(
					'starttime' => date('Y-m-d H:00:00',strtotime('-2 day')),
					'endtime' => date('Y-m-d H:00:00',strtotime('-1 day'))
				);
				break;
			case 2:
				//昨天的同比，是指上周的同一天做对比
				$this->_same = array(
					'starttime' => date('Y-m-d 00:00:00',strtotime('-8 day')),
					'endtime' => date('Y-m-d 00:00:00',strtotime('-7 day')),
				);
				$this->_link = array(
					'starttime' => date('Y-m-d 00:00:00',strtotime('-2 day')),
					'endtime' => date('Y-m-d 00:00:00',strtotime('-1 day'))
				);
				break;
			case 3:
				//上周的同比  是指跟上个月的同一周做对比
				$this->_same = array(
					'starttime' => date('Y-m-d 00:00:00',strtotime('last week last month')),
					'endtime' => date('Y-m-d 00:00:00',strtotime('this week last month')),
				);
				$this->_link = array(
					'starttime' => date('Y-m-d 00:00:00',strtotime('last week last week')),
					'endtime' => date('Y-m-d 00:00:00',strtotime('last week')),
				);
				break;
		}
	}
	
	function setDuration($duration)
	{
		$this->_duration_second = $duration;
		if (!empty($this->_algorithm))
		{
			$this->_algorithm->setDuration($this->_duration_second);
		}
	}
	
	/**
	 * 计算CDS最大数量的同比和环比
	 * @return number[]
	 */
	function cds()
	{
		if (empty($this->_timenode))
		{
			return array(
				'link' => NULL,
				'same' => NULL,
			);
		}
		
		
		$this->_algorithm->setTime($this->_same['starttime'], $this->_same['endtime']);
		$same = $this->_algorithm->CDSOnlineNum();
		
		$this->_algorithm->setTime($this->_link['starttime'], $this->_link['endtime']);
		$link = $this->_algorithm->CDSOnlineNum();

		return array(
			'same' => $same['max'],
			'link' => $link['max'],
		);
	}
	
	function user()
	{
		if (empty($this->_timenode))
		{
			return array(
				'link' => NULL,
				'same' => NULL,
			);
		}
		
		$this->_algorithm->setTime($this->_same['starttime'], $this->_same['endtime']);
		$same = $this->_algorithm->USEROnlineNum();
		
		$this->_algorithm->setTime($this->_link['starttime'], $this->_link['endtime']);
		$link = $this->_algorithm->USEROnlineNum();
		
		return array(
			'link' => $link['max'],
			'same' => $same['max']
		);
	}
	
	function service_max()
	{
		if (empty($this->_timenode))
		{
			return array(
				'link' => NULL,
				'same' => NULL,
			);
		}
		
		$this->_algorithm->setTime($this->_same['starttime'], $this->_same['endtime']);
		$same = $this->_algorithm->ServiceMax();
		
		$this->_algorithm->setTime($this->_link['starttime'], $this->_link['endtime']);
		$link = $this->_algorithm->ServiceMax();
		
		return array(
			'link' => $link['max'],
			'same' => $same['max']
		);
	}
	
	function service_sum()
	{
		if (empty($this->_timenode))
		{
			return array(
				'link' => NULL,
				'same' => NULL,
			);
		}
		
		$this->_algorithm->setTime($this->_same['starttime'], $this->_same['endtime']);
		$same = $this->_algorithm->ServiceSum();
		
		$this->_algorithm->setTime($this->_link['starttime'], $this->_link['endtime']);
		$link = $this->_algorithm->ServiceSum();
		
		return array(
			'link' => $link['max'],
			'same' => $same['max']
		);
	}
}