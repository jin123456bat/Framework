<?php
namespace framework\core\response;

use framework\core\response;
use framework\core\request;

class file extends response
{
	private $_path;

	/**
	 * @param string|file $file
	 * @param bool $download  默认为false
	 * @param string $download_name 下载的时候的文件名 默认为文件本身的名称
	 * 当为false的时候 由浏览器自动判断，例如pdf文件浏览器可能直接显示出来，而不是期望中的下载，当为true的时候强制为下载
	 */
	function __construct($file,$download = false,$download_name = '')
	{
		parent::__construct();
		
		if (is_file($file))
		{
			$this->_path = $file;
			$pathinfo = pathinfo($this->_path);
			$this->_basename = $pathinfo['basename'];
			$this->_extension = $pathinfo['extension'];
		}
		else if ($file instanceof \framework\vendor\file)
		{
			$this->_path = $file->path();
			$this->_basename = $file->basename();
			$this->_extension = $file->extension();
		}
		
		$resource = fopen($file, 'rb');
		
		if ($download)
		{
			$this->setHeader(array(
				'Content-Type: application/force-download',
				'Cache-Control: must-revalidate, post-check=0, pre-check=0',
				'Content-Transfer-Encoding: binary',
			));
		}
		//header("Content-Disposition: attachment;filename=" . $fileName . ".csv");
		if (empty($download_name))
		{
			$this->setHeader('Content-Disposition','attachment;filename='.$this->_basename);
		}
		else
		{
			if (stripos('.', $download_name) === false)
			{
				$download_name .= '.'.$this->_extension;
			}
			$this->setHeader('Content-Disposition','attachment;filename='.$download_name);
		}
		
		$range = request::header('Range');
		if (! empty($range))
		{
			// Range: bytes=5275648-
			
			// Range: bytes=0-499 下载第0-499字节范围的内容
			// Range: bytes=500-999 下载第500-999字节范围的内容
			// Range: bytes=-500 下载最后500字节的内容
			// Range: bytes=500- 下载从第500字节开始到文件结束部分的内容
			
			list ($start, $end) = sscanf(strtolower($range), "bytes=%s-%s");
			
			$start = trim($start);
			$end = trim($end);
			
			$contents = '';
			if ($start == '0' && ! empty($end))
			{
				$contents = fread($resource, $end);
			}
			else if (! empty($start) && $start != '0' && ! empty($end))
			{
				fseek($resource, $start);
				$contents = fread($resource, $end - $start);
			}
			else if ($start == '' && ! empty($end))
			{
				fseek($resource, - $end, SEEK_SET);
				$contents = fread($resource, $end);
			}
			else if ($end == '' && ! empty($start))
			{
				fseek($resource, $start);
				while (! feof($resource))
				{
					$contents .= fread($resource, 8192);
				}
			}
			
			$this->setBody($contents);
			$this->setHttpStatus(206);
			
			// Content-Range: bytes 5275648-15143085/15143086
			// Content-Length: 9867438
			$this->setHeader('Content-Range', 'bytes ' . self::setVariableType($start, 'i') . '-' . self::setVariableType($end, 'i') . '/' . filesize($this->_path) + 1);
			//$this->setHeader('Content-Length', self::setVariableType($end, 'i') - self::setVariableType($start, 'i') + 1);
		}
		else
		{
			$this->setBody(file_get_contents($this->_path));
			$this->setHttpStatus(200);
		}
		fclose($resource);
	}
}