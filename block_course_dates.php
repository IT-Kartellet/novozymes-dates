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

        // Get users courses
        if ($courses = enrol_get_my_courses(NULL, 'visible DESC, fullname ASC')) {
            $years = array();

            foreach ($courses as $course) {
                $datecourse = $DB->get_record('meta_datecourse', array("courseid"=>$course->id));

                if ($datecourse) {
                    $course->date = $datecourse->startdate;
                    $course->metaid = $datecourse->metaid;
                    $course->elearning = $datecourse->elearning;
                } else {
                    $course->date = $course->startdate;
                    $course->elearning = false;
                }

                if ($course->elearning) {
                    $key = 'Elearning courses';
                } else {
                    $key = date("Y",$course->date);
                }

                $years[$key][] = $course;
            }
            ksort($years);

            foreach ($years as $date_year => $courses) {
                if ($date_year == 1970) {
                    $date_year = "Date not specified";
                }
                $front_end .= html_writer::start_div('meta_year') . html_writer::tag('h3', $date_year);
                foreach ($courses as $course) {
                    $coursecontext = context_course::instance($course->id);

                    $front_end .= html_writer::start_div('meta_course_block') . html_writer::link(
                        new moodle_url('course/view.php', array('id' => $course->id)),
                        $icon . format_string($course->fullname, true, array('context' => $coursecontext)),
                        array(
                            'class' => $course->visible ? "" : "dimmed",
                            'title' => format_string($course->shortname, true, array('context' => $coursecontext))
                        )
                    );

                    if (isset($course->date)) {
                        $metadetails = '';
                        if (isset($course->metaid)) {
                            $metadetails = html_writer::span(html_writer::link(new moodle_url('blocks/metacourse/view_metacourse.php', array('id' => $course->metaid)), 'Details'), 'meta_course_unenrol');
                        }

                        $date_part = '';
                        if (!$course->elearning) {
                            $date_part = html_writer::span(get_string("date") . ": " . date("Y-m-d", $course->date), 'meta_course_date');
                        }

                        $front_end .= html_writer::div($date_part . ' ' . $metadetails, 'meta_info');
                    }

                    $front_end .= html_writer::end_div();
                }
                $front_end .= html_writer::end_div();

            }
        
        } else {
            if (!$hasGrow) {
                $this->content->icons[] = '';
                $front_end = "No courses yet";   
            }    
        }
		
		// Add old GROW courses
        if (@$growCourses = $DB->get_records("grow_legacy", array("username" => strtoupper($USER->username)))) {

            $front_end .= "<div class='meta_year'><h3>Courses before 2014</h3>";

            foreach ($growCourses as $grow) {
                $front_end .="<div class='meta_course_block'><span>" . $icon . $grow->coursename . "</span><span class='meta_course_date'> Date:  ".date("Y-m-d", $grow->timestart)."</span></div>";
            }

            $front_end .= "</div>";
            $hasGrow = true;
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


