<?PHP 

/*
 * @copyright &copy; 2007 University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package ILP
 * @version 1.0
 */

//  Lists all the users who's ilp one can view

    require_once('../../config.php');
    require_once($CFG->dirroot.'/user/profile/lib.php');
    require_once('block_ilp_lib.php');
    include('access_context.php');    

    global $USER, $CFG;

    $contextid    = optional_param('contextid', 0, PARAM_INT);
    $embedded    = optional_param('embedded', 0, PARAM_INT);
    $template    = optional_param('template', 'template', PARAM_ALPHA);
	
	add_to_log($courseid, 'ilp', 'view', false, $user->id, 0, $USER->id);

    if(!$embedded) {
	/// Print headers
		$navlinks = array();
		if ($course->id != SITEID) {
			$navlinks[] = array('name' => $course->shortname, 'link' => "$CFG->wwwroot/course/view.php?id=$course->id", 'type' => 'misc');
		}
	
		if($access_isuser) {
			$title = get_string('viewmyilp','block_ilp');
			$navlinks[] = array('name' => get_string('viewmyilp','block_ilp'), 'link' => FALSE, 'type' => 'misc');
		}else{
			$title = fullname($user).': '.get_string('ilp','block_ilp');
			$navlinks[] = array('name' => get_string('ilps','block_ilp'), 'link' => "$CFG->wwwroot/blocks/ilp/list.php?courseid=$course->id", 'type' => 'misc');
			$navlinks[] = array('name' => get_string('ilp','block_ilp'), 'link' => FALSE, 'type' => 'misc');
			$navlinks[] = array('name' => fullname($user), 'link' => FALSE, 'type' => 'misc');
		}
	
		$navigation = build_navigation($navlinks);
		print_header_simple($title, '', $navigation);
    }

    block_ilp_report($user->id,$course->id,$template);

    if(!$embedded) {
      print_footer($course);
    }

?>
