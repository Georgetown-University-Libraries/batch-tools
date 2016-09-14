<?php
/*
Classes for presenting Communities and Collections within DSpace Tools.
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


function pathcmp($a,$b) {
	$aa = $a->getMyPath();
	$bb = $b->getMypath();
	if ($aa == $bb) return 0;
	return ($aa < $bb) ? -1 :1;
}


class community {
	public $community_id;
	public $name;
	public $handle;
	public $parent_comm_id;
	public $shortname;
	
	public static $COMMUNITIES = array();
	public static $COMBO = array();
	
	function __construct($community_id, $name, $handle, $parent_comm_id) {
		$this->community_id = $community_id;
		$this->name = $name;
		$this->handle = $handle;
		$this->parent_comm_id = $parent_comm_id;
		$this->shortname = $this->getShortName($name, $name);
		
		self::$COMMUNITIES[$community_id] = $this;
		self::$COMBO[$community_id] = $this;
	}
	
	public function getPathName() {
        return custom::instance()->getPathName($this->name);
	}
	
	public function getShortName($name, $def) {
        return custom::instance()->getShortName($this->name, $this->name);
	}
	
	public function getParent() {
		if ($this->parent_comm_id == null) {
			return $this;
		}
		return self::$COMMUNITIES[$this->parent_comm_id];
	}
	public function getMyTopCommunity() {
		return $this->getTopCommunity($this);
	}
	public function getTopCommunity($comm) {
		$p = $comm->getParent();
		if ($comm->community_id == $p->community_id) {
			return $comm;
		}
		return $this->getTopCommunity($p);
	}
	public function getMyPath() {
		$p = $this->getParent();
		if ($this->community_id == $p->community_id) {
			return "/" . $p->getPathName();
		}
		return $p->getMyPath() . "/" . $this->getPathName();
	}
	
	public function getMyPrettyPath() {
		$path = $this->getMyPath();
        for($count=1; $count > 0;) {
          $path = preg_replace("|/[^/]+/|","++/", $path, 1, $count);
        }
		return $path;
	}

	public static function toolbar() {
    	$v = util::getArg("comm", "");
		echo "<select id='communityToolbar'>";
		util::makeOpt('allcoll', "All Collections", $v);
		foreach (self::$COMMUNITIES as $comm) {
			if ($comm->parent_comm_id == null) {
				util::makeOpt('comm' . $comm->community_id, $comm->shortname, $v);
			} 			
		}
    	echo '</select>';
	}
	
}

class collection {
	public $collection_id;
	public $name;
	public $handle;
	public $community_id;
	public $topCommunity;
	
	public static $COLLECTIONS = array();
	
	public function getParent() {
		if ($this->community_id == null) {
			return $this;
		}
		return community::$COMMUNITIES[$this->community_id];
	}
	public function getMyPath() {
		return $this->getParent()->getMyPath() . '/' . custom::instance()->getPathName($this->name);
	}

	public function getMyPrettyPath() {
		$path = $this->getMyPath();
        for($count=1; $count > 0;) {
          $path = preg_replace("|/[^/]+/|","++/", $path, 1, $count);
        }
		return $path;
	}


	function __construct($collection_id, $name, $handle, $community_id) {
		$this->collection_id = $collection_id;
		$this->name = $name;
		$this->handle = $handle;
		$this->community_id = $community_id;
		$this->topCommunity=$this->getParent()->getMyTopCommunity();
		
		self::$COLLECTIONS[$collection_id] = $this;
		community::$COMBO[$collection_id] = $this;
	}
	

	public static function getCollectionWidget($commsel, $collsel) {
		$comms = "";
		foreach(community::$COMMUNITIES as $c) {
			if ($c->parent_comm_id == null) {
				$sel = ($commsel == $c->shortname) ? "selected" : "";
				$comms .= "<option value='{$c->shortname}' {$sel}>{$c->name}</option>";			
			}
		}
		$colls = "";
		foreach(self::$COLLECTIONS as $c) {
			$sel = ($collsel == $c->handle) ? "selected" : "";
			$colls .= "<option class='allcoll {$c->topCommunity->shortname}' value='{$c->handle}' {$sel}>{$c->getMyPath()}</option>";
		}
		echo <<< HERE
		<div id="collWidget">
		<fieldset>
		<legend>Select the Collection to process</legend>
		<p>
		<label for="commSelect">Find Collection by Community</label>
		<select id="commSelect" name="community">
		  <option value="allcoll">All Communities</option>
		  {$comms}
		</select>
		</p>
		<p>
		<label for="collSelect">Collection *</label>
		<select id="collSelectHold" disabled style="display:none">
		  <option class="top" value="">Please select a Collection</option>
		  {$colls}
		</select>
		<select id="collSelect" name="collection">
		  <option class="top" value="">Please select a Collection</option>
		  {$colls}
		</select>
		</p>
		</fieldset>
		</div>
HERE;
	}

	public static function getSubcommunityCollWidget($commsel, $name, $label) {
		$comms = "";
		foreach(community::$COMBO as $c) {
			$sel = ($commsel == $c->handle) ? "selected" : "";
			$comms .= "<option value='{$c->handle}' {$sel}>{$c->getMyPath()}</option>";			
		}
		echo <<< HERE
		<div id="comboWidget">
		<p>
		<label for="subcommCollSelect">Select the Community/Collection $label</label>
		<select id="subcommCollSelect" name="$name">
		  <option value="">Select a Community or a Collection</option>
		  {$comms}
		</select>
		</p>
		</div>
HERE;
	}

	public static function getSubcommunityWidget($commsel, $name, $label) {
		$comms = "";
		foreach(community::$COMMUNITIES as $c) {
			$sel = ($commsel == $c->handle) ? "selected" : "";
			$comms .= "<option value='{$c->handle}' {$sel}>{$c->getMyPath()}</option>";			
		}
		echo <<< HERE
		<div id="subcommWidget">
		<p>
		<label for="subcommSelect">Select the (sub)community $label</label>
		<select id="subcommSelect" name="$name">
		  <option value="">Select a Community</option>
		  {$comms}
		</select>
		</p>
		</div>
HERE;
	}

	public static function getSubcommunityIdWidget($commsel, $name, $label) {
		$comms = "";
		foreach(community::$COMMUNITIES as $c) {
			$sel = ($commsel == $c->community_id) ? "selected" : "";
			$comms .= "<option value='{$c->community_id}' {$sel}>{$c->getMyPath()}</option>";			
		}
		echo <<< HERE
		<div id="subcommWidget">
		<p>
		<label for="subcommSelect">Select the (sub)community $label</label>
		<select id="subcommSelect" name="$name">
		  <option value="">Select a (Sub)community</option>
		  {$comms}
		</select>
		</p>
		</div>
HERE;
	}

	public static function getCollectionIdWidget($commsel, $name, $label) {
		$comms = "";
		foreach(collection::$COLLECTIONS as $c) {
			$sel = ($commsel == $c->collection_id) ? "selected" : "";
			$comms .= "<option value='{$c->collection_id}' {$sel}>{$c->getMyPath()}</option>";			
		}
		echo <<< HERE
		<div id="collOnlyWidget">
		<p>
		<label for="collOnlySelect">Select the collection $label</label>
		<select id="collOnlySelect" name="$name">
		  <option value="">Select a Collection</option>
		  {$comms}
		</select>
		</p>
		</div>
HERE;
	}

	public static function getCollectionHandleWidget($commsel, $name, $label) {
		$comms = "";
		foreach(collection::$COLLECTIONS as $c) {
			$sel = ($commsel == $c->handle) ? "selected" : "";
			$comms .= "<option value='{$c->handle}' {$sel}>{$c->getMyPath()}</option>";			
		}
		echo <<< HERE
		<div id="collOnlyWidget">
		<p>
		<label for="collOnlySelect">Select the collection $label</label>
		<select id="collOnlySelect" name="$name">
		  <option value="">Select a Collection</option>
		  {$comms}
		</select>
		</p>
		</div>
HERE;
	}
}

class collectionArg {
	public static function isCollection() {
		return isset($_GET['collection']) && self::oneSet();
	}
	public static function isCommunity() {
		return isset($_GET['community'])  && self::oneSet();
	}
	
	public static function getInputName() {
		if (self::isCollection()) return "collection"; 
		if (self::isCommunity()) return "community";
		return "bogus"; 
	}
	
	static function oneSet() {
		if (isset($_GET['collection']) && isset($_GET['community'])) {
			return false;
		};
		return true;
	}
	static function isValid() {
		return self::getId() != null;
	}
	static function getIdString() {
		if (self::isCollection()) return $_GET['collection'];
		else if (self::isCommunity()) return $_GET['community'];
		return null;
	}
	
	static function getId() {
		$v = self::getIdString();
		if ($v == null) return null;
		if (is_numeric($v)) {
			return intval($v);
		}
		return null;
	}
	
	static function getName() {
		$id = self::getId();
		if ($id == null) return '';
		try {
			if (self::isCollection()) return collection::$COLLECTIONS[$id]->name;
			else if (self::isCommunity()) return community::$COMMUNITIES[$id]->name;
			else return "";
		} catch (exception $e) {
			return "";
		}
	}
}

function hiercmp($a,$b) {
	if ($a->path == $b->path) return 0;
	return ($a->path < $b->path) ? -1 :1;
}

class hierarchy {
	public $id;
	public $pid;
	public $topid;
	public $name;
	public $handle;
	public $phandle;
	public $thandle;
	public $path;
	public $obj;
	public $sname;
	
	public $children;
    public $collList;

	function __construct($obj) {
		if ($obj instanceof community) {
  		  $this->id = $obj->community_id;
  		  $this->hid = "comm-".$obj->community_id;
		  $this->pid = $obj->parent_comm_id;
  		  $this->topid = $obj->getMyTopCommunity()->community_id;
		  $this->rclass = ($this->id == $this->topid) ? "comm" : "scomm";
		  $this->thandle = $obj->getMyTopCommunity()->handle; 
		  $this->type = "community";
		} else if ($obj instanceof collection) {
  		  $this->id = $obj->collection_id;
  		  $this->hid = "coll-".$obj->collection_id;
		  $this->pid = $obj->community_id;
  		  $this->rclass = "coll";
	      $this->topid = $obj->topCommunity->community_id;
  		  $this->thandle = $obj->topCommunity->handle; 
		  $this->type = "collection";
		}
	    $this->children = array();
	    $this->name = $obj->name;
	    $this->handle = $obj->handle;
	    $this->phandle = $obj->getParent()->handle; 
		$this->path = $obj->getMyPath(); 			
		$this->sname = $this->name;
		if (preg_match("/^The /", $this->name)) {
		  $this->sname = preg_replace("/^The /","", $this->name) . ", The";			
		}
	}
	
	public static $OBJECTS = array();
	public static $TOPS = array();
	public static $COMMS = array();
	static function initHierarchy($showall, $handle) {
		foreach(community::$COMMUNITIES as $c) {
			$object = new hierarchy($c);
			if ($object->thandle == $handle || $showall){
				self::$OBJECTS[] = $object;	
				self::$COMMS[$object->id] = $object;
			}
		}
		foreach(collection::$COLLECTIONS as $c) {
			$object = new hierarchy($c);
			if ($object->thandle == $handle || $showall){
				self::$OBJECTS[] = $object;	
			}
		}
		foreach(hierarchy::$OBJECTS as $object) {
			if ($object->pid == 0) {
				self::$TOPS[] = $object;
			} else {
				array_push(self::$COMMS[$object->pid]->children, $object);
			}
		}
		uasort(self::$OBJECTS, 'hiercmp');
	}
    
    function getMyChildCollections() {
        $this->collList = array();
        echo "<h2>{$this->type} {$this->name} {$this->id}</h2>";
        return $this->getChildCollections($this);
    }
    function getChildCollections($root) {
        foreach($this->children as $child) {
        $c = count($root->collList);
        echo "<h4>{$child->type} {$child->name} {$child->id} {$c}</h4>";
            if ($child->type == "community") {
                $child->getChildCollections($root);
            } else {
                array_push($root->collList, $child->id);
            }
        }
        return $root->collList;
    }

	public static function getHierarchyHandleWidget($objsel, $name, $label) {
		$objs = "";
		foreach(hierarchy::$OBJECTS as $obj) {
			$sel = ($objsel == $obj->handle) ? "selected" : "";
			$objs .= "<option value='{$obj->handle}' {$sel}>{$obj->path}</option>";			
		}
		echo <<< HERE
		<div id="hiearchyWidget">
		<p>
		<select id="hierarchySelect" name="$name">
		  <option value="">Select a Community or Collection</option>
		  {$objs}
		</select>
		</p>
		</div>
HERE;
	}

}
?>