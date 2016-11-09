<?php
include './xhprof.php';

//定义根目录
!defined('ROOT') & define('ROOT', __DIR__);
//定义框架的目录
!defined('SYSTEM_ROOT') & define("SYSTEM_ROOT",ROOT.'/framework');

//定义APP的目录
!defined('APP_ROOT') & define("APP_ROOT",ROOT.'/application');
//定义app的名称  app的代码必须放在app名称对应的文件夹里面
!define("APP_NAME", "application");

define('DEBUG', false);

//载入框架
include SYSTEM_ROOT.'/framework.php';

//增加对cli模式的支持
$argc = isset($argc)?$argc:0;
$argv = isset($argv)?$argv:array();

$framework = new framework($argc,$argv);
$app = $framework->createApplication(APP_NAME,APP_ROOT);
$app->run();
