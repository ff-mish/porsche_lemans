<?php

// change the following paths if necessary
$yii=dirname(__FILE__).'/yii/yii.php';
if ($_GET["v2"] == 1) {
  $config=dirname(__FILE__).'/protected/config/main_v2.php';
}
else {
  $config=dirname(__FILE__).'/protected/config/main.php';
}

// remove the following lines when in production mode
defined('YII_DEBUG') or define('YII_DEBUG',true);
// specify how many levels of call stack should be shown in each log message
defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL',3);

function debug_info($var) {
//  $log = fopen("./debug.info", "a+");
//  $out = print_r($var, TRUE);
//  $out = "Time: ". date("Y-m-d H:i:s") . $out;
//  fwrite($log, $out."\r\n");
//  fclose($log);
}

require_once($yii);
Yii::createWebApplication($config)->run();
