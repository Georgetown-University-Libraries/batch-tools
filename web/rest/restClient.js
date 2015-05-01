var loadId = 0;
var THREADS = 4;
var THREADSP = 10;
var COUNT_LIMIT = 1000;
var ITEM_LIMIT = 1000;
var COLL_LIMIT = 500;

$(document).ready(function(){
	createCollectionTable();
	createFilterTable();
});

function createCollectionTable() {
	var tbl = $("<table/>");
	tbl.attr("id","table").addClass("sortable");
	$("#report").replaceWith(tbl);

	var tr = addTr(tbl).addClass("header");
	addTh(tr, "Num").addClass("num").addClass("sorttable_numeric");
	addTh(tr, "Community").addClass("title");
	addTh(tr, "Collection").addClass("title");
	addTh(tr, "Num Items").addClass("sorttable_numeric");


	$.getJSON(
		"/rest/collections",
		{
			limit : COLL_LIMIT,
		},
		function(data){
			$.each(data, function(index, coll){
				var tr = addTr($("#table"));
				tr.attr("cid", coll.id).attr("index",index).addClass(index % 2 == 0 ? "odd data" : "even data");
				addTd(tr, index).addClass("num");
				addTd(tr, "").addClass("title comm");
				addTdAnchor(tr, coll.name, "/handle/" + coll.handle).addClass("title");
				addTdAnchor(tr, coll.numberItems, "javascript:drawItemTable("+coll.id+",'')").addClass("num");
			});
			loadParent();
		}
	);	
}

function loadData() {
	loadId++;
	$("td.datacol,th.datacol").remove();
	filterString = getFilterList();
	doRow(0, THREADS, loadId);
}

function loadParent() {
	doRowParent(0, THREADSP);
}

function doRowParent(row, threads) {
	var tr = $("tr[index="+row+"]");
	if (!tr.is("*")) return; 
	var cid = tr.attr("cid");
	$.getJSON(
		"/rest/collections/"+cid,
		{
			expand : "parentCommunityList",
		},
		function(data) {
			var par = data.parentCommunityList[data.parentCommunityList.length-1];
			tr.find("td.comm:empty").append(getAnchor(par.name, "/handle/" + par.handle));
			
			sorttable.makeSortable($("#table")[0]);
			if (row % threads == 0 || threads == 1) {
				for(var i=1; i<=threads; i++) {
					doRowParent(row+i, threads);
				}					
			}
 		}
	);
}			

function doRow(row, threads, curLoadId) {
	if (loadId != curLoadId) return;
	var tr = $("tr[index="+row+"]");
	if (!tr.is("*")) return; 
	var cid = tr.attr("cid");
	$.getJSON(
		"/rest/filtered-collections/"+cid,
		{
			limit : COUNT_LIMIT,
			filters : filterString,
		},
		function(data) {
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
					var disp = (icount == COUNT_LIMIT) ? icount + "+" : icount;
					var anchor = getAnchor(disp,"javascript:drawItemTable("+cid+",'"+ filterName +"')");
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
		"/rest/filtered-collections/"+cid,
		{
			expand: "items",
			limit: ITEM_LIMIT,
			filters: filter,
		},
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

