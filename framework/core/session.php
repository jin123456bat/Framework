<?php
namespace framework\core;

class session extends component
{
	static private $_session;
	
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
	
	public static function set($name,$value)
	{
		if (empty(self::$_session))
		{
			self::$_session = new session();
			self::$_session->initlize();
		}
		$_SESSION[$name] = $value;
	}
	
	public static function get($name)
	{
		if (empty(self::$_session))
		{
			self::$_session = new session();
			self::$_session->initlize();
		}
		return isset($_SESSION[$name])?$_SESSION[$name]:NULL;
	}
	
	public static function destory()
	{
		if (empty(self::$_session))
		{
			self::$_session = new session();
			self::$_session->initlize();
		}
		return session_destroy();
	}
}