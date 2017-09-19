<?php
namespace framework\vendor;

use lib\error;
use framework\core\validator;
use framework\core\http;
use framework\core\base;

/**
 * 文件基类，包含了文件信息和文件操作
 * 
 * @author fx
 */
class file extends base
{

	/**
	 * 文件上次访问时间的时间戳
	 * @var int
	 */
	private $_atime;

	/**
	 * 文件上次修改时间的时间戳
	 * @var int
	 */
	private $_mtime;

	/**
	 * 文件上次改变时间的时间戳
	 * @var int
	 */
	private $_ctime;

	/**
	 * 文件大小  字节数
	 * @var int
	 */
	private $_size;

	/**
	 * 文件存储的目录，不包含最后一个分割符
	 * @var unknown
	 */
	private $_dirname;

	/**
	 * 完整文件名 包含后缀 不包含目录
	 * @var string
	 */
	private $_basename;

	/**
	 * 不包含后缀的文件名
	 * @var string
	 */
	private $_filename;

	/**
	 * 扩展名 包括.
	 * @var unknown
	 */
	private $_extension;

	private $_mimeType;

	private $_type;

	/**
	 * 文件完整路径
	 * @var unknown
	 */
	private $_path;

	private $_resource;
	
	/**
	 * 文件是否存在
	 * @var boolean
	 */
	private $_exist;
	
	/**
	 * 是否是url文件
	 * @var boolean
	 */
	private $_is_url;
	
	/**
	 * 文件是否可读
	 * @var boolean
	 */
	private $_readable;
	
	/**
	 * 文件是否可写
	 * @var boolean
	 */
	private $_writable;

	function __construct($file)
	{
		$this->_path = $file;
		$this->_exist = file_exists($this->_path);
		// 加锁
		if ($this->_exist)
		{
			$this->_resource = fopen($this->_path, 'a+');
			flock($this->_resource, LOCK_EX);
		}
		
		$this->parse();
	}

	function __destruct()
	{
		// 解锁
		if ($this->_resource)
		{
			flock($this->_resource, LOCK_UN);
		}
	}

	public function initlize()
	{
		parent::initlize();
	}

	/**
	 * 文件解析函数
	 */
	private function parse()
	{
		if (! empty($this->_path))
		{
			// 清空文件缓存
			clearstatcache();
			$this->_is_url = validator::url($this->_path);
			if ($this->_is_url)
			{
				$header = http::head($this->_path);
				$this->_mimeType = isset($header['Content-Type'])?$header['Content-Type']:'';
				
				$url = parse_url($this->_path);
				$pathinfo = pathinfo($url['path']);
				$this->_extension = $pathinfo['extension'];
				$this->_filename = $pathinfo['filename'];
				$this->_basename = $pathinfo['basename'];
				$this->_dirname = $url['scheme'].'://'.$url['host'].$pathinfo['dirname'];
				
				$this->_size = isset($header['Content-Length'])?$header['Content-Length']:0;
				$this->_mtime = isset($header['Last-Modified'])?strtotime($header['Last-Modified']):0;
				$this->_atime = isset($header['Date'])?strtotime($header['Date']):0;
				$this->_ctime = 0;
				
				$http_status = explode(' ', $header[0]);
				if ($http_status[1] == 200)
				{
					$this->_readable = true;
				}
				
				$this->_writable = false;
			}
			else
			{
				$this->_mimeType = finfo_file(finfo_open(FILEINFO_MIME_TYPE, null), $this->_path);
				
				$pathinfo = pathinfo($this->_path);
				$this->_extension = $pathinfo['extension'];
				$this->_filename = $pathinfo['filename'];
				$this->_basename = $pathinfo['basename'];
				$this->_dirname = $pathinfo['dirname'];
				
				$fstat = stat($this->_path);
				$this->_atime = $fstat['atime'];
				$this->_ctime = $fstat['ctime'];
				$this->_mtime = $fstat['mtime'];
				$this->_size = $fstat['size'];
				
				$this->_readable = is_readable($this->_path);
				$this->_writable = is_writable($this->_path);
			}
		}
	}

	/**
	 * 假如有错误返回错误信息，否则返回NULL
	 */
	function error()
	{
		return $this->_error;
	}

	/**
	 * 上次改变时间  对于url文件可能是0
	 * 
	 * @return int 时间戳
	 */
	function ctime()
	{
		return $this->_ctime;
	}

	/**
	 * 上次访问时间 对于url文件可能是0
	 * 
	 * @return int 时间戳
	 */
	function atime()
	{
		return $this->_atime;
	}

	/**
	 * 上次修改时间 对于url文件可能是0
	 * 
	 * @return int 时间戳
	 */
	function mtime()
	{
		return $this->_mtime;
	}

	/**
	 * 获取文件大小
	 */
	function size()
	{
		return $this->_size;
	}

	/**
	 * 获取文件内容
	 */
	function content()
	{
		if ($this->readable())
		{
			$default_opts = array(
				'http'=>array(
					'method'=>'GET',
					'header' => '',
					'follow_location' => 1,
					'max_redirects' => 20,
					'protocol_version' => 1.1,//5.3.0以前请使用1.0
					'timeout' => 60,
					'ignore_errors' => false,
					'request_fulluri' => false,
					'user_agent' => 'User-Agent:Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/61.0.3163.49 Safari/537.36',
				)
			);
			
			$default = stream_context_set_default($default_opts);
			return file_get_contents($this->_path);
		}
		return false;
	}

	/**
	 * 写入数据，会覆盖原来的数据
	 * 
	 * @param unknown $string        
	 * @return number
	 */
	function write($string)
	{
		return file_put_contents($this->_path, $string, LOCK_EX);
	}

	/**
	 * 文件是否可读
	 * 
	 * @return boolean
	 */
	function readable()
	{
		return $this->_readable;
	}

	/**
	 * 文件可写
	 * 
	 * @return boolean
	 */
	function writable()
	{
		return $this->_writable;
	}

	/**
	 * 是否是上传的
	 * 
	 * @return boolean
	 */
	function uploaded()
	{
		return is_uploaded_file($this->_path);
	}

	/**
	 * 文件删除,但是对象不会删除
	 * 
	 * @param callback $callback
	 *        回调函数 第一个参数代表当前对象,第二个参数代表删除是否成功 function($this,$result){}
	 */
	function delete($callback = null)
	{
		$result = unlink($this->_path);
		if (is_callable($callback))
		{
			call_user_func($callback, $this, $result);
		}
		return $result;
	}

	/**
	 * 文件复制
	 * 
	 * @param callback $callback
	 *        回调函数 第一个参数代表当前对象,第二个参数代表复制是否成功 function($this,$result){}
	 */
	function copy($path, $callback = null)
	{
		$result = copy($this->_path, $path);
		if (is_callable($callback))
		{
			call_user_func($callback, $this, $result);
		}
		return $result;
	}
	
	/**
	 * 判断字符串是否是一个目录，不管是否存在
	 */
	static function is_dir($path)
	{
		return $path[strlen($path)-1] == '/';
	}

	/**
	 * 文件移动
	 * @param string $path 
	 * 	假如是一个目录，则文件移动到目录中，
	 * 	假如是一个文件，则相当于rename
	 *  假如是url文件，则下载后保存到指定位置，位置目录会自动创建
	 */
	function move($path)
	{
		if($this->_is_url)
		{
			if (self::is_dir($path))
			{
				if (!file_exists($path))
				{
					mkdir($path,true,0777);
				}
				file_put_contents($path.$this->_basename, $this->content());
				$new_path = $path.$this->_basename;
			}
			else
			{
				$dir = pathinfo($path,PATHINFO_DIRNAME);
				if (!file_exists($dir))
				{
					mkdir($dir,true,0777);
				}
				file_put_contents($path, $this->content());
				$new_path = $path;
			}
		}
		else
		{
			if ($this->uploaded())
			{
				if (self::is_dir($path))
				{
					if (!file_exists($path))
					{
						mkdir($path,true,0777);
					}
					move_uploaded_file($this->_path, $path.'/'.$this->_basename);
					$new_path = $path.'/'.$this->_basename;
				}
				else
				{
					$dir = pathinfo($path,PATHINFO_DIRNAME);
					if (!file_exists($dir))
					{
						mkdir($dir,true,0777);
					}
					move_uploaded_file($this->_path, $path);
					$new_path = $this->_path;
				}
			}
			else
			{
				if (self::is_dir($path))
				{
					if (!file_exists($path))
					{
						mkdir($path,true,0777);
					}
					rename($this->_path, $path.'/'.$this->_basename);
					$new_path = $path.'/'.$this->_basename;
				}
				else
				{
					$dir = pathinfo($path,PATHINFO_DIRNAME);
					if (!file_exists($dir))
					{
						mkdir($dir,true,0777);
					}
					rename($this->_path, $path);
					$new_path = $path;
				}
			}
		}
		return new self($new_path);
	}
	
	/**
	 * 改名（不更改扩展名）
	 * 对于url文件 此函数不起作用
	 * 很奇怪 这里居然会有一个warning错误  code:32  导致文件更名失败
	 */
	function rename($name)
	{
		if (!$this->_is_url)
		{
			$name = $this->dirname().'/'.$name.'.'.$this->_extension;
			rename($this->_path, $name);
			return new self($name);
		}
		return $this;
	}

	/**
	 * 文件更改权限
	 * 
	 * @param callback $callback
	 *        回调函数 第一个参数代表当前对象,第二个参数代表更改是否成功 function($this,$result){}
	 */
	function chmod($mode, $callback = null)
	{
		$result = chmod($this->_path, $mode);
		if (is_callable($callback))
		{
			call_user_func($callback, $this, $result);
		}
		return $result;
	}

	/**
	 * 获取文件mimetype类型
	 * 
	 * @param unknown $magic        
	 */
	function mimeType($magic = null)
	{
		if ($magic !== null)
		{
			return finfo_file(finfo_open(FILEINFO_MIME_TYPE, $magic), $this->_path);
		}
		return $this->_mimeType;
	}

	/**
	 * 获取文件完整路径
	 * @param boolean $real 默认为true、
	 * 	true:磁盘的完整路径
	 * 	false:基于网站的根目录的路径
	 */
	function path($real = true)
	{
		if ($real)
		{
			return $this->_path;
		}
		else
		{
			return str_replace(ROOT, '', $this->_path);
		}
	}

	/**
	 * 获取文件名，不包含文件后缀
	 */
	function filename()
	{
		return $this->_filename;
	}

	/**
	 * 获取文件名，包含后缀
	 */
	function basename()
	{
		return $this->_basename;
	}

	/**
	 * 获取文件后缀 不包括.
	 */
	function extension()
	{
		return $this->_extension;
	}

	/**
	 * 文件存储路径
	 * 
	 * @return mixed
	 */
	function dirname()
	{
		return $this->_dirname;
	}

	/**
	 * 文件的绝对路径
	 * 
	 * @return string
	 */
	function realpath()
	{
		return realpath($this->path());
	}
}
