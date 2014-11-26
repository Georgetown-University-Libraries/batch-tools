<?php
/*
User form for initiating the move of a collection to another community.  Note: in order to properly re-index the repository, 
DSpace will need to be taken offline after running this operation.
Author: Terry Brady, Georgetown University Libraries

License information is contained below.

Copyright (c) 2013, Georgetown University Libraries All rights reserved.

Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:

Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer. 
in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials 
provided with the distribution. THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, 
BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. 
IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES 
(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) 
HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) 
ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/
include '../header.php';
include 'queries.php';
include 'selfQueryCommon.php';

$CUSTOM = custom::instance();
$CUSTOM->getCommunityInit()->initCommunities();
$CUSTOM->getCommunityInit()->initCollections();

$MAX = 2000;

$coll  = util::getPostArg("coll","");
$comm  = util::getPostArg("comm","");
$op    = util::getPostArg("op","");
$field = util::getPostArg("field","");
$dfield = util::getPostArg("dfield",array());
$val    = util::getPostArg("val","");
$isCSV  = (util::getPostArg("query","") == "CSV Extract");
$offset = util::getPostArg("offset","0");

$mfields = initFields($CUSTOM);
$dsel = "<select id='dfield' name='dfield[]' multiple size='10'>";
$sel = "<select id='field' name='field'><option value='0'>All</option>";
foreach ($mfields as $mfi => $mfn) {
    $selected = sel($mfi, $field);
    $sel .= "<option value='{$mfi}' {$selected}>{$mfn}</option>";
    $selected = in_array($mfi, $dfield) ? "selected" : "";
    $dsel .= "<option value='{$mfi}' {$selected}>{$mfn}</option>";
}
$sel .= "</select>";
$dsel .= "</select>";

$status = "";
$hasPerm = $CUSTOM->isUserCollectionOwner();
if ($isCSV) {
    header("Content-type: text/csv");
    header("Content-Disposition: attachment; filename=export.csv"); 
} else {
    header('Content-type: text/html; charset=UTF-8');
}
   
?>
<!DOCTYPE html>
<html>
<body>
<?php
$header = new LitHeader("Query Construction");
$header->litPageHeader();
?>
<script type="text/javascript" src="spin.js"></script>
<script type="text/javascript">
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
    })

    $("#myform").submit(function(event){
        // Stop form from submitting normally
        event.preventDefault();

        prepSubmit();
        // Get some values from elements on the page:
        var form = $( this );
        var dfield = [];
        
        form.find("select[name='dfield[]'] option:selected").each(function(){
            dfield.push($(this).attr("value"));
        });
        
        // Send the data using post
        var posting = $.post("selfQueryData.php", 
            {  
                coll:   form.find("select[name=coll]").val(),
                comm:   form.find("select[name=comm]").val(),
                op:     form.find("select[name=op]").val(),
                field:  form.find("select[name=field]").val(),
                val:    form.find("input[name=val]").val(),
                query:  form.find("input.clicked[name=query]").val(),
                offset: form.find("input[name=offset]").val(),
                dfield : dfield,
            }
        ).done(function( data ) {
            $( "#export" ).empty().append( data );
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
        });
    });

    $(document).ajaxSend(function(){
        $("#myform select,#myform input").attr("disabled",true);
    });
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
    spinner.spin($("#myform")[0]);
}

</script>
<style type="text/css">
form {width: 1000px;}
button.edit {float: right;}
#spinner {display: inline;float: left; height: 200px; width: 45%; border: none;}
fieldset.fields {width: 40%; display:inline;float: left; margin: 20px;}
div.clear {clear: both;}
</style>
</head>
<body>
<?php $header->litHeaderAuth(array(), $hasPerm);?>
<div id="selfQuery">
<form id="myform" action="/">
<fieldset id="queryform">
<legend>Use this option to construct a quality control query </legend>
<button type="button" class="edit" name="edit" onclick="doedit();" disabled>Edit</button>
<div id="status"><?php echo $status?></div>
<?php collection::getCollectionIdWidget($coll, "coll", " to be queried*");?>
<?php collection::getSubcommunityIdWidget($comm, "comm", " to be queried*");?>
<p>
  <label for="field">Field to query</label>
  <?php echo $sel?>
  <label for="op">; Operator: </label>
  <select id="op" name="op" onchange="$('#val').val($(this).find('option:selected').attr('example'));">
    <option value="exists" example="" <?php echo sel($op,'exists')?>>Exists</value>
    <option value="not exists" example="" <?php echo sel($op,'not exists')?>>Doesn't exist</value>
    <option value="equals" example="val" <?php echo sel($op,'equals')?>>Equals</value>
    <option value="not equals" example="val" <?php echo sel($op,'not equals')?>>Not Equals</value>
    <option value="like" example="%val%" <?php echo sel($op,'like')?>>Like</value>
    <option value="not like" example="%val%" <?php echo sel($op,'not like')?>>Not Like</value>
    <option value="matches" example="^.*(val1|val2).*$" <?php echo sel($op,'matches')?>>Matches</value>
    <option value="doesn't match" example="^.*(val1|val2).*$" <?php echo sel($op,"doesn't match")?>>Doesn't Matches</value>
  </select>
  <label for="val">; Value: </label>
  <input name="val" id="val" type="text" value="<?php echo $val?>"/>
</p>
</fieldset>
<p>
  <fieldset class="fields">
  <legend>Fields to display</legend>
  <br/>
  <?php echo $dsel?>
  </fieldset>
  <div class="clear"/>
</p>
<p align="center">
    <input id="offset" name="offset" type="hidden" value="<?php echo $offset?>"/>
    <input id="MAX" name="MAX" type="hidden" value="<?php echo $MAX?>"/>
    <input id="querySubmitPrev" name="query" value="Prev Results" type="submit" disabled/>
    <input id="querySubmit" name="query" value="Show Results" type="submit"/>
	<input id="querySubmitNext" name="query" value="Next Results" type="submit" disabled/>
    <input id="queryCsv" name="query" value="CSV Extract" type="submit" disabled/>
</p>
<p><em>* Up to <?php echo $MAX?> results will be returned</em></p>
</form>
</div>
<div id='export'>
</div>
<?php $header->litFooter();?>
</body>
</html> 