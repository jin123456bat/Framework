<?php
namespace framework\core\protocal\driver;
use framework\core\protocal\protocal;
use framework\core\connection;
use framework\core\console;
use framework\core\response;

class http implements protocal
{
	private static $_mime_type = array(
		'shtml' => 'text/html',
		'html' => 'text/html',
		'htm' => 'text/html',
		'css' => 'text/css',
		'xml' => 'text/xml',
		'gif' => 'image/gif',
		'jpeg' => 'image/jpeg',
		'jpg' => 'image/jpeg',
		'js' => 'application/x-javascript',
		'atom' => 'application/atom+xml',
		'rss' => 'application/rss+xml',
		'mml' => 'text/mathml',
		'txt' => 'text/plain',
		'jad' => 'text/vnd.sun.j2me.app-descriptor',
		'wml' => 'text/vnd.wap.wml',
		'htc' => 'text/x-component',
		'png' => 'image/png',
		'tif' => 'image/tiff',
		'tiff' => 'image/tiff',
		'wbmp' => 'image/vnd.wap.wbmp',
		'ico' => 'image/x-icon',
		'jng' => 'image/x-jng',
		'bmp' => 'image/x-ms-bmp',
		'svg' => 'image/svg+xml',
		'svgz' => 'image/svg+xml',
		'webp' => 'image/webp',
		'jar' => 'application/java-archive',
		'war' => 'application/java-archive',
		'ear' => 'application/java-archive',
		'hqx' => 'application/mac-binhex40',
		'doc' => 'application/msword',
		'pdf' => 'application/pdf',
		'ps' => 'application/postscript',
		'eps' => 'application/postscript',
		'ai' => 'application/postscript',
		'rtf' => 'application/rtf',
		'xls' => 'application/vnd.ms-excel',
		'ppt' => 'application/vnd.ms-powerpoint',
		'wmlc' => 'application/vnd.wap.wmlc',
		'kml' => 'application/vnd.google-earth.kml+xml',
		'kmz' => 'application/vnd.google-earth.kmz',
		'7z' => 'application/x-7z-compressed',
		'cco' => 'application/x-cocoa',
		'jardiff' => 'application/x-java-archive-diff',
		'jnlp' => 'application/x-java-jnlp-file',
		'run' => 'application/x-makeself',
		'pl' => 'application/x-perl',
		'pm' => 'application/x-perl',
		'prc' => 'application/x-pilot',
		'pdb' => 'application/x-pilot',
		'rar' => 'application/x-rar-compressed',
		'rpm' => 'application/x-redhat-package-manager',
		'sea' => 'application/x-sea',
		'swf' => 'application/x-shockwave-flash',
		'sit' => 'application/x-stuffit',
		'tcl' => 'application/x-tcl',
		'tk' => 'application/x-tcl',
		'der' => 'application/x-x509-ca-cert',
		'pem' => 'application/x-x509-ca-cert',
		'crt' => 'application/x-x509-ca-cert',
		'xpi' => 'application/x-xpinstall',
		'xhtml' => 'application/xhtml+xml',
		'zip' => 'application/zip',
		'bin' => 'application/octet-stream',
		'exe' => 'application/octet-stream',
		'dll' => 'application/octet-stream',
		'deb' => 'application/octet-stream',
		'dmg' => 'application/octet-stream',
		'eot' => 'application/octet-stream',
		'iso' => 'application/octet-stream',
		'img' => 'application/octet-stream',
		'msi' => 'application/octet-stream',
		'msp' => 'application/octet-stream',
		'msm' => 'application/octet-stream',
		'mid' => 'audio/midi',
		'midi' => 'audio/midi',
		'kar' => 'audio/midi',
		'mp3' => 'audio/mpeg',
		'ogg' => 'audio/ogg',
		'm4a' => 'audio/x-m4a',
		'ra' => 'audio/x-realaudio',
		'3gpp' => 'video/3gpp',
		'3gp' => 'video/3gpp',
		'mp4' => 'video/mp4',
		'mpeg' => 'video/mpeg',
		'mpg' => 'video/mpeg',
		'mov' => 'video/quicktime',
		'webm' => 'video/webm',
		'flv' => 'video/x-flv',
		'm4v' => 'video/x-m4v',
		'mng' => 'video/x-mng',
		'asx' => 'video/x-ms-asf',
		'asf' => 'video/x-ms-asf',
		'wmv' => 'video/x-ms-wmv',
		'avi' => 'video/x-msvideo',
	);
	
	/**
	 * 其他额外响应的header
	 * @var array
	 */
	private $_header = array(
		
	);
	
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
	 * {@inheritDoc}
	 * @see \framework\core\protocal\protocal::init()
	 */
	function init($request,$connection)
	{
		$this->_server['REQUEST_TIME'] = time();
		$this->_server['REQUEST_TIME_FLOAT'] = microtime(true);
		
		$request = substr($request, strpos($request, "\n"));
		list($method,$path,$version) = explode(' ', $request);
		$method = trim($method);
		$path = trim($path);
		$version = trim($version);
		
		$this->_server['SERVER_PROTOCOL'] = $version;
		$this->_server['REQUEST_METHOD'] = $method;
		$this->_server['REQUEST_URI'] = $path;
		$this->_server['QUERY_STRING'] = '';
		
		if (!in_array(strtolower($method), array('get','post','head','put','delete','trace','options')))
		{
			$connection->send(new response('<h1>错误的请求</h1>',400));
			return false;
		}
		var_dump($this->_server);
		exit();
		//一个http请求中 以一个单行的\r\n为结束 所以只要碰见了一次空行 就认为header解析完成了
		$header_end = false;
		foreach ($request as $req)
		{
			if (!empty($req))
			{
				if (!$header_end)
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
								//处理cookie的header
								foreach (explode(';', $value) as $c)
								{
									list($k,$v) = explode('=', $c);
									$_COOKIE[trim($k)] = trim($v);
								}
								break;
						}
					}
				}
				else if (strtolower($_SERVER['REQUEST_METHOD']) == 'post')
				{
					//这里解释body  但是目前只支持urlencode的方式
					foreach(explode('&', $req) as $r)
					{
						if (!empty($r))
						{
							list($k,$v) = explode('=', $r);
							$_POST[trim($k)] = trim($v);
						}
					}
				}
			}
			else
			{
				$header_end = true;
			}
		}
		//https暂时还不支持
		$_SERVER['HTTPS'] = 'off';
		socket_getpeername($connection->getSocket(),$_SERVER['REMOTE_ADDR'],$_SERVER['REMOTE_PORT']);
		//处理path
		if (strpos($path, '?'))
		{
			$_SERVER['QUERY_STRING'] = substr($path, strpos($path, '?')+1);
			$request_path = '.'.substr($path, 0,strpos($path, '?'));
			if (file_exists($request_path))
			{
				if (is_dir($request_path))
				{
					foreach(array_filter(explode(' ', $this->_config['DirectoryIndex'])) as $index_file_name)
					{
						$index_file_name = rtrim($request_path,'/').'/'.$index_file_name;
						if (is_file($index_file_name))
						{
							$_SERVER['SCRIPT_NAME'] = $index_file_name;
							break;
						}
					}
				}
				else
				{
					if (is_file($request_path))
					{
						$_SERVER['SCRIPT_NAME'] = $request_path;
					}
				}
			}
			//这里就不考虑其他的执行程序
			if (!empty($_SERVER['SCRIPT_NAME']))
			{
				if (is_file($_SERVER['SCRIPT_NAME']))
				{
					//获取后缀名称
					$extension = substr($_SERVER['SCRIPT_NAME'], strrpos($_SERVER['SCRIPT_NAME'], '.')+1);
					if (empty($extension))
					{
						$extension = 'html';
					}
					if ($extension === 'php')
					{
						foreach (explode('&', $_SERVER['QUERY_STRING']) as $q)
						{
							if (!empty($q))
							{
								list($k,$v) = explode('=', $q);
								$_GET[trim($k)] = trim($v);
							}
						}
						$_SERVER['PHP_SELF'] = $_SERVER['SCRIPT_NAME'];
						//交给php来处理接下来的数据
						return true;
					}
					else
					{
						//处理文件请求
						self::$_header[] = 'Content-Type:'.self::$_mime_type[$extension];
						$connection->send(file_get_contents($_SERVER['SCRIPT_NAME']));
						return false;
					}
				}
				else if (is_dir($_SERVER['SCRIPT_NAME']))
				{
					//这里考虑输出目录
				}
			}
			else 
			{
				//这里应该返回404
				$connection->send(new response('<h1>404</h1>',404));
				return false;
			}
		}
		else if (strpos($path,'.php'))
		{
			//处理pathinfo请求
			$_SERVER['QUERY_STRING'] = substr($path, strpos($path, '.php')+4);
			$_SERVER['PHP_SELF'] = substr($path, 0,strpos($path, '.php')+4);
			
			
			
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
			//交给php处理
			return true;
		}
		else
		{
			//处理直接文件请求
			$filepath = '.'.$path;
			if (file_exists($filepath))
			{
				if (is_dir($filepath))
				{
					
				}
				else if (is_file($filepath))
				{
					//获取后缀名称
					$extension = substr($filepath, strrpos($filepath, '.')+1);
					if (empty($extension))
					{
						$extension = 'html';
					}
					//处理文件请求
					self::$_header[] = 'Content-Type:'.self::$_mime_type[$extension];
					$connection->send(file_get_contents($filepath));
					return false;
				}
			}
			else
			{
				//返回404
				return false;
			}
		}
	}
	
	/**
	 * {@inheritDoc}
	 * @see \framework\core\protocal\protocal::encode()
	 */
	function encode($string)
	{
		$content = [];
		if (is_string($string))
		{
			$string = new response($string,200);
		}
		if ($string instanceof response)
		{
			$content[]  = $this->_server['SERVER_PROTOCOL'].' '.$string->getHttpStatus().' '.self::$_http_status[$string->getHttpStatus()];
			$headers = $string->getHeader()->getAll();
			foreach ($headers as $key => $header)
			{
				$content[] = $key.':'.$header;
			}
		}
		
		//添加额外的header
		$content[] = 'Date:'.date(DATE_RFC2822);
		$content[] = 'X-Powered-By:PHP/'.phpversion();
		
		$content[] = '';
		
		$content[] = $string->getBody();
		
		return implode("\r\n", $content);
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
		return $this->_get;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \framework\core\protocal\protocal::post()
	 */
	function post($string)
	{
		return $this->_post;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \framework\core\protocal\protocal::cookie()
	 */
	function cookie($string)
	{
		return $_COOKIE;
	}
	/**
	 * {@inheritDoc}
	 * @see \framework\core\protocal\protocal::server()
	 */
	public function server($buffer)
	{
		// TODO Auto-generated method stub
		return $this->_server;
	}

	/**
	 * {@inheritDoc}
	 * @see \framework\core\protocal\protocal::files()
	 */
	public function files($buffer)
	{
		// TODO Auto-generated method stub
		
	}

	/**
	 * {@inheritDoc}
	 * @see \framework\core\protocal\protocal::request()
	 */
	public function request($buffer)
	{
		// TODO Auto-generated method stub
		
	}

	/**
	 * {@inheritDoc}
	 * @see \framework\core\protocal\protocal::env()
	 */
	public function env($buffer)
	{
		// TODO Auto-generated method stub
		
	}

	/**
	 * {@inheritDoc}
	 * @see \framework\core\protocal\protocal::session()
	 */
	public function session($buffer)
	{
		// TODO Auto-generated method stub
		
	}

}