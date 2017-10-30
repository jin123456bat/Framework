<?php
namespace framework\view;

use framework\core\component;

/**
 * 设置模板文件的路径只有2种方法
 * 要么通过setTemplate直接设置完整路径
 * 要么先通过setTemplatePath设置一下目录 然后在 setTemplateName设置名称
 * @author fx
 */
class engine extends component
{

	/**
	 * 编译器实例
	 * @var compiler
	 */
	private $_compiler = NULL;

	/**
	 * 模板文件所在路径
	 * @var unknown
	 */
	private $_path;

	/**
	 * 模板文件名称
	 * @var unknown
	 */
	private $_name;
	
	/**
	 * 模板文件完整路径
	 * @var unknown
	 */
	private $_template;

	function __construct()
	{
		$config = $this->getConfig('view');
		$engine = isset($config['engine']) && !empty($config['engine'])?$config['engine']:'regp';
		$engine = '\\framework\\view\\engine\\'.$engine.'\\compiler';
		$this->_compiler = new $engine();
	}

	/**
	 * 向模板钟添加变量或者函数
	 */
	function assign($var, $val)
	{
		$this->_compiler->assign($var, $val);
	}
	
	/**
	 * 直接设置模板文件完整路径
	 * 可以不通过路径和名称的形式设置
	 * @param string $template
	 */
	function setTemplate($template)
	{
		$this->_template = $template;
		//以文件所在目录作为默认目录
		$this->_compiler->addTemplatePath(pathinfo($this->_template,PATHINFO_DIRNAME));
		if (file_exists($this->_template) && is_readable($this->_template))
		{
			$this->_compiler->setTempalte(file_get_contents($this->_template));
		}
	}

	/**
	 * 设置模板文件所在路径 
	 * @param string $path        
	 */
	function setTemplatePath($path)
	{
		$this->_path = $path;
		$this->_compiler->addTemplatePath($this->_path);
		if (!empty($this->_name))
		{
			$this->_template = rtrim($this->_path, '/') . '/' . ltrim($this->_name, '/');
			if (file_exists($this->_template) && is_readable($this->_template))
			{
				$this->_compiler->setTempalte(file_get_contents($this->_template),$this->_template);
			}
		}
	}

	/**
	 * 设置模板名称
	 * @param unknown $name        
	 */
	function setTempalteName($name)
	{
		$this->_name = $name;
		if (!empty($this->_path))
		{
			$this->_template = rtrim($this->_path, '/') . '/' . ltrim($this->_name, '/');
			if (file_exists($this->_template) && is_readable($this->_template))
			{
				$this->_compiler->setTempalte(file_get_contents($this->_template),$this->_template);
			}
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