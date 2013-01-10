<?php

function xmldb_local_upgrade($oldversion) {
    global $CFG, $db;
 
    $result = true;
 
    if ($result && $oldversion < 2012052900) {
        $result = $result && install_from_xmldb_file(dirname(__FILE__).'/install.xml');
    }
    return $result;
}
