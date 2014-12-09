var spinner;
$(document).ready(function(){
	var opts = {
	    lines: 13, // The number of lines to draw
	    length: 20, // The length of each line
	    width: 10, // The line thickness
	    radius: 30, // The radius of the inner circle
	    corners: 1, // Corner roundness (0..1)
	    rotate: 0, // The rotation offset
	    direction: 1, // 1: clockwise, -1: counterclockwise
	    color: '#000', // #rgb or #rrggbb or array of colors
	    speed: 1, // Rounds per second
	    trail: 60, // Afterglow percentage
	    shadow: false, // Whether to render a shadow
	    hwaccel: false, // Whether to use hardware acceleration
	    className: 'spinner', // The CSS class to assign to the spinner
	    zIndex: 2e9, // The z-index (defaults to 2000000000)
	    top:  '400px', // Top position relative to parent
	    left: '600px' // Left position relative to parent
	};
	spinner = new Spinner(opts);

    $("input[name=query]").click(function(){
        $("input[name=query]").removeClass("clicked");
        $(this).addClass("clicked");
    });
    
    updateQueryFields();

    $("#myform").submit(function(event){
        
        // Get some values from elements on the page:
        var form = $( this );
        var query = form.find("input.clicked[name=query]").val();
        prepSubmit();
        
        if (query == "CSV Extract") return true;
        if (query == "Permalink") {
        	$("#myform").attr("method", "get");        	
        	$("#myform").attr("action", "selfQuery.php");
        	return true;
        }
        spinner.spin($("#myform")[0]);
        
        // Stop form from submitting normally
        event.preventDefault();

        var dfield = [];
        var dfilter = [];
        var field = [];
        var op = [];
        var val = [];
        
        form.find("select[name='dfield[]'] option:selected").each(function(){
            dfield.push($(this).attr("value"));
        });
        form.find("input[name='filter[]']:checked").each(function(){
            dfilter.push($(this).attr("value"));
        });
        form.find("select[name='field[]'] option:selected").each(function(){
            field.push($(this).attr("value"));
        });
        form.find("select[name='op[]'] option:selected").each(function(){
            op.push($(this).attr("value"));
        });
        form.find("input[name='val[]']").each(function(){
            val.push($(this).attr("value"));
        });
        
        // Send the data using post
        var posting = $.post("selfQueryData.php", 
            {  
                coll:   form.find("select[name=coll]").val(),
                comm:   form.find("select[name=comm]").val(),
                op:     op,
                field:  field,
                val:    val,
                query:  query,
                offset: form.find("input[name=offset]").val(),
                dfield : dfield,
                filter : dfilter,
}
        ).done(function( data ) {
            $( "#exporthold" ).empty().append( data );
            $("#myform select,#myform input").attr("disabled",false);

	        var rescount = parseInt($("#rescount").val());
	        var offset = parseInt($("#cstart").val());
	        var MAX = parseInt($("#MAX").val());
	        
            if (offset == 1) $("#querySubmitPrev").attr("disabled", true); 
            if (rescount < MAX) $("#querySubmitNext").attr("disabled", true); 
            if (parseInt($("#rescount").val()) > 0) {
                $("#queryform input,#queryform select").attr("disabled", true);
                $("button.edit").attr("disabled", false);
            } else {
                $("button.edit").attr("disabled", true);
                $("#queryCsv").attr("disabled", true);
            }
            spinner.stop();
            var sortt = $("#export table.sortable");
            if (sortt.is("*")) {
                sorttable.makeSortable(sortt.get(0));
            	
            }
        });
        
    });

    $(document).ajaxSend(function(){
        $("#myform select,#myform input").attr("disabled",true);
    });
    if (document.location.search != "") $("#myform").submit();
});

function doedit() {
    $('#queryform input,#queryform select').attr('disabled',false);
    $("#offset").val(0);
    $("#queryCsv,#querySubmitPrev,#querySubmitNext,button.edit").attr("disabled", true);
}

function prepSubmit() {
	var query = $("#myform input.clicked[name=query]").val();
	var offset = parseInt($("#offset").val());
	var MAX = parseInt($("#MAX").val());
	if (query == "Prev Results") {
	    offset -= MAX;
	    if (offset < 0) offset = 0;
	} else if (query == "Next Results") {
	    offset += MAX;    
	}
	$("#offset").val(offset);
    $('#queryform input,#queryform select').attr('disabled',false);
}

function changeOperator(thisnode, setval) {
	var example = thisnode.find('option:selected').attr('example');
	var ival = thisnode.siblings('input.qfield');
	if (setval) ival.val(example);
	if (example == "") {
		ival.hide();
		ival.siblings("label[for=val]").hide();
	} else {
		ival.show();
		ival.siblings("label[for=val]").show();
	}
}

function changeField(thisnode) {
	var iop = thisnode.siblings("select['name=op[]']");
	var ival = thisnode.siblings('input.qfield');
	if (thisnode.val() == "") {
		iop.hide();
		iop.siblings("label[for=op]").hide();
		ival.hide();
		ival.siblings("label[for=val]").hide();
	} else {
		iop.show();
		iop.siblings("label[for=op]").show();
		ival.show();
		ival.siblings("label[for=val]").show();
		changeOperator(iop, false);
	}
}

function updateQueryFields() {
	$("select[name='field[]']").each(function(){changeField($(this))});
}

function copyQuery(thisnode) {
	var line=thisnode.parent('p.queryline').clone();
	$('#querylines').append(line);
	changeField(line.find("select[name='field[]']"));
}

function showUsage() {
	$("#usage").dialog({height: 500, width: 400, title: "Fields Used"});
}