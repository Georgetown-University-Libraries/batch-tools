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
include 'selfQueryCommon.php';

$CUSTOM = custom::instance();
$CUSTOM->getCommunityInit()->initCommunities();
$CUSTOM->getCommunityInit()->initCollections();

$MAX = 2000;

$coll  = util::getPostArg("coll","");
$comm  = util::getPostArg("comm","");
$op    = util::getPostArg("op",array());
$field = util::getPostArg("field",array());
$dfield = util::getPostArg("dfield",array());
$filter = util::getPostArg("filter",array());
$val    = util::getPostArg("val",array());
$isCSV  = (util::getPostArg("query","") == "CSV Extract");
$offset    = util::getPostArg("offset","0");

$mfields = initFields($CUSTOM);

if ($isCSV) {
    header("Content-type: text/csv");
    header("Content-Disposition: attachment; filename=export.csv"); 
} else {
    header('Content-type: text/html; charset=UTF-8');
}
   
if (count($_POST) > 0) {

$sep = $isCSV ? "||" : "<hr/>";

$sql = ($comm != "") ? query::comm2coll() : "";
$csql = ($comm != "") ? query::comm2coll() : "";

$csql .= "select count(*) from collection c inner join item i on i.owning_collection=c.collection_id where";

$sql .= <<< EOF
select 
  c.name,
  ch.handle, 
  i.item_id,
  (
EOF;
$sql .= "select array_to_string(array_agg(mv.text_value), '{$sep}')";
$sql .= <<< EOF
    from metadatavalue mv 
    inner join metadatafieldregistry mfr 
    on mfr.metadata_field_id = mv.metadata_field_id
    and mfr.element = 'title' and mfr.qualifier is null
    where mv.item_id = i.item_id
  ),  		
  ih.handle,
  i.discoverable,
  i.withdrawn,
EOF;

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
where 
EOF;

    $arr = array();
    if ($coll != "") {
        $where = " c.collection_id = :pid";
        $arr[':pid'] = $coll;
    } else if ($comm != ""){
        $where = " c.collection_id in (select collection_id from r_comm2coll where community_id = :pid)";
        $arr[':pid'] = $comm;
    } else {
        $where .= " 1=1";
    }
    
    $filters = initFilters();
    foreach($filters as $key => $obj) {
    	if (in_array($key, $filter)) {
    		$where .= $obj['sql'];
    	}
    }
    
    for($i=0; $i<count($field); $i++) {
      if ($field[$i] == "") {
      } else if ($field[$i] == 0) {
        if ($op[$i] == "exists") {        
            $where .= " and exists (select 1 from metadatavalue m where i.item_id = m.item_id)";
        } else if ($op[$i] == "not exists") {
            $where .= " and not exists (select 1 from metadatavalue m where i.item_id = m.item_id)";
        } else if ($op[$i] == "equals") {
            $where .= " and exists (select 1 from metadatavalue m where i.item_id = m.item_id and text_value=:val{$i})";
            $arr[":val{$i}"] = $val[$i];
        } else if ($op[$i] == "not equals") {
            $where .= " and not exists (select 1 from metadatavalue m where i.item_id = m.item_id and text_value=:val{$i})";
            $arr[":val{$i}"] = $val[$i];
        } else if ($op[$i] == "like") {
            $where .= " and exists (select 1 from metadatavalue m where i.item_id = m.item_id and text_value like :val{$i})";
            $arr[":val{$i}"] = $val[$i];
        } else if ($op[$i] == "not like") {
            $where .= " and not exists (select 1 from metadatavalue m where i.item_id = m.item_id and text_value like :val{$i})";
            $arr[":val{$i}"] = $val[$i];
        } else if ($op[$i] == "matches") {
            $where .= " and exists (select 1 from metadatavalue m where i.item_id = m.item_id and text_value ~ :val{$i})";
            $arr[":val{$i}"] = $val[$i];
        } else if ($op[$i] == "doesn't match") {
            $where .= " and not exists (select 1 from metadatavalue m where i.item_id = m.item_id and text_value ~ :val{$i})";
            $arr[":val{$i}"] = $val[$i];
        }
      } else {
        if ($op[$i] == "exists") {        
            $where .= " and exists (select 1 from metadatavalue m where i.item_id = m.item_id and metadata_field_id = :field{$i})";
            $arr[":field{$i}"] = $field[$i];
        } else if ($op[$i] == "not exists") {
            $where .= " and not exists (select 1 from metadatavalue m where i.item_id = m.item_id and metadata_field_id = :field{$i})";
            $arr[":field{$i}"] = $field[$i];
        } else if ($op[$i] == "equals") {
            $where .= " and exists (select 1 from metadatavalue m where i.item_id = m.item_id and metadata_field_id = :field{$i} and text_value=:val{$i})";
            $arr[":field{$i}"] = $field[$i];
            $arr[":val{$i}"] = $val[$i];
        } else if ($op[$i] == "not equals") {
            $where .= " and not exists (select 1 from metadatavalue m where i.item_id = m.item_id and metadata_field_id = :field{$i} and text_value=:val{$i})";
            $arr[":field{$i}"] = $field[$i];
            $arr[":val{$i}"] = $val[$i];
        } else if ($op[$i] == "like") {
            $where .= " and exists (select 1 from metadatavalue m where i.item_id = m.item_id and metadata_field_id = :field{$i} and text_value like :val{$i})";
            $arr[":field{$i}"] = $field[$i];
            $arr[":val{$i}"] = $val[$i];
        } else if ($op[$i] == "not like") {
            $where .= " and not exists (select 1 from metadatavalue m where i.item_id = m.item_id and metadata_field_id = :field{$i} and text_value like :val{$i})";
            $arr[":field{$i}"] = $field[$i];
            $arr[":val{$i}"] = $val[$i];
        } else if ($op[$i] == "matches") {
            $where .= " and exists (select 1 from metadatavalue m where i.item_id = m.item_id and metadata_field_id = :field{$i} and text_value ~ :val{$i})";
            $arr[":field{$i}"] = $field[$i];
            $arr[":val{$i}"] = $val[$i];
        } else if ($op[$i] == "doesn't match") {
            $where .= " and not exists (select 1 from metadatavalue m where i.item_id = m.item_id and metadata_field_id = :field{$i} and text_value ~ :val{$i})";
            $arr[":field{$i}"] = $field[$i];
            $arr[":val{$i}"] = $val[$i];
        }    
      }    
    }
    
    
    $sql .= $where . " limit {$MAX} offset {$offset}";
    $csql .= $where;
    
    $dbh = $CUSTOM->getPdoDb();
    
    $ccount = 0;
    if (!$isCSV) {
        $cstmt = $dbh->prepare($csql);
        $cresult = $cstmt->execute($arr);
        if (!$cresult) {
    	    print($csql);
    	    print_r($arr);
    	    print_r($dbh->errorInfo());
    	    die("Error in SQL query");
        }
        $cresult = $cstmt->fetchAll();
        foreach ($cresult as $row) {
        	$ccount = $row[0];
        }
    }
    
    $stmt = $dbh->prepare($sql);
    $result = $stmt->execute($arr);

    if (!$result) {
        print($sql);
        print_r($arr);
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
        echo "<input type='text' id='cend' name='cend' value='{$cend}' readonly size='6'/>";
        echo " of <input type='text' id='ccount' name='ccount' value='{$ccount}' readonly size='6'/> items</div>";
        echo "</div>";
        echo "<table class='sortable'>";
        echo "<thead>";
        echo "<tr class='header'>";
        echo "<th>Result Num</th>";
        echo "<th class='title''>Collection</th>";
        echo "<th>Collection Handle</th>";
        echo "<th class='title''>Title</th>";
        echo "<th>Item Handle</th>";
        echo "<th>Item Status</th>";
        foreach($dfield as $k) {
            if (!is_numeric($k)) continue;
            $f = $mfields[$k];
            $l = preg_match("/^dc\.(date|identifier).*/",$f) ? "" : "[en]";
            echo "<th class=''>{$f}{$l}</th>";
        }
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";
    } else {
        echo "id,collection,dc.title[en]";
        foreach($dfield as $k) {
            if (!is_numeric($k)) continue;
            $f = $mfields[$k];
            $l = preg_match("/^dc\.(date|identifier).*/",$f) ? "" : "[en]";
           echo ",{$f}{$l}";
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
    }
}

?>
