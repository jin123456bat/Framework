<?php
namespace application\algorithm;

use application\extend\BaseComponent;
use framework\core\model;

/**
 * 这些接口只为api_overview页面提供
 *
 * @author fx
 *        
 */
class api_overview extends BaseComponent
{

	private $_duration;

	private $_startTime;

	private $_endTime;

	function __construct($duration = 300, $startTime = '', $endTime = '')
	{
		$this->_startTime = $startTime;
		$this->_endTime = $endTime;
		$this->_duration = $duration;
	}

	function setDuration($duration)
	{
		$this->_duration = $duration;
	}

	function setTime($startTime, $endTime)
	{
		$this->_startTime = $startTime;
		$this->_endTime = $endTime;
	}

	function cds($sn = array())
	{
		$cds_detail = array();
		sort($sn);
		$sn_md5 = md5(implode(',', $sn));
		$tableName = 'combined_sn_data_container_' . $this->_duration;
		$result = $this->model($tableName)
			->where('sn_md5=? and name=?', array(
			$sn_md5,
			'api_cds_online'
		))
			->where('time>=? and time<?', array(
			$this->_startTime,
			$this->_endTime
		))
			->select(array(
			'time',
			'value'
		));
		foreach ($result as $r)
		{
			$cds_detail[$r['time']] = $r['value'] * 1;
		}
		return $cds_detail;
	}

	function user($sn = array())
	{
		$user_detail = array();
		sort($sn);
		$sn_md5 = md5(implode(',', $sn));
		$tableName = 'combined_sn_data_container_' . $this->_duration;
		$result = $this->model($tableName)
			->where('sn_md5=? and name=?', array(
			$sn_md5,
			'user_online'
		))
			->where('time>=? and time<?', array(
			$this->_startTime,
			$this->_endTime
		))
			->select(array(
			'time',
			'value'
		));
		foreach ($result as $r)
		{
			$user_detail[$r['time']] = $r['value'] * 1;
		}
		return $user_detail;
	}

	function traffic_stat_service($sn = array())
	{
		$user_detail = array();
		sort($sn);
		$sn_md5 = md5(implode(',', $sn));
		$tableName = 'combined_sn_data_container_' . $this->_duration;
		$result = $this->model($tableName)
			->where('sn_md5=? and name=?', array(
			$sn_md5,
			'traffic_stat_service'
		))
			->where('time>=? and time<?', array(
			$this->_startTime,
			$this->_endTime
		))
			->select(array(
			'time',
			'value'
		));
		foreach ($result as $r)
		{
			$user_detail[$r['time']] = $r['value'] * 1;
		}
		return $user_detail;
	}

	function operation_stat_sum($sn = array())
	{
		if (is_string($sn))
		{
			$sn = explode(',', $sn);
		}
		
		$tableName = 'operation_stat_sn_' . $this->_duration;
		$sn = array_map(function ($s)
		{
			return substr($s, 3);
		}, $sn);
		
		return 1 * $this->model($tableName)
			->in('sn', $sn)
			->where('time>=? and time<?', array(
			$this->_startTime,
			$this->_endTime
		))
			->sum('service_size');
	}
}
