<?php
/*
 * Created on 2007-12-9
 *
 * Author:tangwei
 * Project: audiorecorder
 */
 function xmldb_audiorecorder_upgrade($oldversion=0) {

    global $CFG, $db;

    $result = true;
    if ($result && $oldversion < 2008042200) {
          /// Define field format to be added to data_comments
        $table = new XMLDBTable('audiorecorder');
        $field = new XMLDBField('audiorecordertype');
        $field->setAttributes(XMLDB_TYPE_CHAR, '50' , null, XMLDB_NOTNULL, null, null, null, 'upload', 'intro');
        $result = $result && add_field($table, $field);
        //add var1
        $field = new XMLDBField('var1');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '10' , null, null, null, null, null, '3', 'audiorecordertype');
        $result = $result && add_field($table, $field);
        //add var2
        $field = new XMLDBField('var2');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '10' , null, null, null, null, null, '0', 'var1');
        $result = $result && add_field($table, $field);
        //add var3
        $field = new XMLDBField('var3');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '10' , null, null, null, null, null, '0', 'var2');
        $result = $result && add_field($table, $field);
        
        //we will need to upgrade all audiorecordertype to upload
        if ($ars = get_records('audiorecorder')) {
            foreach ($ars as $ar) {
                if ($ar->audiorecordertype == 'uploadsingle' || $ar->audiorecordertype=='') {
                    $ar->audiorecordertype = 'upload';
                    update_record('audiorecorder',$ar);
                }
            }
        }

    }
    return $result;
 }
?>
