<?php

function xmldb_local_upgrade($oldversion) {
    global $CFG, $db;
 
    $result = true;
 
    if ($result && $oldversion < 2012120323) {
        $result = $result && install_from_xmldb_file(dirname(__FILE__).'/install.xml');
    }
  	require_once("$CFG->dirroot/local/lib.php");

    siteinfo_init_db();
    return $result;
}
