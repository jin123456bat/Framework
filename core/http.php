<?php
namespace framework\core;

class http extends base
{

	/**
	 * 创建一个url
	 */
	static function url($c = '', $a = '', array $options = array())
	{
		$default_port = 0;
		if (request::isHttps())
		{
			$default_port = 443;
			$scheme = 'https://';
		}
		else
		{
			$default_port = 80;
			$scheme = 'http://';
		}
		
		if (isset($options['scheme']))
		{
			$scheme = $options['scheme'] . '://';
			unset($options['scheme']);
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
		
		if (! empty($a))
		{
			array_unshift($options, array('a'=>$a));
		}
		if (! empty($c))
		{
			array_unshift($options, array('c'=>$c));
		}
		
		// 判断是否强制使用了url中的session_id
		if (ini_get('session.use_trans_sid') == 1 && ini_get('use_cookies') == 0 && ini_get('use_only_cookies') == 0)
		{
			$session_id = session_id();
			if (empty($session_id))
			{
				application::load(session::class);
				$session_id = session_id();
			}
			$session_id = request::get(session_name(), $session_id, null, 's');
			$options[session_name()] = $session_id;
		}
		$query = ! empty($options) ? '?' . http_build_query($options) : '';
		
		return $scheme . $host . $port . $path . $query . $fragment;
	}

	/**
	 * 发送post请求
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
			$user_agent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.59 Safari/537.36';
			curl_setopt_array($curl, array(
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_CONNECTTIMEOUT => 60,
				CURLOPT_TIMEOUT => 60,
				CURLOPT_POST => true,
				CURLOPT_POSTFIELDS => $data,
				CURLOPT_SSL_VERIFYHOST => 0,
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
				CURLOPT_HTTPHEADER => array(
					'User-Agent: '.$user_agent,
				),
				CURLOPT_USERAGENT => $user_agent,
				CURLINFO_HEADER_OUT => true,
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
	 * @param string $url
	 *        请求的地址
	 * @param array $data
	 *        请求的额外参数 默认是添加到?号后面
	 * @param bool $use_curl
	 *        是否使用curl,默认是不使用
	 */
	static function get($url, array $data = array(), $use_curl = true, $callback = null)
	{
		$url = $url . (!empty($data)?('?' . http_build_query($data)):'');
		if (function_exists('curl_init') && $use_curl)
		{
			$curl = curl_init($url);
			$user_agent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.59 Safari/537.36';
			curl_setopt_array($curl, array(
				CURLOPT_HTTPHEADER => array(
					'User-Agent: '.$user_agent,
				),
				CURLOPT_USERAGENT => $user_agent,
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_CONNECTTIMEOUT => 60,
				CURLOPT_TIMEOUT => 60,
				CURLOPT_POST => false,
				CURLOPT_SSL_VERIFYHOST => false,
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
				CURLINFO_HEADER_OUT => true,
			));
			$response = curl_exec($curl);
			//打印请求头的信息
			//var_dump(curl_getinfo( $curl, CURLINFO_HEADER_OUT));
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
		stream_context_set_default(array(
			'http' => array(
				'method' => 'HEAD'
			)
		));
		$headers = get_headers($url, 1);
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
