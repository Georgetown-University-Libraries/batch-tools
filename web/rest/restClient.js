$(document).ready(function(){
	$("#report").append($("<div>Hello!</div>"));
	$.getJSON(
		"/rest/collections",
		function(data){
			$.each(data, function(index, coll){
				$("#report").append($("<div>"+index+". "+coll.name+"</div>"));
			});
		}
	);
});