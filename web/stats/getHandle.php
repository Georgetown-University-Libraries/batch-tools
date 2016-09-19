<?php
include '../header.php';

$CUSTOM = custom::instance();

$type = util::getArg("type","");
$id =util::getArg("id","");
$uid =util::getArg("uid","");

$id = ($uid == "") ? $id : $uid;

if ($CUSTOM->isPdo()) {
	$arg = array(":id" => $id);
	$argp = ":id";
} else {
	$arg = array($id);
	$argp = "$1";
}

if ($type == "0") {
	$sql = <<< HERE
select h.handle 
from handle h
inner join item2bundle i2b on h.resource_id=i2b.item_id  
inner join bundle2bitstream b2b on i2b.bundle_id=b2b.bundle_id and b2b.bitstream_id = {$argp}
where h.resource_type_id=2
HERE;

    $handle = $CUSTOM->getQueryVal($sql, $arg);
    if ($handle != "") {
	    header("Location: /handle/" . $handle);
	    exit;	
    }
} elseif ($type == "2") {
	$sql = <<< HERE
select h.handle 
from handle h
where h.resource_id={$argp} and h.resource_type_id=2
HERE;

    $handle = $CUSTOM->getQueryVal($sql, $arg);
    if ($handle != "") {
	    header("Location: /handle/" . $handle);
	    exit;	
    }
} elseif ($type == "3") {
	$sql = <<< HERE
select h.handle 
from handle h
where h.resource_id={$argp} and h.resource_type_id=3
HERE;

    $handle = $CUSTOM->getQueryVal($sql, $arg);
    if ($handle != "") {
	    header("Location: /handle/" . $handle);
	    exit;	
    }
} elseif ($type == "4") {
	$sql = <<< HERE
select h.handle 
from handle h
where h.resource_id={$argp} and h.resource_type_id=4
HERE;

    $handle = $CUSTOM->getQueryVal($sql, $arg);
    if ($handle != "") {
	    header("Location: /handle/" . $handle);
	    exit;	
    }
} else {
header('Content-type: text/html; charset=UTF-8');
echo "<html><body>System is not configured to track id to handle</body></html>";	
}