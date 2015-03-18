<?php
/**
 * YAF 程序入口
 */
date_default_timezone_set('PRC');
define('APPLICATION_PATH', dirname(__FILE__));
require APPLICATION_PATH .'/vendor/autoload.php';
$application = new Yaf_Application( APPLICATION_PATH . "/conf/application.ini");
$application->bootstrap()->run();
