<?php
include '../header.php';
include 'queries.php';

$CUSTOM = custom::instance();
$CUSTOM->getCommunityInit()->initCommunities();
$CUSTOM->getCommunityInit()->initCollections();


ini_set('max_execution_time', 120);

initQueries();
$querycol = "";
$headercol = "";
foreach(query::$QUERIES as $q) {
  $querycol .= $q->mainQuery();
  $headercol .= "<th class='{$q->classes}'>{$q->header}<hr/><span class='total'/></th>";
};

$where = util::getIdList("collex", "where coll.collection_id not in ");

$sql = <<< EOF
select 
  coll.collection_id, 
  collname.text_value as collectionName,
  {$querycol}
  '' as blank
from 
  collection coll
inner join 
  metadatavalue collname on collname.resource_id=coll.collection_id and collname.resource_type_id = 3
inner join metadatafieldregistry mfr on mfr.metadata_field_id = collname.metadata_field_id
  and mfr.element = 'title' and mfr.qualifier is null
{$where}
order by collectionName
;
  
EOF;

header('Content-type: text/html; charset=UTF-8');
?>
<html>
<head>
<?php 
$header = new LitHeader("QC Overview for Collections");
$header->litPageHeader();
?>
</head>
<body>
<?php $header->litHeader(array());?>
<?php //$header->sqlDump($sql);?>
<div>
<p>Use the filters to select the attributes you wish to view and to filter the content you wish to see.  Click in the column headers to sort by value.
<br/>Click on the collection name to see an item by item view of this report.</p>
<?php
community::toolbar();
query::toolbar();
echo auxFields::getShowOptToggle();
?>
</div>
<?php

$dbh = $CUSTOM->getPdoDb();
$result = $dbh->query($sql);
if (!$result) {
	print($sql);
  	print_r($dbh->errorInfo());
    die("Error in SQL query: ");
}       
?>
<div id="ins">
<table class="sortable">
<tbody>
<tr  class='header'>
  <th class="">Community<span class='total'/></th>
  <th class="title">Collection<span class='total'/></th>
  <?php echo $headercol;?>
</tr>

<?php
 // iterate over result set
 // print each row
 $c = 0;
 foreach ($result as $row) {
 	 $class = ($c++ % 2 == 0) ? "allrow even" : "allrow odd";
 	 
 	 $vals = array();
 	 $collection_id = array_shift($row);
 	 $name = array_shift($row);
 	 foreach (query::$QUERIES as $k => $q) {
 	 	$vals[$q->name] = $row[$k+1];
 	 }
 	 
 	 $coll = collection::$COLLECTIONS[$collection_id];
 	 $comm = $coll->topCommunity;
 	 
 	 echo "<tr class='" . $class . " comm" . $comm->community_id . " allcoll'>";
 	 
     makelinkcell($comm,"head");
     makelinkcell($coll,"title head");
 	 foreach (query::$QUERIES as $k => $q) {
 	 	$val = $vals[$q->name];
     	makecell($val,$q->classes, $q->testVal($vals, $val), $collection_id, $q->name, $q->getShowArrArg());
 	 }
     echo "</tr>";
 }       

?>
</tbody>
</table>
</div>
<?php $header->litFooter();?>
</body>
</html>

<?php 
  function makelinkcell($obj, $class) {
  	if ($obj instanceof community) {
      $str = "javascript:argLink('" . "qcReportItems.php?community=" . $obj->community_id . "');return false;";
      $href = 'onclick="' . $str . '"';
  	  $val = $obj->shortname;
	  echo "<td class='".$class."'><a href='#' {$href}>{$val}</a></td>";
  	} else {
      $str = "javascript:argLink('" . "qcReportItems.php?collection=" . $obj->collection_id . "');return false;";
      $href = 'onclick="' . $str . '"';
  	  $val = $obj->name;
	  echo "<td class='".$class."'><a href='#' {$href}>{$val}</a></td>";
  	}
  }
  function makedatacell($obj, $class) {
  	echo "<td class='".$class."'>" . $obj->name . "</td>";
  }
  function makecell($val, $class, $test, $coll, $qname, $showarg) {
  	if (!$test) {
  		$class .= " error";
  	}
  	echo "<td class='".$class."'>";
  	if ($val == 0) {
  		echo $val;  		
  	} else {
  		$str = "javascript:argLink('" . "items.php?collection={$coll}&qname={$qname}{$showarg}" . "');return false;";
  		$href = 'onclick="' . $str . '"';
		echo "<a href='#' {$href}>{$val}</a>";
  	}
  	echo "</td>";
  }

?>

