<?php
namespace framework\core\response;
use framework\core\response;
use framework;
use framework\core\request;

class file extends response
{
	private $_path;
	
	function __construct($file)
	{
		if (is_file($file))
		{
			$this->_path = $file;
		}
		else if ($file instanceof framework\vendor\file)
		{
			$this->_path = $file->path();
		}
		
		$resource = fopen($file, 'rb');
		
		$range = request::header('Range');
		if (!empty($range))
		{
			//Range: bytes=5275648- 
			
			
			//Range: bytes=0-499 下载第0-499字节范围的内容 
			//Range: bytes=500-999 下载第500-999字节范围的内容 
			//Range: bytes=-500 下载最后500字节的内容 
			//Range: bytes=500- 下载从第500字节开始到文件结束部分的内容
			
			list($start, $end) = sscanf(strtolower($range), "bytes=%s-%s");
			
			$start = trim($start);
			$end = trim($end);
			
			$contents = '';
			if ($start == '0' && !empty($end))
			{
				$contents = fread($resource, $end);
			}
			else if (!empty($start) && $start!='0' && !empty($end))
			{
				fseek($resource,$start);
				$contents = fread($resource, $end - $start);
			}
			else if ($start == '' && !empty($end))
			{
				fseek($resource,-$end,SEEK_SET);
				$contents = fread($resource, $end);
			}
			else if ($end == '' && !empty($start))
			{
				fseek($resource,$start);
				while (!feof($resource))
				{
					$contents .= fread($resource, 8192);
				}
			}
			
			$this->setBody($contents);
			$this->setHttpStatus(206);
			
			//Content-Range: bytes 5275648-15143085/15143086
			//Content-Length: 9867438
			$this->setHeader('Content-Range','bytes '.self::setVariableType($start,'i').'-'.self::setVariableType($end,'i').'/'.filesize($this->_path)+1);
			$this->setHeader('Content-Length',self::setVariableType($end,'i') - self::setVariableType($start,'i')+1);
		}
		else
		{
			$this->setBody(file_get_contents($this->_path));
			$this->setHttpStatus(200);
		}
		fclose($resource);
	}
}