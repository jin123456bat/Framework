<?php
/**
 * 获取一个数组的某一列
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
 */
function quickSort()
{
	
}