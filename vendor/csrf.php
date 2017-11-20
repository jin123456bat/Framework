<?php
namespace framework\vendor;

use framework\core\component;
use framework\core\cookie;
use framework\core\session;
use framework\core\cache;

class csrf extends component
{
	/**
	 * 存储引擎
	 * @var array
	 */
	private static $_storage = array(
		'cookie' => '\\framework\\core\\cookie',
		'session' => '\\framework\\core\\session',
		'cache' => '\\framework\\core\\cache',
	);
	
	private static $_X_CSRF_TOKEN_NAME = 'X_CSRF_TOKEN';

	/**
	 * 创建token
	 */
	static function token()
	{
		$config = self::getConfig('csrf');
		$storage = isset(self::$_storage[$config['storage']])?self::$_storage[$config['storage']]:self::$_storage['cookie'];
		$token = encryption::unique_id('csrf');
		$value = $storage::get(self::$_X_CSRF_TOKEN_NAME);
		if (! empty($value))
		{
			$value = json_decode($value, true);
			$max_token_num = isset($config['max_token_num']) ? intval($config['max_token_num']) : 10;
			if (! empty($max_token_num))
			{
				arsort($value);
				$value = array_slice($value, 0, $max_token_num - 1);
			}
		}
		else
		{
			$value = array();
		}
		
		$value[$token] = time();
		
		if ($config['storage'] == 'cookie')
		{
			$cookie_config = '__csrf';
			if (isset($config['cookie']))
			{
				$cookie_config = $config['cookie'];
			}
			if ($storage::set(self::$_X_CSRF_TOKEN_NAME, json_encode($value), $cookie_config))
			{
				return $token;
			}
			return false;
		}
		else if ($config['storage'] == 'cache')
		{
			$expires = isset($config['cache']['expires'])?$config['cache']['expires']:0;
			if (isset($config['cache']['store']))
			{
				if($storage::store($config['cache']['store'])->set(self::$_X_CSRF_TOKEN_NAME, json_encode($value),$expires))
				{
					return $token;
				}
			}
			else
			{
				if($storage::set(self::$_X_CSRF_TOKEN_NAME, json_encode($value),$expires))
				{
					return $token;
				}
			}
			return false;
		}
		else
		{
			if ($storage::set(self::$_X_CSRF_TOKEN_NAME, json_encode($value)))
			{
				return $token;
			}
			return false;
		}
	}

	/**
	 * 验证token
	 * @param unknown $token
	 * @return boolean
	 */
	static function verify($token)
	{
		$config = self::getConfig('csrf');
		$storage = isset(self::$_storage[$config['storage']])?self::$_storage[$config['storage']]:self::$_storage['cookie'];
		if ($config['storage'] == 'cache')
		{
			if (isset($config['cache']['store']))
			{
				$value = $storage::store($config['cache']['store'])->get(self::$_X_CSRF_TOKEN_NAME);
			}
			else
			{
				$value = $storage::get(self::$_X_CSRF_TOKEN_NAME);
			}
		}
		else
		{
			$value = $storage::get(self::$_X_CSRF_TOKEN_NAME);
		}
		
		if (! empty($value))
		{
			$value = json_decode($value, true);
			if (isset($value[$token]))
			{
				return true;
			}
		}
		return false;
	}
}