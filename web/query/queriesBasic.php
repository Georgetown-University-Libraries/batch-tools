<?php

function initQueriesBasic() {
$subq = "";
new query("itemCount","Num Items",$subq,"head basic text license type image meta mod date embargo", new testValPos(),array("Accession")); 

$subq = <<< EOF
    and exists 
    (
      select 1
      from item2bundle i2b
      inner join bundle b 
        on i2b.bundle_id = b.bundle_id
        and b.name = 'ORIGINAL'
        and i.item_id = i2b.item_id
      inner join bundle2bitstream b2b on b.bundle_id = b2b.bundle_id
      inner join bitstream bit on bit.bitstream_id = b2b.bitstream_id
      inner join bitstreamformatregistry bfr on bit.bitstream_format_id = bfr.bitstream_format_id
        and bfr.mimetype in (
             'text/plain',
             'application/pdf',
             'text/html',
             'application/msword',
             'text/xml',
	         'application/msword',
             'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
             'application/vnd.ms-powerpoint',
	         'application/vnd.openxmlformats-officedocument.presentationml.presentation',
             'application/vnd.ms-excel',
             'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
       )
    ) 
EOF;
new query("itemCountDoc","Num Document Items",$subq,"head basic text image", new testValTrue(),array("Accession","Creator")); 


$subq = <<< EOF
    and exists 
    (
      select 1
      from item2bundle i2b
      inner join bundle b 
        on i2b.bundle_id = b.bundle_id
        and b.name = 'ORIGINAL'
        and i.item_id = i2b.item_id
      inner join bundle2bitstream b2b on b.bundle_id = b2b.bundle_id
      inner join bitstream bit on bit.bitstream_id = b2b.bitstream_id
      inner join bitstreamformatregistry bfr on bit.bitstream_format_id = bfr.bitstream_format_id
        and bfr.mimetype in (
             'image/jp2',
             'image/jpeg'
      )
    ) 
EOF;
new query("itemCountSuppImage","Num Supported Image Items",$subq,"basic image", new testValTrue(),array("Accession","GenThumb")); 

$subq = <<< EOF
    and exists 
    (
      select 1
      from item2bundle i2b
      inner join bundle b 
        on i2b.bundle_id = b.bundle_id
        and b.name = 'ORIGINAL'
        and i.item_id = i2b.item_id
      inner join bundle2bitstream b2b on b.bundle_id = b2b.bundle_id
      inner join bitstream bit on bit.bitstream_id = b2b.bitstream_id
      inner join bitstreamformatregistry bfr on bit.bitstream_format_id = bfr.bitstream_format_id
        and bfr.mimetype like 'image/%' and bfr.mimetype not in (
             'image/jp2',
             'image/jpeg'
      )
    ) 
EOF;
new query("itemCountUnsuppImage","Num Unsupported Image Items",$subq,"basic type image", new testValZero(),array("Accession","GenThumb")); 

$subq = <<< EOF
    and exists 
    (
      select 1
      from item2bundle i2b
      inner join bundle b 
        on i2b.bundle_id = b.bundle_id
        and b.name = 'ORIGINAL'
        and i.item_id = i2b.item_id
      inner join bundle2bitstream b2b on b.bundle_id = b2b.bundle_id
      inner join bitstream bit on bit.bitstream_id = b2b.bitstream_id
      inner join bitstreamformatregistry bfr on bit.bitstream_format_id = bfr.bitstream_format_id
        and not (
          bfr.mimetype like 'video/%' or 
          bfr.mimetype like 'image/%' or 
          bfr.mimetype in (
             'text/plain',
             'application/pdf',
             'text/html',
             'application/msword',
             'text/xml',
	         'application/msword',
             'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
             'application/vnd.ms-powerpoint',
	         'application/vnd.openxmlformats-officedocument.presentationml.presentation',
             'application/vnd.ms-excel',
             'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
         )
       )
    ) 
EOF;
new query("itemCountOther","Num Other Items",$subq,"basic type", new testValZero(),array("Accession","Format","OrigName")); 

$subq = <<< EOF
    and exists 
    (
      select 1
      from item2bundle i2b
      inner join bundle b 
        on i2b.bundle_id = b.bundle_id
        and b.name = 'ORIGINAL'
        and i.item_id = i2b.item_id
      inner join bundle2bitstream b2b on b.bundle_id = b2b.bundle_id
      inner join bitstream bit on bit.bitstream_id = b2b.bitstream_id
        and bit.size_bytes > 10000000
    ) 
EOF;
new query("largeOrig","Large Original",$subq,"basic", new testValZero(),array("SizeMB","Format")); 


$subq = <<< EOF
    and not exists 
    (
      select 1
      from item2bundle i2b
      inner join bundle b 
        on i2b.bundle_id = b.bundle_id
        and b.name = 'ORIGINAL'
        and i.item_id = i2b.item_id
    ) 
    and not exists 
    (
      select 1
      from metadatavalue m 
      where m.item_id = i.item_id
      and m.metadata_field_id = (
        select metadata_field_id from metadatafieldregistry mfr
        where mfr.element = 'relation' and mfr.qualifier = 'uri'
      )
    ) 
EOF;
new query("itemCountWithoutOriginal","Num Items without Original or Relation URI",$subq,"basic", new testValZero(),array("Accession")); 

$subq = <<< EOF
    and exists 
    (
      select 1
      from metadatavalue m 
      where m.item_id = i.item_id
      and m.metadata_field_id = (
        select metadata_field_id from metadatafieldregistry mfr
        where mfr.element = 'relation' and mfr.qualifier = 'uri'
      )
    ) 
EOF;
new query("itemCountWithRelationURI","Num Items with Relation URI",$subq,"basic", new testValTrue(),array("Accession")); 

$subq = <<< EOF
    and exists 
    (
      select 1
      from item2bundle i2b
      inner join bundle b 
        on i2b.bundle_id = b.bundle_id
        and b.name = 'ORIGINAL'
        and i.item_id = i2b.item_id
      where (
        select count(*)
        from bundle2bitstream b2b
        inner join bitstream bit on b2b.bitstream_id = bit.bitstream_id
        where b2b.bundle_id = b.bundle_id 
      ) > 1
    ) 
EOF;
new query("itemCountWithMultOriginal","Num Items with Multiple Original",$subq,"basic", new testValZero(),array("Accession","OrigName","GenThumb")); 

$subq = <<< EOF
    and exists 
    (
      select 1
      from item2bundle i2b
      inner join bundle b 
        on i2b.bundle_id = b.bundle_id
        and b.name = 'TEXT'
        and i.item_id = i2b.item_id
      where (
        select count(*)
        from bundle2bitstream b2b
        inner join bitstream bit on b2b.bitstream_id = bit.bitstream_id
        where b2b.bundle_id = b.bundle_id 
      ) > 1
    ) 
EOF;
new query("itemCountWithMultText","Num Items with Multiple Text Streams",$subq,"basic", new testValZero(),array("Accession","Text")); 

$subq = <<< EOF
  and i.in_archive != true
EOF;
new query("withdrawn","Withdrawn Item",$subq,"basic", new testValZero(),array("Accession","Withdrawn")); 

}
?>