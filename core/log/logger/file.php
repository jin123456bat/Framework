<?php
namespace framework\core\log\logger;

use framework\core\log\logger;

class file implements logger
{
	/**
	 * 保存日志的文件路径 目录
	 * @var string
	 */
	private $_path;
	
	/**
	 * 文件名前缀
	 * @var unknown
	 */
	private $_prefix;
	
	/**
	 * 日志单文件最大大小
	 * 单位字节
	 * @var int
	 */
	private $_file_max_size;
	
	/**
	 * 日志文件路径  文件
	 * @var string
	 */
	private $_file;
	
	function __construct($config)
	{
		$this->_path = $config['path'];
		$this->_prefix = $config['prefix'];
		$this->_file_max_size = $config['max_size'];
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
		$context = stream_context_create($context);
		file_put_contents($this->_file, '['.date('Y-m-d H:i:s').'] ['.__FUNCTION__.'] '.$message,FILE_APPEND,$context);
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
		$i = 0;
		$this->_file = rtrim($this->_path,'/').'/'.(!empty($this->_prefix)?($this->_prefix.'_'):'').$level.'_'.$i;
		while(file_exists($this->_file) && filesize($this->_file) >= $this->_file_max_size)
		{
			$i++;
			$this->_file = rtrim($this->_path,'/').'/'.(!empty($this->_prefix)?($this->_prefix.'_'):'').$level.'_'.$i;
		}
		
		return call_user_func(array($this,$level),$message,$context);
	}

	
}