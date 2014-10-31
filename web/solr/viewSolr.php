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
        setOptions();
      });
      
      $("#query").change(function(){
        setOptions();
      });
      
      setOptions();
  });
  
  function setOptions() {
      var rep = $("#rep").val();
    
      $("#query option").each(function(){
          if ($(this).hasClass(rep)) {
              $(this).removeAttr("disabled");
          } else {
             $(this).attr("disabled", true);
          }
      });

      if ($("#query option:selected").is(".squery")) {
          $("#squery").removeAttr("disabled");
          $("#squery").val($("#query option:selected").attr("qdata"));
      } else  {
          $("#squery").attr("disabled", true).val("");      
      }
      
      if ($("#query option:selected").hasClass(rep) == false) {
          $("#query").val("count");
          $("#squery").attr("disabled", true).val("");
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
    <option value="search" selected>Discovery/Search</option>
    <option value="oai">OAI</option>
    <option value="statistics">Statistics</option>
    <option value="tstatistics">Temp Statistics</option>
    <option value="statistics-2010">Statistics 2010</option>
    <option value="statistics-2011">Statistics 2011</option>
    <option value="statistics-2012">Statistics 2012</option>
    <option value="statistics-2013">Statistics 2013</option>
    <option value="statistics-2014">Statistics 2014</option>
  </select>
</p>
<p>
  <label for="query">Query</label>
  <select id="query" name="query">
    <option class="search oai statistics statistics-2010 statistics-2011 statistics-2012 statistics-2013 statistics-2014" value="count" qdata="*:*" selected>Count items</option>
    <option class="search oai statistics" value="samples">1000 Recent Sample Records</option>

    <option class="search oai statistics" value="optimize">Optimize</option>

    <option class="statistics squery" value="nouid" qdata="NOT(uid:*)">No UID in stat record</option>
    <option class="statistics squery" value="hasuid" qdata="uid:*">Has UID in stat record (DSpace 4)</option>

    <option class="search squery" value="squery_handle" qdata="handle:10822/1">Discovery Item, Collection, Community</option>
    <option class="oai squery" value="squery_oaihandle" qdata="item.handle:10822/557062" >OAI Item Handle</option>
    <option class="statistics statistics-2010 squery" value="squery2010" qdata="time:[2010-01-01T00:00:00Z+TO+2011-01-01T00:00:00Z]" >Statistics 2010</option>
    <option class="statistics statistics-2011 squery" value="squery2011" qdata="time:[2011-01-01T00:00:00Z+TO+2012-01-01T00:00:00Z]" >Statistics 2011</option>
    <option class="statistics statistics-2012 squery" value="squery2012" qdata="time:[2012-01-01T00:00:00Z+TO+2013-01-01T00:00:00Z]" >Statistics 2012</option>
    <option class="statistics statistics-2013 squery" value="squery2013" qdata="time:[2013-01-01T00:00:00Z+TO+2014-01-01T00:00:00Z]" >Statistics 2013</option>
    <option class="statistics statistics-2014 squery" value="squery2014" qdata="time:[2014-01-01T00:00:00Z+TO+2015-01-01T00:00:00Z]" >Statistics 2014</option>

  </select>
</p>
<p>
  <label for="squery">SOLR Query</label>
  <input type="text" id="squery" name="squery" size="50" value="" disabled/>
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
    $squery = util::getPostArg("squery","");
    $query = util::getPostArg("query","object");
	header('Content-type: application/xml');
    $req = $CUSTOM->getSolrPath() . $rep . "/select?indent=on&version=2.2";
    if ($query == "samples") {
      $req .= "&q=*:*&rows=1000&sort=time+desc";      
    } else if ($query == "optimize") {
      $req = $CUSTOM->getSolrPath() . $rep . "/update?optimize=true";
    } else if ($squery != "") {
      $req .= "&q={$squery}";      
    } else {
      $req .= "&q=bogus:*&rows=0";      
    }
    $ret = file_get_contents($req);
    echo $ret;
    exit;
}
?>
