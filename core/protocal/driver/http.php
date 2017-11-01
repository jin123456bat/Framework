<?php
namespace framework\core\protocal\driver;
use framework\core\protocal\protocal;
use framework\core\connection;

class http implements protocal
{
	/**
	 * {@inheritDoc}
	 * @see \framework\core\protocal\protocal::init()
	 */
	function init($request,$connection)
	{
		$_SERVER['REQUEST_TIME'] = time();
		$_SERVER['SERVER_PROTOCOL'] = $request[2];
		$_SERVER['REQUEST_TIME_FLOAT'] = microtime(true);
		
		$request = explode("\n", $request);
		
		$head = explode(' ', array_shift($request));
		$_SERVER['REQUEST_METHOD'] = $head[0];
		if (strpos($head[1], '?'))
		{
			$_SERVER['QUERY_STRING'] = substr($head[1], strpos($head[1]+1, '?'));
			$_SERVER['PHP_SELF'] = substr($head[1], 0,strpos($head[1], '?'));
			
			foreach (explode('&', $_SERVER['QUERY_STRING']) as $q)
			{
				list($k,$v) = explode('=', $q);
				$_GET[trim($k)] = trim($v);
			}
		}
		else if (strpos($head[1], '.php'))
		{
			$_SERVER['QUERY_STRING'] = substr($head[1], strpos($head[1]+4, '.php'));
			$_SERVER['PHP_SELF'] = substr($head[1], 0,strpos($head[1], '.php'));
			
			$queryString = explode('/', $_SERVER['QUERY_STRING']);
			if (isset($queryString[0]) && !empty($queryString[0]))
			{
				$_GET['c'] = $queryString[0];
			}
			
			if (isset($queryString[1]) && !empty($queryString[1]))
			{
				$_GET['a'] = $queryString[1];
			}
			
			for ($i=2;$i<count($queryString);$i+=2)
			{
				if (isset($queryString[$i+1]))
				{
					$_GET[$queryString[$i]] = $queryString[$i+1];
				}
			}
		}
		
		foreach ($request as $req)
		{
			list($name,$value) = sscanf($req, "%s:%s");
			if (!in_array(strtolower($name), array(
				'cookie'
			)))
			{
				$_SERVER['HTTP_'.strtoupper(str_replace('-', '_', $name))] = $value;
			}
			else
			{
				switch (strtolower($name))
				{
					case 'cookie':
						foreach (explode(';', $value) as $c)
						{
							list($k,$v) = explode('=', $c);
							$_COOKIE[trim($k)] = trim($v);
						}
					break;
				}
			}
		}
		$_SERVER['HTTPS'] = 'off';
		$_SERVER['REMOTE_ADDR'] = '0.0.0.0';//用户的ip地址  这个貌似获取不到啊
	}
	
	/**
	 * {@inheritDoc}
	 * @see \framework\core\protocal\protocal::encode()
	 */
	function encode($string)
	{
		return $string;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \framework\core\protocal\protocal::decode()
	 */
	function decode($buffer)
	{
		return $buffer;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \framework\core\protocal\protocal::get()
	 */
	function get($string)
	{
		return $_GET;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \framework\core\protocal\protocal::post()
	 */
	function post($string)
	{
		
	}
	
	/**
	 * {@inheritDoc}
	 * @see \framework\core\protocal\protocal::cookie()
	 */
	function cookie($string)
	{
		return $_COOKIE;
	}
}