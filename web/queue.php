<?php
/*
Job Queue display page

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
$handleContext =  ""; //or xmlui
$CUSTOM = custom::instance();
$root = $CUSTOM->getRoot();
$qroot =  $CUSTOM->getQueueRoot();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<?php 
$header = new LitHeader("Job Queue");
$header->litPageHeader();

?>
</head>
<body>
<?php 
$header->litHeader(array());

// open this directory 
$myDirectory = opendir($qroot);

// get each entry
while($entryName = readdir($myDirectory)) {
	$dirArray[] = $entryName;
}

// close directory
closedir($myDirectory);

//	count elements in array
$indexCount	= count($dirArray);

// sort 'em
rsort($dirArray);

// print 'em
echo <<< HERE
<table id="queue" class="sortable">
<tbody>
<tr>
  <th>Action</th>
  <th style="width:180px">Started At</th>  
  <th>Status</th>
  <th style="width:500px">Command</th>
</tr>
HERE;
for($index=0; $index < $indexCount  && $index < 25; $index++) {
	$fname = $dirArray[$index];
	if (substr($fname, 0, 4) == "job."){ // don't list hidden files
		$contents = file_get_contents($qroot . $fname, false, NULL, 0, 1000);
		$carr = explode("\n",$contents);
		$cmd = $carr[0];
		
		if (preg_match('|^(.* )(-[ic] "?(\d+/\d+)"?)([^0-9].*)?$|', $cmd, $match) == 1) {
			$cmd = $match[1] . "<a href='" . $handleContext . "/handle/" . $match[3] . "'>" . $match[2] . "</a>" . $match[4];
		}
		
		
		$arr = explode(".", $fname);
		echo "<tr>";
		echo "<td>";
		if (count($arr) > 2) echo $arr[2];
		echo "</td>";
		echo "<td style='width:180px'>";
		if (count($arr) > 1) echo $arr[1];
		echo "</td>";
		echo "<td>";		
		if (count($arr) > 3) echo "<a href='jobstat.php?name=" . $fname . "'>" . $arr[3] . "</a>";
		echo "</td>";
		echo "<td style='width:500px'>" . $cmd . "</pre>";
		echo "</tr>";
	}
}
echo <<< HERE
</tbody>
</table>
<script>
function checkFile(url) {
	var request = new XMLHttpRequest();
	request.open("HEAD", url, false);
	request.send();
	
	if (request.status == "200") return true;
	else return false;
  }

document.querySelector("#queue").addEventListener("click", function(event) {
	event.preventDefault();	
	var url = event.target.href;
	var exist = checkFile(url);
	
	console.log(url);
	console.log(exist);
	
	if (exist) {
		window.open(url, "_self");
	}
	else {
		url = url.substring(url.indexOf("jobstat.php"), url.indexOf("running.txt")) + "complete.txt";
		window.open(url, "_self");
	}
  }, false);
</script>
HERE;
?>

</table>
<?php $header->litFooter();?>
</body>
</html>

