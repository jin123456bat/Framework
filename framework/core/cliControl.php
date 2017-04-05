<?php
namespace framework\core;

class cliControl extends control
{
	/**
	 * 获取参数  尚未完成
	 * @param string $name
	 */
	public function getParam($name,$default = NULL)
	{
		if(request::php_sapi_name() == 'cli')
		{
			$param = self::parseArgment($_SERVER['argc'], $_SERVER['argv']);
			if (isset($param[$name]))
			{
				return $param[$name];
			}
		}
		return $default;
	}
	
	/**
	 * 对命令行参数经行分析
	 * -a index => $_GET['a'] = 'index'
	 * --b index => $_POST['b'] = 'index'
	 * --a a --a b => $_GET['a'] = array('a','b')
	 * @param unknown $argc
	 * @param unknown $argv
	 */
	static public function parseArgment($argc, $argv)
	{
		$param = array();
	
		foreach ($argv as $index => $value)
		{
			if (substr($value, 0, 1) == '-')
			{
				if (isset($argv[$index + 1]))
				{
					if (isset($param[substr($value, 1)]))
					{
						if (is_array($param[substr($value, 1)]))
						{
							$param[substr($value, 1)][] = $argv[$index + 1];
						}
						else if (is_string($param[substr($value, 1)]))
						{
							$param[substr($value, 1)] = array(
								$param[substr($value, 1)],
								$argv[$index + 1]
							);
						}
					}
					else
					{
						$param[substr($value, 1)] = $argv[$index + 1];
					}
					unset($argv[$index + 1]);
				}
				else
				{
					$param[substr($value, 1)] = true;
				}
				unset($argv[$index]);
			}
		}
		return $param;
	}
}