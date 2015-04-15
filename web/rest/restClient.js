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
						var par = parentCommunityList[data.parentCommunityList.length-1];
						tr.find("td.comm").append(getAnchor(par.name, par.handle));
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
	return addTd(tr, getAnchor(val, href));
}

function getAnchor(val, href) {
	var a = $("<a/>");
	a.append(val);
	a.attr("href", href);
	return a;
}