<?php
namespace framework\core;

use framework\view\engine;
use framework;
use framework\vendor\compress;

class view extends response
{

	/**
	 * 模板文件路径
	 * 
	 * @var unknown
	 */
	private $_template;
	
	/**
	 * 模板文件所在目录
	 * @var unknown
	 */
	private $_template_dir;

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
	 * 
	 * @var engine
	 */
	private $_engine;

	function __construct($template, $layout = null)
	{
		$this->_engine = new engine();
		
		if (file_exists($template))
		{
			$this->_template = $template;
			$this->_engine->setTemplate($template);
		}
		else
		{
			if ($layout !== null)
			{
				$this->_layout = $layout;
			}
			else
			{
				$view = $this->getConfig('view');
				$this->_layout = isset($view['layout']) ? $view['layout'] : 'layout';
			}
			
			$this->_template = $template;
			$this->_template_dir = APP_ROOT . '/template/' . trim($this->_layout, '/\\');
			$this->_engine->setTemplatePath($this->_template_dir);
			$this->_engine->setTempalteName($this->_template);
		}
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
		$this->_engine->setTemplatePath(APP_ROOT . '/template/' . trim($this->_layout, '/\\'));
	}

	/**
	 * 设置模板文件
	 * 
	 * @param unknown $template        
	 */
	function setTemplate($template)
	{
		$this->_template = $template;
		$this->_engine->setTempalteName($template);
	}

	/**
	 * 在模板中添加变量
	 * 
	 * @param unknown $var        
	 * @param unknown $val        
	 */
	function assign($var, $val)
	{
		$this->_engine->assign($var, $val);
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
		$body = $this->_engine->fetch();
		// 自动开启html压缩
		$view = $this->getConfig('view');
		if (is_bool($view['compress']) && $view['compress'] || (is_array($view['compress']) && in_array($this->_template, $view['compress'], true)))
		{
			if (! isset($view['no_compress']) || ! in_array($this->_template, $view['no_compress'], true))
			{
				if (class_exists('\framework\vendor\compress', true))
				{
					$body = \framework\vendor\compress::html($body);
				}
			}
		}
		
		
		
		$assets = self::getConfig('assets');
		if (isset($assets['global']['head']['css']) && !empty($assets['global']['head']['css']))
		{
			$filepath = rtrim($this->_template_dir,'/').'/'.$this->_template;
			$filepath = realpath($filepath);
			
			foreach ($assets['global']['head']['css'] as $css => $expect)
			{
				if (is_int($css))
				{
					$path = assets::css($expect);
				}
				else if (!empty($expect))
				{
					$path = assets::css($css);
					
					$expect = self::setVariableType($expect,'a');
					$expect_complete_dir = array();
					foreach ($expect as $file)
					{
						$templatepath = realpath(rtrim($this->_template_dir,'/').'/'.$file);
						$expect_complete_dir[] = $templatepath;
					}
					
					if (in_array($filepath, $expect_complete_dir,true))
					{
						continue;
					}
				}
				
				$path = assets::css($css);
				$label = '<link rel="stylesheet" href="'.$path.'" type="text/css" media="all" />';
				$body = str_replace('</head>', $label.'</head>', $body);
			}
		}
		
		if (isset($assets['global']['head']['js']) && !empty($assets['global']['head']['js']))
		{
			$filepath = rtrim($this->_template_dir,'/').'/'.$this->_template;
			$filepath = realpath($filepath);
			
			foreach ($assets['global']['head']['js'] as $js => $expect)
			{
				if (is_int($js))
				{
					$path = assets::js($expect);
				}
				else if (!empty($expect))
				{
					$path = assets::js($js);
					
					$expect = self::setVariableType($expect,'a');
					$expect_complete_dir = array();
					foreach ($expect as $file)
					{
						$templatepath = realpath(rtrim($this->_template_dir,'/').'/'.$file);
						$expect_complete_dir[] = $templatepath;
					}
					
					if (in_array($filepath, $expect_complete_dir,true))
					{
						continue;
					}
				}
				
				$label = '<script src="'.$path.'" type="text/javascript"></script>';
				$body = str_replace('</head>', $label.'</head>', $body);
			}
		}
		
		if (isset($assets['global']['end']['js']) && !empty($assets['global']['end']['js']))
		{
			$filepath = rtrim($this->_template_dir,'/').'/'.$this->_template;
			$filepath = realpath($filepath);
			
			foreach ($assets['global']['end']['js'] as $js => $expect)
			{
				if (is_int($js))
				{
					$path = assets::js($expect);
				}
				else if (!empty($expect))
				{
					$path = assets::js($js);
					
					$expect = self::setVariableType($expect,'a');
					$expect_complete_dir = array();
					foreach ($expect as $file)
					{
						$templatepath = realpath(rtrim($this->_template_dir,'/').'/'.$file);
						$expect_complete_dir[] = $templatepath;
					}
					
					if (in_array($filepath, $expect_complete_dir,true))
					{
						continue;
					}
				}
				
				$label = '<script src="'.$path.'" type="text/javascript" /></script>';
				$body = str_replace('</body>', $label.'</body>', $body);
			}
		}
		
		return $body;
	}
}
