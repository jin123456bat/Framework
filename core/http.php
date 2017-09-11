<?php
namespace framework\core;

class http extends base
{

	/**
	 * 创建一个url
	 */
	static function url($c, $a, array $options = array())
	{
		if (request::isHttps())
		{
			$scheme = 'https://';
			$default_port = 443;
		}
		else
		{
			$scheme = 'http://';
			$default_port = 80;
		}
		
		if (isset($options['scheme']))
		{
			$scheme = $options['scheme'] . '://';
			unset($options['scheme']);
			$default_port = 0;
		}
		
		$host = $_SERVER['HTTP_HOST'];
		if (isset($options['host']))
		{
			$host = $options['host'];
			unset($options['host']);
		}
		
		$port = $_SERVER['SERVER_PORT'];
		if (isset($options['port']))
		{
			$port = $options['port'];
			unset($options['port']);
		}
		if (($port == 80 && $default_port == 80) || ($port == 443 && $default_port == 443))
		{
			$port = '';
		}
		else
		{
			$port = ':' . $port;
		}
		
		$path = $_SERVER['SCRIPT_NAME'];
		if (isset($options['path']))
		{
			$path = '/' . ltrim($options['path'], '/');
			unset($options['path']);
		}
		
		$fragment = '';
		if (isset($options['fragment']))
		{
			$fragment = '#' . $options['fragment'];
			unset($options['fragment']);
		}
		
		$options['c'] = $c;
		$options['a'] = $a;
		$query = '?' . http_build_query($options);
		
		return $scheme . $host . $port . $path . $query . $fragment;
	}

	/**
	 * 发送post请求
	 * 
	 * @param unknown $url        
	 * @param unknown $data        
	 */
	static function post($url, $data = array())
	{
		if (function_exists('curl_init'))
		{
			if (is_array($data))
			{
				foreach ($data as $index => &$file)
				{
					if (isset($file[0]) && $file[0] == '@')
					{
						// mb php5.5不支持@
						if (class_exists('\CURLFile', true))
						{
							$file = new \CURLFile(substr($file, 1));
						}
					}
				}
			}
			$curl = curl_init();
			curl_setopt_array($curl, array(
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_CONNECTTIMEOUT => 60,
				CURLOPT_TIMEOUT => 60,
				CURLOPT_POST => true,
				CURLOPT_POSTFIELDS => $data,
				CURLOPT_SSL_VERIFYHOST => 0,
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4
			));
			$response = curl_exec($curl);
			curl_close($curl);
			return $response;
		}
		else
		{
			if (is_array($data))
			{
				$data = http_build_query($data);
			}
			$context = array(
				'http' => array(
					'method' => "POST",
					'header' => "Content-type: application/x-www-form-urlencoded\r\n" . "Content-length:" . strlen($data) . "\r\n",
					'content' => $data
				)
			);
			$context = stream_context_create($context);
			return file_get_contents($url, null, $context);
		}
	}

	/**
	 * 发送get请求
	 * 
	 * @param string $url
	 *        请求的地址
	 * @param array $data
	 *        请求的额外参数 默认是添加到?号后面
	 * @param bool $use_curl
	 *        是否使用curl,默认是不使用
	 */
	static function get($url, array $data = array(), $use_curl = true, $callback = null)
	{
		$url = $url . '?' . http_build_query($data);
		if (function_exists('curl_init') && $use_curl)
		{
			$curl = curl_init($url);
			curl_setopt_array($curl, array(
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_CONNECTTIMEOUT => 60,
				CURLOPT_TIMEOUT => 60,
				CURLOPT_POST => false,
				CURLOPT_SSL_VERIFYHOST => false,
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4
			));
			$response = curl_exec($curl);
			curl_close($curl);
			return $response;
		}
		else
		{
			$context = stream_context_create(array(
				'http' => array(
					'method' => 'GET',
					'timeout' => 60,
					'follow_location' => 1,
					'max_redirects' => 3,
					'ignore_errors' => true
				)
			));
			if (! empty($callback) && is_callable($callback))
			{
				stream_context_set_params($context, array(
					'notification' => $callback
				));
			}
			$result = file_get_contents($url, false, $context);
			return $result;
		}
	}
	
	/**
	 * head请求
	 * @param string $url
	 */
	static function head($url)
	{
		$context = stream_context_get_options(stream_context_get_default());
		stream_context_set_default(array('http'=>array('method'=>'HEAD')));
		$headers = get_headers($url,1);
		stream_context_set_default($context);
		return $headers;
	}
	
	/**
	 * put请求
	 * @param string $url
	 */
	static function put($url)
	{
		
	}
	
	/**
	 * delete请求
	 * @param string $url
	 */
	static function delete($url)
	{
		
	}
	
	/**
	 * options请求
	 * @param string $url
	 */
	static function options($url)
	{
		
	}
	
	/**
	 * trace请求
	 * @param string $url
	 */
	static function trace($url)
	{
		
	}
}