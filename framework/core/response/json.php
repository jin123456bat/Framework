<?php
namespace framework\core\response;

use framework\core\response;

class json extends response
{
	
	/**
	 * 请求参数错误
	 * @var unknown
	 */
	const PARAMETER_ERROR = 0;
	
	/**
	 * ok
	 * @var unknown
	 */
	const OK = 1;
	
	/**
	 * 尚未登陆
	 * @var unknown
	 */
	const NOT_LOGIN = 2;
	
	/**
	 * 权限不足
	 * @var unknown
	 */
	const NO_POWER = 3;
	
	private $_isEncode;
	
	private $_code;
	
	private $_result;
	
	private $_body;
	
	private $_contentType = 'application/json';
	
	private $_cache = false;
	
	function __construct($code,$result = NULL,$body = NULL,$cache = false,$encode = false)
	{
		if(is_array($code) || is_object($code))
		{
			$this->_code = $code;
		}
		else
		{
			switch ($code)
			{
				case self::NO_POWER:
					$result = empty($result)?'权限不足':$result;
					break;
				case self::NOT_LOGIN:
					$result = empty($result)?'请重新登陆':$result;
					break;
				case self::OK:
					$result = empty($result)?'ok':$result;
					break;
				case self::PARAMETER_ERROR;
					$result = empty($result)?'参数错误':$result;
					break;
				default:break;
			}
			$this->_body = $body;
			$this->_code = $code;
			$this->_result = $result;
		}
		$this->_cache = $cache;
		$this->_isEncode = $encode;
	}
	
	function isCache()
	{
		return $this->_cache;
	}
	
	/**
	 * 返回的内容是否需要编码
	 */
	function isEncode()
	{
		return $this->_isEncode;
	}
	
	function setEncode($encode)
	{
		return $this->_isEncode = $encode;
	}
	
	function setContentType($contentType)
	{
		$this->_contentType = $contentType;
	}
	
	function getContentType()
	{
		return $this->_contentType;
	}
	
	function getCode()
	{
		return $this->_code;
	}
	
	function getResult()
	{
		return $this->_result;
	}
	
	function getBody()
	{
		return $this->_body;
	}
	
	function setCode($code)
	{
		$this->_code = $code;
	}
	
	function setResult($result)
	{
		$this->_result = $result;
	}
	
	function setBody($body)
	{
		$this->_body = $body;
	}
	
	function __toString()
	{
		if(is_array($this->_code) || is_object($this->_code))
		{
			if ($this->isEncode())
			{
				return json_encode($this->_code);
			}
			else
			{
				return json_encode($this->_code,JSON_UNESCAPED_UNICODE);
			}
		}
		$return =  array(
			'code'=>$this->_code,
			'result'=>$this->_result,
		);
		if($this->_body !== NULL)
			$return['body'] = $this->_body;
		if ($this->isEncode())
		{
			return json_encode($return);
		}
		else
		{
			return json_encode($return,JSON_UNESCAPED_UNICODE);
		}
	}
}