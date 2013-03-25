<?php  //$Id: upgrade.php,v 1.2.2.6 2010/04/17 14:42:33 ulcc Exp $



// This file keeps track of upgrades to 

// the target module

//

// Sometimes, changes between versions involve

// alterations to database structures and other

// major things that may break installations.

//

// The upgrade function in this file will attempt

// to perform all the necessary actions to upgrade

// your older installtion to the current version.

//

// If there's something it cannot do itself, it

// will tell you what you need to do.

//

// The commands in here will all be database-neutral,

// using the functions defined in lib/ddllib.php



function xmldb_ilptarget_upgrade($oldversion=0) {

    global $CFG, $THEME, $db;

    $result = true;
	
	if ($result && $oldversion < 2008052911) {

    /// Define field name to be added to ilptarget_posts
        $table = new XMLDBTable('ilptarget_posts');
        $field = new XMLDBField('category');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0', 'targetset');

    /// Launch add field name
        $result = $result && add_field($table, $field);
    }
	
	if ($result && $oldversion < 2008052910) {

    /// Rename field comment on table ilptarget_comments to commentpost
        $table = new XMLDBTable('ilptarget_comments');
        $field = new XMLDBField('comment');
        $field->setAttributes(XMLDB_TYPE_TEXT, 'small', null, XMLDB_NOTNULL, null, null, null, null, 'modified');

    /// Launch rename field commentpost
        $result = $result && rename_field($table, $field, 'commentpost');
    }
	
	if ($result && $oldversion < 2008052906) {

    /// Define field name to be added to ilptarget_posts
        $table = new XMLDBTable('ilptarget_posts');
        $field = new XMLDBField('name');
        $field->setAttributes(XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, null, 'data2');

    /// Launch add field name
        $result = $result && add_field($table, $field);
    }
	
	if ($result && $oldversion < 2008052904) {
    /// Define field courserelated to be added to ilptarget_posts
        $table = new XMLDBTable('ilptarget_posts');
        $field = new XMLDBField('courserelated');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0', 'course');

    /// Launch add field courserelated
        $result = $result && add_field($table, $field);
		
		$field = new XMLDBField('targetcourse');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0', 'courserelated');

    /// Launch add field targetcourse
        $result = $result && add_field($table, $field);
    }
	
	if ($result && $oldversion < 2008052902) {
    /// Define field course to be added to ilptarget_posts
        $table = new XMLDBTable('ilptarget_posts');
        $field = new XMLDBField('course');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, null, '0', 'setbyuserid');

    /// Launch add field course
        $result = $result && add_field($table, $field);
    }

    return $result;

}



?>

