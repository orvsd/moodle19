<?PHP
///////////////////////////////////////////////////////////////////////////
//                                                                       //
// Moodle configuration file                                             //
//                                                                       //
// This file should be renamed "config.php" in the top-level directory   //
//                                                                       //
///////////////////////////////////////////////////////////////////////////
//                                                                       //
// NOTICE OF COPYRIGHT                                                   //
//                                                                       //
// Moodle - Modular Object-Oriented Dynamic Learning Environment         //
//          http://moodle.org                                            //
//                                                                       //
// Copyright (C) 1999 onwards  Martin Dougiamas  http://moodle.com       //
//                                                                       //
// This program is free software; you can redistribute it and/or modify  //
// it under the terms of the GNU General Public License as published by  //
// the Free Software Foundation; either version 3 of the License, or     //
// (at your option) any later version.                                   //
//                                                                       //
// This program is distributed in the hope that it will be useful,       //
// but WITHOUT ANY WARRANTY; without even the implied warranty of        //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         //
// GNU General Public License for more details:                          //
//                                                                       //
//          http://www.gnu.org/copyleft/gpl.html                         //
//                                                                       //
///////////////////////////////////////////////////////////////////////////
unset($CFG);  // Ignore this line
global $CFG;  // This is necessary here for PHPUnit execution
$CFG = new stdClass();

//=========================================================================
// 1. ORVSD CONFIG
//=========================================================================
// Include relevant configuration from glusterfs mount.
require_once('/data/moodledata/' . $_SERVER['USER'] . '/moodle19/' . $_SERVER['FQDN'] . '/config.php');

// Enable when using external SSL appliance for performance reasons.
// Please note that site may be accessible via https: or https:, but not both!
$CFG->sslproxy = true;

// Now you need to tell Moodle where it is located. Specify the full
// web address to where moodle has been installed.
$CFG->wwwroot   = 'https://' . $_SERVER['FQDN'];
// ORVSD fix for Moodle 1.9 to allow sslproxy termination.
if (php_sapi_name() != 'cli') $_SERVER[SERVER_PORT] = 443;

$CFG->dirroot   = '/var/www/' . $_SERVER['USER'] . '/moodle19/' . $_SERVER['FQDN'] . '/moodle';
$CFG->dataroot  = '/data/moodledata/' . $_SERVER['USER'] . '/moodle19/' . $_SERVER['FQDN'];
$CFG->directorypermissions = 02770;

//=========================================================================
// ALL DONE!  To continue installation, visit your main page with a browser
//=========================================================================

if ($CFG->wwwroot == 'http://example.com/moodle') {
    echo "<p>Error detected in configuration file</p>";
    echo "<p>Your server address can not be: \$CFG->wwwroot = 'http://example.com/moodle';</p>";
    die;
}

if (file_exists("$CFG->dirroot/lib/setup.php"))  {       // Do not edit
    include_once("$CFG->dirroot/lib/setup.php");
} else {
    if ($CFG->dirroot == dirname(__FILE__)) {
        echo "<p>Could not find this file: $CFG->dirroot/lib/setup.php</p>";
        echo "<p>Are you sure all your files have been uploaded?</p>";
    } else {
        echo "<p>Error detected in config.php</p>";
        echo "<p>Error in: \$CFG->dirroot = '$CFG->dirroot';</p>";
        echo "<p>Try this: \$CFG->dirroot = '".dirname(__FILE__)."';</p>";
    }
    die;
}
// MAKE SURE WHEN YOU EDIT THIS FILE THAT THERE ARE NO SPACES, BLANK LINES,
// RETURNS, OR ANYTHING ELSE AFTER THE TWO CHARACTERS ON THE NEXT LINE.
?>
