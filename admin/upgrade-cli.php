<?php
/// This script looks through all the module directories for cron.php files
/// and runs them.  These files can contain cleanup functions, email functions
/// or anything that needs to be run on a regular basis.
///
/// This file is best run from cron on the host system (ie outside PHP).
/// The script can either be invoked via the web server or via a standalone
/// version of PHP compiled for CGI.
///
/// eg   wget -q -O /dev/null 'http://moodle.somewhere.edu/admin/cron.php'
/// or   php /web/moodle/admin/cron.php 
    set_time_limit(0);
    $starttime = microtime();

/// The following is a hack necessary to allow this script to work well 
/// from the command line.

    define('FULLME', 'cron');


/// Do not set moodle cookie because we do not need it here, it is better to emulate session
    $nomoodlecookie = true;

/// The current directory in PHP version 4.3.0 and above isn't necessarily the
/// directory of the script when run from the command line. The require_once()
/// would fail, so we'll have to chdir()

    if (!isset($_SERVER['REMOTE_ADDR']) && isset($_SERVER['argv'][0])) {
        chdir(dirname($_SERVER['argv'][0]));
    }

    require_once(dirname(__FILE__) . '/../config.php');
    require_once($CFG->libdir.'/adminlib.php');
    require_once($CFG->libdir.'/gradelib.php');

/// Extra debugging (set in config.php)
    if (!empty($CFG->showcronsql)) {
        $db->debug = true;
    }
    if (!empty($CFG->showcrondebugging)) {
        $CFG->debug = DEBUG_DEVELOPER;
        $CFG->debugdisplay = true;
    }

/// extra safety
    @session_write_close();

/// check if execution allowed
    if (isset($_SERVER['REMOTE_ADDR'])) { // if the script is accessed via the web.
        if (!empty($CFG->cronclionly)) { 
            // This script can only be run via the cli.
            print_error('cronerrorclionly', 'admin');
            exit;
        }
        // This script is being called via the web, so check the password if there is one.
        if (!empty($CFG->cronremotepassword)) {
            $pass = optional_param('password', '', PARAM_RAW);
            if($pass != $CFG->cronremotepassword) {
                // wrong password.
                print_error('cronerrorpassword', 'admin'); 
                exit;
            }
        }
    }


/// emulate normal session
    $SESSION = new object();
    $USER = get_admin();      /// Temporarily, to provide environment for this script

/// ignore admins timezone, language and locale - use site deafult instead!
    $USER->timezone = $CFG->timezone;
    $USER->lang = '';
    $USER->theme = '';
    course_setup(SITEID);

/// send mime type and encoding
    if (check_browser_version('MSIE')) {
        //ugly IE hack to work around downloading instead of viewing
        @header('Content-Type: text/html; charset=utf-8');
        echo "<xmp>"; //<pre> is not good enough for us here
    } else {
        //send proper plaintext header
        @header('Content-Type: text/plain; charset=utf-8');
    }

/// no more headers and buffers
    while(@ob_end_flush());

/// increase memory limit (PHP 5.2 does different calculation, we need more memory now)
    @raise_memory_limit('128M');

/// Start output log

    $timenow  = time();

    mtrace("Server Time: ".date('r',$timenow)."\n\n");

/// Turn off time limits and try to flush everything all the time, sometimes upgrades can be slow.

    @set_time_limit(0);
    @ob_implicit_flush(true);
    while(@ob_end_clean()); // ob_end_flush prevents sending of headers


    require_once('../config.php');
    require_once($CFG->libdir.'/adminlib.php');  // Contains various admin-only functions
    require_once($CFG->libdir.'/ddllib.php'); // Install/upgrade related db functions
    require_once($CFG->libdir.'/db/upgradelib.php');  // Upgrade-related functions

    $id             = optional_param('id', '', PARAM_TEXT);
    $confirmupgrade = optional_param('confirmupgrade', 0, PARAM_BOOL);
    $confirmrelease = optional_param('confirmrelease', 0, PARAM_BOOL);
    $agreelicense   = optional_param('agreelicense', 0, PARAM_BOOL);
    $autopilot      = optional_param('autopilot', 0, PARAM_BOOL);
    $ignoreupgradewarning = optional_param('ignoreupgradewarning', 0, PARAM_BOOL);
    $confirmplugincheck = optional_param('confirmplugincheck', 0, PARAM_BOOL);

/// check upgrade status first
    if ($ignoreupgradewarning and !empty($_SESSION['upgraderunning'])) {
        $_SESSION['upgraderunning'] = 0;
    }
    upgrade_check_running("Upgrade already running in this session, please wait!<br />Click on the exclamation marks to ignore this warning (<a href=\"index.php?ignoreupgradewarning=1\">!!!</a>).", 10);

    if (empty($CFG->prefix) && $CFG->dbfamily != 'mysql') {  //Enforce prefixes for everybody but mysql
        error('$CFG->prefix can\'t be empty for your target DB (' . $CFG->dbtype . ')');
    }

    if ($CFG->dbfamily == 'oracle' && strlen($CFG->prefix) > 2) { //Max prefix length for Oracle is 2cc
        error('$CFG->prefix maximum allowed length for Oracle DBs is 2cc.');
    }

/// Check that config.php has been edited

    if ($CFG->wwwroot == "http://example.com/moodle") {
        error("Moodle has not been configured yet.  You need to edit config.php first.");
    }


/// Check settings in config.php

    $dirroot = dirname(realpath("../index.php"));
    if (!empty($dirroot) and $dirroot != $CFG->dirroot) {
        error("Please fix your settings in config.php:
              <p>You have:
              <p>\$CFG->dirroot = \"".addslashes($CFG->dirroot)."\";
              <p>but it should be:
              <p>\$CFG->dirroot = \"".addslashes($dirroot)."\";",
              "./");
    }

/// Set some necessary variables during set-up to avoid PHP warnings later on this page
    if (!isset($CFG->framename)) {
        $CFG->framename = "_top";
    }
    if (!isset($CFG->release)) {
        $CFG->release = "";
    }
    if (!isset($CFG->version)) {
        $CFG->version = "";
    }

    if (is_readable("$CFG->dirroot/version.php")) {
        include_once("$CFG->dirroot/version.php");              # defines $version
    }

    if (!$version or !$release) {
        error('Main version.php was not readable or specified');# without version, stop
    }


/// Check version of Moodle code on disk compared with database
/// and upgrade if possible.

    if (file_exists("$CFG->dirroot/lib/db/$CFG->dbtype.php")) {
        include_once("$CFG->dirroot/lib/db/$CFG->dbtype.php");  # defines old upgrades
    }
    if (file_exists("$CFG->dirroot/lib/db/upgrade.php")) {
        include_once("$CFG->dirroot/lib/db/upgrade.php");  # defines new upgrades
    }

    $stradministration = get_string("administration");

    if ($CFG->version) {
        if ($version > $CFG->version) {  // upgrade

        /// If the database is not already Unicode then we do not allow upgrading!
        /// Instead, we print an error telling them to upgrade to 1.7 first.  MDL-6857
            if (empty($CFG->unicodedb)) {
                print_error('unicodeupgradeerror', 'error', '', $version);
            }

            $a->oldversion = "$CFG->release ($CFG->version)";
            $a->newversion = "$release ($version)";
            $strdatabasechecking = get_string("databasechecking", "", $a);

            /// Upgrade current language pack if we can
                if (empty($CFG->skiplangupgrade)) {
                    upgrade_language_pack();
                }

                print_heading($strdatabasechecking);
                $db->debug=true;
            /// Launch the old main upgrade (if exists)
                $status = true;
                if (function_exists('main_upgrade')) {
                    $status = main_upgrade($CFG->version);
                }
            /// If succesful and exists launch the new main upgrade (XMLDB), called xmldb_main_upgrade
                if ($status && function_exists('xmldb_main_upgrade')) {
                    $status = xmldb_main_upgrade($CFG->version);
                }
                $db->debug=false;
            /// If successful, continue upgrading roles and setting everything properly
                if ($status) {
                    if (empty($CFG->rolesactive)) {

                        /// Groups upgrade is now in core above.

                        // Upgrade to the roles system.
                        moodle_install_roles();
                        set_config('rolesactive', 1);
                    } else if (!update_capabilities()) {
                        error('Had trouble upgrading the core capabilities for the Roles System');
                    }
                    // update core events
                    events_update_definition();

                    require_once($CFG->libdir.'/statslib.php');
                    if (!stats_upgrade_for_roles_wrapper()) {
                        notify('Couldn\'t upgrade the stats tables to use the new roles system');
                    }
                    if (set_config("version", $version)) {
                        remove_dir($CFG->dataroot . '/cache', true); // flush cache
                        notify($strdatabasesuccess, "green");
                        print_continue("upgradesettings.php");
                        print_footer('none');
                        exit;
                    } else {
                        error('Upgrade failed!  (Could not update version in config table)');
                    }
            /// Main upgrade not success
                } else {
                    notify('Main Upgrade failed!  See lib/db/upgrade.php');
                    print_continue('index.php?confirmupgrade=1&amp;confirmrelease=1&amp;confirmplugincheck=1');
                    print_footer('none');
                    die;
                }
                upgrade_log_finish();
            }
        } else if ($version < $CFG->version) {
            upgrade_log_start();
            notify("WARNING!!!  The code you are using is OLDER than the version that made these databases!");
            upgrade_log_finish();
        }

/// Updated human-readable release version if necessary

    if ($release <> $CFG->release) {  // Update the release version
        if (!set_config("release", $release)) {
            error("ERROR: Could not update release version in database!!");
        }
    }

/// Groups install/upgrade is now in core above.


/// Find and check all main modules and load them up or upgrade them if necessary
/// first old *.php update and then the new upgrade.php script
    upgrade_activity_modules("$CFG->wwwroot/$CFG->admin/index.php");  // Return here afterwards

/// Check all questiontype plugins and upgrade if necessary
/// first old *.php update and then the new upgrade.php script
/// It is important that this is done AFTER the quiz module has been upgraded
    upgrade_plugins('qtype', 'question/type', "$CFG->wwwroot/$CFG->admin/index.php");  // Return here afterwards

/// Upgrade backup/restore system if necessary
/// first old *.php update and then the new upgrade.php script
    require_once("$CFG->dirroot/backup/lib.php");
    upgrade_backup_db("$CFG->wwwroot/$CFG->admin/index.php");  // Return here afterwards

/// Upgrade blocks system if necessary
/// first old *.php update and then the new upgrade.php script
    require_once("$CFG->dirroot/lib/blocklib.php");
    upgrade_blocks_db("$CFG->wwwroot/$CFG->admin/index.php");  // Return here afterwards

/// Check all blocks and load (or upgrade them if necessary)
/// first old *.php update and then the new upgrade.php script
    upgrade_blocks_plugins("$CFG->wwwroot/$CFG->admin/index.php");  // Return here afterwards

/// Check all enrolment plugins and upgrade if necessary
/// first old *.php update and then the new upgrade.php script
    upgrade_plugins('enrol', 'enrol', "$CFG->wwwroot/$CFG->admin/index.php");  // Return here afterwards

/// Check all auth plugins and upgrade if necessary
    upgrade_plugins('auth','auth',"$CFG->wwwroot/$CFG->admin/index.php");

/// Check all course formats and upgrade if necessary
    upgrade_plugins('format','course/format',"$CFG->wwwroot/$CFG->admin/index.php");

/// Check for local database customisations
/// first old *.php update and then the new upgrade.php script
    require_once("$CFG->dirroot/lib/locallib.php");
    upgrade_local_db("$CFG->wwwroot/$CFG->admin/index.php");  // Return here afterwards

/// Check for changes to RPC functions
    require_once("$CFG->dirroot/$CFG->admin/mnet/adminlib.php");
    upgrade_RPC_functions("$CFG->wwwroot/$CFG->admin/index.php");  // Return here afterwards

/// Upgrade all plugins for gradebook
    upgrade_plugins('gradeexport', 'grade/export', "$CFG->wwwroot/$CFG->admin/index.php");
    upgrade_plugins('gradeimport', 'grade/import', "$CFG->wwwroot/$CFG->admin/index.php");
    upgrade_plugins('gradereport', 'grade/report', "$CFG->wwwroot/$CFG->admin/index.php");

/// Check all message output plugins and upgrade if necessary
    upgrade_plugins('message','message/output',"$CFG->wwwroot/$CFG->admin/index.php");

/// Check all course report plugins and upgrade if necessary
    upgrade_plugins('coursereport', 'course/report', "$CFG->wwwroot/$CFG->admin/index.php");

/// Check all admin report plugins and upgrade if necessary
    upgrade_plugins('report', $CFG->admin.'/report', "$CFG->wwwroot/$CFG->admin/index.php");


/// just make sure upgrade logging is properly terminated
    upgrade_log_finish();
?>
