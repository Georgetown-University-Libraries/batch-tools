$(document).ready(function(){
	$("#report").append($("<div>Hello!</div>"));
	$.getJSON(
		"/rest/collections",
		function(data){
			$.each(data, function(coll){
				$("#report").append($("<div>"+coll.name+"</div>"));
			});
		}
	);
});