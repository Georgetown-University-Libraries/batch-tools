<?php
/*
User form for initiating the move of a community to another community.
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

$status = "";
$hasPerm = $CUSTOM->isUserCollectionOwner();
if ($hasPerm) testArgs();
header('Content-type: text/html; charset=UTF-8');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<?php 
$header = new LitHeader("Move a Community");
$header->litPageHeader();
?>
</head>
<body>
<?php $header->litHeaderAuth(array(), $hasPerm);?>
<div id="formChangeParent">
<form method="POST" action="" onsubmit="jobQueue();return true;">
<p>Use this option to move a community under another community</p>
<div id="status"><?php echo $status?></div>
<?php collection::getSubcommunityIdWidget(util::getPostArg("child",""), "child", " to be moved*");?>
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

// test if child community is an ancestor of parent community (SD-51)
function isAncestor($childId, $parentId) {
	if(!isset(community::$COMMUNITIES[$parentId])) return false;
	
	$parent = community::$COMMUNITIES[$parentId];
	$next_parent_id = $parent->parent_comm_id;
	
	//test if parent is a top level node
	if ($next_parent_id == $parentId) return false;
	
	//test if child community is the parent of parent community
	if ($next_parent_id == $childId) return true;
	
	//recursively test parent community's parent objects
	return isAncestor($childId, $next_parent_id);
};


function testArgs(){
	global $status;
	$CUSTOM = custom::instance();
	$dspaceBatch = $CUSTOM->getDspaceBatch();
	$bgindicator =  $CUSTOM->getBgindicator();
	
	if (count($_POST) == 0) return;
	$child = util::getPostArg("child","");
	if (!util::isIdOrUuid($child)) return;
	$child = is_numeric($child) ? intval($child) : $child;

	$parent = util::getPostArg("parent","");

    if (!util::isIdOrUuid($parent)) return;
	$parent = is_numeric($parent) ? intval($parent) : $parent;

	$currparent = "";

	$note ="";
	
    foreach(community::$COMBO as $obj) {
    	$cmp = $obj->community_id;
    	$cmp = is_numeric($cmp) ? intval($cmp) : $cmp;
    	 if ($cmp == $child) {
    	 	$note = "m";
    		$currparent = $obj->getParent()->community_id;
    		break;
    	}
    }
    
    $note .= count(community::$COMBO); 
	
    if (($child == "") || ($parent == "") || ($currparent == "")) {
    	$status = "Invalid id:  child: {$child}, parent: {$parent}, currparent: {$currparent}. {$note}";
    	return;
    };

// test if the child community is the same as the parent community (SD-51)
    if ($child == $parent) {
    	$status = "Invalid operation:  child community (id {$child}) and parent community (id {$parent}) are the same.";
    	return;
    }

// test if the child community is an ancestor of the parent community (SD-51)
	if (isAncestor($child, $parent)) {
		$status = "Invalid operation:  child community (id {$child}) is an ancestor of the parent community (id {$parent})";
		return;
	}
		

	$args = escapeshellarg($child) . " " . escapeshellarg($currparent) . " " . escapeshellarg($parent);
    	
	$u = escapeshellarg($CUSTOM->getCurrentUser());
	$cmd = <<< HERE
{$u} gu-change-parent {$args}
HERE;

    //echo($dspaceBatch . " " . $cmd);
    exec($dspaceBatch . " " . $cmd . " " . $bgindicator);
    header("Location: ../web/queue.php");
}

?>