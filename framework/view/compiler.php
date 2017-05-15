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
	 */
	abstract public function getLeftDelimiter();
	
	/**
	 * 获取右结束符
	 */
	abstract public function getRightDelimiter();
	
	/**
	 * 设置右结束符
	 * @param unknown $rightDelimiter
	 */
	abstract public function setRightDelimiter($rightDelimiter);
	
	/**
	 * 设置左开始符
	 * @param unknown $leftDelimiter
	 */
	abstract public function setLeftDelimiter($leftDelimiter);
	
	/**
	 * 计算表达式的值
	 * @param string $string
	 * @return mixed 表达式的计算结果
	 */
	abstract public function variable($string);
}