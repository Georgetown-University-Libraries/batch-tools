var filterString = "none";

function createFilterTable() {
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
			$("#filterdiv").dialog({title: "Choose filters to display", width : 450});
			$("#filterdiv").dialog("close");
			$("#filterbutton").click(function(){
				$("#filterdiv").dialog("open");
			});
		}
	);
}

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
	if (list == "") {
		list = "none";
	}
	return list;
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