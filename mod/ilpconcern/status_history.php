<?php



/*

 * @copyright &copy; 2007 University of London Computer Centre

 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk

 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License

 * @package ILP

 * @version 1.0

 */

    require_once("../../config.php");
	require_once($CFG->dirroot.'/blocks/ilp/block_ilp_lib.php');
    require_once("lib.php");

    global $CFG, $USER;

    $id = optional_param('id', 0, PARAM_INT); // Course Module ID, or
	$userid = optional_param('userid', 0, PARAM_INT); //User's concerns we wish to view
	$courseid     = optional_param('courseid', SITEID, PARAM_INT);

	require_login();

    add_to_log($userid, "concerns", "view", "view.php", "$userid");

/// Print the main part of the page

	if ($userid > 0){
		$user = get_record('user', 'id', ''.$userid.'');
	}else{
		$user = $USER;
	}

	$strconcerns = get_string("modulenameplural", "ilpconcern");
    $strconcern  = get_string("modulename", "ilpconcern");
    $strilp = get_string("ilp", "block_ilp");
	$strilps = get_string("ilps", "block_ilp");
    $stredit = get_string("edit");

    $strdelete = get_string("delete");
    $strcomments = get_string("comments", "ilpconcern");

	$navlinks = array();

	if($id > 0){ //module is accessed through a course module use course context

		if (! $cm = get_record("course_modules", "id", $id)) {

            error("Course Module ID was incorrect");

        }



        if (! $course = get_record("course", "id", $cm->course)) {

            error("Course is misconfigured");

        }



        if (! $concerns = get_record("ilpconcern", "id", $cm->instance)) {

            error("Course module is incorrect");

        }

		$context = get_context_instance(CONTEXT_MODULE, $cm->id);

		$link_values = '?id='.$cm->id.'&amp;userid='.$user->id;

		$navlinks[] = array('name' => $course->shortname, 'link' => "$CFG->wwwroot/course/view.php?id=$course->id", 'type' => 'misc');

		$title = "$strconcerns: ".fullname($user);

		$baseurl = $CFG->wwwroot.'/mod/ilpconcern/view.php?id='.$id.'&amp;userid='.$user->id;

		$footer = $course;

    }elseif ($courseid != SITEID) { //module is accessed via report from within course

		$course = get_record('course', 'id', $courseid);

		$context = get_context_instance(CONTEXT_COURSE, $course->id);

		$link_values = '?courseid='.$course->id.'&amp;userid='.$user->id;

		$navlinks[] = array('name' => $course->shortname, 'link' => "$CFG->wwwroot/course/view.php?id=$course->id", 'type' => 'misc');

		$title = "$strconcerns: ".fullname($user);

		$baseurl = $CFG->wwwroot.'/mod/ilptarget/view.php?id='.$id.'&amp;userid='.$user->id;

		$footer = $course;

	}else{ //module is accessed independent of a course use user context

		if($user->id == $USER->id) {
			$context = get_context_instance(CONTEXT_SYSTEM);
		}else{
			$context = get_context_instance(CONTEXT_USER, $user->id);
		}

		$link_values = '?userid='.$user->id;
		$title = "$strconcerns: ".fullname($user);
		$baseurl = $CFG->wwwroot.'/mod/ilpconcern/view.php?userid='.$user->id;

		$footer = '';
	}

	$navlinks[] = array('name' => $strilps, 'link' => "$CFG->wwwroot/blocks/ilp/list.php?courseid=$courseid", 'type' => 'misc');

	$navlinks[] = array('name' => $strilp, 'link' => "$CFG->wwwroot/blocks/ilp/view.php?id=$user->id&amp;courseid=$courseid", 'type' => 'misc');

	$navlinks[] = array('name' => fullname($user), 'link' => FALSE, 'type' => 'misc');

	$navlinks[] = array('name' => $strconcerns, 'link' => "$CFG->wwwroot/mod/ilpconcern/concerns_view.php?userid=$user->id&amp;courseid=$courseid&amp;status=0", 'type' => 'misc');
	
	$navlinks[] = array('name' => get_string("statushistory", "ilpconcern"), 'link' => FALSE, 'type' => 'misc');

	$navigation = build_navigation($navlinks);
	print_header_simple($title, '', $navigation,'', '', true, '','');

//Allow users to see their own profile, but prevent others
if (has_capability('moodle/legacy:guest', $context, NULL, false)) {
	error("You are logged in as Guest.");
}

require_capability('mod/ilpconcern:view', $context);   

if($USER->id != $user->id){
	require_capability('mod/ilpconcern:viewclass', $context);
}

print_heading(get_string('statushistory', 'ilpconcern').': '.fullname($user));
if($CFG->ilpconcern_status_per_student == 1){

	if($studentstatus = get_record('ilpconcern_status', 'userid', $user->id)){

		switch ($studentstatus->status) {
			case "0":
			    $thisstudentstatus = get_string('green', 'ilpconcern');
			    break;
			case "1":
			    $thisstudentstatus = get_string('amber', 'ilpconcern');
			    break;
			case "2":
			    $thisstudentstatus = get_string('red', 'ilpconcern');
			    break;
			case "3":
				$thisstudentstatus = get_string('silver', 'ilpconcern');
				break;
			case "4":
				$thisstudentstatus = get_string('gold', 'ilpconcern');
				break;
		}
	}else{
		$studentstatus->status = 0;
		$thisstudentstatus = get_string('green', 'ilpconcern');
	}

		echo '<div class="ilpcenter">';
		echo '<h2><span class="status-'.$studentstatus->status.'">';
		echo get_string('studentstatus', 'ilpconcern').': '.$thisstudentstatus;
		echo '</span></a></h2>';
		echo '</div>';
	}

$tablecolumns = array('status', 'modified', 'fullname');
$tableheaders = array(get_string('studentstatus','ilpconcern'),get_string('date'),get_string('setby','ilpconcern'));
require_once($CFG->libdir.'/tablelib.php');

$table = new flexible_table('mod-ilpconcern-status-history');

$table->define_columns($tablecolumns);
$table->define_headers($tableheaders);
$table->define_baseurl("$CFG->wwwroot/mod/ilpconcern/status_history.php?userid=$user->id&amp;courseid=$courseid");

$table->sortable(false);
$table->collapsible(false);
$table->initialbars(false);

$table->column_class('status', 'status');
$table->column_class('modified', 'modified');
$table->column_class('fullname', 'fullname');

$table->set_attribute('cellspacing', '0');
$table->set_attribute('id', 'status_update');
$table->set_attribute('class', 'submissions');
$table->set_attribute('width', '90%');
$table->set_attribute('align', 'center');

// Start working -- this is necessary as soon as the niceties are over
$table->setup();

if($statusupdates = get_records('ilpconcern_status_history','userid',$user->id,'modified DESC')) {
	foreach($statusupdates as $statusupdate) {
		switch ($statusupdate->status) {
			case "0":
				$thisstudentstatus = get_string('green', 'ilpconcern');
				break;
			case "1":
				$thisstudentstatus = get_string('amber', 'ilpconcern');
				break;
			case "2":
				$thisstudentstatus = get_string('red', 'ilpconcern');
				break;
			case "3":
				$thisstudentstatus = get_string('silver', 'ilpconcern');
				break;
			case "4":
				$thisstudentstatus = get_string('gold', 'ilpconcern');
				break;
		}
		$tutor = get_record('user','id',$statusupdate->modifiedbyuser);
		$row = array('<span class="status-'.$statusupdate->status.'">'.$thisstudentstatus.'</span>',userdate($statusupdate->modified, get_string('strftimedatetime')),fullname($tutor));
		$table->add_data($row);	
	}
}

$table->print_html();  /// Print the whole table	   

/// Finish the page
print_footer();

?>