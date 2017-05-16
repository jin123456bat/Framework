<?php
namespace framework\view\block;
use framework\view\block;
use framework\view\compiler;

class section extends block
{
	/**
	 * {@inheritDoc}
	 * @see \framework\view\block::compile()
	 */
	function compile($content,$parameter,compiler $compiler)
	{
		$leftDelimiter = $compiler->getLeftDelimiter();
		$rightDelimiter = $compiler->getRightDelimiter();
		
		$result = '';
		$from = isset($parameter['from'])?$parameter['from']:array();
		$key_word = isset($parameter['key'])?$parameter['key']:'key';
		$value_word = isset($parameter['value'])?$parameter['value']:'value';
		foreach ($from as $key => $value)
		{
			$string = $content;
			$string = preg_replace_callback('!'.$leftDelimiter.'\$'.$key_word.$rightDelimiter.'!i', function($match) use($key){
				return $key;
			}, $string);
			
			$pattern = '!'.$leftDelimiter.'\$'.$value_word.$rightDelimiter.'!i';
			$string = preg_replace_callback($pattern, function($match) use($value){
				return $value;
			}, $string);
			
			$result .= $string;
		}
		return $result;
	}
}