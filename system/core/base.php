<?php
namespace core;
class base
{
	function __construct()
	{
		
	}
	
	public function initlize()
	{
		
	}
	
	public function hash()
	{
		return spl_object_hash($this);
	}
	
	/**
	 * 变量类型强制转换
	 * @param unknown $variable
	 * @param string $type
	 * @return string|array|boolean|number|StdClass|unknown
	 */
	private function setVariableType($variable,$type = 's')
	{
		switch ($type)
		{
			case 's':return (string)$variable;
			case 'a':return (array)$variable;
			case 'b':return (bool)$variable;
			case 'd'://double
			case 'f':return (float)$variable;
			case 'o':return (object)$variable;
			case 'i':return (int)$variable;
			case 'binary':return (binary)$variable;
			default:if(settype($variable, $type))
			{
				return $variable;
			}
		}
	}
}