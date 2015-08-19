<?php
include '../header.php';
include 'queries.php';

$CUSTOM = custom::instance();
$CUSTOM->getCommunityInit()->initCommunities();

ini_set('max_execution_time', 120);

$where = util::getIdList("collex", "and i.owning_collection not in ");

initQueries();
$querycol = "";
$headercol = "";
foreach(query::$QUERIES as $q) {
  $querycol .= $q->commQuery($where);
  $headercol .= "<th class='{$q->classes}'>{$q->header}</th>";
};

$sql = <<< EOF
select 
  comm.community_id, 
  commname.text_value as communityName,
  {$querycol}
  '' as blank
from 
  community comm
inner join 
  metadatavalue commname as commname.resource_id=comm.community_id and commname.resource_type_id = 4

order by communityName
;
  
EOF;

header('Content-type: text/html; charset=UTF-8');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<?php 
$header = new LitHeader("QC Overview for Communities");
$header->litPageHeader();
?>
</head>
<body>
<?php $header->litHeader(array());?>
<?php //$header->sqlDump($sql);?>
<div>
<p>Use the filters to select the attributes you wish to view and to filter the content you wish to see.  Click in the column headers to sort by value.
<br/>Collections may contain sub-collections.  Item counts in this report are totalled for every community and sub-community.
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
  <th class="title">Community</th>
  <?php echo $headercol;?>
</tr>

<?php
 // iterate over result set
 // print each row
 $c = 0;
 foreach ($result as $row) {
 	 $class = ($c++ % 2 == 0) ? "allrow even" : "allrow odd";
 	 
 	 $vals = array();
 	 $community_id = array_shift($row);
 	 $name = array_shift($row);
 	 foreach (query::$QUERIES as $k => $q) {
 	 	$vals[$q->name] = $row[$k+1];
 	 }
 	 
 	 $comm = community::$COMMUNITIES[$community_id];
 	 
 	 echo "<tr class='" . $class . " comm" . $comm->community_id . " allcoll'>";
 	 
     makelinkcell($comm,"title head");
 	 foreach (query::$QUERIES as $k => $q) {
 	 	$val = $vals[$q->name];
     	makecell($val,$q->classes, $q->testVal($vals, $val), $community_id, $q->name, $q->getShowArrArg());
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
      $str = "javascript:argLink('" . "qcReportItems.php?community=" . $obj->community_id . "');return false;";
      $href = 'onclick="' . $str . '"';
  	  $val = $obj->name;
	  echo "<td class='".$class."'><a href='#' {$href}>{$val}</a></td>";
  }
  function makedatacell($obj, $class) {
  	echo "<td class='".$class."'>" . $obj->name . "</td>";
  }
  function makecell($val, $class, $test, $comm, $qname, $showarg) {
  	if (!$test) {
  		$class .= " error";
  	}
  	echo "<td class='".$class."'>";
  	if ($val == 0) {
  		echo $val;  		
  	} else {
  		$str = "javascript:argLink('" . "items.php?community={$comm}&qname={$qname}{$showarg}" . "');return false;";
  		$href = 'onclick="' . $str . '"';
		echo "<a href='#' {$href}>{$val}</a>";
  	}
  	echo "</td>";
  }

?>

