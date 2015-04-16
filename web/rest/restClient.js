$(document).ready(function(){
	var tbl = $("<table/>");
	tbl.attr("id","table").addClass("sortable");
	$("#report").replaceWith(tbl);

	var tr = addTr(tbl).addClass("header");
	addTd(tr, "Num").addClass("num");
	addTd(tr, "Community").addClass("title");
	addTd(tr, "Collection").addClass("title");
	addTd(tr, "Num Items");

	$("#itemdiv").dialog({title: "Items", width: "400px", modal: true, hide: true});

	$.getJSON(
		"/rest/collections",
		function(data){
			$.each(data, function(index, coll){
				var tr = addTr($("#table"));
				tr.attr("cid", coll.id).attr("index",index).addClass(index % 2 == 0 ? "odd" : "even");
				addTd(tr, index).addClass("num");
				addTd(tr, "").addClass("title comm");
				addTdAnchor(tr, coll.name, "/handle/" + coll.handle).addClass("title");
				addTdAnchor(tr, coll.numberItems, "javascript:drawItemTable("+coll.id+")").addClass("num");
			});
			doRow(0, 5);
		}
	);

});

function drawItemTable(cid) {
	var itbl = $("#itemtable");
	itbl.find("tr").remove("*");
	var tr = addTr(itbl).addClass("header");
	addTd(tr, "Num").addClass("num");
	addTd(tr, "Item").addClass("title");
	$.getJSON(
		"/rest/collections/"+cid+"?expand=items&limit=5000",
		function(data){
			$.each(data.items, function(index, item){
				var tr = addTr(itbl);
				tr.addClass(index % 2 == 0 ? "odd" : "even");
				addTd(tr, index).addClass("num");
				addTdAnchor(tr, item.name, "/handle/" + item.handle).addClass("title");
			});
			$("#itemdiv").show();
		}
	);
}

function doRow(row, threads) {
	var tr = $("tr[index="+row+"]");
	if (!tr.is("*")) return; 
	var cid = tr.attr("cid");
	$.getJSON(
		"/rest/collections/"+cid+"?expand=parentCommunityList",
		function(data) {
			var par = data.parentCommunityList[data.parentCommunityList.length-1];
			tr.find("td.comm").append(getAnchor(par.name, "/handle/" + par.handle));
			if (row % threads != 0) return;
			for(var i=1; i<=threads; i++) {
				doRow(row+i, threads);
			}
		}
	);
}			
			
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
	a.attr("target", "_blank");
	return a;
}