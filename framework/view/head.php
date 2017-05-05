<?php
namespace framework\view;

class head extends dom
{	
	function css($path)
	{
		$this->text = new link($path);
	}
	
	
}