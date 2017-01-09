<?php
namespace application\algorithm;

class api_overview_ratio extends ratio
{
	function __construct($timenode)
	{
		parent::__construct($timenode);
		$this->_algorithm = new api_overview();
	}
	
	/**
	 * 计算CDS最大数量的同比和环比
	 * @return number[]
	 */
	function cds($sn = array())
	{
		if (!empty($this->_same))
		{
			$this->_algorithm->setTime($this->_same['starttime'], $this->_same['endtime']);
			$same = $this->_algorithm->cds($sn);
		}
		else
		{
			$same = NULL;
		}
		if (!empty($this->_link))
		{
			$this->_algorithm->setTime($this->_link['starttime'], $this->_link['endtime']);
			$link = $this->_algorithm->cds($sn);
		}
		else
		{
			$link = NULL;
		}
	
		return array(
			'same' => empty($same)?NULL:max($same),
			'link' =>empty($link)?NULL:max($link),
		);
	}
	
	function user($sn = array())
	{
		if (!empty($this->_same))
		{
			$this->_algorithm->setTime($this->_same['starttime'], $this->_same['endtime']);
			$same = $this->_algorithm->user($sn);
		}
		else
		{
			$same = NULL;
		}
	
		if (!empty($this->_link))
		{
			$this->_algorithm->setTime($this->_link['starttime'], $this->_link['endtime']);
			$link = $this->_algorithm->user($sn);
		}
		else
		{
			$link = NULL;
		}
	
		return array(
			'link' => empty($link)?NULL:max($link),
			'same' => empty($same)?NULL:max($same),
		);
	}
	
	function service_max($sn = array())
	{
		if (!empty($this->_same))
		{
			$this->_algorithm->setTime($this->_same['starttime'], $this->_same['endtime']);
			$same = $this->_algorithm->traffic_stat_service($sn);
		}
		else
		{
			$same = NULL;
		}
	
		if (!empty($this->_link))
		{
			$this->_algorithm->setTime($this->_link['starttime'], $this->_link['endtime']);
			$link = $this->_algorithm->traffic_stat_service($sn);
		}
		else
		{
			$link = NULL;
		}
	
		return array(
			'link' => empty($link)?NULL:max($link),
			'same' => empty($same)?NULL:max($same),
		);
	}
	
	function service_sum($sn = array())
	{
		if (!empty($this->_same))
		{
			$this->_algorithm->setTime($this->_same['starttime'], $this->_same['endtime']);
			$same = $this->_algorithm->operation_stat_sum($sn);
		}
		else
		{
			$same = NULL;
		}
	
		if (!empty($this->_link))
		{
			$this->_algorithm->setTime($this->_link['starttime'], $this->_link['endtime']);
			$link = $this->_algorithm->operation_stat_sum($sn);
		}
		else
		{
			$link = NULL;
		}
	
		return array(
			'link' => $link,
			'same' => $same,
		);
	}
}