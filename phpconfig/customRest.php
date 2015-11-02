<?php
/*
This file encapsulates institution-specific business logic used within this set of tools.  It would be necessary to provide meaningful implementations of each of these custom functions.

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
include "custom.php";
class customRest extends custom {
	
	public function __construct() {
		$this->communityInit = RestInitializer::instance();
	}

}

class RestInitializer {
	static $INSTANCE;
	
	public function getId($obj) {
		if (isset($obj["uuid"])) {
			return $obj["uuid"];
		}
		if (isset($obj["id"])) {
			return $obj["id"];
		}
		return null;
	}
	
	
	public function initCommunities() {
		$json_a = util::json_get(custom::instance()->getRestServiceUrl() . "/communities/?expand=parentCommunity&limit=10");
		foreach($json_a as $k=>$comm) {
			$pid = (isset($comm["parentCommunity"])) ? $this->getId($comm["parentCommunity"]) : $this->getId($comm);
			$this->initJsonCommunity($pid, $comm);
		}
		uasort(community::$COMMUNITIES, "pathcmp");   
		foreach(community::$COMMUNITIES as $k=>$obj) {
			echo $k . "   ";
		}
	}
	
	public function initJsonCommunity($pid, $comm) {
		new community($this->getId($comm), $comm["name"], $comm["handle"], $pid);
	}
	
	public function initCollections() {
		$json_a = util::json_get(custom::instance()->getRestServiceUrl() . "/communities/?expand=collections");
		foreach($json_a as $k=>$comm) {
			$this->initJsonCommunityColl($comm);
		}
		uasort(collection::$COLLECTIONS, "pathcmp");   
		uasort(community::$COMBO, "pathcmp");   
	}

	public function initJsonCommunityColl($comm) {
		if (isset($comm["collections"])) {
			foreach($comm["collections"] as $coll) {
				new collection($this->getId($coll), $coll["name"], $coll["handle"], $this->getid($comm));
			}		
		}		
	}

	public static function instance() {
		if (self::$INSTANCE == null) self::$INSTANCE = new RestInitializer();
		return self::$INSTANCE;
	}
}

?>