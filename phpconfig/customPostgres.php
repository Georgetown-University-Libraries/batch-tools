<?php
/*
Custom initializer using pgsql module.

NOTE: THESE TOOLS PROVIDE MORE SUPPORT IF USING PDO_PGSQL.  PLEASE USE THAT IF POSSIBLE.

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
class customPostgres extends custom {
	private $dbh;
	public function getDbh() {
		if ($this->dbh != null) return $this->dbh;
		$this->dbh = pg_connect("host=localhost port=5432 dbname=dspace user=dspace_ro password=xxxxx");
		if (!$this->dbh) {
     		die("Error in connection: " . pg_last_error());
		}      
		return $this->dbh;		
	}
	
	public function __construct($ver) {
		parent::__construct($ver);
		PostgresInitializer::setInstance($this->getDbh());
		$this->communityInit = PostgresInitializer::instance();
	}

	public function getQueryVal($sql, $arg = array()) {
		$dbh = $this->getDbh();
		$stmt = pg_prepare($dbh, "my_query", $sql);
		$result = pg_execute($dbh, "my_query",$arg);
		$ret = "";
 		if (!$result) {
     		die("Error in SQL query: " . $sql . pg_last_error());
 		}       

 		while ($row = pg_fetch_array($result)) {
 			$ret = $row[0];
 		}       

		// free memory
		pg_free_result($result);    
		return $ret;
	}
}

class PostgresInitializer {
	private $dbh;
	
	public function __construct($dbh) {
		$this->dbh = $dbh;
	}
	
	public function initCommunities() {
		$sql = <<< EOF
		select community_id, name, handle, parent_comm_id 
		from community
		inner join handle on community_id = resource_id and resource_type_id = 4
		left join community2community on child_comm_id = community_id
		order by name;  
EOF;

		$result = pg_query($this->dbh, $sql);
 		if (!$result) {
     		die("Error in SQL query: " . pg_last_error());
 		}       

 		while ($row = pg_fetch_array($result)) {
 			new community($row[0], $row[1], $row[2], $row[3]);
 		}       

		// free memory
		pg_free_result($result);    
		uasort(community::$COMMUNITIES, "pathcmp");   
	}
	
	public function initCollections() {
		$sql = <<< EOF
		select c.collection_id, c.name, handle, c2c.community_id 
		from collection c
		inner join handle on collection_id = resource_id and resource_type_id = 3
		left join community2collection c2c on c2c.collection_id = c.collection_id
		order by c.name;  
EOF;

		$result = pg_query($this->dbh, $sql);
		if (!$result) {
    		die("Error in SQL query: " . pg_last_error());
		}       
		while ($row = pg_fetch_array($result)) {
		 	new collection($row[0], $row[1], $row[2], $row[3]);
		}       

		// free memory
		pg_free_result($result);       
		uasort(collection::$COLLECTIONS, "pathcmp");   
		uasort(community::$COMBO, "pathcmp");   
	}

	private static $INSTANCE;
	public static function instance() {
		if (self::$INSTANCE == null) die("Must init PostgresInitializer");
		return self::$INSTANCE;
	}
	public static function setInstance($dbh) {
		if (self::$INSTANCE == null) self::$INSTANCE = new PostgresInitializer($dbh);
		return self::$INSTANCE;
	}
}

?>