Making customised print templates.

To create print templates you should create one or both of the following files in the 'print' directory:

course_report.php will define a template of report template for an entire course group 
progress_report.php will define a template for printing individual reports from the ilpconcern module

Once these files are created the print icon and option will automatically appear.

An example 'progress_report.php' file is detailed below:

<?php

/*
 * @copyright &copy; 2007 University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package ILP
 * @version 1.0
 */

//  Lists all the users who's ilp one can view

    require_once('../../../../config.php');
    require_once($CFG->dirroot.'/blocks/ilp/block_ilp_lib.php');
    include($CFG->dirroot.'/blocks/ilp/access_context.php');    

    global $USER, $CFG;

    $contextid    = optional_param('contextid', 0, PARAM_INT);
    $concernspost = optional_param('concernspost', 0, PARAM_INT); //User's concern 

    $post = get_record('ilpconcern_posts','id',$concernspost);
    $user = get_record('user','id',$post->setforuserid);
    $posttutor = get_record('user','id',$post->setbyuserid);   
	
    echo '<h1>Student Progress Report<br />'.fullname($user).'</h1>';
    echo '<table style="clear:both; border:1px solid #ccc; width:100%; text-align:left">';
    echo '<tr>';
    echo '<th scope="row" width="5%">Tutor</th>';
    echo '<td width="28%">'.fullname($posttutor).'</td>';
    echo '<th scope="row" width="5%">Course</th>';
    if($post->courserelated == 1){
        $targetcourse = get_record('course','id',$post->targetcourse);
        echo '<td width="28%">'.$targetcourse->shortname.'</td>';
    }else{
        echo '<td width="28%">&nbsp;</td>';
    }
    echo '<th scope="row" width="5%">Date</th>';
    echo '<td width="28%">'.userdate($post->deadline, get_string('strftimedateshort')).'</td>';
    echo '</tr>';
    echo '</table>';
	echo '<div style="text-align:left">';
    echo format_text($post->concernset,FORMAT_MOODLE);	
	echo '</div>';

?>

