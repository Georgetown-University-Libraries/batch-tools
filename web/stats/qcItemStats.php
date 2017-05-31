<?php
include '../header.php';

ini_set('max_execution_time', 120);

$handle=trim(util::getArg("handle",""));

header('Content-type: text/html; charset=UTF-8');

?>
<html>
<head>
<?php 
$header = new LitHeader("Item Stats {$handle}");
$header->litPageHeader();
?>

<script type="text/javascript">

  function runQuery(handle, type, dur, num, col, filterbots) {
    var regex = dur == "YEAR" ? /^(\d\d\d\d)/ : /^(\d\d\d\d-\d\d)/;
    var rclass  = dur == "YEAR" ? "year" : "month";
    $.getJSON(
        "qcItemStatsSolr.php", 
        {
          handle: handle,
          type: type,
          dur: dur,
          num: num,
          filterbots: filterbots? "Y" : ""
        },
        function(data){
          if (data == null) return;
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
        }
    );
  }

  $(document).ready(function(){
    var handle = $("#handle").val();
    runQuery(handle, "item", "YEAR", 5, "item", false);
    runQuery(handle, "bit", "YEAR", 5, "bit", false);
    runQuery(handle, "item", "YEAR", 5, "itemf", true);
    runQuery(handle, "bit", "YEAR", 5, "bitf", true);

    runQuery(handle, "item", "MONTH", 60, "item", false);
    runQuery(handle, "bit", "MONTH", 60, "bit", false);
    runQuery(handle, "item", "MONTH", 60, "itemf", true);
    runQuery(handle, "bit", "MONTH", 60, "bitf", true);
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
tr.data:nth-child(2n) th, tr.data:nth-child(2n) td{background-color: #EEEEEE;}
tr.header th, tr.header td{background-color: yellow;}
tr.year th, tr.year td {color: red;}
tr.data td {text-align: right;}
</style>

</head>
<body>
<?php $header->litHeader(array());?>
<form method="GET" action="qcItemStats.php">
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

