<?php
namespace framework\core;

class validator extends base
{
	
	/**
	 * 验证变量是否为纯数字 允许小数
	 * 包括int类型或者float类型或者string类型或者array类型
	 * 假如为array类型会递归判断
	 * 注意：负数会返回false
	 * @param string|int|array $variable
	 * @return boolean
	 */
	static function number($variable)
	{
		if (is_array($variable))
		{
			foreach ($variable as $var)
			{
				if (! self::number($var))
				{
					return false;
				}
			}
			return true;
		}
		else if (is_scalar($variable))
		{
			$pattern = '((?<number>\d+(.\d+)?))';
			if (preg_match($pattern, $variable, $matches))
			{
				if ($matches['number'] == $variable)
				{
					return true;
				}
			}
			return false;
		}
	}

	/**
	 * 验证变量是否为纯数字
	 * 包括int类型或者string类型或者array类型
	 * 假如为array类型会递归判断
	 * 注意：小数和负数会返回false   假如要判断包括小数在内的  请用validator::number
	 * @param string|int|array $variable
	 * @return boolean
	 */
	static function int($variable)
	{
		if (is_array($variable))
		{
			foreach ($variable as $var)
			{
				if (! self::int($var))
				{
					return false;
				}
			}
			return true;
		}
		else
		{
			$pattern = '^\d';
			if (preg_match($pattern, $variable, $matches))
			{
				return false;
			}
			return true;
		}
	}

	/**
	 * validator::int 的同名函数
	 * @param string|int|array $variable
	 * @return boolean
	 */
	static function integer($variable)
	{
		return self::int($variable);
	}
}
