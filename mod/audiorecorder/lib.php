<?PHP  // $Id: lib.php,v 1.0 2006/11/18 22:35:27 Tang Wei Exp $

/// Library of functions and constants for module Audio Recorder


/**
 * Standard base class for AudioRecorder.
 */
class audiorecorder_base {

    var $cm;
    var $course;
    var $audiorecorder;
    var $straudiorecorder;
    var $straudiorecorders;
    var $strsubmissions;
    var $strlastmodified;
    var $navigation;
    var $pagetitle;
    var $currentgroup;
    var $usehtmleditor;
    var $defaultformat;
    var $context;

    /**
     * Constructor for the base audiorecorder class
     *
     * Constructor for the base audiorecorder class.
     * If cmid is set create the cm, course, audiorecorder objects.
     * If the audiorecorder is hidden and the user is not a teacher then
     * this prints a page header and notice.
     *
     * @param cmid   integer, the current course module id - not set for new audiorecorders
     * @param audiorecorder   object, usually null, but if we have it we pass it to save db access
     * @param cm   object, usually null, but if we have it we pass it to save db access
     * @param course   object, usually null, but if we have it we pass it to save db access
     */
    function audiorecorder_base($cmid=0, $audiorecorder=NULL, $cm=NULL, $course=NULL) {

        global $CFG;

        if ($cmid) {
            if ($cm) {
                $this->cm = $cm;
            } else if (! $this->cm = get_record('course_modules', 'id', $cmid)) {
                error('Course Module ID was incorrect');
            }
            
            $this->context = get_context_instance(CONTEXT_MODULE,$this->cm->id);

            if ($course) {
                $this->course = $course;
            } else if (! $this->course = get_record('course', 'id', $this->cm->course)) {
                error('Course is misconfigured');
            }

            if ($audiorecorder) {
                $this->audiorecorder = $audiorecorder;
            } else if (! $this->audiorecorder = get_record('audiorecorder', 'id', $this->cm->instance)) {
                error('audiorecorder ID was incorrect');
            }

            $this->straudiorecorder = get_string('modulename', 'audiorecorder');
            $this->straudiorecorders = get_string('modulenameplural', 'audiorecorder');
            $this->strsubmissions = get_string('submissions', 'audiorecorder');
            $this->strlastmodified = get_string('lastmodified');
            
            /*

            if ($this->course->category) {
                $this->navigation = "<a target=\"{$CFG->framename}\" href=\"$CFG->wwwroot/course/view.php?id={$this->course->id}\">{$this->course->shortname}</a> -> ".
                                    "<a target=\"{$CFG->framename}\" href=\"index.php?id={$this->course->id}\">$this->straudiorecorders</a> ->";
            } else {
                $this->navigation = "<a target=\"{$CFG->framename}\" href=\"index.php?id={$this->course->id}\">$this->straudiorecorders</a> ->";
            }

            $this->pagetitle = strip_tags($this->course->shortname.': '.$this->straudiorecorder.': '.format_string($this->audiorecorder->name,true));

            if (!$this->cm->visible and !isteacher($this->course->id)) {
                $pagetitle = strip_tags($this->course->shortname.': '.$this->straudiorecorder);
                print_header($pagetitle, $this->course->fullname, "$this->navigation $this->straudiorecorder", 
                             "", "", true, '', navmenu($this->course, $this->cm));
                notice(get_string("activityiscurrentlyhidden"), "$CFG->wwwroot/course/view.php?id={$this->course->id}");
            }
            */
                    // visibility
        $context = get_context_instance(CONTEXT_MODULE, $cmid);
        if (!$this->cm->visible and !has_capability('mod/audiorecorder:view', $context)) {
            $pagetitle = strip_tags($this->course->shortname.': '.$this->strassignment);
            $navigation = build_navigation('', $this->cm);

            print_header($pagetitle, $this->course->fullname, $navigation,
                         "", "", true, '', navmenu($this->course, $this->cm));
            notice(get_string("activityiscurrentlyhidden"), "$CFG->wwwroot/course/view.php?id={$this->course->id}");
        }

            $this->currentgroup = get_current_group($this->course->id);
        }

    /// Set up things for a HTML editor if it's needed
        if ($this->usehtmleditor = can_use_html_editor()) {
            $this->defaultformat = FORMAT_HTML;
        } else {
            $this->defaultformat = FORMAT_MOODLE;
        }
    }

    /**
     * Display the audiorecorder, used by view.php
     *
     * This in turn calls the methods producing individual parts of the page
     */
    function view() {

        add_to_log($this->course->id, "audiorecorder", "view", "view.php?id={$this->cm->id}", 
                   $this->audiorecorder->id, $this->cm->id);

        $this->view_header();

        $this->view_intro();

        $this->view_dates();

        $this->view_feedback();

        $this->view_footer();
    }

    /**
     * Display the header and top of a page
     *
     * (this doesn't change much for audiorecorder types)
     * This is used by the view() method to print the header of view.php but
     * it can be used on other pages in which case the string to denote the
     * page in the navigation trail should be passed as an argument
     *
     * @param $subpage string Description of subpage to be used in navigation trail
     */
    function view_header($subpage='') {

        global $CFG;

        if ($subpage) {
            $extranav = '<a target="'.$CFG->framename.'" href="view.php?id='.$this->cm->id.'">'.
                          format_string($this->audiorecorder->name,true).'</a> -> '.$subpage;
        } else {
            $extranav = ' '.format_string($this->audiorecorder->name,true);
        }

        print_header($this->pagetitle, $this->course->fullname, $this->navigation.$extranav, '', '', 
                     true, update_module_button($this->cm->id, $this->course->id, $this->straudiorecorder), 
                     navmenu($this->course, $this->cm));

        echo '<div class="reportlink">'.$this->submittedlink().'</div>';
    }


    /**
     * Display the audiorecorder intro
     *
     * This will most likely be extended by audiorecorder type plug-ins
     * The default implementation prints the audiorecorder description in a box
     */
    function view_intro() {
        print_simple_box_start('center', '', '', '', 'generalbox', 'intro');
        $formatoptions = new stdClass;
        $formatoptions->noclean = true;
        echo format_text($this->audiorecorder->intro, $this->audiorecorder->format, $formatoptions);
        print_simple_box_end();
    }

    /**
     * Display the audiorecorder dates
     *
     * Prints the audiorecorder start and end dates in a box.
     * This will be suitable for most audiorecorder types
     */
    function view_dates() {
        if (!$this->audiorecorder->timeavailable && !$this->audiorecorder->timedue) {
            return;
        }

        print_simple_box_start('center', '', '', '', 'generalbox', 'dates');
        echo '<table>';
        if ($this->audiorecorder->timeavailable) {
            echo '<tr><td class="c0">'.get_string('availabledate','audiorecorder').':</td>';
            echo '    <td class="c1">'.userdate($this->audiorecorder->timeavailable).'</td></tr>';
        }
        if ($this->audiorecorder->timedue) {
            echo '<tr><td class="c0">'.get_string('duedate','audiorecorder').':</td>';
            echo '    <td class="c1">'.userdate($this->audiorecorder->timedue).'</td></tr>';
        }
        echo '</table>';
        print_simple_box_end();
    }


    /**
     * Display the bottom and footer of a page
     *
     * This default method just prints the footer.
     * This will be suitable for most audiorecorder types
     */
    function view_footer() {
        //print_footer($this->course);
    }

    /**
     * Display the feedback to the student
     *
     * This default method prints the teacher picture and name, date when marked,
     * grade and teacher comment.
     *
     * @param $submission object The submission object or NULL in which case it will be loaded
     */
    function view_feedback($submission=NULL) {
        global $USER;

        if (!$submission) { /// Get submission for this audiorecorder
            $submission = $this->get_submission($USER->id);
        }

        if (empty($submission->timemarked)) {   /// Nothing to show, so print nothing
            return;
        }

    /// We need the teacher info
        if (! $teacher = get_record('user', 'id', $submission->teacher)) {
            error('Could not find the teacher');
        }

    /// Print the feedback
        print_heading(get_string('feedbackfromteacher', 'audiorecorder', $this->course->teacher));

        echo '<table cellspacing="0" class="feedback">';

        echo '<tr>';
        echo '<td class="left picture">';
        print_user_picture($teacher->id, $this->course->id, $teacher->picture);
        echo '</td>';
        echo '<td class="topic">';
        echo '<div class="from">';
        echo '<div class="fullname">'.fullname($teacher).'</div>';
        echo '<div class="time">'.userdate($submission->timemarked).'</div>';
        echo '</div>';
        echo '</td>';
        echo '</tr>';

        echo '<tr>';
        echo '<td class="left side">&nbsp;</td>';
        echo '<td class="content">';
        if ($this->audiorecorder->grade) {
            echo '<div class="grade">';
            echo get_string("grade").': '.$this->display_grade($submission->grade);
            echo '</div>';
            echo '<div class="clearer"></div>';
        }

        echo '<div class="comment">';
        echo format_text($submission->comment, $submission->format);
        echo '</div>';
        echo '</tr>';

        echo '</table>';
    }

    /** 
     * Returns a link with info about the state of the audiorecorder submissions
     *
     * This is used by view_header to put this link at the top right of the page.
     * For teachers it gives the number of submitted audiorecorders with a link
     * For students it gives the time of their submission.
     * This will be suitable for most audiorecorder types.
     * @return string
     */
    function submittedlink() {
        global $USER;

        $submitted = '';

        if (isteacher($this->course->id)) {
            if (!isteacheredit($this->course->id) and (groupmode($this->course, $this->cm) == SEPARATEGROUPS)) {
                $count = $this->count_real_submissions($this->currentgroup);  // Only their group
            } else {
                $count = $this->count_real_submissions();                     // Everyone
            }
            $submitted = '<a href="submissions.php?id='.$this->cm->id.'">'.
                         get_string('viewsubmissions', 'audiorecorder', $count).'</a>';
        } else {
            $sumbmitted = '';
            /*
            if (isset($USER->id)) {
                if ($submission = $this->get_submission($USER->id)) {
                    if ($submission->timemodified) {
                        if ($submission->timemodified <= $this->audiorecorder->timedue || empty($this->audiorecorder->timedue)) {
                            $submitted = '<span class="early">'.userdate($submission->timemodified).'</span>';
                        } else {
                            $submitted = '<span class="late">'.userdate($submission->timemodified).'</span>';
                        }
                    }
                }
            }
            */
        }

        return $submitted;
    }


    /**
     * Print the setup form for the current audiorecorder type
     *
     * Includes common.html and the audiorecorder type's mod.html
     * This will be suitable for all audiorecorder types
     *
     * @param $form object The object used to fill the form
     * @param $action url Default destination for this form
     */
    function setup(&$form, $action='') {
        global $CFG, $THEME;

        if (empty($this->course)) {
            if (! $this->course = get_record("course", "id", $form->course)) {
                error("Course is misconfigured");
            }
        }

        if (empty($action)) {   // Default destination for this form
            $action = $CFG->wwwroot.'/course/mod.php';
        }

        if (empty($form->audiorecordertype)) {
            $form->audiorecordertype = '';
        } else {
            $form->audiorecordertype = clean_param($form->audiorecordertype, PARAM_SAFEDIR);
        }

        if (empty($form->name)) {
            $form->name = '';
        } else {
            $form->name = stripslashes($form->name);
        }

        if (empty($form->intro)) {
            $form->intro = '';
        } else {
            $form->intro = stripslashes($form->intro);
        }

        $strname    = get_string('name');
        $straudiorecorders = get_string('modulenameplural', 'audiorecorder');
        $strheading = empty($form->name) ? get_string("type$form->audiorecordertype",'audiorecorder') : s(format_string(stripslashes($form->name),true));

        print_header($this->course->shortname.': '.$strheading, $this->course->fullname,
                "<a href=\"$CFG->wwwroot/course/view.php?id={$this->course->id}\">{$this->course->shortname} </a> -> ".
                "<a href=\"$CFG->wwwroot/mod/audiorecorder/index.php?id={$this->course->id}\">$straudiorecorders</a> -> $strheading");

        print_simple_box_start('center', '70%');
        print_heading(get_string('type'.$form->audiorecordertype,'audiorecorder'));
        print_simple_box(get_string('help'.$form->audiorecordertype, 'audiorecorder'), 'center');
        include("$CFG->dirroot/mod/audiorecorder/type/common.html");

        include("$CFG->dirroot/mod/audiorecorder/type/".$form->audiorecordertype."/mod.html");
        $this->setup_end(); 
    }

    /**
     * Print the end of the setup form for the current audiorecorder type
     *
     * Includes common_end.html
     */
    function setup_end() {
        global $CFG;

        include($CFG->dirroot.'/mod/audiorecorder/type/common_end.html');

        print_simple_box_end();

        if ($this->usehtmleditor) {
            use_html_editor();
        }

        print_footer($this->course);
    }

    /**
     * Create a new audiorecorder activity
     *
     * Given an object containing all the necessary data,
     * (defined by the form in mod.html) this function
     * will create a new instance and return the id number
     * of the new instance.
     * The due data is added to the calendar
     * This is common to all audiorecorder types.
     *
     * @param $audiorecorder object The data from the form on mod.html
     * @return int The id of the audiorecorder
     */
    function add_instance($audiorecorder) {

        $audiorecorder->timemodified = time();
        if (empty($audiorecorder->dueenable)) {
            $audiorecorder->timedue = 0;
            $audiorecorder->preventlate = 0;
        } else {
            $audiorecorder->timedue = make_timestamp($audiorecorder->dueyear, $audiorecorder->duemonth, 
                                                  $audiorecorder->dueday, $audiorecorder->duehour, 
                                                  $audiorecorder->dueminute);
        }
        if (empty($audiorecorder->availableenable)) {
            $audiorecorder->timeavailable = 0;
        } else {
            $audiorecorder->timeavailable = make_timestamp($audiorecorder->availableyear, $audiorecorder->availablemonth, 
                                                        $audiorecorder->availableday, $audiorecorder->availablehour, 
                                                        $audiorecorder->availableminute);
        }

        if ($returnid = insert_record("audiorecorder", $audiorecorder)) {

            if ($audiorecorder->timedue) {
                $event = NULL;
                $event->name        = $audiorecorder->name;
                $event->description = $audiorecorder->intro;
                $event->courseid    = $audiorecorder->course;
                $event->groupid     = 0;
                $event->userid      = 0;
                $event->modulename  = 'audiorecorder';
                $event->instance    = $returnid;
                $event->eventtype   = 'due';
                $event->timestart   = $audiorecorder->timedue;
                $event->timeduration = 0;

                add_event($event);
            }
        }

        return $returnid;
    }

    /**
     * Deletes an audiorecorder activity
     *
     * Deletes all database records and calendar events for this audiorecorder.
     * @param $audiorecorder object The audiorecorder to be deleted
     * @return boolean False indicates error
     */
    function delete_instance($audiorecorder) {
        $result = true;

        if (! delete_records('audiorecorder_submissions', 'audiorecorder', $audiorecorder->id)) {
            $result = false;
        }

        if (! delete_records('audiorecorder', 'id', $audiorecorder->id)) {
            $result = false;
        }

        if (! delete_records('event', 'modulename', 'audiorecorder', 'instance', $audiorecorder->id)) {
            $result = false;
        }
        
        // Get the cm id to properly clean up the grade_items for this audiorecorder
        // bug 4976
        if (! $cm = get_record('modules', 'name', 'audiorecorder')) {
            $result = false;
        } else {
            if (! delete_records('grade_item', 'modid', $cm->id, 'cminstance', $audiorecorder->id)) {
                $result = false;
            }
        }

        return $result;
    }

    /**
     * Updates a new audiorecorder activity
     *
     * Given an object containing all the necessary data,
     * (defined by the form in mod.html) this function
     * will update the audiorecorder instance and return the id number
     * The due date is updated in the calendar
     * This is common to all audiorecorder types.
     *
     * @param $audiorecorder object The data from the form on mod.html
     * @return int The audiorecorder id
     */
    function update_instance($audiorecorder) {

        $audiorecorder->timemodified = time();
        if (empty($audiorecorder->dueenable)) {
            $audiorecorder->timedue = 0;
            $audiorecorder->preventlate = 0;
        } else {
            $audiorecorder->timedue = make_timestamp($audiorecorder->dueyear, $audiorecorder->duemonth, 
                                                  $audiorecorder->dueday, $audiorecorder->duehour, 
                                                  $audiorecorder->dueminute);
        }
        if (empty($audiorecorder->availableenable)) {
            $audiorecorder->timeavailable = 0;
        } else {
            $audiorecorder->timeavailable = make_timestamp($audiorecorder->availableyear, $audiorecorder->availablemonth, 
                                                        $audiorecorder->availableday, $audiorecorder->availablehour, 
                                                        $audiorecorder->availableminute);
        }

        $audiorecorder->id = $audiorecorder->instance;

        if ($returnid = update_record('audiorecorder', $audiorecorder)) {

            if ($audiorecorder->timedue) {
                $event = NULL;

                if ($event->id = get_field('event', 'id', 'modulename', 'audiorecorder', 'instance', $audiorecorder->id)) {

                    $event->name        = $audiorecorder->name;
                    $event->description = $audiorecorder->intro;
                    $event->timestart   = $audiorecorder->timedue;

                    update_event($event);
                } else {
                    $event = NULL;
                    $event->name        = $audiorecorder->name;
                    $event->description = $audiorecorder->intro;
                    $event->courseid    = $audiorecorder->course;
                    $event->groupid     = 0;
                    $event->userid      = 0;
                    $event->modulename  = 'audiorecorder';
                    $event->instance    = $audiorecorder->id;
                    $event->eventtype   = 'due';
                    $event->timestart   = $audiorecorder->timedue;
                    $event->timeduration = 0;

                    add_event($event);
                }
            } else {
                delete_records('event', 'modulename', 'audiorecorder', 'instance', $audiorecorder->id);
            }
        }

        return $returnid;
    }

    /**
     * Top-level function for handling of submissions called by submissions.php
     *
     * This is for handling the teacher interaction with the grading interface
     * This should be suitable for most audiorecorder types.
     *
     * @param $mode string Specifies the kind of teacher interaction taking place
     */
    function submissions($mode) {
        ///The main switch is changed to facilitate
        ///1) Batch fast grading
        ///2) Skip to the next one on the popup
        ///3) Save and Skip to the next one on the popup
        
        //make user global so we can use the id
        global $USER;
        
        switch ($mode) {
            case 'grade':                         // We are in a popup window grading
                if ($submission = $this->process_feedback()) {
                    //IE needs proper header with encoding
                    print_header(get_string('feedback', 'audiorecorder').':'.format_string($this->audiorecorder->name));
                    print_heading(get_string('changessaved'));
                    print $this->update_main_listing($submission);
                }
                close_window();
                break;

            case 'single':                        // We are in a popup window displaying submission
                $this->display_submission();
                break;

            case 'all':                           // Main window, display everything
                $this->display_submissions();
                break;

            case 'fastgrade':
                ///do the fast grading stuff  - this process should work for all 3 subclasses
                $grading    = false;
                $commenting = false;
                $col        = false;
                if (isset($_POST['comment'])) {
                    $col = 'comment';
                    $commenting = true;
                }
                if (isset($_POST['menu'])) {
                    $col = 'menu';
                    $grading = true;
                }
                if (!$col) {
                    //both comment and grade columns collapsed..
                    $this->display_submissions();            
                    break;
                }
                foreach ($_POST[$col] as $id => $unusedvalue){

                    $id = (int)$id; //clean parameter name
                    if (!$submission = $this->get_submission($id)) {
                        $submission = $this->prepare_new_submission($id);
                        $newsubmission = true;
                    } else {
                        $newsubmission = false;
                    }
                    unset($submission->data1);  // Don't need to update this.
                    unset($submission->data2);  // Don't need to update this.

                    //for fast grade, we need to check if any changes take place
                    $updatedb = false;

                    if ($grading) {
                        $grade = $_POST['menu'][$id];
                        $updatedb = $updatedb || ($submission->grade != $grade);
                        $submission->grade = $grade;
                    } else {
                        if (!$newsubmission) {
                            unset($submission->grade);  // Don't need to update this.
                        }
                    }
                    if ($commenting) {
                        $commentvalue = trim($_POST['comment'][$id]);
                        $updatedb = $updatedb || ($submission->comment != stripslashes($commentvalue));
                        $submission->comment = $commentvalue;
                    } else {
                        unset($submission->comment);  // Don't need to update this.
                    }

                    $submission->teacher    = $USER->id;
                    $submission->mailed     = $updatedb?0:$submission->mailed;//only change if it's an update
                    $submission->timemarked = time();

                    //if it is not an update, we don't change the last modified time etc.
                    //this will also not write into database if no comment and grade is entered.

                    if ($updatedb){
                        if ($newsubmission) {
                            if (!insert_record('audiorecorder_submissions', $submission)) {
                                return false;
                            }
                        } else {
                            if (!update_record('audiorecorder_submissions', $submission)) {
                                return false;
                            }
                        }            
                        //add to log only if updating
                        add_to_log($this->course->id, 'audiorecorder', 'update grades', 
                                   'submissions.php?id='.$this->audiorecorder->id.'&user='.$submission->userid, 
                                   $submission->userid, $this->cm->id);             
                    }
                        
                } 
                print_heading(get_string('changessaved'));
                $this->display_submissions();            
                break;


            case 'next':
                /// We are currently in pop up, but we want to skip to next one without saving.
                ///    This turns out to be similar to a single case
                /// The URL used is for the next submission.
                
                $this->display_submission();
                break;
                
            case 'saveandnext':
                ///We are in pop up. save the current one and go to the next one.
                //first we save the current changes
                if ($submission = $this->process_feedback()) {
                    //print_heading(get_string('changessaved'));
                    $extra_javascript = $this->update_main_listing($submission);
                }
                
                //then we display the next submission
                $this->display_submission($extra_javascript);
                break;
            
            default:
                echo "something seriously is wrong!!";
                break;                    
        }
    }
    
    /**
    * Helper method updating the listing on the main script from popup using javascript
    *
    * @param $submission object The submission whose data is to be updated on the main page
    */
    function update_main_listing($submission) {
        global $SESSION;
        
        $output = '';

        $perpage = get_user_preferences('audiorecorder_perpage', 10);

        $quickgrade = get_user_preferences('audiorecorder_quickgrade', 0);
        
        /// Run some Javascript to try and update the parent page
        $output .= '<script type="text/javascript">'."\n<!--\n";
        if (empty($SESSION->flextable['mod-audiorecorder-submissions']->collapse['comment'])) {
            if ($quickgrade){
                $output.= 'opener.document.getElementById("comment['.$submission->userid.']").value="'
                .trim($submission->comment).'";'."\n";
             } else {
                $output.= 'opener.document.getElementById("com'.$submission->userid.
                '").innerHTML="'.shorten_text(trim(strip_tags($submission->comment)), 15)."\";\n";
            }
        }

        if (empty($SESSION->flextable['mod-audiorecorder-submissions']->collapse['grade'])) {
            //echo optional_param('menuindex');
            if ($quickgrade){
                $output.= 'opener.document.getElementById("menumenu['.$submission->userid.
                ']").selectedIndex="'.optional_param('menuindex', 0, PARAM_INT).'";'."\n";
            } else {
                $output.= 'opener.document.getElementById("g'.$submission->userid.'").innerHTML="'.
                $this->display_grade($submission->grade)."\";\n";
            }            
        }    
        //need to add student's rrs in there too.
        if (empty($SESSION->flextable['mod-audiorecorder-submissions']->collapse['timemodified']) &&
            $submission->timemodified) {
            $output.= 'opener.document.getElementById("ts'.$submission->userid.
                 '").innerHTML="'.addslashes($this->print_student_answer($submission->userid)).userdate($submission->timemodified)."\";\n";
        }
        
        if (empty($SESSION->flextable['mod-audiorecorder-submissions']->collapse['timemarked']) &&
            $submission->timemarked) {
            $output.= 'opener.document.getElementById("tt'.$submission->userid.
                 '").innerHTML="'.userdate($submission->timemarked)."\";\n";
        }
        
        if (empty($SESSION->flextable['mod-audiorecorder-submissions']->collapse['status'])) {
            $output.= 'opener.document.getElementById("up'.$submission->userid.'").className="s1";';
            $buttontext = get_string('update');
            $button = link_to_popup_window ('/mod/audiorecorder/submissions.php?id='.$this->cm->id.'&amp;userid='.$submission->userid.'&amp;mode=single'.'&amp;offset='.optional_param('offset', '', PARAM_INT), 
                      'grade'.$submission->userid, $buttontext, 450, 700, $buttontext, 'none', true, 'button'.$submission->userid);
            $output.= 'opener.document.getElementById("up'.$submission->userid.'").innerHTML="'.addslashes($button).'";';
        }        
        $output .= "\n-->\n</script>";
        return $output;
    }

    /**
     *  Return a grade in user-friendly form, whether it's a scale or not
     *  
     * @param $grade
     * @return string User-friendly representation of grade
     */
    function display_grade($grade) {

        static $scalegrades = array();   // Cache scales for each audiorecorder - they might have different scales!!

        if ($this->audiorecorder->grade >= 0) {    // Normal number
            if ($grade == -1) {
                return '-';
            } else {
                return $grade.' / '.$this->audiorecorder->grade;
            }

        } else {                                // Scale
            if (empty($scalegrades[$this->audiorecorder->id])) {
                if ($scale = get_record('scale', 'id', -($this->audiorecorder->grade))) {
                    $scalegrades[$this->audiorecorder->id] = make_menu_from_list($scale->scale);
                } else {
                    return '-';
                }
            }
            if (isset($scalegrades[$this->audiorecorder->id][$grade])) {
                return $scalegrades[$this->audiorecorder->id][$grade];
            }
            return '-';
        }
    }

    /**
     *  Display a single submission, ready for grading on a popup window
     *
     * This default method prints the teacher info and comment box at the top and
     * the student info and submission at the bottom.
     * This method also fetches the necessary data in order to be able to
     * provide a "Next submission" button.
     * Calls preprocess_submission() to give audiorecorder type plug-ins a chance
     * to process submissions before they are graded
     * This method gets its arguments from the page parameters userid and offset
     */
    function display_submission($extra_javascript = '') {
    
        global $CFG;
        
        $userid = required_param('userid', PARAM_INT);
        $offset = required_param('offset', PARAM_INT);//offset for where to start looking for student.

        if (!$user = get_record('user', 'id', $userid)) {
            error('No such user!');
        }

        if (!$submission = $this->get_submission($user->id)) {
            $submission = $this->prepare_new_submission($userid);
        }

        if ($submission->timemodified > $submission->timemarked) {
            $subtype = 'audiorecordernew';
        } else {
            $subtype = 'audiorecorderold';
        }

        ///construct SQL, using current offset to find the data of the next student
        $course     = $this->course;
        $audiorecorder = $this->audiorecorder;
        $cm         = $this->cm;
    
        if ($groupmode = groupmode($course, $cm)) {   // Groups are being used
            $currentgroup = setup_and_print_groups($course, $groupmode, 'submissions.php?id='.$this->cm->id);
        } else {
            $currentgroup = false;
        }

    /// Get all teachers and students
        if ($currentgroup) {
            $users = get_group_users($currentgroup);
        } else {
            $users = get_course_users($course->id);
        }

        $select = 'SELECT u.id, u.id, u.firstname, u.lastname, u.picture,'.
                  's.id AS submissionid, s.grade, s.comment, s.timemodified, s.timemarked, ((s.timemarked > 0) AND (s.timemarked >= s.timemodified)) AS status ';
        $sql = 'FROM '.$CFG->prefix.'user u '.
               'LEFT JOIN '.$CFG->prefix.'audiorecorder_submissions s ON u.id = s.userid AND s.audiorecorder = '.$this->audiorecorder->id.' '.
               'WHERE u.id IN ('.implode(',', array_keys($users)).') ';
               
        require_once($CFG->libdir.'/tablelib.php');
        if($sort = flexible_table::get_sql_sort('mod-audiorecorder-submissions')) {
            $sort = 'ORDER BY '.$sort.' ';
        }

        $limit = sql_paging_limit($offset+1, 1);

        $nextid = 0;
        if (($auser = get_record_sql($select.$sql.$sort.$limit, false, true)) !== false) {
            $nextid = $auser->id;
        }

        print_header(get_string('feedback', 'audiorecorder').':'.fullname($user, true).':'.format_string($this->audiorecorder->name));

        /// Print any extra javascript needed for saveandnext
        echo $extra_javascript;

        ///SOme javascript to help with setting up >.>
        
        echo '<script type="text/javascript">'."\n";
        echo 'function setNext(){'."\n";
        echo 'document.submitform.mode.value=\'next\';'."\n";
        echo 'document.submitform.userid.value="'.$nextid.'";'."\n";
        echo '}'."\n";
        
        echo 'function saveNext(){'."\n";
        echo 'document.submitform.mode.value=\'saveandnext\';'."\n";
        echo 'document.submitform.userid.value="'.$nextid.'";'."\n";
        echo 'document.submitform.saveuserid.value="'.$userid.'";'."\n";
        echo 'document.submitform.menuindex.value = document.submitform.grade.selectedIndex;'."\n";
        echo '}'."\n";
            
        echo '</script>'."\n";
        echo '<table cellspacing="0" class="feedback '.$subtype.'" >';

        ///Start of teacher info row

        echo '<tr>';
        echo '<td width="35" valign="top" class="picture teacher">';
        if ($submission->teacher) {
            $teacher = get_record('user', 'id', $submission->teacher);
        } else {
            global $USER;
            $teacher = $USER;
        }
        print_user_picture($teacher->id, $this->course->id, $teacher->picture);
        echo '</td>';
        echo '<td class="content">';
        echo '<form name="submitform" action="submissions.php" method="post">';
        echo '<input type="hidden" name="offset" value="'.++$offset.'">';
        echo '<input type="hidden" name="userid" value="'.$userid.'" />';
        echo '<input type="hidden" name="id" value="'.$this->cm->id.'" />';
        echo '<input type="hidden" name="mode" value="grade" />';
        echo '<input type="hidden" name="menuindex" value="0" />';//selected menu index
        
        //new hidden field, initialized to -1.
        echo '<input type="hidden" name="saveuserid" value="-1" />';
        if ($submission->timemarked) {
            echo '<div class="from">';
            echo '<div class="fullname">'.fullname($teacher, true).'</div>';
            echo '<div class="time">'.userdate($submission->timemarked).'</div>';
            echo '</div>';
        }
        echo '<div class="grade">'.get_string('grade').':';
        choose_from_menu(make_grades_menu($this->audiorecorder->grade), 'grade', $submission->grade, get_string('nograde'), '', -1);
        echo '</div>';
        echo '<div class="clearer"></div>';

        $this->preprocess_submission($submission);

        echo '<br />';
        print_textarea($this->usehtmleditor, 14, 58, 0, 0, 'comment', $submission->comment, $this->course->id);

        if ($this->usehtmleditor) { 
            echo '<input type="hidden" name="format" value="'.FORMAT_HTML.'" />';
        } else {
            echo '<div align="right" class="format">';
            choose_from_menu(format_text_menu(), "format", $submission->format, "");
            helpbutton("textformat", get_string("helpformatting"));
            echo '</div>';
        }

        ///Print Buttons in Single View
        echo '<div class="buttons" align="center">';
        echo '<input type="submit" name="submit" value="'.get_string('savechanges').'" onclick = "document.submitform.menuindex.value = document.submitform.grade.selectedIndex" />';
        echo '<input type="submit" name="cancel" value="'.get_string('cancel').'" />';
        //if there are more to be graded.
        if ($nextid) {
            echo '<input type="submit" name="saveandnext" value="'.get_string('saveandnext').'" onclick="saveNext()" />';
            echo '<input type="submit" name="next" value="'.get_string('next').'" onclick="setNext();" />';
        }
        echo '</div>';
        echo '</form>';
        echo '</td></tr>';
        
        ///End of teacher info row, Start of student info row
        echo '<tr>';
        echo '<td width="35" valign="top" class="picture user">';
        print_user_picture($user->id, $this->course->id, $user->picture);
        echo '</td>';
        echo '<td class="topic">';
        echo '<div class="from">';
        echo '<div class="fullname">'.fullname($user, true).'</div>';
        if ($submission->timemodified) {
            echo '<div class="time">'.userdate($submission->timemodified).
                                     $this->display_lateness($submission->timemodified).'</div>';
        }
        echo '</div>';
        $this->print_user_files($user->id);
        echo '</td>';
        echo '</tr>';
        
        ///End of student info row
        
        echo '</table>';

        if ($this->usehtmleditor) {
            use_html_editor();
        }

        print_footer('none');
    }

    /**
     *  Preprocess submission before grading
     *
     * Called by display_submission()
     * The default type does nothing here.
     * @param $submission object The submission object
     */
    function preprocess_submission(&$submission) {
    }

    /**
     *  Display all the submissions ready for grading
     */
    function display_submissions() {

        global $CFG, $db, $USER;

        /* first we check to see if the form has just been submitted
         * to request user_preference updates
         */
         
        if (isset($_POST['updatepref'])){
            $perpage = optional_param('perpage', 10, PARAM_INT);
            $perpage = ($perpage <= 0) ? 10 : $perpage ;
            set_user_preference('audiorecorder_perpage', $perpage);
            set_user_preference('audiorecorder_quickgrade', optional_param('quickgrade',0, PARAM_BOOL));
        }

        /* next we get perpage and quickgrade (allow quick grade) params 
         * from database
         */
        $perpage    = get_user_preferences('audiorecorder_perpage', 10);
        $quickgrade = get_user_preferences('audiorecorder_quickgrade', 0);
        
        $teacherattempts = true; /// Temporary measure
        $page    = optional_param('page', 0, PARAM_INT);
        $strsaveallfeedback = get_string('saveallfeedback', 'audiorecorder');

    /// Some shortcuts to make the code read better
        
        $course     = $this->course;
        $audiorecorder = $this->audiorecorder;
        $cm         = $this->cm;
        
        $tabindex = 1; //tabindex for quick grading tabbing; Not working for dropdowns yet

        add_to_log($course->id, 'audiorecorder', 'view submission', 'submissions.php?id='.$this->audiorecorder->id, $this->audiorecorder->id, $this->cm->id);
        
        print_header_simple(format_string($this->audiorecorder->name,true), "", '<a href="index.php?id='.$course->id.'">'.$this->straudiorecorders.'</a> -> <a href="view.php?a='.$this->audiorecorder->id.'">'.format_string($this->audiorecorder->name,true).'</a> -> '. $this->strsubmissions, '', '', true, update_module_button($cm->id, $course->id, $this->straudiorecorder), navmenu($course, $cm));
    
    ///Position swapped
        if ($groupmode = groupmode($course, $cm)) {   // Groups are being used
            $currentgroup = setup_and_print_groups($course, $groupmode, 'submissions.php?id='.$this->cm->id);
        } else {
            $currentgroup = false;
        }

    /// Get all teachers and students
        if ($currentgroup) {
            $users = get_group_users($currentgroup);
        } else {
            $users = get_course_users($course->id);
        }

        $tablecolumns = array('picture', 'fullname', 'grade', 'comment', 'timemodified', 'timemarked', 'status');
        $tableheaders = array('', get_string('fullname'), get_string('grade'), get_string('comment', 'audiorecorder'), get_string('lastmodified').' ('.$course->student.')', get_string('lastmodified').' ('.$course->teacher.')', get_string('status'));

        require_once($CFG->libdir.'/tablelib.php');
        $table = new flexible_table('mod-audiorecorder-submissions');
                        
        $table->define_columns($tablecolumns);
        $table->define_headers($tableheaders);
        $table->define_baseurl($CFG->wwwroot.'/mod/audiorecorder/submissions.php?id='.$this->cm->id.'&amp;currentgroup='.$currentgroup);
                
        $table->sortable(true, 'lastname');//sorted by lastname by default
        $table->collapsible(true);
        $table->initialbars(true);
        
        $table->column_suppress('picture');
        $table->column_suppress('fullname');
        
        $table->column_class('picture', 'picture');
        $table->column_class('fullname', 'fullname');
        $table->column_class('grade', 'grade');
        $table->column_class('comment', 'comment');
        $table->column_class('timemodified', 'timemodified');
        $table->column_class('timemarked', 'timemarked');
        $table->column_class('status', 'status');
        
        $table->set_attribute('cellspacing', '0');
        $table->set_attribute('id', 'attempts');
        $table->set_attribute('class', 'submissions');
        $table->set_attribute('width', '90%');
        $table->set_attribute('align', 'center');
            
        // Start working -- this is necessary as soon as the niceties are over
        $table->setup();

    /// Check to see if groups are being used in this audiorecorder

        if (!$teacherattempts) {
            $teachers = get_course_teachers($course->id);
            if (!empty($teachers)) {
                $keys = array_keys($teachers);
            }
            foreach ($keys as $key) {
                unset($users[$key]);
            }
        }
        
        if (empty($users)) {
            print_heading(get_string('noattempts','audiorecorder'));
            return true;
        }

    /// Construct the SQL

        if ($where = $table->get_sql_where()) {
            $where .= ' AND ';
        }

        if ($sort = $table->get_sql_sort()) {
            $sort = ' ORDER BY '.$sort;
        }

        $select = 'SELECT u.id, u.id, u.firstname, u.lastname, u.picture, s.id AS submissionid, s.grade, s.comment, s.timemodified, s.timemarked, ((s.timemarked > 0) AND (s.timemarked >= s.timemodified)) AS status ';
        $sql = 'FROM '.$CFG->prefix.'user u '.
               'LEFT JOIN '.$CFG->prefix.'audiorecorder_submissions s ON u.id = s.userid AND s.audiorecorder = '.$this->audiorecorder->id.' '.
               'WHERE '.$where.'u.id IN ('.implode(',', array_keys($users)).') ';
    
        $table->pagesize($perpage, count($users));
        
        if($table->get_page_start() !== '' && $table->get_page_size() !== '') {
            $limit = ' '.sql_paging_limit($table->get_page_start(), $table->get_page_size());     
        }
        else {
            $limit = '';
        }
    
        ///offset used to calculate index of student in that particular query, needed for the pop up to know who's next
        $offset = $page * $perpage;
        
        $strupdate = get_string('update');
        $strgrade  = get_string('grade');
        $grademenu = make_grades_menu($this->audiorecorder->grade);

        if (($ausers = get_records_sql($select.$sql.$sort.$limit)) !== false) {
            
            foreach ($ausers as $auser) {
                $picture = print_user_picture($auser->id, $course->id, $auser->picture, false, true);
                
                if (empty($auser->submissionid)) {
                    $auser->grade = -1; //no submission yet
                }
                    
                if (!empty($auser->submissionid)) {
                ///Prints student answer and student modified date
                ///attach file or print link to student answer, depending on the type of the audiorecorder.
                ///Refer to print_student_answer in inherited classes.     
                    if ($auser->timemodified > 0) {            
                        $studentmodified = '<div id="ts'.$auser->id.'">'.$this->print_student_answer($auser->id).userdate($auser->timemodified).'</div>';
                    } else {
                        $studentmodified = '<div id="ts'.$auser->id.'">&nbsp;</div>';
                    }
                ///Print grade, dropdown or text
                    if ($auser->timemarked > 0) {
                        $teachermodified = '<div id="tt'.$auser->id.'">'.userdate($auser->timemarked).'</div>';
                        
                        if ($quickgrade) {
                            $grade = '<div id="g'.$auser->id.'">'.choose_from_menu(make_grades_menu($this->audiorecorder->grade), 
                            'menu['.$auser->id.']', $auser->grade, get_string('nograde'),'',-1,true,false,$tabindex++).'</div>';
                        } else {
                            $grade = '<div id="g'.$auser->id.'">'.$this->display_grade($auser->grade).'</div>';
                        }

                    } else {
                        $teachermodified = '<div id="tt'.$auser->id.'">&nbsp;</div>';
                        if ($quickgrade){                    
                            $grade = '<div id="g'.$auser->id.'">'.choose_from_menu(make_grades_menu($this->audiorecorder->grade), 
                            'menu['.$auser->id.']', $auser->grade, get_string('nograde'),'',-1,true,false,$tabindex++).'</div>';
                        } else {
                            $grade = '<div id="g'.$auser->id.'">'.$this->display_grade($auser->grade).'</div>';
                        }
                    }
                ///Print Comment
                    if ($quickgrade){
                        $comment = '<div id="com'.$auser->id.'"><textarea tabindex="'.$tabindex++.'" name="comment['.$auser->id.']" id="comment['.$auser->id.']">'.($auser->comment).'</textarea></div>';
                    } else {
                        $comment = '<div id="com'.$auser->id.'">'.shorten_text(strip_tags($auser->comment),15).'</div>';
                    }
                } else {
                    $studentmodified = '<div id="ts'.$auser->id.'">&nbsp;</div>';
                    $teachermodified = '<div id="tt'.$auser->id.'">&nbsp;</div>';
                    $status          = '<div id="st'.$auser->id.'">&nbsp;</div>';
                    if ($quickgrade){   // allow editing
                        $grade = '<div id="g'.$auser->id.'">'.choose_from_menu(make_grades_menu($this->audiorecorder->grade), 
                                 'menu['.$auser->id.']', $auser->grade, get_string('nograde'),'',-1,true,false,$tabindex++).'</div>';
                    } else {
                        $grade = '<div id="g'.$auser->id.'">-</div>';
                    }
                    if ($quickgrade){
                        $comment = '<div id="com'.$auser->id.'"><textarea tabindex="'.$tabindex++.'" name="comment['.$auser->id.']" id="comment['.$auser->id.']">'.($auser->comment).'</textarea></div>';
                    } else {
                        $comment = '<div id="com'.$auser->id.'">&nbsp;</div>';
                    }
                }

                if ($auser->status === NULL) {
                    $auser->status = 0;
                }

                $buttontext = ($auser->status == 1) ? $strupdate : $strgrade;
                                   
                ///No more buttons, we use popups ;-).
                $button = link_to_popup_window ('/mod/audiorecorder/submissions.php?id='.$this->cm->id.'&amp;userid='.$auser->id.'&amp;mode=single'.'&amp;offset='.$offset++, 
                                                'grade'.$auser->id, $buttontext, 500, 780, $buttontext, 'none', true, 'button'.$auser->id);

                $status  = '<div id="up'.$auser->id.'" class="s'.$auser->status.'">'.$button.'</div>';
                
                $row = array($picture, fullname($auser), $grade, $comment, $studentmodified, $teachermodified, $status);
                $table->add_data($row);
            }
        }
        
        /// Print quickgrade form around the table
        if ($quickgrade){
            echo '<form action="submissions.php" name="fastg" method="post">';
            echo '<input type="hidden" name="id" value="'.$this->cm->id.'">';
            echo '<input type="hidden" name="mode" value="fastgrade">';
            echo '<input type="hidden" name="page" value="'.$page.'">';
            echo '<p align="center"><input type="submit" name="fastg" value="'.get_string('saveallfeedback', 'audiorecorder').'" /></p>';
        }

        $table->print_html();  /// Print the whole table

        if ($quickgrade){
            echo '<p align="center"><input type="submit" name="fastg" value="'.get_string('saveallfeedback', 'audiorecorder').'" /></p>';
            echo '</form>';
        }
        /// End of fast grading form
        
        /// Mini form for setting user preference
        echo '<br />';
        echo '<form name="options" action="submissions.php?id='.$this->cm->id.'" method="post">';
        echo '<input type="hidden" id="updatepref" name="updatepref" value="1" />';
        echo '<table id="optiontable" align="center">';
        echo '<tr align="right"><td>';
        echo '<label for="perpage">'.get_string('pagesize','audiorecorder').'</label>';
        echo ':</td>';
        echo '<td align="left">';
        echo '<input type="text" id="perpage" name="perpage" size="1" value="'.$perpage.'" />';
        helpbutton('pagesize', get_string('pagesize','audiorecorder'), 'audiorecorder');
        echo '</td></tr>';
        echo '<tr align="right">';
        echo '<td>';
        print_string('quickgrade','audiorecorder');
        echo ':</td>';
        echo '<td align="left">';
        if ($quickgrade){
            echo '<input type="checkbox" name="quickgrade" value="1" checked="checked" />';
        } else {
            echo '<input type="checkbox" name="quickgrade" value="1" />';
        }
        helpbutton('quickgrade', get_string('quickgrade', 'audiorecorder'), 'audiorecorder').'</p></div>';
        echo '</td></tr>';
        echo '<tr>';
        echo '<td colspan="2" align="right">';
        echo '<input type="submit" value="'.get_string('savepreferences').'" />';
        echo '</td></tr></table>';
        echo '</form>';
        ///End of mini form
        print_footer($this->course);
    }

    /**
     *  Process teacher feedback submission
     *
     * This is called by submissions() when a grading even has taken place.
     * It gets its data from the submitted form.
     * @return object The updated submission object
     */
    function process_feedback() {

        global $USER;

        if (!$feedback = data_submitted()) {      // No incoming data?
            return false;
        }

        ///For save and next, we need to know the userid to save, and the userid to go
        ///We use a new hidden field in the form, and set it to -1. If it's set, we use this
        ///as the userid to store
        if ((int)$feedback->saveuserid !== -1){
            $feedback->userid = $feedback->saveuserid;
        }

        if (!empty($feedback->cancel)) {          // User hit cancel button
            return false;
        }

        $submission = $this->get_submission($feedback->userid, true);  // Get or make one

        $submission->grade      = $feedback->grade;
        $submission->comment    = $feedback->comment;
        $submission->format     = $feedback->format;
        $submission->teacher    = $USER->id;
        $submission->mailed     = 0;       // Make sure mail goes out (again, even)
        $submission->timemarked = time();

        unset($submission->data1);  // Don't need to update this.
        unset($submission->data2);  // Don't need to update this.

        if (empty($submission->timemodified)) {   // eg for offline audiorecorders
            $submission->timemodified = time();
        }

        if (! update_record('audiorecorder_submissions', $submission)) {
            return false;
        }

        add_to_log($this->course->id, 'audiorecorder', 'update grades', 
                   'submissions.php?id='.$this->audiorecorder->id.'&user='.$feedback->userid, $feedback->userid, $this->cm->id);
        
        return $submission;

    }

    /**
     * Load the submission object for a particular user
     *
     * @param $userid int The id of the user whose submission we want or 0 in which case USER->id is used
     * @param $createnew boolean optional Defaults to false. If set to true a new submission object will be created in the database
     * @return object The submission
     */
    function get_submission($userid=0, $createnew=false) {
        global $USER;

        if (empty($userid)) {
            $userid = $USER->id;
        }

        $submission = get_record('audiorecorder_submissions', 'audiorecorder', $this->audiorecorder->id, 'userid', $userid);

        if ($submission || !$createnew) {
            return $submission;
        }
        $newsubmission = $this->prepare_new_submission($userid);
        if (!insert_record("audiorecorder_submissions", $newsubmission)) {
            error("Could not insert a new empty submission");
        }

        return get_record('audiorecorder_submissions', 'audiorecorder', $this->audiorecorder->id, 'userid', $userid);
    }
    
        /**
     * Return all audiorecorder submissions by ENROLLED students (even empty)
     *
     * @param $sort string optional field names for the ORDER BY in the sql query
     * @param $dir string optional specifying the sort direction, defaults to DESC
     * @return array The submission objects indexed by id
     */
    function get_submissions($sort='', $dir='DESC') {
        return audiorecorder_get_all_submissions($this->audiorecorder, $sort, $dir);
    }

    /**
     * Counts all real audiorecorder submissions by ENROLLED students (not empty ones)
     *
     * @param $groupid int optional If nonzero then count is restricted to this group
     * @return int The number of submissions
     */
    function count_real_submissions($groupid=0) {
        return audiorecorder_count_real_submissions($this->audiorecorder, $groupid);
    }
    

    /**
     * Instantiates a new submission object for a given user
     *
     * Sets the audiorecorder, userid and times, everything else is set to default values.
     * @param $userid int The userid for which we want a submission object
     * @return object The submission
     */
    function prepare_new_submission($userid) {
        $submission = new Object; 
        $submission->audiorecorder   = $this->audiorecorder->id;
        $submission->userid       = $userid;
        $submission->timecreated  = time();
        $submission->timemodified = $submission->timecreated;
        $submission->numfiles     = 0;
        $submission->data1        = '';
        $submission->data2        = '';
        $submission->grade        = -1;
        $submission->comment      = '';
        $submission->format       = 0;
        $submission->teacher      = 0;
        $submission->timemarked   = 0;
        $submission->mailed       = 0;
        return $submission;
    }
    /**
     * Alerts teachers by email of new or changed audiorecorders that need grading
     *
     * First checks whether the option to email teachers is set for this audiorecorder.
     * Sends an email to ALL teachers in the course (or in the group if using separate groups).
     * Uses the methods email_teachers_text() and email_teachers_html() to construct the content.
     * @param $submission object The submission that has changed
     */
    function email_teachers($submission) {
        global $CFG;

        if (empty($this->audiorecorder->emailteachers)) {          // No need to do anything
            return;
        }

        $user = get_record('user', 'id', $submission->userid);

        if (groupmode($this->course, $this->cm) == SEPARATEGROUPS) {   // Separate groups are being used
            if ($groups = user_group($this->course->id, $user->id)) {  // Try to find groups
                $teachers = array();
                foreach ($groups as $group) {
                    $teachers = array_merge($teachers, get_group_teachers($this->course->id, $group->id));
                }
            } else {
                $teachers = get_group_teachers($this->course->id, 0);   // Works even if not in group
            }
        } else {
            $teachers = get_course_teachers($this->course->id);
        }

        if ($teachers) {

            $straudiorecorders = get_string('modulenameplural', 'audiorecorder');
            $straudiorecorder  = get_string('modulename', 'audiorecorder');
            $strsubmitted  = get_string('submitted', 'audiorecorder');

            foreach ($teachers as $teacher) {
                unset($info);
                $info->username = fullname($user);
                $info->audiorecorder = format_string($this->audiorecorder->name,true);
                $info->url = $CFG->wwwroot.'/mod/audiorecorder/submissions.php?id='.$this->cm->id;

                $postsubject = $strsubmitted.': '.$info->username.' -> '.$this->audiorecorder->name;
                $posttext = $this->email_teachers_text($info);
                $posthtml = ($teacher->mailformat == 1) ? $this->email_teachers_html($info) : '';

                @email_to_user($teacher, $user, $postsubject, $posttext, $posthtml);  // If it fails, oh well, too bad.
            }
        }
    }

    /**
     * Creates the text content for emails to teachers
     *
     * @param $info object The info used by the 'emailteachermail' language string
     * @return string
     */
    function email_teachers_text($info) {
        $posttext  = $this->course->shortname.' -> '.$this->straudiorecorders.' -> '.
                     format_string($this->audiorecorder->name, true)."\n";
        $posttext .= '---------------------------------------------------------------------'."\n";
        $posttext .= get_string("emailteachermail", "audiorecorder", $info)."\n";
        $posttext .= "\n---------------------------------------------------------------------\n";
        return $posttext;
    }

     /**
     * Creates the html content for emails to teachers
     *
     * @param $info object The info used by the 'emailteachermailhtml' language string
     * @return string
     */
    function email_teachers_html($info) {
        global $CFG;
        $posthtml  = '<p><font face="sans-serif">'.
                     '<a href="'.$CFG->wwwroot.'/course/view.php?id='.$this->course->id.'">'.$this->course->shortname.'</a> ->'.
                     '<a href="'.$CFG->wwwroot.'/mod/audiorecorder/index.php?id='.$this->course->id.'">'.$this->straudiorecorders.'</a> ->'.
                     '<a href="'.$CFG->wwwroot.'/mod/audiorecorder/view.php?id='.$this->cm->id.'">'.format_string($this->audiorecorder->name,true).'</a></font></p>';
        $posthtml .= '<hr /><font face="sans-serif">';
        $posthtml .= '<p>'.get_string('emailteachermailhtml', 'audiorecorder', $info).'</p>';
        $posthtml .= '</font><hr />';
        return $posthtml;
    }

    
    /**
     * Produces a list of links to the files uploaded by a user
     *
     * @param $userid int optional id of the user. If 0 then $USER->id is used.
     * @param $return boolean optional defaults to false. If true the list is returned rather than printed
     * @return string optional
     */
    function print_user_files($userid=0, $return=false) {
        global $CFG, $USER;
    
        if (!$userid) {
            if (!isloggedin()) {
                return '';
            }
            $userid = $USER->id;
        }
    
        $filearea = $this->file_area_name($userid);

        $output = '';
    
        if ($basedir = $this->file_area($userid)) {
            if ($files = get_directory_list($basedir)) {
                require_once($CFG->libdir.'/filelib.php');
                foreach ($files as $key => $file) {
                    
                    $icon = mimeinfo('icon', $file);
                    
                    if ($CFG->slasharguments) {
                        $ffurl = "$CFG->wwwroot/file.php/$filearea/$file";
                    } else {
                        $ffurl = "$CFG->wwwroot/file.php?file=/$filearea/$file";
                    }
                
                    $output .= '<img align="middle" src="'.$CFG->pixpath.'/f/'.$icon.'" height="16" width="16" alt="'.$icon.'" />'.
                            '<a href="'.$ffurl.'" >'.$file.'</a><br />';
                }
            }
        }

        $output = '<div class="files">'.$output.'</div>';

        if ($return) {
            return $output;
        }
        echo $output;
    }

    /**
     * Count the files uploaded by a given user
     *
     * @param $userid int The user id
     * @return int
     */
    function count_user_files($userid) {
        global $CFG;

        $filearea = $this->file_area_name($userid);

        if ($basedir = $this->file_area($userid)) {
            if ($files = get_directory_list($basedir)) {
                return count($files);
            }
        }
        return 0;
    }

    /**
     * Creates a directory file name, suitable for make_upload_directory()
     *
     * @param $userid int The user id
     * @return string path to file area
     */
    function file_area_name($userid) {
        global $CFG;
    
        return $this->course->id.'/'.$CFG->moddata.'/audiorecorder/'.$this->audiorecorder->id.'/'.$userid;
    }

    /**
     * Makes an upload directory
     *
     * @param $userid int The user id
     * @return string path to file area.
     */
    function file_area($userid) {
        return make_upload_directory( $this->file_area_name($userid) );
    }

    /**
     * Returns true if the student is allowed to submit
     *
     * Checks that the audiorecorder has started and, if the option to prevent late
     * submissions is set, also checks that the audiorecorder has not yet closed.
     * @return boolean
     */
    function isopen() {
        $time = time();
        if ($this->audiorecorder->preventlate && $this->audiorecorder->timedue) {
            return ($this->audiorecorder->timeavailable <= $time && $time <= $this->audiorecorder->timedue);
        } else {
            return ($this->audiorecorder->timeavailable <= $time);
        }
    }
     function user_complete($user) {
        if ($submission = $this->get_submission($user->id)) {
            if ($basedir = $this->file_area($user->id)) {
                if ($files = get_directory_list($basedir)) {
                    $countfiles = count($files)." ".get_string("uploadedfiles", "audiorecorder");
                    foreach ($files as $file) {
                        $countfiles .= "; $file";
                    }
                }
            }
    
            print_simple_box_start();
            echo get_string("lastmodified").": ";
            echo userdate($submission->timemodified);
            echo $this->display_lateness($submission->timemodified);
    
            $this->print_user_files($user->id);
    
            echo '<br />';
    
            if (empty($submission->timemarked)) {
                print_string("notgradedyet", "audiorecorder");
            } else {
                $this->view_feedback($submission);
            }
    
            print_simple_box_end();
    
        } else {
            print_string("notsubmittedyet", "audiorecorder");
        }
    }

    /**
     * Return a string indicating how late a submission is
     *
     * @param $timesubmitted int 
     * @return string
     */
    function display_lateness($timesubmitted) {
        return audiorecorder_display_lateness($timesubmitted, $this->audiorecorder->timedue);
    }


} ////// End of the audiorecorder_base class



//===================OTHER FUNCTIONs================================--
/**
 * Return all audiorecorder submissions by ENROLLED students (even empty)
 *
 * There are also audiorecorder type methods get_submissions() wich in the default
 * implementation simply call this function.
 * @param $sort string optional field names for the ORDER BY in the sql query
 * @param $dir string optional specifying the sort direction, defaults to DESC
 * @return array The submission objects indexed by id
 */
function audiorecorder_get_all_submissions($audiorecorder, $sort="", $dir="DESC") {
/// Return all audiorecorder submissions by ENROLLED students (even empty)
    global $CFG;

    if ($sort == "lastname" or $sort == "firstname") {
        $sort = "u.$sort $dir";
    } else if (empty($sort)) {
        $sort = "a.timemodified DESC";
    } else {
        $sort = "a.$sort $dir";
    }

    return get_records_sql("SELECT a.* 
                              FROM {$CFG->prefix}audiorecorder_submissions a, 
                                   {$CFG->prefix}user u
                             WHERE u.id = a.userid
                               AND a.audiorecorder = '$audiorecorder->id' 
                          ORDER BY $sort");
    /*                      
    $select = "s.course = '$audiorecorder->course' AND";
    if ($audiorecorder->course == SITEID) {
        $select = '';
    }
    return get_records_sql("SELECT a.* 
                              FROM {$CFG->prefix}audiorecorder_submissions a, 
                                   {$CFG->prefix}user_students s,
                                   {$CFG->prefix}user u
                             WHERE a.userid = s.userid
                               AND u.id = a.userid
                               AND $select a.audiorecorder = '$audiorecorder->id' 
                          ORDER BY $sort");
    */
}

/**
 * Counts all real audiorecorder submissions by ENROLLED students (not empty ones)
 *
 * There are also audiorecorder type methods count_real_submissions() wich in the default
 * implementation simply call this function.
 * @param $groupid int optional If nonzero then count is restricted to this group
 * @return int The number of submissions
 */
function audiorecorder_count_real_submissions($audiorecorder, $groupid=0) {
    global $CFG;

    if ($groupid) {     /// How many in a particular group?
        return count_records_sql("SELECT COUNT(DISTINCT g.userid, g.groupid)
                                     FROM {$CFG->prefix}audiorecorder_submissions a,
                                          {$CFG->prefix}groups_members g
                                    WHERE a.audiorecorder = $audiorecorder->id 
                                      AND a.timemodified > 0
                                      AND g.groupid = '$groupid' 
                                      AND a.userid = g.userid ");
    } else {
        $cm = get_coursemodule_from_instance('audiorecorder', $audiorecorder->id);
        $context = get_context_instance(CONTEXT_MODULE, $cm->id);

        // this is all the users with this capability set, in this context or higher
        if ($users = get_users_by_capability($context, 'mod/audiorecorder:submit')) {
            foreach ($users as $user) {
                $array[] = $user->id;
            }

            $userlists = '('.implode(',',$array).')';

            return count_records_sql("SELECT COUNT(*)
                                      FROM {$CFG->prefix}audiorecorder_submissions
                                     WHERE audiorecorder = '$audiorecorder->id' 
                                       AND timemodified > 0
                                       AND userid IN $userlists ");
        } else {
            return 0; // no users enroled in course
        }
        
    }
}

/**
 * Return an array of grades, indexed by user, and a max grade.
 *
 * @param $audiorecorderid int
 * @return object with properties ->grades (an array of grades) and ->maxgrade.
 */
function audiorecorder_grades($audiorecorderid) {

    if (!$audiorecorder = get_record('audiorecorder', 'id', $audiorecorderid)) {
        return NULL;
    }
    if ($audiorecorder->grade == 0) { // No grading
        return NULL;
    }

    $grades = get_records_menu('audiorecorder_submissions', 'audiorecorder',
                               $audiorecorder->id, '', 'userid,grade');

    if ($audiorecorder->grade > 0) {
        if ($grades) {
            foreach ($grades as $userid => $grade) {
                if ($grade == -1) {
                    $grades[$userid] = '-';
                }
            }
        }
        $return->grades = $grades;
        $return->maxgrade = $audiorecorder->grade;

    } else { // Scale
        if ($grades) {
            $scaleid = - ($audiorecorder->grade);
            if ($scale = get_record('scale', 'id', $scaleid)) {
                $scalegrades = make_menu_from_list($scale->scale);
                foreach ($grades as $userid => $grade) {
                    if (empty($scalegrades[$grade])) {
                        $grades[$userid] = '-';
                    } else {
                        $grades[$userid] = $scalegrades[$grade];
                    }
                }
            }
        }
        $return->grades = $grades;
        $return->maxgrade = "";
    }

    return $return;
}

/**
 * Returns the users with data in one audiorecorder (students and teachers)
 *
 * @param $audiorecorderid int
 * @return array of user objects
 */
function audiorecorder_get_participants($audiorecorderid) {

    global $CFG;

    //Get students
    $students = get_records_sql("SELECT DISTINCT u.id, u.id
                                 FROM {$CFG->prefix}user u,
                                      {$CFG->prefix}audiorecorder_submissions a
                                 WHERE a.audiorecorder = '$audiorecorderid' and
                                       u.id = a.userid");
    //Get teachers
    $teachers = get_records_sql("SELECT DISTINCT u.id, u.id
                                 FROM {$CFG->prefix}user u,
                                      {$CFG->prefix}audiorecorder_submissions a
                                 WHERE a.audiorecorder = '$audiorecorderid' and
                                       u.id = a.teacher");

    //Add teachers to students
    if ($teachers) {
        foreach ($teachers as $teacher) {
            $students[$teacher->id] = $teacher;
        }
    }
    //Return students array (it contains an array of unique users)
    return ($students);
}

/**
 * Checks if a scale is being used by an audiorecorder
 *
 * This is used by the backup code to decide whether to back up a scale
 * @param $audiorecorderid int
 * @param $scaleid int
 * @return boolean True if the scale is used by the audiorecorder
 */
function audiorecorder_scale_used ($audiorecorderid, $scaleid) {

    $return = false;

    $rec = get_record('audiorecorder','id',$audiorecorderid,'grade',-$scaleid);

    if (!empty($rec) && !empty($scaleid)) {
        $return = true;
    }

    return $return;
}

function audiorecorder_print_overview($courses, &$htmlarray) {

    global $USER, $CFG;

    if (empty($courses) || !is_array($courses) || count($courses) == 0) {
        return array();
    }

    if (!$audiorecorders = get_all_instances_in_courses('audiorecorder',$courses)) {
        return;
    }

    // Do audiorecorder_base::isopen() here without loading the whole thing for speed
    foreach ($audiorecorders as $key => $audiorecorder) {
        $time = time();
        if ($audiorecorder->timedue) {
            if ($audiorecorder->preventlate) {
                $isopen = ($audiorecorder->timeavailable <= $time && $time <= $audiorecorder->timedue);
            } else {
                $isopen = ($audiorecorder->timeavailable <= $time);
            }
        }
        if (empty($isopen) || empty($audiorecorder->timedue)) {
            unset($audiorecorders[$key]);
        }
    }

    $strduedate = get_string('duedate', 'audiorecorder');
    $strduedateno = get_string('duedateno', 'audiorecorder');
    $strgraded = get_string('graded', 'audiorecorder');
    $strnotgradedyet = get_string('notgradedyet', 'audiorecorder');
    $strnotsubmittedyet = get_string('notsubmittedyet', 'audiorecorder');
    $strsubmitted = get_string('submitted', 'audiorecorder');
    $straudiorecorder = get_string('modulename', 'audiorecorder');
    $strreviewed = get_string('reviewed','audiorecorder');

    foreach ($audiorecorders as $audiorecorder) {
        $str = '<div class="audiorecorder overview"><div class="name">'.$straudiorecorder. ': '.
               '<a '.($audiorecorder->visible ? '':' class="dimmed"').
               'title="'.$straudiorecorder.'" href="'.$CFG->wwwroot.
               '/mod/audiorecorder/view.php?id='.$audiorecorder->coursemodule.'">'.
               $audiorecorder->name.'</a></div>';
        if ($audiorecorder->timedue) {
            $str .= '<div class="info">'.$strduedate.': '.userdate($audiorecorder->timedue).'</div>';
        } else {
            $str .= '<div class="info">'.$strduedateno.'</div>';
        }
        $context = get_context_instance(CONTEXT_MODULE, $audiorecorder->coursemodule);
        if (has_capability('mod/audiorecorder:grade', $context)) {
            
            // count how many people can submit
            $submissions = 0; // init
            if ($students = get_users_by_capability($context, 'mod/audiorecorder:submit')) {
                foreach ($students as $student) {
                    if (get_records_sql("SELECT id,id FROM {$CFG->prefix}audiorecorder_submissions
                                         WHERE audiorecorder = $audiorecorder->id AND
                                               userid = $student->id AND
                                               teacher = 0 AND
                                               timemarked = 0")) {
                        $submissions++;  
                    }
                }
            }
            
            if ($submissions) {
                $str .= get_string('submissionsnotgraded', 'audiorecorder', $submissions);
            }
        } else {
            $sql = "SELECT *
                      FROM {$CFG->prefix}audiorecorder_submissions
                     WHERE userid = '$USER->id'
                       AND audiorecorder = '{$audiorecorder->id}'";
            if ($submission = get_record_sql($sql)) {
                if ($submission->teacher == 0 && $submission->timemarked == 0) {
                    $str .= $strsubmitted . ', ' . $strnotgradedyet;
                } else if ($submission->grade <= 0) {
                    $str .= $strsubmitted . ', ' . $strreviewed;
                } else {
                    $str .= $strsubmitted . ', ' . $strgraded;
                }
            } else {
                $str .= $strnotsubmittedyet . ' ' . audiorecorder_display_lateness(time(), $audiorecorder->timedue);
            }
        }
        $str .= '</div>';
        if (empty($htmlarray[$audiorecorder->course]['audiorecorder'])) {
            $htmlarray[$audiorecorder->course]['audiorecorder'] = $str;
        } else {
            $htmlarray[$audiorecorder->course]['audiorecorder'] .= $str;
        }
    }
}

function audiorecorder_display_lateness($timesubmitted, $timedue) {
    if (!$timedue) {
        return '';
    }
    $time = $timedue - $timesubmitted;
    if ($time < 0) {
        $timetext = get_string('late', 'audiorecorder', format_time($time));
        return ' (<span class="late">'.$timetext.'</span>)';
    } else {
        $timetext = get_string('early', 'audiorecorder', format_time($time));
        return ' (<span class="early">'.$timetext.'</span>)';
    }
}


//=======================OTHER STANDARD FUNCTION ========================
function audiorecorder_add_instance($ar) {
/// Given an object containing all the necessary data, 
/// (defined by the form in mod.html) this function 
/// will create a new instance and return the id number 
/// of the new instance.
    global $CFG;
    require_once("$CFG->dirroot/mod/audiorecorder/type/$ar->audiorecordertype/audiorecorder.class.php");
    $arclass = "audiorecorder_$ar->audiorecordertype";

    $ars = new $arclass();
    return $ars->add_instance($ar);
}


function audiorecorder_update_instance($ar) {
/// Given an object containing all the necessary data, 
/// (defined by the form in mod.html) this function 
/// will update an existing instance with new data.

    global $CFG;

    require_once("$CFG->dirroot/mod/audiorecorder/type/$ar->audiorecordertype/audiorecorder.class.php");
    $arclass = "audiorecorder_$ar->audiorecordertype";

    $ars = new $arclass();
    return $ars->update_instance($ar);
}

function audiorecorder_set_times(&$ar) {
	$time = time();

	$ar->timecreated = $time;
	$ar->timemodified = $time;

	$ar->timeopen = make_timestamp(
		$ar->openyear, $ar->openmonth, $ar->openday, 
		$ar->openhour, $ar->openminute, 0
	);
	$ar->timeclose = make_timestamp(
		$ar->closeyear, $ar->closemonth, $ar->closeday, 
		$ar->closehour, $ar->closeminute, 0
	);
}

function audiorecorder_delete_instance($id) {
/// Given an ID of an instance of this module, 
/// this function will permanently delete the instance 
/// and any data that depends on it.  

    global $CFG;

    if (! $arinstance = get_record('audiorecorder', 'id', $id)) {
        return false;
    }
    require_once("$CFG->dirroot/mod/audiorecorder/type/$arinstance->audiorecordertype/audiorecorder.class.php");
    $arclass = "audiorecorder_$arinstance->audiorecordertype";

    $ars = new $arclass();
    return $ars->delete_instance($arinstance);
}


function audiorecorder_user_outline($course, $user, $mod, $ar) {
/// Return a small object with summary information about what a 
/// user has done with a given particular instance of this module
/// Used for user activity reports.
/// $return->time = the time they did it
/// $return->info = a short text description

    return $return;
}

function audiorecorder_user_complete($course, $user, $mod, $ar) {
/// Print a detailed representation of what a  user has done with 
/// a given particular instance of this module, for user activity reports.

    return true;
}

function audiorecorder_print_recent_activity($course, $isteacher, $timestart) {
/// Given a course and a time, this module should find recent activity 
/// that has occurred in audiorecorder activities and print it out. 
/// Return true if there was output, or false is there was none.

    global $CFG;

    return false;  //  True if anything was printed, otherwise false 
}

function audiorecorder_cron () {
/// Function to be run periodically according to the moodle cron
/// This function searches for things that need to be done, such 
/// as sending out mail, toggling flags etc ... 

    global $CFG;

    return true;
}

//////////////////////////////////////////////////////////////////////////////////////
/// Any other NEWMODULE functions go here.  Each of them must have a name that 
/// starts with NEWMODULE_

function audiorecorder_print_audioobject(){
	//Print all audio object
	print "<object classid=\"clsid:d27cdb6e-ae6d-11cf-96b8-444553540000\" codebase=\"http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,0,0\" width=\"200\" height=\"350\" name=\"audioplayer\" id=\"audioplayer\" align=\"middle\">";
	print "<param name=\"allowScriptAccess\" value=\"sameDomain\" />";
	print "<param name=\"movie\" value=\"audioplayer.swf\" />";
	print "<param name=\"quality\" value=\"high\" />";
	print "<param name=\"bgcolor\" value=\"#ffffff\" />";
	print "<embed src=\"audioplayer.swf\" quality=\"high\" bgcolor=\"#ffffff\" width=\"200\" height=\"350\" name=\"audioplayer\" align=\"middle\" allowScriptAccess=\"sameDomain\" type=\"application/x-shockwave-flash\" pluginspage=\"http://www.macromedia.com/go/getflashplayer\" />";
	print "</object>";
	print "<object classid=\"clsid:D66F6E64-E742-4C6C-8DB8-4071EF3A9BE9\" codebase=\"AudioRecorder.cab\" id=\"AR\" width=\"0\" height=\"0\">";
	print "</object>";

	print "<object type=\"application/x-mplayer2\" id=\"MediaPlayer\" name=\"MediaPlayer\" data=\"01.mp3\" width=\"0\" height=\"0\">";
	print "<param name=\"src\" value=\"01.mp3\" />";
	print "<param name=\"filename\" value=\"01.mp3\" />";
	print "<param name=\"type\" value=\"application/x-mplayer2\" />";

	print "</object>";

}

/*
*  Create an array list of allowed time length
*/
function audiorecorder_max_time() {
	$maxtimes = array(  '1' => '1 Minute',
                                '2' => '2 Minutes',
                                '3' => '3 Minutes',
                                '4' => '4 Minutes',
                                '5' => '5 Minutes',
                                '8' => '8 Minutes',
                                '10' => '10 Minutes',
                                '15' => '15 Minutes',
                                '20' => '20 Minutes',
                                '30' => '30 Minutes'
                             );
     return $maxtimes;

}

/*
 * Return audiorecorder types
 */
function audiorecorder_types(){
    $types = array();
    $types['uploadsingle']=get_string('typeuploadsingle','audiorecorder');
    $types['upload']=get_string('typeupload','audiorecorder');
    return $types;
}

?>
