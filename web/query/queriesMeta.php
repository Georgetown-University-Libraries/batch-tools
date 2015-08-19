<?php

function initQueriesMeta() {

$subq = <<< EOF
    and not exists 
    (
      select 1
      from metadatavalue m 
      where m.resource_id = i.item_id and m.resource_type_id = 2
      and m.metadata_field_id = (
        select metadata_field_id from metadatafieldregistry mfr
        where mfr.element = 'creator'
      )
    ) 
EOF;
new query("itemCountWithNoCreator","Num Items with No dc.creator",$subq,"meta", new testValZero(),array("Accession","Creator","Author")); 

$subq = <<< EOF
    and not exists 
    (
      select 1
      from metadatavalue m 
      where m.resource_id = i.item_id and m.resource_type_id = 2
      and m.metadata_field_id = (
        select metadata_field_id from metadatafieldregistry mfr
        where mfr.element = 'contributor' and mfr.qualifier = 'author'
      )
    ) 
EOF;
new query("itemCountWithNoContribAuthor","Num Items with No dc.contributor.author",$subq,"meta", new testValZero(),array("Accession","Creator","Author")); 

$subq = <<< EOF
    and exists 
    (
      select 1
      from metadatavalue m 
      where m.resource_id = i.item_id and m.resource_type_id = 2
      and m.metadata_field_id = (
        select metadata_field_id from metadatafieldregistry mfr
        where mfr.element = 'contributor' and mfr.qualifier = 'other'
      )
    ) 
EOF;
new query("itemCountWithNoContribOther","Num Items with dc.contributor.other",$subq,"meta", new testValTrue(),array("Accession")); 


$subq = <<< EOF
    and not exists 
    (
      select 1
      from metadatavalue m 
      where m.resource_id = i.item_id and m.resource_type_id = 2
      and m.metadata_field_id = (
        select metadata_field_id from metadatafieldregistry mfr
        where mfr.element = 'creator'
      )
    ) 
    and not exists 
    (
      select 1
      from metadatavalue m 
      where m.resource_id = i.item_id and m.resource_type_id = 2
      and m.metadata_field_id = (
        select metadata_field_id from metadatafieldregistry mfr
        where mfr.element = 'contributor' and mfr.qualifier = 'author'
      )
    ) 
EOF;
new query("itemCountWithNeitherAuthor","Num Items with Neither Author Field",$subq,"meta", new testValZero(),array("Accession","Creator","Author")); 

$subq = <<< EOF
    and not exists 
    (
      select 1
      from metadatavalue m 
      where m.resource_id = i.item_id and m.resource_type_id = 2
      and m.metadata_field_id = (
        select metadata_field_id from metadatafieldregistry mfr
        where mfr.element = 'title' and mfr.qualifier is null
      )
    ) 
EOF;
new query("itemCountWithNoTitle","Num Items with No Title",$subq,"meta", new testValZero(),array("Accession")); 

$subq = <<< EOF
    and not exists 
    (
      select 1
      from metadatavalue m 
      where m.resource_id = i.item_id and m.resource_type_id = 2
      and m.metadata_field_id = (
        select metadata_field_id from metadatafieldregistry mfr
        where mfr.element = 'identifier' and mfr.qualifier = 'uri'
      )
    ) 
EOF;
new query("itemCountWithNoIdent","Num Items with No URI",$subq,"meta", new testValZero(),array("Accession")); 

$subq = <<< EOF
    and not exists 
    (
      select 1
      from metadatavalue m 
      where m.resource_id = i.item_id and m.resource_type_id = 2
      and m.metadata_field_id = (
        select metadata_field_id from metadatafieldregistry mfr
        where mfr.element = 'publisher'
      )
    ) 
EOF;
new query("itemCountWithNoPub","Num Items with No Publisher",$subq,"meta", new testValTrue(),array("Accession")); 

$subq = <<< EOF
    and not exists 
    (
      select 1
      from metadatavalue m 
      where m.resource_id = i.item_id and m.resource_type_id = 2
      and m.metadata_field_id = (
        select metadata_field_id from metadatafieldregistry mfr
        where mfr.element = 'subject' and mfr.qualifier is null
      )
    ) 
EOF;
new query("itemCountWithNoSubject","Num Items with No Subject",$subq,"meta", new testValTrue(),array("Accession")); 

$subq = <<< EOF
    and exists 
    (
      select 1
      from metadatavalue m 
      where m.resource_id = i.item_id and m.resource_type_id = 2
      and m.metadata_field_id = (
        select metadata_field_id from metadatafieldregistry mfr
        where mfr.element = 'subject' and mfr.qualifier is null
      )
      and text_value like '%;%'
    ) 
EOF;
new query("itemCountWithCompoundSubject","Num Items with Compound Subject",$subq,"meta", new testValZero(),array("Accession","Subject")); 

$subq = <<< EOF
    and exists 
    (
      select 1
      from metadatavalue m 
      where m.resource_id = i.item_id and m.resource_type_id = 2
      and (text_value is null or text_value = '')
    ) 
EOF;
new query("itemCountWithEmptyMeta","Num Items with Empty Metadata Field",$subq,"meta", new testValZero(),array("Accession")); 

$subq = <<< EOF
    and 
    (
      select count(*)
      from metadatavalue m 
      where m.resource_id = i.item_id and m.resource_type_id = 2
      and m.metadata_field_id = (
        select metadata_field_id from metadatafieldregistry mfr
        where mfr.element = 'description' and mfr.qualifier is null
      )
    ) > 1 
EOF;
new query("itemMultDesc","Num Items with Mult Description",$subq,"meta", new testValZero(),array("Accession")); 

$subq = <<< EOF
    and 
    (
      select count(*)
      from metadatavalue m 
      where m.resource_id = i.item_id and m.resource_type_id = 2
      and m.metadata_field_id = (
        select metadata_field_id from metadatafieldregistry mfr
        where mfr.element = 'identifier' and mfr.qualifier = 'uri'
      )
    ) > 1 
EOF;
new query("itemMultUri","Num Items with Mult Identifier URI",$subq,"meta", new testValZero(),array("Accession","URI")); 

$subq = <<< EOF
    and exists
    (
      select 1
      from metadatavalue m 
      where m.resource_id = i.item_id and m.resource_type_id = 2
      and m.metadata_field_id = (
        select metadata_field_id from metadatafieldregistry mfr
        where mfr.element = 'identifier' and mfr.qualifier = 'bibliographicCitation'
      )
    ) 
EOF;
new query("itemCountWithBiblioCite","Num Items with Bibliographic Citation",$subq,"meta", new testValZero(),array("Accession","BibliographicCitation")); 


}
?>