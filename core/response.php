<?php
namespace framework\core;

class response extends component
{	
	/**
	 * http code
	 * 
	 * @var integer
	 */
	private $_status = 200;

	/**
	 * http response header
	 * 
	 * @var header
	 */
	private $_header = null;

	/**
	 * http body
	 * 
	 * @var string
	 */
	private $_body = '';

	/**
	 * response content charset
	 * 
	 * @var string
	 */
	private $_charset = 'utf-8';

	/**
	 * 响应内容类型
	 */
	private $_contentType = 'text/html';

	function __construct($response_string = '', $status = 200)
	{
		$this->_status = filter::int($status);
		$this->_header = new header();
		$this->setBody($response_string);
		parent::__construct();
	}

	function initlize()
	{
		$this->_charset = ini_get('default_charset');
		$this->setHeader('Content-Type', $this->getContentType() . '; charset=' . $this->getCharset());
		$this->setHeader('Server','framework/'.APP_NAME);
		//$this->setHeader('Date',date(DATE_RFC2822));
		parent::initlize();
	}

	/**
	 * 设置字符集
	 * 
	 * @param unknown $charset        
	 */
	function setCharset($charset)
	{
		$this->_charset = $charset;
	}

	/**
	 * 获取当前使用的字符集
	 * 
	 * @return string
	 */
	function getCharset()
	{
		return strtolower($this->_charset);
	}

	/**
	 * set response content type
	 * 
	 * @param unknown $contentType        
	 */
	function setContentType($contentType)
	{
		$this->_contentType = $contentType;
	}

	/**
	 * get response content type
	 * 
	 * @return string|unknown
	 */
	function getContentType()
	{
		return $this->_contentType;
	}

	/**
	 * get http body
	 * 
	 * @return string
	 */
	function getBody()
	{
		return $this->_body;
	}

	/**
	 * set response body
	 * 
	 * @param unknown $content        
	 */
	function setBody($content)
	{
		$this->_body = $this->setVariableType($content, 's');
		$this->setHeader('Content-Length',strlen($this->_body));
	}

	/**
	 * set http code
	 * 
	 * @param unknown $status        
	 */
	function setHttpStatus($status)
	{
		$this->_status = filter::int($status);
	}

	/**
	 * get http code
	 * 
	 * @return int
	 */
	function getHttpStatus()
	{
		return $this->_status;
	}

	/**
	 * set http header or add http header
	 * 
	 * @param unknown $header
	 *        header's name
	 * @param
	 *        string [optional] $value
	 *        if $header instanceof header this parameter will be ignore
	 *        otherwise if $value is not empty $header and $value will added into current header
	 */
	function setHeader($header, $value = '')
	{
		if ($header instanceof header)
		{
			$this->_header = $header;
		}
		else if (! empty($value))
		{
			$this->_header->set($header, $value);
		}
		else if (is_array($header))
		{
			foreach ($header as $k => $h)
			{
				if (is_int($k))
				{
					$this->_header->set($h);
				}
				else if (is_string($k))
				{
					$this->_header->set($k,$h);
				}
			}
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
