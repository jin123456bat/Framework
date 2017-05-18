<?php
namespace framework\core;

/**
 *
 * @author fx
 *        
 */
class session extends component
{

	private static $_session;

	public static $_data = array();

	function initlize()
	{
		$session = $this->getConfig('session');
		
		//假如用户定义了SessionHandler
		if (isset($session['handler']) && !empty($session['handler']))
		{
			$sessionHandler = application::load($session['handler']);
			if ($sessionHandler !== null)
			{
				if (!$sessionHandler instanceof \SessionHandlerInterface)
				{
					session_set_save_handler(array(
						$sessionHandler,
						'open'
					), array(
						$sessionHandler,
						'close'
					), array(
						$sessionHandler,
						'read'
					), array(
						$sessionHandler,
						'write'
					), array(
						$sessionHandler,
						'destroy'
					), array(
						$sessionHandler,
						'gc'
					));
					register_shutdown_function('session_write_close');
				}
				else
				{
					session_set_save_handler($sessionHandler,true);
					register_shutdown_function('session_write_close');
				}
				
			}
		}
		
		if (request::php_sapi_name() == 'web')
		{
			session_start();
		}
		// 每次请求重新生成session_id，防止session_id暴力破解
		// 这里有个问题，假如当前请求还没有返回的话，直接发送第二次请求，会导致第二次请求带上旧的cookie，而旧的cookie已经被删除掉了，这样子的话直接判断为尚未登陆状态
		// 假如为false的话，会导致session在尚未gc之前有大量的session_id
		// session_regenerate_id(false);
		parent::initlize();
	}

	public static function getInstance()
	{
		if (empty(self::$_session))
		{
			self::$_session = new self();
			if (method_exists(self::$_session, 'initlize'))
			{
				self::$_session->initlize();
			}
		}
		return self::$_session;
	}

	/**
	 * 判断一个session是否存在
	 *
	 * @param unknown $name        	
	 */
	public static function has($name)
	{
		self::getInstance();
		return isset($_SESSION[$name]);
	}

	/**
	 * 设置一个session,已经存在的会被覆盖
	 *
	 * @param unknown $name        	
	 * @param unknown $value        	
	 */
	public static function set($name, $value)
	{
		self::getInstance();
		$_SESSION[$name] = $value;
	}

	/**
	 * 获取一个session变量，不存在的话返回NULL
	 *
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
		return null;
	}

	/**
	 * 删除一个session变量
	 *
	 * @param unknown $name        	
	 */
	public static function delete($name)
	{
		self::getInstance();
		unset($_SESSION[$name]);
	}

	/**
	 * 删除所有session
	 *
	 * @return boolean
	 */
	public static function destory()
	{
		session_destroy();
	}
}
