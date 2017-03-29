<?php
namespace framework\core;

use framework\core\log\LogLevel;

class log extends component
{
    
    public static $_logger;
    
    function __construct()
    {
    }
    
    function initlize()
    {
    }
    
    public static function getLogger()
    {
        if (empty(self::$_logger)) {
            self::$_logger = new logger(ROOT.'/log/');
        }
        return self::$_logger;
    }
    
    /**
     * 用于记录mysql的日志信息
     * @param unknown $sql
     * @param unknown $time
     */
    public static function mysql($sql, $time)
    {
        $logger = self::getLogger();
        $logger->log(LogLevel::INFO, $sql.'['.$time.']');
    }
}
