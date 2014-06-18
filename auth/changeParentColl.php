<?php
/*
User form for initiating the move of a collection to another community.  Note: in order to properly re-index the repository, 
DSpace will need to be taken offline after running this operation.
Author: Terry Brady, Georgetown University Libraries

License information is contained below.

Copyright (c) 2013, Georgetown University Libraries All rights reserved.

Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:

Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer. 
in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials 
provided with the distribution. THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, 
BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. 
IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES 
(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) 
HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) 
ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/
include '../web/header.php';

$CUSTOM = custom::instance();
$CUSTOM->getCommunityInit()->initCommunities();
$CUSTOM->getCommunityInit()->initCollections();

$status = "";
testArgs();
header('Content-type: text/html; charset=UTF-8');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<?php 
$header = new LitHeader("Move a Collection");
$header->litPageHeader();
?>
</head>
<body>
<?php $header->litHeader(array());?>
<div id="formChangeParent">
<form method="POST" action="" onsubmit="jobQueue();return true;">
<p>Use this option to move a collection to a different community</p>
<div id="status"><?php echo $status?></div>
<?php collection::getCollectionIdWidget(util::getPostArg("child",""), "child", " to be moved*");?>
<?php collection::getSubcommunityIdWidget(util::getPostArg("parent",""), "parent", " to use as a destination*");?>
<p align="center">
	<input id="changeParentSubmit" type="submit" title="Submit Form" disabled/>
</p>
<p><em>* Required field</em></p>
</form>
</div>
<?php $header->litFooter();?>
</body>
</html>

<?php 
function checkedArr($arr, $value) {
	echo in_array($value,$arr) ? "checked" : "";
}
function checkedPost($name, $value) {
	echo (util::getPostArg($name, "") == $value) ? "checked" : "";
}
function uncheckedPost($name, $value) {
	if (count($_POST) > 0){
		echo (util::getPostArg($name, "") == $value) ? "checked" : "";
	} else {
		echo "checked";
	}
}

function testArgs(){
	global $status;
	$CUSTOM = custom::instance();
	$dspaceBatch = $CUSTOM->getDspaceBatch();
	$bgindicator =  $CUSTOM->getBgindicator();
	
	if (count($_POST) == 0) return;
	$child = util::getPostArg("child","");

	if (!is_numeric($child)) return;
	$child = intval($child);

	if (!isset(collection::$COLLECTIONS[$child])) return;
	$coll = collection::$COLLECTIONS[$child];
	$currparent =$coll->getParent()->community_id;
	$parent = util::getPostArg("parent","");

	if (!is_numeric($parent)) return;
	$parent = intval($parent);
	
	if ($parent == $currparent) return;
	
	$args = $child . " " . $currparent . " " . $parent;

	$u = escapeshellarg($CUSTOM->getCurrentUser());
	$cmd = <<< HERE
{$u} gu-change-coll-parent {$args}
HERE;

    //echo($dspaceBatch . " " . $cmd);
    exec($dspaceBatch . " " . $cmd . " " . $bgindicator);
    header("Location: ../web/queue.php");
}

?>