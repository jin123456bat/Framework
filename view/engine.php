<?php
namespace framework\view;

use framework\core\component;
use framework\vendor\file;
use framework\view\compiler\compiler;
use framework\core\base;

/**
 *
 * @author fx
 */
class engine extends component
{

	/**
	 * 编译器实例
	 * @var compiler
	 */
	private $_compiler = NULL;

	private $_path;

	private $_name;

	function __construct()
	{
		$this->_compiler = new compiler();
	}

	/**
	 * 向模板钟添加变量或者函数
	 */
	function assign($var, $val)
	{
		$this->_compiler->assign($var, $val);
	}

	/*
	 * /**
	 * 变量替换
	 * @param unknown $var
	 * @param unknown $val
	 */
	private function replaceVariable($var, $val)
	{
		$pattern = '!' . $this->_leftDelimiter . '.*\$' . $var . '.*' . $this->_rightDelimiter . '!';
		$this->_compile = preg_replace_callback($pattern, function ($matches) use ($var, $val) {
			return preg_replace('!\$' . $var . '!', $val, $matches[0]);
		}, $this->_compile);
	}

	/**
	 * 计算一个不带括号的表达式的值
	 * 
	 * @param unknown $string        
	 * @return mixed
	 */
	private function expression($string)
	{
		return @eval('return ' . $string . ';');
	}

	/**
	 * 现在的方式有点问题 --- 输出的模板同时也参与了内容的迭代
	 * 模板中函数等 最终运算
	 */
	private function parse()
	{
		$pattern = '!' . $this->_leftDelimiter . '.*' . $this->_rightDelimiter . '!';
		$this->_compile = preg_replace_callback($pattern, function ($matches) {
			$result = $matches[0];
			do
			{
				// 匹配最内层括号
				$result = preg_replace_callback('!\w*\([^\(\)]*\)!', function ($brackets) {
					// 找到其中的参数
					preg_match('!\(.*\)!', $brackets[0], $args);
					$args_list = explode(',', trim($args[0], '()'));
					$args_list = array_map(function ($arg) {
						if (! empty($arg))
						{
							// 参数中有表达式，计算参数中的表达式
							return $this->expression($arg);
						}
						else
						{
							return '';
						}
					}, $args_list);
					// 找到函数名
					$func_name = str_replace($args[0], '', $brackets[0]);
					if (! empty($func_name))
					{
						// 假如是PHP内置函数 直接执行
						if (function_exists($func_name))
						{
							return call_user_func_array($func_name, $args_list);
						}
						else
						{
							// 不是PHP内置函数 在$this->_function中寻找
							if (isset($this->_function[$func_name]))
							{
								return call_user_func_array($this->_function[$func_name], $args_list);
							}
						}
					}
					else
					{
						return $this->expression(trim($args[0], '()'));
					}
				}, $result);
			}
			while (preg_match('!\(.*\)!', $result));
			return $this->expression(ltrim(rtrim($result, $this->_rightDelimiter), $this->_leftDelimiter));
		}, $this->_compile);
	}

	/**
	 * 设置模板路径
	 * 
	 * @param unknown $path        
	 */
	function setTemplatePath($path)
	{
		$this->_path = $path;
		$this->_compiler->setTemplatePath($path);
	}

	/**
	 * 设置模板名称
	 * 
	 * @param unknown $name        
	 */
	function setTempalteName($name)
	{
		$this->_name = $name;
		$file = rtrim($this->_path, '/') . '/' . ltrim($this->_name, '/');
		if (file_exists($file) && is_readable($file))
		{
			$content = file_get_contents($file);
			$this->_compiler->setTempalte($content);
		}
	}

	/**
	 * 获取解析后的内容
	 */
	function fetch()
	{
		return $this->_compiler->fetch();
	}
}