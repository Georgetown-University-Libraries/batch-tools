var stop = false;
var filterString = "";

$(document).ready(function(){
	var tbl = $("<table/>");
	tbl.attr("id","table").addClass("sortable");
	$("#report").replaceWith(tbl);

	var tr = addTr(tbl).addClass("header");
	addTh(tr, "Num").addClass("num");
	addTh(tr, "Community").addClass("title");
	addTh(tr, "Collection").addClass("title");
	addTh(tr, "Num Items");

	addFilter("","None","none").click(
		function(){
			$("input.filter,input.all").attr("checked",false);
			$("#filter-reload").attr("disabled", false);
		}
	);
	addFilter("all","All","all").click(
		function(){
			$("input.filter,input.none").attr("checked",false);
			$("#filter-reload").attr("disabled", false);
		}
	);
	$.getJSON(
		"/rest/filters",
		function(data){
			$.each(data, function(index, filter){
				addFilter(filter["filter-name"], filter["filter-name"], "filter").click(
					function(){
						$("input.none,input.all").attr("checked",false);
						$("#filter-reload").attr("disabled", false);
					}
				);
			});
			var button = $("<button id='filter-reload' disabled='true'>Reload</button>");
			button.click(
				function(){
					$("#filterdiv").dialog("hide");
					$("#filter-reload").attr("disabled", true);
					stop = true;
				}
			);
			$("#filterdiv").append(button);
			$("#filterdiv").dialog({title: "Choose filters to display", hide: {effect: "explode", duration: 1000}});
			$("#filterbutton").click(function(){
				$("#filterdiv").dialog("show");
			});
		}
	);

	$.getJSON(
		"/rest/collections",
		function(data){
			$.each(data, function(index, coll){
				var tr = addTr($("#table"));
				tr.attr("cid", coll.id).attr("index",index).addClass(index % 2 == 0 ? "odd data" : "even data");
				addTd(tr, index).addClass("num");
				addTd(tr, "").addClass("title comm");
				addTdAnchor(tr, coll.name, "/handle/" + coll.handle).addClass("title");
				addTdAnchor(tr, coll.numberItems, "javascript:drawItemTable("+coll.id+",'')").addClass("num");
			});
			loadData();
		}
	);

});

function addFilter(val, name, cname) {
	var div = $("<div/>");
	var input = $("<input name='filters[]' type='checkbox'/>");
	input.attr("id",val);
	input.val(val);
	input.addClass(cname);
	div.append(input);
	var label = $("<label>"+name+"</label>");
	div.append(label);
	$("#filterdiv").append(div);
	return input;
}

function loadData() {
	$("td.datacol,th.datacol").remove();
	filterString = getFilterList();
	doRow(0, 5);
}

function getFilterList() {
	var list="";
	$("input:checked[name='filters[]']").each(
		function(){
			if (list != "") {
				list += ",";
			}
			list += $(this).val();
		}
	);
	return list;
}

function doRow(row, threads) {
	var tr = $("tr[index="+row+"]");
	if (!tr.is("*")) return; 
	var cid = tr.attr("cid");
	$.getJSON(
		"/rest/collections/"+cid+"?expand=parentCommunityList,filters&filters=" + filterString,
		function(data) {
			var par = data.parentCommunityList[data.parentCommunityList.length-1];
			tr.find("td.comm:empty").append(getAnchor(par.name, "/handle/" + par.handle));

			$.each(data.itemFilters, function(index, itemFilter){
				var trh = $("tr.header");
				var filterName = itemFilter["filter-name"];
				var icount = itemFilter.items.length;
				if (!trh.find("th."+filterName).is("*")) {
					var th = addTh(trh, filterName.replace(/_/g," "));
					th.addClass(filterName).addClass("datacol");;

					$("tr.data").each(function(){
						var td = addTd($(this), "");
						td.addClass(filterName).addClass("num").addClass("datacol");
					});
				}
				tr.find("td."+filterName).append(getAnchor(icount,"javascript:drawItemTable("+cid+",'"+ filterName +"')"));
			});

			if (row % threads != 0) return;
			if (stop == true) {
				loadData();
				return;
			}
			
			for(var i=1; i<=threads; i++) {
				doRow(row+i, threads);
			}
		}
	);
}			
			
function drawItemTable(cid, filter, collname) {
	var itbl = $("#itemtable");
	itbl.find("tr").remove("*");
	var tr = addTr(itbl).addClass("header");
	addTd(tr, "Num").addClass("num");
	addTd(tr, "Handle");
	addTd(tr, "Item").addClass("title");
	$.getJSON(
		"/rest/collections/"+cid+"?expand=items,filters&limit=5000&filters="+filter,
		function(data){
			var source = filter == "" ? data.items : data.itemFilters[0].items;
			
			$.each(source, function(index, item){
				var tr = addTr(itbl);
				tr.addClass(index % 2 == 0 ? "odd data" : "even data");
				addTd(tr, index+1).addClass("num");
				addTdAnchor(tr, item.handle, "/handle/" + item.handle);
				addTd(tr, item.name).addClass("ititle");
			});
			$("#itemdiv").dialog({title: filter + " Items in " + data.name, width: "80%", minHeight: 500, modal: true});
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

function addTh(tr, val) {
	var th = $("<th/>");
	if (val != null) {
		th.append(val);
	}
	tr.append(th);
	return th;
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