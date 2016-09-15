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
$isD6 = ($CUSTOM->getDSpaceVer() >= 6);

$hasPerm = $CUSTOM->isUserCollectionOwner();
if ($hasPerm) {
	if ($isD6) {
		testArgsD6();
	} else {
		testArgs();	    
	}
}
header('Content-type: text/html; charset=UTF-8');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<?php 
$header = new LitHeader("Re-index a Collection");
$header->litPageHeader();
?>
</head>
<body>
<?php $header->litHeaderAuth(array(), $hasPerm);?>
<div id="formReindex">
<form method="POST" action="" onsubmit="jobQueue();return true;">
<p>Use this option to re-index the discovery index for a collection</p>
<div id="status"><?php echo $status?></div>
<?php
if ($isD6) {
  collection::getCollectionWidget(util::getPostArg("coll",""), "coll", " to be reindexed*");
  collection::getSubcommunityWidget(util::getPostArg("comm",""), "comm", " to be reindexed*");        
} else {
  collection::getCollectionIdWidget(util::getPostArg("coll",""), "coll", " to be reindexed*");
  collection::getSubcommunityIdWidget(util::getPostArg("comm",""), "comm", " to be reindexed*");    
}
?>
<p align="center">
	<input id="reindexSubmit" type="submit" title="Submit Form" disabled/>
</p>
<p><em>* One of the 2 selection fields is required</em></p>
</form>
</div>
<?php $header->litFooter();?>
</body>
</html>

<?php 

function testArgs(){
	global $status;
	$CUSTOM = custom::instance();
	$dspaceBatch = $CUSTOM->getDspaceBatch();
	$bgindicator =  $CUSTOM->getBgindicator();
	
	if (count($_POST) == 0) return;
	$coll = util::getPostArg("coll","");
	$comm = util::getPostArg("comm","");

    //NOTE: DSpace 6 will need handles... so this logic should be rewritten
	if (is_numeric($coll)) {
	    $coll = intval($coll);
	    if (!isset(collection::$COLLECTIONS[$coll])) {
	    	$status = "collection not found";
	    	return;
	    }
  	    $args = "coll " . $coll;
	} else if (is_numeric($comm)) {
	    $comm = intval($comm);
	    if (!isset(community::$COMMUNITIES[$comm])) {
	    	$status = "Community not found";
	    	return;
	    }
  	    $args = "comm " . $comm;
	} else {
		$status = "A valid collection or community must be selected";
		return;
	}

	$u = escapeshellarg($CUSTOM->getCurrentUser());
	$cmd = <<< HERE
{$u} gu-reindex {$args}
HERE;

    //echo($dspaceBatch . " " . $cmd);
    exec($dspaceBatch . " " . $cmd . " " . $bgindicator);
    header("Location: ../web/queue.php");
}

function testArgsD6(){
	global $status;
	$CUSTOM = custom::instance();
	$dspaceBatch = $CUSTOM->getDspaceBatch();
	$bgindicator =  $CUSTOM->getBgindicator();
	
	if (count($_POST) == 0) return;
	$coll = util::getPostArg("coll","");
	$comm = util::getPostArg("comm","");

	if (util::isIdOrUuid($coll)) {
	    if (!isset(collection::$COLLECTIONS[$coll])) {
	    	$status = "collection not found";
	    	return;
	    }
  	    $args = "coll " . $coll;
	} else if (util::isIdOrUuid($comm)) {
	    if (!isset(community::$COMMUNITIES[$comm])) {
	    	$status = "Community not found";
	    	return;
	    }
  	    $args = "comm " . $comm;
	} else {
		$status = "A valid collection or community must be selected";
		return;
	}

	$u = escapeshellarg($CUSTOM->getCurrentUser());
	$cmd = <<< HERE
{$u} gu-reindex-d6 {$args}
HERE;

    //echo($dspaceBatch . " " . $cmd);
    exec($dspaceBatch . " " . $cmd . " " . $bgindicator);
    header("Location: ../web/queue.php");
}

?>