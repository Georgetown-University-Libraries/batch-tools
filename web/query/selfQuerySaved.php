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

function save(&$saved, $name, $desc, $permalink) {
    $saved[$name] = array("desc" => $desc, "permalink" => $permalink);
}

function initSavedSearches() {
    $saved = array();
    save($saved, "Mult Original 2000", "...", "saved=&savename=2015-01-26_15%3A40%3A00&savedesc=&coll=&comm=&field%5B%5D=&op%5B%5D=exists&val%5B%5D=&filter%5B%5D=multoriginal&offset=0&MAX=2000");
    save($saved, "Mult Original 20", "2...", "saved=&savename=2015-01-26_15%3A40%3A00&savedesc=&coll=&comm=&field%5B%5D=&op%5B%5D=exists&val%5B%5D=&filter%5B%5D=multoriginal&offset=0&MAX=20");
    save($saved, "Mult Original 10", "3...", "saved=&savename=2015-01-26_15%3A40%3A00&savedesc=&coll=&comm=&field%5B%5D=&op%5B%5D=exists&val%5B%5D=&filter%5B%5D=multoriginal&offset=0&MAX=10");
    return $saved;
}

?>