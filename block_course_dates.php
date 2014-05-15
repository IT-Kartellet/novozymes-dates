<?php

include_once($CFG->dirroot . '/course/lib.php');
include_once($CFG->libdir . '/coursecatlib.php');

class block_course_dates extends block_list {
    function init() {
        $this->title = get_string("mycourses");
    }

    function has_config() {
        return true;
    }

    function get_content() {
        global $CFG, $USER, $DB, $OUTPUT, $PAGE;
        $PAGE->requires->jquery();
        $PAGE->requires->js("/blocks/course_dates/core.js");


        if($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';
        $icon  = '<img src="' . $OUTPUT->pix_url('i/course') . '" class="icon" alt="" />';
        
        $front_end = "";
        $hasGrow = false;

        // Add old GROW courses
        if (@$growCourses = $DB->get_records("grow_legacy", array("username" => strtoupper($USER->username)))) {

            $front_end .= "<div class='meta_year'><h3>Old courses</h3>";

            foreach ($growCourses as $grow) {
                $front_end .="
                     <div class='meta_course_block'><span>" . $icon . $grow->coursename . "</span><span class='meta_course_date'> Date:  ".date("Y-m-d", $grow->timestart)."</span></div>";
            }

            $front_end .= "</div>";
            $hasGrow = true;
        }

        // Get users courses
        if ($courses = enrol_get_my_courses(NULL, 'visible DESC, fullname ASC')) {

            // Add current courses
            array_walk($courses, function ($course) use ($DB){
                $date = $DB->get_records_sql("SELECT * FROM {meta_datecourse} where courseid = :cid", array("cid"=>$course->id));
                $date = reset($date);
                if ($date) {   
                    $course->date = $date->startdate;
                }
                
            });
            $years = array();

            foreach ($courses as $c) {
                @$date = date("Y",$c->date);
                $years[$date][] = $c;
            }
            ksort($years);

            while (count($years) > 0) {

                $year = reset($years); // also returns element
                $date_year = key($years);
                unset($years[key($years)]);
                if ($date_year == 1970) {
                    $date_year = "Date not specified";
                }
                $front_end .= "<div class='meta_year'><h3>" . $date_year . "</h3>";
                foreach ($year as $course) {
                    $coursecontext = context_course::instance($course->id);
                    $linkcss = $course->visible ? "" : " class=\"dimmed\" ";
                    $course_enrol_id = $DB->get_records("enrol", array("enrol"=>"manual", "courseid"=>$course->id));
                    $course_enrol_id = reset($course_enrol_id);

                    if (isset($course->date)) {
                         $front_end .="
                         <div class='meta_course_block'><a $linkcss title=\""
                           . format_string($course->shortname, true, array('context' => $coursecontext))."\" ".
                           "href=\"$CFG->wwwroot/course/view.php?id=$course->id\">"
                           .$icon. format_string($course->fullname, true, array('context' => context_course::instance($course->id))) . "</a><div class='meta_info'><span class='meta_course_date'> " . get_string("date"). ":  ".date("Y-m-d", $course->date)."</span><span class='meta_course_unenrol'><a href='". $CFG->wwwroot . "/enrol/manual/unenrolself.php?enrolid=".$course_enrol_id->id."'>Unenrol me</a></span></div></div>";
                    } else {
                         $front_end .="<div class='meta_course_block'><a $linkcss title=\""
                           . format_string($course->shortname, true, array('context' => $coursecontext))."\" ".
                           "href=\"$CFG->wwwroot/course/view.php?id=$course->id\">"
                           .$icon. format_string($course->fullname , true, array('context' => context_course::instance($course->id))) . "</a></div>";
                    }
                }
                $front_end .= "</div>";

            }
        
        } else {
            if (!$hasGrow) {
                $this->content->icons[] = '';
                $front_end = "No courses yet";   
            }
            
            
        }
        $this->content->items[] = $front_end;
        $this->title = get_string("mycourses");
        return $this->content;
    }

    /**
     * Returns the role that best describes the course list block.
     *
     * @return string
     */
    public function get_aria_role() {
        return 'navigation';
    }
}


