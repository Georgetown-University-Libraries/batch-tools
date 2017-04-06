<?php
include '../header.php';

ini_set('max_execution_time', 120);

$CUSTOM = custom::instance();

$item=util::getArg("item","");
$ititle = $item == "" ? "All Items" : $item;
$isearch = "type:2" . $item == "" ? "" : "+AND+id:{$item}";
$bsearch = "type:0+AND+bundle:ORIGINAL" . $item == "" ? "" : "+AND+owningItem:{$item}";

header('Content-type: text/html; charset=UTF-8');

?>
<html>
<head>
<?php 
$header = new LitHeader();
$header->litPageHeader();
?>

<script type="text/javascript">

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

