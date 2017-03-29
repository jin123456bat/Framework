<?php
namespace framework\core;

use framework\core\log\AbstractLogger;

class logger extends AbstractLogger
{
    function __construct($log_path)
    {
        $this->_log_path = $log_path;
        if (!file_exists($this->_log_path)) {
            mkdir($this->_log_path, 0777, true);
        }
    }
    
    /**
     * {@inheritDoc}
     * @see \framework\core\log\LoggerInterface::emergency()
     */
    public function emergency($message, array $context = array())
    {
        // TODO Auto-generated method stub
    }

    /**
     * {@inheritDoc}
     * @see \framework\core\log\LoggerInterface::alert()
     */
    public function alert($message, array $context = array())
    {
        // TODO Auto-generated method stub
    }

    /**
     * {@inheritDoc}
     * @see \framework\core\log\LoggerInterface::critical()
     */
    public function critical($message, array $context = array())
    {
        // TODO Auto-generated method stub
    }

    /**
     * {@inheritDoc}
     * @see \framework\core\log\LoggerInterface::error()
     */
    public function error($message, array $context = array())
    {
        // TODO Auto-generated method stub
    }

    /**
     * {@inheritDoc}
     * @see \framework\core\log\LoggerInterface::warning()
     */
    public function warning($message, array $context = array())
    {
        // TODO Auto-generated method stub
    }

    /**
     * {@inheritDoc}
     * @see \framework\core\log\LoggerInterface::notice()
     */
    public function notice($message, array $context = array())
    {
        // TODO Auto-generated method stub
    }

    /**
     * {@inheritDoc}
     * @see \framework\core\log\LoggerInterface::info()
     */
    public function info($message, array $context = array())
    {
        // TODO Auto-generated method stub
    }

    /**
     * {@inheritDoc}
     * @see \framework\core\log\LoggerInterface::debug()
     */
    public function debug($message, array $context = array())
    {
        // TODO Auto-generated method stub
    }
}
