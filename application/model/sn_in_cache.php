<?php
namespace application\model;

use framework\core\model;

class sn_in_cache extends model
{
    function __config()
    {
        $db = $this->getConfig('db');
        return $db['cloud_web_v2'];
    }
}
