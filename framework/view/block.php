<?php
namespace framework\view;
use framework\core\component;

abstract class block extends component
{
	/**
	 * block的执行函数
	 * @param string $content block包裹的内容
	 * @param string $parameter block携带的参数
	 * @param compiler $compiler compiler对象
	 */
	abstract function compile($content,$parameter,compiler $compiler);
}