var filterString = "";
var loadId = 0;

$(document).ready(function(){
	var tbl = $("<table/>");
	tbl.attr("id","table").addClass("sortable");
	$("#report").replaceWith(tbl);

	var tr = addTr(tbl).addClass("header");
	addTh(tr, "Num").addClass("num").addClass("sorttable_numeric");
	addTh(tr, "Community").addClass("title");
	addTh(tr, "Collection").addClass("title");
	addTh(tr, "Num Items").addClass("sorttable_numeric");

	addFilter("","None","De-select all filters","none").click(
		function(){
			$("input.filter,input.all").attr("checked",false);
			$("#filter-reload").attr("disabled", false);
		}
	);
	addFilter("all","All","Show all filters","all").click(
		function(){
			$("input.filter,input.none").attr("checked",false);
			$("#filter-reload").attr("disabled", false);
		}
	);
	$.getJSON(
		"/rest/filters",
		function(data){
			$.each(data, function(index, filter){
				addFilter(filter["filter-name"], filter.title, filter.description, "filter").click(
					function(){
						$("input.none,input.all").attr("checked",false);
						$("#filter-reload").attr("disabled", false);
					}
				);
			});
			var button = $("<button id='filter-reload' disabled='true'>Reload</button>");
			button.click(
				function(){
					$("#filterdiv").dialog("close");
					$("#filter-reload").attr("disabled", true);
					loadData();
				}
			);
			$("#filterdiv").append(button);
			$("#filterdiv").dialog({title: "Choose filters to display", hide: true, width : 450});
			$("#filterbutton").click(function(){
				$("#filterdiv").dialog("open");
			});
		}
	);

	$.getJSON(
		"/rest/collections?limit=500",
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

function addFilter(val, title, description, cname) {
	var div = $("<div/>");
	var input = $("<input name='filters[]' type='checkbox'/>");
	input.attr("id",val);
	input.val(val);
	input.addClass(cname);
	div.append(input);
	var ftitle = (title == null) ? val : title;
	var label = $("<label>" + ftitle + "</label>");
	label.attr("title", description);
	div.append(label);
	$("#filterdiv").append(div);
	return input;
}

function loadData() {
	loadId++;
	$("td.datacol,th.datacol").remove();
	filterString = getFilterList();
	doRow(0, 1, loadId);
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

function doRow(row, threads, curLoadId) {
	if (loadId != curLoadId) return;
	var tr = $("tr[index="+row+"]");
	if (!tr.is("*")) return; 
	var cid = tr.attr("cid");
	$.getJSON(
		"/rest/filtered-collections/"+cid+"?expand=parentCommunityList&filters=" + filterString,
		function(data) {
			var par = data.parentCommunityList[data.parentCommunityList.length-1];
			tr.find("td.comm:empty").append(getAnchor(par.name, "/handle/" + par.handle));

			$.each(data.itemFilters, function(index, itemFilter){
				if (loadId != curLoadId) {
					return;
				}
				var trh = $("tr.header");
				var filterName = itemFilter["filter-name"];
				var filterTitle = itemFilter.title == null ? filterName : itemFilter.title;
				var icount = itemFilter["item-count"];
				if (!trh.find("th."+filterName).is("*")) {
					var th = addTh(trh, filterTitle);
					th.addClass(filterName).addClass("datacol").addClass("sorttable_numeric");
					
					if (itemFilter.description != null) {
						th.attr("title", itemFilter.description);											
					}

					$("tr.data").each(function(){
						var td = addTd($(this), "");
						td.addClass(filterName).addClass("num").addClass("datacol");
					});
				}
				
				if (icount == null || icount == "0") {
					tr.find("td."+filterName).text("0");
				} else {
					var anchor = getAnchor(icount,"javascript:drawItemTable("+cid+",'"+ filterName +"')");
					tr.find("td."+filterName).append(anchor);						
				}
				
				
			});
			
			sorttable.makeSortable($("#table")[0]);
			if (row % threads == 0 || threads == 1) {
				for(var i=1; i<=threads; i++) {
					doRow(row+i, threads, curLoadId);
				}					
			}
 		}
	);
}			
			
function drawItemTable(cid, filter, collname) {
	var itbl = $("#itemtable");
	itbl.find("tr").remove("*");
	var tr = addTr(itbl).addClass("header");
	addTh(tr, "Num").addClass("num").addClass("sorttable_numeric");
	addTh(tr, "Handle");
	addTh(tr, "Item").addClass("title");
	$.getJSON(
		"/rest/filtered-collections/"+cid+"?expand=items&filters="+filter,
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