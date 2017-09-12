<?php
namespace framework\view\tag;

use framework\view\tag;
use framework\view\compiler;

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