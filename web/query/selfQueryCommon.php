<?php
/*
User form for initiating the move of a collection to another community.  Note: in order to properly re-index the repository, 
DSpace will need to be taken offline after running this operation.
Author: Terry Brady, Georgetown University Libraries

License information is contained below.

Copyright (c) 2013, Georgetown University Libraries All rights reserved.

Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:

Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer. 
in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials 
provided with the distribution. THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, 
BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. 
IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES 
(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) 
HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) 
ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/
function initFields($CUSTOM) {

$sql = <<< EOF
select 
  mfr.metadata_field_id, 
  msr.short_id, 
  mfr.element, 
  mfr.qualifier, 
  (msr.short_id || '.' || mfr.element || case when mfr.qualifier is null then '' else '.' || mfr.qualifier end) as name
from metadatafieldregistry mfr
inner join metadataschemaregistry msr on msr.metadata_schema_id=mfr.metadata_schema_id
order by msr.short_id, mfr.element, mfr.qualifier;
EOF;

$dbh = $CUSTOM->getPdoDb();
$stmt = $dbh->prepare($sql);

$result = $stmt->execute(array());

$result = $stmt->fetchAll();

if (!$result) {
    print($sql);
    print_r($dbh->errorInfo());
    die("Error in SQL query");
}       

$mfields = array();
foreach ($result as $row) {
    $mfi = $row[0];
    $mfn = $row[4];
    $mfields[$mfi] = $mfn;
}

return $mfields;
}

function sel($val,$test) {
    return ($val == $test) ? 'selected' : '';
} 

function initFilters() {
	$FILTERS = array();
	$FILTERS['xpriv'] = array('name' => 'Exclude Private', 'sql' => " and i.discoverable is true");
	$FILTERS['xwithdrawn'] = array('name' => 'Exclude Withdrawn', 'sql' => " and i.withdrawn is false");

    $q = <<< EOF
    and not exists (
      select 1 
      from item2bundle i2b 
      inner join bundle b 
      on 
	    i2b.bundle_id = b.bundle_id 
	    and b.name = 'ORIGINAL' 
        and i.item_id = i2b.item_id
    )
EOF;
	$FILTERS['nooriginal'] = array(
		'name' => 'No Original', 
		'sql' => $q, 
    );
    
    $q = <<< EOF
    and exists (
      select 1 
      from item2bundle i2b 
      inner join bundle b 
      on 
	    i2b.bundle_id = b.bundle_id 
	    and b.name = 'ORIGINAL' 
        and i.item_id = i2b.item_id
    )
EOF;
	$FILTERS['hasoriginal'] = array(
		'name' => 'Has Original', 
		'sql' => $q,
    );

	$q = <<< EOF
    and exists (
      select 1 
      from item2bundle i2b 
      inner join bundle b 
      on 
	    i2b.bundle_id = b.bundle_id 
	    and b.name = 'ORIGINAL' 
        and i.item_id = i2b.item_id
      where (
	    select count(*) 
	    from bundle2bitstream b2b 
	    where b.bundle_id=b2b.bundle_id
      ) > 1
    )
EOF;
	$FILTERS['multoriginal'] = array(
		'name' => 'Has Multiple Originals', 
		'sql' => $q
    );

	$q = <<< EOF
    and exists 
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
	$FILTERS['unrestitem'] = array(
		'name' => 'Unrestricted Item', 
		'sql' => $q
    );
	
	$q = <<< EOF
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
	$FILTERS['restitem'] = array(
		'name' => 'Restricted/Embargo Item', 
		'sql' => $q
    );

	$q = <<< EOF
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
      where exists (
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
	$FILTERS['unrestbit'] = array(
		'name' => 'Unrestricted Bitstream', 
		'sql' => $q
    );
	
	$q = <<< EOF
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
	$FILTERS['restbit'] = array(
		'name' => 'Restricted Bitstream', 
		'sql' => $q
    );
	
	return $FILTERS; 
}

?>