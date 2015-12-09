<?php
/*
User form for clearing the oai cache from a web page.
Author: Wei Zhang, Georgetown University Libraries

License information is contained below.

Copyright (c) 2014, Georgetown University Libraries All rights reserved.

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

$status = "";
$CUSTOM = custom::instance();
$hasPerm = $CUSTOM->isUserCollectionOwner();
if ($hasPerm) testArgs();
header('Content-type: text/html; charset=UTF-8');
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<?php 
$header = new LitHeader("Normalize Lang Field on Metadata Records");
$header->litPageHeader();
?>
</head>

<body>
<?php $header->litHeaderAuth(array(), $hasPerm);?>
<div id="formMetadata">
<form method="POST" action="" onsubmit="jobQueue();return true;" enctype="multipart/form-data" >
<div id="status"><?php echo $status?></div>
<fieldset class="mapfile">
<p>Click the "Submit" button to normalize variant lang fields for metadata records (repository wide).</p>
<input name="hidden" type="hidden" value="x"/>
<p align="center">
	<input id="submit" type="submit" title="Submit Job"/>
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
	$dspaceBatch =  $CUSTOM->getDspaceBatch();
	
	if (count($_POST) == 0) return;
	
	$u = escapeshellarg($CUSTOM->getCurrentUser());
	$cmd = <<< HERE
{$u} normalize-lang
HERE;
    exec($dspaceBatch . " " . $cmd);
    header("Location: ../web/queue.php");
}
?>
