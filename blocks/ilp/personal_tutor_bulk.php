<?php  // $Id: personal_tutor_bulk.php,v 1.1.2.3 2009/12/02 17:15:04 ulcc Exp $

/// Bulk user registration script from a comma separated file
/// Returns list of users with their user ids

require('../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->libdir.'/csvlib.class.php');
require_once($CFG->dirroot.'/user/profile/lib.php');
require_once('block_ilp_lib.php');

$iid         = optional_param('iid', '', PARAM_INT);
$previewrows = optional_param('previewrows', 10, PARAM_INT);
$readcount   = optional_param('readcount', 0, PARAM_INT);

@set_time_limit(3600); // 1 hour should be enough
@raise_memory_limit('256M');
if (function_exists('apache_child_terminate')) {
    // if we are running from Apache, give httpd a hint that
    // it can recycle the process after it's done. Apache's
    // memory management is truly awful but we can help it.
    @apache_child_terminate();
}

$title = get_string('personal_tutor_bulk_upload','block_ilp');
$navlinks[] = array('name' => get_string('ilps','block_ilp'), 'link' => "$CFG->wwwroot/blocks/ilp/list.php?courseid=1", 'type' => 'misc');
$navlinks[] = array('name' => get_string('personal_tutor_bulk_upload','block_ilp'), 'link' => FALSE, 'type' => 'misc');

$navigation = build_navigation($navlinks);
print_header_simple($title, '', $navigation,'', '', true, '','');

require_capability('moodle/site:uploadusers', get_context_instance(CONTEXT_SYSTEM));

$textlib = textlib_get_instance();
$systemcontext = get_context_instance(CONTEXT_SYSTEM);

$strcannotassignrole        = get_string('cannotassignrole', 'error');
$errorstr                   = get_string('error');

$returnurl = $CFG->wwwroot.'/blocks/ilp/list.php?courseid=1';

// array of all valid fields for validation
$STD_FIELDS = array('tutorid', 'studentid', 'role');

if (empty($iid)) {
    $mform = new admin_uploaduser_form1();

    if ($formdata = $mform->get_data()) {
        $iid = csv_import_reader::get_new_iid('uploaduser');
        $cir = new csv_import_reader($iid, 'uploaduser');

        $content = $mform->get_file_content('userfile');

        $readcount = $cir->load_csv_content($content, $formdata->encoding, $formdata->delimiter_name, 'validate_user_upload_columns');
        unset($content);

        if ($readcount === false) {
            error($cir->get_error(), $returnurl);
        } else if ($readcount == 0) {
            print_error('csvemptyfile', 'error', $returnurl);
        }
        // continue to form2

    } else {
        print_heading(get_string('personal_tutor_bulk_upload','block_ilp'));
        echo get_string('csv_format','block_ilp');
        $mform->display();
        die;
    }
} else {
    $cir = new csv_import_reader($iid, 'uploaduser');
}

if (!$columns = $cir->get_columns()) {
    error('Error reading temporary file', $returnurl);
}
$mform = new admin_uploaduser_form2(null, $columns);
// get initial date from form1
$mform->set_data(array('iid'=>$iid, 'previewrows'=>$previewrows, 'readcount'=>$readcount));

// If a file has been uploaded, then process it
if ($formdata = $mform->is_cancelled()) {
    $cir->cleanup(true);
    redirect($returnurl);

} else if ($formdata = $mform->get_data(false)) { // no magic quotes here!!!
    // Print the header
    print_heading(get_string('uploadusersresult', 'admin'));

    // verification moved to two places: after upload and into form2
    $rolesassigned = 0;
	$rolesnotassigned = 0;

    // init csv import helper
    $cir->init();
    $linenum = 1; //column header is first line

    // init upload progress tracker
    $upt = new uu_progress_tracker();
    $upt->init(); // start table

    while ($line = $cir->next()) {
        $upt->flush();
        $linenum++;

        $upt->track('line', $linenum);

        $user = new object();

        // add fields to user object
        foreach ($line as $key => $value) {
            if ($value !== '') {
                $key = $columns[$key];
            }
			$user->$key = $value;
            if (in_array($key, $upt->columns)) {
              $upt->track($key, $value);
            }
        }

        // add default values for remaining fields
        foreach ($STD_FIELDS as $field) {
            if (isset($user->$field)) {
                continue;
            }
		}

            // make sure user context exists
			$mentee = get_record('user',$formdata->user_field,$user->studentid);
			$mentor = get_record('user',$formdata->user_field,$user->tutorid);
			$usercontext = get_context_instance(CONTEXT_USER, $mentee->id);

			$role = get_record('role','shortname',$user->role);

			$a = fullname($mentor).' -> '.fullname($mentee);

			if (role_assign($role->id, $mentor->id, 0, $usercontext->id)) {
                $upt->track('enrolments', get_string('tutor_assigned', 'block_ilp', $a));
				$rolesassigned++;
            }else{
                $upt->track('enrolments', get_string('tutor_not_assigned', 'block_ilp', $a), 'error');
				$rolesnotassigned++;
            }
    }
    $upt->flush();
    $upt->close(); // close table

    $cir->close();
    $cir->cleanup(true);

    print_box_start('boxwidthnarrow boxaligncenter generalbox', 'uploadresults');
    echo '<p>';
    echo get_string('tutorsassigned', 'block_ilp').': '.$rolesassigned.'<br />';
    echo get_string('tutorsnotassigned', 'admin').': '.$rolesnotassigned.'<br />';
    print_box_end();

    print_continue($returnurl);
    die;
}

/// Print the form
print_heading_with_help(get_string('uploaduserspreview', 'admin'), 'uploadusers2');

$ci = 0;
$ri = 0;

echo '<table id="uupreview" class="generaltable boxaligncenter" summary="'.get_string('uploaduserspreview', 'admin').'">';
echo '<tr class="heading r'.$ri++.'">';
foreach ($columns as $col) {
    echo '<th class="header c'.$ci++.'" scope="col">'.s($col).'</th>';
}
echo '</tr>';

$cir->init();
while ($fields = $cir->next()) {
    if ($ri > $previewrows) {
        echo '<tr class="r'.$ri++.'">';
        foreach ($fields as $field) {
            echo '<td class="cell c'.$ci++.'">...</td>';;
        }
        break;
    }
    $ci = 0;
    echo '<tr class="r'.$ri++.'">';
    foreach ($fields as $field) {
        echo '<td class="cell c'.$ci++.'">'.s($field).'</td>';;
    }
    echo '</tr>';
}
$cir->close();

echo '</table>';
echo '<div class="centerpara">'.get_string('uupreprocessedcount', 'admin', $readcount).'</div>';
$mform->display();
die;

/////////////////////////////////////
/// Utility functions and classes ///
/////////////////////////////////////

class uu_progress_tracker {
    var $_row;
    var $columns = array('line','tutorid','studentid','role','enrolments');

    function uu_progress_tracker() {
    }

    function init() {
        $ci = 0;
        echo '<table id="uuresults" class="generaltable boxaligncenter" summary="'.get_string('uploadusersresult', 'admin').'">';
        echo '<tr class="heading r0">';
		echo '<th class="header c'.$ci++.'" scope="col">'.get_string('uucsvline', 'admin').'</th>';
        echo '<th class="header c'.$ci++.'" scope="col">'.get_string('mentor','block_ilp').'</th>';
        echo '<th class="header c'.$ci++.'" scope="col">'.get_string('mentee','block_ilp').'</th>';
        echo '<th class="header c'.$ci++.'" scope="col">'.get_string('role').'</th>';
        echo '<th class="header c'.$ci++.'" scope="col">'.get_string('enrolments').'</th>';
        echo '</tr>';
        $this->_row = null;
    }

    function flush() {
        if (empty($this->_row) or empty($this->_row['line']['normal'])) {
            $this->_row = array();
            foreach ($this->columns as $col) {
                $this->_row[$col] = array('normal'=>'', 'info'=>'', 'warning'=>'', 'error'=>'');
            }
            return;
        }
        $ci = 0;
        $ri = 1;
        echo '<tr class="r'.$ri++.'">';
        foreach ($this->_row as $field) {
            foreach ($field as $type=>$content) {
                if ($field[$type] !== '') {
                    $field[$type] = '<span class="uu'.$type.'">'.$field[$type].'</span>';
                } else {
                    unset($field[$type]);
                }
            }
            echo '<td class="cell c'.$ci++.'">';
            if (!empty($field)) {
                echo implode('<br />', $field);
            } else {
                echo '&nbsp;';
            }
            echo '</td>';
        }
        echo '</tr>';
        foreach ($this->columns as $col) {
            $this->_row[$col] = array('normal'=>'', 'info'=>'', 'warning'=>'', 'error'=>'');
        }
    }

    function track($col, $msg, $level='normal', $merge=true) {
        if (empty($this->_row)) {
            $this->flush(); //init arrays
        }
        if (!in_array($col, $this->columns)) {
            debugging('Incorrect column:'.$col);
            return;
        }
        if ($merge) {
            if ($this->_row[$col][$level] != '') {
                $this->_row[$col][$level] .='<br />';
            }
            $this->_row[$col][$level] .= s($msg);
        } else {
            $this->_row[$col][$level] = s($msg);
        }
    }

    function close() {
        echo '</table>';
    }
}

/**
 * Validation callback function - verified the column line of csv file.
 * Converts column names to lowercase too.
 */
function validate_user_upload_columns(&$columns) {
    global $STD_FIELDS, $PRF_FIELDS;

    if (count($columns) < 2) {
        return get_string('csvfewcolumns', 'error');
    }

    // test columns
    $processed = array();
    foreach ($columns as $key=>$unused) {
        $columns[$key] = strtolower($columns[$key]); // no unicode expected here, ignore case
        $field = $columns[$key];
        if (!in_array($field, $STD_FIELDS) && !in_array($field, $PRF_FIELDS) &&// if not a standard field and not an enrolment field, then we have an error
            !preg_match('/^course\d+$/', $field) && !preg_match('/^group\d+$/', $field) &&
            !preg_match('/^type\d+$/', $field) && !preg_match('/^role\d+$/', $field)) {
            return get_string('invalidfieldname', 'error', $field);
        }
        if (in_array($field, $processed)) {
            return get_string('csvcolumnduplicates', 'error');
        }
        $processed[] = $field;
    }
    return true;
}

print_footer();
?>