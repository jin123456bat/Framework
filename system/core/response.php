<?php
namespace core;

use system\vendor\file;

class response extends base
{
	public $status = 200;
	
	public $header = NULL;
	
	public $body = '';
	
	function __construct()
	{
		$this->header = new header();
	}
	
	/**
	 * 设置缓存时间
	 */
	function cache($cache)
	{
		
	}
	
	function getBody()
	{
		return $this->body;
	}
	
	function setBody($content)
	{
		if ($content instanceof \vendor\file)
		{
			$this->body = $content->content();
		}
		if ($content instanceof view)
		{
			$this->body = $content->display();
		}
		$this->body = $this->setVariableType($content,'s');
		
	}
	
	function setHttpStatus($status)
	{
		$this->status = filter::int($status);
	}
	
	function getHttpStatus()
	{
		return $this->status;
	}
	
	function setHeader(header $header,$value = '')
	{
		if ($header instanceof header)
		{
			$this->header = $header;
		}
		else if (!empty($value))
		{
			$this->header->add($header,$value);
		}
	}
	
	
}