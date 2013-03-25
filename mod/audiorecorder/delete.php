<?php  // $Id: delete.php,v 1.0 2008/01/10 12:00:00 TangWei Exp $

    require_once('../../config.php');
    require_once('lib.php');

    $id = optional_param('id', 0, PARAM_INT);  // Course module ID
    $a  = optional_param('a', 0, PARAM_INT);   // Audiorecorder ID

    if ($id) {
        if (! $cm = get_coursemodule_from_id('audiorecorder', $id)) {
            error("Course Module ID was incorrect");
        }

        if (! $audiorecorder = get_record('audiorecorder', 'id', $cm->instance)) {
            error("audiorecorder ID was incorrect");
        }

        if (! $course = get_record('course', 'id', $audiorecorder->course)) {
            error("Course is misconfigured");
        }
    } else {
        if (!$audiorecorder = get_record('audiorecorder', 'id', $a)) {
            error("Course module is incorrect");
        }
        if (! $course = get_record('course', 'id', $audiorecorder->course)) {
            error("Course is misconfigured");
        }
        if (! $cm = get_coursemodule_from_instance('audiorecorder', $audiorecorder->id, $course->id)) {
            error("Course Module ID was incorrect");
        }
    }

    require_login($course->id, false, $cm);

/// Load up the required audiorecorder code
    require($CFG->dirroot.'/mod/audiorecorder/type/'.$audiorecorder->audiorecordertype.'/audiorecorder.class.php');
    $arclass = 'audiorecorder_'.$audiorecorder->audiorecordertype;
    $arinstance = new $arclass($cm->id, $audiorecorder, $cm, $course);

    $arinstance->delete();   // delete something

?>
