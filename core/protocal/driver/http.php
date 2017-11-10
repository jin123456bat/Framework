<?php
namespace framework\core\protocal\driver;
use framework\core\protocal\protocal;
use framework\core\connection;
use framework\core\response;
use framework\core\server;
use framework\core\response\file;

class http implements protocal
{
	/**
	 * @var connection
	 */
	private $_connection;
	
	/**
	 * http状态码以及含义
	 * @var array
	 */
	private static $_http_status = array(
		100 => 'Continue',
		101 => 'Switching Protocols',
		200 => 'OK',
		201 => 'Created',
		202 => 'Accepted',
		203 => 'Non-Authoritative Information',
		204 => 'No Content',
		205 => 'Reset Content',
		206 => 'Partial Content',
		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Found',
		303 => 'See Other',
		304 => 'Not Modified',
		305 => 'Use Proxy',
		306 => '(Unused)',
		307 => 'Temporary Redirect',
		400 => 'Bad Request',
		401 => 'Unauthorized',
		402 => 'Payment Required',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		407 => 'Proxy Authentication Required',
		408 => 'Request Timeout',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',
		412 => 'Precondition Failed',
		413 => 'Request Entity Too Large',
		414 => 'Request-URI Too Long',
		415 => 'Unsupported Media Type',
		416 => 'Requested Range Not Satisfiable',
		417 => 'Expectation Failed',
		422 => 'Unprocessable Entity',
		423 => 'Locked',
		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service Unavailable',
		504 => 'Gateway Timeout',
		505 => 'HTTP Version Not Supported',
	);
	
	/**
	 * 作为http的配置
	 * @var array
	 */
	private $_config = array(
		'DirectoryIndex' => 'index.php index.html',
		'ShowDirectory' => false,
		'DocumentRoot' => './',
	);
	
	/**
	 * $_SERVER
	 * @var array
	 */
	private $_server = array(
		'SERVER_SOFTWARE' => 'framework',
	);
	
	/**
	 * $_GET
	 * @var array
	 */
	private $_get = array();
	
	/**
	 * $_POST
	 * @var array
	 */
	private $_post = array();
	
	/**
	 * $_COOKIE
	 * @var array
	 */
	private $_cookie = array();
	
	/**
	 * $_FILES
	 * @var array
	 */
	private $_files = array();
	
	/**
	 * $_REQUEST
	 * @var array
	 */
	private $_request = array();
	
	/**
	 * $_SESSION
	 * @var array
	 */
	private $_session = array();
	
	/**
	 * {@inheritDoc}
	 * @see \framework\core\protocal\protocal::init()
	 */
	function init($connection)
	{
		$this->_connection = $connection;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \framework\core\protocal\protocal::encode()
	 */
	function encode($string)
	{
		$content = [];
		if (!($string instanceof response))
		{
			$string = new response($string,200);
		}
		
		$content[]  = $this->_server['SERVER_PROTOCOL'].' '.$string->getHttpStatus().' '.self::$_http_status[$string->getHttpStatus()];
		$headers = $string->getHeader()->getAll();
		foreach ($headers as $key => $header)
		{
			$content[] = $key.':'.$header; 
		}
		
		//添加额外的header
		$content[] = 'Date:'.date(DATE_RFC2822);
		$content[] = 'X-Powered-By:PHP/'.phpversion();
		$content[] = 'Connection: keep-alive';
		
		$body = $string->getBody();
		
		//编码压缩
		if (function_exists('zlib_encode'))
		{
			if (isset($this->_server['ACCEPT-ENCODING']))
			{
				$encoding = explode(',', trim($this->_server['ACCEPT-ENCODING']));
				$encoding = array_map(function($encode){
					return strtolower(trim($encode));
				},$encoding);
				$encode = '';
				$encode_string = '';
				if (in_array('gzip', $encoding))
				{
					$encode = ZLIB_ENCODING_GZIP;
					$encode_string = 'gzip';
				}
				else if (in_array('deflate', $encoding))
				{
					$encode = ZLIB_ENCODING_DEFLATE;
					$encode_string = 'deflate';
				}
				if (!empty($encode) && !empty($encode_string))
				{
					$body = zlib_encode($body,$encode);
					$content[] = 'Content-Encoding:'.$encode_string;
				}
			}
		}
		
		$content[] = '';
		$content[] = $body;
		
		return implode("\r\n", $content);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \framework\core\protocal\protocal::decode()
	 */
	function decode($request)
	{
		$connection = $this->_connection;
		
		//重置变量
		$this->_server = array();
		$this->_get = array();
		$this->_post = array();
		$this->_cookie = array();
		$this->_files = array();
		$this->_env = array();
		$this->_request = array();
		$this->_session = array();
		
		$this->_server['REQUEST_TIME'] = time();
		$this->_server['REQUEST_TIME_FLOAT'] = microtime(true);
		$this->_server['QUERY_STRING'] = '';
		
		socket_getpeername($connection->getSocket(),$this->_server['REMOTE_ADDR'],$this->_server['REMOTE_PORT']);
		
		$header = explode("\r\n", substr($request,0, strpos($request, "\r\n\r\n")));
		
		$head = array_shift($header);
		list($method,$path,$version) = explode(' ', $head,3);
		$method = trim($method);
		$path = trim($path);
		$version = trim($version);
		
		$this->_server['SERVER_PROTOCOL'] = $version;
		$this->_server['REQUEST_METHOD'] = $method;
		$this->_server['REQUEST_URI'] = $path;
		$this->_server['SCRIPT_NAME'] = parse_url($path, PHP_URL_PATH);
		
		//get
		$this->_server['QUERY_STRING'] = parse_url($path,PHP_URL_QUERY);
		if (!empty($this->_server['QUERY_STRING']))
		{
			parse_str($this->_server['QUERY_STRING'],$this->_get);
		}
		
		if (!in_array(strtolower($method), array('get','post','head','put','delete','trace','options')))
		{
			$connection->write(new response('<h1>错误的请求</h1>',400));
			return false;
		}
		
		//处理其他的请求头
		foreach ($header as $head)
		{
			if (!empty($head))
			{
				list($name,$value) = explode(':', $head,2);
				$name = strtolower(trim($name));
				if (!in_array($name, array(
					'cookie'
				)))
				{
					$this->_server['HTTP_'.strtoupper(str_replace('-', '_', $name))] = trim($value);
				}
				else
				{
					switch ($name)
					{
						case 'cookie':
							//处理cookie的header
							parse_str(str_replace('; ', '&', $value), $this->_cookie);
							break;
					}
				}
			}
		}
		
		if (strtolower($this->_server['REQUEST_METHOD']) == 'post')
		{
			//剩下的就是body了
			$body = substr($request,strpos($request, "\r\n\r\n")+4);
			$GLOBALS['HTTP_RAW_REQUEST_DATA'] = $GLOBALS['HTTP_RAW_POST_DATA'] = $body;
			
			//body的长度校验
			if (isset($this->_server['HTTP_CONTENT_LENGTH']))
			{
				if ($this->_server['HTTP_CONTENT_LENGTH'] < strlen($body))
				{
					$body = substr($body, 0,$this->_server['HTTP_CONTENT_LENGTH']);
				}
				else if ($this->_server['HTTP_CONTENT_LENGTH'] > strlen($body))
				{
					//等待发送  尚未完成
					return false;
				}
			}
			
			//默认的编码方式
			$this_content_type = 'application/x-www-form-urlencoded';
			foreach(explode(';', $this->_server['HTTP_CONTENT_TYPE']) as $content_type)
			{
				$content_type = strtolower(trim($content_type));
				if ($content_type == 'multipart/form-data')
				{
					$this_content_type = 'multipart/form-data';
					break;
				}
				else if ($content_type == 'application/x-www-form-urlencoded')
				{
					$this_content_type = 'application/x-www-form-urlencoded';
					break;
				}
			}
			
			if ($this_content_type == 'application/x-www-form-urlencoded')
			{
				//x-www-form-urlencode
				//$body = urldecode($body);
				parse_str($body,$this->_post);
			}
			else if ($this_content_type == 'multipart/form-data')
			{
				if(preg_match('/boundary=(?<boundary>.+)/', $this->_server['HTTP_CONTENT_TYPE'],$match))
				{
					$boundary = $match['boundary'];
				}
				
				foreach(explode('--'.$boundary, $body) as $split)
				{
					if (empty($split) || $split == '--')
					{
						continue;
					}
					//提取头
					$head = trim(substr($split, 0,strpos($split, "\r\n\r\n")));
					//提取值
					$content = substr($split, strpos($split, "\r\n\r\n")+4,-2);
					
					if (!empty($content))
					{
						if (preg_match('/Content-Disposition: form-data; name="(?<name>[^"]*)"; filename="(?<filename>[^"]*)"/i', $head,$match))
						{
							preg_match('/Content-Type: (?<type>[^\r\n]*)/i', $head,$type);
							$temp_file = tempnam(sys_get_temp_dir(), 'php');
							file_put_contents($temp_file, $content);
							//文件
							//多文件上传看来必须文件名不能一样
							$this->_files[$match['name']] = array(
								'name' => $match['filename'],
								'size' => strlen($content),
								'type' => isset($type['type'])?$type['type']:'',
								'error' => UPLOAD_ERR_OK,
								'tmp_name' => $temp_file,
							);
						}
						else if (preg_match('/Content-Disposition:\s*form-data;\s*name="(?<name>[^"]*)"/i', $split,$match))
						{
							$this->_post[$match['name']] = $content;
						}
					}
				}
			}
		}
		
		$this->_request = array_merge($this->_get,$this->_post);
		
		//一下是判断解析文件
		if (!empty($this->_server['SCRIPT_NAME']))
		{
			$path = rtrim($this->_config['DocumentRoot'],'/').ltrim($this->_server['SCRIPT_NAME']);
			if (is_dir($path))
			{
				$index_file_path = '';
				
				$index_files = explode(' ', $this->_config['DirectoryIndex']);
				foreach ($index_files as $index)
				{
					$file = rtrim($path).'/'.trim($index,' ');
					if (file_exists($file) && is_file($file) && is_readable($file))
					{
						$index_file_path = $file;
						break;
					}
				}
				
				if (empty($index_file_path))
				{
					if ($this->_config['ShowDirectory'])
					{
						//这里读取目录内容来处理
					}
					else
					{
						$this->_connection->write(new response('<h1>forbidden</h1>',403));
					}
				}
				else
				{
					$path = $index_file_path;
				}
				//这里必须返回空，不为空的话会把控制权交给router
			}
			
			if (is_file($path))
			{
				//假如请求的文件和当前执行的文件是同一个文件
				if(realpath($path) == realpath(APP_ROOT.'/'.$_ENV['PHP_SELF']))
				{
					//交给router来处理接下来的流程
					return $request;
				}
				else if (is_readable($path))
				{
					//这里读取文件内容来处理
					$this->_connection->write(new file($path));
					//这里必须返回空，不为空的话会把控制权交给router
				}
				else 
				{
					$this->_connection->write(new response('<h1>forbidden</h1>',403));
				}
			}
			else
			{
				$this->_connection->write(new response('<h1>not found</h1>',404));
			}
		}
	}
	
	/**
	 * http下的session目前还有几个限制
	 * 1、session.use_cookie必须=1
	 * 2、session.name必须设置
	 * 3、session.save_handler必须是files
	 * 4、session.save_path不能设置深度参数
	 * 5、session.serialize_handler必须是php不能是wddx
	 * {@inheritDoc}
	 * @see \framework\core\protocal\protocal::parse()
	 */
	public function parse($string)
	{
		//解析session
		//session存储在cookie当中
		if(ini_get('session.use_cookies'))
		{
			$session_name = ini_get('session.name');
			if (isset($this->_cookie[$session_name]))
			{
				$key = $this->_cookie[$session_name];
				
				//使用文件存储session
				if (ini_get('session.save_handler') == 'files')
				{
					//这里不考虑深度参数 有关深度参数可以参考http://php.net/manual/zh/session.configuration.php
					$path = ini_get('session.save_path');
					$file = rtrim($path,'/').'/sess_'.$key;
					if (file_exists($file) && is_readable($file))
					{
						if (ini_get('session.serialize_handler') == 'php')
						{
							$this->_session = unserialize(file_get_contents($file));
						}
					}
				}
				
			}
		}
		
		return array(
			'_GET' => $this->_get,
			'_POST'=> $this->_post,
			'_COOKIE' => $this->_cookie,
			'_SERVER' => $this->_server,
			'_FILES' => $this->_files,
			'_REQUEST' => $this->_request,
			'_SESSION' => $this->_session,
		);
	}
	
	/**
	 * 发送完数据后是否需要关闭连接
	 * @return boolean
	 */
	public function closeAfterWrite()
	{
		return true;
	}
}