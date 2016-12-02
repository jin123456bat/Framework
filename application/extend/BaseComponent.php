<?php
namespace application\extend;
use framework\core\component;

class BaseComponent extends component
{
	function combineSns($sn = array())
	{
		if (empty($sn))
		{
			static $cache = NULL;
			if (empty($cache))
			{
				$temp = array();
				//sn必须同时在user_info表和feedback表中存在
				$u_sn = $this->model('feedback')->join('user_info','user_info.sn=feedback.sn')->where('feedback.version>=?',array('9.1.0'))->select('distinct(user_info.sn)');
				foreach ($u_sn as $s)
				{
					$temp[] = $s['sn'];
				}
				$cache = $temp;
			}
			return $cache;
		}
		return self::setVariableType($sn,'a');
	}
}