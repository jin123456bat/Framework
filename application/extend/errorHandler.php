<?php
namespace applicaton\extend;

use framework\core\component;

class errorHandler extends component
{
    function run($errno, $errstr, $errfile, $errline, array $errcontext)
    {
        $file = ROOT.'/error.log';
        if (!(error_reporting() & $errno)) {
            return ;
        }
        $data = '['.date('Y-m-d H:i:s').'] '.$errno.':'.$errstr.' In File '.$errfile.' On Line '.$errline;
        file_put_contents($file, $data, FILE_APPEND);
    }
}
