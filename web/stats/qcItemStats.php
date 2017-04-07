<?php
include '../header.php';

ini_set('max_execution_time', 120);

$CUSTOM = custom::instance();
$shards = $CUSTOM->getSolrShards();
$qroot = "/solr/statistics/select?shards={$shards}&rows=0&wt=json";

$handle=util::getArg("handle","");
$item="";
if ($handle == '') {
	$item = "";
	$ititle = "All Items";
} else {
	$item = -1;
	$item = $CUSTOM->getQueryVal("select resource_id from handle where handle=:h", array(":h" => $handle));
	if ($item == null) {
		$item = -1;
		$ititle = "{$handle} not found";
	} else {
		$ititle = $handle;
	}
}

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
  function getStatsBotsStr() {
    return '<?php echo $CUSTOM->getStatsBotsStr();?>';
  }

  function getItem() {
    var item = $("#item").val();
    return item == "" ? "*" : item;
  }

  function runQuery(url, regex, col, rclass) {
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
        add(ctimestr, col, count, rclass);
      }
    });
  }

  function getDateFacet(NUM,DUR) {
    return "&facet=true&facet.date=time&facet.date.start=NOW/"+DUR+"/DAY-"+NUM+DUR+"S&facet.date.end=NOW&facet.date.gap=%2B1"+DUR;
  }

  $(document).ready(function(){
    var QIY  = getSolrHeader() + "&q=type:2+AND+id:"+getItem()+getDateFacet(5,"YEAR");
    var QIM  = getSolrHeader() + "&q=type:2+AND+id:"+getItem()+getDateFacet(60,"MONTH");
    var QIYF = getSolrHeader() + "&q=type:2+AND+id:"+getItem()+getDateFacet(5,"YEAR")+getStatsBotsStr();
    var QIMF = getSolrHeader() + "&q=type:2+AND+id:"+getItem()+getDateFacet(60,"MONTH")+getStatsBotsStr();
    var QBY  = getSolrHeader() + "&q=type:0+AND+bundleName:ORIGINAL+AND+owningItem:"+getItem()+getDateFacet(5,"YEAR");
    var QBM  = getSolrHeader() + "&q=type:0+AND+bundleName:ORIGINAL+AND+owningItem:"+getItem()+getDateFacet(60,"MONTH");
    var QBYF = getSolrHeader() + "&q=type:0+AND+bundleName:ORIGINAL+AND+owningItem:"+getItem()+getDateFacet(5,"YEAR")+getStatsBotsStr();
    var QBMF = getSolrHeader() + "&q=type:0+AND+bundleName:ORIGINAL+AND+owningItem:"+getItem()+getDateFacet(60,"MONTH")+getStatsBotsStr();
    runQuery(QIY,  /^(\d\d\d\d).*/, "item", "year");
    runQuery(QIM,  /^(\d\d\d\d-\d\d).*/, "item", "month");
    runQuery(QIYF, /^(\d\d\d\d).*/, "itemf", "year");
    runQuery(QIMF, /^(\d\d\d\d-\d\d).*/, "itemf", "month");
    runQuery(QBY,  /^(\d\d\d\d).*/, "bit", "year");
    runQuery(QBM,  /^(\d\d\d\d-\d\d).*/, "bit", "month");
    runQuery(QBY,  /^(\d\d\d\d).*/, "bitf", "year");
    runQuery(QBM,  /^(\d\d\d\d-\d\d).*/, "bitf", "month");
  });

  

  function add(ctimestr, col, val, rclass) {
    var tr = $("tr.data[date='"+ctimestr+"']").addClass(rclass);
    if (!tr.is("*")) {
      tr = $("<tr/>");
      tr.attr("class","data").attr("date",ctimestr);
      tr.append($("<th/>").text(ctimestr));
      tr.append($("<td/>").attr("class","item"));
      tr.append($("<td/>").attr("class","itemf"));
      tr.append($("<td/>").attr("class","bit"));
      tr.append($("<td/>").attr("class","bitf"));
      $("#datatbl tbody").append(tr);
    }
    tr.find("td."+col).text(val);
  }
</script>
<style type="text/css">
tr.data:nth-child(2) th, tr.data:nth-child(2) td{background-color: #EEEEEE;}
tr.header th, tr.header:td{background-color: yellow;}
tr.year th, tr.year td {color: red;}
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
  <th class="itemf">Item View (bots filtered)</th>
  <th class="bit">Bitstream Views</th>
  <th class="bitf">Bitstream Views (bots filtered)</th>
</tr>
</tbody>
</table>
</div>
<?php $header->litFooter();?>
</body>
</html>

