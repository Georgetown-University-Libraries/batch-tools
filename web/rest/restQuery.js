var metadataSchemas;

/*
 * http://stackoverflow.com/questions/5448545/how-to-retrieve-get-parameters-from-javascript
 * getSearchParameters() and transformToAssocArray()
 */
function getSearchParameters() {
    var prmstr = window.location.search.substr(1);
    return transformToAssocArray(prmstr);
}

function transformToAssocArray( prmstr ) {
  if (prmstr == null) prmstr = "";
  var params = {
	"query_field[]" : [],
	"query_op[]"    : [],
	"query_val[]"   : [],
	"show_fields[]"   : [],
	"limit"         : 100,
	"offset"        : 0,		  
  };
  $("#this-search").attr("href",window.location.href);
  var prmarr = prmstr.split("&");
  for ( var i = 0; i < prmarr.length; i++) {
      var tmparr = prmarr[i].split("=");
      var field = tmparr[0];
      var val = tmparr[1];
      var pval = params[field];
      
      if ($.isArray(pval)) {
    	  pval[pval.length] = val;    	  
      } else {
    	  params[field] = val;
      }
  }
  return params;
}

$(document).ready(function(){
	createFilterTable();
	var params = getSearchParameters();
	loadMetadataFields(params);
	$("#item-button").click(function(){
		var encodedUri = encodeURI(itemdata);
        window.open(encodedUri);
    });
	$("#query-button").click(function(){runQuery();})
});

function loadMetadataFields(params) {
	$.getJSON(
		"/rest/metadataregistry",
		function(data){
			metadataSchemas = data;
			$("#limit").val(params.limit);  
		    $("#offset").val(params.offset);
		    var fields = params["query_field[]"];
		    var ops = params["query_op[]"];
		    var vals = params["query_val[]"];
		    if (fields.length == 0) {
				drawFilterQuery("","","");
		    } else {
		    	for(var i=0; i<fields.length; i++) {
		    		var op = ops.length > i ? ops[i] : "";
		    		var val = vals.length > i ? vals[i] : "";
					drawFilterQuery(fields[i],op,val);
		    	} 
		    }
			drawShowFields(params["show_fields[]"]);
		}
	);
}

function drawShowFields(pfields) {
	var sel = $("<select name='show_fields'/>").attr("multiple","true").attr("size","8").appendTo("#show-fields");
	$.each(metadataSchemas, function(index, schema){
		$.each(schema.fields, function(findex, field) {
			var name = field.name;
			var opt = $("<option/>");
			opt.attr("value",name).text(name);
			for(var i=0; i<pfields.length; i++) {
				if (pfields[i] == name) {
					opt.attr("selected", true);
				}
			}
			sel.append(opt);
		});
	});
}

function drawFilterQuery(pField, pOp, pVal) {
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
	sel.val(pField);
	div.append(sel);
	var opsel = $("<select class='query-tool' name='query_op[]'/>");
	$("<option>exists</option>").val("exists").appendTo(opsel);
	$("<option>does not exist</option>").val("doesnt_exist").appendTo(opsel);
	$("<option selected>equals</option>").val("equals").appendTo(opsel);
	$("<option>does not equals</option>").val("not_equals").appendTo(opsel);
	$("<option>like</option>").val("like").appendTo(opsel);
	$("<option>not like</option>").val("not_like").appendTo(opsel);
	$("<option>contains</option>").val("contains").appendTo(opsel);
	$("<option>does not contain</option>").val("doesnt_contain").appendTo(opsel);
	$("<option>matches</option>").val("matches").appendTo(opsel);
	$("<option>does not match</option>").val("doesnt_match").appendTo(opsel);
	opsel.val(pOp);
	opsel.change(function(){
		valField($(this));
	});
	div.append(opsel);
	var input = $("<input class='query-tool' name='query_val[]'/>");
	div.append(input);
	input.val(pVal);
	valField(opsel);
	$("<button class='field_plus'>+</button>").appendTo(div).click(function(){
		drawFilterQuery();
		queryButtons();
	});
	$("<button class='field_minus'>-</button>").appendTo(div).click(function(){
		$(this).parent("div.metadata").remove();
		queryButtons();
	});
	queryButtons();
}

function valField(valop) {
	var val = valop.val();
	var disableval = (val == "exists" || val == "not_exists");
	var valinput = valop.parent("div.metadata").find("input[name='query_val[]']");
	valinput.attr("readonly",disableval);
	if (disableval) {
		valinput.val("");		
	}
}

function queryButtons() {
	$("button.field_plus").attr("disabled",true);
	$("button.field_plus:last").attr("disabled",false);
	$("button.field_minus").attr("disabled",false);
	if ($("button.field_minus").length == 1) {
		$("button.field_minus").attr("disabled",true);				
	}
}

function runQuery() {
	var params = {
		"query_field[]" : [],
		"query_op[]"    : [],
		"query_val[]"   : [],
		"show_fields"   : $("#show-fields select").val(),
		"limit"         : $("#limit").val(),
		"offset"        : $("#offset").val(),
		"expand"        : "parentCollection,metadata",
		"filters[]"     :getFilterList(),
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
	var pstr = $.param(params).replace(/%5B%5D/g,"[]");
	$("#this-search").attr("href", window.location.pathname +"?" + pstr);
}

var mdCols = [];
var itemdata;

function getFilterList() {
	var list = "";
	$("input.filter:checked").each(function(){
		list += $(this).val() + ",";
	});
	list += "all_filters";
	return list;
}

function drawItemFilterTable(data) {
	itemdata = "data:text/csv;charset=utf-8,";
	itemdata += "id,collection,dc.title";
	var itbl = $("#itemtable");
	itbl.find("tr").remove("*");
	var tr = addTr(itbl).addClass("header");
	addTh(tr, "Num").addClass("num").addClass("sorttable_numeric");
	addTh(tr, "Collection");
	addTh(tr, "Item Handle");
	addTh(tr, "Title");
	
	mdCols = [];
	$.each(data.metadata, function(index, field) {
		addTh(tr,field.key).addClass("returnFields");
		itemdata += "," + field.key;
		mdCols[mdCols.length] = field.key;
	});
	
	$.each(data.items, function(index, item){
		var tr = addTr(itbl);
		tr.addClass(index % 2 == 0 ? "odd data" : "even data");
		addTd(tr, index+1).addClass("num");
		itemrow = item.id + "," + item.parentCollection.handle + ',"' + item.name + '"';
		addTdAnchor(tr, item.parentCollection.name, "/handle/" + item.parentCollection.handle);
		addTdAnchor(tr, item.handle, "/handle/" + item.handle);
		addTd(tr, item.name);
		
		for(var i=0; i<mdCols.length; i++) {
			var key =  mdCols[i];
			var td = addTd(tr, "");
			itemrow += ',';
			var itemcol = "";
			$.each(item.metadata, function(colindex, metadata) {
				if (metadata.key == key) {
					if (metadata.value != null) {
						var div = $("<div>"+metadata.value+"</div>");
						if (itemcol != "") itemcol += "||";
						itemcol += metadata.value;
						td.append(div);
					}
				}
			});
			if (itemcol != "") {
				itemrow += '"' + itemcol + '"';				
			}
		}
		itemdata += "\n" + itemrow;
		
	});

	$("#itemdiv").dialog({title: data["query-annotation"], width: "80%", minHeight: 500, modal: true});
}
