<?php
namespace framework\core;

/**
 * @author fx
 *
 */
class session extends component
{
	static private $_session;
	
	static public $_data = array();
	
	function initlize()
	{
		$session = $this->getConfig('session');
		//配置session
		
		$sessionHandler = application::load('SessionHandler');
		if ($sessionHandler !== NULL)
		{
			session_set_save_handler(
				array($sessionHandler,'open'),
				array($sessionHandler,'close'),
				array($sessionHandler,'read'),
				array($sessionHandler,'write'),
				array($sessionHandler,'destroy'),
				array($sessionHandler,'gc')
			);
			register_shutdown_function('session_write_close');
		}
		
		session_start();
		parent::initlize();
	}
	
	public static function getInstance()
	{
		if (empty(self::$_session))
		{
			self::$_session = new self();
			if (method_exists(self::$_session,'initlize'))
			{
				self::$_session->initlize();
			}
		}
		return self::$_session;
	}
	
	public static function set($name,$value)
	{
		self::getInstance();
		$_SESSION[$name] = $value;
	}
	
	/**
	 * 获取SESSION信息
	 * @param unknown $name
	 * @return NULL|mixed
	 */
	public static function get($name)
	{
		self::getInstance();
		if (isset($_SESSION[$name]))
		{
			return $_SESSION[$name];
		}
		return NULL;
	}
	
	/**
	 * 删除所有session
	 * @return boolean
	 */
	public static function destory()
	{
		session_destroy();
	}
}