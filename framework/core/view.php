<?php
namespace framework\core;

use framework\view\engine;

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
	 * 模板引擎
	 * @var engine
	 */
	private $_engine;

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
		
		$this->_engine = new engine();
		$this->_engine->setTemplatePath(APP_ROOT . '/template/' . trim($this->_layout, '/\\'));
		$this->_engine->setTempalteName($this->_template);
		
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
		
		//$file = APP_ROOT . '/template/' . trim($this->_layout, '/\\') . '/' . $this->_template;
		//$this->_engine = new engine($file);
		$this->_engine->setTemplatePath(APP_ROOT . '/template/' . trim($this->_layout, '/\\'));
	}

	/**
	 * 设置模板文件
	 * @param unknown $template
	 */
	function setTemplate($template)
	{
		$this->_template = $template;
		$this->_engine->setTempalteName($template);
		//$file = APP_ROOT . '/template/' . trim($this->_layout, '/\\') . '/' . $this->_template;
		//$this->_engine = new engine($file);
	}

	/**
	 * 在模板中添加变量
	 *
	 * @param unknown $var        	
	 * @param unknown $val        	
	 */
	function assign($var, $val)
	{
		$this->_engine->assign($var,$val);
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
			$body = $this->_engine->fetch();
			return $body;
		}
		else
		{
			exit('file not exist');
		}
	}
}
