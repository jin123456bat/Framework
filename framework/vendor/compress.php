<?php
namespace framework\vendor;

use framework\core\component;

class compress extends component
{
	/**
	 * css文件压缩，返回压缩后的内容
	 * @param unknown $path
	 * @return mixed|string
	 */
	static function css($path)
	{
		$file = new file($path,false);
		if(!$file->hasError() && $file->readable())
		{
			$content = $file->content();
			//去掉注释
			$content = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $content);
			//去除回车
			$content = str_replace(array("\n","\r","\r\n","\t"), '', $content);
			//去除连续的空格
			$count = 0;
			do{
				$content = str_replace(array('  '),array(' '), $content,$count);
			}while (!empty($count));
			//去除括号前后的空格
			$replace = array(
				'{','}',':',';'
			);
			foreach ($replace as $word)
			{
				$content = str_replace(array(' '.$word.' ',$word.' ',' '.$word), $word, $content);
			}
			//将最后一个分号去掉
			$content = str_replace(';}', '}', $content);
			return $content;
		}
		return '';
	}
	
	/**
	 * js文件压缩，返回压缩后的内容
	 * @param unknown $path
	 * @return string
	 */
	static function js($path)
	{
		$file = new file($path,false);
		if(!$file->hasError() && $file->readable())
		{
			$content = $file->content();
			$content = preg_replace('#\/\/[^\n]*#','',$content);//剔除js行注释
			$content = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $content);//块注释
			//去除回车
			$content = str_replace(array("\n","\r","\r\n","\t"), '', $content);
			//去除连续的空格
			$count = 0;
			do{
				$content = str_replace(array('  '),array(' '), $content,$count);
			}while (!empty($count));
			//去除括号前后的空格
			$replace = array(
				'{','}',':',';','(',')',',','=','+','-','*','/','<','>','>=','<=','&','?','|','[',']'
			);
			foreach ($replace as $word)
			{
				$content = str_replace(array(' '.$word.' ',$word.' ',' '.$word), $word, $content);
			}
			return $content;
		}
		return '';
	}
	
	/**
	 * html压缩，返回压缩后的内容 尚未完成
	 * @param unknown $path
	 * @return string
	 */
	static function html($path)
	{
		$file = new file($path,false);
		if(!$file->hasError() && $file->readable())
		{
			$content = $file->content();
			//去除连续的空格
			$count = 0;
			do{
				$content = str_replace(array('  '),array(' '), $content,$count);
			}while (!empty($count));
			
			return $content;
		}
		return '';
	}
}