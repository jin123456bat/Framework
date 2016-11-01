<?php
namespace framework\core\log;
interface LoggerAwareInterface
{
	function setLogger(LoggerInterface $logger);
}