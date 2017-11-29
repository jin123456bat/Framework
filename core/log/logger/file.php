<?php
namespace framework\core\log\logger;

use framework\core\log\logger;
use framework\data\data;

class file implements logger
{
	/**
	 * 保存日志的文件路径
	 * @var string
	 */
	private $_path;
	
	function __construct($config)
	{
		$this->_path = $config['path'];
	}
	
	/**
	 * {@inheritDoc}
	 * @see \framework\core\log\logger::emergency()
	 */
	public function emergency($message, array $context = array())
	{
		// TODO Auto-generated method stub
		
	}

	/**
	 * {@inheritDoc}
	 * @see \framework\core\log\logger::alert()
	 */
	public function alert($message, array $context = array())
	{
		// TODO Auto-generated method stub
		
	}

	/**
	 * {@inheritDoc}
	 * @see \framework\core\log\logger::critical()
	 */
	public function critical($message, array $context = array())
	{
		// TODO Auto-generated method stub
		
	}

	/**
	 * {@inheritDoc}
	 * @see \framework\core\log\logger::error()
	 */
	public function error($message, array $context = array())
	{
		// TODO Auto-generated method stub
		
	}

	/**
	 * {@inheritDoc}
	 * @see \framework\core\log\logger::warning()
	 */
	public function warning($message, array $context = array())
	{
		// TODO Auto-generated method stub
		
	}

	/**
	 * {@inheritDoc}
	 * @see \framework\core\log\logger::notice()
	 */
	public function notice($message, array $context = array())
	{
		// TODO Auto-generated method stub
		
	}

	/**
	 * {@inheritDoc}
	 * @see \framework\core\log\logger::info()
	 */
	public function info($message, array $context = array())
	{
		// TODO Auto-generated method stub
		file_put_contents($this->_path, '['.date('Y-m-d H:i:s').'] ['.__FUNCTION__.'] '.$message,FILE_APPEND,$context);
	}

	/**
	 * {@inheritDoc}
	 * @see \framework\core\log\logger::debug()
	 */
	public function debug($message, array $context = array())
	{
		// TODO Auto-generated method stub
		
	}

	/**
	 * {@inheritDoc}
	 * @see \framework\core\log\logger::log()
	 */
	public function log($level, $message, array $context = array())
	{
		return call_user_func(array($this,$level),$message,$context);
	}

	
}