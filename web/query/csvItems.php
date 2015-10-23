<?php
include '../../phpconfig/init.php';
include '../util.php';
include 'queries.php';

$CUSTOM = custom::instance();

header("Content-type: text/csv");
header("Content-Disposition: attachment; filename=export.csv");

initQueries();

function inval() {
	echo "<h1>Invalid Parameters</h1>";
	die();
}

try {
	$id = collectionArg::getId();
	if ($id == null) inval();
	
	$qname = $_GET['qname'];
	$rptquery = query::getQuery($qname);
} catch (exception $e){
	echo "Err {$e}";
	inval();
}

$qargs = query::getFilterArgs();

$cols = "";
foreach(auxFields::$SHOWARR as $k => $v) {
	$cols .= "{$v} as {$k},\n";
}
$cols .= "1";

$sel = <<< EOF
select 
  i.item_id,
  (
    select regexp_replace(mv.text_value,E'[\r\n\t ]+',' ','g')
    from metadatavalue mv  
    inner join metadatafieldregistry mfr on mfr.metadata_field_id = mv.metadata_field_id
      and mfr.element = 'title' and mfr.qualifier is null
    where mv.resource_id = i.item_id and mv.resource_type_id = 2 
  ) as title,
  handle,
  {$cols}
from 
  item i
inner join 
  handle on i.item_id = handle.resource_id and handle.resource_type_id = 2
EOF;

if (collectionArg::isCollection()) {
$sql = <<< EOF
{$sel}
where
  i.owning_collection = :pid
  {$rptquery->subq};  
EOF;
} else if (collectionArg::isCommunity()) {
$comm2coll = query::comm2coll();
$sql = <<< ZZZ
{$comm2coll}
{$sel}
inner join
  r_comm2coll r on i.owning_collection = r.collection_id
where
  r.community_id = :pid
  {$rptquery->subq};  
ZZZ;
}

$dbh = $CUSTOM->getPdoDb();
$stmt = $dbh->prepare($sql);

$result = $stmt->execute(array(':pid' => $id));

$result = $stmt->fetchAll();

//$header->sqlDump($sql);

if (!$result) {
	print($sql);
	print_r($dbh->errorInfo());
     die("Error in SQL query");
}       

$iname = collectionArg::getInputName();

$showopt = auxFields::getShowOptCb();

echo "id,dc.title[en_US]";
$c = 0;
foreach(auxFields::$SHOWARR as $k => $v) {
	if (!isset(auxFields::$DC[$k])) continue;
	$dc = auxFields::$DC[$k];
	$dc1 = preg_replace("/\[.*\]/","",$dc);
    echo ",";
    echo $dc1;	
    echo ",";
    echo $dc1 . "[]";	
    echo ",";
    echo $dc1 . "[en_US]";	
} 
echo "\n";
foreach ($result as $row) {
 	echo $row[0];
 	echo ',"';
 	echo $row[1];
 	echo '"';
 	$i=0;
 	foreach(auxFields::$SHOWARR as $k => $v) {
 		$val = $row[$i+3];
 		$i++;
	    if (!isset(auxFields::$DC[$k])) continue;
  	    $dc = auxFields::$DC[$k];
	    $dc1 = preg_replace("/\[.*\]/","",$dc);
 		
 		$val = str_replace("<hr/>","||",$val);
 		$val = preg_replace("/\n/"," ",$val);
 		$val = str_replace('/["]/',"",$val);
		echo ($dc == $dc1) ? "," . '"' . $val . '"' : ",";
		echo ($dc == ($dc1 . "[]")) ? "," . '"' . $val . '"' : ",";
		echo ($dc == ($dc1 . "[en_US]")) ? "," . '"' . $val . '"' : ",";
 	}
 	echo "\n";
}       

