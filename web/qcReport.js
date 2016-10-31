/*
Dynamic controls for DSpace tools.

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


  $(document).ready(function(){
	$("button.checkbutton").click(function(){
		var label = $(this).attr("label");
		var name = $(this).attr("name");
		if ($(this).hasClass("checkoff")) {
			$(document).find("input.qccol."+label).removeAttr("checked");
			$(this).removeClass("checkoff").addClass("checkon").text("Check "+name);
		} else {
			$(document).find("input.qccol."+label).attr("checked","Y");
			$(this).removeClass("checkon").addClass("checkoff").text("Uncheck "+name);			
		}
		
	});  
	  
  	$("#communityToolbar,#warnToolbar,#typeToolbar").change(function(){
  		filterRows();
  	});

  	$("#viewToolbar,#colToolbar").change(function(){
  		filterCols();
  	});

  	if ($("#commSelect").length > 0){
  	  	$("#commSelect").change(function(){
  	  		commSelect();
  	  		$("#collSelect").val("allcoll");	  
  	  	});
  	  	commSelect();
  	}
  	
  	if ($("#formFilter").length > 0) mediaFilterForm();
  	if ($("#formIngest").length > 0) bulkIngestForm();
  	if ($("#formIngestZip").length > 0) bulkIngestZipForm();
  	if ($("#formUningest").length > 0) bulkUningestForm();
  	if ($("#formMetadata").length > 0) metadataForm();
  	if ($("#formChangeParent").length > 0) changeParentForm();
  	if ($("#formReindex").length > 0) reindexForm();
  	
  	if ($("#showopt").length > 0) {
  		$('#showopt').dialog({
  			modal: true, 
  			closeText: "Done", 
  			autoOpen: false,
  			title : "Item Display Options",
  			close : function() {$("#soc").removeAttr("checked");},
  			height: 500,
  			resizable: true
  		});
  	}
  	if ($("#tools").length > 0) {
  		$('#tools').dialog({
  			modal: true, 
  			closeText: "Done", 
  			autoOpen: false,
  			title : "Item Display Tools",
  			close : function() {$("#tools").removeAttr("checked");},
  			height: 580,
  			resizable: true
  		});
  	}
  	
  	if ($("#ALLCOLLS").length > 0) {
  		$("#ALLCOLLS").click(function() {
  			if ($("#ALLCOLLS:checked").length > 0) {
  				$.cookie("ALLCOLLS", "Y");
  			} else {
  				$.removeCookie("ALLCOLLS");
  			}
  		});
  		if ($.cookie("ALLCOLLS") == "Y") {
  			$("#ALLCOLLS").attr("checked", "Y");
  		} else {
  			$("#ALLCOLLS").removeAttr("checked");
  		}
  	}
  	
  	$("fieldset.queryCols").show();
  	
  	bitstreamPrep();
  	$("#showopt").show();
  	
  });	
  
  function total() {
	  var rows = $("#ins tr.allrow:visible");
	  $("#ins table.sortable tr.header span.total").each(function(col){
		  if (col > 1) {
			  var sum = 0;
			  var err = false;
			  rows.each(function(){
				  var td = $(this).find("td")[col];
				  var v = $(td).text();
				  v = parseInt(v);
				  sum += v;
				  if (v > 0) {
					  if ($(td).hasClass("error")) err = true;					  
				  }
			  });
			  if (!isNaN(sum)) {
				  $(this).text(sum);
			  }
			  if (err) {
				  $(this).addClass("toterror");
			  }
		  }
	  });
	  
  }
  
  
  function commSelect() {
		var v = "option." + $("#commSelect").val();
		
		$("#collSelect option").remove();
		$("#collSelectHold option").filter("option.top,"+v).each(function(){
			$(this).clone().appendTo("#collSelect");
		});
  }
  
  function mediaFilterForm() {
	  	var v = function() {
	  		var b = ($("#collSelect").val() != "") && ($("#formFilter input[name^='action']:checked").length > 0);
	  		if (b){
	  	  		$("#filterSubmit").removeAttr("disabled");
	  		} else {
	  	  		$("#filterSubmit").attr("disabled", true);  			
	  		}
	  	};
	  	$("#formFilter #collSelect").change(v);
	  	$("#formFilter input[name^='action']").click(v);
	  	v();
  }
  
  function changeParentForm() {
	  	var v = function() {
	  		var b = ($("#subcommCollSelect").val() != "") && ($("#subcommSelect").val() != "");
	  		if (b){
	  	  		$("#changeParentSubmit").removeAttr("disabled");
	  		} else {
	  	  		$("#changeParentSubmit").attr("disabled", true);  			
	  		}
	  	};
	  	$("#formChangeParent #subcommCollSelect").change(v);
	  	$("#formChangeParent #subcommSelect").change(v);
	  	v();
  }

  function reindexForm() {
	  	var v = function() {
	  		var b = ($("#subcommCollSelect").val() != "") || ($("#subcommSelect").val() != "");
	  		if (b){
	  	  		$("#reindexSubmit").removeAttr("disabled");
	  		} else {
	  	  		$("#reindexSubmit").attr("disabled", true);  			
	  		}
	  	};
	  	$("#formReindex #subcommCollSelect").change(v);
	  	v();
  }

  function bulkIngestForm() {
	  	var v = function() {
	  		var b = ($("#collSelect").val() != "") && ($("#loc").val() != 0);
	  		if (b){
	  	  		$("#ingestSubmit").removeAttr("disabled");
	  		} else {
	  	  		$("#ingestSubmit").attr("disabled", true);  			
	  		}
	  	};
	  	$("#formIngest #collSelect").change(v);
	  	$("#loc").blur(v);
	  	$("#loc").keyup(v);
  }
  
  function bulkIngestZipForm() {
	  	var v = function() {
	  		var b = ($("#collSelect").val() != "") && ($("#zip").val() != "");
	  		if (b){
	  	  		$("#ingestSubmit").removeAttr("disabled");
	  		} else {
	  	  		$("#ingestSubmit").attr("disabled", true);  			
	  		}
	  	};
	  	$("#formIngestZip #collSelect,#formIngestZip #zip").change(v);
  }

  function bulkUningestForm() {
	  	var v = function() {
	  		var b = ($("#mapfile").val() != "");
	  		if (b){
	  	  		$("#ingestSubmit").removeAttr("disabled");
	  		} else {
	  	  		$("#ingestSubmit").attr("disabled", true);  			
	  		}
	  		$("#maptext").load("../web/getmapfile.php?name="+$("#mapfile").val());
	  	};
	  	$("#mapfile").change(v);
  }
  
  function metadataForm() {
	  	var v = function() {
	  		var b = ($("#metadata").val() != "");
	  		if (b){
	  	  		$("#submit").removeAttr("disabled");
	  		} else {
	  	  		$("#submit").attr("disabled", true);  			
	  		}
	  	};
	  	$("#metadata").change(v).val("");
	  	$("#preview").attr("checked",true).change(
	  		function(){
	  			if (!$("#preview").is("input:checked")) {
	  				if (!confirm("Warning: it is highly advisable that you preview changes careful before allowing the update to run.\n\n" +
	  						"Are you sure you wish to proceed?")){
	  					$("#preview").attr("checked", true);
	  				}
	  			}
	  		}
	  	);
  }

  function filterRows() {
		$("tr.allrow").hide();
  		var crows = ($("#communityToolbar").length > 0) ? $("tr." + $("#communityToolbar").val()) : $("tr.allrow");

  		var t =  $("#typeToolbar").val();
        if (t == "docs") {
        	crows = crows.filter(function() {
        		var v = $(this).find("td.itemCountDoc").text();
        		return v != "0";
        	});
        } else if (t == "images") {
        	crows = crows.filter(function() {
        		var v = $(this).find("td.itemCountImage").text();
        		return v != "0";
        	});
        } else if (t == "video") {
        	crows = crows.filter(function() {
        		var v = $(this).find("td.itemCountVideo").text();
        		return v != "0";
        	});
        } else if (t == "other") {
        	crows = crows.filter(function() {
        		var v = $(this).find("td.itemCountOther").text();
        		return v != "0";
        	});
        } else if (t == "empty") {
        	crows = crows.filter(function() {
        		var v = $(this).find("td.itemCount").text();
        		return v == "0";
        	});
        }
        

  		var v =  $("#warnToolbar").val();
  		if (v == 'warn') {
  		  crows.show();
  		  var rows = $("td:visible.error").parent("tr");
  		  crows.hide();
  		  rows.show();
  		} else if (v == 'no-warn') {
  		  crows.show();
  		  var rows = $("td:visible.error").parent("tr");
  		  rows.hide();
  		} else {
  		  crows.show();  			
  		}	  
  		total();
  }
  
  function filterCols() {
	  $("td.allcol,th.allcol").hide();
	  var v = "td." + $("#viewToolbar").val() + ",th." + $("#viewToolbar").val();
	  var ccols = $(v);
	  v = "td." + $("#colToolbar").val() + ",th." + $("#colToolbar").val();
	  ccols.filter(v).show();
	  filterRows();
  }
  
  function loadMsg() {
	  $('#loading').show();
  }
  
  function hideMsg() {
	  $('#loading').hide();
  }

  function bitstreamPrep() {
	filterCols();
  }
  
  function argLink(url) {
	  loadMsg();
	  var url2 = url;
	  url2 += "&comm=" + $("#communityToolbar").val();
	  url2 += "&view=" + $("#viewToolbar").val();
	  if ($('#qcol').val() != "") {
		  url2 += "&col="  + $("#qcol").val();
	  } else {
		  url2 += "&col="  + $("#colToolbar").val();		  
	  }
	  url2 += "&type=" + $("#typeToolbar").val();
	  url2 += "&warn=" + $("#warnToolbar").val();
	  $("#showopt input:checkbox:checked").each(function(){
		  var v = $(this).val();
		  url2 += "&show[]=" + v;
	  });
	  window.open(url2, 'ITEMS');
	  window.focus();
	  hideMsg();
  }

  function qcLink(url) {
	  loadMsg();
	  var url2 = url;
	  url2 += "&collex=";
	  $("input:checked[name=collex]").each(function(){url2 += $(this).val() +","});
	  url2 += "&col=";
	  $("input.qccol:checked").each(function(){url2 += $(this).val() +","});		  
	  if ($("#warnonly:checked").is("*")) {
		  url2 += "&warn=warn";
	  }
	  window.open(url2, 'COLLs');
	  window.focus();
	  hideMsg();
  }

  function dolocSelect() {
	  if ($("#locsel").val() != null) {
		  $("#loc").val($("#loc").val() + $("#locsel").val() + "/");		
		  $("#loc").focus();
		  $("#loc").blur();
	  }
	  $("#locpick").hide();
  }
  
  function doloc() {
	  $("#locsel").load("getIngestLoc.php?loc=" + $("#loc").val(), function(){
		  $("#locpick").show();
	  });
  }
  
  function jobQueue() {
	  $('#filterSubmit,#ingestSubmit,#changeParentSubmit,#reindexSubmit').attr('disabled',true);
	  $("#submitting").show();
	  setTimeout(function(){document.location="/batch-tools/web/queue.php"}, 8000)
  }

  function jobQueueUpload() {
	  $('#filterSubmit,#ingestSubmit,#changeParentSubmit,#reindexSubmit').attr('disabled',true);
	  $("#status").text("Please wait for file to upload (see status) before navigating away");
  }