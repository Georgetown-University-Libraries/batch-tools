<?php
include '../header.php';
include 'queries.php';

$CUSTOM = custom::instance();
$CUSTOM->getCommunityInit()->initCommunities();
$CUSTOM->getCommunityInit()->initCollections();

initQueries();
function inval() {
	echo "<h1>Invalid Parameters</h1>";
	die();
}

try {
	$id = collectionArg::getId();
	if ($id == null) inval();
} catch (exception $e){
	echo "Err {$e}";
	inval();
}

$querycol = "";
$headercol = "";
foreach(query::$QUERIES as $q) {
  $querycol .= $q->mainItemQuery();
  $headercol .= "<th class='{$q->classes}'>{$q->header}</th>";
};



$sel = <<< EOF
select 
  h.handle, 
  regexp_replace(mv.text_value,E'[\r\n\t ]+',' ','g') as title,
  {$querycol}
  '' as blank
from item i2
inner join
  metadatavalue mv on mv.item_id = i2.item_id 
inner join metadatafieldregistry mfr on mfr.metadata_field_id = mv.metadata_field_id
  and mfr.element = 'title' and mfr.qualifier is null
inner join handle h on h.resource_id = i2.item_id and resource_type_id = 2
EOF;

$where = <<< EOF
order by title
limit 4000
EOF;

if (collectionArg::isCollection()) {
$sql = <<< EOF
{$sel}
inner join collection2item c2i 
  on c2i.item_id = i2.item_id 
  and c2i.collection_id = :pid
{$where};  
EOF;
} else if (collectionArg::isCommunity()) {
$comm2coll = query::comm2coll();
$sql = <<< EOF
{$comm2coll}
{$sel}
inner join r_comm2coll r 
  on r.collection_id = i2.owning_collection 
  and r.community_id = :pid
{$where};  
EOF;
}

header('Content-type: text/html; charset=UTF-8');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<?php 
$header = new LitHeader("QC Collection Overview for " . collectionArg::getName());
$header->litPageHeader();
?>
</head>
<body>
<?php 
$qargs = query::getFilterArgs();
if (collectionArg::isCollection())
	$header->litHeader(array("<a href='qcReportCollection.php{$qargs}' onclick='loadMsg()'>QC Overview for Collections</a>"));
else
	$header->litHeader(array("<a href='qcReportCommunity.php{$qargs}' onclick='loadMsg()'>QC Overview for Communities</a>"));
?>
<?php //$header->sqlDump($sql);?>
<?php
$dbh = $CUSTOM->getPdoDb();
$stmt = $dbh->prepare($sql);

$result = $stmt->execute(array(':pid' => $id));

$result = $stmt->fetchAll();

//$header->sqlDump($sql);


if ($result == null) {
	print($sql);
	print_r($dbh->errorInfo());
     die("Error in SQL query");
}       
?>
<div>
<p>Use the filters to select the attributes you wish to view and to filter the content you wish to see.  Click in the column headers to sort by value.
<br/>Note: a maximum of 4000 items will be displayed.</p>
<?php query::toolbar()?>
</div>
<table class="sortable">
<tbody>
<tr  class='header'>
  <th class="">Count</th>
  <th class="">Handle</th>
  <th class="title">Title</th>
  <?php echo $headercol;?>
</tr>

<?php
 // iterate over result set
 // print each row
 $c = 0;
foreach ($result as $row) {
 	 $class = ($c++ % 2 == 0) ? "allrow even" : "allrow odd";
 	 
 	 $vals = array();
 	 $handle = $row[0];
 	 $name = $row[1];
 	 foreach (query::$QUERIES as $k => $q) {
 	 	$vals[$q->name] = $row[$k+2];
 	 }
 	 
 	 echo "<tr class='" . $class . " allcoll'>";
 	 
     makedatacell($c,"head");
     makelinkdatacell($handle,"head");
     makedatacell($name,"title head");
 	 foreach (query::$QUERIES as $k => $q) {
 	 	$val = $vals[$q->name];
     	makecell($val,$q->classes, $q->testVal($vals, $val));
 	 }
     echo "</tr>";
 }       

?>
</tbody>
</table>
<?php $header->litFooter();?>
</body>
</html>

<?php 
  function makedatacell($obj, $class) {
  	echo "<td class='".$class."'>" . $obj . "</td>";
  }
  function makelinkdatacell($obj, $class) {
	$handleContext =  isset($GLOBALS['handleContext']) ? $GLOBALS['handleContext'] : "";
  	echo "<td class='".$class."'><a href='" . $handleContext . "/handle/" . $obj . "' target='_blank'>" . $obj . "</td>";
  }
  function makecell($val, $class, $test) {
  	if (!$test) {
  		$class .= " error";
  	}
  	echo "<td class='".$class."'>";
  	if ($val == 0) {
  		echo $val;  		
  	} else {
  		echo $val;
  	}
  	echo "</td>";
  }

?>

