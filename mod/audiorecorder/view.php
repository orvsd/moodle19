<?PHP  // $Id: view.php,v 1.1 2003/09/30 02:45:19 moodler Exp $

/// This page prints a particular instance of NEWMODULE
/// (Replace NEWMODULE with the name of your module)

    require_once("../../config.php");
    require_once("lib.php");
    $id = optional_param('id', 0, PARAM_INT);    // Course Module ID
    $a  = optional_param('a', 0, PARAM_INT);  //  audiorecorder ID

    if ($id) {
        if (! $cm = get_record("course_modules", "id", $id)) {
            error("Course Module ID was incorrect");
        }
    
        if (! $course = get_record("course", "id", $cm->course)) {
            error("Course is misconfigured");
        }
    
        if (! $ar = get_record("audiorecorder", "id", $cm->instance)) {
            error("Course module is incorrect");
        }

    } else {
        if (! $ar = get_record("audiorecorder", "id", $a)) {
            error("Course module is incorrect");
        }
        if (! $course = get_record("course", "id", $ar->course)) {
            error("Course is misconfigured");
        }
        if (! $cm = get_coursemodule_from_instance("audiorecorder", $ar->id, $course->id)) {
            error("Course Module ID was incorrect");
        }
    }

    require_login($course->id);

    add_to_log($course->id, "audiorecorder", "view", "view.php?id=$cm->id", "$ar->id");

/// Print the page header

    if ($course->category) {
        $navigation = "<A HREF=\"../../course/view.php?id=$course->id\">$course->shortname</A> ->";
    }

    $strars = get_string("modulenameplural", "audiorecorder");
    $strar  = get_string("modulename", "audiorecorder");
    print_header_simple(format_string($ar->name), "",
                 "<a href=\"index.php?id=$course->id\">$strars</a> -> ".format_string($ar->name), "", "", true,
                  update_module_button($cm->id, $course->id, $strar), navmenu($course, $cm));

/// Print the main part of the page
    //$arinstance=new audiorecorder_uploadsingle($cm->id, $ar, $cm, $course);
    require ("$CFG->dirroot/mod/audiorecorder/type/$ar->audiorecordertype/audiorecorder.class.php");
    $arclass = "audiorecorder_$ar->audiorecordertype";
    $arinstance = new $arclass($cm->id, $ar, $cm, $course);
    
    //include audio_player flash file
    $context = get_context_instance(CONTEXT_MODULE, $cm->id);
    if (has_capability('mod/audiorecorder:view', $context)) {
        include_once "ar_flash.php";
    }else {
        require_capability('mod/audiorecorder:view', $context);
    }


/// Finish the page
    print_footer($course);

?>