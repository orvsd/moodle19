<?PHP

/*
 * @copyright &copy; 2007 University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package ILP
 * @version 1.0
 */

//  Lists the student info texts relevant to the student.
//  with links to edit for those who can.

    require_once('../../config.php');
    require_once('block_ilp_lib.php');
	include('access_context.php');



    global $GFG, $USER;



    $contextid    = optional_param('contextid', 0, PARAM_INT);               // one of this or

    $courseid     = optional_param('courseid', SITEID, PARAM_INT);                  // this are required

	$group = optional_param('group', -1, PARAM_INT);

	$updatepref = optional_param('updatepref', -1, PARAM_INT);

    //$coursecontext ;

    if ($contextid) {

        if (! $coursecontext = get_context_instance_by_id($contextid)) {

            error("Context ID is incorrect");

        }

        if (! $course = get_record('course', 'id', $coursecontext->instanceid)) {

            error("Course ID is incorrect");

        }

    } else if ($courseid) {

        if (! $course = get_record('course', 'id', $courseid)) {

            error("Course ID is incorrect");

        }

        if (! $coursecontext = get_context_instance(CONTEXT_COURSE, $course->id)) {

            error("Context ID is incorrect");

        }

    }



    require_login($course);



    $sitecontext = get_context_instance(CONTEXT_SYSTEM);



    if (has_capability('moodle/site:doanything',$sitecontext)) {  // are we god ?

        $access_isgod = 1 ;

    }

    if (has_capability('block/ilp:viewclass',$coursecontext)) { // are we the teacher on the course ?

        $access_isteacher = 1 ;

    }
	$baseurl = $CFG->wwwroot.'/blocks/ilp/list.php?courseid='.$courseid;
	
	add_to_log($courseid, 'ilp', 'view overview', '', 'Tutor Overview', 0, $USER->id);

/// Print headers

	$navlinks = array();

	$navlinks[] = array('name' => get_string('ilps','block_ilp'), 'link' => FALSE, 'type' => 'misc');

	$navigation = build_navigation($navlinks);
	print_header_simple(get_string('ilps','block_ilp'), '', $navigation,'', '', true, '','');

    if ($courseid and $access_isteacher and $course->id != $SITE->id) {

	$context = get_context_instance(CONTEXT_COURSE, $course->id);

	if ($updatepref > 0){

		$perpage = optional_param('perpage', 10, PARAM_INT);

		$perpage = ($perpage <= 0) ? 10 : $perpage ;

		set_user_preference('target_perpage', $perpage);

	}



	/* next we get perpage and from database

     */



	$perpage = get_user_preferences('target_perpage', 10);

	$teacherattempts = false; /// Temporary measure

	$page    = optional_param('page', 0, PARAM_INT);

/// Check to see if groups are being used in this course
/// and if so, set $currentgroup to reflect the current group

    $groupmode    = groups_get_course_groupmode($course);   // Groups are being used
    $currentgroup = groups_get_course_group($course, true);

    if (!$currentgroup) {      // To make some other functions work better later
        $currentgroup  = NULL;
    }

    $isseparategroups = ($course->groupmode == SEPARATEGROUPS and $course->groupmodeforce and
                         !has_capability('moodle/site:accessallgroups', $context));

    /// Get all teachers and students

    //$users = get_users_by_capability($context, 'mod/ilptarget:view'); // everyone with this capability set to non-prohibit

	print_heading(get_string("students")." (".$course->shortname.")");
	
	if (file_exists($CFG->dirroot.'/blocks/ilp/templates/print/course_reports.php')) {
		echo '<div style="float:right; margin: 10px; font-size: 1.05em"><a href="'.$CFG->wwwroot.'/blocks/ilp/templates/print/course_reports.php?'.(($courseid)?'courseid='.$courseid : '').'" target="newWin"><img src="'.$CFG->wwwroot.'/blocks/ilp/templates/custom/pix/print.gif" alt="Print" title="Print" /> Print</a></div>';
	}
	
    groups_print_course_menu($course, $baseurl);

	$doanythingroles = get_roles_with_capability('moodle/site:doanything', CAP_ALLOW, $sitecontext);

	/*if ($roles = get_roles_used_in_context($context)) {



        // We should exclude "admin" users (those with "doanything" at site level) because

        // Otherwise they appear in every participant list







        foreach ($roles as $role) {

            if (isset($doanythingroles[$role->id])) {   // Avoid this role (ie admin)

                unset($roles[$role->id]);

                continue;

            }

            $rolenames[$role->id] = strip_tags(format_string($role->name));   // Used in menus etc later on

        }

    }*/



	$tablecolumns = array('picture', 'fullname');
		$tableheaders = array('', get_string('fullname'));

		if($CFG->ilpconcern_status_per_student == 1){
			$tablecolumns[] .= 'status';
			$tableheaders[] .= get_string('studentstatus', 'ilpconcern');
		}
		
		if (file_exists($CFG->dirroot.'/blocks/ilp/templates/custom/mis_lib.php')) {
      		require_once($CFG->dirroot.'/blocks/ilp/templates/custom/mis_lib.php');
			if(function_exists('block_ilp_get_target_grade')) {
				$tablecolumns[] .= 'tg';
				$tableheaders[] .= get_string('target_grade', 'block_ilp');
			}
			if(function_exists('block_ilp_get_overall_attendance')) {
				$tablecolumns[] .= 'attendance';
				$tableheaders[] .= get_string('attendance', 'block_ilp');
			}
			if(function_exists('block_ilp_get_overall_punctuality')) {
				$tablecolumns[] .= 'punctuality';
				$tableheaders[] .= get_string('punctuality', 'block_ilp');
			}
		}
		
		$tablecolumns[] .= 'targets';
		$tableheaders[] .= get_string('modulenameplural', 'ilptarget');
		
		if($config->ilp_show_concerns == 1) { 
			$i = 1;
			while ($i <= 4){	
				if(eval('return $CFG->ilpconcern_report'.$i.';') == 1) {
					$tablecolumns[] .= get_string('report'.$i.'plural','ilpconcern');
					$tableheaders[] .= get_string('report'.$i.'plural','ilpconcern');
				}
				$i++;
			}
		}

		$tablecolumns[] .= 'ilp';
		$tableheaders[] .= get_string('ilp', 'block_ilp');
		
		if($config->ilp_show_subject_reports == 1) { 
			$tablecolumns[] .= 'subject_report';
			$tableheaders[] .= get_string('subject_report', 'block_ilp');
		}
		
		if (file_exists($CFG->dirroot.'/blocks/ilp/templates/custom/mis_lib.php')) {
      		require_once($CFG->dirroot.'/blocks/ilp/templates/custom/mis_lib.php');
			if(function_exists('block_ilp_get_local_link')) {
				$tablecolumns[] .= 'locallink';
				$tableheaders[] .= get_string('infolink', 'block_ilp');
			}
		}

	require_once($CFG->libdir.'/tablelib.php');

	$table = new flexible_table('mod-targets');



	$table->define_columns($tablecolumns);

	$table->define_headers($tableheaders);

	$table->define_baseurl($baseurl);



	$table->sortable(false, 'lastname');

	$table->collapsible(false);

	$table->initialbars(true);



	$table->column_suppress('picture');

	$table->column_class('picture', 'picture');

	$table->column_class('fullname', 'fullname');

	$table->column_class('ilp', 'ilp');



	$table->set_attribute('cellspacing', '0');

	$table->set_attribute('id', 'attempts');

	$table->set_attribute('class', 'submissions');

	$table->set_attribute('width', '100%');

	$table->set_attribute('align', 'center');



	// Start working -- this is necessary as soon as the niceties are over

	$table->setup();



	// we are looking for all users with this role assigned in this context or higher

    if ($usercontexts = get_parent_contexts($context)) {

        $listofcontexts = '('.implode(',', $usercontexts).')';

    } else {

        $sitecontext = get_context_instance(CONTEXT_SYSTEM, SITEID);

        $listofcontexts = '('.$sitecontext->id.')'; // must be site

    }



	//if (empty($users)) {

		//print_heading(get_string('noattempts','assignment'));

		//return true;

	//}



	$select = 'SELECT u.id, u.firstname, u.lastname, u.picture ';

    $from = 'FROM '.$CFG->prefix.'user u INNER JOIN

    '.$CFG->prefix.'role_assignments ra on u.id=ra.userid LEFT OUTER JOIN

    '.$CFG->prefix.'user_lastaccess ul on (ra.userid=ul.userid and ul.courseid = '.$course->id.') LEFT OUTER JOIN

    '.$CFG->prefix.'role r on ra.roleid = r.id ';



    // excluse users with these admin role assignments

    if ($doanythingroles) {

        $adminroles = 'AND ra.roleid NOT IN (';



        foreach ($doanythingroles as $aroleid=>$role) {

            $adminroles .= "$aroleid,";

        }

        $adminroles = rtrim($adminroles,",");

        $adminroles .= ')';

    } else {

        $adminroles = '';

    }



	// join on 2 conditions

    // otherwise we run into the problem of having records in ul table, but not relevant course

    // and user record is not pulled out

    $where  = "WHERE (ra.contextid = $context->id OR ra.contextid in $listofcontexts)

	 AND u.deleted = 0

        AND (ul.courseid = $course->id OR ul.courseid IS NULL)

        AND u.username <> 'guest'

	 AND ra.roleid = 5

	 AND r.id = 5

        $adminroles";





    $wheresearch = '';



	if ($currentgroup) {    // Displaying a group by choice

        // FIX: TODO: This will not work if $currentgroup == 0, i.e. "those not in a group"

        $from  .= 'LEFT JOIN '.$CFG->prefix.'groups_members gm ON u.id = gm.userid ';

        $where .= ' AND gm.groupid = '.$currentgroup;

    }

	if ($table->get_sql_where()) {

        $where .= ' AND '.$table->get_sql_where();

    }



    if ($table->get_sql_sort()) {

        $sort = ' ORDER BY '.$table->get_sql_sort();

    } else {

        $sort = '';

    }



	$nousers = get_records_sql($select.$from.$where.$wheresearch.$sort);



	$table->pagesize($perpage, count($nousers));

	///offset used to calculate index of student in that particular query, needed for the pop up to know who's next

    $offset = $page * $perpage;



	if (($ausers = get_records_sql($select.$from.$where.$wheresearch.$sort, $table->get_page_start(), $table->get_page_size())) !== false) {



            foreach ($ausers as $auser) {
				$picture = print_user_picture($auser->id, $course->id, $auser->picture, false, true);
				$update  = '<a href="'.$CFG->wwwroot.'/blocks/ilp/view.php?courseid='.$courseid.'&amp;id='.$auser->id.'">'.get_string('viewilp', 'block_ilp').'</a>';
				$row = array($picture, fullname($auser));
				if($CFG->ilpconcern_status_per_student == 1){
					$studentstatus = ilp_get_status($auser->id);
					$row[] .= '<span class="status-'.$studentstatus[1].'">'.$studentstatus[0].'</span>';
				}
					if (file_exists($CFG->dirroot.'/blocks/ilp/templates/custom/mis_lib.php')) {
						require_once($CFG->dirroot.'/blocks/ilp/templates/custom/mis_lib.php');
						if(function_exists('block_ilp_get_target_grade')) {
							$row[] .= block_ilp_get_target_grade($mentee->id);
						}
						if(function_exists('block_ilp_get_overall_attendance')) {
							$attendance = block_ilp_get_overall_attendance($auser->id);
							$row[] .= '<span class="attendance-'.$attendance[1].'">'.$attendance[0].'%</span>';
						}
						if(function_exists('block_ilp_get_overall_punctuality')) {
							$punctuality = block_ilp_get_overall_punctuality($auser->id);
							$row[] .= '<span class="attendance-'.$punctuality[1].'">'.$punctuality[0].'%</span>';
						}	
					}
					if(ilptarget_get_total($auser->id) > 0){
						$targetrow = '<a href="'.$CFG->wwwroot.'/mod/ilptarget/target_view.php?'.(($courseid)?'courseid='.$courseid.'&amp;' : '').'userid='.$auser->id.'">'.ilptarget_display_complete($auser->id).'</a>';
						if(ilptarget_check_new($USER->id,$auser->id) == 1) {
							$targetrow .= '<img src="'.$CFG->pixpath.'/i/new.gif" alt="" style="margin-left: 2px" />';
						}
						$row[] .= $targetrow;
					}else{
						$row[] .= '<a href="'.$CFG->wwwroot.'/mod/ilptarget/target_view.php?'.(($courseid)?'courseid='.$courseid.'&amp;' : '').'userid='.$auser->id.'">'.get_string('norecords','ilptarget').'</a>';
					}
				if($config->ilp_show_concerns == 1) { 
					$i = 1;
					while ($i <= 4){
						unset($reviewrow);	
						if(eval('return $CFG->ilpconcern_report'.$i.';') == 1) {
						$reviewrow = '<a href="'.$CFG->wwwroot.'/mod/ilpconcern/concerns_view.php?'.(($courseid)?'courseid='.$courseid.'&amp;' : '').'userid='.$auser->id.'&amp;status='.($i - 1).'">'.ilpconcern_get_total($auser->id,$i).'</a>';					
						if(ilpconcern_check_new($USER->id,$auser->id,$i) == 1) {
							$reviewrow .= '<img src="'.$CFG->pixpath.'/i/new.gif" alt="" style="margin-left: 2px" />';
						}
						if(ilpconcern_get_total($auser->id,$i)) {
							$reviewrow .= '<br />'.userdate(ilpconcern_get_last_report($auser->id,$i), get_string('strftimedateshort'));
						}
						$row[] .= $reviewrow;
						}
						$i++;
					}
				}
				
				$row[] .= $update;
				
				if($config->ilp_show_subject_reports == 1) { 
					$row[] .= '<a href="'.$CFG->wwwroot.'/blocks/ilp_student_info/view.php?view=subject&amp;courseid='.$courseid.'&amp;id='.$auser->id.'">'.get_string('subject_report', 'block_ilp').'</a>';
				}
				
				if (file_exists($CFG->dirroot.'/blocks/ilp/templates/custom/mis_lib.php')) {
		      		require_once($CFG->dirroot.'/blocks/ilp/templates/custom/mis_lib.php');
					if(function_exists('block_ilp_get_local_link')) {	
						$row[] .= block_ilp_get_local_link($auser->id,$courseid);
					}
				}	
	
				$table->add_data($row);
	
				}

        }

        $table->print_html();  /// Print the whole table

		/// Mini form for setting user preference

        echo '<br />';

        echo '<form name="options" action="list.php?courseid='.$courseid.'" method="post">';

        echo '<input type="hidden" id="updatepref" name="updatepref" value="1" />';

        echo '<table id="optiontable" align="center">';

        echo '<tr align="right"><td>';

        echo '<label for="perpage">'.get_string('pagesize','ilptarget').'</label>';

        echo ':</td>';

        echo '<td align="left">';

        echo '<input type="text" id="perpage" name="perpage" size="1" value="'.$perpage.'" />';

        helpbutton('pagesize', get_string('pagesize','ilptarget'), 'target');

        echo '</td></tr>';

        echo '<tr>';

        echo '<td colspan="2" align="right">';

        echo '<input type="submit" value="'.get_string('savepreferences').'" />';

        echo '</td></tr></table>';

        echo '</form>';



    } else {

        //$courses = get_my_courses($USER->id); // should be courses i can teach in

        $courses = get_records_sql("SELECT course.*

                                    FROM {$CFG->prefix}role_assignments ra,

                                         {$CFG->prefix}role_capabilities rc,

                                         {$CFG->prefix}context c,

                                         {$CFG->prefix}course course

                                    WHERE ra.userid = $USER->id

                                    AND   ra.contextid = c.id

                                    AND   ra.roleid = rc.roleid

                                    AND   rc.capability = 'block/ilp:viewclass'

                                    AND   c.instanceid = course.id

                                    AND   c.contextlevel = ".CONTEXT_COURSE." 
									
									ORDER BY course.fullname");

		$baseurl = $CFG->wwwroot.'/blocks/ilp/list.php?courseid='.$courseid;

		require_once($CFG->libdir.'/tablelib.php');
		$table = new flexible_table('ilp-mentee-list');

		if ($updatepref > 0){
			$perpage = optional_param('perpage', 10, PARAM_INT);
			$perpage = ($perpage <= 0) ? 10 : $perpage ;
			set_user_preference('ilp_personal_tutor_perpage', $perpage);
		}

		/* next we get perpage and from database */
		$perpage = get_user_preferences('ilp_personal_tutor_perpage', 10);
		$page    = optional_param('page', 0, PARAM_INT);

		$tablecolumns = array('picture', 'fullname');
		$tableheaders = array('', get_string('fullname'));

		if($CFG->ilpconcern_status_per_student == 1){
			$tablecolumns[] .= 'status';
			$tableheaders[] .= get_string('studentstatus', 'ilpconcern');
		}
		
		if (file_exists($CFG->dirroot.'/blocks/ilp/templates/custom/mis_lib.php')) {
      		require_once($CFG->dirroot.'/blocks/ilp/templates/custom/mis_lib.php');
			if(function_exists('block_ilp_get_target_grade')) {
				$tablecolumns[] .= 'tg';
				$tableheaders[] .= get_string('target_grade', 'block_ilp');
			}
			if(function_exists('block_ilp_get_overall_attendance')) {
				$tablecolumns[] .= 'attendance';
				$tableheaders[] .= get_string('attendance', 'block_ilp');
			}
			if(function_exists('block_ilp_get_overall_punctuality')) {
				$tablecolumns[] .= 'punctuality';
				$tableheaders[] .= get_string('punctuality', 'block_ilp');
			}
		}
		
		$tablecolumns[] .= 'targets';
		$tableheaders[] .= get_string('modulenameplural', 'ilptarget');
		
		if($config->ilp_show_concerns == 1) { 
			$i = 1;
			while ($i <= 4){	
				if(eval('return $CFG->ilpconcern_report'.$i.';') == 1) {
					$tablecolumns[] .= get_string('report'.$i.'plural','ilpconcern');
					$tableheaders[] .= get_string('report'.$i.'plural','ilpconcern');
				}
				$i++;
			}
		}

		$tablecolumns[] .= 'ilp';
		$tableheaders[] .= '';
		
		if($config->ilp_show_subject_reports == 1) { 
			$tablecolumns[] .= 'subject_report';
			$tableheaders[] .= get_string('subject_report', 'block_ilp');
		}
		
		if (file_exists($CFG->dirroot.'/blocks/ilp/templates/custom/mis_lib.php')) {
      		require_once($CFG->dirroot.'/blocks/ilp/templates/custom/mis_lib.php');
			if(function_exists('block_ilp_get_local_link')) {
				$tablecolumns[] .= 'locallink';
				$tableheaders[] .= get_string('infolink', 'block_ilp');
			}
		}

		$table->define_columns($tablecolumns);
		$table->define_headers($tableheaders);
		$table->define_baseurl($baseurl);

		$table->sortable(true, 'lastname');
		$table->collapsible(false);
		$table->initialbars(false);

		$table->column_suppress('picture');
		$table->column_class('picture', 'picture');
		$table->column_class('fullname', 'fullname');
		$table->column_class('ilp', 'ilp');

		$table->set_attribute('cellspacing', '0');
		$table->set_attribute('id', 'attempts');
		$table->set_attribute('class', 'generalbox');
		$table->set_attribute('width', '10%');
		$table->set_attribute('align', 'center');

	// Start working -- this is necessary as soon as the niceties are over

		$table->setup();


	///offset used to calculate index of student in that particular query, needed for the pop up to know who's next
	    $offset = $page * $perpage;

        $mentee_select = "SELECT u.* ";
		$mentee_from = "FROM {$CFG->prefix}role_assignments ra, {$CFG->prefix}context c, {$CFG->prefix}user u ";
	    if($CFG->ilpconcern_status_per_student = 1) {
			$mentee_select .= ", s.status ";
			$mentee_from .= "LEFT JOIN {$CFG->prefix}ilpconcern_status s ON u.id = s.userid ";
		}
		$mentee_where = "WHERE ra.userid = $USER->id AND ra.contextid = c.id AND c.instanceid = u.id AND c.contextlevel = ".CONTEXT_USER;
		if ($table->get_sql_where()) {
	        $mentee_where .= ' AND '.$table->get_sql_where();
	    }
		if ($table->get_sql_sort()) {
	        $mentee_sort = ' ORDER BY '.$table->get_sql_sort();
	    }else{
	        $mentee_sort = '';
	    }

        $nomentees = get_records_sql($mentee_select.$mentee_from.$mentee_where.$mentee_sort);
		$table->pagesize($perpage, count($nomentees));

		if(($mentees = get_records_sql($mentee_select.$mentee_from.$mentee_where.$mentee_sort, $table->get_page_start(), $table->get_page_size())) !== FALSE){
		print_heading(get_string('blockname', 'block_mentees'));
		if (file_exists($CFG->dirroot.'/blocks/ilp/templates/print/mentee_reports.php')) {
			echo '<div style="float:right; margin: 10px; font-size: 1.05em"><a href="'.$CFG->wwwroot.'/blocks/ilp/templates/print/mentee_reports.php" target="newWin"><img src="'.$CFG->wwwroot.'/blocks/ilp/templates/custom/pix/print.gif" alt="Print" title="Print" /> Print</a></div>';
		}
		    foreach ($mentees as $mentee) {
			$picture = print_user_picture($mentee->id, $course->id, $mentee->picture, false, true);
			$update  = '<a href="view.php?courseid='.$courseid.'&amp;id='.$mentee->id.'">'.get_string('viewilp', 'block_ilp').'</a>';
			$row = array($picture, fullname($mentee));
			if($CFG->ilpconcern_status_per_student == 1){
				$studentstatus = ilp_get_status($mentee->id);
				$row[] .= '<span class="status-'.$studentstatus[1].'">'.$studentstatus[0].'</span>';
			}
				if (file_exists($CFG->dirroot.'/blocks/ilp/templates/custom/mis_lib.php')) {
					require_once($CFG->dirroot.'/blocks/ilp/templates/custom/mis_lib.php');
					if(function_exists('block_ilp_get_target_grade')) {
						$row[] .= block_ilp_get_target_grade($mentee->id);
					}
					if(function_exists('block_ilp_get_overall_attendance')) {
						$attendance = block_ilp_get_overall_attendance($mentee->id);
						$row[] .= '<span class="attendance-'.$attendance[1].'">'.$attendance[0].'%</span>';
					}
					if(function_exists('block_ilp_get_overall_punctuality')) {
						$punctuality = block_ilp_get_overall_punctuality($mentee->id);
						$row[] .= '<span class="attendance-'.$punctuality[1].'">'.$punctuality[0].'%</span>';
					}	
				}
				if(ilptarget_get_total($mentee->id) > 0){
					$targetrow = '<a href="'.$CFG->wwwroot.'/mod/ilptarget/target_view.php?userid='.$mentee->id.'">'.ilptarget_display_complete($mentee->id).'</a>';
					if(ilptarget_check_new ($USER->id,$mentee->id) == 1) {
						$targetrow .= '<img src="'.$CFG->pixpath.'/i/new.gif" alt="" style="margin-left: 2px" />';
					}
					$row[] .= $targetrow;
				}else{
					$row[] .= '<a href="'.$CFG->wwwroot.'/mod/ilptarget/target_view.php?userid='.$mentee->id.'">'.get_string('norecords','ilptarget').'</a>';
				}
			if($config->ilp_show_concerns == 1) { 
				$i = 1;
				while ($i <= 4){	
					if(eval('return $CFG->ilpconcern_report'.$i.';') == 1) {
					$reviewrow = '<a href="'.$CFG->wwwroot.'/mod/ilpconcern/concerns_view.php?userid='.$mentee->id.'&amp;status='.($i - 1).'">'.ilpconcern_get_total($mentee->id,$i).'</a>';					
					if(ilpconcern_check_new ($USER->id,$mentee->id,$i) == 1) {
						$reviewrow .= '<img src="'.$CFG->pixpath.'/i/new.gif" alt="" style="margin-left: 2px" />';
					}
					if(ilpconcern_get_total($mentee->id,$i)) {
						$reviewrow .= '<br />'.userdate(ilpconcern_get_last_report($mentee->id,$i), get_string('strftimedateshort'));
					}
					$row[] .= $reviewrow;
					}
					$i++;
				}
			}		

			$row[] .= $update;
			
			if($config->ilp_show_subject_reports == 1) { 
				$row[] .= '<a href="'.$CFG->wwwroot.'/blocks/ilp_student_info/view.php?view=subject&amp;courseid='.$courseid.'&amp;id='.$mentee->id.'">'.get_string('subject_report', 'block_ilp').'</a>';
			}
			
			if (file_exists($CFG->dirroot.'/blocks/ilp/templates/custom/mis_lib.php')) {
		    	require_once($CFG->dirroot.'/blocks/ilp/templates/custom/mis_lib.php');
				if(function_exists('block_ilp_get_local_link')) {	
					$row[] .= block_ilp_get_local_link($mentee->id,$courseid);
				}
			}
				
        	$table->add_data($row);
		}

    	$table->print_html();  /// Print the whole table

		/// Mini form for setting user preference

        echo '<br />';

        echo '<form name="options" action="list.php?courseid='.$courseid.'" method="post">';

        echo '<input type="hidden" id="updatepref" name="updatepref" value="1" />';

        echo '<table id="optiontable" align="center">';

        echo '<tr align="right"><td>';

        echo '<label for="perpage">'.get_string('pagesize','ilptarget').'</label>';

        echo ':</td>';

        echo '<td align="left">';

        echo '<input type="text" id="perpage" name="perpage" size="1" value="'.$perpage.'" />';

        helpbutton('pagesize', get_string('pagesize','ilptarget'), 'target');

        echo '</td></tr>';

        echo '<tr>';

        echo '<td colspan="2" align="right">';

        echo '<input type="submit" value="'.get_string('savepreferences').'" />';

        echo '</td></tr></table>';

        echo '</form>';

        }

	 if ($courses) {

            print_heading(get_string('courses'));
			echo '<div class="generalbox" style="width:90%; margin:auto">';
            foreach ($courses as $course) {

?>

<a href="list.php?courseid=<?php echo $course->id ?>"><?php echo $course->fullname ?></a><br />

<?php

            }
			echo '</div>';

        }

        if (!($mentees or $courses)) {

            redirect("view.php",get_string('youarebeingredirectedtoyourown','block_ilp'),0);

        }

    }



    print_footer($course);

?>

