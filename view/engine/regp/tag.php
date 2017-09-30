<?php
namespace framework\view\engine\regp;

use framework\core\component;

abstract class tag extends component
{

	abstract function compile($parameter,compiler $compiler);
}