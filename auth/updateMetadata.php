<?php
/*
User form for initiating a metadata update.  The DSpace UI does not necessarily present the import metadata option to all users who may need this option.
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
$dspaceBatch = $CUSTOM->getDspaceBatch();

$status = "";
$hasPerm = $CUSTOM->isUserCollectionOwner();
if ($hasPerm) testArgs();

header('Content-type: text/html; charset=UTF-8');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<?php 
$header = new LitHeader("Update Metadata");
$header->litPageHeader();
?>
</head>
<body>
<?php $header->litHeaderAuth(array(), $hasPerm);?>
<div id="formMetadata">
<form method="POST" action="" onsubmit="jobQueue();return true;" enctype="multipart/form-data" >
<p>Be very careful with this option.  (1)Export metadata from DSpace as CSV (2)Edit the CSV (3)Carefully use this option to update</p>
<div id="status"><?php echo $status?></div>
<fieldset class="mapfile">
<p>
  <label for="metadata">Metadata CSV</label>
  <input type="file" id="metadata" name="metadata"/>
</p>
<p>
  <input type="checkbox" class="checkbox" name="preview" value="preview" id="preview" checked/>
  <label for="actTiles">Preview changes</label>
</p>
</p>
<p align="center">
	<input id="submit" type="submit" title="Submit Job" disabled/>
</p>
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
	$ingestLoc =  $CUSTOM->getIngestLoc();
	
	if (count($_POST) == 0) {
		$status = "";
		return;
	}
	
	if ($_FILES["metadata"]["error"]) {
		$name = isset($_FILES["metadata"]["tmp_name"]) ? $_FILES["metadata"]["tmp_name"] : $_FILES["metadata"]["name"];
		$status = "File upload error: " . $_FILES["metadata"]["error"] . " on file " . $name;
		return;
	}
	
	$run = (util::getPostArg("preview","") == "") ? "-s" : "";
	
	$temp = $ingestLoc . $_FILES["metadata"]["name"];
	move_uploaded_file($_FILES["metadata"]["tmp_name"], $temp);
    $temp = escapeshellarg($temp);  
      
	$user = escapeshellarg($CUSTOM->getCurrentUserEmail());

	$u = escapeshellarg($CUSTOM->getCurrentUser());
	$cmd = <<< HERE
{$u} metadata-import -f {$temp} -e {$user} {$run}
HERE;
    
    $status = $cmd;
    return;
    
    //echo($dspaceBatch . " " .$cmd);
    exec($dspaceBatch . " " . $cmd);
    header("Location: ../web/queue.php");
}
?>
