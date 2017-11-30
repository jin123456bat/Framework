<?php
namespace framework\core\request\parser\xml;

use framework\core\request\parser\parser;

/**
 * xml解析器
 * @author jin
 *
 */
class xml extends parser
{	
	/**
	 * {@inheritDoc}
	 * @see \framework\core\parser\parser::getData()
	 */
	function getData()
	{
		$xml = simplexml_load_string($this->_content);
		$json = json_encode($xml);
		return json_decode($json,TRUE);
	}
}