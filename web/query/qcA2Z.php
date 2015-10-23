<?php
include '../header.php';

$CUSTOM = custom::instance();
$CUSTOM->getCommunityInit()->initCommunities();
$CUSTOM->getCommunityInit()->initCollections();
$CUSTOM->initHierarchy();

header('Content-type: text/html; charset=UTF-8');

function namecmp($a,$b) {
	$aa = strtolower($a->sname);
	$bb = strtolower($b->sname);
	if ($aa == $bb) return 0;
	return ($aa < $bb) ? -1 :1;
}

uasort(hierarchy::$OBJECTS, "namecmp");
$URL = "/"; 

?>
<html>
<head>
<?php 
$header = new LitHeader("Communities and Collections A-Z List");
$header->litPageHeader();
?>
</head>
<body>
<?php $header->litHeader(array());?>
<div>
<?php
foreach(hierarchy::$OBJECTS as $coll) {
	echo "<div><a href='" .$URL . $coll->handle . "'>" . $coll->sname . "</a></div>";
}
?>

</div>
<?php $header->litFooter();?>
</body>
</html>

