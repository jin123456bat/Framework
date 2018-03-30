<?php
namespace framework\vendor\formatter;

/**
 * html代码格式化
 * 用于显示在浏览器上
 * @author jin12
 *
 */
class html extends formatter
{
	function __construct($string,$charset = NULL)
	{
		parent::__construct($string,$charset);
		
		//去除代码中的所有格式
		$this->_code = str_replace(array(
			"\n",
			"\r",
			"\t",
		), array(
			"",
			"",
			"",
		), htmlspecialchars_decode($this->_code));
		
		//增加回车
		$this->_code = str_replace(array(
			'>',
			'{',
			'}',
			';',
			'*/',
			'</',
		), array(
			">\n",
			"{\n",
			"}\n",
			";\n",
			"*/\n",
			"\n</",
		), $this->_code);
		
		//多余的回车合并
		$this->_code = str_replace(array(
			"\n\n",
		), array(
			"\n",
		), $this->_code);
	}
	
	/**
	 * 格式化代码
	 * @param unknown $string
	 */
	function getCode()
	{
		$string = explode("\n", $this->_code);
		$flag = 0;
		foreach ($string as &$s)
		{
			$t = trim($s);
			if (!empty($t))
			{
				if ($flag > 0)
				{
					$s = str_repeat("\t", $flag).$s;
				}
				
				$indent = self::indent($t, $this->_charset);
				if($indent > 0)
				{
					$flag++;
				}
				else if ($indent < 0)
				{
					$flag--;
					if ($s[0] == "\t")
					{
						$s = mb_substr($s, 1,null,$this->_charset);
					}
				}
			}
		}
		$string = implode("\n",$string);
		
		return htmlspecialchars($string);
	}
	
	function indent($line)
	{
		$indent_tags = array(
			'html',
			'div',
			'head',
			'body',
			'title',
			'span',
			'table',
			'tr',
			'td',
		);
		
		//兼容html代码中的css部分
		$last_word = $line[mb_strlen($line,$this->_charset)-1];
		$first_word = $line[0];
		
		if ($last_word == '{')
		{
			return 1;
		}
		else if ($first_word == '}')
		{
			return -1;
		}
		
		//对于html代码的标签的缩进
		foreach ($indent_tags as $tag)
		{
			//判断是否是结束符
			$pattern = '/< *\/ *'.$tag.' *>/i';
			if (preg_match($pattern, $line))
			{
				return -1;
			}
			
			//判断是否是开始符
			$pattern= '/< *'.$tag.'[^>]*>/i';
			if (preg_match($pattern, $line))
			{
				return 1;
			}
		}
		
		return 0;
	}
}