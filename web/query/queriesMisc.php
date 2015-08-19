<?php

function initQueriesMisc() {
$m = <<< EOF
(select max(length(text_value)) from metadatavalue mx where mx.resource_id=i.item_id and mx.resource_type_id = 2)
EOF;
auxFields::addAuxField("maxmeta", "Longest metadata field", "{$m}", "", false);
$m = <<< EOF
      (select array_to_string(array_agg('...' || substring(text_value,'(.{0,10}[^[:ascii:]].{0,10})') || '...'),'<br/>') 
  			from metadatavalue mx 
  			inner join metadatafieldregistry mfr on mx.metadata_field_id=mfr.metadata_field_id 
  			 and mx.resource_id=i.item_id and mx.resource_type_id =2)
EOF;
auxFields::addAuxField("NonAscii", "Non-Ascii characters in metadata", "{$m}", "", false);
$m = <<< EOF
      (select array_to_string(array_agg('...' || substring(text_value,'(.{0,10}&#.{0,10})') || '...'),'<br/>') 
  			from metadatavalue mx 
  			inner join metadatafieldregistry mfr on mx.metadata_field_id=mfr.metadata_field_id 
  			 and mx.resource_id=i.item_id and mx.resource_type_id =2)
EOF;
auxFields::addAuxField("AmperPound", "Chacters escaped with Amper Pound", "{$m}", "", false);

$subq = <<< EOF
    and exists 
    (
      select 1
      from metadatavalue m 
      where m.resource_id = i.item_id and m.resource_type_id = 2
      and (text_value ~ '^.*[^ ]{50,50}.*$')
    ) 
EOF;
new query("itemCountWithLongMeta","Num Items with Long Unbreaking Metadata",$subq,"misc", new testValTrue(),array("Accession","URI")); 

$subq = <<< EOF
    and exists 
    (
      select 1
      from metadatavalue m 
      where m.resource_id = i.item_id and m.resource_type_id = 2
      and m.metadata_field_id = (
        select metadata_field_id from metadatafieldregistry mfr
        where mfr.element = 'description' and mfr.qualifier in ('','abstract')
      )
      and (text_value ~ '^.*(http://|https://|mailto:).*$')
    ) 
EOF;
new query("itemCountDescUrl","Num Items with URL in description or abstract",$subq,"misc", new testValTrue(),array("Accession","URI")); 

$subq = <<< EOF
    and exists 
    (
      select 1
      from metadatavalue m 
      where m.resource_id = i.item_id and m.resource_type_id = 2
      and m.metadata_field_id = (
        select metadata_field_id from metadatafieldregistry mfr
        where mfr.element = 'description' and mfr.qualifier = 'provenance'
      )
      and (text_value ~ '^.*No\. of bitstreams.*\.(PDF|pdf|DOC|doc|PPT|ppt|DOCX|docx|PPTX|pptx).*$')
    ) 
EOF;
new query("hasFullText","Has full text per provenance",$subq,"misc", new testValTrue(),array("Provenance")); 

$subq = <<< EOF
    and exists 
    (
      select 1
      from metadatavalue m 
      where m.resource_id = i.item_id and m.resource_type_id = 2
      and m.metadata_field_id = (
        select metadata_field_id from metadatafieldregistry mfr
        where mfr.element = 'description' and mfr.qualifier = 'provenance'
      )
      and (text_value !~ '^.*No\. of bitstreams.*\.(PDF|pdf|DOC|doc|PPT|ppt|DOCX|docx|PPTX|pptx).*$')
    ) 
EOF;
new query("hasNoFullText","Has no full text per provenance",$subq,"misc", new testValTrue(),array("Provenance")); 

$subq = <<< EOF
    and exists 
    (
      select 1
      from metadatavalue m 
      where m.resource_id = i.item_id and m.resource_type_id = 2
      and (length(text_value) > 6000)
    ) 
EOF;
new query("itemCountLongMeta","Num Items with long metadata",$subq,"misc", new testValZero(),array("Accession","maxmeta")); 

$subq = <<< EOF
    and exists 
    (
      select 1
      from metadatavalue m 
      where m.resource_id = i.item_id and m.resource_type_id = 2
      and text_value like '%&#%'
    ) 
EOF;
new query("hasAmperPound","Has &# in metadata",$subq,"misc", new testValZero(),array("AmperPound")); 

$subq = <<< EOF
    and exists 
    (
      select 1
      from metadatavalue m 
      where m.resource_id = i.item_id and m.resource_type_id = 2
      and text_value ~ '^.*[^[:ascii:]].*$'
    ) 
EOF;
new query("hasNonAsciiCurly","Has non-ascii in metadata (includes curly quotes)",$subq,"misc", new testValTrue(),array("NonAscii")); 

}

?>