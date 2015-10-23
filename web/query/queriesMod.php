<?php

function initQueriesMod() {
$subq = <<< EOF
    and exists 
    (
      select 1
      from metadatavalue m 
      where m.resource_id = i.item_id and m.resource_type_id = 2
      and m.metadata_field_id = (
        select metadata_field_id from metadatafieldregistry mfr
        where mfr.element = 'date' and mfr.qualifier = 'accessioned'
      )
     and to_date(text_value, 'YYYY-MM-DD') > current_date - interval '1 day'
    ) 
EOF;
new query("itemLast1day","Mod last 1 day",$subq,"mod", new testValTrue(),array("Accession")); 

$subq = <<< EOF
    and exists 
    (
      select 1
      from metadatavalue m 
      where m.resource_id = i.item_id and m.resource_type_id = 2
      and m.metadata_field_id = (
        select metadata_field_id from metadatafieldregistry mfr
        where mfr.element = 'date' and mfr.qualifier = 'accessioned'
      )
      and to_date(text_value, 'YYYY-MM-DD') > current_date - interval '7 day'
    ) 
EOF;
new query("itemLast7day","Mod last 7 days",$subq,"mod", new testValTrue(),array("Accession")); 

$subq = <<< EOF
    and exists 
    (
      select 1
      from metadatavalue m 
      where m.resource_id = i.item_id and m.resource_type_id = 2
      and m.metadata_field_id = (
        select metadata_field_id from metadatafieldregistry mfr
        where mfr.element = 'date' and mfr.qualifier = 'accessioned'
      )
      and to_date(text_value, 'YYYY-MM-DD') > current_date - interval '30 day'
    ) 
EOF;
new query("itemLast30day","Mod last 30 days",$subq,"mod", new testValTrue(),array("Accession")); 

$subq = <<< EOF
    and exists 
    (
      select 1
      from metadatavalue m 
      where m.resource_id = i.item_id and m.resource_type_id = 2
      and m.metadata_field_id = (
        select metadata_field_id from metadatafieldregistry mfr
        where mfr.element = 'date' and mfr.qualifier = 'accessioned'
      )
      and to_date(text_value, 'YYYY-MM-DD') > current_date - interval '60 day'
    ) 
EOF;
new query("itemLast60day","Mod last 60 days",$subq,"mod", new testValTrue(),array("Accession")); 
}
?>