<?php
/*
User form for initiating a bulk ingest.  User must have already uploaded ingestion folders to a server-accessible folder.
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
$status = "";
testArgs();
header('Content-type: text/html; charset=UTF-8');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<?php 
$header = new LitHeader("View SOLR Data");
$header->litPageHeader();
?>
<script type="text/javascript">
  $(document).ready(function(){
      $("#rep").change(function(){
        $("#query").val("count");
        setOptions();
      });
      
      $("#query").change(function(){
        setOptions();
      });
      
      setOptions();
  });
  
  function setOptions() {
      $("#query option").attr("disabled",true);
      var rep = "#query option." + $("#rep").val();
      $(rep).removeAttr("disabled");
      $("#handle").attr("disabled", true);
      if ($("#query option:selected").is(".handle")) {
        $("#handle").removeAttr("disabled");
      }
  }
</script>
</head>
<body>
<?php $header->litHeader(array());?>
<div id="viewSolr">
<form method="POST" action="">
<p>View the SOLR Related Items for a DSpace Resource.</p>
<div id="status"><?php echo $status?></div>
<p>
  <label for="rep">Repository</label>
  <select id="rep" name="rep">
    <option value="search">Discovery/Search</option>
    <option value="oai">OAI</option>
    <option value="statistics">Statistics</option>
  </select>
</p>
<p>
  <label for="query">Query</label>
  <select id="query" name="query">
    <option class="search oai statistics" value="count">Count items</option>
    <option class="search oai statistics" value="samples">1000 Recent Sample Records</option>

    <option class="search oai statistics" value="optimize">Optimize</option>

    <option class="search handle" value="object">Discovery Item, Collection, Community</option>
    <option class="oai handle" value="oaiitem">OAI item</option>

    <option class="statistics" value="nouid">No UID in stat record</option>
    <option class="statistics" value="hasuid">Has UID in stat record</option>
  </select>
</p>
<p>
  <label for="handle">Handle</label>
  <input type="text" id="handle" name="handle" size="20" value="10822/1"/>
</p>
<p align="center">
	<input id="ingestSubmit" type="submit" title="Submit"/>
</p>
</form>
</div>

<?php $header->litFooter();?>
</body>
</html>
<?php 
function testArgs(){
	global $status;
	global $ingestLoc;
	
    $CUSTOM = custom::instance();
	if (count($_POST) == 0) return;
    $rep = util::getPostArg("rep","search");
	$handle = util::getPostArg("handle","");
    $query = util::getPostArg("query","object");
	header('Content-type: application/xml');
    $req = $CUSTOM->getSolrPath() . $rep . "/select?indent=on&version=2.2";
    if ($query == "object") {
      if ($handle == "") return;
      $req .= "&q=handle:{$handle}";      
    } else if ($query == "oaiitem") {
      if ($handle == "") return;
      $req .= "&q=item.handle:{$handle}";      
    } else if ($query == "nouid") {
      $req .= "&q=NOT(uid)&rows=0";      
    } else if ($query == "hasuid") {
      $req .= "&q=*-*-*-*-*&rows=0";      
    } else if ($query == "count") {
      $req .= "&q=*:*&rows=0";      
    } else if ($query == "samples") {
      $req .= "&q=*:*&rows=1000&sort=time+desc";      
    } else if ($query == "optimize") {
      $req .= $CUSTOM->getSolrPath() . $rep . "/update?optimize=true";      
    } else {
      $req .= "&q=bogus:*&rows=0";      
    }
    $ret = file_get_contents($req);
    echo $ret;
    exit;
}
?>
