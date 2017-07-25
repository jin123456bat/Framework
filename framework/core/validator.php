<?php
namespace framework\core;

/**
 * 在实体类(\framework\lib\data)中使用
 * 用于验证变量参数是否符合规则
 */
class validator extends base
{
	/**
	 * 大于等于
	 */
	static function ge($val1,$val2)
	{
		return $val1>=$val2;
	}
	
	/**
	 * 小于等于
	 */
	static function le($val1,$val2)
	{
		return $val1<=$val2;
	}
	
	/**
	 * 等于
	 * @param unknown $val1
	 * @param unknown $val2
	 */
	static function ne($val1,$val2)
	{
		return $val1 != $val2;
	}
	
	/**
	 * 小于
	 */
	static function lt($val1,$val2)
	{
		return $val1<$val2;
	}
	
	/**
	 * 大于
	 */
	static function gt($val1,$val2)
	{
		return $val1>$val2;
	}
	
	/**
	 * 等于
	 */
	static function eq($val1,$val2)
	{
		return $val1 == $val2;
	}
	
	/**
	 * 验证变量必须存在
	 * 相当于empty
	 * @param string|int|array $variable
	 * @return boolean
	 */
	static function required($variable)
	{
		return !empty($variable);
	}
	
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
			$pattern = '/-?\d+(.\d+)?/';
			if (preg_match($pattern, $variable, $matches))
			{
				if ($matches[0] == $variable)
				{
					return true;
				}
			}
			return false;
		}
	}
	
	/**
	 * self::number的同名函数
	 * @see
	 * 		self::number($variable)
	 * @return boolean
	 */
	static function decimal($variable)
	{
		return self::number($variable);
	}

	/**
	 * 验证变量是否为纯数字 允许为负数
	 * 包括int类型或者string类型或者array类型
	 * 假如为array类型会递归判断 假如有一个不满足要求则会返回false
	 * 注意：小数会返回false   假如要判断包括小数在内的  请用validator::number
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
			$pattern = '/-?\d+/';
			if (preg_match($pattern, $variable, $matches))
			{
				return $matches[0] == $variable;
			}
			return false;
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
