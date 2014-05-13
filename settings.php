<?php

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    $options = array('all'=>get_string('allcourses', 'block_course_dates'), 'own'=>get_string('owncourses', 'block_course_dates'));

    $settings->add(new admin_setting_configselect('block_course_dates_adminview', get_string('adminview', 'block_course_dates'),
                       get_string('configadminview', 'block_course_dates'), 'all', $options));

    $settings->add(new admin_setting_configcheckbox('block_course_dates_hideallcourseslink', get_string('hideallcourseslink', 'block_course_dates'),
                       get_string('confighideallcourseslink', 'block_course_dates'), 0));
}


