<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * siteinfo plugin function library 
 *
 * @package    local
 * @subpackage siteinfo
 * @copyright  2012 Kenneth Lett (http://osuosl.org)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Initialise the siteinfo table with this site's info
 * @return bool
 */

function siteinfo_init_db() {

    global $CFG, $DB, $SITE;


    // timeframe - default is within the last month, 
    // i.e time() - 2592000 seconds (30 days)
    // other options:
    // in the last week = time() - 604800
    $timeframe = time() - 2592000;


    // teachers = regular and non-editing teachers
    $teachers = siteinfo_usercount("teacher",null);
    
    $courselist_string = siteinfo_courselist(); 

    $siteinfo = new stdClass();
    $siteinfo->baseurl      = $CFG->wwwroot;
    $siteinfo->basepath     = $CFG->dirroot;
    $siteinfo->sitename     = $SITE->fullname;
    $siteinfo->sitetype     = "moodle";
    $siteinfo->siteversion  = $CFG->version;
    $siteinfo->siterelease  = $CFG->release;
    $siteinfo->location     = gethostname();
    $siteinfo->adminemail   = $CFG->supportemail;
    $siteinfo->totalusers   = siteinfo_usercount(null, null);
    $siteinfo->adminusers   = intval($CFG->siteadmins);
    $siteinfo->teachers     = $teachers;
    $siteinfo->activeusers  = siteinfo_usercount(null, $timeframe);
    $siteinfo->totalcourses = count($courselist);
    $siteinfo->courses      = $courselist_string;
    $siteinfo->timemodified = time();
    
    insert_record('siteinfo', $siteinfo);

    return true;
}

/**
 * Update the siteinfo table with this site's info
 * this will get called on certain events, see events.php
 * @return bool
 */
function siteinfo_update_db() {
    global $CFG, $SITE;
    // timeframe - default is within the last month, 
    // i.e time() - 2592000 seconds (30 days)
    // other options:
    // in the last week = time() - 604800
    $timeframe = time() - 2592000;
    
    // teachers = regular and non-editing teachers
    $teachers = siteinfo_usercount("teacher",null);
    
    $courselist_string = siteinfo_courselist(); 

    $siteinfo = new stdClass();
    $siteinfo->id           = 1;
    $siteinfo->baseurl      = $CFG->wwwroot;
    $siteinfo->basepath     = $CFG->dirroot;
    $siteinfo->sitename     = $SITE->fullname;
    $siteinfo->sitetype     = "moodle";
    $siteinfo->siteversion  = $CFG->version;
    $siteinfo->siterelease  = $CFG->release;
    $siteinfo->location     = gethostname();
    $siteinfo->adminemail   = $CFG->supportemail;
    $siteinfo->totalusers   = siteinfo_usercount(null, null);
    $siteinfo->adminusers   = intval($CFG->siteadmins);
    $siteinfo->teachers     = $teachers;
    $siteinfo->activeusers  = siteinfo_usercount(null, $timeframe);
    $siteinfo->totalcourses = count($courselist);
    $siteinfo->courses      = $courselist_string;
    $siteinfo->timemodified = time();

    try {
        update_record('siteinfo', $siteinfo);
    } catch (Exception $e) {
        //echo 'Caught exception: ',  $e->getMessage(), "\n";
        return false;
    }
    return true;  
}

/**
 * Count users
 * @return int
 */
function siteinfo_usercount($role="none", $timeframe=null) {
    global $CFG;

    switch ($role) {
      case "teacher":
        $role_condition = "IN (3,4)";
        break;
      case "manager":
        $role_condition = "= 1";
        break;
      case "course_creator":
        $role_condition = "= 2";
        break;
      case "student":
        $role_condition = "= 5";
        break;
      case "guest":
        $role_condition = "= 6";
        break;
      case "authed":
        $role_condition = "= 7";
        break;
      case "frontpage":
        $role_condition = "= 8";
        break;
      default:
        $role = false;
    }

    if ($timeframe) {
      //sql += (append WHERE clause to sql to limit by activity date)
      $where = "AND mdl_user.lastaccess > $timeframe";
    } else {
      $where = '';
    }

    if($role) {
      $sql = "SELECT COUNT(*)
              FROM mdl_role_assignments
              LEFT JOIN mdl_user
              ON mdl_user.id = mdl_role_assignments.userid
              WHERE mdl_role_assignments.roleid $role_condition
              $where";

    } else {
      $sql = "SELECT COUNT(*) 
                FROM mdl_user
               WHERE mdl_user.deleted = 0
               AND mdl_user.confirmed = 1
               $where";
    }

    $count = count_records_sql($sql, null);

    return intval($count);
}

/**
 * generate list of courses installed here
 * @return array
 * @TODO: write this function 
 */
function siteinfo_courselist() {
    global $CFG;
    // get all course idnumbers
    $table = 'course';
    $select = 'format != "site"';
    $params = null;
    $sort = 'id';
    $fields = 'id,shortname,idnumber';
    $courses = get_records_select($table,$select);
    $course_list = array();
    foreach($courses as $id=>$course) {
        if($course) {
          $enrolled = siteinfo_get_enrolments($id);
          $course_list[] = '{"serial":"0",' . 
                            '"shortname":"' . htmlentities($course->shortname) . 
                            '","enrolled":' . $enrolled . '}';
        }
    }
    $courselist_string = '';

    if (count($course_list) > 0) {
     $courselist_string = "[" . implode(',', $course_list) . "]";
    }

    return $courselist_string;

}

/**
 * Geti student enrollments for this course 
 * @return array
 * @TODO: write this function 
 */
function siteinfo_get_enrolments($courseid) {
  global $CFG;

  $sql = "select count(mdl_role_assignments.userid) 
          from mdl_role_assignments
          left join mdl_context
            on mdl_context.id = mdl_role_assignments.contextid
          where mdl_context.contextlevel=50
          and mdl_context.instanceid=$courseid
          and mdl_role_assignments.roleid=5";
  
  $params = null;
  return get_field_sql($sql,$params, IGNORE_MISSING);
}
