<?php
header("Content-type: text");
include '../web/util.php';

if (false) {
  $json_a = util::json_get("https://demo.dspace.org/rest/communities/?expand=subCommunities");

  //var_dump($json_a);
  foreach($json_a as $k=>$comm) {
	showComm(0, $comm);
	if (!isset($comm["subcommunities"])) continue;
	foreach($comm["subcommunities"] as $scomm) {
		showComm($comm["id"], $scomm);
	}
  }
	
} else {
    showHier("https://demo.dspace.org/rest/hierarchy");
    showHier("https://localhost/rest/hierarchy");
    showHier("http://localhost/rest/hierarchy");
    showHier("https://localhost/oai/request?verb=ListRecords&metadataPrefix=oai_dc&set=col_10822_1043690");
    showHier("http://localhost/oai/request?verb=ListRecords&metadataPrefix=oai_dc&set=col_10822_1043690");
    showHier("https://localhost/solr/statistics/select?q=type%3A2+AND+id%3A2924%0A%0A&wt=json&indent=true");
    showHier("http://localhost/solr/statistics/select?q=type%3A2+AND+id%3A2924%0A%0A&wt=json&indent=true");
    showHier("https://repository.library.georgetown.edu/oai/request?verb=ListRecords&metadataPrefix=oai_dc&set=col_10822_1043690");
    
}

function showHier($url) {
    echo "\n\n  *** {$url} *** \n================================\n";
    $isNotLocal = (strpos($url, '//localhost') === false);
    $m = $isNotLocal ? "true" : "false";
    echo "Not Local: {$m}\n";
    $data = util::url_get($url);
    echo strlen($data) . "\n";
    $json_a = util::json_get($url);
    if ($json_a) echo "hasJson\n";
    /*
    foreach($json_a as $k=>$comm) {
        showColl($comm);
        if (!isset($comm["subcommunities"])) continue;
        foreach($comm["subcommunities"] as $scomm) {
            showColl($scomm);
        }
    }
    */
}

function showComm($pid, $comm) {
	echo $comm["id"] . "\t" . $comm["name"] . "\t" . $comm["handle"] . "\t" . $pid . "\n";
	//var_dump($comm);
}

function showColl($comm) {
	if ($comm["collections"] != null) {
		foreach($comm["collections"] as $coll) {
			showComm($comm["id"], $coll);
		}
	}
}


?>

