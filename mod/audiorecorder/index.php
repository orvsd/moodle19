<?PHP // $Id: index.php,v 1.1 2006/01/06 02:45:19 moodler Exp $

/// This page lists all the instances of NEWMODULE in a particular course
/// Replace NEWMODULE with the name of your module

    require_once("../../config.php");
    require_once("lib.php");

    $id = optional_param('id', PARAM_INT);    // Course Module ID

    if (! $course = get_record("course", "id", $id)) {
        error("Course ID is incorrect");
    }

    require_login($course->id);

    add_to_log($course->id, "audiorecorder", "view all", "index.php?id=$course->id", "");


/// Get all required strings

    $strars = get_string("modulenameplural", "audiorecorder");
    $strar  = get_string("modulename", "audiorecorder");


/// Print the header
    /*
    if ($course->category) {
        $navigation = "<A HREF=\"../../course/view.php?id=$course->id\">$course->shortname</A> ->";
    }
    */

    print_header_simple("$course->shortname: $strars", "$course->fullname", "$navigation $strars", "", "", true, "", navmenu($course));

/// Get all the appropriate data

    if (! $ars = get_all_instances_in_course("audiorecorder", $course)) {
        notice("There are no audiorecorders", "../../course/view.php?id=$course->id");
        die;
    }

/// Print the list of instances (your module will probably extend this)

    $timenow = time();
    $strname  = get_string("name");
    $strweek  = get_string("week");
    $strtopic  = get_string("topic");
    $strduedate = get_string("duedate", "audiorecorder");
    $strsubmitted = get_string("submitted", "audiorecorder");
    $strgrade = get_string("grade");

    if ($course->format == "weeks") {
        $table->head  = array ($strweek, $strname, $strduedate, $strsubmitted, $strgrade);
        $table->align = array ("CENTER", "LEFT", "LEFT", "LEFT", "LEFT");
    } else if ($course->format == "topics") {
        $table->head  = array ($strtopic, $strname, $strduedate, $strsubmitted, $strgrade);
        $table->align = array ("CENTER", "LEFT", "LEFT", "LEFT", "LEFT");
    } else {
        $table->head  = array ($strname, $strduedate, $strsubmitted, $strgrade);
        $table->align = array ("LEFT", "LEFT", "LEFT","RIGHT");
    }

    foreach ($ars as $ar) {
        if (!file_exists($CFG->dirroot.'/mod/audiorecorder/type/'.$ar->audiorecordertype.'/audiorecorder.class.php')) {
            continue;
        }

        require_once ($CFG->dirroot.'/mod/audiorecorder/type/'.$ar->audiorecordertype.'/audiorecorder.class.php');
        $arclass = 'audiorecorder_'.$ar->audiorecordertype;
        $arinstance = new $arclass($ar->coursemodule);
            
        $submitted = $arinstance->submittedlink();
        
        $grade = '-';
        if ($submission = $arinstance->get_submission($USER->id)) {
            if ($submission->timemarked) {
                $grade = $arinstance->display_grade($submission->grade);
            }
        }
        
        if (!$ar->visible) {
            //Show dimmed if the mod is hidden
            $link = "<A class=\"dimmed\" HREF=\"view.php?id=$ar->coursemodule\">$ar->name</A>";
        } else {
            //Show normal if the mod is visible
            $link = "<A HREF=\"view.php?id=$ar->coursemodule\">$ar->name</A>";
        }
        $due = $ar->timedue ? userdate($ar->timedue) : '-';
        
        if ($course->format == "weeks" or $course->format == "topics") {
            $table->data[] = array ($ar->section, $link, $due, $submitted, $grade);
        } else {
            $table->data[] = array ($link, $due, $submitted, $grade);
        }
    }

    echo "<BR>";

    print_table($table);

/// Finish the page

    print_footer($course);

?>
