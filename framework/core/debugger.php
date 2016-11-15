<?php
namespace framework\core;
class debugger extends component
{
	/**
	 * 包含微秒的开始时间
	 * @var float
	 */
	private $_micro_starttime;
	
	/**
	 * 调试结束时间
	 * @var float
	 */
	private $_micro_endtime;
	
	/**
	 * 执行时间
	 * @var float
	 */
	private $_time;
	
	/**
	 * cpu时间
	 * @var unknown
	 */
	private $_cpu;
	
	/**
	 * 开始计数的内存占用
	 * @var unknown
	 */
	private $_start_memory;
	
	/**
	 * 结束计数内存占用
	 * @var unknown
	 */
	private $_end_memory;
	
	/**
	 * 内存占用
	 * @var float
	 */
	private $_memory;
	
	function __construct()
	{
		list($msec,$sec) = explode(' ', microtime());
		$this->_micro_starttime = $sec + $msec;
		$this->_start_memory = memory_get_usage(true);
	}
	
	/**
	 * 开始debug计数
	 */
	function start()
	{
		list($msec,$sec) = explode(' ', microtime());
		$this->_micro_starttime = $sec + $msec;
		$this->_start_memory = memory_get_usage(true);
	}
	
	/**
	 * 结束debug计数
	 */
	function stop()
	{
		list($msec,$sec) = explode(' ', microtime());
		$this->_micro_endtime = $sec + $msec;
		$this->_end_memory = memory_get_usage(true);
		
		$this->_time = $this->_micro_endtime - $this->_micro_starttime;
		$this->_memory = $this->_end_memory - $this->_start_memory;
	}
	
	/**
	 * 获取开始计时时间
	 * @return float
	 */
	function getStarttime()
	{
		return $this->_micro_starttime;
	}
	
	/**
	 * 获取结束计时时间
	 * @return float
	 */
	function getEndtime()
	{
		return $this->_micro_endtime;
	}
	
	/**
	 * 获取计时时间
	 * @return float
	 */
	function getTime()
	{
		return $this->_time;
	}
	
	/**
	 * 获取内存占用量
	 * @return number
	 */
	function getMemory()
	{
		return $this->_memory;
	}
}