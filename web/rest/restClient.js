$(document).ready(function(){
	var tbl = $("<table>");
	tbl.attr("id","table").addClass("sortable");
	$("#report").replaceWith(tbl);
	var tr = addTr(tbl).addClass("header");
	addTd(tr, "Num").addClass("num");
	addTd(tr, "Community").addClass("title");
	addTd(tr, "Collection").addClass("title");
	addTd(tr, "Num Items");

	$.getJSON(
		"/rest/collections",
		function(data){
			$.each(data, function(index, coll){
				var tr = addTr($("#table"));
				tr.attr("cid", coll.id).addClass(index % 2 == 0 ? "odd" : "even");
				addTd(tr, index).addClass("num");
				addTd(tr, "").addClass("title comm");
				addTdAnchor(tr, coll.name, "/handle/" + coll.handle).addClass("title");
				addTd(tr, coll.numberItems).addClass("num");
				$.getJSON(
					"/rest/collections/"+coll.id+"?expand=parentCommunityList",
					function(data) {
						tr.find("td.comm").append(data.parentCommunityList[0].name);
					}
				)
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

function addTdAnchor(tr, val, href) {
	var a = $("<a/>");
	a.append(val);
	a.attr("href", href);
	return addTd(tr, a);
}