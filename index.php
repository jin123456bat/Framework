<?php

//定义根目录
!defined('ROOT') & define('ROOT', __DIR__);
//定义框架的目录
!defined('SYSTEM_ROOT') & define("SYSTEM_ROOT",ROOT.'/system');
//定义APP的目录
!defined('APP_ROOT') & define("APP_ROOT",ROOT.'/application');
//定义app的名称
!define("APP_NAME", "application");


include SYSTEM_ROOT.'/framework.php';

$framework = new framework();
$app = $framework->createApplication(APP_NAME,APP_ROOT);
$app->run();
