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
				$cache = array_intersect($return,$return1);
			}
			return $cache;
		}
		return self::setVariableType($sn,'a');
	}
}