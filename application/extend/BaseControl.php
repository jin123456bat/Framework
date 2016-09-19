<?php
namespace application\extend;

use framework\core\control;

class BaseControl extends control
{
	private $_startTime;
	
	private $_endTime;
	
	private $_duration;
	
	private $_duration_second;
	
	private function setTime()
	{
		$this->_startTime = request::param('starttime');
		$this->_endTime = request::param('endtime');
		$this->_duration = request::param('duration');
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
		if (!in_array($this->_duration,array('minutely','hourly','daily')))
		{
			return new json(json::FAILED,'duration参数错误');
		}
	
		$this->_duration_second = $this->_duration == 'hourly'?60*60:($this->_duration=='minutely'?60:60*60*24);
	}
}