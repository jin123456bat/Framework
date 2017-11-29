<?php
namespace framework\core;

use framework\core\log\logger;

class log extends component
{
	const LEVEL_EMERGENCY = 'emergency';
	
	const LEVEL_ALERT = 'alert';
	
	const LEVEL_CRITICAL = 'critical';
	
	const LEVEL_ERROR = 'error';
	
	const LEVEL_WARNING = 'warning';
	
	const LEVEL_NOTICE = 'notice';
	
	const LEVEL_INFO = 'info';
	
	const LEVEL_DEBUG = 'debug';
	
	/**
	 * @var array 
	 */
	private $_config;
	
	public static $_logger;

	function __construct()
	{
	}

	function initlize()
	{
	}

	/**
	 * @return logger
	 */
	private static function getLogger()
	{
		if (empty(self::$_logger))
		{
			$config = self::getConfig('log');
			$logger = $config['logger'];
			$config = $config[$logger];
			
			self::$_logger = new $logger($config);
		}
		return self::$_logger;
	}

	/**
	 * 用于记录mysql的日志信息
	 * 
	 * @param unknown $sql        
	 * @param unknown $time        
	 */
	public static function mysql($sql, $time)
	{
		$logger = self::getLogger();
		$logger->log(self::LEVEL_INFO, $sql . '[' . $time . ']');
	}
}
