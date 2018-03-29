<?php
namespace framework\core\database\sphinx;

/**
 * 字符串搜索匹配
 * @author jin
 */
class match
{
	private $_match = '';
	
	/**
	 * match字符串组合
	 * @param array|string $field
	 * @param array|string $value
	 * @param string $is_or
	 * @return string
	 */
	private function string($field,$value,$is_or = false)
	{
		if (is_scalar($field) && is_scalar($value))
		{
			return '(@'.$field.' "'.$value.'")';
		}
		else if (is_scalar($field) && is_array($value))
		{
			if ($is_or)
			{
				return '(@'.$field.' "'.implode('" | "', $value).'")';
			}
			else
			{
				return '(@'.$field.' "'.implode('" "', $value).'")';
			}
		}
		else if (is_array($field))
		{
			$s = array_map(function($f) use($value,$is_or){
				return $this->string($f, $value,$is_or);
			}, $field);
			if ($is_or)
			{
				return '('.implode(' | ', $s).')';
			}
			else
			{
				return '('.implode(' ', $s).')';
			}
		}
	}
	
	function __toString()
	{
		return $this->_match;
	}
	
	/**
	 * var_dump($express->string('name','jin'));  name中包含jin的
	 * var_dump($express->string('name',array('jin','chen'))); name中包含jin并且包含chen的
	 * var_dump($express->string('name',array('jin','chen'),true)); name中包含jin或者包含chen的
	 * var_dump($express->string(array('name','desc'),'jin'));  name并且desc中包含jin的
	 * var_dump($express->string(array('name','desc'),'jin',true)); name或者desc中包含jin的
	 * var_dump($express->string(array('name','desc'),array('jin','chen'))); name和desc中包含jin并且包含chen的
	 * var_dump($express->string(array('name','desc'),array('jin','chen'),true)); name或者desc中包含jin或者包含chen的
	 * 
	 * @param string|array $field
	 * @param string $value
	 * @param string $is_or
	 */
	function where($field,$value = NULL,$is_or = false)
	{
		$string = $this->string($field, $value,$is_or);
		if (empty($this->_match))
		{
			$this->_match = $string;
		}
		else if ($is_or)
		{
			$this->_match .= ' | '.$string;
		}
		else
		{
			$this->_match .= ' '.$string;
		}
		return $this;
	}
}