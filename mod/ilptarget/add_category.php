<?php  

/*
 * @copyright &copy; 2009 University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @mod ilpconcern
 */

    require_once("../../config.php");
    require_once("$CFG->dirroot/mod/ilptarget/lib.php");

    global $CFG;

	$id = optional_param('id', 0, PARAM_INT);
    $action = optional_param('action',NULL, PARAM_CLEAN);

	if($id > 0) {
		$template = get_record('ilp_post_category','id',$id);
	}
	


print_header_simple();

$mform = new ilptarget_addcategory_form('', array('id' => $id));

if ($mform->is_cancelled()){
}
if($fromform = $mform->get_data()){        
	$mform->process_data($fromform);
	redirect($CFG->wwwroot.'/admin/module.php?module=ilptarget',get_string('changessaved'),0);
}
if($action == 'delete'){ //Check to see if we are deleting
	delete_records('ilp_post_category','id',$id);
	redirect($CFG->wwwroot.'/admin/module.php?module=ilptarget',get_string('changessaved'),0);
}

    print_heading(get_string('editcategory', 'ilptarget'));
	$mform->display(); 
 

?>
