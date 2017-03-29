<?php
namespace framework\core;

use framework\lib\data;

class entity extends data
{
    public $_data;
    
    function __construct($data = null)
    {
        $this->_data = $data;
    }
}
