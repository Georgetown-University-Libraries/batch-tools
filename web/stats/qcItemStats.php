<?php
include '../header.php';

ini_set('max_execution_time', 120);

$CUSTOM = custom::instance();
$shards = $CUSTOM->getSolrShards();
$qroot = "/solr/statistics/select?shards={$shards}&rows=0&wt=json";

$handle=util::getArg("handle","");
$item="";
if ($handle != '') {
	$item = $CUSTOM->getQueryVal("select resource_id from handle where handle=:h", array(":h" => $handle));
}
$ititle = $item == "" ? "All Items" : $handle;

header('Content-type: text/html; charset=UTF-8');

?>
<html>
<head>
<?php 
$header = new LitHeader($ititle);
$header->litPageHeader();
?>

<script type="text/javascript">
  function getSolrHeader() {
    return '<?php echo $qroot;?>';
  }

  function getItem() {
    var item = $("#item").val();
    return item == "" ? "*" : item;
  }

  function runQuery(url, regex, col) {
    $.getJSON(url, function(data){
      var timeobj = data.facet_counts.facet_dates.time;
      var times = Object.keys(timeobj).reverse();
      for(var i=0; i<times.length; i++) {
        var ctime = times[i];
        var match = regex.exec(ctime);
        if (match == null) {
          continue;
        }
        var ctimestr = match[1];
        var count = timeobj[ctime];
        add(ctimestr, col, count);
      }
    });
  }
  
  $(document).ready(function(){
    var QIY = getSolrHeader() + "&q=type:2+AND+id:"+getItem()+"&facet=true&facet.date=time&facet.date.start=NOW/YEAR/DAY-5YEARS&facet.date.end=NOW&facet.date.gap=%2B1YEAR";
    var QIM = getSolrHeader() + "&q=type:2+AND+id:"+getItem()+"&facet=true&facet.date=time&facet.date.start=NOW/MONTH/DAY-60MONTHS&facet.date.end=NOW&facet.date.gap=%2B1MONTH";
    var QBY = getSolrHeader() + "&q=type:0+AND+bundleName:ORIGINAL+AND+owningItem:"+getItem()+"&facet=true&facet.date=time&facet.date.start=NOW/YEAR/DAY-5YEARS&facet.date.end=NOW&facet.date.gap=%2B1YEAR";
    var QBM = getSolrHeader() + "&q=type:0+AND+bundleName:ORIGINAL+AND+owningItem:"+getItem()+"&facet=true&facet.date=time&facet.date.start=NOW/MONTH/DAY-60MONTHS&facet.date.end=NOW&facet.date.gap=%2B1MONTH";
    runQuery(QIY, /^(\d\d\d\d).*/, "item");
    runQuery(QIM, /^(\d\d\d\d-\d\d-\d\d).*/, "item");
    runQuery(QBY, /^(\d\d\d\d).*/, "bit");
    runQuery(QBM, /^(\d\d\d\d-\d\d-\d\d).*/, "bit");
  });

  

  function add(ctimestr, col, val) {
    var tr = $("tr.data[date='"+ctimestr+"']");
    if (!tr.is("*")) {
      tr = $("<tr/>");
      tr.attr("class","data").attr("date",ctimestr);
      tr.append($("<th/>").text(ctimestr));
      tr.append($("<td/>").attr("class","item"));
      tr.append($("<td/>").attr("class","bit"));
      $("#datatbl tbody").append(tr);
    }
    tr.find("td."+col).text(val);
  }
</script>
<style type="text/css">
tr.data:even th, tr.data:even:td{background-color: #EEEEEE;}
tr.header th, tr.header:td{background-color: yellow;}
</style>

</head>
<body>
<?php $header->litHeader();?>
<form method="GET" action="qcItemStats.php">
<input type="hidden" id="item" name="item" value="<?php echo $item?>"/>
Item Handle: <input type="text" id="handle" name="handle" value="<?php echo $handle?>"/>
<input type="submit" id="refresh" value="Refresh"/>
</form>


<div id="ins">
<div id="foo"/>
<table id="datatbl" class="sortable">
<tbody>
<tr  class='header'>
  <th>Month</th>
  <th class="item">Item View</th>
  <th class="bit">Bitstream Views</th>
</tr>
</tbody>
</table>
</div>
<?php $header->litFooter();?>
</body>
</html>

