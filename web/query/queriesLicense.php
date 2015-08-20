<?php

function initQueriesLicense() {
$subq = <<< EOF
    and not exists 
    (
      select 1
      from item2bundle i2b
      inner join metadatavalue bunmv
        on i2b.bundle_id = bunmv.resource_id and bunmv.resource_type_id = 1
        and bunmv.text_value = 'LICENSE'
        and i.item_id = i2b.item_id
      inner join metadatafieldregistry bunmfr
        on bunmfr.metadata_field_id = bunmv.metdata_field_id
        and bunmfr.element = 'title' and bunmfr.qualifier is null      
    ) 
EOF;
new query("itemCountWithoutLicense","Num Items without License",$subq,"license", new testValTrue(),array("Accession")); 

$subq = <<< EOF
    and exists 
    (
      select 1
      from item2bundle i2b
      inner join metadatavalue bunmv
        on i2b.bundle_id = bunmv.resource_id and bunmv.resource_type_id = 1
        and bunmv.text_value not in ('ORIGINAL', 'THUMBNAIL','TEXT')
        and i.item_id = i2b.item_id
      inner join metadatafieldregistry bunmfr
        on bunmfr.metadata_field_id = bunmv.metdata_field_id
        and bunmfr.element = 'title' and bunmfr.qualifier is null      
      inner join bundle2bitstream b2b on i2b.bundle_id = b2b.bundle_id
      inner join metadatavalue bitmv
        on b2b.bitstream_id = bitmv.resource_id and bitmv.resource_type_id = 0
        and bitmv.text_value != 'license.txt'
      inner join metadatafieldregistry bitmfr
        on bitm.metadata_field_id = bitmfr.metdata_field_id
        and bitmfr.element = 'title' and bitmfr.qualifier is null      inner join bitstream bit on bit.bitstream_id = b2b.bitstream_id
    ) 
EOF;
new query("itemCountLicense","Num Documentation Items",$subq,"license", new testValTrue(),array("Accession","Format","OtherName")); 

}
?>