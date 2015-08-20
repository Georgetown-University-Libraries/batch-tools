<?php

class auxFields {
	
  public static $AUXQ;	
  public static $AUXT;	
  public static $IMGKEY;
  public static $SHOWARR;
  public static $DC;	
  	
  public static function initAuxFields() {
	$handleContext =  isset($GLOBALS['handleContext']) ? $GLOBALS['handleContext'] : "";

    self::$AUXQ = array();
    self::$DC = array();
    self::$AUXT = array();
    self::$IMGKEY = array();

    self::addAuxField("ItemId", "Item id number in DSpace", "i.item_id", "", false);

    self::addAuxField("Available", "Date or date range item became available to the public.", self::getFieldByName("date","available"), "dc.date.available", false);
    self::addAuxField("Accession", "Date DSpace takes possession of item.", self::getFieldByName("date","accessioned"), "", false);
    self::addAuxField("Issue", "Date of publication or distribution.", self::getFieldByName("date","issued"), "dc.date.issued", false);
    self::addAuxField("Create", "Date of creation or manufacture of intellectual content if different from date.issued.", self::getFieldByName("date","created"), "dc.date.created[en_US]", false);
    self::addAuxField("Creator", "dc.creator", self::getFieldByName("creator",null), "dc.creator[en_US]", false);
    self::addAuxField("Author", "dc.contributor.author", self::getFieldByName("contributor","author"), "dc.contributor.author[en_US]", false);
    self::addAuxField("URI", "", self::getFieldByName("identifier","uri"), "", false);
    self::addAuxField("RelURI", "URI to external resource", self::getFieldByName("relation","uri"), "dc.relation.uri[en_US]", false);
    self::addAuxField("Publisher", "", self::getFieldByName("publisher",null), "dc.publisher[en_US]", false);
    self::addAuxField("Subject", "", self::getFieldByName("subject",null), "dc.subject[en_US]", false);
    self::addAuxField("Format", "", self::getFieldByName("format",null), "dc.format[en_US]", false);
    self::addAuxField("Type", "", self::getFieldByName("type",null), "dc.type[en_US]", false);
    self::addAuxField("Provenance", "dc.description.provenance", self::getFieldByName("description","provenance"), "", false);
    self::addAuxField("CoverageTemporal", "", self::getFieldByName("coverage","temporal"), "", false);
    self::addAuxField("CoverageGeorgraphic", "", self::getFieldByName("coverage","geographic"), "", false);
    self::addAuxField("BibliographicCitation", "", self::getFieldByName("identifier","bibliographicCitation"), "", false);

    $thumb = <<< EOF
select array_to_string(array_agg(text('{$handleContext}/bitstream/id/' || bit.bitstream_id || '/' || bitmv.text_value)), '<hr/>') 
from bitstream bit
inner join metadatavalue bitmv
  on bitmv.resource_id = bit.bitstream_id and bitmv.resource_type_id = 0
inner join metadatafieldregistry bitmfr
  on bitmfr.metadata_field_id = bitmv.metadata_field_id 
  and bitmfr.element = 'title' and bitmfr.qualifier is null
inner join bundle2bitstream b2b
  on b2b.bitstream_id = bit.bitstream_id
inner join metadatavalue bunmv
  on b2b.bundle_id = bunmv.resource_id and bunmv.resource_type_id = 1
  and bunmv.text_value = 'THUMBNAIL'
inner join metadatafieldregistry bunmfr
  on bunmfr.metadata_field_id = bunmv.metdata_field_id
  and bunmfr.element = 'title' and bunmfr.qualifier is null
inner join item2bundle i2b
  on i2b.bundle_id = b.bundle_id
  and i2b.item_id=i.item_id
EOF;
    self::addAuxField("GenThumb", "Thumbnail", "({$thumb})", "", true);

    $thumbName = <<< EOF
select array_to_string(array_agg(text(bitmv.text_value)), '<hr/>')
from bitstream bit
inner join metadatavalue bitmv
  on bitmv.resource_id = bit.bitstream_id and bitmv.resource_type_id = 0
inner join metadatafieldregistry bitmfr
  on bitmfr.metadata_field_id = bitmv.metadata_field_id 
  and bitmfr.element = 'title' and bitmfr.qualifier is null
inner join bundle2bitstream b2b
  on b2b.bitstream_id = bit.bitstream_id
inner join bundle2bitstream b2b
  on b2b.bitstream_id = bit.bitstream_id
inner join metadatavalue bunmv
  on b2b.bundle_id = bunmv.resource_id and bunmv.resource_type_id = 1
  and bunmv.text_value = 'THUMBNAIL'
inner join metadatafieldregistry bunmfr
  on bunmfr.metadata_field_id = bunmv.metdata_field_id
  and bunmfr.element = 'title' and bunmfr.qualifier is null
inner join item2bundle i2b
  on i2b.bundle_id = b.bundle_id
  and i2b.item_id=i.item_id
EOF;
    self::addAuxField("ThumbName", "Name of the thumbnail file", "({$thumbName})", "", false);

    $origName = <<< EOF
select array_to_string(array_agg(text(bitmv.text_value)), '<hr/>')
from bitstream bit
inner join metadatavalue bitmv
  on bitmv.resource_id = bit.bitstream_id and bitmv.resource_type_id = 0
inner join metadatafieldregistry bitmfr
  on bitmfr.metadata_field_id = bitmv.metadata_field_id 
  and bitmfr.element = 'title' and bitmfr.qualifier is null
inner join bundle2bitstream b2b
  on b2b.bitstream_id = bit.bitstream_id
inner join bundle2bitstream b2b
  on b2b.bitstream_id = bit.bitstream_id
inner join metadatavalue bunmv
  on b2b.bundle_id = bunmv.resource_id and bunmv.resource_type_id = 1
  and bunmv.text_value = 'ORIGINAL'
inner join metadatafieldregistry bunmfr
  on bunmfr.metadata_field_id = bunmv.metdata_field_id
  and bunmfr.element = 'title' and bunmfr.qualifier is null
inner join item2bundle i2b
  on i2b.bundle_id = b.bundle_id
  and i2b.item_id=i.item_id
EOF;
    self::addAuxField("OrigName", "Name of the original file", "({$origName})", "", false);

    /*Note that this rule contains a filter to ignore @mire zoom tiles, disregard if not applicable*/
    $otherName = <<< EOF
select array_to_string(array_agg(text(bitmv.text_value)), '<hr/>')
from bitstream bit
inner join metadatavalue bitmv
  on bitmv.resource_id = bit.bitstream_id and bitmv.resource_type_id = 0
inner join metadatafieldregistry bitmfr
  on bitmfr.metadata_field_id = bitmv.metadata_field_id 
  and bitmfr.element = 'title' and bitmfr.qualifier is null
inner join bundle2bitstream b2b
  on b2b.bitstream_id = bit.bitstream_id
inner join bundle2bitstream b2b
  on b2b.bitstream_id = bit.bitstream_id
inner join metadatavalue bunmv
  on b2b.bundle_id = bunmv.resource_id and bunmv.resource_type_id = 1
  and bunmv.text_value not in ('ORIGINAL', 'THUMBNAIL','TEXT')
inner join metadatafieldregistry bunmfr
  on bunmfr.metadata_field_id = bunmv.metdata_field_id
  and bunmfr.element = 'title' and bunmfr.qualifier is null
inner join item2bundle i2b
  on i2b.bundle_id = b.bundle_id
  and i2b.item_id=i.item_id
where bit.name != 'license.txt'
EOF;
    self::addAuxField("OtherName", "Name of auxilliary files", "({$otherName})", "", false);

    $textName = <<< EOF
select array_to_string(array_agg(text(bitmv.text_value)), '<hr/>')
from bitstream bit
inner join metadatavalue bitmv
  on bitmv.resource_id = bit.bitstream_id and bitmv.resource_type_id = 0
inner join metadatafieldregistry bitmfr
  on bitmfr.metadata_field_id = bitmv.metadata_field_id 
  and bitmfr.element = 'title' and bitmfr.qualifier is null
inner join bundle2bitstream b2b
  on b2b.bitstream_id = bit.bitstream_id
inner join bundle2bitstream b2b
  on b2b.bitstream_id = bit.bitstream_id
inner join metadatavalue bunmv
  on b2b.bundle_id = bunmv.resource_id and bunmv.resource_type_id = 1
  and bunmv.text_value = 'TEXT'
inner join metadatafieldregistry bunmfr
  on bunmfr.metadata_field_id = bunmv.metdata_field_id
  and bunmfr.element = 'title' and bunmfr.qualifier is null
inner join item2bundle i2b
  on i2b.bundle_id = b.bundle_id
  and i2b.item_id=i.item_id
EOF;
    self::addAuxField("Text", "Text bundle file name", "({$textName})", "", false);

    $size = <<< EOF
select array_to_string(array_agg(to_char(bit.size_bytes/1000/1000.0,'999G999G999D9')), '<hr/>')
from bitstream bit
inner join bundle2bitstream b2b
  on b2b.bitstream_id = bit.bitstream_id
inner join bundle2bitstream b2b
  on b2b.bitstream_id = bit.bitstream_id
inner join metadatavalue bunmv
  on b2b.bundle_id = bunmv.resource_id and bunmv.resource_type_id = 1
  and bunmv.text_value = 'ORIGINAL'
inner join metadatafieldregistry bunmfr
  on bunmfr.metadata_field_id = bunmv.metdata_field_id
  and bunmfr.element = 'title' and bunmfr.qualifier is null
inner join item2bundle i2b
  on i2b.bundle_id = b.bundle_id
  and i2b.item_id=i.item_id
EOF;
    self::addAuxField("SizeMB", "Size of the original file MB", "({$size})", "", false);

    $size = <<< EOF
select array_to_string(array_agg(to_char(bit.size_bytes/1000.0,'999G999G999D9')), '<hr/>')
from bitstream bit
inner join bundle2bitstream b2b
  on b2b.bitstream_id = bit.bitstream_id
inner join bundle2bitstream b2b
  on b2b.bitstream_id = bit.bitstream_id
inner join metadatavalue bunmv
  on b2b.bundle_id = bunmv.resource_id and bunmv.resource_type_id = 1
  and bunmv.text_value = 'ORIGINAL'
inner join metadatafieldregistry bunmfr
  on bunmfr.metadata_field_id = bunmv.metdata_field_id
  and bunmfr.element = 'title' and bunmfr.qualifier is null
inner join item2bundle i2b
  on i2b.bundle_id = b.bundle_id
  and i2b.item_id=i.item_id
EOF;
    self::addAuxField("SizeKB", "Size of the original file KB", "({$size})", "", false);

    $origId = <<< EOF
select array_to_string(array_agg(text(bit.internal_id)), '<hr/>')
from bitstream bit
inner join bundle2bitstream b2b
  on b2b.bitstream_id = bit.bitstream_id
inner join bundle2bitstream b2b
  on b2b.bitstream_id = bit.bitstream_id
inner join metadatavalue bunmv
  on b2b.bundle_id = bunmv.resource_id and bunmv.resource_type_id = 1
  and bunmv.text_value = 'ORIGINAL'
inner join metadatafieldregistry bunmfr
  on bunmfr.metadata_field_id = bunmv.metdata_field_id
  and bunmfr.element = 'title' and bunmfr.qualifier is null
inner join item2bundle i2b
  on i2b.bundle_id = b.bundle_id
  and i2b.item_id=i.item_id
EOF;
    self::addAuxField("OrigId", "Assetstore Id", "({$origId})", "", false);
    self::addAuxField("Private", "Private Item", "case when i.discoverable = false then 'Private' else 'Discoverable' end", "", false);
    self::addAuxField("Withdrawn", "Withdrawn Item", "case when i.in_archive = true then 'In Archive' else 'Withdrawn' end", "", false);

    $bitRestricted = <<< EOF
select case when count(*) > 0 then 'Unrestricted' else 'Restricted' end
from bitstream bit
inner join bundle2bitstream b2b
  on b2b.bitstream_id = bit.bitstream_id
inner join bundle2bitstream b2b
  on b2b.bitstream_id = bit.bitstream_id
inner join metadatavalue bunmv
  on b2b.bundle_id = bunmv.resource_id and bunmv.resource_type_id = 1
  and bunmv.text_value = 'ORIGINAL'
inner join metadatafieldregistry bunmfr
  on bunmfr.metadata_field_id = bunmv.metdata_field_id
  and bunmfr.element = 'title' and bunmfr.qualifier is null
inner join item2bundle i2b
  on i2b.bundle_id = b.bundle_id
  and i2b.item_id=i.item_id
where exists (
  select 1 
  from resourcepolicy 
  where resource_type_id=0
  and bit.bitstream_id=resource_id
  and epersongroup_id = 0
  and (start_date is null or start_date <= current_date)
  and (end_date is null or start_date >= current_date)
)
EOF;
    self::addAuxField("BitRestricted", "Original Restricted", "({$bitRestricted})", "", false);

    $thumbRestricted = <<< EOF
select case when count(*) > 0 then 'Unrestricted' else 'Restricted' end
from bitstream bit
inner join bundle2bitstream b2b
  on b2b.bitstream_id = bit.bitstream_id
inner join bundle2bitstream b2b
  on b2b.bitstream_id = bit.bitstream_id
inner join metadatavalue bunmv
  on b2b.bundle_id = bunmv.resource_id and bunmv.resource_type_id = 1
  and bunmv.text_value = 'THUMBNAIL'
inner join metadatafieldregistry bunmfr
  on bunmfr.metadata_field_id = bunmv.metdata_field_id
  and bunmfr.element = 'title' and bunmfr.qualifier is null
inner join item2bundle i2b
  on i2b.bundle_id = b.bundle_id
  and i2b.item_id=i.item_id
where exists (
  select 1 
  from resourcepolicy 
  where resource_type_id=0
  and bit.bitstream_id=resource_id
  and epersongroup_id = 0
  and (start_date is null or start_date <= current_date)
  and (end_date is null or start_date >= current_date)
)
EOF;

    self::addAuxField("ThumbRestricted", "Thumbnail Restricted", "({$thumbRestricted})", "", false);
    
    $mime = <<< EOF
select array_to_string(array_agg(text(bfr.mimetype)), '<hr/>')
from bitstream bit
inner join bitstreamformatregistry bfr 
  on bit.bitstream_format_id = bfr.bitstream_format_id
inner join bundle2bitstream b2b
  on b2b.bitstream_id = bit.bitstream_id
inner join bundle2bitstream b2b
  on b2b.bitstream_id = bit.bitstream_id
inner join metadatavalue bunmv
  on b2b.bundle_id = bunmv.resource_id and bunmv.resource_type_id = 1
  and bunmv.text_value = 'ORIGINAL'
inner join metadatafieldregistry bunmfr
  on bunmfr.metadata_field_id = bunmv.metdata_field_id
  and bunmfr.element = 'title' and bunmfr.qualifier is null
inner join item2bundle i2b
  on i2b.bundle_id = b.bundle_id
  and i2b.item_id=i.item_id
EOF;
    self::addAuxField("Mime", "Bitstream Mime Time", "({$mime})", "", false);
    
    
    self::$SHOWARR = array();
    if (isset($_GET['show'])){
      foreach($_GET['show'] as $s) {
	    if (isset(self::$AUXQ[$s])){
		   self::$SHOWARR[$s] = self::$AUXQ[$s];
	    }
      }
    }
  }
  
  public static function addAuxField($name, $desc, $query, $dc, $isImg) {
  	  self::$AUXT[$name] = ($desc == "") ? $name : $desc;
  	  self::$AUXQ[$name] = $query;
  	  if ($dc != "") {
  	  	  self::$DC[$name] = $dc;
  	  }
  	  if ($isImg) {
  	  	  self::$IMGKEY[$name] = true;
  	  }  	  

      if (isset($_GET['show'])){
        foreach($_GET['show'] as $s) {
	      if (isset(self::$AUXQ[$s])){
		     self::$SHOWARR[$s] = self::$AUXQ[$s];
	      }
        }
      }
  }
  
  
  public static function getFieldByName($element, $qualifier) {
	return "(select array_to_string(array_agg(text_value),'<hr/>') " .
			"from metadatavalue mx " .
			"inner join metadatafieldregistry mfr on mx.metadata_field_id=mfr.metadata_field_id " .
			"and element='" . $element . "' and " .
  			(($qualifier == null) ?	"qualifier is null" : "qualifier='" . $qualifier . "'") .
			" and mx.resource_id=i.item_id and mx.resource_type_id=2)";
  }

  public static function getShowOptCb() {
  	$showopt = "";
	foreach(self::$AUXQ as $k => $v) {
	  $sel = isset(self::$SHOWARR[$k]) ? "checked" : "";
	  $edit = isset(self::$DC[$k]) ? "*" : "";
	  $title = self::getTitleAttr($k); 
	  $showopt .= "<li><input type='checkbox' {$title} value='{$k}' $sel name='show[]' id='show_{$k}'/>" .
	  		"<label {$title} for='show_{$k}'>{$k}{$edit}</label></li>";
	}
  	return <<< HERE
<span>Designate the fields to display for each item</span>
<ul>
{$showopt}
</ul>
HERE;
  }

  public static function  getShowOptToggle()  {
  	$showOpt = self::getShowOptCb();
  	return <<< HERE
  	<input id="soc" type="checkbox" onclick="$('#showopt').dialog('open')"/><label for="soc">Item Display Options</label>
  	<div id="showopt">{$showOpt}</div>
HERE;
  }

  public static function  getShowToolsToggle()  {
  	$showOpt = self::getShowOptCb();
  	return <<< HERE
  	<input id="soc" type="checkbox" onclick="$('#showopt').dialog('open')"/><label for="soc">Item Display Options</label>
  	<div id="showopt">{$showOpt}</div>
HERE;
  }
  
  public static function getTitleAttr($k) {
  	$title = isset(self::$AUXT[$k]) ? self::$AUXT[$k] : $k;
  	return  "title='{$title}'"; 
  }
}

?>