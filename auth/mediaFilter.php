<?php
/*
User form for initiating the DSpace filter media process via a web interface.

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
$hasPerm = $CUSTOM->isUserCollectionOwner();
if ($hasPerm) testArgs();
header('Content-type: text/html; charset=UTF-8');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<?php 
$header = new LitHeader("Media Filter");
$header->litPageHeader();
?>
</head>
<body>
<?php $header->litHeaderAuth(array(), $hasPerm);?>
<div id="formFilter">
<form method="POST" action="" onsubmit="jobQueue();return true;">
<p>After a bitstream (document, image, etc) has been loaded into DSpace, the filter media process must run against it in order to enable all functionality for the media that was loaded.  The filter media process runs in the background as a batch process.  This process runs nightly against the repository.  This script permits an administrator to manually invoke the filter media process.</p>
<div id="status"><?php echo $status?></div>
<?php collection::getCollectionWidget(util::getPostArg("community",""), util::getPostArg("collection",""));?>
<p>
<fieldset class="cb">
<legend>Actions to Perform * </legend>
<?php $arr = util::getPostArg("action",array())?>
<p>
  <input type="checkbox" class="checkbox" name="action[]" value="actThumb" id="actThumb" <?php checkedArr($arr,"actThumb")?> />
  <label for="actThumb">Create Thumbnails</label>
</p>
<p>
  <input type="checkbox" class="checkbox" name="action[]" value="actText" id="actText" <?php checkedArr($arr,"actText")?> />
  <label for="actText">Full-text index</label>
</p>
</fieldset>
</p>
<fieldset class="cb">
<legend>Options</legend>
<p>
  <input type="checkbox" class="checkbox" name="optForce" value="optForce" id="optForce" <?php checkedPost("optForce", "optForce")?>/>
  <label for="optForce">Force re-creation of items</label>
</p>
</fieldset>
</p>
<p align="center">
    <input id="filterSubmit" type="submit" title="Submit Form" disabled/>
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
    $coll = util::getPostArg("collection","");
    $coll = util::getPostArg("collection","");

    $status = $CUSTOM->validateCollection($coll);
    if ($status != "") return;
    
    
    $args = "-v -i " . $coll;

    $arr = util::getPostArg("action",array());

    if (in_array("actText", $arr)) {
        $args .= ' -p "HTML Text Extractor" -p "PDF Text Extractor" -p "PowerPoint Text Extractor" -p "Word Text Extractor"';   
    }

    if (in_array("actThumb", $arr)) {
        $args .= ' -p "ImageMagick Image Thumbnail" -p "ImageMagick PDF Thumbnail"';
    }        
    
    if (util::getPostArg("optForce", false)) $args .= " -f";
    
    $u = escapeshellarg($CUSTOM->getCurrentUser());
    $cmd = <<< HERE
{$u} filter-media {$args}
HERE;

    //echo($dspaceBatch . " " . $cmd);
    exec($dspaceBatch . " " . $cmd . " " . $bgindicator);
    header("Location: ../web/queue.php");
}

?>