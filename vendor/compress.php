<?php
namespace framework\vendor;

use framework\core\component;

class compress extends component
{

	/**
	 * css文件压缩，返回压缩后的内容
	 * 
	 * @param unknown $content
	 *        假如$content是一个文件的路径 则以文件内容压缩 否则直接以content压缩
	 * @return mixed|string
	 */
	static function css($content)
	{
		if (file_exists($content))
		{
			$file = new file($content);
			$content = $file->content();
		}
		
		// 去掉注释
		$content = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $content);
		// 去除回车
		$content = str_replace(array(
			"\n",
			"\r",
			"\r\n",
			"\t"
		), '', $content);
		// 去除连续的空格
		$count = 0;
		do
		{
			$content = str_replace(array(
				'  '
			), array(
				' '
			), $content, $count);
		}
		while (! empty($count));
		// 去除括号前后的空格
		$replace = array(
			'{',
			'}',
			':',
			';'
		);
		foreach ($replace as $word)
		{
			$content = str_replace(array(
				' ' . $word . ' ',
				$word . ' ',
				' ' . $word
			), $word, $content);
		}
		// 将最后一个分号去掉
		$content = str_replace(';}', '}', $content);
		return $content;
	}

	/**
	 * html压缩，返回压缩后的内容
	 * 
	 * @param unknown $content
	 *        假如$content是一个文件的路径 则以文件内容压缩 否则直接以content压缩
	 * @return string
	 */
	static function js($content)
	{
		if (file_exists($content))
		{
			$file = new file($content);
			$content = $file->content();
		}
		
		$content = preg_replace('#\/\/[^\n]*#', '', $content); // 剔除js行注释
		$content = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $content); // 块注释
		                                                                        // 去除回车
		$content = str_replace(array(
			"\n",
			"\r",
			"\r\n",
			"\t"
		), '', $content);
		// 去除连续的空格
		$count = 0;
		do
		{
			$content = str_replace(array(
				'  '
			), array(
				' '
			), $content, $count);
		}
		while (! empty($count));
		// 去除括号前后的空格
		$replace = array(
			'{',
			'}',
			':',
			';',
			'(',
			')',
			',',
			'=',
			'+',
			'-',
			'*',
			'/',
			'<',
			'>',
			'>=',
			'<=',
			'&',
			'?',
			'|',
			'[',
			']'
		);
		foreach ($replace as $word)
		{
			$content = str_replace(array(
				' ' . $word . ' ',
				$word . ' ',
				' ' . $word
			), $word, $content);
		}
		return $content;
	}

	/**
	 * html压缩，返回压缩后的内容
	 * 
	 * @param string $content
	 *        假如$content是一个文件的路径 则以文件内容压缩 否则直接以content压缩
	 * @return string
	 */
	static function html($content)
	{
		if (file_exists($content))
		{
			$file = new file($content);
			$content = $file->content();
		}
		
		$stime = microtime();
		
		// 处理css
		$content = preg_replace_callback('!(?<start><style(.|\s)*?>)(?<content>(.|\s)*?)(?<end></style>)!is', function ($match) {
			return $match['start'] . self::css($match['content']) . $match['end'];
		}, $content);
		// 处理js
		$content = preg_replace_callback('!(?<start><script(.|\s)*?>)(?<content>(.|\s)*?)(?<end></script>)!is', function ($match) {
			return $match['start'] . self::js($match['content']) . $match['end'];
		}, $content);
		
		// 处理html部分
		// 去除连续的空格
		$count = 0;
		do
		{
			$content = str_replace(array(
				'  ',
				"\t"
			), array(
				' '
			), $content, $count);
		}
		while (! empty($count));
		
		// 去除特殊字符
		$array = array(
			"\r",
			"\n",
			"\r\n"
		);
		$count = 0;
		do
		{
			$content = str_replace($array, '', $content, $count);
		}
		while (! empty($count));
		
		// 删除html的注释
		$content = preg_replace_callback('/<!--(.|\s)*?-->/', function ($match) {
			return '';
		}, $content);
		
		$etime = microtime();
		
		return $content;
	}
}