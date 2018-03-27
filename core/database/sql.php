<?php
namespace framework\core\database;

use framework\core\base;

abstract class sql extends base
{
	/**
	 * 关联params和sql后的sql
	 */
	function getSql($sql = null, $params = array())
	{
		if (empty($sql))
		{
			$sql = $this->__toString();
		}
		
		// 去掉sql中的百分号
		$sql = str_replace('%', '#', $sql);
		
		$sql_s = str_replace('?', '%s', $sql);
		if (empty($params))
		{
			$params = $this->getParams();
		}
		// echo $sql.'<br>|';
		
		$num_params = array();
		$word_params = array();
		foreach ($params as $index => $value)
		{
			if (is_int($index))
			{
				$num_params[] = '\'' . $value . '\'';
			}
			else
			{
				$word_params[$index] = '\'' . $value . '\'';
			}
		}
		
		// 排序，防止出现 a把ab替换掉了
		uksort($word_params, function ($a, $b) {
			if (strlen($a) > strlen($b))
			{
				return - 1;
			}
			elseif (strlen($a) == strlen($b))
			{
				return 0;
			}
			return 1;
		});
			$sql_w = vsprintf($sql_s, $num_params);
			// 把#替换为% 恢复sql
			$sql_w = str_replace('#', '%', $sql_w);
			
			foreach ($word_params as $index => $value)
			{
				$sql_w = str_replace(':' . $index, $value, $sql_w);
			}
			return $sql_w;
	}
}