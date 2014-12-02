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
include '../header.php';
include 'queries.php';
include 'selfQueryCommon.php';

$CUSTOM = custom::instance();
$CUSTOM->getCommunityInit()->initCommunities();
$CUSTOM->getCommunityInit()->initCollections();

$MAX = 2000;

$val    = util::getPostArg("val","");
$isCSV  = (util::getPostArg("query","") == "CSV Extract");
$offset = util::getPostArg("offset","0");

$mfields = initFields($CUSTOM);
$dsel = "<select id='dfield' name='dfield[]' multiple size='10'>";
$sel = "<select name='field[]' class='qfield'><option value=''>N/A</option><option value='0'>All</option>";
foreach ($mfields as $mfi => $mfn) {
    $sel .= "<option value='{$mfi}'>{$mfn}</option>";
    $dsel .= "<option value='{$mfi}'>{$mfn}</option>";
}
$sel .= "</select>";
$dsel .= "</select>";

$filters = initFilters();
$filsel = "<div class='filters'>";
foreach($filters as $key => $obj) {
	$name = $obj['name'];
	$filsel .= "<div class='filter'><input name='filter[]' value='{$key}' type='checkbox' id='{$key}'><label for='{$key}'>{$name}</label></div>";
}
$filsel .= "</div>";


$status = "";
$hasPerm = $CUSTOM->isUserCollectionOwner();
if ($isCSV) {
    header("Content-type: text/csv");
    header("Content-Disposition: attachment; filename=export.csv"); 
} else {
    header('Content-type: text/html; charset=UTF-8');
}
   
?>
<!DOCTYPE html>
<html>
<body>
<?php
$header = new LitHeader("Query Construction");
$header->litPageHeader();
?>
<script type="text/javascript" src="spin.js"></script>
<script type="text/javascript" src="selfQuery.js"></script>
<style type="text/css">
form {width: 1000px;}
button.edit {float: right;}
#spinner {display: inline;float: left; height: 200px; width: 45%; border: none;}
fieldset.fields {width: 40%; display:inline;float: left; margin: 20px;}
div.clear {clear: both;}
</style>
</head>
<body>
<?php $header->litHeaderAuth(array(), $hasPerm);?>
<div id="selfQuery">
<form id="myform" action="selfQueryData.php" method="POST">
<fieldset id="queryform">
<legend>Use this option to construct a quality control query </legend>
<button type="button" class="edit" name="edit" onclick="doedit();" disabled>Edit</button>
<div id="status"><?php echo $status?></div>
<?php collection::getCollectionIdWidget("", "coll", " to be queried*");?>
<?php collection::getSubcommunityIdWidget("", "comm", " to be queried*");?>
<?php for($qc=0; $qc < 3; $qc++){?>
<p>
  <label for="field">Field to query</label>
  <?php echo $sel?>
  <label for="op">; Operator: </label>
  <select name="op[]" class="qfield" onchange="$(this).siblings('input.qfield').val($(this).find('option:selected').attr('example'));">
    <option value="exists" example="">Exists</value>
    <option value="not exists" example="">Doesn't exist</value>
    <option value="equals" example="val">Equals</value>
    <option value="not equals" example="val">Not Equals</value>
    <option value="like" example="%val%">Like</value>
    <option value="not like" example="%val%">Not Like</value>
    <option value="matches" example="^.*(val1|val2).*$">Matches</value>
    <option value="doesn't match" example="^.*(val1|val2).*$">Doesn't Matches</value>
  </select>
  <label for="val">; Value: </label>
  <input name="val[]" type="text" value="<?php echo $val?>" class="qfield"/>
</p>
<?php }?>
</fieldset>
<div>
  <fieldset class="fields">
    <legend>Fields to display</legend>
    <?php echo $dsel?>
  </fieldset>
  <fieldset class="filters">
    <legend>Filters</legend>
    <?php echo $filsel?>
  </fieldset>
  
  <div class="clear"/>
</div>
<p align="center">
    <input id="offset" name="offset" type="hidden" value="<?php echo $offset?>"/>
    <input id="MAX" name="MAX" type="hidden" value="<?php echo $MAX?>"/>
    <input id="querySubmitPrev" name="query" value="Prev Results" type="submit" disabled/>
    <input id="querySubmit" name="query" value="Show Results" type="submit"/>
	<input id="querySubmitNext" name="query" value="Next Results" type="submit" disabled/>
    <input id="queryCsv" name="query" value="CSV Extract" type="submit" disabled/>
</p>
<p><em>* Up to <?php echo $MAX?> results will be returned</em></p>
</form>
</div>
<div id='exporthold'>
</div>
<?php $header->litFooter();?>
</body>
</html> 