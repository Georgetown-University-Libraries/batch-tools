<?php
/*
Helper service for locating an ingest folder.
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
include '../phpconfig/init.php';
include '../web/util.php';

$hasPerm = $CUSTOM->isUserCollectionOwner();
if (!$hasPerm) exit; 
$CUSTOM = custom::instance();
$ingestLoc = $CUSTOM->getIngestLoc();
$loc = util::getArg("loc","");

if (preg_match("|\.\.|", $loc) == 1) {
	die("Location cannot contain ..");
}

$testloc = $ingestLoc . $loc;
$myDirectory = opendir($testloc);

echo $testloc;

$dirArray = array();

// get each entry
while($entryName = readdir($myDirectory)) {
	if (substr($entryName,0,1) == ".") continue;
	if (is_dir($testloc . $entryName)) {
		$dirArray[] = $entryName;		
	}
}

// close directory
closedir($myDirectory);
sort($dirArray);

foreach($dirArray as $d) {
	echo <<< HERE
	<option value="{$d}">{$d}</option>
HERE;
}
?>
