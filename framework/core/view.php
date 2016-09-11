<?php
namespace framework\core;
use framework\lib\config;

class view
{
	/**
	 * 模板文件路径
	 * @var unknown
	 */
	private $_template;
	
	/**
	 * 布局名
	 * @var unknown
	 */
	private $_layout;
	
	/**
	 * 模板文件字符编码
	 * @var unknown
	 */
	private $_charset;
	
	function __construct($template)
	{
	}
	
	/**
	 * 获取模板文件渲染后的html代码
	 * @return string
	 */
	function display()
	{
		
	}
}