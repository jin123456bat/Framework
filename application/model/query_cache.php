<?php
namespace application\model;

use framework\core\model;

class query_cache extends model
{
    function __config()
    {
        $db = $this->getConfig('db');
        return $db['cloud_web_v2'];
    }
}
