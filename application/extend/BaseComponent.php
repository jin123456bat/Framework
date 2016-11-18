<?php
namespace application\extend;
use framework\core\component;

class BaseComponent extends component
{
	function combineSns($sn = array())
	{
		if (empty($sn))
		{
			$return = array();
			$return1 = array();
			$sql = 'SELECT sn FROM (SELECT DISTINCT (sn) FROM  `operation_stat`) AS t WHERE sn REGEXP  "C[A_Z]S"';
			$sns = $this->model('operation_stat')->query($sql);
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
}