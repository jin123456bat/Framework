<?php
namespace framework\core;

class validator extends base
{
	static function int($variable)
	{
		if (is_array($variable))
		{
			foreach ($variable as $var)
			{
				if (!self::int($var))
				{
					return false;
				}
			}
			return true;
		}
		else
		{
			$pattern = '^\d';
			if(preg_match($pattern, $variable,$matches))
			{
				return false;
			}
			return true;
		}
	}
	
	static function integer($variable)
	{
		return self::int($variable);
	}
}