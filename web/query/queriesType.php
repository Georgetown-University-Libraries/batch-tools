<?php

function initQueriesType() {
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
      inner join bitstreamformatregistry bfr on bit.bitstream_format_id = bfr.bitstream_format_id
        and bfr.mimetype like 'video/%'
    ) 
EOF;
new query("itemCountVideo","Num Video Items",$subq,"type", new testValZero(),array("Accession","Format")); 

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
      inner join bitstreamformatregistry bfr on bit.bitstream_format_id = bfr.bitstream_format_id
        and bfr.mimetype like 'text/html%'
    ) 
EOF;
new query("itemCountHtml","Num HTML Items",$subq,"type", new testValZero(),array("Accession","Format")); 

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
      inner join bundle2bitstream b2b
        on i2b.bundle_id = b2b.bundle_id
      inner join metadatavalue bitmv
        on b2b.bitstream_id = bitmv.resource_id and bitmv.resource_type_id = 0
        and bitmv.text_value ~ '.*\.zip$'
      inner join metadatafieldregistry bitmfr
        on bitm.metadata_field_id = bitmfr.metdata_field_id
        and bitmfr.element = 'title' and bitmfr.qualifier is null      inner join bitstream bit on bit.bitstream_id = b2b.bitstream_id
    ) 
EOF;
new query("itemCountZip","Num Zip files",$subq,"type", new testValTrue(),array("Accession","Format","OrigName")); 

}
?>