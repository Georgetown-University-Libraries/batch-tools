<?php
include '../header.php';

ini_set('max_execution_time', 120);
header('Content-type: application/json');


$CUSTOM = custom::instance();
$handle=trim(util::getArg("handle",""));
$arr = $CUSTOM->getExtConfig();
$key = $arr["GANAKEY"];
$id  = $arr["GANAID"];

$url = "https://www.googleapis.com/analytics/v3/data/ga?ids=ga%3A{$id}&start-date=3000daysAgo&end-date=yesterday&metrics=ga%3Apageviews&dimensions=ga%3Ayear%2Cga%3Amonth&filters=ga%3ApagePath%3D%3D%2Fhandle%2F{$handle}&key={$key}";
echo file_get_contents($url);
