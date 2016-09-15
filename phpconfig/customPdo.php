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
include "customRest.php";
class customPdo extends customRest {
	
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
	
	public function getDSpaceVer() {
	    $sql = <<< HERE
  select 
    cast(substring(version from '^\d+') as int) 
  from 
    schema_version 
  where 
    installed_rank = (
      select 
        max(installed_rank) 
      from schema_version
    );
HERE;
	    return $this->getQueryVal($sql, array());
	}
}

?>