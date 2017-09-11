<?php
namespace framework\view;

abstract class compiler
{

	/**
	 * 获取模板编译后的内容
	 */
	abstract public function fetch();

	/**
	 * 获左开始符
	 * 
	 * @param $quote 是否获取转义后的字符
	 *        可以用户正则表达式 默认为true
	 */
	abstract public function getLeftDelimiter($quote = true);

	/**
	 * 获取右结束符
	 * 
	 * @param $quote 是否获取转义后的字符
	 *        可以用户正则表达式 默认为true
	 */
	abstract public function getRightDelimiter($quote = true);

	/**
	 * 设置右结束符
	 * 
	 * @param unknown $rightDelimiter        
	 */
	abstract public function setRightDelimiter($rightDelimiter);

	/**
	 * 设置左开始符
	 * 
	 * @param unknown $leftDelimiter        
	 */
	abstract public function setLeftDelimiter($leftDelimiter);

	/**
	 * 计算表达式的值
	 * 
	 * @param string $string        
	 * @return mixed 表达式的计算结果
	 */
	abstract public function variable($string);
}