<?php
/*
User form for undoing a bulk ingest.  
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
$ingestLoc =  $CUSTOM->getIngestLoc();
$root = $CUSTOM->getRoot();
$mroot =  $CUSTOM->getMapRoot();

$myDirectory = opendir($mroot);

// get each entry
while($entryName = readdir($myDirectory)) {
	$dirArray[] = $entryName;
}

// close directory
closedir($myDirectory);

rsort($dirArray);


$status = "";
$hasPerm = $CUSTOM->isUserCollectionOwner();
if ($hasPerm) testArgs();
header('Content-type: text/html; charset=UTF-8');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<?php 
$header = new LitHeader("Undo Bulk Ingest");
$header->litPageHeader();
?>
</head>
<body>
<?php $header->litHeaderAuth(array(), $hasPerm);?>
<div id="formUningest">
<form method="POST" action="" onsubmit="jobQueue();return true;">
<p>This process will remove items added by bulk ingest</p>
<div id="status"><?php echo $status?></div>
<fieldset class="mapfile">
<legend>Ingest Map file * </legend>
<p>
  <label for="mapfile">Map File</label>
  <select name="mapfile" id="mapfile">
  	<option value="">Select the mapfile to undo</option>
  	<?php
	foreach($dirArray as $fname) {
		if (substr($fname,0,1) != "."){
			echo "<option value='" . $fname . "'>" . $fname . "</option>";
		}
	}
  	?>
  </select>
</p>
<p>
<textarea readonly rows="6" cols="80" id="maptext"></textarea>
</p>
</fieldset>
</p>
<p align="center">
	<input id="ingestSubmit" type="submit" title="Submit Job" disabled/>
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
	$root = $CUSTOM->getRoot();
	$mroot =  $CUSTOM->getMapRoot();
	$dspaceBatch = $CUSTOM->getDspaceBatch();
	
	if (count($_POST) == 0) return;
	
	$mapfile = util::getPostArg("mapfile","");
	
	$u = escapeshellarg($CUSTOM->getCurrentUser());
	$user = escapeshellarg($CUSTOM->getCurrentUserEmail());
	$mapfile = escapeshellarg($mroot.$mapfile);

	$cmd = <<< HERE
{$u} gu-uningest {$user} {$mapfile}
HERE;
    
    //echo($dspaceBatch . " " .$cmd);
    exec($dspaceBatch . " " . $cmd);
    header("Location: ../web/queue.php");
}
?>
