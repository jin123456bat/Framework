<?php
namespace framework\view;
class meta extends dom
{
	
	protected $_needClose = false;
	
	/**
	 * 
	 * @param unknown $name
	 * @param unknown $content
	 * @param string $http_equiv 为true的话生成http_equiv为name的meta标签
	 */
	function __construct($name,$content,$http_equiv = false,$scheme = '')
	{
		if ($http_equiv)
		{
			$this->prop('http_equiv',$name);
		}
		else
		{
			$this->prop('name',$name);
		}
		$this->prop('content',$content);
		
		if (!empty($scheme))
		{
			$this->prop('scheme',$scheme);
		}
	}
}