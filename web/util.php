<?php
/*
Utility Class...

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
class util {
	public static function makeOpt($val,$text,$curval) {
    	$sel = ($val == $curval) ? "selected" : "";
    	echo "<option value='{$val}' {$sel}>{$text}</option>";
    }

	public static function getArg($name, $def) {
		if (isset($_GET[$name])) return $_GET[$name];
		return $def; 
	}
	
	public static function getIdList($name, $prefix) {
		$arg = self::getArg($name, "");
		if ($arg == "") return "";
		$ret = $prefix . "(";
		$arr = explode(",", $arg);
		$first = true;
		foreach($arr as $i) {
			if (is_numeric($i)) {
				if ($first) {
					$first = false;
				} else {
					$ret .= ",";
				}
				$ret .= $i;
			}
		}
		$ret .= ")";
		return $ret;
	}
	

	public static function getPostArg($name, $def) {
		if (isset($_POST[$name])) return $_POST[$name];
		return $def; 
	}

	public static function submitPage($cmd) {
$header = new LitHeader("Submit Job");
echo <<< HERE
<html>
<head>
HERE;
$header->litPageHeader();
echo <<< HERE
</head>
<body>
HERE;
$header->litHeader(array());
echo <<< HERE
<div>{$cmd}</div>
HERE;
$header->litFooter();
echo <<< HERE
</body>
</html>
HERE;
	exit();
	}

    public static function json_get($url) {
    	$ch = curl_init();
		$headers = array('Accept: application/json');

		curl_setopt($ch, CURLOPT_URL, "$url"); # URL to post to
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 ); # return into a variable
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers ); # custom headers, see above
		//Assuming localhost access..
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0 ); # custom headers, see above
		$result = curl_exec( $ch ); # run!
		curl_close($ch);
		return json_decode($result, true);
    	
    }
	
}

?>