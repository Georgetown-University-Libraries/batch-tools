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
</head>
<body>
<?php $header->litHeader(array());?>
<div id="viewSolr">
<form method="POST" action="">
<p>View the SOLR Related Items for a DSpace Resource.</p>
<div id="status"><?php echo $status?></div>
<p>
  <label for="handle">Handle</label>
  <input type="text" id="handle" name="handle" size="20" value="10822/1"/>
</p>
<p>
  <label for="query">Query</label>
  <select id="query" name="query">
    <option value="object">Item, Collection, Community</option>
    <option value="count">Count items</option>
  </select>
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
	$handle = util::getPostArg("handle","");
    $query = util::getPostArg("query","object");
	header('Content-type: application/xml');
    $req = $CUSTOM->getSolrPath() . "search/select?indent=on&version=2.2";
    if ($query == "count") {
      $req .= "&q=*:*&rows=0";      
    } else {
      if ($handle == "") return;
      $req .= "&q=handle:{$handle}";      
    }
    $ret = file_get_contents($req);
    echo $ret;
    exit;
}
?>
