<?PHP 



//  Lists the student info texts relevant to the student.

//  with links to edit for those who can. 

    require_once('../../config.php');
    require_once('block_ilp_student_info_lib.php');
    require_once($CFG->dirroot.'/blocks/ilp/block_ilp_lib.php');
    require_once('access_context.php'); 

    $contextid    = optional_param('contextid', 0, PARAM_INT);               // one of this or
    $courseid     = optional_param('courseid', SITEID, PARAM_INT);                  // this are required
    $userid       = optional_param('id', 0, PARAM_INT);                  // this is required
    $view       = optional_param('view', 'all', PARAM_TEXT); 
    $text       = optional_param('text', 'all', PARAM_TEXT);   

 $module = 'project/ilp';
$config = get_config($module);

    if (!$userid) {

        $userid = $USER->id ;

    }

    include('access_context.php'); 

    $user = get_record('user','id',$userid);

/// Print headers
	$navlinks = array();
    if ($course->id != SITEID) {
		$navlinks[] = array('name' => $course->shortname, 'link' => "$CFG->wwwroot/course/view.php?id=$course->id", 'type' => 'misc');
    } 
		
	if(!$access_isuser) {
		$navlinks[] = array('name' => get_string('ilps','block_ilp'), 'link' => "$CFG->wwwroot/blocks/ilp/list.php?courseid=$course->id", 'type' => 'misc');
	}
	
	$navlinks[] = array('name' => get_string('ilp','block_ilp'), 'link' => "$CFG->wwwroot/blocks/ilp/view.php?id=$user->id&amp;courseid=$course->id", 'type' => 'misc');
		
	$navlinks[] = array('name' => get_string('ilp_student_info','block_ilp_student_info'), 'link' => FALSE, 'type' => 'misc');
		
	if(!$access_isuser) {
		$navlinks[] = array('name' => fullname($user), 'link' => FALSE, 'type' => 'misc');
	}
	
	$navigation = build_navigation($navlinks);
	print_header_simple(get_string('ilp_student_info','block_ilp_student_info'), '', $navigation,'', '', true, '','');

if (file_exists('templates/custom/template.php')) {
  include('templates/custom/template.php');
}elseif (file_exists('template.php')) {
  include('template.php');
}else{
  error("missing template \"$template\"") ; 
}
 
print_footer($course);

?>
