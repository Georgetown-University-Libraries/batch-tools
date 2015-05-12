<?php
include '../header.php';
include 'solrFacets.php';

ini_set('max_execution_time', 120);

$CUSTOM = custom::instance();

solrFacets::init($CUSTOM);
$duration=solrFacets::getDurationArg();
$type=solrFacets::getTypeArg();
$auth=solrFacets::getAuthArg();
$ip=solrFacets::getIpArg();

$scope=util::getArg("scope","ALL");
hierarchy::initHierarchy(($scope == "ALL"), $scope);
$colcount = solrFacets::getDurationKey("colcount");


header('Content-type: text/html; charset=UTF-8');

?>
<html>
<head>
<?php 
$header = new LitHeader(solrFacets::getTypeKey("desc") . " for the " . solrFacets::getDurationKey("desc") . " for " . solrFacets::getAuthKey("desc") . " from " . solrFacets::getIpKey("desc"));
$header->litPageHeader();
?>

<script type="text/javascript">
var first = true;
var complete = 0;

var getStatsFunc = function(cell, tbd) {  
    var id = cell.attr("id");
    var arr = /(comm|coll)-(\d+)-all/.exec(id);
    if (arr.length <= 2) return;
    var prefix = id.replace("all","");
    var req = "solrStats.php?" +
        arr[1] + "=" + arr[2] +  
        "&duration=" + $("#duration").val() +
        "&type=" + $("#type").val() +
        "&auth=" + $("#auth").val() +
        "&ip=" + $("#ip").val()
        ;
            
    $.getJSON(req,function(data){
        var colcount = parseInt($("#colcount").val());  
        var count=0;
        var times = new Array();
        for(var time in data.facet_counts.facet_dates.time) {
            times[count] = time;
            $("#t"+count).text(time.substr(0,10));
            count++;
            if (count >= colcount) break;
        }              
            
        count = 0;
        for(var time in data.facet_counts.facet_dates.time) {
            var range = "&time=[" + time + "+TO+" + ((count + 1 == times.length) ? "NOW" : times[count+1]) + "]";
            var val = parseInt(data.facet_counts.facet_dates.time[time]);
            $("#"+prefix+count).html("<a href='"+req+range+"&debug=rpt'>0</a>");
            $("#"+prefix+count).find("a").text(val);        
            count++;
            if (count > colcount) break;
        }
        first = false;
        cell.html("<a href='"+req+"&debug=rpt'>0</a>");
        cell.find("a").text(parseInt(data.response.numFound));
        complete++;
        if (complete == tbd) {
            $(".tot").each(function(){
                var v = 0;
                var id = $(this).attr("id");
                var cid = id.replace("tot-",".");
                $("tr.comm").find(cid).each(
                    function(){
                        v += parseInt($(this).text());
                    }
                );
                $(this).text(v);
            });
            $("#totall").each(function(){
                var v = 0;
                $("tr.comm").find(".data-all").each(
                    function(){
                        v += parseInt($(this).text());
                    }
                );
                $(this).text(v);
            });
        }
    });
};

var initScomm = false;
var initColl = false;

var doScomm = function() {
    if (initScomm) return;
    initScomm = true;
    $("tr.scomm .data-all").each(function(index){
        setTimeout(getStatsFunc, index * 400, $(this), 0);
    });
}

var doColl = function() {
    if (initColl) return;
    initColl = true;
    $("tr.coll .data-all").each(function(index){
        setTimeout(getStatsFunc, index * 200, $(this), 0);
    });
}

$(document).ready(function(){
    $("#cfscomm").click(doScomm);
    $("#cfcoll").click(doColl);
    $("tr.comm .data-all").each(function(index){
        setTimeout(getStatsFunc, index * 500, $(this), $("tr.comm .data-all").length);
    });

    $("td.data").show();
    $("input.cfilter").change(
      function(){
          if ($("#cfcomm:checked").is("*")) {
              $("tr.comm").show();
          } else {
              $("tr.comm").hide();              
          }
          if ($("#cfscomm:checked").is("*")) {
              $("tr.scomm").show();
          } else {
              $("tr.scomm").hide();              
          }
          if ($("#cfcoll:checked").is("*")) {
              $("tr.coll").show();
          } else {
              $("tr.coll").hide();              
          }
      }
    );
})
</script>
<style type="text/css">
  tr.comm {background-color: #e4c3f4;}
  tr.scomm {background-color: cyan; display: none}
  tr.coll {display: none;}
  td.data {display:none;}
</style>

</head>
<body>
<?php $header->litHeader(array());?>

<form method="GET" action="qcHierarchyStats.php">
<select name="duration" id="duration">
<?php
foreach(solrFacets::$DURATION as $k => $v) {
  util::makeOpt($k,$v['desc'],$duration);
}
?>
</select>

<select name="type" id="type">
<?php
foreach(solrFacets::$TYPE as $k => $v) {
  util::makeOpt($k,$v['desc'],$type);
}
?>
</select>

<select name="auth" id="auth">
<?php
foreach(solrFacets::$AUTH as $k => $v) {
  util::makeOpt($k,$v['desc'],$auth);
}
?>
</select>

<select name="ip" id="ip">
<?php
foreach(solrFacets::$IP as $k => $v) {
  util::makeOpt($k,$v['desc'],$ip);
}
?>
</select>

<select name="scope">
<?php
foreach($CUSTOM->getStatsComm() as $k => $v) {
  util::makeOpt($k,$v,$scope);
}
?>
</select>
<input type="hidden" name="colcount" id="colcount" value="<?php echo $colcount?>"/>
<input type="submit" value="Refresh"/>
</form>

<div>
<b>Show:</b>
<input type="checkbox" name="cfilter" class="cfilter" value="comm" id="cfcomm" checked><label for="cfcomm">Communities</label>
<input type="checkbox" name="cfilter" class="cfilter" value="scomm" id="cfscomm"><label for="cfscomm">Sub-communities</label>
<input type="checkbox" name="cfilter" class="cfilter" value="coll" id="cfcoll"><label for="cfcoll">Collections</label>
</div>

<div id="ins">
<table class="sortable">
<tbody>
<tr  class='header'>
  <th style="width:600px">Path</th>
  <th class="">Handle</th>
  <?php
  for($i=0; $i<$colcount; $i++){
    echo '<th class="sorttable_numeric" id="t' . $i .'">Loading...</th>';
  } 
  ?>
  <th class="sorttable_numeric" id="all">All Time</th>
</tr>

<?php
 // iterate over result set
 // print each row
 $c = 0;
 foreach (hierarchy::$OBJECTS as $obj) {
    $class = ($c++ % 2 == 0) ? "allrow even" : "allrow odd";
      
    echo "<tr class='".$obj->rclass."' colls='" . implode(",", $obj->getChildCollections()) . "'>";
      
    echo "<td>" . $obj->path . "</td>";
    echo "<td><a href='/handle/" . $obj->handle . "'>" . $obj->handle . "</td>";
    for($i=0; $i<$colcount; $i++){
        echo "<td class='data data-" . $i . "' id='" . $obj->hid . "-" . $i . "'>";
        echo "-</td>";
    } 
    echo "<td class='data data-all' id='".$obj->hid."-all'>-</td>";
    echo "</tr>";
 }       
?>
<tr class='total'>
  <th style="width:600px">Total</th>
  <th class=""></th>
  <?php
  for($i=0; $i<$colcount; $i++){
    echo '<td class="sorttable_numeric tot" id="tot-data-' . $i .'">0</td>';
  } 
  ?>
  <td class="sorttable_numeric" id="totall">0</td>
</tr>

</tbody>
</table>
</div>
<?php $header->litFooter();?>
</body>
</html>

