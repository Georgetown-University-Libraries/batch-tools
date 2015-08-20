<?php

function initQueriesEmbargo() {

$subq = <<< EOF
  and i.discoverable = false
EOF;
new query("private","Private Item - Not Searchable",$subq,"embargo", new testValZero(),array("Accession","DocStream","EmbargoLift","BitRestricted", "ThumbRestricted","Private")); 

$subq = <<< EOF
    and not exists 
    (
	  select 1 
  	  from resourcepolicy 
  	  where resource_type_id=2
  	    and i.item_id=resource_id
  		and epersongroup_id = 0
  		and (start_date is null or start_date <= current_date)
  		and (end_date is null or start_date >= current_date)
    ) 
EOF;
new query("restrictedItem","Restricted Item Metadata - No Anonymous Access",$subq,"embargo", new testValZero(),array("Accession","DocStream","EmbargoLift","BitRestricted", "ThumbRestricted","Private")); 

$subq = <<< EOF
    and exists 
    (
      select 1
      from item2bundle i2b
      inner join metadatavalue bunmv
        on i2b.bundle_id = bunmv.resource_id and bunmv.resource_type_id = 1
        and bunmv.text_value = 'ORIGINAL'
        and i.item_id = i2b.item_id
      inner join metadatafieldregistry bunmfr
        on bunmfr.metadata_field_id = bunmv.metdata_field_id
        and bunmfr.element = 'title' and bunmfr.qualifier is null      
      inner join bundle2bitstream b2b on i2b.bundle_id = b2b.bundle_id
      inner join bitstream bit on bit.bitstream_id = b2b.bitstream_id
      where not exists (
		select 1 
  		from resourcepolicy 
  		where resource_type_id=0
  		and bit.bitstream_id=resource_id
  		and epersongroup_id = 0
  		and (start_date is null or start_date <= current_date)
  		and (end_date is null or start_date >= current_date)
      )
    ) 
EOF;
new query("restrictedOriginal","Restricted Original Bitstream - No Anonymous Access",$subq,"embargo", new testValTrue(),array("Accession","DocStream","EmbargoLift","BitRestricted", "ThumbRestricted","Private")); 

$subq = <<< EOF
    and exists 
    (
      select 1
      from item2bundle i2b
      inner join metadatavalue bunmv
        on i2b.bundle_id = bunmv.resource_id and bunmv.resource_type_id = 1
        and bunmv.text_value = 'ORIGINAL'
        and i.item_id = i2b.item_id
      inner join metadatafieldregistry bunmfr
        on bunmfr.metadata_field_id = bunmv.metdata_field_id
        and bunmfr.element = 'title' and bunmfr.qualifier is null      
      inner join bundle2bitstream b2b on i2b.bundle_id = b2b.bundle_id
      inner join bitstream bit on bit.bitstream_id = b2b.bitstream_id
      where exists (
		select 1 
  		from resourcepolicy 
  		where resource_type_id=0
  		and bit.bitstream_id=resource_id
  		and epersongroup_id != 0
  		and (start_date is null or start_date <= current_date)
  		and (end_date is null or start_date >= current_date)
      )
    ) 
EOF;
new query("specialAccess","Special Access Rule - Original Bitstream Accessible to a Specific Group",$subq,"embargo", new testValZero(),array("Accession","DocStream","EmbargoLift","BitRestricted", "ThumbRestricted","Private")); 

$subq = <<< EOF
    and exists 
    (
      select 1
      from item2bundle i2b
      inner join metadatavalue bunmv
        on i2b.bundle_id = bunmv.resource_id and bunmv.resource_type_id = 1
        and bunmv.text_value = 'THUMBNAIL'
        and i.item_id = i2b.item_id
      inner join metadatafieldregistry bunmfr
        on bunmfr.metadata_field_id = bunmv.metdata_field_id
        and bunmfr.element = 'title' and bunmfr.qualifier is null      
      inner join bundle2bitstream b2b on i2b.bundle_id = b2b.bundle_id
      inner join bitstream bit on bit.bitstream_id = b2b.bitstream_id
      where not exists (
		select 1 
  		from resourcepolicy 
  		where resource_type_id=0
  		and bit.bitstream_id=resource_id
  		and epersongroup_id = 0
  		and (start_date is null or start_date <= current_date)
  		and (end_date is null or start_date >= current_date)
      )
    ) 
EOF;
new query("restrictedThumbnail","Restricted Thumbnail - No Anonymous Access",$subq,"embargo", new testValTrue(),array("Accession","DocStream","EmbargoLift","BitRestricted", "ThumbRestricted","Private")); 

}
?>