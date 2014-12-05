<?php
/*
Prepare SQL queries for metadata registry.

Author: Wei Zhang, Georgetown University Libraries

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

function initSchema($CUSTOM) {

$sql = <<< EOF
SELECT metadata_schema_id, namespace, short_id
FROM metadataschemaregistry
ORDER BY metadata_schema_id
EOF;

$dbh = $CUSTOM->getPdoDb();
$stmt = $dbh->prepare($sql);
$result = $stmt->execute(array());
$result = $stmt->fetchAll();

if (!$result) {
	print($sql);
	print_r($dbh->errorInfo());
	die("Error in SQL query");
}

$mschemas = array();
foreach ($result as $row) {
	$id = $row[0];
	$schema_namespace = $row[1];
	$schema_name = $row[2];
	
	array_push($mschemas, array($id, $schema_namespace, $schema_name));
}

return $mschemas;

}

?>