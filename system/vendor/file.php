<?php
namespace system\vendor;
use system\core\base;

/**
 * 文件基类，包含了文件信息和文件操作
 * @author fx
 *
 */
class file extends base
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
	
	private $_error;
	
	function __construct($file)
	{
		if (is_string($file))
		{
			if (is_dir($file) || is_file($file))
			{
				$this->_path = $file;
			}
			else
			{
				if (($file[strlen($file)-1] == '/' || $file[strlen($file)-1] == '\\'))
				{
					if(mkdir($file,0777,true))
					{
						$this->_path = $file;
					}
					else
					{
						$this->_error = '创建目录失败';
					}
				}
				else
				{
					if(touch($file))
					{
						$this->_path = $file;
					}
					else
					{
						$this->_error = '创建文件失败';
					}
				}
			}
		}
		else if (is_object($file) && $file instanceof file)
		{
			$this->_path = $file->path();
		}
		else
		{
			$this->_error = '构造参数错误';
		}
		
		$this->parse();
	}
	
	/**
	 * 文件解析函数
	 */
	private function parse()
	{
		clearstatcache();
		
		$this->_mimtType = finfo_file(finfo_open(FILEINFO_MIME_TYPE, NULL), $this->_path);
		
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
	
	/**
	 * 假如有错误返回错误信息，否则返回NULL
	 */
	function error()
	{
		return $this->_error;
	}
	
	/**
	 * 上次改变时间
	 * @return int 时间戳
	 */
	function ctime()
	{
		return $this->_ctime;
	}
	
	/**
	 * 上次访问时间
	 * @return int 时间戳
	 */
	function atime()
	{
		return $this->_atime;
	}
	
	/**
	 * 上次修改时间
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
	function content($start = 0,$length = NULL)
	{
		if ($this->readable())
		{
			return file_get_contents($this->_path,NULL,NULL,$start,$length);
		}
		return false;
	}
	
	/**
	 * 文件是否可读
	 * @return boolean
	 */
	function readable()
	{
		return is_readable($this->_path);	
	}

	/**
	 * 文件可写
	 * @return boolean
	 */
	function writable()
	{
		return is_writable($this->_path);
	}
	
	/**
	 * 是否是上传的
	 * @return boolean
	 */
	function uploaded()
	{
		return is_uploaded_file($this->_path);
	}
	
	/**
	 * 文件删除,但是对象不会删除
	 * @param callback $callback 回调函数  第一个参数代表当前对象,第二个参数代表删除是否成功 function($this,$result){}
	 * 
	 */
	function delete($callback = NULL)
	{
		$result = unlink($this->_path);
		if (is_callable($callback))
		{
			call_user_func($callback,$this,$result);
		}
		return $result;
	}
	
	/**
	 * 文件复制
	 * @param callback $callback 回调函数  第一个参数代表当前对象,第二个参数代表复制是否成功 function($this,$result){}
	 */
	function copy($path,$callback = NULL)
	{
		$result = copy($this->_path,$path);
		if (is_callable($callback))
		{
			call_user_func($callback,$this,$result);
		}
		return $result;
	}
	
	/**
	 * 文件移动
	 * @param callback $callback 回调函数  第一个参数代表当前对象,第二个参数代表移动是否成功 function($this,$result){}
	 */
	function move($path,$callback = NULL)
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
			call_user_func($callback,$this,$result);
		}
		return $result;
	}
	
	/**
	 * 文件更改权限
	 * @param callback $callback 回调函数  第一个参数代表当前对象,第二个参数代表更改是否成功 function($this,$result){}
	 */
	function chmod($mode,$callback = NULL)
	{
		$result = chmod($this->_path,$mode);
		if (is_callable($callback))
		{
			call_user_func($callback,$this,$result);
		}
		return $result;
	}
	
	/**
	 * 创建文件，假如文件存在则设定文件的访问时间和修改时间
	 */
	function touch()
	{
		
	}
	
	/**
	 * 获取文件mimetype类型
	 * @param unknown $magic
	 */
	function mimeType($magic = NULL)
	{
		if ($magic !== NULL)
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
	 * @return mixed
	 */
	function dirname()
	{
		return $this->_dirname;
	}
}