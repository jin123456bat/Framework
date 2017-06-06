<?php
namespace framework\vendor;

use lib\error;

/**
 * 文件基类，包含了文件信息和文件操作
 *
 * @author fx
 *        
 */
class file extends \framework\lib\error
{

	private $_atime;

	private $_mtime;

	private $_ctime;

	private $_size;

	private $_dirname;

	private $_basename;

	private $_filename;

	private $_extension;

	private $_mimtType;

	private $_type;

	private $_path;
	
	private $_resource;

	function __construct($file,$create_not_exist = true)
	{
		if (is_string($file))
		{
			if (is_dir($file) || is_file($file))
			{
				$this->_path = $file;
			}
			else if ($create_not_exist)
			{
				if (($file[strlen($file) - 1] == '/' || $file[strlen($file) - 1] == '\\'))
				{
					if (mkdir($file, 0777, true))
					{
						$this->_path = $file;
					}
					else
					{
						$this->addError('000001', 'create dir failed');
					}
				}
				else
				{
					if (touch($file))
					{
						$this->_path = $file;
					}
					else
					{
						$this->addError('000001', 'create file failed');
					}
				}
			}
		}
		else
		{
			$this->addError('000001', 'constructor parameter $file error');
		}
		
		//加锁
		if (file_exists($this->_path))
		{
			$this->_resource = fopen($this->_path, 'a+');
			flock($this->_resource, LOCK_EX);
		}
		
		$this->parse();
	}
	
	function __destruct()
	{
		//解锁
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
			
			$this->_mimtType = finfo_file(finfo_open(FILEINFO_MIME_TYPE, null), $this->_path);
			
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
	 * 上次改变时间
	 *
	 * @return int 时间戳
	 */
	function ctime()
	{
		return $this->_ctime;
	}

	/**
	 * 上次访问时间
	 *
	 * @return int 时间戳
	 */
	function atime()
	{
		return $this->_atime;
	}

	/**
	 * 上次修改时间
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
	function content($start = null, $length = null)
	{
		if ($this->readable())
		{
			if (!empty($length))
			{
				return file_get_contents($this->_path, null, null, $start,$length);
			}
			else
			{
				return file_get_contents($this->_path, null, null, $start);
			}
		}
		return false;
	}
	
	/**
	 * 写入数据，会覆盖原来的数据
	 * @param unknown $string
	 * @return number
	 */
	function write($string)
	{
		return file_put_contents($this->_path, $string,LOCK_EX);
	}

	/**
	 * 文件是否可读
	 *
	 * @return boolean
	 */
	function readable()
	{
		return is_readable($this->_path);
	}

	/**
	 * 文件可写
	 *
	 * @return boolean
	 */
	function writable()
	{
		return is_writable($this->_path);
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
	 *        	回调函数 第一个参数代表当前对象,第二个参数代表删除是否成功 function($this,$result){}
	 *        	
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
	 *        	回调函数 第一个参数代表当前对象,第二个参数代表复制是否成功 function($this,$result){}
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
	 * 文件移动
	 *
	 * @param callback $callback
	 *        	回调函数 第一个参数代表当前对象,第二个参数代表移动是否成功 function($this,$result){}
	 */
	function move($path, $callback = null)
	{
		if ($this->uploaded())
		{
			$result = move_uploaded_file($this->_path, $path);
		}
		else
		{
			$result = rename($this->_path, $path);
		}
		if (is_callable($callback))
		{
			call_user_func($callback, $this, $result);
		}
		return $result;
	}

	/**
	 * 文件更改权限
	 *
	 * @param callback $callback
	 *        	回调函数 第一个参数代表当前对象,第二个参数代表更改是否成功 function($this,$result){}
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
		return $this->_mimtType;
	}

	/**
	 * 获取文件路径
	 */
	function path()
	{
		return $this->_path;
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
	 * 获取文件后缀
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
