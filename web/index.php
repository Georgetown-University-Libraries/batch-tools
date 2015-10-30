<?php
/*
DSpace Tools Landing Page

Note that several paths to institution-specific resources would need to be set.  This code assumes that presence of PROD, TEST, DEV and AUX servers.
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
include 'header.php';

$CUSTOM = custom::instance();

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<style type="text/css">
div.col {
	width: 48%;
	float: left;
}
</style>
<?php 
$header = new LitHeader("Home");
$header->litPageHeader();
?>
</head>
<body>
<?php 
$header->litHeader(array());
?>

<?php 
echo $CUSTOM->getNavHtml();
?>
<div class="batch-tool-links">
<?php
echo $CUSTOM->getIntroHtml();
?>
<div class="col">
<h4>Reporting Tools (Viewer Access)*</h4>
<ul>
<li><a href="queue.php">Job Queue</a></li>
<li>
  <a href="/static/rest/index.html">QC Overview for Collections</a>
</li>
<li>
  <a href="/static/rest/query.html">Self-Service Query</a>
</li>
<?php 
if ($CUSTOM->showStatsTools()) {
?>
<li>
  <a href="stats/qcHierarchyStats.php">Show Statistics</a>
</li>
<?php 
}
?>
<!--Analyze SOLR values-->
<li>
  <a href="solr/viewSolr.php">SOLR Index Queries</a>
</li>
<!-- Use the OAI service to provide data exports-->
<li>
  <a href="export/oaiExport.php">Export data from OAI harvester</a>
</li>
</ul>
echo $CUSTOM->getOtherHtml();
</div>
<div class="col">
<?php
if ($CUSTOM->showBatchTools()) { 
	echo $CUSTOM->getAdminHtml();
}
?>
</div>
<?php
$header->litFooter();
?>
</body>
</html>

