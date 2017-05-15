<?php
namespace framework\view;
use framework\core\component;

abstract class tag extends component
{
	abstract function compile($parameter,$compiler);
}