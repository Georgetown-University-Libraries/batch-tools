<?php
/*
User form for initiating the move of a collection to another community.  Note: in order to properly re-index the repository, 
DSpace will need to be taken offline after running this operation.
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
include '../header.php';
include 'queries.php';

$CUSTOM = custom::instance();
$CUSTOM->getCommunityInit()->initCommunities();
$CUSTOM->getCommunityInit()->initCollections();

$coll  = util::getPostArg("coll","");
$comm  = util::getPostArg("comm","");
$op    = util::getPostArg("op","");
$field = util::getPostArg("field","");
$val   = util::getPostArg("val","");

$sql = <<< EOF
select 
  mfr.metadata_field_id, 
  msr.short_id, 
  mfr.element, 
  mfr.qualifier, 
  (msr.short_id || '.' || mfr.element || case when mfr.qualifier is null then '' else '.' || mfr.qualifier end) as name
from metadatafieldregistry mfr
inner join metadataschemaregistry msr on msr.metadata_schema_id=mfr.metadata_schema_id
order by msr.short_id, mfr.element, mfr.qualifier;
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

$sel = "<select id='field' name='field'>";
foreach ($result as $row) {
    $selected = sel($row[0], $field);
    $sel .= "<option value='{$row[0]}' {$selected}>{$row[4]}</option>";
}
$sel .= "</select>";

$status = "";
$hasPerm = $CUSTOM->isUserCollectionOwner();
header('Content-type: text/html; charset=UTF-8');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<?php 
$header = new LitHeader("Query Construction");
$header->litPageHeader();
$op = util::getPostArg("op","");
?>
</head>
<body>
<?php $header->litHeaderAuth(array(), $hasPerm);?>
<div id="selfQuery">
<form method="POST" action="">
<p>Use this option to construct a quality control query</p>
<div id="status"><?php echo $status?></div>
<?php collection::getCollectionIdWidget($coll, "coll", " to be queried*");?>
<?php collection::getSubcommunityIdWidget($comm, "comm", " to be queried*");?>
<p>
  <label for="field">Field to query</label>
  <?php echo $sel?>
  <label for="op">; Operator: </label>
  <select id="op" name="op" onchange="$('#val').val($(this).find('option:selected').attr('example'));">
    <option value="exists" example="" <?php echo sel($op,'exists')?>>Exists</value>
    <option value="not exists" example="" <?php echo sel($op,'not exists')?>>Doesn't exist</value>
    <option value="equals" example="val" <?php echo sel($op,'equals')?>>Equals</value>
    <option value="not equals" example="val" <?php echo sel($op,'not equals')?>>Not Equals</value>
    <option value="like" example="%val%" <?php echo sel($op,'like')?>>Like</value>
    <option value="not like" example="%val%" <?php echo sel($op,'not like')?>>Not Like</value>
    <option value="matches" example="^.*(val1|val2).*$" <?php echo sel($op,'matches')?>>Matches</value>
    <option value="doesn't match" example="^.*(val1|val2).*$" <?php echo sel($op,"doesn't match")?>>Doesn't Matches</value>
  </select>
  <label for="val">; Value: </label>
  <input name="val" id="val" type="text" value="<?php echo $val?>"/>
</p>
<p align="center">
	<input id="querySubmit" type="submit" title="Submit Form"/>
</p>
<p><em>* One of the 2 selection fields is required</em></p>
</form>
</div>
<?php 
if (count($_POST) > 0) {

$sql = <<< EOF
select 
  c.name,
  i.item_id,
  regexp_replace(mv.text_value,E'[\r\n\t ]+',' ','g') as title,
  handle
from 
  collection c
inner join 
  item i on i.owning_collection=c.collection_id
inner join 
  handle on i.item_id = resource_id and resource_type_id = 2
left join
  metadatavalue mv on mv.item_id = i.item_id 
inner join metadatafieldregistry mfr on mfr.metadata_field_id = mv.metadata_field_id
  and mfr.element = 'title' and mfr.qualifier is null
where 
EOF;

    $arr = array();
    if ($coll != "") {
        $sql .= " c.collection_id = :pid";
        $arr[':pid'] = $coll;
    } else if ($comm != ""){
        $sql = query::comm2coll() . $sql . " c.collection_id in (select collection_id from r_comm2coll where community_id = :pid)";
        $arr[':pid'] = $comm;
    } else {
        $sql .= " 1=1";
    }

    $where = "";
    if ($op == "exists") {        
        $where = " and exists (select 1 from metadatavalue m where i.item_id = m.item_id and metadata_field_id = :field)";
        $arr[':field'] = $field;
    } else if ($op == "not exists") {
        $where = " and not exists (select 1 from metadatavalue m where i.item_id = m.item_id and metadata_field_id = :field)";
        $arr[':field'] = $field;
    } else if ($op == "equals") {
        $where = " and exists (select 1 from metadatavalue m where i.item_id = m.item_id and metadata_field_id = :field and text_value=:val)";
        $arr[':field'] = $field;
        $arr[':val'] = $val;
    } else if ($op == "not equals") {
        $where = " and not exists (select 1 from metadatavalue m where i.item_id = m.item_id and metadata_field_id = :field and text_value=:val)";
        $arr[':field'] = $field;
        $arr[':val'] = $val;
    } else if ($op == "like") {
        $where = " and exists (select 1 from metadatavalue m where i.item_id = m.item_id and metadata_field_id = :field and text_value like :val)";
        $arr[':field'] = $field;
        $arr[':val'] = $val;
    } else if ($op == "not like") {
        $where = " and not exists (select 1 from metadatavalue m where i.item_id = m.item_id and metadata_field_id = :field and text_value like :val)";
        $arr[':field'] = $field;
        $arr[':val'] = $val;
    } else if ($op == "matches") {
        $where = " and exists (select 1 from metadatavalue m where i.item_id = m.item_id and metadata_field_id = :field and text_value ~ :val)";
        $arr[':field'] = $field;
        $arr[':val'] = $val;
    } else if ($op == "doesn't match") {
        $where = " and not exists (select 1 from metadatavalue m where i.item_id = m.item_id and metadata_field_id = :field and text_value ~ :val)";
        $arr[':field'] = $field;
        $arr[':val'] = $val;
    }
    $sql .= $where;

    $dbh = $CUSTOM->getPdoDb();
    $stmt = $dbh->prepare($sql);

    $result = $stmt->execute($arr);

    if (!$result) {
        print($sql);
        print_r($dbh->errorInfo());
        die("Error in SQL query");
    }       

    $result = $stmt->fetchAll();

?>
<div id="export">
<table class="sortable">
<tbody>
<tr  class='header'>
  <th class="">Count</th>
  <th class="title">Collection</th>
  <th class="title">Title</th>
  <th class="">Handle</th>
</tr>

<?php
$handleContext =  isset($GLOBALS['handleContext']) ? $GLOBALS['handleContext'] : "";
$c = 0;
foreach ($result as $row) {
     $class = ($c++ % 2 == 0) ? "allrow even" : "allrow odd";
    echo "<tr class='{$class}'>";
    echo "<td>{$c}</td>";
    echo "<td>{$row[0]}</td>";
    echo "<td>{$row[2]}</td>";
    $h = $row[3];
    $href = $handleContext . '/handle/' .$h . '?show=full';
    $disp = $h;
    
    echo "<td><a href='{$href}'>{$disp}</a></td>";
    echo "</tr>";
}       

?>
</tbody>
</table>
</div>
<?php
}
?>

<?php $header->litFooter();?>
</body>
</html>

<?php
function sel($val,$test) {
    return ($val == $test) ? 'selected' : '';
}
?>
