<?php
include "queriesBasic.php";
include "queriesDate.php";
include "queriesEmbargo.php";
include "queriesImage.php";
include "queriesLicense.php";
include "queriesMeta.php";
include "queriesMisc.php";
include "queriesMod.php";
include "queriesText.php";
include "queriesType.php";
include "auxFields.php";

class query {
	public $name;
	public $header;
	public $subq;
	public $classes;
	public $func;
	public $showarr;
	
	public static $count = 0;

	public static $CATQ = array();
	public static $MULTCAT = array();
	
	public static $QUERIES = array();
	
	function __construct($name, $header, $subq, $classes, $testVal, $showarr) {
    	$CUSTOM = custom::instance();
    	
		self::$count++;
		if (self::$count % 2 == 0) {
			$oddeven = "even";			
		} else {
			$oddeven = "odd";
		}
		
		$this->name = $name;
		$this->header = $header;
		$this->subq = $subq;
		$this->classes = $name . " allcol " . $oddeven . " " . $classes;
		$this->testVal = $testVal;
		$this->showarr = $showarr;
		
		$docols = array();
		if (isset($_GET['col'])) {
			$col = $_GET['col'];
			if ($col != "") {
				$docols = explode(",", $_GET['col']);
			}
		}
		
		if (!$CUSTOM->hasQueryKey($classes)) return;
		
		$addit = (count($docols) == 0) ? true : in_array($name, $docols);

		if ($addit) {
			self::$QUERIES[count(self::$QUERIES)] = $this;
		}
		
		$carr = explode(" ", $classes);
		if (count($carr) > 1) {
			self::$MULTCAT[$name] = $this;
		} else {
		  foreach ($carr as $cat) {
			if (isset(self::$CATQ[$cat])) {
			} else {
				self::$CATQ[$cat] = array();
			}
			self::$CATQ[$cat][] = $this;
		  }
		}
		
	}
	
	function getShowArrArg() {
		$ret = "";
		foreach($this->showarr as $s) {
			$ret .= "&show[]=" . $s;
		}
		return $ret;
	}
	
	public static function getQuery($name) {
		foreach(self::$QUERIES as $q) {
			if ($q->name == $name) 
				return $q;
		}
		return new query("invalid","Invalid Parameters","and 1=2","",new testValZero(),array());
	}
	
	function testVal($vals, $val) {
		return $this->testVal->testVal($vals, $val);
	}
	
	function mainQuery() {
		return "
  (
    select count(*) 
    from item i
    inner join collection2item c2i 
      on c2i.item_id = i.item_id 
      and c2i.collection_id = coll.collection_id
    where (i.in_archive is true or i.discoverable = false)
    {$this->subq}
  ) as {$this->name},
";		
	}

	function commQuery($filter) {
		return "
  (
    with recursive r_comm2coll as (
      select 
        community_id, 
        collection_id
      from 
        community2collection
      union
      select 
        cm2cm.parent_comm_id as community_id, 
        r.collection_id
      from 
        r_comm2coll r
      inner join
        community2community cm2cm
      on
        cm2cm.child_comm_id = r.community_id
    )
    select count(i.*) as x
    from r_comm2coll r
    inner join item i 
    on i.owning_collection = r.collection_id 
    and i.in_archive is true 
    where r.community_id = comm.community_id
    {$this->subq} 
    {$filter}
  ) as {$this->name},
";		
	}

	function mainItemQuery() {
		return "
  (
    select count(*) 
    from item i
    where i.item_id = i2.item_id
    {$this->subq}
  ) as {$this->name},
";		
	}

	public static function getFilterArgs() {
		$str = "?view=" . util::getArg("view",self::getDefaultView());
		$str .= "&col=" . util::getArg("col","");
		$str .= "&warn=" . util::getArg("warn","");
		$str .= "&type=" . util::getArg("type","");
		$str .= "&comm=" . util::getArg("comm","");
		return $str;
	}
	
	public static function getDefaultView() {
    	$vc = util::getArg("col","");
    	if (($vc == "") || ($vc == "")) return "basic";
    	return "";		
	}

    public static function toolbar() {
    	$v = util::getArg("view",self::getDefaultView());
    	echo '<select id="viewToolbar">';
    	util::makeOpt("allcol","All Attributes",$v);
    	foreach(custom::instance()->getQueryKeys() as $label => $name) {
    		util::makeOpt($label, $name, $v);
    	}
    	echo '</select>';

    	$v = util::getArg("col","");
    	echo '<select id="colToolbar">';
    	util::makeOpt("","All Columns",$v);
    	foreach(self::$QUERIES as $q) {
    		util::makeOpt($q->name,$q->header,$v);
  		};    	
    	echo '</select>';

    	$v = util::getArg("warn","");
    	echo '<select id="warnToolbar">';
    	util::makeOpt("","Any Status",$v);
    	util::makeOpt("warn","Has Warning",$v);
    	util::makeOpt("no-warn","Has No Warning",$v);
    	echo '</select>';

    	$v = util::getArg("type","basic");
    	echo '<select id="typeToolbar">';
    	util::makeOpt("","All Item types",$v);
    	util::makeOpt("docs","Has Documents",$v);
    	util::makeOpt("images","Has Images",$v);
    	util::makeOpt("video","Has Video",$v);
    	util::makeOpt("other","Has Other",$v);
    	util::makeOpt("empty","Has No Media",$v);
    	echo '</select>';

		$v = util::getArg("col","");
		echo "<input type='hidden' name='qcol' value='$v' id='qcol'/>";
    }

    static function getQueryList() {
    	$CUSTOM = custom::instance();
    	$CATEGORIES = $CUSTOM->getQueryKeys();
    	echo <<< HERE
    	<fieldset class='queryCols'><legend>Columns to Query</legend>
    	<fieldset>
    	  <button onclick="$('input.qccol,#warnonly').removeAttr('checked');">Uncheck All</button>
HERE;
    	foreach($CATEGORIES as $label => $name) {
    	  echo <<< HERE
    	  <button label='$label' name='$name' class='checkbutton checkon'>Check $name</button>
HERE;
    	}
    	echo <<< HERE
    	  <button onclick="$('input.qccol').attr('checked','Y');">Check All</button>
    	</fieldset>
    	<div class='checkboxes'>
HERE;
		self::showCheckboxes("General Attributes", self::$MULTCAT);
		$dcount = 2+count(self::$MULTCAT);
		$split = true;
		$total = count(self::$QUERIES) + 2 * count(self::$CATQ);
		foreach(self::$CATQ as $cat => $arr) {
		  if (!isset($CATEGORIES[$cat])) continue;
		  self::showCheckboxes($CATEGORIES[$cat], $arr);
		  $dcount += count($arr) + 2;
		  if ($split && $dcount >= $total/2) {
		  	echo "</div><div class='checkboxes'>";
		  	$split = false;
		  }
		}
		echo <<< HERE
		</div>
    	</fieldset>
HERE;
    }
    
    static function showCheckboxes($title, $arr) {
		  echo "<h4>$title</h4>";
		  echo "<ul>";
		  foreach($arr as $q) {
     		$name = $q->name;
     		$header = $q->header;
     		$classes = $q->classes;
     		$checked = ($name == "itemCount") ? "checked" : "";
     		echo "<li><input class='qccol $classes' name='qcCol' type='checkbox' $checked id='$name' value='$name'><label for='$name'>$header</label></li>";
		  }	
		  echo "</ul>";    	
    }

}

class testValTrue {
	public static function testVal($vals, $val) {
		return true;
	}
}

class testValZero {
	public static function testVal($vals, $val) {
		return ($val == 0);
	}
}

class testValPos {
	public static function testVal($vals, $val) {
		return ($val > 0);
	}
}

class testValNumItem {
	public static function testVal($vals, $val) {
		return ($val == $vals['itemCount']);
	}
}
class testValNumImage {
	public static function testVal($vals, $val) {
		return ($val == $vals['itemCountImage']);
	}
}
class testValNumDoc {
	public static function testVal($vals, $val) {
		return ($val == $vals['itemCountDoc']);
	}
}

function initQueries() {
    auxFields::initAuxFields();

    initQueriesBasic();
    initQueriesText();
    initQueriesType();
    initQueriesDate();
    initQueriesImage();
    initQueriesLicense();
    initQueriesMeta();
    initQueriesMisc();
    initQueriesMod();
    initQueriesEmbargo();

    $CUSTOM = custom::instance();
    $CUSTOM->initCustomQueries();
}
?>