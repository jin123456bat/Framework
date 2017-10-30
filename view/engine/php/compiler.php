<?php
namespace framework\view\engine\php;

/**
 * php原生的编译引擎
 * @author jin
 *
 */
class compiler extends \framework\view\engine\compiler
{
	/**
	 * 模板内容
	 * @var string
	 */
	private $_template = '';
	
	/**
	 * 模板的完整路径
	 * @var string
	 */
	private $_template_path = '';
	
	/**
	 * 模板的根目录
	 * 也就是模板相对路径的路径
	 * @var string
	 */
	private $_template_root = '';
	
	private $_assign = array();
	
	function __construct($template = '')
	{
		$this->_template = $template;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \framework\view\engine\compiler::assign()
	 */
	public function assign($var, $val)
	{
		// TODO Auto-generated method stub
		$this->_assign[$var] = $val;
	}

	/**
	 * {@inheritDoc}
	 * @see \framework\view\engine\compiler::unassign()
	 */
	public function unassign($var)
	{
		// TODO Auto-generated method stub
		unset($this->_assign[$var]);
	}

	/**
	 * {@inheritDoc}
	 * @see \framework\view\engine\compiler::setTempalte()
	 */
	public function setTempalte($tempalte,$template_path)
	{
		// TODO Auto-generated method stub
		$this->_template_path = $template_path;
	}

	/**
	 * {@inheritDoc}
	 * @see \framework\view\engine\compiler::fetch()
	 */
	public function fetch()
	{
		ob_start();
		ob_implicit_flush(false);
		extract($this->_assign, EXTR_OVERWRITE);
		include($this->_template_path);
		return ob_get_clean();
	}

	/**
	 * {@inheritDoc}
	 * @see \framework\view\engine\compiler::getLeftDelimiter()
	 */
	public function getLeftDelimiter($quote = true)
	{
		// TODO Auto-generated method stub
		
	}

	/**
	 * {@inheritDoc}
	 * @see \framework\view\engine\compiler::getRightDelimiter()
	 */
	public function getRightDelimiter($quote = true)
	{
		// TODO Auto-generated method stub
		
	}

	/**
	 * {@inheritDoc}
	 * @see \framework\view\engine\compiler::setRightDelimiter()
	 */
	public function setRightDelimiter($rightDelimiter)
	{
		// TODO Auto-generated method stub
		
	}

	/**
	 * {@inheritDoc}
	 * @see \framework\view\engine\compiler::setLeftDelimiter()
	 */
	public function setLeftDelimiter($leftDelimiter)
	{
		// TODO Auto-generated method stub
		
	}

	/**
	 * {@inheritDoc}
	 * @see \framework\view\engine\compiler::addTemplatePath()
	 */
	public function addTemplatePath($dir)
	{
		// TODO Auto-generated method stub
		//$paths = get_include_path();
		//set_include_path($paths.';'.$dir);
		$this->_template_root = $dir;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \framework\view\engine\compiler::getTemplatePath()
	 */
	public function getTemplatePath()
	{
		// TODO Auto-generated method stub
		
	}

	
}