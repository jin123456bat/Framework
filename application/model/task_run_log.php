<?php
namespace application\model;

use application\extend\model;

class task_run_log extends model
{
    function __config()
    {
        $db = $this->getConfig('db');
        return $db['cloud_web_v2'];
    }
}
