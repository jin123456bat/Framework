<?php
namespace framework\vendor\formatter;
abstract class formatter
{
	protected $_code;
	
	protected $_charset = 'utf-8';
	
	/**
	 * @param unknown $string 完整代码片段
	 * @param unknown $charset 字符集 默认utf-8
	 */
	function __construct($string,$charset = NULL)
	{
		$this->_code = $string;
		if (!is_null($charset))
		{
			$this->_charset = $charset;
		}
	}
	
	/**
	 * @param string $string 当前行
	 * @return int 返回1 增加缩进
	 * 返回-1 减少缩进
	 * 返回0 缩进不变
	 */
	abstract function indent($string);
	
	/**
	 * 获取格式化后的代码
	 */
	abstract function getCode();
}