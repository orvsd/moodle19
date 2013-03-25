<?php // $Id: audiorecorder.class.php,v 1.0 2007/12/09 20:56:23 Tang Wei Exp $

define('AUDIORECORDER_STATUS_SUBMITTED', 'submitted');

/**
 * Extend the base audiorecorder class for audiorecorders where you upload a single file
 *
 */
class audiorecorder_upload extends audiorecorder_base {

    function audiorecorder_upload($cmid=0) {
        parent::audiorecorder_base($cmid);

    }

    function view() {
        global $USER;

        require_capability('mod/audiorecorder:view', $this->context);

        add_to_log($this->course->id, 'audiorecorder', 'view', "view.php?id={$this->cm->id}", $this->audiorecorder->id, $this->cm->id);

        $this->view_header();

        if ($this->audiorecorder->timeavailable > time()
          and !has_capability('mod/audiorecorder:grade', $this->context)      // grading user can see it anytime
          and $this->audiorecorder->var3) {                                   // force hiding before available date
            print_simple_box_start('center', '', '', '', 'generalbox', 'intro');
            print_string('notavailableyet', 'audiorecorder');
            print_simple_box_end();
        } else {
            $this->view_intro();
        }

        $this->view_dates();

        if (has_capability('mod/audiorecorder:submit', $this->context)) {
            $filecount = $this->count_user_files($USER->id);
            $submission = $this->get_submission($USER->id);

            $this->view_feedback();

            if ($this->is_finalized($submission)) {
                print_heading(get_string('submission', 'audiorecorder'), 'center', 3);
            } else {
                print_heading(get_string('submissiondraft', 'audiorecorder'), 'center', 3);
            }

            if ($filecount and $submission) {
                print_simple_box($this->print_user_files($USER->id, true), 'center');
            } else {
                if ($this->is_finalized($submission)) {
                    print_simple_box(get_string('nofiles', 'audiorecorder'), 'center');
                } else {
                    print_simple_box(get_string('nofilesyet', 'audiorecorder'), 'center');
                }
            }

            $this->view_upload_form();

            if ($this->notes_allowed()) {
                print_heading(get_string('notes', 'audiorecorder'), 'center', 3);
                $this->view_notes();
            }

            $this->view_final_submission();
        }
        $this->view_footer();
    }


    function view_feedback($submission=NULL) {
        global $USER;

        if (!$submission) { /// Get submission for this audiorecorder
            $submission = $this->get_submission($USER->id);
        }

        if (empty($submission->timemarked)) {   /// Nothing to show, so print nothing
            if ($this->count_responsefiles($USER->id)) {
                print_heading(get_string('responsefiles', 'audiorecorder', $this->course->teacher), '', 3);
                $responsefiles = $this->print_responsefiles($USER->id, true);
                print_simple_box($responsefiles, 'center');
            }
            return;
        }

    /// We need the teacher info
        if (! $teacher = get_record('user', 'id', $submission->teacher)) {
            error('Could not find the teacher');
        }

    /// Print the feedback
        print_heading(get_string('submissionfeedback', 'audiorecorder'), '', 3);

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

        echo '<tr>';
        echo '<td class="left side">&nbsp;</td>';
        echo '<td class="content">';
        echo $this->print_responsefiles($USER->id, true);
        echo '</tr>';

        echo '</table>';
    }


    function view_upload_form() {
        global $CFG, $USER; 
        //
        $submission = $this->get_submission($USER->id);

        $struploadafile = get_string('uploadafile');
        $strmaxsize = get_string('maxsize', '', display_size($this->audiorecorder->maxbytes));

        if ($this->is_finalized($submission)) {
            // no uploading
            return;
        }

        if ($this->can_upload_file($submission)) {
            echo '<center>';
            echo '<form enctype="multipart/form-data" method="post" action="upload.php">';
            echo "<p>$struploadafile ($strmaxsize)</p>";
            echo '<input type="hidden" name="id" value="'.$this->cm->id.'" />';
            echo '<input type="hidden" name="action" value="uploadfile" />';
            require_once($CFG->libdir.'/uploadlib.php');
            upload_print_form_fragment(1,array('newfile'),null,false,null,0,$this->audiorecorder->maxbytes,false);
            echo '<input type="submit" name="save" value="'.get_string('uploadthisfile').'" />';
            echo '</form>';
            echo '</center>';
            echo '<br />';
        }

    }

    function view_notes() {
        global $USER;

        if ($submission = $this->get_submission($USER->id)
          and !empty($submission->data1)) {
            print_simple_box(format_text($submission->data1, FORMAT_HTML), 'center', '630px');
        } else {
            print_simple_box(get_string('notesempty', 'audiorecorder'), 'center');
        }
        if ($this->can_update_notes($submission)) {
            $options = array ('id'=>$this->cm->id, 'action'=>'editnotes');
            echo '<center>';
            print_single_button('upload.php', $options, get_string('edit'), 'post', '_self', false);
            echo '</center>';
        }
    }

    function view_final_submission() {
        global $CFG, $USER;

        $submission = $this->get_submission($USER->id);

        if ($this->can_finalize($submission)) {
            //print final submit button
            print_heading(get_string('submitformarking','audiorecorder'), '', 3);
            echo '<center>';
            echo '<form method="post" action="upload.php">';
            echo '<input type="hidden" name="id" value="'.$this->cm->id.'" />';
            echo '<input type="hidden" name="action" value="finalize" />';
            echo '<input type="submit" name="formarking" value="'.get_string('sendformarking', 'audiorecorder').'" />';
            echo '</form>';
            echo '</center>';
        } else if ($this->is_finalized($submission)) {
            print_heading(get_string('submitedformarking','audiorecorder'), '', 3);
        } else {
            //no submission yet
        }
    }

    function custom_feedbackform($submission, $return=false) {
        global $CFG;

        $mode         = optional_param('mode', '', PARAM_ALPHA);
        $offset       = optional_param('offset', 0, PARAM_INT);
        $forcerefresh = optional_param('forcerefresh', 0, PARAM_BOOL);

        $output = get_string('responsefiles', 'audiorecorder').': ';

        $output .= '<form enctype="multipart/form-data" method="post" '.
             "action=\"$CFG->wwwroot/mod/audiorecorder/upload.php\">";
        $output .= '<input type="hidden" name="id" value="'.$this->cm->id.'" />';
        $output .= '<input type="hidden" name="action" value="uploadresponse" />';
        $output .= '<input type="hidden" name="mode" value="'.$mode.'" />';
        $output .= '<input type="hidden" name="offset" value="'.$offset.'" />';
        $output .= '<input type="hidden" name="userid" value="'.$submission->userid.'" />';
        require_once($CFG->libdir.'/uploadlib.php');
        $output .= upload_print_form_fragment(1,array('newfile'),null,false,null,0,0,true);
        $output .= '<input type="submit" name="save" value="'.get_string('uploadthisfile').'" />';
        $output .= '</form>';

        if ($forcerefresh) {
            $output .= $this->update_main_listing($submission);
        }

        $responsefiles = $this->print_responsefiles($submission->userid, true);
        if (!empty($responsefiles)) {
            $output .= $responsefiles;
        }

        if ($return) {
            return $output;
        }
        echo $output;
        return;
    }


    function print_student_answer($userid, $return=false){
        global $CFG;

        $filearea = $this->file_area_name($userid);
        $submission = $this->get_submission($userid);

        $output = '';

        if ($basedir = $this->file_area($userid)) {
            if (!$this->is_finalized($submission)) {
                $output .= '<strong>'.get_string('draft', 'audiorecorder').':</strong> ';
            }

            if ($this->notes_allowed() and !empty($submission->data1)) {
                $output .= link_to_popup_window ('/mod/audiorecorder/type/upload/notes.php?id='.$this->cm->id.'&amp;userid='.$userid,
                                                'notes'.$userid, get_string('notes', 'audiorecorder'), 500, 780, get_string('notes', 'audiorecorder'), 'none', true, 'notesbutton'.$userid);
                $output .= '&nbsp;';
            }

            if ($files = get_directory_list($basedir, 'responses')) {
                foreach ($files as $key => $file) {
                    require_once($CFG->libdir.'/filelib.php');
                    $icon = mimeinfo('icon', $file);
                    $ffurl = "$CFG->wwwroot/file.php?file=/$filearea/$file";
                    $output .= '<img align="middle" src="'.$CFG->pixpath.'/f/'.$icon.'" height="16" width="16" alt="'.$icon.'" />'.
                            '<a href="'.$ffurl.'" >'.$file.'</a>&nbsp;';
                }
            }
        }
        $output = '<div class="files">'.$output.'</div>';
        $output .= '<br />';

        return $output;
    }
    /**
     * Produce a mp3 player using Moodle MP3 player filter
     * @param $ffurl, address of URL
     * @return  string mp3 player HTML tags.
     */
    function print_mp3_filter_player($ffurl){
        global $CFG;
        //set up player interface.
        $c = 'bgColour=000000&amp;btnColour=ffffff&amp;btnBorderColour=cccccc&amp;iconColour=000000&amp;iconOverColour=00cc00&amp;trackColour=cccccc&amp;handleColour=ffffff&amp;loaderColour=ffffff&amp;waitForPlay=yes&amp;';
        
        $output = "";
        $output  = '&nbsp;<object class="mediaplugin mp3" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"';
        $output .= ' codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0" ';
        $output .= ' width="90" height="15" id="mp3player">';
        $output .= " <param name=\"movie\" value=\"$CFG->wwwroot/filter/mediaplugin/mp3player.swf?src=".$ffurl."\" />";
        $output .= ' <param name="quality" value="high" />';
        $output .= ' <param name="bgcolor" value="#333333" />';
        $output .= ' <param name="flashvars" value="'.$c.'" />';
        $output .= " <embed src=\"$CFG->wwwroot/filter/mediaplugin/mp3player.swf?src=".$ffurl."\" ";
        $output .= "  quality=\"high\" bgcolor=\"#333333\" width=\"90\" height=\"15\" name=\"mp3player\" ";
        $output .= ' type="application/x-shockwave-flash" ';
        $output .= ' flashvars="'.$c.'" ';
        $output .= ' pluginspage="http://www.macromedia.com/go/getflashplayer">';
        $output .= '</embed>';
        $output .= '</object>&nbsp;';
        return $output;
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

        $mode    = optional_param('mode', '', PARAM_ALPHA);
        $offset  = optional_param('offset', 0, PARAM_INT);

        if (!$userid) {
            if (!isloggedin()) {
                return '';
            }
            $userid = $USER->id;
        }

        $filearea = $this->file_area_name($userid);

        $output = '';

        if ($submission = $this->get_submission($userid)) {

            $candelete = $this->can_delete_files($submission);
            $strdelete   = get_string('delete');

            if (!$this->is_finalized($submission) and !empty($mode)) {                 // only during grading
                $output .= '<strong>'.get_string('draft', 'audiorecorder').':</strong><br />';
            }

            if ($this->notes_allowed() and !empty($submission->data1) and !empty($mode)) { // only during grading
                $offset = required_param('offset', PARAM_INT);

                $npurl = "type/upload/notes.php?id={$this->cm->id}&amp;userid=$userid&amp;offset=$offset&amp;mode=single";
                $output .= '<a href="'.$npurl.'">'.get_string('notes', 'audiorecorder').'</a><br />';

            }

            if ($basedir = $this->file_area($userid)) {
                if ($files = get_directory_list($basedir, 'responses')) {
                    require_once($CFG->libdir.'/filelib.php');
                    foreach ($files as $key => $file) {

                        $icon = mimeinfo('icon', $file);

                        $ffurl   = "$CFG->wwwroot/file.php?file=/$filearea/$file";

                        //add mp3 playback control
                        if ($CFG->audiofile_enablemp3 && ($mode=="single")) {
                            $output .=$this->print_mp3_filter_player($ffurl);
                        }

                        $output .= '<img align="middle" src="'.$CFG->pixpath.'/f/'.$icon.'" height="16" width="16" alt="'.$icon.'" />'
                                  .'<a href="'.$ffurl.'" >'.$file.'</a>';


                        if ($candelete) {
                            $delurl  = "$CFG->wwwroot/mod/audiorecorder/delete.php?id={$this->cm->id}&amp;file=$file&amp;userid={$submission->userid}&amp;mode=$mode&amp;offset=$offset";

                            $output .= '<a title="'.$strdelete.'" href="'.$delurl.'">&nbsp;'
                                      .'<img src="'.$CFG->pixpath.'/t/delete.gif" height="11" width="11" border="0" alt="'.$strdelete.'" /></a> ';
                        }

                        $output .= '<br />';
                    }
                }
            }
            if (has_capability('mod/audiorecorder:grade', $this->context)
              and $this->can_unfinalize($submission)
              and $mode != '') { // we do not want it on view.php page
                $options = array ('id'=>$this->cm->id, 'userid'=>$userid, 'action'=>'unfinalize', 'mode'=>$mode, 'offset'=>$offset);
                $output .= print_single_button('upload.php', $options, get_string('unfinalize', 'audiorecorder'), 'post', '_self', true);
            }

            $output = '<div class="files">'.$output.'</div>';

        }

        if ($return) {
            return $output;
        }
        echo $output;
    }

    function print_responsefiles($userid, $return=false) {
        global $CFG, $USER;

        $mode    = optional_param('mode', '', PARAM_ALPHA);
        $offset  = optional_param('offset', 0, PARAM_INT);

        $filearea = $this->file_area_name($userid).'/responses';

        $output = '';

        $candelete = $this->can_manage_responsefiles();
        $strdelete   = get_string('delete');

        if ($basedir = $this->file_area($userid)) {
            $basedir .= '/responses';

            if ($files = get_directory_list($basedir)) {
                require_once($CFG->libdir.'/filelib.php');
                foreach ($files as $key => $file) {

                    $icon = mimeinfo('icon', $file);

                    $ffurl   = "$CFG->wwwroot/file.php?file=/$filearea/$file";

                    $output .= '<img align="middle" src="'.$CFG->pixpath.'/f/'.$icon.'" height="16" width="16" alt="'.$icon.'" />'
                              .'<a href="'.$ffurl.'" >'.$file.'</a>';
                    //add mp3 playback control
                    if ($CFG->audiofile_enablemp3 && ($mode=="single")) {
                        $output .=$this->print_mp3_filter_player($ffurl);
                    }

                    if ($candelete) {
                        $delurl  = "$CFG->wwwroot/mod/audiorecorder/delete.php?id={$this->cm->id}&amp;file=$file&amp;userid=$userid&amp;mode=$mode&amp;offset=$offset&amp;action=response";

                        $output .= '<a title="'.$strdelete.'" href="'.$delurl.'">&nbsp;'
                                  .'<img src="'.$CFG->pixpath.'/t/delete.gif" height="11" width="11" border="0" alt="'.$strdelete.'" /></a> ';
                    }

                    $output .= '&nbsp;';
                }
            }


            $output = '<div class="responsefiles">'.$output.'</div>';

        }

        if ($return) {
            return $output;
        }
        echo $output;
    }


    function upload() {
        $action = required_param('action', PARAM_ALPHA);

        switch ($action) {
            case 'finalize':
                $this->finalize();
                break;
            case 'unfinalize':
                $this->unfinalize();
                break;
            case 'uploadresponse':
                $this->upload_responsefile();
                break;
            case 'uploadfile':
                $this->upload_file();
            case 'savenotes':
            case 'editnotes':
                $this->upload_notes();
            default:
                error('Error: Unknow upload action ('.$action.').');
        }
    }

    function upload_notes() {
        global $CFG, $USER;

        $action = required_param('action', PARAM_ALPHA);

        $returnurl = 'view.php?id='.$this->cm->id;

        if ($submission = $this->get_submission($USER->id)) {
            $defaulttext = $submission->data1;
        } else {
            $defaulttext = '';
        }

        if (!$this->can_update_notes($submission)) {
            $this->view_header(get_string('upload'));
            notify(get_string('uploaderror', 'audiorecorder'));
            print_continue($returnurl);
            $this->view_footer();
            die;
        }

        if (data_submitted('nomatch') and $action == 'savenotes') {
            $text = required_param('text', PARAM_RAW); // to be cleaned before display
            $submission = $this->get_submission($USER->id, true); // get or create submission
            $updated = new object();
            $updated->id           = $submission->id;
            $updated->timemodified = time();
            $updated->data1        = $text;

            if (update_record('audiorecorder_submissions', $updated)) {
                add_to_log($this->course->id, 'audiorecorder', 'upload', 'view.php?a='.$this->audiorecorder->id, $this->audiorecorder->id, $this->cm->id);
                redirect($returnurl);
            } else {
                $this->view_header(get_string('notes', 'audiorecorder'));
                notify(get_string('notesupdateerror', 'audiorecorder'));
                print_continue($returnurl);
                $this->view_footer();
                die;
            }
        }

        /// show notes edit form
        $this->view_header(get_string('notes', 'audiorecorder'));
        print_heading(get_string('notes', 'audiorecorder'), 'center');

        echo '<form name="theform" action="upload.php" method="post">';
        echo '<table cellspacing="0" class="editbox" align="center">';
        echo '<tr><td align="right">';
        helpbutton('reading', get_string('helpreading'), 'moodle', true, true);
        echo '<br />';
        helpbutton('writing', get_string('helpwriting'), 'moodle', true, true);
        echo '<br />';
        echo '</td></tr>';
        echo '<tr><td align="center">';
        print_textarea(can_use_html_editor(), 20, 60, 630, 400, 'text', $defaulttext);
        echo '</td></tr>';
        echo '<tr><td align="center">';
        echo '<input type="hidden" name="id" value="'.$this->cm->id.'" />';
        echo '<input type="hidden" name="action" value="savenotes" />';
        echo '<input type="submit" value="'.get_string('savechanges').'" />';
        echo '<input type="reset" value="'.get_string('revert').'" />';
        echo '</td></tr></table>';
        echo '</form>';

        if (can_use_html_editor()) {
            use_html_editor();   // MUst be at the end of the page
        }

        $this->view_footer();
        die;
    }

    function upload_responsefile() {
        global $CFG;

        $userid = required_param('userid', PARAM_INT);
        $mode   = required_param('mode', PARAM_ALPHA);
        $offset = required_param('offset', PARAM_INT);

        $returnurl = "submissions.php?id={$this->cm->id}&amp;userid=$userid&amp;mode=$mode&amp;offset=$offset";

        if (data_submitted('nomatch') and $this->can_manage_responsefiles()) {
            $dir = $this->file_area_name($userid).'/responses';
            check_dir_exists($CFG->dataroot.'/'.$dir, true, true);

            require_once($CFG->dirroot.'/lib/uploadlib.php');
            $um = new upload_manager('newfile',false,true,$this->course,false,0,true);

            if (!$um->process_file_uploads($dir)) {
                print_header(get_string('upload'));
                notify(get_string('uploaderror', 'audiorecorder'));
                echo $um->get_errors();
                print_continue($returnurl);
                print_footer('none');
                die;
            }
        }
        redirect($returnurl);
    }

    function upload_file() {
        global $CFG, $USER;

        $mode   = optional_param('mode', '', PARAM_ALPHA);
        $offset = optional_param('offset', 0, PARAM_INT);

        $returnurl = 'view.php?id='.$this->cm->id;

        $filecount = $this->count_user_files($USER->id);
        $submission = $this->get_submission($USER->id);

        if (!$this->can_upload_file($submission)) {
            $this->view_header(get_string('upload'));
            notify(get_string('uploaderror', 'audiorecorder'));
            print_continue($returnurl);
            $this->view_footer();
            die;
        }

        $dir = $this->file_area_name($USER->id);
        check_dir_exists($CFG->dataroot.'/'.$dir, true, true); // better to create now so that student submissions do not block it later

        require_once($CFG->dirroot.'/lib/uploadlib.php');
        $um = new upload_manager('newfile',false,true,$this->course,false,$this->audiorecorder->maxbytes,true);

        if ($um->process_file_uploads($dir)) {
            $submission = $this->get_submission($USER->id, true); //create new submission if needed
            $updated = new object();
            $updated->id           = $submission->id;
            $updated->timemodified = time();

            if (update_record('audiorecorder_submissions', $updated)) {
                add_to_log($this->course->id, 'audiorecorder', 'upload',
                        'view.php?a='.$this->audiorecorder->id, $this->audiorecorder->id, $this->cm->id);
            } else {
                $new_filename = $um->get_new_filename();
                $this->view_header(get_string('upload'));
                notify(get_string('uploadnotregistered', 'audiorecorder', $new_filename));
                print_continue($returnurl);
                $this->view_footer();
                die;
            }
            redirect('view.php?id='.$this->cm->id);
        }
        $this->view_header(get_string('upload'));
        notify(get_string('uploaderror', 'audiorecorder'));
        echo $um->get_errors();
        print_continue($returnurl);
        $this->view_footer();
        die;
    }

    function finalize() {
        global $USER;

        $confirm = optional_param('confirm', 0, PARAM_BOOL);

        $returnurl = 'view.php?id='.$this->cm->id;
        $submission = $this->get_submission($USER->id);

        if (!$this->can_finalize($submission)) {
            redirect($returnurl); // probably already graded, erdirect to audiorecorder page, the reason should be obvious
        }

        if (!data_submitted('nomatch') or !$confirm) {
            $optionsno = array('id'=>$this->cm->id);
            $optionsyes = array ('id'=>$this->cm->id, 'confirm'=>1, 'action'=>'finalize');
            $this->view_header(get_string('submitformarking', 'audiorecorder'));
            print_heading(get_string('submitformarking', 'audiorecorder'));
            notice_yesno(get_string('onceaudiorecordersent', 'audiorecorder'), 'upload.php', 'view.php', $optionsyes, $optionsno, 'post', 'get');
            $this->view_footer();
            die;

        } else {
            $updated = new object();
            $updated->id = $submission->id;
            $updated->data2 = audiorecorder_STATUS_SUBMITTED;
            $updated->timemodified = time();
            if (update_record('audiorecorder_submissions', $updated)) {
                add_to_log($this->course->id, 'audiorecorder', 'upload', //TODO: add finilize action to log
                        'view.php?a='.$this->audiorecorder->id, $this->audiorecorder->id, $this->cm->id);
                $this->email_teachers($submission);
            } else {
                $this->view_header(get_string('submitformarking', 'audiorecorder'));
                notify(get_string('finalizeerror', 'audiorecorder'));
                print_continue($returnurl);
                $this->view_footer();
                die;
            }
        }
        redirect($returnurl);
    }

    function unfinalize() {

        $userid = required_param('userid', PARAM_INT);
        $mode   = required_param('mode', PARAM_ALPHA);
        $offset = required_param('offset', PARAM_INT);

        $returnurl = "submissions.php?id={$this->cm->id}&amp;userid=$userid&amp;mode=$mode&amp;offset=$offset&amp;forcerefresh=1";

        if (data_submitted('nomatch')
          and $submission = $this->get_submission($userid)
          and $this->can_unfinalize($submission)) {

            $updated = new object();
            $updated->id = $submission->id;
            $updated->data2 = '';
            if (update_record('audiorecorder_submissions', $updated)) {
                //TODO: add unfinilize action to log
                add_to_log($this->course->id, 'audiorecorder', 'view submission', 'submissions.php?id='.$this->audiorecorder->id, $this->audiorecorder->id, $this->cm->id);
            } else {
                $this->view_header(get_string('submitformarking'));
                notify(get_string('finalizeerror', 'audiorecorder'));
                print_continue($returnurl);
                $this->view_footer();
                die;
            }
        }
        redirect($returnurl);
    }


    function delete() {
        $action   = optional_param('action', '', PARAM_ALPHA);

        switch ($action) {
            case 'response':
                $this->delete_responsefile();
                break;
            default:
                $this->delete_file();
        }
        die;
    }


    function delete_responsefile() {
        global $CFG;

        $file     = required_param('file', PARAM_FILE);
        $userid   = required_param('userid', PARAM_INT);
        $mode     = required_param('mode', PARAM_ALPHA);
        $offset   = required_param('offset', PARAM_INT);
        $confirm  = optional_param('confirm', 0, PARAM_BOOL);

        $returnurl = "submissions.php?id={$this->cm->id}&amp;userid=$userid&amp;mode=$mode&amp;offset=$offset";

        if (!$this->can_manage_responsefiles()) {
           redirect($returnurl);
        }

        $urlreturn = 'submissions.php';
        $optionsreturn = array('id'=>$this->cm->id, 'offset'=>$offset, 'mode'=>$mode, 'userid'=>$userid);

        if (!data_submitted('nomatch') or !$confirm) {
            $optionsyes = array ('id'=>$this->cm->id, 'file'=>$file, 'userid'=>$userid, 'confirm'=>1, 'action'=>'response', 'mode'=>$mode, 'offset'=>$offset);
            print_header(get_string('delete'));
            print_heading(get_string('delete'));
            notice_yesno(get_string('confirmdeletefile', 'audiorecorder', $file), 'delete.php', $urlreturn, $optionsyes, $optionsreturn, 'post', 'get');
            print_footer('none');
            die;
        }

        $dir = $this->file_area_name($userid).'/responses';
        $filepath = $CFG->dataroot.'/'.$dir.'/'.$file;
        if (file_exists($filepath)) {
            if (@unlink($filepath)) {
                redirect($returnurl);
            }
        }

        // print delete error
        print_header(get_string('delete'));
        notify(get_string('deletefilefailed', 'audiorecorder'));
        print_continue($returnurl);
        print_footer('none');
        die;

    }


    function delete_file() {
        global $CFG;

        $file     = required_param('file', PARAM_FILE);
        $userid   = required_param('userid', PARAM_INT);
        $confirm  = optional_param('confirm', 0, PARAM_BOOL);
        $mode     = optional_param('mode', '', PARAM_ALPHA);
        $offset   = optional_param('offset', 0, PARAM_INT);

        require_login($this->course->id, false, $this->cm);

        if (empty($mode)) {
            $urlreturn = 'view.php';
            $optionsreturn = array('id'=>$this->cm->id);
            $returnurl = 'view.php?id='.$this->cm->id;
        } else {
            $urlreturn = 'submissions.php';
            $optionsreturn = array('id'=>$this->cm->id, 'offset'=>$offset, 'mode'=>$mode, 'userid'=>$userid);
            $returnurl = "submissions.php?id={$this->cm->id}&amp;offset=$offset&amp;mode=$mode&amp;userid=$userid";
        }

        if (!$submission = $this->get_submission($userid) // incorrect submission
          or !$this->can_delete_files($submission)) {     // can not delete
            $this->view_header(get_string('delete'));
            notify(get_string('cannotdeletefiles', 'audiorecorder'));
            print_continue($returnurl);
            $this->view_footer();
            die;
        }
        $dir = $this->file_area_name($userid);

        if (!data_submitted('nomatch') or !$confirm) {
            $optionsyes = array ('id'=>$this->cm->id, 'file'=>$file, 'userid'=>$userid, 'confirm'=>1, 'sesskey'=>sesskey(), 'mode'=>$mode, 'offset'=>$offset);
            if (empty($mode)) {
                $this->view_header(get_string('delete'));
            } else {
                print_header(get_string('delete'));
            }
            print_heading(get_string('delete'));
            notice_yesno(get_string('confirmdeletefile', 'audiorecorder', $file), 'delete.php', $urlreturn, $optionsyes, $optionsreturn, 'post', 'get');
            if (empty($mode)) {
                $this->view_footer();
            } else {
                print_footer('none');
            }
            die;
        }

        $filepath = $CFG->dataroot.'/'.$dir.'/'.$file;
        if (file_exists($filepath)) {
            if (@unlink($filepath)) {
                $updated = new object();
                $updated->id = $submission->id;
                $updated->timemodified = time();
                if (update_record('audiorecorder_submissions', $updated)) {
                    add_to_log($this->course->id, 'audiorecorder', 'upload', //TODO: add delete action to log
                            'view.php?a='.$this->audiorecorder->id, $this->audiorecorder->id, $this->cm->id);
                }
                redirect($returnurl);
            }
        }

        // print delete error
        if (empty($mode)) {
            $this->view_header(get_string('delete'));
        } else {
            print_header(get_string('delete'));
        }
        notify(get_string('deletefilefailed', 'audiorecorder'));
        print_continue($returnurl);
        if (empty($mode)) {
            $this->view_footer();
        } else {
            print_footer('none');
        }
        die;
    }


    function can_upload_file($submission) {
        global $USER;

        if (has_capability('mod/audiorecorder:submit', $this->context)           // can submit
          and $this->isopen()                                                 // audiorecorder not closed yet
          and (empty($submission) or $submission->grade == -1)                // not graded
          and (empty($submission) or $submission->userid == $USER->id)        // his/her own submission
          and $this->count_user_files($USER->id) < $this->audiorecorder->var1) { // file limit not reached
            return true;
        } else {
            return false;
        }
    }

    function can_manage_responsefiles() {
        if (has_capability('mod/audiorecorder:grade', $this->context)) {
            return true;
        } else {
            return false;
        }
    }

    function can_delete_files($submission) {
        global $USER;

        if (has_capability('mod/audiorecorder:grade', $this->context)) {
            return true;
        }

        if (has_capability('mod/audiorecorder:submit', $this->context)
          and $this->isopen()                                      // audiorecorder not closed yet
          and (!empty($submission) and $submission->grade == -1)   // not graded
          and $this->audiorecorder->resubmit                          // deleting allowed
          and $USER->id == $submission->userid                     // his/her own submission
          and !$this->is_finalized($submission)) {                 // no deleting after final submission
            return true;
        } else {
            return false;
        }
    }

    function is_finalized($submission) {
        if (!empty($submission)
          and $submission->data2 == audiorecorder_STATUS_SUBMITTED) {
            return true;
        } else {
            return false;
        }
    }

    function can_unfinalize($submission) {
        if (has_capability('mod/audiorecorder:grade', $this->context)
          and !empty($submission)
          and $this->is_finalized($submission)
          and $submission->grade == -1) {
            return true;
        } else {
            return false;
        }
    }

    function can_finalize($submission) {
        global $USER;

        if (has_capability('mod/audiorecorder:submit', $this->context)           // can submit
          and $this->isopen()                                                 // audiorecorder not closed yet
          and !empty($submission)                                             // submission must exist
          and $submission->data2 != audiorecorder_STATUS_SUBMITTED               // not graded
          and $submission->userid == $USER->id                                // his/her own submission
          and $submission->grade == -1                                        // no reason to finalize already graded submission
          and ($this->count_user_files($USER->id)
            or ($this->notes_allowed() and !empty($submission->data1)))) { // something must be submitted

            return true;
        } else {
            return false;
        }
    }

    function can_update_notes($submission) {
        global $USER;

        if (has_capability('mod/audiorecorder:submit', $this->context)
          and $this->notes_allowed()                                               // notesd must be allowed
          and $this->isopen()                                                 // audiorecorder not closed yet
          and (empty($submission) or $submission->grade == -1)                // not graded
          and (empty($submission) or $USER->id == $submission->userid)        // his/her own submission
          and !$this->is_finalized($submission)) {                            // no updateingafter final submission
            return true;
        } else {
            return false;
        }
    }

    function notes_allowed() {
        return (boolean)$this->audiorecorder->var2;
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

        if ( is_dir($CFG->dataroot.'/'.$filearea) && $basedir = $this->file_area($userid)) {
            if ($files = get_directory_list($basedir, 'responses')) {
                return count($files);
            }
        }
        return 0;
    }

    function count_responsefiles($userid) {
        global $CFG;

        $filearea = $this->file_area_name($userid).'/responses';

        if ( is_dir($CFG->dataroot.'/'.$filearea) && $basedir = $this->file_area($userid)) {
            $basedir .= '/responses';
            if ($files = get_directory_list($basedir)) {
                return count($files);
            }
        }
        return 0;
    }


}

?>
