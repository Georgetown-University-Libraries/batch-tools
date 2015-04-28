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
		}
	);
}

function drawFilterQuery() {
	var div = $("<div class='metadata'/>").appendTo("#metadatadiv");
	var sel = $("<select name='query_field[]'/>");
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
	sel = $("<select name='query_op[]'/>");
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
	var input = $("<input name='query_val[]'/>");
	div.append(input);
	$("<button class='field_plus'>+</button>").appendTo(div).click(function(){
		drawFilterQuery(metadataSchemas);
		queryButtons();
	});
	$("<button class='field_minus'>-</button>").appendTo(div).click(function(){
		$(this).parent("div.metadata").remove();
		queryButtons();
	});
}

function queryButtons() {
	$("button.field_plus").attr("disabled",true);
	$("button.field_plus:last").attr("disabled",false);
	$("button.field_minus").attr("disabled",false);
	$("div.metadata:first button.field_minus").attr("disabled",true);	
}
