<?PHP



/*

 * @copyright &copy; 2007 University of London Computer Centre

 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk

 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License

 * @package ILP

 * @version 1.0

 */





class block_ilp extends block_base {

    function init() {

        $this->title = get_string('blockname', 'block_ilp');

        $this->version = 2008053102;

    }

	function has_config() {

        return true;

    }

	function config_save($data) {
    // Default behavior: save all variables as $CFG properties
	$module = 'project/ilp';
    foreach ($data as $name => $value) {
        set_config($name, $value, $module);
    }
    return true;
	}


    function get_content() {

        global $CFG,$USER;
 		$module = 'project/ilp';
		$config = get_config($module);
		include_once($CFG->dirroot.'/my/pagelib.php');
		require_once($CFG->dirroot.'/blocks/ilp/block_ilp_lib.php');
        page_id_and_class($id,$class);


        if (empty($this->instance)) {
            $this->content = '';
            return $this->content;
        }

        // the following 3 lines is need to pass _self_test();
        if (empty($this->instance->pageid)) {
            return '';
        }
		$access_isgod = 0 ;
        $access_isteacher = 0 ;
        $access_isstudent = 0 ;
        $access_istutor = 0 ;
        $access_isother = 0 ;
        $access_ismymoodle = 0;
		$access_isguest = 0;

        if($id == PAGE_MY_MOODLE){
            $access_ismymoodle = 1;
        }
		if (has_capability('moodle/site:doanything', get_context_instance(CONTEXT_SYSTEM))) {  // are we god ?
            $access_isgod = 1 ;
        }
        if ($access_ismymoodle || $this->instance->pageid == SITEID || !$currentcontext = get_context_instance(CONTEXT_COURSE, $this->instance->pageid)) {
            $courses = count_records_sql("SELECT course.*
                                    FROM {$CFG->prefix}role_assignments ra,
                                         {$CFG->prefix}role_capabilities rc,
                                         {$CFG->prefix}context c,
                                         {$CFG->prefix}course course
                                    WHERE ra.userid = $USER->id
                                    AND   ra.contextid = c.id
                                    AND   ra.roleid = rc.roleid
                                    AND   rc.capability = 'block/ilp:viewclass'
                                    AND   c.instanceid = course.id
                                    AND   c.contextlevel = ".CONTEXT_COURSE);
            $mentees = count_records_sql("SELECT u.*
                                    FROM {$CFG->prefix}role_assignments ra, {$CFG->prefix}context c, {$CFG->prefix}user u
                                    WHERE ra.userid = $USER->id AND ra.contextid = c.id AND c.instanceid = u.id AND c.contextlevel = ".CONTEXT_USER);
            if($courses > 0) {
                $access_isteacher = 1;
            }elseif($mentees > 0){
                $access_istutor = 1;
            }elseif(has_capability('moodle/legacy:guest', get_context_instance(CONTEXT_SYSTEM), NULL, false)) {
				$access_isguest = 1;
			}elseif(!$access_isgod){
                $access_isstudent = 1;
            }
            $access_isother = 1 ;
        }else{
            if (has_capability('block/ilp:viewclass',$currentcontext)) { // are we the teacher on the course ?
                $access_isteacher = 1 ;
            } elseif (has_capability('block/ilp:view',$currentcontext)) {  // are we a student on the course ?
                $access_isstudent = 1 ;
            }
        }
		$url = ($access_isstudent) ? $CFG->wwwroot.'/blocks/ilp/view.php' : $CFG->wwwroot.'/blocks/ilp/list.php' ;

        if (!$access_isother) {
            $url .= "?courseid=".$this->instance->pageid ;
        }
        $this->content = new object();
		$this->content->text = '';
		if ($access_isstudent) {
			if (file_exists($CFG->dirroot.'/blocks/ilp/templates/custom/mis_lib.php')) {
      			require_once($CFG->dirroot.'/blocks/ilp/templates/custom/mis_lib.php');
				if(function_exists('block_ilp_get_overall_attendance')) {
					$attendance = block_ilp_get_overall_attendance($USER->id);
					$this->content->text .= '<div class="progress-container"><span id="progress_text">'.get_string('attendance','block_ilp').': '.$attendance[0].'%</span><div class="attendance-'.$attendance[1].'" style="width: '.$attendance[0].'%"></div></div>';
				}
				if(function_exists('block_ilp_get_overall_punctuality')) {
					$punctuality = block_ilp_get_overall_punctuality($USER->id);
					$this->content->text .= '<div class="progress-container"><span id="progress_text">'.get_string('punctuality','block_ilp').': '.$punctuality[0].'%</span><div class="attendance-'.$punctuality[1].'" style="width: '.$punctuality[0].'%"></div></div>';
				}	
    		}
			if(ilptarget_get_total($USER->id) > 0){
					$this->content->text .= '<div class="progress-container"><span id="progress_text"><a href="'.$CFG->wwwroot.'/mod/ilptarget/target_view.php?courseid='.$this->instance->pageid.'&amp;userid='.$USER->id.'">'.get_string('modulenameplural','ilptarget').': '.ilptarget_display_complete($USER->id).'</a>';
					if(ilptarget_check_new ($USER->id,$USER->id) == 1) {
						$this->content->text .= '<img src="'.$CFG->pixpath.'/i/new.gif" alt="" style="margin-left: 2px" />';
					}
					$this->content->text .= '</span><div class="attendance-blue" style="width: '.ilptarget_percentage_complete($USER->id).'%;"></div></div>';
			}else{
				$this->content->text .= '<div class="progress-container"><span id="progress_text"><a href="'.$CFG->wwwroot.'/mod/ilptarget/target_view.php?courseid='.$this->instance->pageid.'&amp;userid='.$USER->id.'">'.get_string('norecords','ilptarget').'</a></span><div class="attendance-blue" style="width: 0%;"></div></div>';
			}
			$this->content->text .= '<div class="clearer"></div>';
			$this->content->text .= '<ul class="list">';
			if(!empty($config->ilp_user_guide_link) && $config->ilp_user_guide_link != '0'){
				$this->content->text .= '<li><img src="'.$CFG->pixpath.'/i/info.gif" class="icon" alt="" /><a href="'.$config->ilp_user_guide_link.'" target="newWin">'.get_string('userguide','block_ilp').'</a></li>';
			}
			$this->content->text .= '<li><img src="'.$CFG->pixpath.'/i/users.gif" class="icon" alt="" /><a href="'.$url.'">'.get_string('viewmyilp','block_ilp').'</a></li>';
			$studentstatus = ilp_get_status($USER->id);
			$this->content->text .= '<li><span class="status-'.$studentstatus[1].'">'.get_string('mystudentstatus', 'ilpconcern').': '.$studentstatus[0].'</span></li>';
			if($config->ilp_show_concerns == 1) { 
				$i = 1;
				while ($i <= 4){	
					if(eval('return $CFG->ilpconcern_report'.$i.';') == 1) {
					$this->content->text .= '<li><img src="'.$CFG->pixpath.'/mod/ilpconcern/icon.gif" class="icon" alt="" /><a href="'.$CFG->wwwroot.'/mod/ilpconcern/concerns_view.php?courseid='.$this->instance->pageid.'&amp;userid='.$USER->id.'&amp;status='.($i - 1).'">'.ilpconcern_get_total($USER->id,$i).' '.get_string('report'.$i.'plural','ilpconcern').'</a>';
					if(ilpconcern_check_new ($USER->id,$USER->id,$i) == 1) {
						$this->content->text .= '<img src="'.$CFG->pixpath.'/i/new.gif" alt="" style="margin-left: 2px" />';
					}
					$this->content->text .= '</li>';
					}
					$i++;
				}
			}
			$this->content->text .= '</ul>';
		}elseif(!$access_isguest){
			$this->content->text .= '<ul class="list">';
			$this->content->text .= '<li><img src="'.$CFG->pixpath.'/i/users.gif" class="icon" alt="" /><a href="'.$url.'">'.get_string('viewilps','block_ilp').'</a></li>';
			if (!$access_ismymoodle && ($this->instance->pageid != SITEID && ($access_isgod || $access_isteacher))) {
				$this->content->text .= '<li><img src="'.$CFG->pixpath.'/mod/ilptarget/icon.gif" class="icon" alt="" /><a href="'.$CFG->wwwroot.'/mod/ilptarget/view_students.php?courseid='.$this->instance->pageid.'">'.get_string('modulenameplural','ilptarget').'</a></li>';	
				$this->content->text .= '<li><img src="'.$CFG->pixpath.'/mod/ilpconcern/icon.gif" class="icon" alt="" /><a href="'.$CFG->wwwroot.'/mod/ilpconcern/view_students.php?courseid='.$this->instance->pageid.'">'.get_string('modulenameplural','ilpconcern').'</a></li>';
			}

			if(!$access_ismymoodle && ($access_isgod || ($access_isteacher && $this->instance->pageid != SITEID))) {
				$this->content->text .= '<li>'.get_string('download_reports','block_ilp').':</li>';
				$this->content->text .= '<li><img src="'.$CFG->pixpath.'/i/users.gif" class="icon" alt="" /><a href="'.$CFG->wwwroot.'/blocks/ilp/reports.php?mode=user&amp;courseid='.$this->instance->pageid.'">'.get_string('user_reports','block_ilp').'</a></li>';
				if($access_isgod) {
					$this->content->text .= '<li><img src="'.$CFG->pixpath.'/i/course.gif" class="icon" alt="" /><a href="'.$CFG->wwwroot.'/blocks/ilp/reports.php?mode=course">'.get_string('course_reports','block_ilp').'</a></li>';
                    $this->content->text .= '<li><img src="'.$CFG->pixpath.'/f/excel.gif" class="icon" alt="" /><a href="'.$CFG->wwwroot.'/blocks/ilp/reports.php?mode=allreviews">'.get_string('allreview_reports','block_ilp').'</a></li>';
                    $this->content->text .= '<li>'.get_string('manage_users','block_ilp').'</li>';
                    $this->content->text .= '<li><img src="'.$CFG->pixpath.'/i/roles.gif" class="icon" alt="" /><a href="'.$CFG->wwwroot.'/blocks/ilp/personal_tutor_bulk.php">'.get_string('personal_tutor_bulk_upload','block_ilp').'</a></li>';
				}
			}
			$this->content->text .= '</ul>';
		}

        $this->content->footer = '';
        return $this->content;



    }





    // my moodle can only have SITEID and it's redundant here, so take it away

    //function applicable_formats() {

     //   return array('all' => true, 'my' => false);

    //}



}



?>

