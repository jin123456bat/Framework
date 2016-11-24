<?php
namespace application\extend;

use framework\core\request;
use framework\core\control;
use framework\core\response\json;

abstract class BaseControl extends control
{
	protected $_timemode;
	
	protected $_startTime;
	
	protected $_endTime;
	
	protected $_duration;
	
	protected $_duration_second;
	
	protected function setTime()
	{
		$this->_timemode = request::param('timemode');
		
		switch ($this->_timemode)
		{
			case '1':
				//最近24小时
				$timestamp = (floor(time() / (5*60)) - 1) * 5*60;
				$this->_endTime = date('Y-m-d H:i:s',$timestamp);
				$this->_startTime = date('Y-m-d H:i:s',strtotime('-24 hour',strtotime($this->_endTime)));
				break;
			case '2':
				//昨天
				$this->_startTime = date('Y-m-d 00:00:00',strtotime('-1 day'));
				$this->_endTime = date('Y-m-d 00:00:00');
				break;
			case '3':
				//上周
				$this->_startTime = date('Y-m-d 00:00:00', strtotime('last week'));
				$this->_endTime = date('Y-m-d 00:00:00',strtotime('this week'));
				break;
				//近7天
			case '4':
				$this->_startTime = date('Y-m-d 00:00:00',strtotime('-7 day'));
				$this->_endTime = date('Y-m-d 00:00:00');
				break;
			case '5':
				//最近30天
				$this->_endTime = date('Y-m-d 00:00:00');
				$this->_startTime = date('Y-m-d 00:00:00',strtotime('-30 day'));
				break;
			case '6':
				//上月
				$this->_endTime = date('Y-m-1 00:00:00');
				$this->_startTime = date('Y-m-1 00:00:00',strtotime('last month'));
				break;
			default:
				//自定义时间
				$this->_timemode = NULL;
				$this->_startTime = request::param('starttime');
				$this->_endTime = request::param('endtime');
				break;
		}
		
		if (strtotime($this->_startTime) === false)
		{
			return new json(json::FAILED,'开始时间错误');
		}
		if (strtotime($this->_endTime) ===  false)
		{
			return new json(json::FAILED,'结束时间错误');
		}
		if (strtotime($this->_startTime) >= strtotime($this->_endTime))
		{
			return new json(json::FAILED,'开始时间不能大于等于结束时间');
		}
		$this->_duration = request::param('duration');
		switch ($this->_duration)
		{
			case 'minutely':$this->_duration_second = 60*5;break;
			case 'hourly':$this->_duration_second = 60*60;break;
			case 'daily':$this->_duration_second = 60*60*24;break;
			default:
				return new json(json::FAILED,'duration参数错误');
		}
	}
	
	/**
	 * 获取分类名称
	 * @param array('class','category') $class_category
	 * @return NULL
	 */
	function getCategory($class_category)
	{
		if (isset($class_category['class']) && isset($class_category['category']))
		{
			$category = $this->getConfig('category');
			switch ($class_category['class'])
			{
				case 0: return $category['http'][$class_category['category']];
				case 1: return $category['mobile'][$class_category['category']];
				case 2:
					if ($class_category['category']>=128)
					{
						return $category['videoLive'][$class_category['category']-128];
					}
					return $category['videoDemand'][$class_category['category']];
			}
		}
		return NULL;
	}
	
	
	/**
	 * 获取有效的sn
	 * @return unknown[]
	 */
	function combineSns($sn = array())
	{
		if (empty($sn))
		{
			$return = array();
			$return1 = array();
			$sns = $this->model('operation_stat')->where('sn like ?',array('C_S%'))->select('distinct(sn)');
			foreach ($sns as $s)
			{
				$return[] = $s['sn'];
			}
			
			//sn必须同时在user_info表和feedback表中存在
			$u_sn = $this->model('feedback')->join('user_info','user_info.sn=feedback.sn')->select('distinct(user_info.sn)');
			foreach ($u_sn as $s)
			{
				$return1[] = $s['sn'];
			}
			return array_intersect($return,$return1);
		}
		return $sn;
	}
	
	function modelWithSn($name)
	{
		$sn = $this->combineSns();
		$model = parent::model($name);
		$sql = '';
		$param = array();
		if (is_array($sn))
		{
			foreach ($sn as $s)
			{
				$sql .= 'sn like ? or ';
				$param[] = '%'.substr($s, 3);
			}
		}
		$sql = substr($sql, 0,-4);
		$model->where($sql,$param);
		return $model;
	}
}