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
	var sel = $("<select class='query-tool' name='show_fields'/>").attr("multiple","true").attr("size","8").appendTo("#show-fields");
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
	$("<option>contains</option>").val("contains").appendTo(sel);
	$("<option>does not contain</option>").val("doesnt_contain").appendTo(sel);
	$("<option>matches</option>").val("matches").appendTo(sel);
	$("<option>does not match</option>").val("doesnt_match").appendTo(sel);
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
		"show_fields"   : "",
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

var mdCols = [];

function drawItemFilterTable(data) {
	var itbl = $("#itemtable");
	itbl.find("tr").remove("*");
	var tr = addTr(itbl).addClass("header");
	addTh(tr, "Num").addClass("num").addClass("sorttable_numeric");
	addTh(tr, "Collection");
	addTh(tr, "Item Handle");
	addTh(tr, "Title");
	
	mdCols = [];
	$.each(data.metadata, function(index, field)) {
		addTh(tr,field.key).addClass("returnFields");
		mdCols[mdCols.length] = field.key;
	}

	$.each(data.items, function(index, item){
		var tr = addTr(itbl);
		tr.addClass(index % 2 == 0 ? "odd data" : "even data");
		addTd(tr, index+1).addClass("num");
		addTdAnchor(tr, item.parentCollection.name, "/handle/" + item.parentCollection.handle).addClass("ititle");
		addTdAnchor(tr, item.handle, "/handle/" + item.handle);
		addTd(tr, item.name).addClass("ititle");
		
		for(var i=0; i<mdCols.length; i++) {
			var key =  mdCols[i];
			var td = addTd(tr, "-");
			$.each(data.metadata, function(index, metadata) {
				if (metadata.key == key) {
					if (metadata.value != null) {
						td.append(metadata.value);
					}
				}
			});
		}
		
	});

	$("#itemdiv").dialog({title: data["query-annotation"], width: "80%", minHeight: 500, modal: true});
}
