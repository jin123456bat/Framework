<?php
/**
 * 快速排序算法
 * 返回数据所在的key
 * @param array $array
 * @param mixed $data
 * @return array
 */
function quickSort($array)
{
	if (count($array)<=1)
	{
		return $array;
	}
	else
	{
		$flag = array_shift($array);
		
		$left = array();
		$right = array();
		
		foreach ($array as $value)
		{
			if ($value < $flag)
			{
				$left[] = $value;
			}
			else
			{
				$right[] = $value;
			}
		}
		//从小到大
		return array_merge(quickSort($left),array($flag),quickSort($right));
	}
}