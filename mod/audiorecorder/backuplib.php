<?php //$Id: backuplib.php,v 0.8 2007/04/13 00:45:29 tangwei_ Exp $
    //This php script contains all the stuff to backup/restore
    //audiorecorder mods

    //This is the "graphical" structure of the audiorecorder mod:
    //
    //                     audiorecorder
    //                    (CL,pk->id)             
    //                        |
    //                        |
    //                        |
    //                 audiorecorder_submisions 
    //           (UL,pk->id, fk->audiorecorder,files)
    //
    // Meaning: pk->primary key field of the table
    //          fk->foreign key to link with parent
    //          nt->nested field (recursive data)
    //          CL->course level info
    //          UL->user level info
    //          files->table may have files)
    //
    //-----------------------------------------------------------

    //This function executes all the backup procedure about this mod
    function audiorecorder_backup_mods($bf,$preferences) {

        global $CFG;

        $status = true;

        //Iterate over audiorecorder table
        $audiorecorders = get_records ("audiorecorder","course",$preferences->backup_course,"id");
        if ($audiorecorders) {
            foreach ($audiorecorders as $audiorecorder) {
                if (backup_mod_selected($preferences,'audiorecorder',$audiorecorder->id)) {
                    $status = audiorecorder_backup_one_mod($bf,$preferences,$audiorecorder);
                    // backup files happens in backup_one_mod now too.
                }
            }
        }
        return $status;  
    }

    function audiorecorder_backup_one_mod($bf,$preferences,$audiorecorder) {
        
        global $CFG;
    
        if (is_numeric($audiorecorder)) {
            $audiorecorder = get_record('audiorecorder','id',$audiorecorder);
        }
    
        $status = true;

        //Start mod
        fwrite ($bf,start_tag("MOD",3,true));
        //Print audiorecorder data
        fwrite ($bf,full_tag("ID",4,false,$audiorecorder->id));
        fwrite ($bf,full_tag("MODTYPE",4,false,"audiorecorder"));
        fwrite ($bf,full_tag("NAME",4,false,$audiorecorder->name));
        fwrite ($bf,full_tag("INTRO",4,false,$audiorecorder->intro));
        fwrite ($bf,full_tag("GRADE",4,false,$audiorecorder->grade));
        fwrite ($bf,full_tag("RESUBMIT",4,false,$audiorecorder->resubmit));
        fwrite ($bf,full_tag("PREVENTLATE",4,false,$audiorecorder->preventlate));
        fwrite ($bf,full_tag("MAXBYTES",4,false,$audiorecorder->maxbytes));
        fwrite ($bf,full_tag("TIMEDUE",4,false,$audiorecorder->timedue));
        fwrite ($bf,full_tag("TIMEAVAILABLE",4,false,$audiorecorder->timeavailable));
        fwrite ($bf,full_tag("TIMEMODIFIED",4,false,$audiorecorder->timemodified));
        //if we've selected to backup users info, then execute backup_audiorecorder_submisions and
        //backup_audiorecorder_files_instance
        if (backup_userdata_selected($preferences,'audiorecorder',$audiorecorder->id)) {
            $status = backup_audiorecorder_submissions($bf,$preferences,$audiorecorder->id);
            if ($status) {
                $status = backup_audiorecorder_files_instance($bf,$preferences,$audiorecorder->id);
            }
        }
        //End mod
        $status =fwrite ($bf,end_tag("MOD",3,true));

        return $status;
    }

    //Backup audiorecorder_submissions contents (executed from audiorecorder_backup_mods)
    function backup_audiorecorder_submissions ($bf,$preferences,$audiorecorder) {

        global $CFG;

        $status = true;

        $audiorecorder_submissions = get_records("audiorecorder_submissions","audiorecorder",$audiorecorder,"id");
        //If there is submissions
        if ($audiorecorder_submissions) {
            //Write start tag
            $status =fwrite ($bf,start_tag("SUBMISSIONS",4,true));
            //Iterate over each submission
            foreach ($audiorecorder_submissions as $ass_sub) {
                //Start submission
                $status =fwrite ($bf,start_tag("SUBMISSION",5,true));
                //Print submission contents
                fwrite ($bf,full_tag("ID",6,false,$ass_sub->id));       
                fwrite ($bf,full_tag("USERID",6,false,$ass_sub->userid));       
                fwrite ($bf,full_tag("TIMECREATED",6,false,$ass_sub->timecreated));       
                fwrite ($bf,full_tag("TIMEMODIFIED",6,false,$ass_sub->timemodified));       
                fwrite ($bf,full_tag("NUMFILES",6,false,$ass_sub->numfiles));       
                fwrite ($bf,full_tag("DATA1",6,false,$ass_sub->data1));       
                fwrite ($bf,full_tag("DATA2",6,false,$ass_sub->data2));       
                fwrite ($bf,full_tag("GRADE",6,false,$ass_sub->grade));       
                fwrite ($bf,full_tag("COMMENT",6,false,$ass_sub->comment));       
                fwrite ($bf,full_tag("FORMAT",6,false,$ass_sub->format));       
                fwrite ($bf,full_tag("TEACHER",6,false,$ass_sub->teacher));       
                fwrite ($bf,full_tag("TIMEMARKED",6,false,$ass_sub->timemarked));       
                fwrite ($bf,full_tag("MAILED",6,false,$ass_sub->mailed));       
                //End submission
                $status =fwrite ($bf,end_tag("SUBMISSION",5,true));
            }
            //Write end tag
            $status =fwrite ($bf,end_tag("SUBMISSIONS",4,true));
        }
        return $status;
    }

    //Backup audiorecorder files because we've selected to backup user info
    //and files are user info's level
    function backup_audiorecorder_files($bf,$preferences) {

        global $CFG;
       
        $status = true;

        //First we check to moddata exists and create it as necessary
        //in temp/backup/$backup_code  dir
        $status = check_and_create_moddata_dir($preferences->backup_unique_code);
        //Now copy the audiorecorder dir
        if ($status) {
            //Only if it exists !! Thanks to Daniel Miksik.
            if (is_dir($CFG->dataroot."/".$preferences->backup_course."/".$CFG->moddata."/audiorecorder")) {
                $status = backup_copy_file($CFG->dataroot."/".$preferences->backup_course."/".$CFG->moddata."/audiorecorder",
                                           $CFG->dataroot."/temp/backup/".$preferences->backup_unique_code."/moddata/audiorecorder");
            }
        }

        return $status;

    } 

    function backup_audiorecorder_files_instance($bf,$preferences,$instanceid) {

        global $CFG;
       
        $status = true;

        //First we check to moddata exists and create it as necessary
        //in temp/backup/$backup_code  dir
        $status = check_and_create_moddata_dir($preferences->backup_unique_code);
        $status = check_dir_exists($CFG->dataroot."/temp/backup/".$preferences->backup_unique_code."/moddata/audiorecorder/",true);
        //Now copy the audiorecorder dir
        if ($status) {
            //Only if it exists !! Thanks to Daniel Miksik.
            if (is_dir($CFG->dataroot."/".$preferences->backup_course."/".$CFG->moddata."/audiorecorder/".$instanceid)) {
                $status = backup_copy_file($CFG->dataroot."/".$preferences->backup_course."/".$CFG->moddata."/audiorecorder/".$instanceid,
                                           $CFG->dataroot."/temp/backup/".$preferences->backup_unique_code."/moddata/audiorecorder/".$instanceid);
            }
        }

        return $status;

    } 

    //Return an array of info (name,value)
    function audiorecorder_check_backup_mods($course,$user_data=false,$backup_unique_code,$instances=null) {
        if (!empty($instances) && is_array($instances) && count($instances)) {
            $info = array();
            foreach ($instances as $id => $instance) {
                $info += audiorecorder_check_backup_mods_instances($instance,$backup_unique_code);
            }
            return $info;
        }
        //First the course data
        $info[0][0] = get_string("modulenameplural","audiorecorder");
        if ($ids = audiorecorder_ids ($course)) {
            $info[0][1] = count($ids);
        } else {
            $info[0][1] = 0;
        }

        //Now, if requested, the user_data
        if ($user_data) {
            $info[1][0] = get_string("submissions","audiorecorder");
            if ($ids = audiorecorder_submission_ids_by_course ($course)) { 
                $info[1][1] = count($ids);
            } else {
                $info[1][1] = 0;
            }
        }
        return $info;
    }

    //Return an array of info (name,value)
    function audiorecorder_check_backup_mods_instances($instance,$backup_unique_code) {
        $info[$instance->id.'0'][0] = '<b>'.$instance->name.'</b>';
        $info[$instance->id.'0'][1] = '';
        if (!empty($instance->userdata)) {
            $info[$instance->id.'1'][0] = get_string("submissions","audiorecorder");
            if ($ids = audiorecorder_submission_ids_by_instance ($instance->id)) {
                $info[$instance->id.'1'][1] = count($ids);
            } else {
                $info[$instance->id.'1'][1] = 0;
            }
        }
        return $info;
    }

    //Return a content encoded to support interactivities linking. Every module
    //should have its own. They are called automatically from the backup procedure.
    function audiorecorder_encode_content_links ($content,$preferences) {

        global $CFG;

        $base = preg_quote($CFG->wwwroot,"/");

        //Link to the list of audiorecorders
        $buscar="/(".$base."\/mod\/audiorecorder\/index.php\?id\=)([0-9]+)/";
        $result= preg_replace($buscar,'$@audiorecorderINDEX*$2@$',$content);

        //Link to audiorecorder view by moduleid
        $buscar="/(".$base."\/mod\/audiorecorder\/view.php\?id\=)([0-9]+)/";
        $result= preg_replace($buscar,'$@audiorecorderVIEWBYID*$2@$',$result);

        return $result;
    }

    // INTERNAL FUNCTIONS. BASED IN THE MOD STRUCTURE

    //Returns an array of audiorecorders id 
    function audiorecorder_ids ($course) {

        global $CFG;

        return get_records_sql ("SELECT a.id, a.course
                                 FROM {$CFG->prefix}audiorecorder a
                                 WHERE a.course = '$course'");
    }
    
    //Returns an array of audiorecorder_submissions id
    function audiorecorder_submission_ids_by_course ($course) {

        global $CFG;

        return get_records_sql ("SELECT s.id , s.audiorecorder
                                 FROM {$CFG->prefix}audiorecorder_submissions s,
                                      {$CFG->prefix}audiorecorder a
                                 WHERE a.course = '$course' AND
                                       s.audiorecorder = a.id");
    }

    //Returns an array of audiorecorder_submissions id
    function audiorecorder_submission_ids_by_instance ($instanceid) {

        global $CFG;

        return get_records_sql ("SELECT s.id , s.audiorecorder
                                 FROM {$CFG->prefix}audiorecorder_submissions s
                                 WHERE s.audiorecorder = $instanceid");
    }
?>
