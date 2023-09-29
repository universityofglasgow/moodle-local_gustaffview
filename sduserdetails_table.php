<?php

require_once(dirname(dirname(__FILE__)).'../../config.php');
global $CFG,$USER, $DB;

require "$CFG->libdir/tablelib.php";


class sduserdetailscurrent_table extends table_sql
{

    /**
     * Constructor
     * @param int $unequeid all tables have to have a unique id, this is used
     *      as a key when storing table properties like sort order in the session.
     */
    function __construct($unequeid)
    {
        parent::__construct($unequeid);
        // Define the list of columns to show.


//        $columns = array('assessment', 'assessmenttype', 'itemmodule', 'duedate', 'includedingcat', 'status', 'yourgrade', 'feedback');
        $columns = array('assessment', 'assessmenttype', 'itemmodule', 'duedate', 'includedingcat', 'status', 'yourgrade', 'feedback');
        $this->define_columns($columns);

        $tdr = optional_param('tdr', '', PARAM_INT);
        $ts = optional_param('ts', '', PARAM_ALPHA);
        $courseid = optional_param('courseid', '0', PARAM_INT);


        $selectcourse = optional_param('selectcourse', '', PARAM_INT);
        $selectgroup = optional_param('selectgroup', '', PARAM_TEXT);
        $selectstudent = optional_param('selectstudent', '', PARAM_TEXT);

        $tdrnew = 4;

        $tdirdd_icon = '';
        if ($tdr==4 && $ts=="duedate") {
            $tdirdd_icon = ' <i class="fa fa-caret-down"></i>';
            $tdrnew = 3;
        }
        if ($tdr==3 && $ts=="duedate") {
            $tdirdd_icon = ' <i class="fa fa-caret-up"></i>';
            $tdrnew = 4;
        }

        $tdirat_icon = '';
        if ($tdr==4 && $ts=="assessmenttype") {
            $tdirat_icon = ' <i class="fa fa-caret-down"></i>';
            $tdrnew = 3;
        }
        if ($tdr==3 && $ts=="assessmenttype") {
            $tdirat_icon = ' <i class="fa fa-caret-up"></i>';
            $tdrnew = 4;
        }


        $headers = array(
            get_string('assessment'),
            '<a href="sduserdetails.php?t=1&selectgroup='.$selectgroup.'&selectstudent=' . $selectstudent . '&ts=assessmenttype&tdr=' . $tdrnew . '&selectcourse=' . $selectcourse . '">' . get_string('assessmenttype','block_newgu_spdetails') . $tdirat_icon . '</a>',
            get_string('activity') . ' type',
            '<a href="sduserdetails.php?t=1&selectgroup='.$selectgroup.'&selectstudent=' . $selectstudent . '&ts=duedate&tdr=' . $tdrnew . '&selectcourse=' . $selectcourse . '">' . get_string('duedate','block_newgu_spdetails') . $tdirdd_icon . '</a>',
            get_string('source', 'block_newgu_spdetails'),
            get_string('status'),
            get_string('yourgrade', 'block_newgu_spdetails'),
            get_string('feedback')
        );
        $this->define_headers($headers);

    }

    function col_assessment($values){
      global $DB, $CFG;
      $itemname = $values->itemname;

      $modulename = $values->itemmodule;
      $iteminstance = $values->iteminstance;
      $courseid = $values->courseid;

      $cmid = block_newgu_spdetails_external::get_cmid($modulename, $courseid, $iteminstance);

      $link = $CFG->wwwroot . '/mod/' . $modulename . '/view.php?id=' . $cmid;

      if (!empty($link)) {
          return $itemname;
      }
    }

    function col_includedingcat($values){
      global $DB, $CFG;
      $cmid = $values->id;
      $modulename = $values->itemmodule;
      $iteminstance = $values->iteminstance;
      $courseid = $values->courseid;
      $categoryid = $values->categoryid;
      $itemid = $values->id;

      $itemname = $values->itemname;

      $cfdvalue = 0;

      $arr_customfield = $DB->get_record('customfield_field', array('shortname'=>'show_on_studentdashboard'));
      $cffid = $arr_customfield->id;

     $arr_customfielddata = $DB->get_record('customfield_data', array('fieldid'=>$cffid, 'instanceid'=>$courseid));

     if (!empty($arr_customfielddata)) {
          $cfdvalue = $arr_customfielddata->value;
     }

      if ($cfdvalue==1) {
          return "Old GCAT";
      } else {
          return "Gradebook";
      }
    }

    function col_itemmodule($values){
        return $values->itemmodule;
    }

    function col_assessmenttype($values){

      global $DB;

      $cmid = $values->id;
      $modulename = $values->itemmodule;
      $iteminstance = $values->iteminstance;
      $courseid = $values->courseid;
      $categoryid = $values->categoryid;
      $itemid = $values->id;

      $arr_gradecategory = $DB->get_record('grade_categories',array('courseid'=>$courseid, 'id'=>$categoryid));
      if (!empty($arr_gradecategory)) {
        $gradecategoryname = $arr_gradecategory->fullname;
      }

      $aggregationcoef = $values->aggregationcoef;

      $assessmenttype = block_newgu_spdetails_external::return_assessmenttype($gradecategoryname, $aggregationcoef);

      return $assessmenttype;

    }

    function col_weight($values){

      global $DB;

      $cmid = $values->id;
      $modulename = $values->itemmodule;
      $iteminstance = $values->iteminstance;
      $courseid = $values->courseid;
      $categoryid = $values->categoryid;

      $aggregationcoef = $values->aggregationcoef;
      $aggregationcoef2 = $values->aggregationcoef2;

      $finalweight = get_weight($courseid,$categoryid,$aggregationcoef,$aggregationcoef2);
      return $finalweight;

    }

    function col_duedate($values){

      global $DB;

      $userid = $values->userid;
      $modulename = $values->itemmodule;
      $iteminstance = $values->iteminstance;
      $courseid = $values->courseid;
      $itemid = $values->id;


      $duedate = 0;
      $extspan = "";
      $extensionduedate = 0;

      // READ individual TABLE OF ACTIVITY (MODULE)
      if ($modulename!="") {
        $arr_duedate = $DB->get_record($modulename,array('course'=>$courseid, 'id'=>$iteminstance));

      if (!empty($arr_duedate)) {
        if ($modulename=="assign") {
          $duedate = $arr_duedate->duedate;

          $arr_userflags = $DB->get_record('assign_user_flags', array('userid'=>$userid, 'assignment'=>$iteminstance));

          if ($arr_userflags) {
          $extensionduedate = $arr_userflags->extensionduedate;
          if ($extensionduedate>0) {
            $extspan = '<a href="javascript:void(0)" title="' . get_string('extended', 'block_newgu_spdetails') . '" class="extended">*</a>';
          }
          }

        }
        if ($modulename=="forum") {
          $duedate = $arr_duedate->duedate;
        }
        if ($modulename=="quiz") {
          $duedate = $arr_duedate->timeclose;
        }
        if ($modulename=="workshop") {
          $duedate = $arr_duedate->submissionend;
        }
      }
    }

      if ($duedate!=0) {
        return date("d/m/Y", $duedate) . $extspan;
      } else {
        return get_string('noduedate', 'block_newgu_spdetails');
      }


    }



    function col_status($values){

      global $DB, $CFG;

      $link = "";
      $status = "";

      $userid = $values->userid;
      $modulename = $values->itemmodule;
      $iteminstance = $values->iteminstance;
      $courseid = $values->courseid;
      $itemid = $values->id;


      $gradestatus = block_newgu_spdetails_external::return_gradestatus($modulename, $iteminstance, $courseid, $itemid, $userid);

      $status = $gradestatus["status"];
      $link = $gradestatus["link"];
      $allowsubmissionsfromdate = $gradestatus["allowsubmissionsfromdate"];
      $duedate = $gradestatus["duedate"];
      $cutoffdate = $gradestatus["cutoffdate"];

      $finalgrade = $gradestatus["finalgrade"];

      $statustodisplay = "";

      if($status == 'tosubmit'){
        $statustodisplay = '<span class="status-item status-submit">'.get_string('submit').'</span> ';
      }
      if($status == 'notsubmitted'){
        $statustodisplay = '<span class="status-item">'.get_string('notsubmitted', 'block_newgu_spdetails').'</span> ';
      }
      if($status == 'submitted'){
        $statustodisplay = '<span class="status-item status-submitted">'. ucwords(trim(get_string('submitted', 'block_newgu_spdetails'))) . '</span> ';
        if ($finalgrade!=Null) {
          $statustodisplay = '<span class="status-item status-item status-graded">'.get_string('graded', 'block_newgu_spdetails').'</span>';
        }
      }
      if($status == "notopen"){
        $statustodisplay = '<span class="status-item">' . get_string('submissionnotopen', 'block_newgu_spdetails') . '</span> ';
      }
      if($status == "TO_BE_ASKED"){
        $statustodisplay = '<span class="status-item status-graded">' . get_string('individualcomponents', 'block_newgu_spdetails') . '</span> ';
      }
      if($status == "overdue"){
        $statustodisplay = '<span class="status-item status-overdue">' . get_string('overdue', 'block_newgu_spdetails') . '</span> ';
      }

      return $statustodisplay;

    }

    function col_yourgrade($values){

      global $DB, $CFG;

      $userid = $values->userid;
      $modulename = $values->itemmodule;
      $iteminstance = $values->iteminstance;
      $courseid = $values->courseid;
      $itemid = $values->id;
      $gradetype = $values->gradetype;

      $link = "";

      $arr_gradetodisplay = get_gradefeedback($modulename, $iteminstance, $courseid, $itemid, $userid, $values->grademax, $gradetype);
      $link = $arr_gradetodisplay["link"];
      $gradetodisplay = $arr_gradetodisplay["gradetodisplay"];

      return $gradetodisplay;
    }



    function col_feedback($values){

      global $DB, $CFG;

      $userid = $values->userid;
      $modulename = $values->itemmodule;
      $iteminstance = $values->iteminstance;
      $courseid = $values->courseid;
      $itemid = $values->id;
      $gradetype = $values->gradetype;

      $link = "";

      $feedback = get_gradefeedback($modulename, $iteminstance, $courseid, $itemid, $userid, $values->grademax, $gradetype);
      $gradetodisplay = $feedback["gradetodisplay"];

      return $gradetodisplay;

    }

}





class sduserdetailspast_table extends table_sql
{

    /**
     * Constructor
     * @param int $unequeid all tables have to have a unique id, this is used
     *      as a key when storing table properties like sort order in the session.
     */
    function __construct($unequeid)
    {
        parent::__construct($unequeid);
        // Define the list of columns to show.


        $columns = array('coursename', 'assessment', 'assessmenttype', 'weight', 'startdate', 'enddate', 'viewsubmission', 'yourgrade', 'feedback');
//        $columns = array('coursename', 'assessment', 'itemmodule', 'assessmenttype', 'includedingcat', 'weight', 'startdate', 'enddate', 'viewsubmission', 'yourgrade', 'feedback');

        $this->define_columns($columns);

        // Define the titles of columns to show in header.

        $tdr = optional_param('tdr', '', PARAM_INT);
        $ts = optional_param('ts', '', PARAM_ALPHA);

        $tdrnew = 4;

        $tdircn_icon = '';
        if ($tdr==4 && $ts=="coursename") {
            $tdircn_icon = ' <i class="fa fa-caret-down"></i>';
            $tdrnew = 3;
        }
        if ($tdr==3 && $ts=="coursename") {
            $tdircn_icon = ' <i class="fa fa-caret-up"></i>';
            $tdrnew = 4;
        }

        $tdirsd_icon = '';
        if ($tdr==4 && $ts=="startdate") {
            $tdirsd_icon = ' <i class="fa fa-caret-down"></i>';
            $tdrnew = 3;
        }
        if ($tdr==3 && $ts=="startdate") {
            $tdirsd_icon = ' <i class="fa fa-caret-up"></i>';
            $tdrnew = 4;
        }

        $tdired_icon = '';
        if ($tdr==4 && $ts=="enddate") {
            $tdired_icon = ' <i class="fa fa-caret-down"></i>';
            $tdrnew = 3;
        }
        if ($tdr==3 && $ts=="enddate") {
            $tdired_icon = ' <i class="fa fa-caret-up"></i>';
            $tdrnew = 4;
        }

        $tdirat_icon = '';
        if ($tdr==4 && $ts=="assessmenttype") {
            $tdirat_icon = ' <i class="fa fa-caret-down"></i>';
            $tdrnew = 3;
        }
        if ($tdr==3 && $ts=="assessmenttype") {
            $tdirat_icon = ' <i class="fa fa-caret-up"></i>';
            $tdrnew = 4;
        }

        $headers = array(
            '<a href="view.php?t=2&ts=coursename&tdr=' . $tdrnew . '">' . get_string('course') . $tdircn_icon . '</a>',
/*            get_string('coursecode', 'local_gu_staffview'), */
            get_string('assessment'),
/*            get_string('activity') . ' type', */
            '<a href="view.php?t=2&ts=assessmenttype&tdr=' . $tdrnew . '">' . get_string('assessmenttype','block_newgu_spdetails') . $tdirat_icon . '</a>',
/*            get_string('source', 'local_gu_staffview'), */
            get_string('weight', 'block_newgu_spdetails'),
            '<a href="view.php?t=2&ts=startdate&tdr=' . $tdrnew . '">' . get_string('startdate','block_newgu_spdetails') . $tdirsd_icon . '</a>',
            '<a href="view.php?t=2&ts=enddate&tdr=' . $tdrnew . '">' . get_string('enddate','block_newgu_spdetails') . $tdired_icon . '</a>',
            get_string('viewsubmission','block_newgu_spdetails'),
            get_string('yourgrade', 'block_newgu_spdetails'),
            get_string('feedback')
        );
        $this->define_headers($headers);
    }

    function col_coursename($values){
      global $CFG;
      $courseid = $values->courseid;
      $link = $CFG->wwwroot . "/course/view.php?id=" . $courseid;

      return "<a href='".$link."'>" . $values->coursename . "</a>";

    }

    function col_assessment($values){
      global $DB, $CFG;

      $itemname = $values->itemname;

      $modulename = $values->itemmodule;
      $iteminstance = $values->iteminstance;
      $courseid = $values->courseid;

      $cmid = block_newgu_spdetails_external::get_cmid($modulename, $courseid, $iteminstance);

      $link = $CFG->wwwroot . '/mod/' . $modulename . '/view.php?id=' . $cmid;

      if (!empty($link)) {
          return '<a href="' . $link . '">' . $itemname . '</a>';
      }

    }

    function col_assessmenttype($values){

      global $DB;

      $cmid = $values->id;
      $modulename = $values->itemmodule;
      $iteminstance = $values->iteminstance;
      $courseid = $values->courseid;
      $categoryid = $values->categoryid;

      $arr_gradecategory = $DB->get_record('grade_categories',array('courseid'=>$courseid, 'id'=>$categoryid));
      if (!empty($arr_gradecategory)) {
        $gradecategoryname = $arr_gradecategory->fullname;
      }

      $aggregationcoef = $values->aggregationcoef;

      $assessmenttype = block_newgu_spdetails_external::return_assessmenttype($gradecategoryname, $aggregationcoef);


      return $assessmenttype ;

    }
/*
    function col_includedingcat($values){
      global $DB, $CFG;
      $cmid = $values->id;
      $modulename = $values->itemmodule;
      $iteminstance = $values->iteminstance;
      $courseid = $values->courseid;
      $categoryid = $values->categoryid;
      $itemid = $values->id;

      $itemname = $values->itemname;

      $cfdvalue = 0;

      $arr_customfield = $DB->get_record('customfield_field', array('shortname'=>'show_on_studentdashboard'));
      $cffid = $arr_customfield->id;

     $arr_customfielddata = $DB->get_record('customfield_data', array('fieldid'=>$cffid, 'instanceid'=>$courseid));


     if (!empty($arr_customfielddata)) {
          $cfdvalue = $arr_customfielddata->value;
     }

      if ($cfdvalue==1) {
          return "Old GCAT";
      } else {
          return "Gradebook";
      }

    }
*/
    function col_weight($values){

      global $DB;

      $cmid = $values->id;
      $modulename = $values->itemmodule;
      $iteminstance = $values->iteminstance;
      $courseid = $values->courseid;
      $categoryid = $values->categoryid;
      $aggregationcoef = $values->aggregationcoef;
      $aggregationcoef2 = $values->aggregationcoef2;

      $finalweight = get_weight($courseid,$categoryid,$aggregationcoef,$aggregationcoef2);
      return $finalweight;

    }


    function col_startdate($values){

      global $DB;

//      return $values->startdate;


      $modulename = $values->itemmodule;
      $iteminstance = $values->iteminstance;
      $courseid = $values->courseid;
      $itemid = $values->id;

      $submissionstartdate = 0;

      // READ individual TABLE OF ACTIVITY (MODULE)
      if ($modulename!="") {
        $arr_submissionstartdate = $DB->get_record($modulename,array('course'=>$courseid, 'id'=>$iteminstance));

      if (!empty($arr_submissionstartdate)) {
        if ($modulename=="assign") {
          $submissionstartdate = $arr_submissionstartdate->allowsubmissionsfromdate;
        }
        if ($modulename=="forum") {
          $submissionstartdate = $arr_submissionstartdate->assesstimestart;
        }
        if ($modulename=="quiz") {
          $submissionstartdate = $arr_submissionstartdate->timeopen;
        }
        if ($modulename=="workshop") {
          $submissionstartdate = $arr_submissionstartdate->submissionstart;
        }
      }
    }

      if ($submissionstartdate!=0) {
        return date("d/m/Y", $submissionstartdate);
      }

    }


    function col_enddate($values){

      global $DB;

      $modulename = $values->itemmodule;
      $iteminstance = $values->iteminstance;
      $courseid = $values->courseid;
      $itemid = $values->id;


      $duedate = 0;

      // READ individual TABLE OF ACTIVITY (MODULE)
      if ($modulename!="") {
        $arr_duedate = $DB->get_record($modulename,array('course'=>$courseid, 'id'=>$iteminstance));


      if (!empty($arr_duedate)) {
        if ($modulename=="assign" || $modulename=="forum") {
          $duedate = $arr_duedate->duedate;
        }
        if ($modulename=="quiz") {
          $duedate = $arr_duedate->timeclose;
        }
        if ($modulename=="workshop") {
          $duedate = $arr_duedate->submissionend;
        }
      }
    }

      if ($duedate!=0) {
        return date("d/m/Y", $duedate);
      }

    }

    function col_viewsubmission($values){

      global $DB, $USER, $CFG;

      $modulename = $values->itemmodule;
      $iteminstance = $values->iteminstance;
      $courseid = $values->courseid;
      $itemid = $values->id;
      $link = "";

      $status="";

      $cmid = block_newgu_spdetails_external::get_cmid($modulename, $courseid, $iteminstance);

      $link = $CFG->wwwroot . '/mod/' . $modulename . '/view.php?id=' . $cmid;

      if (!empty($link)) {
          return '<a href="' . $link . '">' . get_string('viewsubmission', 'block_newgu_spdetails') . '</a>';
      }
    }

    function col_yourgrade($values){

            global $DB, $USER, $CFG;

            $modulename = $values->itemmodule;
            $iteminstance = $values->iteminstance;
            $courseid = $values->courseid;
            $itemid = $values->id;
            $gradetype = $values->gradetype;

            $link = "";

            $feedback = get_gradefeedback($modulename, $iteminstance, $courseid, $itemid, $USER->id, $values->grademax, $gradetype);
            $link = $feedback["link"];

            $rawgrade = $feedback["rawgrade"];
            $finalgrade = $feedback["finalgrade"];

            $gradetodisplay = $feedback["gradetodisplay"];
            $provisional_22grademaxpoint = $feedback["provisional_22grademaxpoint"];
            $converted_22grademaxpoint = $feedback["converted_22grademaxpoint"];

            return $gradetodisplay;

    }

    function col_feedback($values){

      global $DB, $USER, $CFG;

      $modulename = $values->itemmodule;
      $iteminstance = $values->iteminstance;
      $courseid = $values->courseid;
      $itemid = $values->id;
      $gradetype = $values->gradetype;

      $link = "";

      $feedback = get_gradefeedback($modulename, $iteminstance, $courseid, $itemid, $USER->id, $values->grademax, $gradetype);
      $link = $feedback["link"];
      $gradetodisplay = $feedback["gradetodisplay"];

      if ($link!="") {
        return '<a href="' . $link . '">' . get_string('readfeedback', 'block_newgu_spdetails') . '</a>';
      } else {
        if ($modulename!="quiz") {
          return $gradetodisplay;
        }
      }

    }

}
