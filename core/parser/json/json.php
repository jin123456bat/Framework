<?php
namespace framework\core\parser\json;

use framework\core\parser\parser;

/**
 * json解析器
 * @author jin
 *
 */
class json extends parser
{	
	/**
	 * {@inheritDoc}
	 * @see \framework\core\parser\parser::getData()
	 */
	function getData()
	{
		return json_decode($this->_content,true);
	}
}