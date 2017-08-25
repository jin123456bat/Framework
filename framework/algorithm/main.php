<?php
/**
 * 获取一个数组的某一列
 * 返回数字下标的数组
 * @param array $array
 * @param string|int $column
 * @return array
 */
function getArrayColumn($array,$column)
{
	$temp = array();
	foreach ($array as $value)
	{
		$temp[] = $value[$column];
	}
	return $temp;
}

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