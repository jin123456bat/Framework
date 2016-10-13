<?php
namespace framework\core;

use framework\vendor\file;

class response extends component
{
	/**
	 * http code
	 * @var integer
	 */
	private $_status = 200;
	
	/**
	 * http response header
	 * @var header
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
	
	function __construct($response_string = '',$status = 200)
	{
		$this->_body = $response_string;
		$this->_status = filter::int($status);
		$this->_header = new header();
		parent::__construct();
	}
	
	/**
	 * get http body
	 * @return string
	 */
	function getBody()
	{
		return $this->_body;
	}
	
	function setBody($content)
	{
		if ($content instanceof \vendor\file)
		{
			$this->_body = $content->content();
		}
		if ($content instanceof view)
		{
			$this->_body = $content->display();
		}
		$this->_body = $this->setVariableType($content,'s');
	}
	
	/**
	 * set http code
	 * @param unknown $status
	 */
	function setHttpStatus($status)
	{
		$this->_status = filter::int($status);
	}
	
	/**
	 * get http code
	 * @return int
	 */
	function getHttpStatus()
	{
		return $this->_status;
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
			$this->_header = $header;
		}
		else if (!empty($value))
		{
			$this->_header->add($header,$value);
		}
	}
	
	function getHeader()
	{
		return $this->_header;
	}
	
	function __toString()
	{
		return $this->getBody();
	}
}