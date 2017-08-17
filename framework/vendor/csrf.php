<?php
namespace framework\vendor;

use framework\core\component;
use framework\core\cookie;

class csrf extends component
{

	private static $_X_CSRF_TOKEN_NAME = 'X_CSRF_TOKEN';

	/**
	 * 创建token
	 */
	static function token()
	{
		$token = encryption::unique_id('csrf');
		
		$value = cookie::get(self::$_X_CSRF_TOKEN_NAME);
		if (! empty($value))
		{
			$value = json_decode($value, true);
			
			$config = $this->getConfig('csrf');
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
		
		if (cookie::set(self::$_X_CSRF_TOKEN_NAME, json_encode($value), '__csrf'))
		{
			return $token;
		}
		return false;
	}

	static function verify($token)
	{
		$value = cookie::get(self::$_X_CSRF_TOKEN_NAME);
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