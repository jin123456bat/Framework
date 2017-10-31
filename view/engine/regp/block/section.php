<?php
namespace framework\view\engine\regp\block;

use framework\view\engine\regp\block;
use framework\view\engine\regp\compiler;

class section extends block
{

	/**
	 *
	 * {@inheritdoc}
	 *
	 * @see \framework\view\block::compile()
	 */
	function compile($content, $parameter, compiler $compiler)
	{
		$result = '';
		$from = isset($parameter['from']) ? $parameter['from'] : array();
		$key_word = isset($parameter['key']) ? $parameter['key'] : 'key';
		$value_word = isset($parameter['value']) ? $parameter['value'] : 'value';
		foreach ($from as $key => $value)
		{
			$compiler->setTempalte($content,'');
			$compiler->assign($value_word, $value);
			$compiler->assign($key_word, $key);
			$string = $compiler->fetch();
			$result .= $string;
		}
		return $result;
	}
}