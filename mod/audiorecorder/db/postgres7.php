<?PHP

function audiorecorder_upgrade($oldversion) {
/// This function does anything necessary to upgrade 
/// older versions to match current functionality 

    global $CFG;

    if ($oldversion < 2006120200) {

       # Do something ...

    }

    return true;
}

?>
