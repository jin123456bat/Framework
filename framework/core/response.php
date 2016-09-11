<?php
namespace framework\core;

use framework\vendor\file;

class response extends base
{
	/**
	 * http code
	 * @var integer
	 */
	private $_status = 200;
	
	/**
	 * http response header
	 * @var unknown
	 */
	private $_header = NULL;
	
	/**
	 * http body
	 * @var string
	 */
	private $_body = '';
	
	/**
	 * response content charset
	 * @var string
	 */
	private $_charset = 'utf-8';
	
	function __construct($response_string = '')
	{
		$this->_body = $response_string;
		$this->header = new header();
	}
	
	/**
	 * get http body
	 * @return string
	 */
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
	
	/**
	 * set http code
	 * @param unknown $status
	 */
	function setHttpStatus($status)
	{
		$this->status = filter::int($status);
	}
	
	/**
	 * get http code
	 * @return int
	 */
	function getHttpStatus()
	{
		return $this->status;
	}
	
	/**
	 * set http header or add http header
	 * @param unknown $header 
	 * header's name
	 * @param string [optional] $value
	 * if $header instanceof header this parameter will be ignore
	 * otherwise if $value is not empty $header and $value will added into current header
	 */
	function setHeader($header,$value = '')
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