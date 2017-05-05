<?php
namespace framework\view;

class dom
{
	public $id = '';
	
	/**
	 * 当节点为闭合节点的时候为包裹的内容 否则为value值
	 * @var string
	 */
	public $text = '';
	
	/**
	 * 存储了dom节点的所有style属性
	 * @var array
	 */
	public $style = array();
	
	/**
	 * 包含了额外的属性组
	 * @example
	 * 	data-id=1  array('data-id',1);
	 * @var array
	 */
	protected $_prop = array();
	
	/**
	 * 生成的标签是否需要结束标签
	 * 默认是需要结束标签 
	 * 不需要结束标签的比如img或者hr等
	 * 重载这个变量为false
	 * @example false;
	 * @var string
	 */
	protected $_needClose = true;
	
	/**
	 * 
	 * @param string|html $string
	 */
	function __construct($string = '',$attributes = array())
	{
		if ($string instanceof dom)
		{
			$this->text .= $string.'';
		}
		else if (is_array($string))
		{
			array_walk($string, function($s){
				return $s instanceof dom?$s->__toString():$s;
			});
			$this->text = implode('',$string);
		}
		else if (is_scalar($string))
		{
			$this->text = $string;
		}
		
		
		if (isset($attributes['text']))
		{
			$this->text = $attributes['text'];
			unset($attributes['text']);
		}
		if (isset($attributes['class']))
		{
			$this->prop('class', $attributes['class'],true);
			unset($attributes['class']);
		}
		if (isset($attributes['style']))
		{
			$this->style($attributes['style']);
			unset($attributes['style']);
		}
		$this->prop($attributes);
	}
	
	function __toString()
	{
		$_label = str_replace(__NAMESPACE__.'\\', '', get_class($this)) ;
		$style = empty($this->getStyleString())?'':' style="'.$this->getStyleString().'"';
		$prop = empty($this->getPropString())?'':' '.$this->getPropString();
		$text = $this->text;
		if ($this->_needClose)
		{
			$_content = '<'.$_label.$style.$prop.'>'.$text.'</'.$_label.'>';
		}
		else
		{
			$value = empty($text)?'':(' value="'+$text+'"');
			$_content = '<'.$_label.$style.$prop.$value.' />';
		}
		
		return $_content;
	}
	
	/**
	 * 设置/添加/删除 dom节点的style属性
	 * 当value设置为空的时候为删除
	 * key参数可以是一个数组用于批量添加设置style
	 * 已经存在的style会被覆盖掉
	 * @param unknown $key
	 * @param string $value
	 * @param boolean $important 属性是否是important
	 */
	function style($key,$value = '',$important = false)
	{
		if (is_scalar($key))
		{
			if (!empty($value))
			{
				$this->style[$key] = $value.($important?' !important':'');
			}
			else
			{
				unset($this->style[$key]);
			}
		}
		else if (is_array($key))
		{
			$this->style = array_merge($this->style,$key);
		}
	}
	
	/**
	 * 获取dom节点的style字符串
	 * 中间用空格分开 
	 * @param string $separetor 分隔符  默认一个空格
	 * @return string  display:block;
	 */
	protected function getStyleString($separetor = ' ')
	{
		$string = array();
		foreach ($this->style as $key => $value)
		{
			$string[] = $key.':'.$value.';';
		}
		return implode($separetor,$string);
	}
	
	/**
	 * 在dom节点上增加属性
	 * @param unknown $key
	 * @param unknown $value
	 * @param string $append 默认值false  是否通过追加的形式 通常用于class属性
	 */
	public function prop($key,$value = '',$append = false)
	{
		//按照删除
		if (is_numeric($key) && !empty($value))
		{
			$this->prop($value);
		}
		else if (is_array($key))
		{
			foreach ($key as $k => $v)
			{
				$this->prop($k,$v);
			}
		}
		else if (is_scalar($key))
		{
			if (!empty($value))
			{
				if ($append)
				{
					if (isset($this->_prop[$key]))
					{
						$this->_prop[$key] .= ' '.$value;
					}
					else
					{
						$this->_prop[$key] = $value;
					}
				}
				else
				{
					$this->_prop[$key] = $value;
				}
			}
			else
			{
				unset($this->_prop[$key]);
			}
		}
	}
	
	/**
	 * 获取dom节点的属性字符串 
	 * @param $seperator 分隔符 默认空格
	 * @return string
	 */
	protected function getPropString($seperator = ' ')
	{
		$string = array();
		foreach ($this->_prop as $key => $value)
		{
			$string[] = $key.'="'.$value.'" ';
		}
		return implode($seperator,$string);
	}
}