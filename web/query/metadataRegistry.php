<?php
/*
For browsing medatada registries. 

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

include '../header.php';
include 'queries.php';
include 'metadataRegistryCommon.php';

$CUSTOM = custom::instance();
$hasPerm = $CUSTOM->isUserCollectionOwner();

$mschemas = initSchema($CUSTOM);
$table = "<table><th colspan=3>Available Metadata Registries</th>";
$table .= "<tr><td>schema id</td><td>namespace</td><td>schema name</td></tr>";
foreach ($mschemas as $mschema) {
    $table .= "<tr><td>$mschema[0]</td><td>$mschema[1]</td><td>$mschema[2]</td></tr>";
}
$table .= "</table>";

$status = "";
?>

<!DOCTYPE html>
<html>
<head>
<?php
$header = new LitHeader("Metadata Registries");
$header->litPageHeader();
?>

<style type="text/css">
table {width: 60%; display:inline; float: center; margin: 20px;}
div.clear {clear: both;}
</style>
</head>

<body>
<?php $header->litHeaderAuth(array(), $hasPerm);?>
<div id="table">
    <?php echo $table?>
</div>
    
<div class="clear" />
<?php $header->litFooter();?>
</body>
</html> 