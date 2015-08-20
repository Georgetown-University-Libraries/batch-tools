<?php

function initQueriesImage() {
$subq = <<< EOF
    and exists 
    (
      select 1
      from item2bundle i2b
      inner join metadatavalue bunmv
        on b2b.bundle_id = bunmv.resource_id and bunmv.resource_type_id = 1
        and bunmv.text_value = 'ORIGINAL'
        and i.item_id = i2b.item_id
      inner join metadatafieldregistry bunmfr
        on bunmfr.metadata_field_id = bunmv.metdata_field_id
        and bunmfr.element = 'title' and bunmfr.qualifier is null      
    ) 
    and not exists 
    (
      select 1
      from item2bundle i2b
      inner join metadatavalue bunmv
        on b2b.bundle_id = bunmv.resource_id and bunmv.resource_type_id = 1
        and bunmv.text_value = 'THUMBNAIL'
        and i.item_id = i2b.item_id
      inner join metadatafieldregistry bunmfr
        on bunmfr.metadata_field_id = bunmv.metdata_field_id
        and bunmfr.element = 'title' and bunmfr.qualifier is null      
    ) 
EOF;
new query("itemCountWithoutThumbnail","Num Items with Original without Thumbnail",$subq,"image", new testValZero(),array("Accession")); 

$subq = <<< EOF
    and exists 
    (
      select 1
      from item2bundle i2b
      inner join metadatavalue bunmv
        on b2b.bundle_id = bunmv.resource_id and bunmv.resource_type_id = 1
        and bunmv.text_value = 'ORIGINAL'
        and i.item_id = i2b.item_id
      inner join metadatafieldregistry bunmfr
        on bunmfr.metadata_field_id = bunmv.metdata_field_id
        and bunmfr.element = 'title' and bunmfr.qualifier is null      
      inner join bundle2bitstream b2b
        on b2b.bundle_id=b.bundle_id
      inner join bitstream bit
        on bit.bitstream_id = b2b.bitstream_id 
    ) 
EOF;
new query("itemCountWithOriginal","Num Items with Original",$subq,"image", new testValTrue(),array("Accession")); 

$subq = <<< EOF
    and exists 
    (
      select 1
      from item2bundle i2b
      inner join metadatavalue bunmv
        on b2b.bundle_id = bunmv.resource_id and bunmv.resource_type_id = 1
        and bunmv.text_value = 'THUMBNAIL'
        and i.item_id = i2b.item_id
      inner join metadatafieldregistry bunmfr
        on bunmfr.metadata_field_id = bunmv.metdata_field_id
        and bunmfr.element = 'title' and bunmfr.qualifier is null      
      inner join bundle2bitstream b2b
        on b2b.bundle_id=b.bundle_id
      inner join bitstream bit
        on bit.bitstream_id = b2b.bitstream_id 
    ) 
EOF;
new query("itemCountWithThumbnail","Num Items with Thumbnail",$subq,"image", new testValTrue(),array("Accession")); 


$subq = <<< EOF
    and exists 
    (
      select 1
      from item2bundle i2b
      inner join metadatavalue bunmv
        on b2b.bundle_id = bunmv.resource_id and bunmv.resource_type_id = 1
        and bunmv.text_value = 'THUMBNAIL'
        and i.item_id = i2b.item_id
      inner join metadatafieldregistry bunmfr
        on bunmfr.metadata_field_id = bunmv.metdata_field_id
        and bunmfr.element = 'title' and bunmfr.qualifier is null      
      where (
        select count(*)
        from bundle2bitstream b2b
        inner join bitstream bit on b2b.bitstream_id = bit.bitstream_id
        where b2b.bundle_id = b.bundle_id 
      ) > 1
    ) 
EOF;
new query("itemCountWithMultThumbnail","Num Items with Multiple Thumbnail",$subq,"image", new testValZero(),array("Accession","GenThumb")); 

$subq = <<< EOF
    and exists 
    (
      select 1
      from item2bundle i2b
      inner join metadatavalue bunmv
        on b2b.bundle_id = bunmv.resource_id and bunmv.resource_type_id = 1
        and bunmv.text_value = 'THUMBNAIL'
        and i.item_id = i2b.item_id
      inner join metadatafieldregistry bunmfr
        on bunmfr.metadata_field_id = bunmv.metdata_field_id
        and bunmfr.element = 'title' and bunmfr.qualifier is null      
      inner join bundle2bitstream b2b on b2b.bundle_id = b.bundle_id
      inner join bitstream bit on b2b.bitstream_id = bit.bitstream_id
        and bit.size_bytes < 400
    ) 
EOF;
new query("itemCountWithTinyThumbnail","Num Items with Invalid Thumbnail (Too Small)",$subq,"image", new testValZero(),array("Accession","GenThumb")); 

$subq = <<< EOF
    and exists 
    (
      select 1
      from item2bundle i2b
      inner join metadatavalue bunmv
        on b2b.bundle_id = bunmv.resource_id and bunmv.resource_type_id = 1
        and bunmv.text_value = 'THUMBNAIL'
        and i.item_id = i2b.item_id
      inner join metadatafieldregistry bunmfr
        on bunmfr.metadata_field_id = bunmv.metdata_field_id
        and bunmfr.element = 'title' and bunmfr.qualifier is null      
      inner join bundle2bitstream b2b on b2b.bundle_id = b.bundle_id
      inner join bitstream bit on b2b.bitstream_id = bit.bitstream_id
        and bit.name != (
          select bit2.name || '.jpg'
          from bitstream bit2
          inner join bundle2bitstream b2b2 on bit2.bitstream_id = b2b2.bitstream_id
          inner join bundle b2 on b2b2.bundle_id=b2.bundle_id and b2.name = 'ORIGINAL'
          inner join item2bundle i2b2 on i2b2.bundle_id=b2.bundle_id and i2b2.item_id = i.item_id
          limit 1
        ) 
    ) 
    and exists 
    (
      select 1
      from item2bundle i2b
      inner join metadatavalue bunmv
        on b2b.bundle_id = bunmv.resource_id and bunmv.resource_type_id = 1
        and bunmv.text_value = 'THUMBNAIL'
        and i.item_id = i2b.item_id
      inner join metadatafieldregistry bunmfr
        on bunmfr.metadata_field_id = bunmv.metdata_field_id
        and bunmfr.element = 'title' and bunmfr.qualifier is null      
      where (
        select count(*)
        from bundle2bitstream b2b
        inner join bitstream bit on b2b.bitstream_id = bit.bitstream_id
        where b2b.bundle_id = b.bundle_id 
      ) = 1
    ) 
EOF;
new query("itemCountWithInvalidThumbnailName","Num Items with Invalid Thumbnail Name",$subq,"image", new testValZero(),array("Accession","OrigName","ThumbName","GenThumb")); 
}
?>