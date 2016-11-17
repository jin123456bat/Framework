<?php
namespace application\extend;
use framework\core\component;

class BaseComponent extends component
{
	function combineSns($sn = array())
	{
		$return = array();
		$sql = 'SELECT sn FROM (SELECT DISTINCT (sn) FROM  `operation_stat`) AS t WHERE sn REGEXP  "C[A_Z]S"';
		$sns = $this->model('operation_stat')->query($sql);
		foreach ($sns as $sn)
		{
			$return[] = $sn['sn'];
		}
		if (is_array($sn))
		{
			return array_unique(array_merge($return,$sn));
		}
		else if (is_scalar($sn))
		{
			$return[] = $sn;
			return $return;
		}
	}
}