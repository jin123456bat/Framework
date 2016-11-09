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
		
		if (request::php_sapi_name()=='web')
		{
			session_start();
		}
		//每次请求重新生成session_id，防止session_id暴力破解
		//这里有个问题，假如当前请求还没有返回的话，直接发送第二次请求，会导致第二次请求带上旧的cookie，而旧的cookie已经被删除掉了，这样子的话直接判断为尚未登陆状态
		//假如为false的话，会导致session在尚未gc之前有大量的session_id
		//session_regenerate_id(false);
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