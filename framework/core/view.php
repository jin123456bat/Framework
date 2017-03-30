<?php
namespace framework\core;

class view extends response
{

	/**
	 * 模板文件路径
	 * 
	 * @var unknown
	 */
	private $_template;

	/**
	 * 布局名
	 * 
	 * @var unknown
	 */
	private $_layout;

	/**
	 * 模板文件字符编码
	 * 
	 * @var unknown
	 */
	private $_charset;

	/**
	 * 模板中的变量
	 * 
	 * @var unknown
	 */
	private $_variables = array();

	/**
	 * 模板中的函数
	 * 
	 * @var unknown
	 */
	private $_functions = array();

	function __construct($template, $layout = null)
	{
		$app = $this->getConfig('app');
		if ($layout !== null)
		{
			$this->_layout = $layout;
		}
		else
		{
			$this->_layout = isset($app['layout']) ? $app['layout'] : 'layout';
		}
		
		$this->_template = $template;
		parent::__construct();
	}

	/**
	 * 设置模板文件夹
	 * 
	 * @param unknown $layout        	
	 */
	function setLayout($layout)
	{
		$this->_layout = $layout;
	}

	function setTemplate($template)
	{
		$this->_template = $template;
	}

	/**
	 * 在模板中添加变量
	 * 
	 * @param unknown $var        	
	 * @param unknown $val        	
	 */
	function assign($var, $val)
	{
		$this->_variables[$var] = $val;
	}

	function functions($var, $callback)
	{
		if (is_callable($callback))
		{
			$this->_functions[$var] = $callback;
		}
	}

	/**
	 * 重写getBody返回响应内容
	 * 
	 * {@inheritdoc}
	 *
	 * @see \framework\core\response::getBody()
	 */
	function getBody()
	{
		$file = APP_ROOT . '/template/' . trim($this->_layout, '/\\') . '/' . $this->_template;
		if (file_exists($file))
		{
			ob_start();
			extract($this->_variables);
			extract($this->_functions);
			include $file;
			$body = ob_get_contents();
			ob_end_clean();
			return $body;
		}
	}
}
