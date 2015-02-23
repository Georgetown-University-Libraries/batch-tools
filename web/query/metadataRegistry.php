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
$mfields = initField($CUSTOM);

if (util::getArg("JSON","") == "") {
  $tablems = "<h3>Available Metadata Registries</h3>";
  $tablems .= "<table><tr style=\"font-weight: bold; text-align: center\"><td>Schema No</td><td>Schema Namespace</td><td>Schema Name</td></tr>";
  foreach ($mschemas as $mschema) {
    $tablems .= "<tr><td style=\"text-align: center\">$mschema[0]</td><td><a href=\"{$mschema[1]}\">$mschema[1]</td><td style=\"text-align: center\">$mschema[2]</td></tr>";
  }
  $tablems .= "</table>";

  $tablemf = "<h3>Metadata Fields</h3>";
  $tablemf .= "<table><tr style=\"font-weight: bold; text-align: center\"><td>Field Name</td><td style=\"width: 70%\">Field Description</td></tr>";
  foreach ($mfields as $mfield) {
    $tablemf .= "<tr><td>$mfield[0]</td><td>$mfield[1]</td></tr>";
  }
  $tablemf .= "</table>";

?>
<!DOCTYPE html>
<html>
<head>
<?php
$header = new LitHeader("Metadata Registries");
$header->litPageHeader();
?>

<style type="text/css">
div#table {margin: 10px auto;}
table {width: 80%; margin: 20px;}
table td {padding-left: 10px;}
div.clear {clear: both;}
</style>
</head>

<body>
<?php $header->litHeaderAuth(array(), $hasPerm);?>

<div><a href="metadataRegistry.php?JSON=Y">JSON Registry</a></div>
<div id="table">
    <?php echo $tablems?>
</div>
<br />
<div id="table">
    <?php echo $tablemf?>
</div>
    
<div class="clear" />
<?php $header->litFooter();?>
</body>
</html>
<?php
} else {
  $data = array();
  foreach ($mschemas as $mschema) {
    $fields = array();
    foreach ($mfields as $mfield) {
      $matches = array();
      $patt = "/^{$mschema[2]}\.([^\.]+)(\.([^\.]+))?$/";
      
      if (preg_match($patt, $mfield[0], $matches)) {
        $field = array("name" => $mfield[0], "element" => $matches[1]);
        if ($mfield[1] != null) {
            $field["description"] = $mfield[1];
        }
        if (count($matches) == 4) {
          $field["qualifier"] = $matches[3];            
        } 
        $fields[] = $field;            
      } 
    }
    $data[] = array("prefix" => $mschema[2], "namespace" => $mschema[1], "fields" => $fields);
  }
  
  header('Content-Type: application/json');
  header("Content-Disposition: attachment; metadataRegistry.json"); 
  echo json_encode($data);     
}
?> 