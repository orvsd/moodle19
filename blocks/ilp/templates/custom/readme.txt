Making customised templates.

To create a custom display template you should copy the template.php file from /blocks/ilp/template.php to the 'custom' driectory. You can then make changes to this file to display customised display option within the ILP without altering the core code for upgrades.

To add attendance, punctuality or custom links to the Tutor and Learner Overview pages you can create a file called mis_lib.php in the 'custom' directory that contains the following functions:

function block_ilp_get_overall_attendance ($userid) { } 

function block_ilp_get_overall_punctuality ($userid) { }

These should both return an array with a number rounded to zero decimal places and a worded value 'green', 'amber' or 'red' to indicate the colour of the background.

function block_ilp_get_local_link($userid) { } should return a small piece of HTML to be displayed in the final column.

An example 'mis_lib.php' file is detailed below:

<?PHP // $Id: readme.txt,v 1.1.2.2 2010/11/07 12:12:58 ulcc Exp $

function block_ilp_get_overall_attendance ($userid) {

	$attendance = round(94.5678,0);
	$att_result = array($attendance);

	if($attendance > 0) {
      if($attendance > 85) {
        $att_result[] = 'green';
      }elseif($attendance >= 75 && $attendance <= 85){
        $att_result[] = 'amber';
      }elseif($attendance < 75) {
        $att_result[] = 'red';
      }
    }else{
      $att_result[] = 'none';
    }
	return $att_result;
	
}

function block_ilp_get_overall_punctuality ($userid) {

	$punctuality = round(84.3678,0);
	$pun_result = array($punctuality);
	
	if($punctuality > 0) {
      if($punctuality > 85) {
        $pun_result[] = 'green';
      }elseif($punctuality >= 75 && $punctuality <= 85){
        $pun_result[] = 'amber';
      }elseif($punctuality < 75) {
        $pun_result[] = 'red';
      }
    }else{
      $pun_result[] = 'none';
    }
	return $pun_result;
	
}

function block_ilp_get_local_link($userid) {
	return '<a href = "http://bbc.co.uk">BBC</a>';
}

?>