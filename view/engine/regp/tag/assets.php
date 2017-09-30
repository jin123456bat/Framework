<?php
namespace framework\view\engine\regp\tag;

use framework\view\engine\regp\tag;
use framework\view\engine\regp\compiler;

class assets extends tag
{

	function compile($parameter,compiler $compiler)
	{
		$view = self::getConfig('view');
		$dir = SYSTEM_ROOT.'/assets';
		
		if (isset($parameter['file']))
		{
			$file = $dir.'/'.$parameter['file'];
			if (file_exists($file))
			{
				return $path;
			}
		}
	}
}