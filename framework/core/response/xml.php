<?php
namespace framework\core\response;

use framework\core\response;

class xml extends response
{
	private $_xml;
	
	private $_cdata;
	
	private $_version;
	
	private $_charset;
	
	private $_contentType = '';
	
	private $_cache;
	
	private $_header;
	
	/**
	 * @param mixed $content 
	 * @param boolean $cdata 是否开启cdata解析
	 * @param boolean $cache 输出结果是否缓存
	 */
	function __construct($content,$cdata = false,$cache = false,$header = 'request')
	{
		$this->_cdata = $cdata;
		$this->_header = $header;
		if(is_string($content))
		{
			$this->loadString($content);
		}
		else if (is_array($content))
		{
			$this->loadArray($content);
		}
	}
	
	
	
	function setHeader($string)
	{
		$this->_header = $string;
	}
	
	/**
	 * 设置输出结果是否缓存
	 * @param unknown $cache
	 */
	function cache($cache)
	{
		$this->_cache = $cache;
	}
	
	/**
	 * 返回是否缓存输出结果
	 * @return unknown
	 */
	function isCache()
	{
		return $this->_cache;
	}
	
	/**
	 * 获得mimetype
	 * @return string
	 */
	function getContentType()
	{
		return $this->_contentType;
	}
	
	/**
	 * 设置contentType
	 * @param unknown $contentType
	 */
	function setContentType($contentType)
	{
		$this->_contentType = $contentType;
	}
	
	function __toString()
	{
		return $this->_xml;
	}
	
	/**
	 * 从字符串载入
	 * @param unknown $string
	 */
	function loadString($string)
	{
		$this->_xml = $string;
	}
	
	/**
	 * 将数组转化为xml字符串
	 * @param unknown $array
	 */
	function loadArray($array)
	{
		$header = '<'.$this->_header.'>';
		$footer = '</'.$this->_header.'>';
		$body = $this->parseArray($array);
		$this->_xml = $header.$body.$footer;
	}
	
	/**
	 * 计算hash
	 * @return string
	 */
	function hash()
	{
		return md5($this->_xml);
	}
	
	/**
	 * 将数组转化为xml字符串  无头
	 * @param unknown $array
	 * @return string
	 */
	private function parseArray($array,$last_key = '')
	{
		$content = '';
		$replace = '';
		if (is_array($array))
		{
			foreach($array as $key=>$value)
			{
				if (is_string($value) || is_int($value) || is_bool($value) || is_float($value))
				{
					$replace = $this->_cdata?('<![CDATA['.$value.']]>'):$value;
				}
				else if (is_array($value))
				{
					if (array_key_exists(0, $value))
					{
						foreach ($value as $num => $item)
						{
							$replace .= $this->parseArray($item);
							if ($num != count($value)-1)
							{
								$replace .= '</'.$key.'>'.'<'.$key.'>';
							}
						}
					}
					else
					{
						$replace = $this->parseArray($value);
					}
				}
				$content .= '<'.$key.'>'.$replace.'</'.$key.'>';
			}
		}
		else
		{
			return $array;
		}
		return $content;
	}
}