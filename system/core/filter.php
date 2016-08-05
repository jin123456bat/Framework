<?php
namespace system\core;

class filter extends base
{
	/**
	 * integer的别名
	 * @see filter::integer
	 * @param unknown $string
	 * @return boolean
	 */
	static function int($string)
	{
		return self::integer($string);
	}
	
	/**
	 * 纯数字过滤器
	 * @param string|array $string
	 * @return boolean
	 */
	static function integer($string)
	{
		if (is_array($string))
		{
			return array_map(function($n){
				return self::integer($n);
			}, $string);
		}
		else
		{
			$pattern = '\d+';
			if(preg_match($pattern, $string,$matches))
			{
				return implode('', $matches[0]);
			}
			return 0;
		}
	}
	
	/**
	 * 浮点数过滤器
	 * @param unknown $number
	 * @return mixed
	 */
	static function number($number)
	{
		if ($number == '.')
		{
			return 0;
		}
		if ($number[0] == '.')
		{
			$number = '0'.$number;
		}
		if ($number[-1] == '.')
		{
			$number .= '0';
		}
		$pattern = '\d+.\d*';
		if (preg_match($pattern, $number,$matches))
		{
			return $matches[0];
		}
		return 0;
	}
	
	/**
	 * 字母或数字或下划线
	 * @param unknown $string
	 * @return string|number
	 */
	static function word($string)
	{
		$pattern = '\w+';
		if(preg_match($pattern, $string,$matches))
		{
			return implode('', $matches[0]);
		}
		return '';
	}
	
	/**
	 * 去除空白
	 * @param unknown $string
	 */
	static function anyspace($string)
	{
		$pattern = '\S+';
		if(preg_match($pattern, $string,$matches))
		{
			return implode('', $matches[0]);
		}
		return NULL;
	}
}