<?PHP // $Id: block_ilp_lib.php,v 1.5.2.25 2010/11/29 13:06:08 ulcc Exp $
require_once($CFG->libdir.'/formslib.php');
// Set defaults in case they haven't been defined

    $module = 'project/ilp';
    $config = get_config($module);

    if ( !isset($CFG->ilptarget_send_target_message))
        { $CFG->ilptarget_send_target_message = 0; }
    if ( !isset($CFG->ilptarget_send_comment_message))
        { $CFG->ilptarget_send_comment_message = 0; }
	if ( !isset($CFG->ilptarget_tutor_calendar))
		{ $CFG->ilptarget_tutor_calendar = 0; }
    if ( !isset($CFG->ilptarget_course_specific))
        { $CFG->ilptarget_course_specific = 0; }
    if ( !isset($CFG->ilptarget_use_template))
        { $CFG->ilptarget_use_template = 0; }
    if ( !isset($CFG->ilptarget_template))
        { $CFG->ilptarget_template = ''; }

    if ( !isset($CFG->ilpconcern_status_per_student))
        { $CFG->ilpconcern_status_per_student = 0; }
    if ( !isset($CFG->ilpconcern_send_concern_message))
        { $CFG->ilpconcern_send_concern_message = 0; }
    if ( !isset($CFG->ilpconcern_send_comment_message))
        { $CFG->ilpconcern_send_comment_message = 0; }
    if ( !isset($CFG->ilpconcern_course_specific))
        { $CFG->ilpconcern_course_specific = 0; }
    if ( !isset($CFG->ilpconcern_report1))
        { $CFG->ilpconcern_report1 = 0; }
    if ( !isset($CFG->ilpconcern_report2))
        { $CFG->ilpconcern_report2 = 0; }
    if ( !isset($CFG->ilpconcern_report3))
        { $CFG->ilpconcern_report3 = 0; }
    if ( !isset($CFG->ilpconcern_report4))
        { $CFG->ilpconcern_report4 = 0; }
    if ( !isset($CFG->ilpconcern_use_template))
        { $CFG->ilpconcern_use_template = 0; }

   if (!isset($config->ilp_show_student_info))
        { $config->ilp_show_student_info = 0; }
   if (!isset($config->ilp_show_personal_reports))
        { $config->ilp_show_personal_reports = 0; }
   if (!isset($config->ilp_show_subject_reports))
        { $config->ilp_show_subject_reports = 0; }
   if (!isset($config->ilp_show_targets))
        { $config->ilp_show_targets = 0; }
   if (!isset($config->ilp_show_concerns))
        { $config->ilp_show_concerns = 0; }
   if (!isset($config->ilp_show_achieved_targets))
        { $config->ilp_show_achieved_targets = 0; }
   if (!isset($config->ilp_limit_categories))
        { $config->ilp_limit_categories = 0; }
   if (empty($config->ilp_categories))
        { $config->ilp_categories = ''; }
   if (!isset($config->ilp_user_guide_link))
        { $config->ilp_user_guide_link = 0; }

    if ( !isset($config->block_ilp_student_info_allow_per_student_teacher_text))
        { $config->block_ilp_student_info_allow_per_student_teacher_text = 0; }
    if ( empty($config->block_ilp_student_info_default_per_student_teacher_text))
        { $config->block_ilp_student_info_default_per_student_teacher_text = ''; }

    if ( !isset($config->block_ilp_student_info_allow_per_student_student_text))
        { $config->block_ilp_student_info_allow_per_student_student_text = 0; }
    if ( empty($config->block_ilp_student_info_default_per_student_student_text))
        { $config->block_ilp_student_info_default_per_student_student_text = ''; }

    if ( !isset($config->block_ilp_student_info_allow_per_student_shared_text))
        { $config->block_ilp_student_info_allow_per_student_shared_text = 0; }
    if ( empty($config->block_ilp_student_info_default_per_student_shared_text))
        { $config->block_ilp_student_info_default_per_student_shared_text = ''; }

    if ( !isset($config->block_ilp_student_info_allow_per_tutor_teacher_text))
        { $config->block_ilp_student_info_allow_per_tutor_teacher_text = 0; }
    if ( empty($config->block_ilp_student_info_default_per_tutor_teacher_text))
        { $config->block_ilp_student_info_default_per_tutor_teacher_text = ''; }

    if ( !isset($config->block_ilp_student_info_allow_per_tutor_student_text))
        { $config->block_ilp_student_info_allow_per_tutor_student_text = 0; }
    if ( empty($config->block_ilp_student_info_default_per_tutor_student_text))
        { $config->block_ilp_student_info_default_per_tutor_student_text = ''; }

    if (!isset($config->block_ilp_student_info_allow_per_tutor_shared_text))
        { $config->block_ilp_student_info_allow_per_tutor_shared_text = 0; }
    if (empty($config->block_ilp_student_info_default_per_tutor_shared_text))
        { $config->block_ilp_student_info_default_per_tutor_shared_text = ''; }

    if (!isset($config->block_ilp_student_info_allow_per_teacher_teacher_text))
        { $config->block_ilp_student_info_allow_per_teacher_teacher_text = 0; }
    if (empty($config->block_ilp_student_info_default_per_teacher_teacher_text))
        { $config->block_ilp_student_info_default_per_teacher_teacher_text = ''; }

    if (!isset($config->block_ilp_student_info_allow_per_teacher_student_text))
        { $config->block_ilp_student_info_allow_per_teacher_student_text = 0; }
    if (empty($config->block_ilp_student_info_default_per_teacher_student_text))
        { $config->block_ilp_student_info_default_per_teacher_student_text = ''; }

    if (!isset($config->block_ilp_student_info_allow_per_teacher_shared_text))
        { $config->block_ilp_student_info_allow_per_teacher_shared_text = 0; }
    if (empty($config->block_ilp_student_info_default_per_teacher_shared_text))
        { $config->block_ilp_student_info_default_per_teacher_shared_text = ''; }

//  given userid returns the status of the student
//  TODO: Needs to be used throughout
function ilp_get_status($userid) {
    global $CFG;
    $module = 'project/ilp';
    $config = get_config($module);

    if($CFG->ilpconcern_status_per_student == 1){
        if($studentstatus = get_record('ilpconcern_status', 'userid', $userid)){
        switch ($studentstatus->status) {
            case "0":
                $thisstudentstatus = array(get_string('green', 'ilpconcern'),0);
                break;
            case "1":
                $thisstudentstatus = array(get_string('amber', 'ilpconcern'),1);
                break;
            case "2":
                $thisstudentstatus = array(get_string('red', 'ilpconcern'),2);
                break;
            case "3":
                $thisstudentstatus = array(get_string('silver', 'ilpconcern'),3);
                break;
			case "4":
                $thisstudentstatus = array(get_string('gold', 'ilpconcern'),4);
                break;
        }
        $studentstatusnum = $studentstatus->status;
    }else{
        $studentstatusnum = 0;
        $thisstudentstatus = array(get_string('green', 'ilpconcern'),0);
    }
        return $thisstudentstatus;
    }else{
        return '';
    }
}

//  given userid spews out users ilp report
//  this bit just queries db then hands massive assoc array to the template.
function block_ilp_report($id,$courseid,$template='template') {

    global $CFG, $USER;

	$module = 'project/ilp';
	$config = get_config($module);

    $user = get_record('user','id',$id);

    if (!$user) {
      error("bad user $id");
    }

	if($CFG->ilpconcern_status_per_student == 1){
		if($studentstatus = get_record('ilpconcern_status', 'userid', $id)){
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
			$studentstatusnum = $studentstatus->status;
		}else{
			$studentstatusnum = 0;
			$thisstudentstatus = get_string('green', 'ilpconcern');
		}
	}

    if (file_exists('templates/custom/'.$template.'.php')) {
      include('templates/custom/'.$template.'.php');
    }elseif (file_exists('template.php')) {
      include('template.php');
    }else{
      error("missing template \"$template\"") ;
    }
}



function get_my_ilp_courses($userid) {
    global $CFG, $USER;

	$module = 'project/ilp';
	$config = get_config($module);
    $ilpcourses = array();
	$courses = get_my_courses($userid);

	if($config->ilp_limit_categories == '1') {
		$ilp_categories = $config->ilp_categories;
		$allowed_categories = explode(',', $ilp_categories);

		foreach ($courses as $course){
			if(in_array($course->category,$allowed_categories)){
				$ilpcourses[] = $course;
			}
		}
	}else{
		$ilpcourses = $courses;
	}
	return $ilpcourses;
}

function print_row($left, $right) {
    echo "$left $right<br />";
}



function display_custom_profile_fields($userid) {
    global $CFG, $USER;

    if ($categories = get_records_select('user_info_category', '', 'sortorder ASC')) {
        foreach ($categories as $category) {
            if ($fields = get_records_select('user_info_field', "categoryid=$category->id", 'sortorder ASC')) {
                foreach ($fields as $field) {
                    require_once($CFG->dirroot.'/user/profile/field/'.$field->datatype.'/field.class.php');
                    $newfield = 'profile_field_'.$field->datatype;
                    $formfield = new $newfield($field->id, $userid);
                    if (!$formfield->is_empty()) {
                        print_row(s($formfield->field->name.':'), $formfield->display_data());
                    }
                }
            }
        }
    }
}

/**
     * Displays the Student Info summary to the ILP
     *
     * @param id   			userid fed from ILP page (required)
     * @param courseid   	courseid fed from ILP page (required)
     * @param full   		display a full report or just a title link - for layout and navigation
     * @param title  		display default title - turn off to add customised title to template
	 * @param icon   		display an icon with the title
	 * @param teachertext   display the teacher text section
	 * @param studenttext   display the student text section
	 * @param sharedtext   	display the shared text section
*/

function display_ilp_student_info ($id,$courseid,$full=TRUE,$title=TRUE,$icon=TRUE,$teachertext=TRUE,$studenttext=TRUE,$sharedtext=TRUE) {

	global $CFG,$USER;
	require_once("../ilp_student_info/block_ilp_student_info_lib.php");
	include ('access_context.php');

	$module = 'project/ilp';
    $config = get_config($module);

	$user = get_record('user','id',$id);

	if($title == TRUE) {
		echo '<h2>';

		if ($icon == TRUE) {
			if (file_exists('templates/custom/pix/student_info.gif')) {
				echo '<img src="'.$CFG->wwwroot.'/blocks/ilp/templates/custom/pix/student_info.gif" alt="" />';
			}else{
      			echo '<img src="'.$CFG->wwwroot.'/blocks/ilp/pix/student_info.gif" alt="" />';
			}
		}

		echo '<a href="'.$CFG->wwwroot.'/blocks/ilp_student_info/view.php?id='.$id.(($courseid)?'&courseid='.$courseid:'').'&amp;view=info">'.(($access_isuser)?get_string('viewmyilp_student_info','block_ilp_student_info'):get_string('ilp_student_info', 'block_ilp_student_info')).'</a></h2>';
	}

	if($full == TRUE) {

		if($config->block_ilp_student_info_allow_per_student_teacher_text == 1 && $teachertext == TRUE) {

			$text = block_ilp_student_info_get_text($user->id,0,0,'student','teacher') ;
			echo '<div class="block_ilp_student_info_text">'.stripslashes($text->text).'</div>';

			if($access_isteacher or $access_istutor or $access_isgod) {
				echo block_ilp_student_info_edit_button($user->id,0,(($courseid)? $courseid : 0),'student','teacher',$text->id) ;
			}
		}

		if($config->block_ilp_student_info_allow_per_student_student_text == 1 && $studenttext == TRUE) {

			$text = block_ilp_student_info_get_text($user->id,0,0,'student','student') ;
			echo '<div class="block_ilp_student_info_text">'.stripslashes($text->text).'</div>';

			if($access_isuser or $access_isgod) {
				echo block_ilp_student_info_edit_button($user->id,0,(($courseid)? $courseid : 0),'student','student',$text->id) ;
			}
		}

		if($config->block_ilp_student_info_allow_per_student_shared_text == 1 && $sharedtext == TRUE) {
			$text = block_ilp_student_info_get_text($user->id,0,0,'student','shared') ;
			echo '<div class="block_ilp_student_info_text">'.stripslashes($text->text).'</div>';

			if($access_isuser or $access_isteacher or $access_istutor or $access_isgod) {
				echo block_ilp_student_info_edit_button($user->id,0,(($courseid)? $courseid : 0),'student','shared',$text->id);
			}
		}
	}
}

/**
     * Counts total number of targets for a user
     * @param userid   			userid fed from ILP page (required)
*/

function ilptarget_get_total($userid) {
	global $CFG;
	$targettotal = count_records_sql('SELECT COUNT(*) FROM '.$CFG->prefix.'ilptarget_posts WHERE setforuserid = '.$userid.' AND status != "3"' );
	return $targettotal;
}

/**
     * Counts number of achieved targets for a user
     * @param userid   			userid fed from ILP page (required)
*/

function ilptarget_get_achieved ($userid) {
	global $CFG;
	$targetcomplete = count_records_sql('SELECT COUNT(*) FROM '.$CFG->prefix.'ilptarget_posts WHERE setforuserid = '.$userid.' AND status = "1"');
	return $targetcomplete;
}

/**
     * Counts number of achieved targets for a user
     * @param userid   			userid fed from ILP page (required)
*/

function ilptarget_percentage_complete ($userid) {
	return round((ilptarget_get_achieved($userid) / ilptarget_get_total($userid))*100,0);
}

/**
     * Counts number of achieved targets for a user
     * @param userid   			userid fed from ILP page (required)
*/

function ilptarget_display_complete ($userid) {
	return ilptarget_get_achieved($userid).'/'.ilptarget_get_total($userid).' '.get_string('complete', 'ilptarget');
}

/**
     * Get last post for report type
     * @param userid   			userid fed from ILP page (required)
*/

function ilptarget_get_last_report($userid) {
	global $CFG;
	if($lastreport = get_record_sql('SELECT * FROM '.$CFG->prefix.'ilptarget_posts WHERE setforuserid = '.$userid.' AND status = 0 ORDER BY timemodified DESC',FALSE)) {
		return $lastreport->timemodified;
	}
}

/**
     * Checks if there is an unread target by comparing with logs
     * @param userid   			userid fed from ILP page (required)
*/

function ilptarget_check_new ($userid1,$userid2) {
	/*global $CFG;
	if ($lastview = get_record_sql('SELECT * FROM '.$CFG->prefix.'log WHERE userid = '.$userid1.' AND module = \'target\' AND info = '.$userid2.' ORDER BY TIME DESC',FALSE)) {
		$lastviewtime = $lastview->time;
	}else{
		$lastviewtime = 0;
	}
	if(ilptarget_get_total($userid2) > 0 ) {
		$lastreport = ilptarget_get_last_report($userid2);
	}else{
		$lastreport = 0;
	}
	if($lastreport > $lastviewtime) {
		return 1;
	}else{*/
		return 0;
	//}
}

/**
     * Displays the ilptarget summary to the ILP
     *
     * @param id   			userid fed from ILP page (required)
     * @param courseid   	courseid fed from ILP page (required)
     * @param full   		display a full report or just a title link - for layout and navigation
     * @param title  		display default title - turn off to add customised title to template
	 * @param icon   		display an icon with the deafult title
	 * @param sortorder     DESC or ASC - to sort on deadline dates
	 * @param limit		    limit the number of targets shown on the page
	 * @param status	    -1 means all otherwise a particular status can be entered
	 * @param tutorsetonly 	display tutor set targets only
	 * @param studentsetonly display student set targets only
*/

function display_ilptarget ($id,$courseid,$full=TRUE,$title=TRUE,$icon=TRUE,$sortorder='ASC',$limit=0,$status=-1,$tutorsetonly=FALSE,$studentsetonly=FALSE) {

	global $CFG,$USER;
	require_once("$CFG->dirroot/blocks/ilp_student_info/block_ilp_student_info_lib.php");
	require_once("$CFG->dirroot/mod/ilptarget/lib.php");
	include ('access_context.php');

	$module = 'project/ilp';
    $config = get_config($module);

	$user = get_record('user','id',$id);

	$select = "SELECT {$CFG->prefix}ilptarget_posts.*, up.username ";
	$from = "FROM {$CFG->prefix}ilptarget_posts, {$CFG->prefix}user up ";
	$where = "WHERE up.id = setbyuserid AND setforuserid = $id ";

	if($status != -1) {
		$where .= "AND status = $status ";
	}elseif($config->ilp_show_achieved_targets == 1){
    	$where .= "AND status != 3 ";
	}else{
    	$where .= "AND status = 0 ";
	}

	if($CFG->ilptarget_course_specific == 1 && $courseid != 0){
		$where .= "AND course = $courseid ";
	}

	if($tutorsetonly == TRUE && $studentsetonly == FALSE) {
		$where .= "AND setforuserid != setbyuserid ";
	}

	if($studentsetonly == TRUE && $tutorsetonly == FALSE) {
		$where .= "AND setforuserid = setbyuserid ";
	}

	$order = "ORDER BY deadline $sortorder ";

    $target_posts = get_records_sql($select.$from.$where.$order,0,$limit);

	if($title == TRUE) {
		echo '<h2';
		if($full == FALSE) {
			echo ' style="display:inline"';
		}
		echo '>';

		if ($icon == TRUE) {
			if (file_exists('templates/custom/pix/target.gif')) {
				echo '<img src="'.$CFG->wwwroot.'/blocks/ilp/templates/custom/pix/target.gif" alt="" />';
			}else{
      			echo '<img src="'.$CFG->wwwroot.'/blocks/ilp/pix/target.gif" alt="" />';
			}
		}

		echo '<a href="'.$CFG->wwwroot.'/mod/ilptarget/target_view.php?'.(($courseid > 1)?'courseid='.$courseid.'&amp;' : '').'userid='.$id.'">'.(($access_isuser)? get_string("mytargets", "ilptarget"):get_string("modulenameplural", "ilptarget")).'</a>';

		if(has_capability('mod/ilptarget:addtarget', $context) || ($USER->id == $user->id && has_capability('mod/ilptarget:addowntarget', $context))) {
			echo '<div class="ilpadd">';
			echo '<a class="button" href="'.$CFG->wwwroot.'/mod/ilptarget/target_view.php?'.(($courseid != SITEID)?'courseid='.$courseid.'&amp;' : '').'userid='.$id.'&amp;action=updatetarget" onclick="this.blur();"><span>'.get_string('add', 'ilptarget').'</span></a>';
			echo '</div>';
			echo '<div class="clearer">&nbsp;</div>';
		}

		echo '</h2>';
	}

	if($full == FALSE) {
		echo '<p style="display:inline; margin-left: 5px">'.ilptarget_display_complete($userid).'</p>';
	}

	if($full == TRUE) {
		echo '<div class="block_ilp_ilptarget">';

		if($target_posts) {
			foreach($target_posts as $post) {
				$posttutor = get_record('user','id',$post->setbyuserid);

				echo '<div class="ilp_post yui-t4';
				if($post->category > 0){
					$targetcategory = get_record('ilp_post_category','id',$post->category);
					echo ' target-category-'.$targetcategory->id.' ';
				}
				echo '">';
				   echo '<div class="bd" role="main">';
					echo '<div class="yui-main">';
					echo '<div class="yui-b"><div class="yui-gd">';
					echo '<div class="yui-u first">';
					echo get_string('name', 'ilptarget');
					echo '</div>';
					echo '<div class="yui-u">';
					echo $post->name;
					echo '</div>';
				echo '</div>';
				echo '<div class="yui-gd">';
					echo '<div class="yui-u first">';
					echo '<p>'.get_string('targetagreed', 'ilptarget').'</p>';
						echo '</div>';
					echo '<div class="yui-u">';
					echo '<p>'.$post->targetset.'</p>';
						echo '</div>';
				echo '</div>';
				echo '</div>';
					echo '</div>';
					echo '<div class="yui-b">';
					echo '<ul>';
					if($post->category > 0){
						$targetcategory = get_record('ilp_post_category','id',$post->category);
						echo '<li>'.get_string('categorydetails', 'ilptarget').': '.$targetcategory->name.'</li>';
					}
					echo '<li>'.get_string('setby', 'ilptarget').': '.fullname($posttutor);
					if($post->courserelated == 1){
						$targetcourse = get_record('course','id',$post->targetcourse);
						echo '<li>'.get_string('course').': '.$targetcourse->shortname.'</li>';
					}
					echo '<li>'.get_string('set', 'ilptarget').': '.userdate($post->timecreated, get_string('strftimedate')).'</li>';
					
					echo '<li>';
					if($post->status == 0 && time() > $post->deadline) { echo '<span style="color:#E41B17; font-weight:bold">'; }
					echo get_string('deadline', 'ilptarget').': '.userdate($post->deadline, get_string('strftimedate'));
					if($post->status == 0 && time() > $post->deadline) { echo '</span>'; }
					if($post->status == 1) {
						echo '<li>'.get_string('achieved', 'ilptarget').': '.userdate($post->timemodified, get_string('strftimedate')).'</li>';
					}
					echo '</li></ul>';

					$commentcount = count_records('ilptarget_comments', 'targetpost', $post->id);

					echo '<div class="commands"><a href="'.$CFG->wwwroot.'/mod/ilptarget/target_comments.php?'.(($courseid > 1)?'courseid='.$courseid.'&amp;' : '').'userid='.$id.'&amp;targetpost='.$post->id.'">'.$commentcount.' '.get_string("comments", "ilptarget").'</a> ';

					if($post->status == 0 || has_capability('moodle/site:doanything', $context)){
						echo ilptarget_update_status_menu($post->id,$context);
					}
					echo '</div>';

					if($post->status == 1){
						echo '<img class="achieved" src="'.$CFG->pixpath.'/mod/ilptarget/achieved.gif" alt="" />';
					}elseif(time() > $post->deadline) {
						echo '<img class="achieved" src="'.$CFG->pixpath.'/mod/ilptarget/overdue.gif" alt="" />';
					}
					echo '</div>';
					echo '</div>';
				echo '</div>';
			}
		}
		echo '</div>';
	}
}

function display_remote_ilptarget ($id,$courseid,$full=TRUE,$title=TRUE,$icon=TRUE,$sortorder='ASC',$limit=0,$status=-1,$tutorsetonly=FALSE,$studentsetonly=FALSE)
{
    global $CFG, $MNET;
    require_once $CFG->dirroot.'/mnet/xmlrpc/client.php';

    if($title == TRUE) {
        echo '<h2';
        if($full == FALSE) {
            echo ' style="display:inline"';
        }
        echo '>';

        if ($icon == TRUE) {
            if (file_exists('templates/custom/pix/target.gif')) {
                echo '<img src="'.$CFG->wwwroot.'/blocks/ilp/templates/custom/pix/target.gif" alt="" />';
            }else{
                echo '<img src="'.$CFG->wwwroot.'/blocks/ilp/pix/target.gif" alt="" />';
            }
        }

        echo (($access_isuser)? get_string("remotemytargets", "ilptarget"):get_string("remoteplural", "ilptarget")).'</h2>';
    }

    $path_to_function = "mod/ilptarget/rpclib.php/mnet_ilp_targets";
    $user   =   get_record('user','id',$id); //this line will use in real version but at the mo got to use dummy data
    $remote_record  =   array();
     //get hosts greater than 2 as 1 and 2 are usually home site and allhost
     $mnet_host =   get_records_sql("select * from {$CFG->prefix}mnet_host where id > 2");

    if (isset($user) && $user != false)
    {
        //loop through hosts
        if (isset($mnet_host) && $mnet_host != false)
        {

            foreach ($mnet_host as $mh)
            {
                $wwwroot    =   $mh->wwwroot."/";
                $mnet_peer = new mnet_peer();
                $mnet_peer->set_wwwroot($wwwroot);
                $mnet_peer->set_id($mh->id); //

                $mnet_request = new mnet_xmlrpc_client();
                $mnet_request->set_method($path_to_function);

                $mnet_request->add_param(array('username',$user->username)); // will be useed in real version
                // $mnet_request->add_param(array('username',"student01"));
                $mnet_request->add_param($courseid); //course
                $mnet_request->add_param($sortorder); //sortorder
                $mnet_request->add_param($limit); //limit
                $mnet_request->add_param($status);//status
                $mnet_request->add_param($tutorsetonly);//tutor only
                $mnet_request->add_param($studentsetonly);//student ony

                $mnet_request->send($mnet_peer);

                if (isset($mnet_request->response) && $mnet_request->response != false && $mnet_request->response != "")
                {
                    $remote_record[]    =   $mnet_request;
                    //break;
                }


            }

        }
        else
        {
            echo "failed on host";
            return array(false);
        }
    }
    else
    {
        echo "failed on user";
        return array(false);
    }

    if($remote_record) {
        foreach ($remote_record as $remv)
        {
            if ($remv != false)
            {
                $remote_rec = $remv->response;
                foreach ($remote_rec as $post)
                {
                    echo '<div class="ilp_post yui-t4">';
                       echo '<div class="bd" role="main">';
                        echo '<div class="yui-main">';
                        echo '<div class="yui-b"><div class="yui-gd">';
                        echo '<div class="yui-u first">';
                        echo get_string('name', 'ilptarget');
                        echo '</div>';
                        echo '<div class="yui-u">';
                        echo $post['name'];
                        echo '</div>';
                    echo '</div>';
                    echo '<div class="yui-gd">';
                        echo '<div class="yui-u first">';
                        echo '<p>'.get_string('targetagreed', 'ilptarget').'</p>';
                            echo '</div>';
                        echo '<div class="yui-u">';
                        echo '<p>'.$post['targetset'].'</p>';
                            echo '</div>';
                    echo '</div>';
                    echo '</div>';
                        echo '</div>';
                        echo '<div class="yui-b">';
                        echo '<ul>';
                        echo '<li>'.get_string('setby', 'ilptarget').': '.$post['setbyuserid'];
                        if($post['courserelated'] == 1){
                            echo '<li>'.get_string('course').': '.$post['$targetcourse'].'</li>';
                        }
                        echo '<li>'.get_string('set', 'ilptarget').': '.userdate($post['timecreated'], get_string('strftimedate'));
                        echo '<li>'.get_string('deadline', 'ilptarget').': '.userdate($post['deadline'], get_string('strftimedate'));
                        echo '</ul>';

                        if($post['status'] == 1){
                            echo '<img class="achieved" src="'.$CFG->pixpath.'/mod/ilptarget/achieved.gif" alt="" />';
                        }
                        echo '</div>';
                        echo '</div>';
                    echo '</div>';
                }
            }
        }
    }
}

/**
     * Counts total number of reviews for a user
     * @param userid   			userid fed from ILP page (required)
*/

function ilpconcern_get_total($userid,$i) {
	global $CFG;
	$reporttotal = count_records_sql('SELECT COUNT(*) FROM '.$CFG->prefix.'ilpconcern_posts WHERE setforuserid = '.$userid.' AND status = "'.($i - 1).'"' );
	return $reporttotal;
}

/**
     * Get last post for report type
     * @param userid   			userid fed from ILP page (required)
*/

function ilpconcern_get_last_report($userid,$i) {
	global $CFG;
	$lastreport = get_record_sql('SELECT * FROM '.$CFG->prefix.'ilpconcern_posts WHERE setforuserid = '.$userid.' AND status = '.($i - 1).' ORDER BY timemodified DESC',FALSE);
	return $lastreport->timemodified;
}

/**
     * Checks if there is an unread review by comparing with logs
     * @param userid   			userid fed from ILP page (required)
*/

function ilpconcern_check_new ($userid1,$userid2,$i) {

	/*global $CFG;
	if ($lastview = get_record_sql('SELECT * FROM '.$CFG->prefix.'log WHERE userid = '.$userid1.' AND module = \'concerns\' AND info = '.$userid2.' ORDER BY TIME DESC',FALSE)) {
		$lastviewtime = $lastview->time;
	}else{
		$lastviewtime = 0;
	}
	if(ilpconcern_get_total($userid2,$i) > 0 ) {
		$lastreport = ilpconcern_get_last_report($userid2,$i);
	}else{
		$lastreport = 0;
	}
	if($lastreport > $lastviewtime) {
		return 1;
	}else{*/
		return 0;
	//}
}

/**
     * Displays the ilpconcern summary to the ILP
     *
     * @param id   			userid fed from ILP page (required)
     * @param courseid   	courseid fed from ILP page (required)
	 * @param report	   	report number from ILP page (required)
     * @param full   		display a full report or just a title link - for layout and navigation
     * @param title  		display default title - turn off to add customised title to template
	 * @param icon   		display an icon with the deafult title
	 * @param sortorder     DESC or ASC - to sort on deadline dates
	 * @param limit		    limit the number of targets shown on the page
	 * @param status	    -1 means all otherwise a particular status can be entered
*/

function display_ilpconcern ($id,$courseid,$report,$full=TRUE,$title=TRUE,$icon=TRUE,$sortorder='DESC',$limit=0) {

	global $CFG,$USER;
	require_once("$CFG->dirroot/blocks/ilp_student_info/block_ilp_student_info_lib.php");
	require_once("$CFG->dirroot/mod/ilpconcern/lib.php");
	include ('access_context.php');

	$module = 'project/ilp';
    $config = get_config($module);

	$user = get_record('user','id',$id);

	$status = $report - 1;

	$select = "SELECT {$CFG->prefix}ilpconcern_posts.*, up.username ";
	$from = "FROM {$CFG->prefix}ilpconcern_posts, {$CFG->prefix}user up ";
	$where = "WHERE up.id = setbyuserid AND status = $status AND setforuserid = $id ";

	if($CFG->ilpconcern_course_specific == 1 && $courseid != 0){
		$where .= 'AND course = '.$courseid.' ';
	}

    $order = "ORDER BY deadline $sortorder ";

    $concerns_posts = get_records_sql($select.$from.$where.$order,0,$limit);

	if($title == TRUE) {
		echo '<h2';
		if($full == FALSE) {
			echo ' style="display:inline"';
		}
		echo '>';

		if ($icon == TRUE) {
			if (file_exists('templates/custom/pix/report'.$report.'.gif')) {
				echo '<img src="'.$CFG->wwwroot.'/blocks/ilp/templates/custom/pix/report'.$report.'.gif" alt="" />';
			}else{
      			echo '<img src="'.$CFG->wwwroot.'/blocks/ilp/pix/report'.$report.'.gif" alt="" />';
			}
		}

		echo '<a href="'.$CFG->wwwroot.'/mod/ilpconcern/concerns_view.php?'.(($courseid > 1)?'courseid='.$courseid.'&amp;' : '').'userid='.$id.'&amp;status='.$status.'">'.(($access_isuser)? get_string('report'.$report.'plural','ilpconcern'):get_string('report'.$report.'plural','ilpconcern')).'</a>';

		echo '<div class="ilpadd">';
		if(eval('return $CFG->ilpconcern_report'.$report.';') == 1 && (has_capability('mod/ilpconcern:addreport'.$report, $context) || ($USER->id == $user->id && has_capability('mod/ilpconcern:addownreport'.$report, $context)))) {
			echo '<a class="button" href="'.$CFG->wwwroot.'/mod/ilpconcern/concerns_view.php?'.(($courseid > 1)?'courseid='.$courseid.'&amp;' : '').'userid='.$id.'&amp;status='.$status.'&amp;action=updateconcern&amp;status='.($status).'" onclick="this.blur();"><span>'.get_string('addconcern', 'ilpconcern', get_string('report'.$report, 'ilpconcern')).'</span></a>';
		}
		echo '</div>';
		echo '<div class="clearer">&nbsp;</div>';

		echo '</h2>';

	}

	if($full == FALSE) {
		echo '<p style="display:inline; margin-left: 5px">'.ilpconcern_get_total($userid,$report).' '.get_string('report'.$report.'plural','ilpconcern');
		if(ilpconcern_get_total($userid,$report) > 0 ) {
			echo ' | '.get_string('lastreview','ilpconcern').': '.userdate(ilpconcern_get_last_report($userid,$report), get_string('strftimedate')).'</p>';
		}
	}

	if($full == TRUE) {
		echo '<div class="block_ilp_ilpconcern">';

		if($concerns_posts) {
			foreach($concerns_posts as $post) {
				$posttutor = get_record('user','id',$post->setbyuserid);

				echo '<div class="ilp_post yui-t4">';
				   echo '<div class="bd" role="main">';
					echo '<div class="yui-main">';
					echo '<div class="yui-b">';
					if(isset($post->name)){
						echo '<div class="yui-gd">';
						echo '<div class="yui-u first">';
						echo get_string('name', 'ilpconcern');
						echo '</div>';
						echo '<div class="yui-u">';
						echo $post->name;
						echo '</div>';
					echo '</div>';
					}
				echo '<div class="yui-gd">';
					echo '<div class="yui-u first">';
					echo '<p>'.get_string('report'.$report,'ilpconcern').'</p>';
						echo '</div>';
					echo '<div class="yui-u">';
					echo '<p>'.$post->concernset.'</p>';
						echo '</div>';
				echo '</div>';
				echo '</div>';
					echo '</div>';
					echo '<div class="yui-b">';
					echo '<ul>';
					echo '<li>'.get_string('setby', 'ilpconcern').': '.fullname($posttutor);
					if($post->courserelated == 1){
						$targetcourse = get_record('course','id',$post->targetcourse);
						echo '<li>'.get_string('course').': '.$targetcourse->shortname.'</li>';
					}
					if(eval('return $CFG->ilpconcern_report'.$report.'_status;') == 1) {
						switch ($post->rec_status) {
							case "0":
								$thisrecstatus = get_string('green', 'ilpconcern');
								break;
							case "1":
								$thisrecstatus = get_string('amber', 'ilpconcern');
								break;
							case "2":
								$thisrecstatus = get_string('red', 'ilpconcern');
								break;
							case "3":
								$thisrecstatus = get_string('silver', 'ilpconcern');
								break;
							case "4":
								$thisrecstatus = get_string('gold', 'ilpconcern');
								break;
							default:
								$thisrecstatus = '';
							break;
						}
						echo '<li>'.get_string('rec_status', 'ilpconcern').': <span class="status-'.$post->rec_status.'">'.$thisrecstatus.'</span></li>';
					}
					echo '<li>'.get_string('deadline', 'ilpconcern').': '.userdate($post->deadline, get_string('strftimedate')).'</li>';
					echo '</ul>';

					$commentcount = count_records('ilpconcern_comments', 'concernspost', $post->id);

					echo '<div class="commands"><a href="'.$CFG->wwwroot.'/mod/ilpconcern/concerns_comments.php?'.(($courseid > 1)?'courseid='.$courseid.'&amp;' : '').'userid='.$id.'&amp;concernspost='.$post->id.'">'.$commentcount.' '.get_string("comments", "ilpconcern").'</a> ';
					if (file_exists($CFG->dirroot.'/blocks/ilp/templates/print/progress_report.php')) {
						echo '<a href="'.$CFG->wwwroot.'/blocks/ilp/templates/print/progress_report.php?'.(($courseid)?'courseid='.$courseid.'&amp;' : '').'userid='.$post->setforuserid.'&amp;concernspost='.$post->id.'" target="newWin">| <img style="height:11px; width:11px" src="'.$CFG->wwwroot.'/blocks/ilp/templates/custom/pix/print.gif" alt="Print" title="Print" /> Print</a>';
					}
					
					echo ilpconcern_update_menu($post->id,$context,$report);

					echo '</div>';

					echo '</div>';
					echo '</div>';
				echo '</div>';
			}
		}
		echo '</div>';
	}
}

/**
     * Displays the Personal report summary to the ILP
     *
     * @param id   			userid fed from ILP page
     * @param courseid   	courseid fed from ILP page
     * @param full   		display a full report or just a title link - for layout and navigation
     * @param title  		display default title - turn off to add customised title to template
	 * @param icon   		display an icon with the title
	 * @param teachertext   display the teacher text section
	 * @param studenttext   display the student text section
	 * @param sharedtext   	display the shared text section
*/

function display_ilp_personal_report ($id,$courseid,$full=TRUE,$title=TRUE,$icon=TRUE,$teachertext=TRUE,$studenttext=TRUE,$sharedtext=TRUE) {

	global $CFG,$USER;
	require_once("$CFG->dirroot/blocks/ilp_student_info/block_ilp_student_info_lib.php");
	include ('access_context.php');

	$module = 'project/ilp';
    $config = get_config($module);

	$user = get_record('user','id',$id);

	if($title == TRUE) {
		echo '<h2>';

		if ($icon == TRUE) {
			if (file_exists('templates/custom/pix/personal_report.gif')) {
				echo '<img src="'.$CFG->wwwroot.'/blocks/ilp/templates/custom/pix/personal_report.gif" alt="" />';
			}else{
      			echo '<img src="'.$CFG->wwwroot.'/blocks/ilp/pix/personal_report.gif" alt="" />';
			}
		}

		echo '<a href="'.$CFG->wwwroot.'/blocks/ilp_student_info/view.php?id='.$id.(($courseid)?'&courseid='.$courseid:'').'&amp;view=personal">'.get_string('personal_report', 'block_ilp').'</a></h2>';
	}

	if($full == TRUE) {

    	$context = get_context_instance(CONTEXT_USER, $user->id);
    	$tutors = get_users_by_capability($context, 'block/ilp_student_info:viewclass', 'u.*', 'u.lastname ASC', '', '', '', '', false);

    	if ($tutors) {

			foreach ($tutors as $tutor) {
				if (count_records('ilp_student_info_per_tutor','teacher_userid',$tutor->id, 'student_userid', $user->id) != 0){
					echo '<table style="text-align:left; margin:5px;" class="generalbox"><tbody><tr><th colspan="2">'.fullname($tutor).'<th></tr>';

					if($config->block_ilp_student_info_allow_per_tutor_teacher_text == 1 && $teachertext == TRUE) {
						$text = block_ilp_student_info_get_text($user->id,$tutor->id,$course->id,'tutor','teacher');

						echo '<tr><td>'.get_string('tutor_comment','block_ilp_student_info').':</td></tr><tr><td><div class="block_ilp_student_info_text">'.stripslashes($text->text).'</div></td>';

						if($tutor->id == $USER->id or $access_isgod) {
							echo '<td class="per_tutor_teacher_edit">'.block_ilp_student_info_edit_button($user->id,$tutor->id,$course->id,'tutor','teacher',$text->id).'</td>';
						}else{
							echo '<td class="per_tutor_teacher_edit"></td></tr>';
						}
					}

					if($config->block_ilp_student_info_allow_per_tutor_student_text == 1 && $studenttext == TRUE) {
						$text = block_ilp_student_info_get_text($user->id,$tutor->id,$course->id,'tutor','student');

						echo '<tr><td>'.get_string('student_response','block_ilp_student_info').':</td></tr><tr><td><div class="block_ilp_student_info_text">'.stripslashes($text->text).'</div></td>';

						if($access_isuser || $access_isgod) {
							echo '<td class="per_tutor_student_edit">'.block_ilp_student_info_edit_button($user->id,$tutor->id,$course->id,'tutor','student',$text->id).'</td></tr>';
						}else{
							echo '<td class="per_tutor_student_edit"></td></tr>';
						}
					}

					if($config->block_ilp_student_info_allow_per_tutor_shared_text == 1 && $sharedtext == TRUE) {
						$text = block_ilp_student_info_get_text($user->id,$tutor->id,$course->id,'tutor','shared') ;

						echo '<tr><td>'.get_string('shared_text','block_ilp_student_info').':</td></tr><tr><td><div class="block_ilp_student_info_text">'.stripslashes($text->text).'</div></td>';

						if($access_isuser or $tutor->id == $USER->id or $access_isgod) {
							echo '<td class="per_tutor_shared_edit">'.block_ilp_student_info_edit_button($user->id,$tutor->id,$course->id,'tutor','shared',$text->id).'</td></tr>';
						}else{
							echo '<td class="per_tutor_shared_edit"></td></tr>';
						}
					}
				}elseif($tutor->id == $USER->id){

					if($config->block_ilp_student_info_allow_per_tutor_teacher_text == 1 && $teachertext == TRUE) {
						$text = block_ilp_student_info_get_text($user->id,$tutor->id,$course->id,'tutor','teacher') ;
						echo '<tr><td>'.get_string('notextteacher','block_ilp').':'.block_ilp_student_info_edit_button($user->id,$tutor->id,$course->id,'tutor','teacher',$text->id).'</td></tr>';
					}

					if($config->block_ilp_student_info_allow_per_tutor_shared_text == 1 && $sharedtext == TRUE) {
						$text = block_ilp_student_info_get_text($user->id,$tutor->id,$course->id,'tutor','shared') ;
						echo '<tr><td>'.get_string('notextshared','block_ilp').':'.block_ilp_student_info_edit_button($user->id,$tutor->id,$course->id,'tutor','shared',$text->id).'</td></tr>';
					}
				}
			}
		}
    	unset($tutors);
		echo '</tbody></table>';
	}
}

/**
     * Displays the Subject report summary to the ILP
     *
     * @param id   			userid fed from ILP page
     * @param courseid   	courseid fed from ILP page
     * @param full   		display a full report or just a title link - for layout and navigation
     * @param title  		display default title - turn off to add customised title to template
	 * @param icon   		display an icon with the title
	 * @param teachertext   display the teacher text section
	 * @param studenttext   display the student text section
	 * @param sharedtext   	display the shared text section
*/

function display_ilp_subject_report ($id,$courseid,$full=TRUE,$title=TRUE,$icon=TRUE,$teachertext=TRUE,$studenttext=TRUE,$sharedtext=TRUE) {

	global $CFG,$USER;
	require_once("$CFG->dirroot/blocks/ilp_student_info/block_ilp_student_info_lib.php");
	include ('access_context.php');

	$module = 'project/ilp';
    $config = get_config($module);

	$user = get_record('user','id',$id);

	if($title == TRUE) {
		echo '<h2>';

		if ($icon == TRUE) {
			if (file_exists('templates/custom/pix/subject_report.gif')) {
				echo '<img src="'.$CFG->wwwroot.'/blocks/ilp/templates/custom/pix/subject_report.gif" alt="" />';
			}else{
      			echo '<img src="'.$CFG->wwwroot.'/blocks/ilp/pix/subject_report.gif" alt="" />';
			}
		}

		echo '<a href="'.$CFG->wwwroot.'/blocks/ilp_student_info/view.php?id='.$id.(($courseid)?'&courseid='.$courseid:'').'&amp;view=subject">'.get_string('subject_report', 'block_ilp').'</a></h2>';
	}

	if($full == TRUE) {

		$ilpcourses = get_my_ilp_courses($user->id);

    	foreach ($ilpcourses as $course) {
        	print_heading("$course->fullname ($course->shortname)", "left", "3");

        	// who teachers with it ?
	        $context = get_context_instance(CONTEXT_COURSE, $course->id);

			$teachers = get_users_by_capability($context, 'moodle/course:update', 'u.id,u.firstname,u.lastname', 'u.lastname ASC', '', '', '', '', false);

			echo '<table style="text-align:left; margin:5px;" class="generalbox"><tbody>';

			foreach($teachers as $teacher) {
				if (count_records('ilp_student_info_per_teacher','teacher_userid',$teacher->id, 'courseid', $course->id, 'student_userid', $user->id) != 0){

					echo '<tr><th colspan="3">'.fullname($teacher).'<th></tr>';

					if($config->block_ilp_student_info_allow_per_teacher_teacher_text == 1 && $teachertext == TRUE) {
						$text = block_ilp_student_info_get_text($user->id,$teacher->id,$course->id,'teacher','teacher');
						echo '<tr><td class="per_teacher_teacher_intro">'.get_string('tutor_comment','block_ilp_student_info').':</td></tr><tr><td class="per_teacher_teacher_text"><div class="block_ilp_student_info_text">'.stripslashes($text->text).'</div></td>';

						if($teacher->id == $USER->id or $access_isgod) {
							echo '<td class="per_teacher_teacher_edit">'.block_ilp_student_info_edit_button($user->id,$teacher->id,$course->id,'teacher','teacher',$text->id).'</td></tr>' ;
						}else{
							echo '<td class="per_teacher_teacher_edit"></td></tr>';
				  		}
					}

					if($config->block_ilp_student_info_allow_per_teacher_student_text == 1 && $studenttext == TRUE) {
						$text = block_ilp_student_info_get_text($user->id,$teacher->id,$course->id,'teacher','student');
						echo'<tr><td class="per_teacher_student_intro">'.get_string('student_response','block_ilp_student_info').':</td></tr><tr><td class="per_teacher_student_text"><div class="block_ilp_student_info_text">'.stripslashes($text->text).'</div></td>';

						if($access_isuser or $access_isgod) {
							echo '<td class="per_teacher_student_edit">'.block_ilp_student_info_edit_button($user->id,$teacher->id,$course->id,'teacher','student',$text->id).'</td></tr>' ;
						}else{
							echo '<td class="per_teacher_student_edit"></td></tr>';
				  		}
					}

					if($config->block_ilp_student_info_allow_per_teacher_shared_text == 1 && $sharedtext == TRUE) {
						$text = block_ilp_student_info_get_text($user->id,$teacher->id,$course->id,'teacher','shared');
						echo '<tr><td class="per_teacher_shared_intro">'.get_string('shared_text','block_ilp_student_info').':</td></tr><tr><td class="per_teacher_shared_text"><div class="block_ilp_student_info_text">'.stripslashes($text->text).'</div></td>';

						if($access_isuser or $teacher->id == $USER->id or $access_isgod) {
							echo '<td class="per_teacher_shared_edit">'.block_ilp_student_info_edit_button($user->id,$teacher->id,$course->id,'teacher','shared',$text->id).'</td></tr>' ;
						}else{
							echo '<td class="per_teacher_shared_edit"></td></tr>';
				  		}
					}
					echo '<tr><td colspan="3" class="per_teacher_row"><hr /></td></tr>';
				}elseif($teacher->id == $USER->id){

					if($config->block_ilp_student_info_allow_per_teacher_teacher_text == 1) {
						$text = block_ilp_student_info_get_text($user->id,$teacher->id,$course->id,'teacher','teacher') ;
						echo '<tr><td>'.get_string('notextteacher','block_ilp').':'.block_ilp_student_info_edit_button($user->id,$teacher->id,$course->id,'teacher','teacher',$text->id).'</td></tr>';
					}

					if($config->block_ilp_student_info_allow_per_teacher_shared_text == 1) {
						$text = block_ilp_student_info_get_text($user->id,$teacher->id,$course->id,'teacher','shared') ;
						echo '<tr><td>'.get_string('notextshared','block_ilp').':'.block_ilp_student_info_edit_button($user->id,$teacher->id,$course->id,'teacher','shared',$text->id).'</td></tr>';
					}
				}
			}
			unset($teachers);
			echo '</tbody></table>';
		}
	}
}

/*** Forms for bulk upload of personal tutors ***/

class admin_uploaduser_form1 extends moodleform {
    function definition (){
        global $CFG, $USER;

        $mform =& $this->_form;

        $this->set_upload_manager(new upload_manager('userfile', false, false, null, false, 0, true, true, false));

        $mform->addElement('header', 'settingsheader', get_string('upload'));

        $mform->addElement('file', 'userfile', get_string('file'), 'size="40"');
        $mform->addRule('userfile', null, 'required');

        $choices = csv_import_reader::get_delimiter_list();
        $mform->addElement('select', 'delimiter_name', get_string('csvdelimiter', 'admin'), $choices);
        if (array_key_exists('cfg', $choices)) {
            $mform->setDefault('delimiter_name', 'cfg');
        } else if (get_string('listsep') == ';') {
            $mform->setDefault('delimiter_name', 'semicolon');
        } else {
            $mform->setDefault('delimiter_name', 'comma');
        }

        $textlib = textlib_get_instance();
        $choices = $textlib->get_encodings();
        $mform->addElement('select', 'encoding', get_string('encoding', 'admin'), $choices);
        $mform->setDefault('encoding', 'UTF-8');

        $choices = array('10'=>10, '20'=>20, '100'=>100, '1000'=>1000, '100000'=>100000);
        $mform->addElement('select', 'previewrows', get_string('rowpreviewnum', 'admin'), $choices);
        $mform->setType('previewrows', PARAM_INT);

        $this->add_action_buttons(false, get_string('uploadusers'));
    }
}

class admin_uploaduser_form2 extends moodleform {
    function definition (){
        global $CFG, $USER;

        //no editors here - we need proper empty fields
        $CFG->htmleditor = null;

        $mform   =& $this->_form;
        $columns =& $this->_customdata;

        $mform->addElement('header', 'settingsheader', get_string('settings'));

        $options = array('username' => 'Username', 'idnumber' => 'ID Number', 'email' => 'Email');
        $mform->addElement('select', 'user_field', get_string('user_field', 'block_ilp'), $options);

// hidden fields
        $mform->addElement('hidden', 'iid');
        $mform->setType('iid', PARAM_INT);

        $mform->addElement('hidden', 'previewrows');
        $mform->setType('previewrows', PARAM_INT);

        $mform->addElement('hidden', 'readcount');
        $mform->setType('readcount', PARAM_INT);

        $this->add_action_buttons(true, get_string('uploadusers'));
    }

    /**
     * Form tweaks that depend on current data.
     */
    function definition_after_data() {
        $mform   =& $this->_form;
        $columns =& $this->_customdata;

        foreach ($columns as $column) {
            if ($mform->elementExists($column)) {
                $mform->removeElement($column);
            }
        }
    }

    /**
     * Server side validation.
     */
    function validation($data, $files) {
        $errors = parent::validation($data, $files);
        $columns =& $this->_customdata;

        // look for other required data
        if (!in_array('tutorid', $columns)) {
            $errors['tutor'] = get_string('missingfield', 'error', 'tutorid');
        }
        if (!in_array('studentid', $columns)) {
            $errors['student'] = get_string('missingfield', 'error', 'studentid');
        }
        if (!in_array('role', $columns)) {
            $errors['role'] = get_string('missingfield', 'error', 'role');
        }
        return $errors;
    }
}



?>

