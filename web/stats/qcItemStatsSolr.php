<?php
include '../header.php';

ini_set('max_execution_time', 120);
header('Content-type: application/json');


$CUSTOM = custom::instance();
$shards = $CUSTOM->getSolrShards();
$qroot  = $CUSTOM->getSolrPath() . "/statistics/select?shards={$shards}&rows=0&wt=json";

$handle=util::getArg("handle","");
$item="";
if ($handle == '') {
	$item = "*";
} else {
	$item = -1;
	$item = $CUSTOM->getQueryVal("select resource_id from handle where handle=:h", array(":h" => $handle));
	if ($item == null) {
		$item = -1;
	} else {
	}
}
$dur=util::getArg("dur","YEAR");
if ($dur != "YEAR" && $dur != "MONTH") {
	$dur = "YEAR";
}
$num=util::getArg("num","5");
if (!is_numeric($num)) {
	$num = 5;
} else if ($num < 0 || $num > 200) {
	$num = 5;
}
$filterbots=(util::getArg("filterbots","") != "");
$type=util::getArg("type","item");
if ($type != "item" && $type != "bit") {
	$type = "item";
}
$url = $qroot;
$url .= ($type == "item") ? "&q=type:2+AND+id:{$item}" : "&q=type:0+AND+bundleName:ORIGINAL+AND+owningItem:{$item}";
$url .= $filterbots ? $CUSTOM->getStatsBotsStr() : "";
$url .= "&facet=true&facet.date=time&facet.date.start=NOW/{$num}/DAY-{$num}{$dur}S&facet.date.end=NOW&facet.date.gap=%2B1{$dur}";
echo file_get_contents($url);
