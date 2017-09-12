<?php
namespace framework\core;

use framework\vendor\captcha;

/**
 * 在实体类(\framework\lib\data)中使用
 * 用于验证变量参数是否符合规则
 */
class validator extends base
{

	static function enum($val, $array)
	{
		return in_array(trim($val), $array);
	}

	static function datetime($val, $format)
	{
		if (empty($format))
		{
			$format = 'Y-m-d H:i:s';
		}
		return trim($val) == date($format, strtotime($val));
	}

	/**
	 * 国内手机号码
	 * 
	 * @param unknown $val        
	 * @return number
	 */
	static function telephone($val)
	{
		if (preg_match('/(\+?86)?0?(13|14|15|18)[0-9]{9}/', trim($val), $match))
		{
			return $match[0] == $val;
		}
		return false;
	}

	/**
	 * 全中文
	 */
	static function chinese($val)
	{
		if (preg_match('/[\u4e00-\u9fa5]/', trim($val), $match))
		{
			return $match[0] == $val;
		}
		return false;
	}

	/**
	 * email
	 * 
	 * @param unknown $val        
	 */
	static function email($val)
	{
		if (preg_match('/\w[-\w.+]*@([A-Za-z0-9][-A-Za-z0-9]+\.)+[A-Za-z]{2,14}/', trim($val), $match))
		{
			return $match[0] == $val;
		}
		return false;
	}

	/**
	 * url
	 * 
	 * @param unknown $val        
	 * @return number
	 */
	static function url($val)
	{
		if (preg_match('/^((https|http|ftp|rtsp|mms)?:\/\/)[^\s]+/', trim($val), $match))
		{
			return $match[0] == $val;
		}
		return false;
	}

	static function ip($val)
	{
		$val = explode('/', trim($val));
		
		if (isset($val[1]))
		{
			if (! ($val[1] > 0 && $val[1] < 36))
			{
				return false;
			}
		}
		if (preg_match('/(25[0-5]|2[0-4]\d|[0-1]\d{2}|[1-9]?\d)\.(25[0-5]|2[0-4]\d|[0-1]\d{2}|[1-9]?\d)\.(25[0-5]|2[0-4]\d|[0-1]\d{2}|[1-9]?\d)\.(25[0-5]|2[0-4]\d|[0-1]\d{2}|[1-9]?\d)/', $val[0], $match))
		{
			return $match[0] == $val[0];
		}
		return false;
	}

	/**
	 * 国内身份证号码
	 */
	static function idcard($val)
	{
		if (preg_match('/\d{17}[\d|x]|\d{15}/', trim($val), $match))
		{
			return $match[0] == $val;
		}
		return false;
	}

	/**
	 * 判断请求参数中不能包含某字段
	 * 
	 * @param unknown $val        
	 * @return boolean
	 */
	static function unsafe($val)
	{
		return $val === NULL;
	}

	/**
	 * 大于等于
	 */
	static function ge($val1, $val2)
	{
		return $val1 >= $val2;
	}

	/**
	 * 小于等于
	 */
	static function le($val1, $val2)
	{
		return $val1 <= $val2;
	}

	/**
	 * 等于
	 * 
	 * @param unknown $val1        
	 * @param unknown $val2        
	 */
	static function ne($val1, $val2)
	{
		return $val1 != $val2;
	}

	/**
	 * 小于
	 */
	static function lt($val1, $val2)
	{
		return $val1 < $val2;
	}

	/**
	 * 大于
	 */
	static function gt($val1, $val2)
	{
		return $val1 > $val2;
	}

	/**
	 * 等于
	 */
	static function eq($val1, $val2)
	{
		return $val1 == $val2;
	}

	/**
	 * 判断验证器中变量必须存在
	 * 
	 * @param string|int|array $variable        
	 * @return boolean
	 */
	static function required($variable)
	{
		return ! empty($variable);
	}

	/**
	 * 验证变量是否为纯数字 允许小数
	 * 包括int类型或者float类型或者string类型或者array类型
	 * 假如为array类型会递归判断
	 * 注意：负数会返回false
	 * 
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
	 * 
	 * @see self::number($variable)
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
	 * 注意：小数会返回false 假如要判断包括小数在内的 请用validator::number
	 * 
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
	 * 
	 * @param string|int|array $variable        
	 * @return boolean
	 */
	static function integer($variable)
	{
		return self::int($variable);
	}
	
	/**
	 * 验证验证码是否正确
	 * @param string $code
	 */
	static function captcha($code)
	{
		return captcha::validate($code);
	}
}
