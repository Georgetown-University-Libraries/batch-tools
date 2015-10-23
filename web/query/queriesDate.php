<?php

function initQueriesDate() {
$subq = <<< EOF
    and exists 
    (
      select 1
      from metadatavalue m 
      where m.resource_id = i.item_id and m.resource_type_id = 2
      and m.metadata_field_id = (
        select metadata_field_id from metadatafieldregistry mfr
        where mfr.element = 'date' and mfr.qualifier = 'created'
      )
       and text_value !~ '^((No [dD]ate)|(([0-9][0-9][0-9][0-9]|[0-9][0-9][0-9][0-9]-(0[1-9]|1[012])|[0-9][0-9][0-9][0-9]-(0[1-9]|1[012])-(31|30|[12][0-9]|0[1-9]))))$'
    ) 
EOF;

new query("itemCountWithCreate","Num Items with Invalid Creation Date",$subq,"date", new testValZero(),array("Accession","Create")); 

$subq = <<< EOF
    and not exists 
    (
      select 1
      from metadatavalue m 
      where m.resource_id = i.item_id and m.resource_type_id = 2
      and m.metadata_field_id = (
        select metadata_field_id from metadatafieldregistry mfr
        where mfr.element = 'date' and mfr.qualifier = 'created'
      )
     ) 
EOF;
new query("itemCountWithNoCreate","Num Items with No Creation Date",$subq,"date", new testValZero(),array("Accession")); 

$subq = <<< EOF
    and exists 
    (
      select 1
      from metadatavalue m 
      where m.resource_id = i.item_id and m.resource_type_id = 2
      and m.metadata_field_id = (
        select metadata_field_id from metadatafieldregistry mfr
        where mfr.element = 'date' and mfr.qualifier = 'issued'
      )
       and text_value !~ '^([0-9][0-9][0-9][0-9]|[0-9][0-9][0-9][0-9]-(0[1-9]|1[012])||[0-9][0-9][0-9][0-9]-(0[1-9]|1[012])-(31|30|[12][0-9]|0[1-9]))$'
    ) 
EOF;

new query("itemCountWithInvIssue","Num Items with Invalid Issue Date",$subq,"date", new testValZero(),array("Accession","Issue")); 

$subq = <<< EOF
    and 
    (
      (
        select count(*)
        from metadatavalue m 
      where m.resource_id = i.item_id and m.resource_type_id = 2
      and m.metadata_field_id = (
        select metadata_field_id from metadatafieldregistry mfr
        where mfr.element = 'date' and mfr.qualifier = 'created'
      )
      ) > 1
      or
      (
        select count(*)
        from metadatavalue m 
      where m.resource_id = i.item_id and m.resource_type_id = 2
      and m.metadata_field_id = (
        select metadata_field_id from metadatafieldregistry mfr
        where mfr.element = 'date' and mfr.qualifier = 'issued'
      )
      ) > 1
      or
      (
        select count(*)
        from metadatavalue m 
      where m.resource_id = i.item_id and m.resource_type_id = 2
      and m.metadata_field_id = (
        select metadata_field_id from metadatafieldregistry mfr
        where mfr.element = 'date' and mfr.qualifier = 'available'
      )
      ) > 1
      or
      (
        select count(*)
        from metadatavalue m 
      where m.resource_id = i.item_id and m.resource_type_id = 2
      and m.metadata_field_id = (
        select metadata_field_id from metadatafieldregistry mfr
        where mfr.element = 'date' and mfr.qualifier = 'accessioned'
      )
      ) > 1
    ) 
EOF;
new query("itemCountWithDupDate","Num Items with Duplicate Date",$subq,"date", new testValZero(),array("Accession","Create","Issue","Available")); 

$subq = <<< EOF
    and exists 
    (
      select 1
      from metadatavalue m 
      where m.resource_id = i.item_id and m.resource_type_id = 2
      and m.metadata_field_id = (
        select metadata_field_id from metadatafieldregistry mfr
        where mfr.element = 'date' and mfr.qualifier is null
      )
    ) 
EOF;
new query("itemCountWithUnqualDate","Num Items with Unqualified dc.date",$subq,"date", new testValZero(),array("Accession")); 
}
?>