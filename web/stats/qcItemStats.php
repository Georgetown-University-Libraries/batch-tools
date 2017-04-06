<?php
include '../header.php';

ini_set('max_execution_time', 120);

$CUSTOM = custom::instance();
$shards = $CUSTOM->getSolrShards();
$qroot = $CUSTOM->getSolrPath() . "statistics/select?shards={$shards}&rows=0&wt=json";

$item=util::getArg("item","");
$ititle = $item == "" ? "All Items" : $item;
$isearch = "&q=type:2" . $item == "" ? "" : "+AND+id:{$item}";
$bsearch = "&q=type:0+AND+bundle:ORIGINAL" . $item == "" ? "" : "+AND+owningItem:{$item}";
$facet = "&facet=true&facet.date=time&facet.date.start=NOW/MONTH/DAY-30MONTHS&facet.date.end=NOW&facet.date.gap=".urlencode("+1MONTH");

header('Content-type: text/html; charset=UTF-8');

?>
<html>
<head>
<?php 
$header = new LitHeader($item);
$header->litPageHeader();
?>

<script type="text/javascript">
  $(document).ready(function(){
    $.getJSON("{$qroot}{$facet}{$isearch}", function(data){
      $("#foo").text(data);
    });
  });
</script>
<style type="text/css">
</style>

</head>
<body>
<?php $header->litHeader(array());?>

<form method="GET" action="qcItemStats.php">
<input type="text" name="item" value="<?php echo $item?>"/>
<input type="submit" value="Refresh"/>
</form>


<div id="ins">
<div id="foo"/>
<table class="sortable">
<tbody>
<tr  class='header'>
  <th>Month</th>
  <th>Item View</th>
  <th>Bitstream Views</th>
</tr>
</tbody>
</table>
</div>
<?php $header->litFooter();?>
</body>
</html>

