<?php
namespace framework\core\request\parser\json;

use framework\core\request\parser\parser;

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