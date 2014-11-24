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

$MAX = 2000;

$coll  = util::getPostArg("coll","");
$comm  = util::getPostArg("comm","");
$op    = util::getPostArg("op","");
$field = util::getPostArg("field","");
$dfield = util::getPostArg("dfield",array());
$val    = util::getPostArg("val","");
$isCSV  = (util::getPostArg("query","") == "CSV Extract");
$offset    = util::getPostArg("offset","0");

if (util::getPostArg("query","") == "Prev Results") {
    $offset -= $MAX;
    if ($offset < 0) $offset = 0;
} else if (util::getPostArg("query","") == "Next Results") {
    $offset += $MAX;    
}

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

$mfields = array();
$dsel = "<select id='dfield' name='dfield[]' multiple size='10' disabled>";
$sel = "<select id='field' name='field' disabled><option value='0'>All</option>";
foreach ($result as $row) {
    $mfi = $row[0];
    $mfn = $row[4];
    $mfields[$mfi] = $mfn;
    $selected = sel($mfi, $field);
    $sel .= "<option value='{$mfi}' {$selected}>{$mfn}</option>";
    $selected = in_array($mfi, $dfield) ? "selected" : "";
    $dsel .= "<option value='{$mfi}' {$selected}>{$mfn}</option>";
}
$sel .= "</select>";
$dsel .= "</select>";

$status = "";
$hasPerm = $CUSTOM->isUserCollectionOwner();
if ($isCSV) {
    header("Content-type: text/csv");
    header("Content-Disposition: attachment; filename=export.csv"); 
} else {
    header('Content-type: text/html; charset=UTF-8');
}
   
if (!$isCSV) {
?>
<!DOCTYPE html>
<html>
<body>
<?php
$header = new LitHeader("Query Construction");
$header->litPageHeader();
?>
<script type="text/javascript">
$(document).ready(function(){
   if ($("#cstart").val() > 1) $("#querySubmitPrev").attr("disabled", false); 
   if ($("#rescount").val() == <?php echo $MAX?>) $("#querySubmitNext").attr("disabled", false); 
   if ($("#rescount").val() > 1) {
       $("#queryform input,#queryform select").attr("disabled", true);
       $("button.edit").attr("disabled", false);
       $("#queryCsv").attr("disabled", false);
   } else {
       $("#queryform input,#queryform select").attr("disabled", false);       
   }
   $("#dfield").attr("disabled", false);       
   $("#spinner").hide();
});

function doedit() {
    $('#queryform input,#queryform select').attr('disabled',false);
    $("#offset").val(0);
    $("#queryCsv,#querySubmitPrev,#querySubmitNext,button.edit").attr("disabled", true);
}

function prepSubmit() {
    $('#queryform input,#queryform select').attr('disabled',false);
    $("#spinner").show();
}
</script>
<style type="text/css">
body{width: 1000px;}
button.edit {float: right;}
#spinner {display: inline;float: left; height: 200px; width: 45%; border: none;}
fieldset.fields {width: 40%; display:inline;float: left; margin: 20px;}
div.clear {clear: both;}
</style>
</head>
<body>
<?php $header->litHeaderAuth(array(), $hasPerm);?>
<div id="selfQuery">
<form method="POST" action="" onsubmit="prepSubmit();">
<fieldset id="queryform">
<legend>Use this option to construct a quality control query </legend>
<button type="button" class="edit" name="edit" onclick="doedit();" disabled>Edit</button>
<div id="status"><?php echo $status?></div>
<?php collection::getCollectionIdWidget($coll, "coll", " to be queried*");?>
<?php collection::getSubcommunityIdWidget($comm, "comm", " to be queried*");?>
<p>
  <label for="field">Field to query</label>
  <?php echo $sel?>
  <label for="op">; Operator: </label>
  <select id="op" name="op" onchange="$('#val').val($(this).find('option:selected').attr('example'));" disabled>
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
  <input name="val" id="val" type="text" value="<?php echo $val?>" disabled/>
</p>
</fieldset>
<p>
  <fieldset class="fields">
  <legend>Fields to display</legend>
  <br/>
  <?php echo $dsel?>
  </fieldset>
  <iframe id="spinner" src="spinner.html"></iframe>
  <div class="clear"/>
</p>
<p align="center">
    <input id="offset" name="offset" type="hidden" value="<?php echo $offset?>"/>
    <input id="querySubmitPrev" name="query" value="Prev Results" type="submit" disabled/>
    <input id="querySubmit" name="query" value="Show Results" type="submit"/>
	<input id="querySubmitNext" name="query" value="Next Results" type="submit" disabled/>
    <input id="queryCsv" name="query" value="CSV Extract" type="submit" disabled/>
</p>
<p><em>* Up to <?php echo $MAX?> results will be returned</em></p>
</form>
</div>
<?php 
}

if (count($_POST) > 0) {

$sql = <<< EOF
select 
  c.name,
  ch.handle, 
  i.item_id,
  regexp_replace(mv.text_value,E'[\r\n\t ]+',' ','g') as title,
  ih.handle,
  i.discoverable,
  i.withdrawn,
EOF;

    $sep = $isCSV ? "||" : "<hr/>";
    foreach($dfield as $k) {
        if (is_numeric($k)) {
            $sql .= "(select array_to_string(array_agg(text_value), '{$sep}') from metadatavalue m where i.item_id=m.item_id and m.metadata_field_id={$k}),";
        }
    }

$sql .= <<< EOF
  1
from 
  collection c
inner join 
  item i on i.owning_collection=c.collection_id
inner join 
  handle ih on i.item_id = ih.resource_id and ih.resource_type_id = 2
inner join 
  handle ch on c.collection_id = ch.resource_id and ch.resource_type_id = 3
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
    
    if ($field == 0) {
        if ($op == "exists") {        
            $where = " and exists (select 1 from metadatavalue m where i.item_id = m.item_id)";
        } else if ($op == "not exists") {
            $where = " and not exists (select 1 from metadatavalue m where i.item_id = m.item_id)";
        } else if ($op == "equals") {
            $where = " and exists (select 1 from metadatavalue m where i.item_id = m.item_id and text_value=:val)";
            $arr[':val'] = $val;
        } else if ($op == "not equals") {
            $where = " and not exists (select 1 from metadatavalue m where i.item_id = m.item_id and text_value=:val)";
            $arr[':val'] = $val;
        } else if ($op == "like") {
            $where = " and exists (select 1 from metadatavalue m where i.item_id = m.item_id and text_value like :val)";
            $arr[':val'] = $val;
        } else if ($op == "not like") {
            $where = " and not exists (select 1 from metadatavalue m where i.item_id = m.item_id and text_value like :val)";
            $arr[':val'] = $val;
        } else if ($op == "matches") {
            $where = " and exists (select 1 from metadatavalue m where i.item_id = m.item_id and text_value ~ :val)";
            $arr[':val'] = $val;
        } else if ($op == "doesn't match") {
            $where = " and not exists (select 1 from metadatavalue m where i.item_id = m.item_id and text_value ~ :val)";
            $arr[':val'] = $val;
        }
        
    } else {
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
        
        
    }
    
    
    $sql .= $where . " limit {$MAX} offset {$offset}";

    $dbh = $CUSTOM->getPdoDb();
    $stmt = $dbh->prepare($sql);

    $result = $stmt->execute($arr);

    if (!$result) {
        print($sql);
        print_r($dbh->errorInfo());
        die("Error in SQL query");
    }       

    $result = $stmt->fetchAll();
    $rescount = count($result);
    $cstart = $offset + 1;
    $cend = $offset + $rescount;

    if (!$isCSV) {
        echo "<div id='export'>";
        echo "<input type='hidden' id='rescount' name='rescount' value='{$rescount}' readonly size='6'/>";
        echo "<div><input type='text' id='cstart' name='cstart' value='{$cstart}' readonly size='6'/> to ";
        echo "<input type='text' id='cend' name='cend' value='{$cend}' readonly size='6'/> items</div>";
        echo "<table class='sortable'>";
        echo "<tbody>";
        echo "<tr class='header'>";
        echo "<th>Result Num</th>";
        echo "<th class='title''>Collection</th>";
        echo "<th>Collection Handle</th>";
        echo "<th class='title''>Title</th>";
        echo "<th>Item Handle</th>";
        echo "<th>Item Status</th>";
        foreach($dfield as $k) {
            if (!is_numeric($k)) continue;
            echo "<th class=''>{$mfields[$k]}[en]</th>";
        }
        echo "</tr>";
    } else {
        echo "id,collection,dc.title[en]";
        foreach($dfield as $k) {
           if (!is_numeric($k)) continue;
           echo ",{$mfields[$k]}[en]";
        }
        echo "\n";
    }

    $handleContext =  isset($GLOBALS['handleContext']) ? $GLOBALS['handleContext'] : "";
    $c = 0;
    foreach ($result as $row) {
        $class = ($c++ % 2 == 0) ? "allrow even" : "allrow odd";
        $cname = $row[0];
        $ch = $row[1];
        $iid = $row[2];
        $title = $row[3];
        $ih = $row[4];
        $fdiscoverable = $row[5];
        $fwithdrawn = $row[6];
        $fstatus = ($fdiscoverable ? "" : "Private ") . ($fwithdrawn ? "Withdrawn" : ""); 
        $col = 6;
        if ($isCSV) {
            echo "{$iid},{$ch}" .',"' . $title . '"';
            foreach($dfield as $k) {
                if (!is_numeric($k)) continue;
                $col++;
                $val = $row[$col];
                $val = preg_replace("/\n/"," ",$val);
                $val = str_replace('/["]/',"",$val);
                echo ',"' . $val . '"';
            }
            echo "\n";
        } else {
            echo "<tr class='{$class}'>";
            echo "<td>{$c}</td>";
            echo "<td>{$cname}</td>";
            $href = $handleContext . '/handle/' .$ch . '?show=full';
            $disp = $ch;
    
            echo "<td><a href='{$href}'>{$disp}</a></td>";
            echo "<td>{$title}</td>";
            $href = $handleContext . '/handle/' .$ih . '?show=full';
            $disp = $ih;
    
            echo "<td><a href='{$href}'>{$disp}</a></td>";         
            echo "<td>{$fstatus}</td>";         
            foreach($dfield as $k) {
                if (!is_numeric($k)) continue;
                $col++;
                echo "<td>{$row[$col]}</td>";
            }
            echo "</tr>";
        }
        if ($c > 2000) break;
    }       

    if (!$isCSV) { 
        echo "</tbody>";
        echo "</table>";
        echo "</div>";
        $header->litFooter();
        echo "</body>";
        echo "</html>";
    }
}

function sel($val,$test) {
    return ($val == $test) ? 'selected' : '';
}
?>
