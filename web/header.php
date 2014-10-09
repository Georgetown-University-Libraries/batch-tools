<?php
/*
Present a DSpace Tools page header.
Note that the format type ids and metadata field ids in this code are institution specific and have not been abstracted.

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
include dirname(dirname(__FILE__)) . '/phpconfig/init.php';
include 'util.php';

class LitHeader {
	public $title;
	public function __construct($title) {
		$this->title = $title;
	}
	
	public function litPageHeader() {
		$CUSTOM = custom::instance();
		$WEBROOT = custom::getWebRoot();
		echo <<< HERE
		<script	src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
 		<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.9.1/jquery-ui.min.js"></script>
		<script	src="{$WEBROOT}/web/qcReport.js"></script>
		<script	src="{$WEBROOT}/web/sorttable.js"></script>
		<link rel="stylesheet" type="text/css" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/themes/ui-lightness/jquery-ui.css"></link>
		<link rel="stylesheet" type="text/css" href="{$WEBROOT}/web/qcReport.css"></link>
		<title>{$this->title}</title>
HERE;
	}
	
	public function litHeader($arr) {
		$CUSTOM = custom::instance();
		$this->litHeaderAuth($arr, $CUSTOM->isUserViewer());
	}
	
	public function litHeaderAuth($arr, $hasPerm) {
		$CUSTOM = custom::instance();
		$WEBROOT = $CUSTOM->getWebRoot();
		$sysname = $CUSTOM->getSystemName();
		
		echo <<< HERE
		<div class="breadcrumb">
		  <a href="{$WEBROOT}/web/index.php">{$sysname}</a> &gt;
HERE;
	    foreach($arr as $a) {
	    	echo "{$a} &gt;"; 
	    }
		echo <<< HERE
		  {$this->title}
          [{$CUSTOM->getCurrentUser()}]
		</div>
		<hr/>
		<h1 align="center">{$this->title}</h1>
HERE;
		if (!$hasPerm) {
			echo "<h2 class='guperm'>You do not have permission to access this page.</h2>";
			$this->litFooter();
			exit;
		}
	}

	public function litFooter() {
		echo <<< HERE
		<hr/>
HERE;
	}
	
}
?>