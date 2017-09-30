<?php
namespace framework\view\tag;

use framework\view\engine\regp\tag;
use framework\view\engine\regp\compiler;

class import extends tag
{

	function compile($parameter,compiler $compiler)
	{
		if (isset($parameter['file']))
		{
			// 在目录中搜索
			foreach ($compiler->getTemplatePath() as $path)
			{
				$file = rtrim($path, '/') . '/' . ltrim($parameter['file'], '/');
				if (file_exists($file) && is_readable($file))
				{
					return file_get_contents($file);
				}
			}
			
			// 不存在目录中 猜测可能是绝对路径
			$file = $parameter['file'];
			if (file_exists($file) && is_readable($file))
			{
				return file_get_contents($file);
			}
		}
	}
}