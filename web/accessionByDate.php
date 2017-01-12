<?php
/*
User form for triggering the update of DSpace statistics from a web page.
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

$status = "";
$CUSTOM = custom::instance();
header('Content-type: text/html; charset=UTF-8');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<?php 
$header = new LitHeader("Item Count By Accession Date");
$header->litPageHeader();
?>
<style type="text/css">
  td.num {text-align: right;}
  tr[class$="tot"] td,tr[class$="tot"] th {background-color: #EEE;}
  tr.head td,tr.head th {background-color: yellow;}
  tr.grand_tot td,tr.grand_tot th {background-color: orange;}
</style>
<script type="text/javascript">
var tot = 0;
$(function(){
  $("tr[class$='-tot']").each(function(){
    tot += Number($(this).find("td:first").text());
    $(this).find("td:last").text(tot);
  });
});
</script>
</head>
<body>
<?php 
$header->litHeader(array());
$sql = <<< HERE
select
  substr(text_value,1,7) as month,
  count(*)
from metadatavalue
where metadata_field_id = (
  select metadata_field_id
  from metadatafieldregistry
  where element='date' and qualifier='accessioned'
) and place=1
group by month
union
select
  substr(text_value,1,4) || '-tot' as month,
  count(*)
  from metadatavalue
  where metadata_field_id = (
    select metadata_field_id
    from metadatafieldregistry
    where element='date' and qualifier='accessioned'
  ) and place=1
  group by month
union
select
  'grand_tot' as month,
  count(*)
from metadatavalue
where metadata_field_id = (
  select metadata_field_id
  from metadatafieldregistry
  where element='date' and qualifier='accessioned'
) and place=1
group by month
order by month;
HERE;

$dbh = $CUSTOM->getPdoDb();
$stmt = $dbh->prepare($sql);
$result = $stmt->execute($arg);
if (!$result) {
	print($sql);
	print_r($stmt->errorInfo());
	die("Error in SQL query: ");
}
$result = $stmt->fetchAll();
echo "<table>";
echo "<tr><th>Date</th><th>Accession Count</th><th>Total</th></tr>";
foreach($result as $row) {
	$d = $row[0];
	$c = $row[1];
	echo "<tr class='row {$d}'><th>{$d}</th><td class='num'>{$c}</td><td class='num'/></tr>";
}
echo "<table>";

$header->litFooter();

?>

</body>
</html>
