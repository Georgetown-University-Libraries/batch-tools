$(document).ready(function(){
	var tbl = $("<table id='table'/>");
	$("#report").replace(tbl);
	var tr = addTr(tbl);
	addTd(tr, "Num"));
	addTd(tr, "Collection"));
	
	
	$("#table").append()
	$.getJSON(
		"/rest/collections",
		function(data){
			$.each(data, function(index, coll){
				var tr = addTr($("#table"));
				addTd(tr, index);
				addTdAnchor(tr, coll.name, "/handle/" + coll.handle);
			});
		}
	);
});

function addTr(tbl) {
	var tr = $("<tr/>");
	tbl.append(tr);
	return tr;
}

function addTd(tr, val) {
	var td = $("<td/>");
	if (val != null) {
		td.append(val);
	}
	tr.append(td);
	return td;
}

function getTdAnchor(tr, val, href) {
	var a = $("<a/>");
	a.append(val);
	a.attr("href", href);
	return addTd(tr, a);
}