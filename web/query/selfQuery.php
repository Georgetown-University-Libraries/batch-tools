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
include 'selfQuerySaved.php';

$CUSTOM = custom::instance();
$CUSTOM->getCommunityInit()->initCommunities();
$CUSTOM->getCommunityInit()->initCollections();

$coll  = util::getArg("coll","");
$comm  = util::getArg("comm","");
$op    = util::getArg("op",array());
$field = util::getArg("field",array());
$dfield = util::getArg("dfield",array());
$filter = util::getArg("filter",array());
$val    = util::getArg("val",array());
$isCSV  = (util::getArg("query","") == "CSV Extract");
$offset    = util::getArg("offset","0");

$saved = initSavedSearches();
$savename = util::getArg("savename","");
$savedesc = util::getArg("savedesc","");

if (count($field) == 0) array_push($field,"");
if (count($op) == 0) array_push($op,"");
if (count($val) == 0) array_push($val,"");

$MAX = 2000;

$mfields = initFields($CUSTOM);
$dsel = "<select id='dfield' name='dfield[]' multiple size='10'>";
foreach ($mfields as $mfi => $mfn) {
    if (preg_match("/^dc\.(date\.accessioned|date\.available|description\.provenance).*/", $mfn)) continue;
    $t = arrsel($dfield,$mfi,'selected');
    $dsel .= "<option value='{$mfi}' {$t}>{$mfn}</option>";
}
$dsel .= "</select>";

$filters = initFilters();
$filsel = "<div class='filters'>";
foreach($filters as $key => $obj) {
	$name = $obj['name'];
	$t = arrsel($filter,$key,'checked');
	$filsel .= "<div class='filter'><input name='filter[]' value='{$key}' type='checkbox' id='{$key}' {$t}><label for='{$key}'>{$name}</label></div>";
}
$filsel .= "</div>";


$status = "";
$hasPerm = $CUSTOM->isUserViewer();
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
div.clear {clear: both;}
.dataval {word-wrap: break-word; overflow-wrap: break-word; width: 200px; max-width: 200px;}
#savebox {padding: 15px; margin: 0 auto; width: 80%; }
#savebox label.field {display: block; float: left; width: 150px;}
#savebox input {display: block; float: left;}
#savecontent {display: none;}
#savebox:hover #savecontent {display: block;}
</style>
</head>
<body>
<?php $header->litHeaderAuth(array(), $hasPerm);?>
<div id="selfQuery">
<form id="myform" action="selfQueryData.php" method="POST">
<h3>Search Scope</h3>
<div>
<fieldset id="queryform">
<legend>Use this option to construct a quality control query </legend>
<button type="button" class="edit" name="edit" onclick="doedit();" disabled>Edit</button>
<div id="status"><?php echo $status?></div>
<div id="accordion">
<h3>Search Scope</h3>
<div>
<?php collection::getCollectionIdWidget($coll, "coll", " to be queried*");?>
<?php collection::getSubcommunityIdWidget($comm, "comm", " to be queried*");?>
<div id="querylines">
<?php for($i=0; $i<count($field); $i++) {?>
<p class="queryline">
  <label for="field">Field to query</label>
  <?php 
  echo "<select name='field[]' class='qfield' onchange='changeField($(this));'><option value=''>N/A</option><option value='0'>All</option>";
  foreach ($mfields as $mfi => $mfn) {
  	$t = sel($field[$i],$mfi,'selected');
  	echo "<option value='{$mfi}' {$t}>{$mfn}</option>";
  }
  echo "</select>";
  ?>
  <label for="op">; Operator: </label>
  <select name="op[]" class="qfield" onchange="changeOperator($(this), true);">
    <option value="exists"        <?php echo sel($op[$i],'exists','selected')?>        example="">Exists</value>
    <option value="not exists"    <?php echo sel($op[$i],'not exists','selected')?>    example="">Doesn't exist</value>
    <option value="equals"        <?php echo sel($op[$i],'equals','selected')?>        example="val">Equals</value>
    <option value="not equals"    <?php echo sel($op[$i],'not equals','selected')?>    example="val">Not Equals</value>
    <option value="like"          <?php echo sel($op[$i],'like','selected')?>          example="%val%">Like</value>
    <option value="not like"      <?php echo sel($op[$i],'not like','selected')?>      example="%val%">Not Like</value>
    <option value="matches"       <?php echo sel($op[$i],'matches','selected')?>       example="^.*(val1|val2).*$">Matches</value>
    <option value="doesn't match" <?php echo sel($op[$i],"doesn't match",'selected')?> example="^.*(val1|val2).*$">Doesn't Match</value>
  </select>
  <label for="val">; Value: </label>
  <input name="val[]" type="text" value="<?php echo $val[$i]?>" class="qfield"/>
  <input type="button" onclick="copyQuery($(this))" value="+"/>
</p>
<?php }?>
</div>
</fieldset>
</div>
<h3>Fields to Display</h3>
<div>
  <fieldset class="fields">
    <legend>Fields to display</legend>
    <?php echo $dsel?>
    <div style="font-style:italic">Provenance, Accession Date, Available Date cannot be exported</div>
  </fieldset>
</div>
<h3>Filter Query Results</h3>
<div>
  <fieldset class="filters">
    <legend>Filters</legend>
    <?php echo $filsel?>
  </fieldset>
</div>
<h3>Show Results</h3>
<div align="center">
    <input id="offset" name="offset" type="hidden" value="<?php echo $offset?>"/>
    <input id="MAX" name="MAX" type="hidden" value="<?php echo $MAX?>"/>
    <input id="querySubmitPrev" name="query" value="Prev Results" type="submit" disabled/>
    <input id="querySubmit" name="query" value="Show Results" type="submit"/>
	<input id="querySubmitNext" name="query" value="Next Results" type="submit" disabled/>
    <input id="queryCsv" name="query" value="CSV Extract" type="submit" disabled/>
</div>
<h3>Save Search</h3>
<div>
  <fieldset id="savebox">
    <select id="saved" name="saved">
      <option> - Choose Saved Search -</option>
      <optgroup label="My Saved Searches" id="mysaved">
      </optgroup>
      <optgroup label="Common Searches">
      <?php
        foreach($saved as $name => $search) {
          echo "<option value='{$search['permalink']}' title='{$search['desc']}'>{$name}</option>";
        }
      ?>
      </optgroup>
    </select>
    <label>Saved Searches and Permalinks<label>
    <div id="savecontent">
      <div>
        <label for="savename" class="field">Search Name:</label>
        <input type="text" name="savename" id="savename" title="Name your search if you would like to save it for the future" value="<?php echo $savename?>" disabled/>
        <input id="queryLink" name="query" value="Permalink" type="submit" disabled/>
        <input id="queryRemlink" name="query" value="Remove link" type="button" disabled/>
        <div class="clear"/>
      </div>
      <div>
        <label for="savedesc" class="field">Search Desc:</label>
        <input type="text" name="savedesc" id="savedesc" title="Optional - describe your search for future use" size="50" value="<?php echo $savedesc?>" disabled/>
        <div class="clear"/>
      </div>
    </div>
  </fieldset>  
</div>
</div>
<p><em>* Up to <?php echo $MAX?> results will be returned</em></p>
</form>
</div>
<div id='exporthold'>
</div>
<?php $header->litFooter();?>
</body>
</html> 