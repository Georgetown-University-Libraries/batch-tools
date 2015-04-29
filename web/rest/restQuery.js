var metadataSchemas;

$(document).ready(function(){
	createFilterTable();
	createQueryTable();
});

function loadMetadataFields() {
	$.getJSON(
		"/rest/metadataregistry",
		function(data){
			metadataSchemas = data;
			drawFilterQuery();
			drawShowFields();
		}
	);
}

function drawShowFields() {
	var sel = $("<select class='query-tool' name='show_fields[]'/>").attr("multiple","true").attr("size","8").appendTo("#show-fields");
	$.each(metadataSchemas, function(index, schema){
		$.each(schema.fields, function(findex, field) {
			var name = field.name;
			var opt = $("<option/>");
			opt.attr("value",name).text(name);
			sel.append(opt);
		});
	});
}

function drawFilterQuery() {
	var div = $("<div class='metadata'/>").appendTo("#queries");
	var sel = $("<select class='query-tool' name='query_field[]'/>");
	var opt = $("<option/>");
	sel.append(opt);
	$.each(metadataSchemas, function(index, schema){
		$.each(schema.fields, function(findex, field) {
			var name = field.name;
			var opt = $("<option/>");
			opt.attr("value",name).text(name);
			sel.append(opt);
		});
	});
	div.append(sel);
	sel = $("<select class='query-tool' name='query_op[]'/>");
	$("<option>exists</option>").val("exists").appendTo(sel);
	$("<option>does not exist</option>").val("not_exists").appendTo(sel);
	$("<option selected>equals</option>").val("equals").appendTo(sel);
	$("<option>does not equals</option>").val("not_equals").appendTo(sel);
	$("<option>like</option>").val("like").appendTo(sel);
	$("<option>not like</option>").val("not_like").appendTo(sel);
	$("<option>matches</option>").val("like").appendTo(sel);
	$("<option>does not mat</option>").val("not_like").appendTo(sel);
	sel.change(function(){
		var val = $(this).val();
		var disableval = (val == "exists" || val == "not_exists");
		$(this).parent("div.metadata").find("input[name='query_val[]']").val("").attr("readonly",disableval);
	});
	div.append(sel);
	var input = $("<input class='query-tool' name='query_val[]'/>");
	div.append(input);
	$("<button class='field_plus'>+</button>").appendTo(div).click(function(){
		drawFilterQuery(metadataSchemas);
		queryButtons();
	});
	$("<button class='field_minus'>-</button>").appendTo(div).click(function(){
		$(this).parent("div.metadata").remove();
		queryButtons();
	});
	$("#query-button").click(function(){runQuery();})
}

function queryButtons() {
	$("button.field_plus").attr("disabled",true);
	$("button.field_plus:last").attr("disabled",false);
	$("button.field_minus").attr("disabled",false);
	$("div.metadata:first button.field_minus").attr("disabled",true);		
}

function runQuery() {
	var params = {
		"query_field[]" : [],
		"query_op[]"    : [],
		"query_val[]"   : [],
		"show_fields[]" : [],
		"limit"         : 100,
		"offset"        : 0,
		"expand"        : "parentCollection,metadata"
	};
	$("select.query-tool,input.query-tool").each(function() {
		var paramArr = params[$(this).attr("name")];
		paramArr[paramArr.length] = $(this).val();
	});
	params.limit = $("#limit").val();
	params.offset = $("#offset").val();
	$.getJSON("/rest/filtered-items", params, function(data){
		drawItemFilterTable(data);
	});
}

function drawItemFilterTable(data) {
	var itbl = $("#itemtable");
	itbl.find("tr").remove("*");
	var tr = addTr(itbl).addClass("header");
	addTh(tr, "Num").addClass("num").addClass("sorttable_numeric");
	addTh(tr, "Collection").addClass("title");
	addTh(tr, "Item").addClass("title");

	$.each(data.items, function(index, item){
		var tr = addTr(itbl);
		tr.addClass(index % 2 == 0 ? "odd data" : "even data");
		addTd(tr, index+1).addClass("num");
		addTdAnchor(tr, parentCollection.handle, "/handle/" + item.handle);
		addTd(tr, parentCollection.name).addClass("ititle");
		addTdAnchor(tr, item.handle, "/handle/" + item.handle);
		addTd(tr, item.name).addClass("ititle");
	});

	$("#itemdiv").dialog({title: filter + " Items in " + data.name, width: "80%", minHeight: 500, modal: true});
}