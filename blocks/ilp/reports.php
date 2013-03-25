<?php



/*

 * @copyright &copy; 2007 University of London Computer Centre

 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk

 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License

 * @package ILP

 * @version 1.0

 */

require_once("../../config.php");
@set_time_limit(3600); // 1 hour should be enough
@raise_memory_limit('1G');

    $courseid   = optional_param('courseid', 1, PARAM_INT);
    $mode = optional_param('mode', '', PARAM_TEXT);

    require_login();

    $sitecontext = get_context_instance(CONTEXT_SYSTEM);

    if (has_capability('moodle/legacy:guest', $sitecontext, NULL, false)) {
        error("You are logged in as Guest.");
    }

    if ($courseid) {

        if (! $course = get_record('course', 'id', $courseid)) {
            error("Course ID is incorrect");
        }

        if (! $coursecontext = get_context_instance(CONTEXT_COURSE, $course->id)) {
            error("Context ID is incorrect");
        }
    }

function ilp_get_status_report($userid) {
    global $CFG;
    $module = 'project/ilp';
    $config = get_config($module);

    if($CFG->ilpconcern_status_per_student == 1){

        if($studentstatus = get_record('ilpconcern_status', 'userid', $userid)){

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

        return $thisstudentstatus;

    }else{

        return '';

    }

}

function cleanHTML($text) {

// start by completely removing all unwanted tags
    $text = ereg_replace("<(/)?(font|span|del|ins)[^>]*>","",$text);

// then run another pass over the html (twice), removing unwanted attributes
    $text = ereg_replace("<([^>]*)(class|lang|style|size|face)=(\"[^\"]*\"|'[^']*'|[^>]+)([^>]*)>","<\\1>",$text);
    $text = ereg_replace("<([^>]*)(class|lang|style|size|face)=(\"[^\"]*\"|'[^']*'|[^>]+)([^>]*)>","<\\1>",$text);

// Remove all high characters
    $text = preg_replace('/([\xc0-\xdf].)/se', "'&#' . ((ord(substr('$1', 0, 1)) - 192) * 64 + (ord(substr('$1', 1, 1)) - 128)) . ';'", $text);
    $text = preg_replace('/([\xe0-\xef]..)/se', "'&#' . ((ord(substr('$1', 0, 1)) - 224) * 4096 + (ord(substr('$1', 1, 1)) - 128) * 64 + (ord(substr('$1', 2, 1)) - 128)) . ';'", $text);

// Remove tags as text {subtitle}Subtitle of first paragraph{/subtitle}
    $text = preg_replace("'&lt;.*?&gt;'si","",$text);

// remove odd characters
    $text = str_replace('&#160;',' ',$text);
    $text = str_replace('&#44',',',$text);
    $text = str_replace('&amp;','&',$text);

// remove HTML tags
$text = preg_replace
    (
    array(
    // Remove invisible content
    '@<head[^>]*?>.*?</head>@siu',
    '@<style[^>]*?>.*?</style>@siu',
    '@<script[^>]*?.*?</script>@siu',
    '@<object[^>]*?.*?</object>@siu',
    '@<embed[^>]*?.*?</embed>@siu',
    '@<applet[^>]*?.*?</applet>@siu',
    '@<noframes[^>]*?.*?</noframes>@siu',
    '@<noscript[^>]*?.*?</noscript>@siu',
    '@<noembed[^>]*?.*?</noembed>@siu',

    // Add line breaks before & after blocks
    '@<((br)|(hr))@iu',
    '@</?((address)|(blockquote)|(center)|(del))@iu',
    '@</?((div)|(h[1-9])|(ins)|(isindex)|(p)|(pre))@iu',
    '@</?((dir)|(dl)|(dt)|(dd)|(li)|(menu)|(ol)|(ul))@iu',
    '@</?((table)|(th)|(td)|(caption))@iu',
    '@</?((form)|(button)|(fieldset)|(legend)|(input))@iu',
    '@</?((label)|(select)|(optgroup)|(option)|(textarea))@iu',
    '@</?((frameset)|(frame)|(iframe))@iu',),

    array(
    ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ',
    "\n\$0", "\n\$0", "\n\$0", "\n\$0", "\n\$0", "\n\$0",
    "\n\$0", "\n\$0",),$text
    );

// use Moodle filter, strip remaining tags and enclose in quotations
    $text = '"'.strip_tags(format_text($text,FORMAT_MOODLE)).'"';

    return $text;
}

function ilp_report_byuser_csv($courseid) {
    global $CFG, $SESSION;

    $fields = array('username'  => 'Username',
                    'firstname' => 'First Name',
                    'lastname'  => 'Last Name',
                    'idnumber'  => 'ID Number',
                    'status'    => 'Status',
                    'tutorcompletedtargets' => 'Completed Targets (Tutor Set)',
                    'tutorsettargets' => 'Tutor Set Targets',
                    'studentcompletedtargets' => 'Completed Targets (Student Set)',
                    'studentsettargets' => 'Student Set Targets',
                    'subjectreports' => 'Subject Reports',
                    'report1'    => 'Report 1 Total',
                    'report2'    => 'Report 2 Total',
                    'report3'    => 'Report 3 Total',
                    'report4'    => 'Report 4 Total',
                    );

    $filename = clean_filename('ilpuserreport.csv');

    header("Content-Type: application/download\n");
    header("Content-Disposition: attachment; filename=$filename");
    header("Expires: 0");
    header("Cache-Control: must-revalidate,post-check=0,pre-check=0");
    header("Pragma: public");

    $delimiter = get_string('listsep');
    $encdelim  = '&#'.ord($delimiter);

    $row = array();
    foreach ($fields as $fieldname) {
        $row[] = str_replace($delimiter, $encdelim, $fieldname);
    }
    echo implode($delimiter, $row)."\n";

    if($courseid > 1) {
        $select = 'SELECT u.id, u.firstname, u.lastname, u.idnumber ';
        $context = get_context_instance(CONTEXT_COURSE, $courseid);

        $from = 'FROM '.$CFG->prefix.'user u INNER JOIN
        '.$CFG->prefix.'role_assignments ra on u.id=ra.userid LEFT OUTER JOIN
        '.$CFG->prefix.'user_lastaccess ul on (ra.userid=ul.userid and ul.courseid = '.$courseid.') LEFT OUTER JOIN
        '.$CFG->prefix.'role r on ra.roleid = r.id ';

        $where  = "WHERE ra.contextid = $context->id
        AND u.deleted = 0
        AND (ul.courseid = $courseid OR ul.courseid IS NULL)
        AND u.username <> 'guest'
        AND ra.roleid = 5
        AND r.id = 5 ";

        $sort = "ORDER BY u.lastname ASC ";

        $users = get_records_sql($select.$from.$where);

    }else{
        $users = get_records('user','deleted',0);
    }

    foreach ($users as $auser) {
        $row = array();
        if (!$user = get_record('user', 'id', $auser->id)) {
            continue;
        }

    $report = array('username' => $user->username,
                    'firstname' => $user->firstname,
                    'lastname'  => $user->lastname,
                    'idnumber'  => $user->idnumber,
                    'status'    => ilp_get_status_report($user->id)
                    );

        $report['tutorcompletedtargets'] = count_records_sql('SELECT COUNT(*) FROM '.$CFG->prefix.'ilptarget_posts WHERE setforuserid = '.$user->id.' AND setforuserid != setbyuserid AND status = "1"');

        $report['tutorsettargets'] = count_records_sql('SELECT COUNT(*) FROM '.$CFG->prefix.'ilptarget_posts WHERE setforuserid = '.$user->id.' AND setforuserid != setbyuserid AND status != "3"' );

        $report['studentcompletedtargets'] = count_records_sql('SELECT COUNT(*) FROM '.$CFG->prefix.'ilptarget_posts WHERE setforuserid = '.$user->id.' AND setforuserid = setbyuserid AND status = "1"');

        $report['studentsettargets'] = count_records_sql('SELECT COUNT(*) FROM '.$CFG->prefix.'ilptarget_posts WHERE setforuserid = '.$user->id.' AND setforuserid = setbyuserid AND status != "3"' );
        $report['subjectreports'] = count_records_sql('SELECT COUNT(*) FROM '.$CFG->prefix.'ilp_student_info_per_teacher WHERE student_userid = '.$user->id);

    $report['report1'] = count_records_sql('SELECT COUNT(*) FROM '.$CFG->prefix.'ilpconcern_posts WHERE setforuserid = '.$user->id.' AND status = "0"' );

    $report['report2'] = count_records_sql('SELECT COUNT(*) FROM '.$CFG->prefix.'ilpconcern_posts WHERE setforuserid = '.$user->id.' AND status = "1"' );

    $report['report3'] = count_records_sql('SELECT COUNT(*) FROM '.$CFG->prefix.'ilpconcern_posts WHERE setforuserid = '.$user->id.' AND status = "2"' );

    $report['report4'] = count_records_sql('SELECT COUNT(*) FROM '.$CFG->prefix.'ilpconcern_posts WHERE setforuserid = '.$user->id.' AND status = "3"' );

        foreach ($report as $data){
            $row[] = str_replace($delimiter, $encdelim, $data);
        }

        echo implode($delimiter, $row)."\n";
    }
    die;
}

function ilp_report_bycourse_csv() {
    global $CFG, $SESSION;

    $fields = array('coursename'  => 'Course',
                    'shortname' => 'Short Name',
                    'idnumber'  => 'Course ID',
                    'category' => 'Category',
                    'tutorcompletedtargets' => 'Completed Targets (Tutor Set)',
                    'tutorsettargets' => 'Tutor Set Targets',
                    'studentcompletedtargets' => 'Completed Targets (Student Set)',
                    'studentsettargets' => 'Student Set Targets',
                    'subjectreports' => 'Subject Reports',
                    'report1'    => 'Report 1 Total',
                    'report2'    => 'Report 2 Total',
                    'report3'    => 'Report 3 Total',
                    'report4'    => 'Report 4 Total'
                    );

    $filename = clean_filename('ilpcoursereport.csv');

    header("Content-Type: application/download\n");
    header("Content-Disposition: attachment; filename=$filename");
    header("Expires: 0");
    header("Cache-Control: must-revalidate,post-check=0,pre-check=0");
    header("Pragma: public");

    $delimiter = get_string('listsep');
    $encdelim  = '&#'.ord($delimiter);

    $row = array();
    foreach ($fields as $fieldname) {
        $row[] = str_replace($delimiter, $encdelim, $fieldname);
    }
    echo implode($delimiter, $row)."\n";

    $courses = get_records('course');

    foreach ($courses as $course) {
        $row = array();
        if (!$course = get_record('course', 'id', $course->id)) {
            continue;
        }

    if($course->id > 1) {
        $category = get_record('course_categories','id',$course->category);
    }else{
        $category->name = 'None';
    }

    $report = array('username' => $course->fullname,
                    'firstname' => $course->shortname,
                    'idnumber'  => $course->idnumber,
                    'category' => $category->name
                    );

    $report['tutorcompletedtargets'] = count_records_sql('SELECT COUNT(*) FROM '.$CFG->prefix.'ilptarget_posts WHERE course = '.$course->id.' AND setforuserid != setbyuserid AND status = "1"');
    $report['tutorsettargets'] = count_records_sql('SELECT COUNT(*) FROM '.$CFG->prefix.'ilptarget_posts WHERE course = '.$course->id.' AND setforuserid != setbyuserid AND status != "3"' );

    $report['studentcompletedtargets'] = count_records_sql('SELECT COUNT(*) FROM '.$CFG->prefix.'ilptarget_posts WHERE course = '.$course->id.' AND setforuserid = setbyuserid AND status = "1"');

    $report['studentsettargets'] = count_records_sql('SELECT COUNT(*) FROM '.$CFG->prefix.'ilptarget_posts WHERE course = '.$course->id.' AND setforuserid = setbyuserid AND status != "3"' );
    $report['subjectreports'] = count_records_sql('SELECT COUNT(*) FROM '.$CFG->prefix.'ilp_student_info_per_teacher WHERE courseid = '.$course->id);

    $report['report1'] = count_records_sql('SELECT COUNT(*) FROM '.$CFG->prefix.'ilpconcern_posts WHERE course = '.$course->id.' AND status = "0"' );

    $report['report2'] = count_records_sql('SELECT COUNT(*) FROM '.$CFG->prefix.'ilpconcern_posts WHERE course = '.$course->id.' AND status = "1"' );

    $report['report3'] = count_records_sql('SELECT COUNT(*) FROM '.$CFG->prefix.'ilpconcern_posts WHERE course = '.$course->id.' AND status = "2"' );

    $report['report4'] = count_records_sql('SELECT COUNT(*) FROM '.$CFG->prefix.'ilpconcern_posts WHERE course = '.$course->id.' AND status = "3"' );

        foreach ($report as $data){
            $row[] = str_replace($delimiter, $encdelim, $data);
        }

        echo implode($delimiter, $row)."\n";
    }
    die;
}

function ilp_report_allreviews_csv() {
    global $CFG, $SESSION;

    $fields = array('username'  => 'Username',
                    'firstname' => 'First Name',
                    'lastname'  => 'Last Name',
                    'idnumber'  => 'ID Number',
                    'status'    => 'Student Status',
                    'reportType' => 'Report Type',
                    'name' => 'Name',
                    'report' => 'Report',
                    'dateSet'   => 'Date Set',
                    'deadline' => 'Deadline/Date',
                    'setBy' => 'Set By',
                    'tstatus' => 'Review Status',
                    'shortname' => 'Course Name',
                    'course' => 'Course ID'
            );

    $filename = clean_filename('ilpallreports.csv');

    header("Content-Type: application/download\n");
    header("Content-Disposition: attachment; filename=$filename");
    header("Expires: 0");
    header("Cache-Control: must-revalidate,post-check=0,pre-check=0");
    header("Pragma: public");

    $delimiter = get_string('listsep');
    $encdelim  = '&#'.ord($delimiter);

    $row = array();
    foreach ($fields as $fieldname) {
        $row[] = str_replace($delimiter, $encdelim, $fieldname);
    }
    echo implode($delimiter, $row)."\n";

    if($targets = get_records_sql('SELECT * FROM '.$CFG->prefix.'ilptarget_posts WHERE status != 3')) {
        foreach ($targets as $target) {
        $row = array();
        if (!$user = get_record('user', 'id', $target->setforuserid)) {
            continue;
        }
        if (!$reviewer = get_record('user', 'id', $target->setbyuserid)) {
            continue;
        }
        switch ($target->status) {
            case "0":
                $targetstatus = get_string('outstanding', 'ilptarget');
                break;
            case "1":
                $targetstatus = get_string('achieved', 'ilptarget');
                break;
            case "2":
                $targetstatus = get_string('red', 'ilptarget');
                break;
            case "3":
                $targetstatus = get_string('withdrawn', 'ilptarget');
                break;
        }


        $report = array('username' => $user->username,
            'firstname' => $user->firstname,
            'lastname'  => $user->lastname,
            'idnumber'  => $user->idnumber,
            'status'    => ilp_get_status_report($user->id),
            'reportType' => get_string('modulename','ilptarget'),
            'name' => $target->name,
            'report' => cleanHTML($target->targetset),
            'dateSet'   => userdate($target->timecreated, get_string('strftimedate')),
            'deadline' => userdate($target->deadline, get_string('strftimedate')),
            'setBy' => fullname($reviewer),
            'tstatus' => $targetstatus
                    );
        if($target->courserelated == 1 && $target->targetcourse != SITEID){
            $targetcourse = get_record('course','id',$target->targetcourse);
            $report['shortname'] = $targetcourse->shortname;
            $report['course'] = $targetcourse->idnumber;
        }else{
            $report['shortname'] = '';
            $report['course'] = '';
        }



        foreach ($report as $data){
            $row[] = str_replace($delimiter, $encdelim, $data);
        }
        echo implode($delimiter, $row)."\n";
        }
    }

    if($reviews = get_records_sql('SELECT * FROM '.$CFG->prefix.'ilpconcern_posts')) {
        foreach ($reviews as $review) {
        $row = array();
        if (!$user = get_record('user', 'id', $review->setforuserid)) {
            continue;
        }
        if (!$reviewer = get_record('user', 'id', $review->setbyuserid)) {
            continue;
        }
        switch ($review->status) {
            case "0":
                $reviewstatus = get_string('report1', 'ilpconcern');
                break;
            case "1":
                $reviewstatus = get_string('report2', 'ilpconcern');
                break;
            case "2":
                $reviewstatus = get_string('report3', 'ilpconcern');
                break;
            case "3":
                $reviewstatus = get_string('report4', 'ilpconcern');
                break;
        }


        $report = array('username' => $user->username,
            'firstname' => $user->firstname,
            'lastname'  => $user->lastname,
            'idnumber'  => $user->idnumber,
            'status'    => ilp_get_status_report($user->id),
            'reportType' => $reviewstatus,
            'name' => '',
            'report' => cleanHTML($review->concernset),
            'dateSet'   => userdate($review->timecreated, get_string('strftimedate')),
            'deadline' => userdate($review->deadline, get_string('strftimedate')),
            'setBy' => fullname($reviewer),
            'tstatus' => ''
                    );
        if($review->courserelated == 1 && $review->targetcourse != SITEID){
            $reviewcourse = get_record('course','id',$review->targetcourse);
            $report['shortname'] = $reviewcourse->shortname;
            $report['course'] = $reviewcourse->idnumber;
        }else{
            $report['shortname'] = '';
            $report['course'] = '';
        }



        foreach ($report as $data){
            $row[] = str_replace($delimiter, $encdelim, $data);
        }
        echo implode($delimiter, $row)."\n";
        }
    die;
    }
}



if(has_capability('moodle/site:doanything',$sitecontext) || has_capability('block/ilp:viewclass',$coursecontext)){
    if ($mode == 'user') {
        ilp_report_byuser_csv($course->id);
    }elseif($mode == 'course'){
        ilp_report_bycourse_csv();
    }elseif($mode == 'allreviews'){
        ilp_report_allreviews_csv();
    }
}


?>
