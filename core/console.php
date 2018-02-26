<?php
namespace framework\core;

class console
{

	const SEPARATOR = "\r\n";

	const TEXT_COLOR_GREEN = "[32m";

	const TEXT_COLOR_RED = "[31m";

	const TEXT_COLOR_YELLOW = "[33m";

	const TEXT_COLOR_BLUE = "[34m";

	static function log($message, $color = self::TEXT_COLOR_GREEN)
	{
		if (env::php_sapi_name() == 'cli' || env::php_sapi_name() == 'socket')
		{
			echo chr(27) . $color . $message . chr(27) . "[0m" . self::SEPARATOR;
		}
	}
}