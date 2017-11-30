<?php
namespace framework\core\request\parser;

abstract class parser
{
	protected $_content;
	
	/**
	 * 设置解析器数据源
	 * @param unknown $string
	 */
	function setData($string)
	{
		$this->_content = $string;
	}
	
	/**
	 * 获取解析后的数据
	 */
	abstract function getData();
}