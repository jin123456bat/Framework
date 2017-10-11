<?php
namespace framework\view\engine;

/**
 * 引擎编译器接口
 * @author jin
 *
 */
abstract class compiler
{
	/**
	 * 在模板中添加变量
	 * @param string $var
	 * @param mixed $val
	 */
	abstract public function assign($var, $val);
	
	/**
	 * 在模板中删除变量
	 * @param string $var
	 */
	abstract public function unassign($var);
	
	/**
	 * 设置模板内容
	 * @param string $tempalte
	 */
	abstract public function setTempalte($tempalte);

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
	 * 添加模板默认目录
	 * @param string $dir 目录
	 */
	abstract public function addTemplatePath($dir);
	
	/**
	 * 获取模板默认目录
	 * @return string[]
	 */
	abstract public function getTemplatePath();
}