<?php
namespace framework\core;

/**
 * 在页面（ Cookie 可见的页面）下次刷新前，Cookie 不会生效。
 * 测试 Cookie 是否已经成功设置，需要在下次页面加载时、Cookie 过期前检测。
 * 过期时间是通过 expire 参数设置的。 直接调用 print_r($_COOKIE); 调试检测 Cookie 是个很好的方式。
 * 为同一个参数再次设置 Cookie 前，必须先把它删掉。
 * 如果参数的值是空 string 或 FALSE，并且其他参数和上次调用 setcookie 仍旧一样， 则指定的名称会被远程客户端删除。
 * 内部的实现是：将值设置成 'deleted'，并且过期时间是一年前。
 * 因为设置值成 FALSE 会导致 Cookie 被删除，所以要避免使用布尔值。 代替方式：0 是 FALSE，1 是 TRUE。
 * Cookie 名称可以设置成数组名称，PHP 脚本里会是数组， 但用户系统里储存的是单独分开的 Cookie。
 * 可以考虑使用 explode() 为一个 Cookie 设置多个名称和值。
 * 不建议将 serialize() 用于此处，因为它会导致安全漏洞。
 * 
 * @author fx
 */
class cookie extends component
{
	public static $_data = array();
	
	/**
	 * 获取文件上传的配置信息
	 * 配置可以直接写在cookie配置文件中
	 * 配置文件以 key => array(所有相关的配置)的形式
	 * 
	 * @param unknown $config
	 *        配置文件中的key
	 *        假如没有指定key 用有default=true的配置
	 *        假如没有default=true的配置使用第一个
	 *        假如整个就是一个配置 则使用整个配置
	 * @return mixed
	 */
	public static function getConfig($config = null)
	{
		$configs = parent::getConfig('cookie');
		// cookie没有配置过直接返回空数组
		if (empty($configs))
		{
			return array();
		}
		
		// 判断是否整个就是一个大配置
		if ((isset($configs['path']) && is_scalar($configs['path'])) || (isset($configs['domain']) && is_scalar($configs['domain'])) || isset($configs['secure']) || (isset($configs['expire']) && validator::int($configs['expire'])))
		{
			return $configs;
		}
		
		// 判断是否有指定的配置
		if (! empty($config))
		{
			// 假如存在指定配置 直接使用指定配置
			if (isset($configs[$config]))
			{
				return $configs[$config];
			}
		}
		
		// 获取默认的配置
		foreach ($configs as $index => $c)
		{
			if (isset($c['default']) && $c['default'] === true)
			{
				return $c;
			}
		}
		
		// 使用第一个配置
		if (is_array(current($configs)))
		{
			$c = current($configs);
			return $c;
		}
		
		// 以上配置都不对 获取失败
		return array();
	}

	/**
	 *
	 * @param unknown $name        
	 * @param unknown $value
	 *        设置的值 这里必须是一个字符串
	 * @param unknown $config
	 *        配置名称 默认使用第一个配置
	 * @return boolean 成功返回true 失败 false 当失败的时候并不一定代表客户端成功设置了cookie
	 */
	static function set($name, $value, $config = NULL)
	{
		// 获取cookie的配置
		$config = self::getConfig($config);
		
		// 过期时间，默认为0（关掉浏览器） 可以设置一个timestamp 比如time()+60 为1分钟后过期
		$expire = isset($config['expire']) ? intval($config['expire']) : 0;
		$path = isset($config['path']) ? $config['path'] : '/';
		$domain = isset($config['domain']) ? $config['domain'] : $_SERVER['HTTP_HOST'];
		
		if (isset($_SERVER['HTTPS']) && ! empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off')
		{
			$secure = isset($config['secure']) ? $config['secure'] : true;
		}
		else
		{
			$secure = isset($config['secure']) ? $config['secure'] : false;
		}
		
		$httponly = isset($config['httponly']) ? $config['httponly'] : true;
		
		self::$_data[$name] = array(
			$name => $value,
			'path' => $path,
			'expires' => date(DATE_RFC2822,$expire),
			'Max-Age' => $expire - time(),
		);
		
		if (!empty($path))
		{
			self::$_data[$name]['path'] = $path;
		}
		if (!empty($domain))
		{
			self::$_data[$name]['domain'] = $domain;
		}
		if ($httponly)
		{
			self::$_data[$name]['HttpOnly'] = true;
		}
		
		if (request::php_sapi_name() == 'web')
		{
			return setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);
		}
		return true;
	}

	/**
	 * 获取已经设置cookie的值
	 * 
	 * @param unknown $name        
	 * @return NULL|unknown
	 */
	static function get($name)
	{
		return isset($_COOKIE[$name]) ? $_COOKIE[$name] : NULL;
	}

	/**
	 * 删除cookie
	 * 
	 * @param unknown $name        
	 */
	static function delete($name)
	{
		self::$_data[$name] = array(
			$name => 'deleted',
			'expres' => date(DATE_RFC2822,0),
			'Max-Age' => 0,
		);
		
		if (request::php_sapi_name() == 'web')
		{
			setcookie($name, false);
		}
	}
}