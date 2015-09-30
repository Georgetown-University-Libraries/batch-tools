<?php
/*
Custom initializer using PDO module.

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
class customPdo extends custom {
	
	private $dbh;
	public function getPdoDb() {
		if ($this->dbh != null) return $this->dbh;
		$this->dbh = new PDO("pgsql:host=localhost;port=5432;dbname=dspace;user=dspace_ro;password=xxxx");
		if (!$this->dbh) {
  	        print_r($this->dbh->errorInfo());
     		die("Error in SQL query: ");
		}      
		return $this->dbh;		
	}
	
	public function isPdo() {return true;}
	public function __construct($ver) {
		parent::__construct($ver);
		PdoInitializer::setInstance($this->getPdoDb());
		$this->communityInit = PdoInitializer::instance();
	}

	public function getQueryVal($sql, $arg) {
		$dbh = $this->getPdoDb();
		$stmt = $dbh->prepare($sql);
		$result = $stmt->execute($arg);
 		if (!$result) {
 			print($sql);
  	        print_r($dbh->errorInfo());
     		die("Error in SQL query: ");
 		}       
		$result = $stmt->fetchAll();
 		$ret = "";
 		foreach ($result as $row) {
		 	$ret = $row[0];
		}  
		return $ret;
	}
}

class PdoInitializer {
	private $dbh;
	
	public function __construct($dbh) {
		$this->dbh = $dbh;
	}
	
	public function initCommunities() {
		$sql = <<< EOF
		select community_id, m.text_value as name, handle, parent_comm_id 
		from community
		inner join metadatavalue m on community_id = m.resource_id and m.resource_type_id = 4
        inner join metadatafieldregistry mfr 
          on mfr.metadata_field_id = m.metadata_field_id
          and mfr.element = 'title' and mfr.qualifier is null
		inner join handle h on community_id = h.resource_id and h.resource_type_id = 4
		left join community2community on child_comm_id = community_id
		order by name;  
EOF;

		$result = $this->dbh->query($sql);
 		if (!$result) {
 			print($sql);
  	        print_r($dbh->errorInfo());
     		die("Error in SQL query: ");
 		}       

 		foreach ($result as $row) {
 			new community($row[0], $row[1], $row[2], $row[3]);
 		}       

		// free memory
		uasort(community::$COMMUNITIES, "pathcmp");   
	}
	
	public function initCollections() {
		$sql = <<< EOF
		select c.collection_id, m.text_value as name, handle, c2c.community_id 
		from collection c
		inner join metadatavalue m on collection_id = m.resource_id and m.resource_type_id = 3
        inner join metadatafieldregistry mfr 
          on mfr.metadata_field_id = m.metadata_field_id
          and mfr.element = 'title' and mfr.qualifier is null
		inner join handle h on collection_id = h.resource_id and h.resource_type_id = 3
		left join community2collection c2c on c2c.collection_id = c.collection_id
		order by name;  
EOF;

		$result = $this->dbh->query($sql);
 		if (!$result) {
 			print($sql);
  	        print_r($dbh->errorInfo());
     		die("Error in SQL query: ");
 		}       

 		foreach ($result as $row) {
		 	new collection($row[0], $row[1], $row[2], $row[3]);
		}       

		// free memory
		uasort(collection::$COLLECTIONS, "pathcmp");   
		uasort(community::$COMBO, "pathcmp");   
	}
	

	private static $INSTANCE;
	public static function instance() {
		if (self::$INSTANCE == null) die("Must init PdoInitializer");
		return self::$INSTANCE;
	}
	public static function setInstance($dbh) {
		if (self::$INSTANCE == null) self::$INSTANCE = new PdoInitializer($dbh);
		return self::$INSTANCE;
	}
}

?>