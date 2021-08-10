<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/course/renderer.php');

/**
 * Class qmultopics_course_renderer
 */
class qmultopics_course_renderer extends \core_course_renderer{

    /**
     * Override the constructor so that we can initialise the label caches
     *
     * @param moodle_page $page
     * @param string $target
     */
    public function __construct(moodle_page $page, $target) {
        global $COURSE, $USER;
        $context = context_course::instance($COURSE->id);
        $roles = get_user_roles($context, $USER->id, true);
        $role = key($roles);
        $rolename = $roles[$role]->shortname;

        if ($rolename == 'student') {
            $this->user_data = $this->get_user_data();
        } else {
            $this->enrolled_students = $this->get_enrolled_students();
            // Assignment.
            if (isset($this->enrolled_students['assign'])){
                $studentids = implode("','",array_keys($this->enrolled_students['assign']));

                $this->assignment_data = $this->get_assignment_data();
                $this->group_assignment_data = $this->get_group_assignment_data();
                $this->assignments_submitted = $this->get_assignments_submitted($studentids);
                $this->assignments_graded = $this->get_assignments_graded($studentids);
            }
            // Choice.
            if (isset($this->enrolled_students['choice'])){
                $studentids = implode("','",array_keys($this->enrolled_students['choice']));

                $this->choice_data = $this->get_choice_data();
                $this->choice_answers = $this->get_choice_answers($studentids);
            }
            // Feedback.
            if (isset($this->enrolled_students['feedback'])){
                $studentids = implode("','",array_keys($this->enrolled_students['feedback']));

                $this->feedback_data = $this->get_feedback_data();
                $this->feedback_completions = $this->get_feedback_completions($studentids);
            }
            // Lesson.
            if (isset($this->enrolled_students['lesson'])){
                $studentids = implode("','",array_keys($this->enrolled_students['lesson']));

                $this->lesson_data = $this->get_lesson_data();
                $this->lesson_submissions = $this->get_lesson_submissions($studentids);
                $this->lesson_completions = $this->get_lesson_completions($studentids);
            }
            // Quiz.
            if (isset($this->enrolled_students['quiz'])){
                $studentids = implode("','",array_keys($this->enrolled_students['quiz']));

                $this->quiz_data = $this->get_quiz_data();
                $this->quiz_submitted = $this->get_quiz_submitted($studentids);
//                $this->quiz_graded = $this->get_quiz_graded($studentids);
            }
        }

        parent::__construct($page, $target);
    }

    /**
     * Get submission and grading data for modules in this course
     *
     * @return array
     * @throws dml_exception
     */
    protected function get_user_data() {
        global $COURSE, $DB, $USER;
        $sql = "
            select
            concat_ws('_', cm.id,a.id, asu.id, ag.id, c.id, ca.id, f.id, fc.id,
                l.id,la.id,lg.id,q.id,qa.id,qg.id,gi.id,gg.id) as row_id
            ,m.name as module_name
            ,gi.hidden as gi_hidden
            ,gi.locked as gi_locked
            ,gg.hidden as gg_hidden
            ,gg.locked as gg_locked
            #,'assign >'
            ,a.id as assign_id
            ,a.name as assign
            ,a.duedate as assign_duedate
            ,a.cutoffdate as assign_cutoffdate
            ,a.teamsubmission
            ,a.requireallteammemberssubmit
            ,asu.userid as assign_userid
            ,asu.status as assign_submission_status
            ,asu.timemodified as assign_submit_time
            ,ag.grade as assign_grade
            ,ag.timemodified as assign_grade_time
            #,'choice >'
            ,c.id as choice_id
            ,c.name as choice
            ,c.timeopen as choice_timeopen
            ,c.timeclose as choice_duedate
            ,ca.userid as choice_userid
            ,ca.timemodified as choice_submit_time
            #,'feedback >'
            ,f.id as feedback_id
            ,f.name as feedback
            ,f.timeopen as feedback_timeopen
            ,f.timeclose as feedback_duedate
            ,fc.userid as feedback_userid
            ,fc.timemodified as feedback_submit_time
            #,'lesson >'
            ,l.id as lesson_id
            ,l.name as lesson
            ,l.deadline as lesson_duedate
            ,la.userid as lesson_userid
            ,la.correct
            ,la.timeseen as lesson_submit_time
            ,lg.grade as lesson_grade
            ,lg.completed as lesson_completed
            #,'quiz >'
            ,q.id as quiz_id
            ,q.name as quiz_name
            ,q.timeopen as quiz_timeopen
            ,q.timeclose as quiz_duedate
            ,qa.userid as quiz_userid
            ,qa.state as quiz_state
            ,qa.timestart as quiz_timestart
            ,qa.timefinish as quiz_submit_time
            ,qg.grade as quiz_grade
            from {course_modules} cm
            join {modules} m on m.id = cm.module
            # assign
            left join {assign} a on a.id = cm.instance and a.course = cm.course and m.name = 'assign'
            left join {assign_submission} asu on asu.assignment = a.id
            left join {assign_grades} ag on ag.assignment = asu.assignment and ag.userid = asu.userid
            # choice
            left join {choice} c on c.id = cm.instance and c.course = cm.course and m.name = 'choice'
            left join {choice_answers} ca on ca.choiceid = c.id
            # feedback
            left join {feedback} f on f.id = cm.instance and f.course = cm.course and m.name = 'feedback'
            left join {feedback_completed} fc on fc.feedback = f.id
            # lesson
            left join {lesson} l on l.id = cm.instance and l.course = cm.course and m.name = 'lesson'
            left join {lesson_attempts} la on la.lessonid = l.id
            left join {lesson_grades} lg on lg.lessonid = la.lessonid and lg.userid = la.userid
            # quiz
            left join {quiz} q on q.id = cm.instance and q.course = cm.course and m.name = 'quiz'
            left join {quiz_attempts} qa on qa.quiz = q.id
            left join {quiz_grades} qg on qg.quiz = qa.quiz and qg.userid = qa.userid
            # grading
            left join {grade_items} gi on (gi.courseid = cm.course and gi.itemmodule = m.name and gi.iteminstance = cm.instance)
            left join {grade_grades} gg on (gg.itemid = gi.id and gg.userid = asu.userid)
            where 1
            and cm.course = $COURSE->id
            and (asu.userid = $USER->id or ca.userid = $USER->id or fc.userid = $USER->id or lg.userid = $USER->id or qg.userid = $USER->id)
            #limit 5000
        ";
        return $DB->get_records_sql($sql);
    }


    protected function get_enrolled_students() {
        $result = [];
        $mtypes = ['assign', 'choice', 'feedback', 'lesson', 'quiz'];
        foreach ($mtypes as $mtype) {
            $result[$mtype] = $this->enrolled_users($mtype);
        }
        return $result;
    }

    /**
     * Renders HTML to display one course module in a course section
     *
     * This includes link, content, availability, completion info and additional information
     * that module type wants to display (i.e. number of unread forum posts)
     *
     * This function calls:
     * {@link core_course_renderer::course_section_cm_name()}
     * {@link core_course_renderer::course_section_cm_text()}
     * {@link core_course_renderer::course_section_cm_availability()}
     * {@link core_course_renderer::course_section_cm_completion()}
     * {@link course_get_cm_edit_actions()}
     * {@link core_course_renderer::course_section_cm_edit_actions()}
     *
     * @param stdClass $course
     * @param completion_info $completioninfo
     * @param cm_info $mod
     * @param int|null $sectionreturn
     * @param array $displayoptions
     * @return string
     * @throws coding_exception
     * @throws dml_exception
     */
    public function course_section_cm0($course, &$completioninfo, cm_info $mod, $sectionreturn, $displayoptions = array()) {
        $output = '';
        /*
        We return empty string (because course module will not be displayed at all)
        if:
        1) The activity is not visible to users
        and
        2) The 'availableinfo' is empty, i.e. the activity was
           hidden in a way that leaves no info, such as using the
           eye icon.
        */
        if (!$mod->is_visible_on_course_page()) {
            return $output;
        }

        $indentclasses = 'mod-indent';
        if (!empty($mod->indent)) {
            $indentclasses .= ' mod-indent-'.$mod->indent;
            if ($mod->indent > 15) {
                $indentclasses .= ' mod-indent-huge';
            }
        }

        $output .= html_writer::start_tag('div');

        if ($this->page->user_is_editing()) {
            $output .= course_get_cm_move($mod, $sectionreturn);
        }

        $output .= html_writer::start_tag('div', array('class' => 'mod-indent-outer'));

        // This div is used to indent the content.
        $output .= html_writer::div('', $indentclasses);

        // Start a wrapper for the actual content to keep the indentation consistent.
        $output .= html_writer::start_tag('div');

        // Display the link to the module (or do nothing if module has no url).
        $cmname = $this->course_section_cm_name($mod, $displayoptions);

        if (!empty($cmname)) {
            // Start the div for the activity title, excluding the edit icons.
            $output .= html_writer::start_tag('div', array('class' => 'activityinstance'));
            $output .= $cmname;

            // Module can put text after the link (e.g. forum unread).
            $output .= $mod->afterlink;

            // Closing the tag which contains everything but edit icons.
            // Content part of the module should not be part of this.
            $output .= html_writer::end_tag('div'); // Activityinstance.
        }

        /*
        If there is content but NO link (eg label), then display the
        content here (BEFORE any icons). In this case cons must be
        displayed after the content so that it makes more sense visually
        and for accessibility reasons, e.g. if you have a one-line label
        it should work similarly (at least in terms of ordering) to an
        activity.
        */
        $contentpart = $this->course_section_cm_text($mod, $displayoptions);
        $url = $mod->url;
        if (empty($url)) {
            $output .= $contentpart;
        }

        $modicons = '';
        if ($this->page->user_is_editing()) {
            $editactions = course_get_cm_edit_actions($mod, $mod->indent, $sectionreturn);
            $modicons .= ' '. $this->course_section_cm_edit_actions($editactions, $mod, $displayoptions);
            $modicons .= $mod->afterediticons;
        }

        $modicons .= $this->course_section_cm_completion($course, $completioninfo, $mod, $displayoptions);

        if (!empty($modicons)) {
            $output .= html_writer::span($modicons, 'actions');
        }

        // Show availability info (if module is not available).
        $output .= $this->course_section_cm_availability($mod, $displayoptions);

        // If there is content AND a link, then display the content here.
        // (AFTER any icons). Otherwise it was displayed before.
        if (!empty($url)) {
            $output .= $contentpart;
        }

        // Amending badges
        $output .= html_writer::start_div();
        $output .= $this->show_badges($mod);
        $output .= html_writer::end_div();

        $output .= html_writer::end_tag('div');

        // End of indentation div.
        $output .= html_writer::end_tag('div');

        $output .= html_writer::end_tag('div');
        return $output;
    }
    public function course_section_cm($course, &$completioninfo, cm_info $mod, $sectionreturn, $displayoptions = array()) {
        $output = '';
        // We return empty string (because course module will not be displayed at all)
        // if:
        // 1) The activity is not visible to users
        // and
        // 2) The 'availableinfo' is empty, i.e. the activity was
        //     hidden in a way that leaves no info, such as using the
        //     eye icon.
        if (!$mod->is_visible_on_course_page()) {
            return $output;
        }

        $indentclasses = 'mod-indent';
        if (!empty($mod->indent)) {
            $indentclasses .= ' mod-indent-'.$mod->indent;
            if ($mod->indent > 15) {
                $indentclasses .= ' mod-indent-huge';
            }
        }

        $output .= html_writer::start_tag('div');

        if ($this->page->user_is_editing()) {
            $output .= course_get_cm_move($mod, $sectionreturn);
        }

//        $output .= html_writer::start_tag('div', array('class' => 'mod-indent-outer w-100'));
        $output .= html_writer::start_tag('div', array('class' => 'mod-indent-outer'));

        // This div is used to indent the content.
        $output .= html_writer::div('', $indentclasses);

        // Start a wrapper for the actual content to keep the indentation consistent
        $output .= html_writer::start_tag('div');

        // Display the link to the module (or do nothing if module has no url)
        $cmname = $this->course_section_cm_name($mod, $displayoptions);

        if (!empty($cmname)) {
            // Start the div for the activity title, excluding the edit icons.
            $output .= html_writer::start_tag('div', array('class' => 'activityinstance'));
            $output .= $cmname;


            // Module can put text after the link (e.g. forum unread)
            $output .= $mod->afterlink;

            // Closing the tag which contains everything but edit icons. Content part of the module should not be part of this.
            $output .= html_writer::end_tag('div'); // .activityinstance
        }

        // If there is content but NO link (eg label), then display the
        // content here (BEFORE any icons). In this case cons must be
        // displayed after the content so that it makes more sense visually
        // and for accessibility reasons, e.g. if you have a one-line label
        // it should work similarly (at least in terms of ordering) to an
        // activity.
        $contentpart = $this->course_section_cm_text($mod, $displayoptions);
        $url = $mod->url;
        if (empty($url)) {
            $output .= $contentpart;
        }

        $modicons = '';
        if ($this->page->user_is_editing()) {
            $editactions = course_get_cm_edit_actions($mod, $mod->indent, $sectionreturn);
            $modicons .= ' '. $this->course_section_cm_edit_actions($editactions, $mod, $displayoptions);
            $modicons .= $mod->afterediticons;
        }

        $modicons .= $this->course_section_cm_completion($course, $completioninfo, $mod, $displayoptions);

        if (!empty($modicons)) {
            $output .= html_writer::div($modicons, 'actions');
        }

        // Show availability info (if module is not available).
        $output .= $this->course_section_cm_availability($mod, $displayoptions);

        // If there is content AND a link, then display the content here
        // (AFTER any icons). Otherwise it was displayed before
        if (!empty($url)) {
            $output .= $contentpart;
        }

        // Amending badges - but for courses with < 1000 students only
//        if (count($this->enrolled_users('')) < 1000) {
            $output .= html_writer::start_div();
            $output .= $this->show_badges($mod);
            $output .= html_writer::end_div();
//        }

        $output .= html_writer::end_tag('div'); // $indentclasses

        // End of indentation div.
        $output .= html_writer::end_tag('div');

        $output .= html_writer::end_tag('div');
        return $output;
    }

    /**
     * Show a badge for the given module
     *
     * @param $mod
     * @return string
     * @throws coding_exception
     * @throws dml_exception
     */
    public function show_badges($mod) {
        switch($mod->modname) {
            case 'assign':
                return $this->show_assignment_badges($mod);
                break;
            case 'choice':
                return $this->show_choice_badge($mod);
                break;
            case 'feedback':
                return $this->show_feedback_badge($mod);
                break;
            case 'lesson':
                return $this->show_lesson_badge($mod);
                break;
            case 'quiz':
                return $this->show_quiz_badge($mod);
                break;
            default:
                return '';
        }
    }

    /**
     * Show a due date badge
     *
     * @param $duedate
     * @return string
     * @throws coding_exception
     */
    public function show_due_date_badge($duedate, $cutoffdate = 0) {
        // If duedate is 0 don't show a badge.
        if ($duedate == 0) {
            return '';
        }
        $dateformat = "%d %B %Y";
        $badgedate = $duedate;
        $badgeclass = '';

        $today = new DateTime(); // This object represents current date/time
        $today->setTime( 0, 0, 0 ); // reset time part, to prevent partial comparison

        $match_date = DateTime::createFromFormat( "Y.m.d\\TH:i", date("Y.m.d\\TH:i",$duedate ));
        $match_date->setTime( 0, 0, 0 ); // reset time part, to prevent partial comparison

        $diff = $today->diff( $match_date );
        $diffdays = (integer)$diff->format( "%R%a" ); // Extract days count in interval

        switch( true ) {
            case $diffdays == 0:
                $badgeclass = ' badge-danger';
                if ($cutoffdate > 0 && $duedate < time()) {
                    $match_date = DateTime::createFromFormat( "Y.m.d\\TH:i", date("Y.m.d\\TH:i",$cutoffdate ));
                    $match_date->setTime( 0, 0, 0 ); // reset time part, to prevent partial comparison

                    $diff = $today->diff( $match_date );
                    $diffdays = (integer)$diff->format( "%R%a" ); // Extract days count in interval

                    switch( true ) {
                        case $diffdays == 0:
                            $duetext = get_string('badge_duetoday', 'format_qmultopics');
                            break;
                        case $diffdays < 0:
                            $duetext = get_string('badge_wasdue', 'format_qmultopics');
                            break;
                        default:
                            $duetext = get_string('badge_cutoffdate', 'format_qmultopics');
                            $badgedate = $cutoffdate;
                            break;
                    }
                } elseif ($duedate < time()) {
                    $dateformat = "%d %B %Y %H:%M:%S";
                    $duetext = get_string('badge_wasdue', 'format_qmultopics');
                } else {
                    $duetext = get_string('badge_duetoday', 'format_qmultopics');
                }
                break;
            case $diffdays < 0:
                if ($cutoffdate > 0) {
                    $match_date = DateTime::createFromFormat( "Y.m.d\\TH:i", date("Y.m.d\\TH:i",$cutoffdate ));
                    $match_date->setTime( 0, 0, 0 ); // reset time part, to prevent partial comparison

                    $diff = $today->diff( $match_date );
                    $diffdays = (integer)$diff->format( "%R%a" ); // Extract days count in interval

                    switch( true ) {
                        case $diffdays == 0:
                            $badgeclass = ' badge-danger';
                            $duetext = get_string('badge_duetoday', 'format_qmultopics');
                            break;
                        case $diffdays < 0:
                            $badgeclass = ' badge-danger';
                            $duetext = get_string('badge_wasdue', 'format_qmultopics');
                            break;
                        default:
                            $duetext = get_string('badge_cutoffdate', 'format_qmultopics');
                            $badgedate = $cutoffdate;
                            break;
                    }
                } else {
                    $badgeclass = ' badge-danger';
                    $duetext = get_string('badge_wasdue', 'format_qmultopics');
                }
                break;
            case $diffdays < 14:
                $badgeclass = ' badge-warning';
                $duetext = get_string('badge_due', 'format_qmultopics');
                break;
            default:
                $duetext = get_string('badge_due', 'format_qmultopics');
        }


        $badgecontent = $duetext . userdate($badgedate, $dateformat);
        return $this->html_badge($badgecontent, $badgeclass);
    }

    /**
     * Return the html for a badge
     *
     * @param $badgetext
     * @param string $badgeclass
     * @param string $title
     * @return string
     * @throws coding_exception
     */
    public function html_badge($badgetext, $badgeclass = "", $title = "") {
        $o = '';
        $o .= html_writer::div($badgetext, 'badge '.$badgeclass, array('title' => $title));
        $o .= get_string('badge_spacer', 'format_qmultopics');
        return $o;
    }

    /**
     * Get the enrolled users with the given capability
     *
     * @param $capability
     * @return array
     * @throws dml_exception
     */
    public function enrolled_users($capability) {
        global $COURSE, $DB;

        switch($capability) {
            case 'assign':
                $capability = 'mod/assign:submit';
                break;
            case 'quiz':
                $capability = 'mod/quiz:attempt';
                break;
            case 'choice':
                $capability = 'mod/choice:choose';
                break;
            case 'feedback':
                $capability = 'mod/feedback:complete';
                break;
            default:
                // If no modname is specified, assume a count of all users is required.
                $capability = '';
        }

        $context = \context_course::instance($COURSE->id);
        $groupid = '';

        $onlyactive = true;
        $capjoin = get_enrolled_with_capabilities_join(
            $context, '', $capability, $groupid, $onlyactive);
        $sql = "SELECT DISTINCT u.id
                FROM {user} u
                $capjoin->joins
                WHERE $capjoin->wheres
                AND u.deleted = 0
                ";
        return $DB->get_records_sql($sql, $capjoin->params);
    }

    // Assignments.
    /**
     * Show badge for assign plus additional due date badge
     *
     * @param $mod
     * @return string
     * @throws coding_exception
     * @throws dml_exception
     */
    public function show_assignment_badges0($mod) {
        global $COURSE;
        $o = '';

        $assignment = false;
        foreach ($COURSE->module_data as $module) {
            if ($module->assign_id == $mod->instance) {
                $assignment = $module;
                break;
            }
        }

        if ($assignment) {

            // Show assignment due date.
            $o .= $this->show_due_date_badge($assignment->assign_duedate, $assignment->assign_cutoffdate);

            // Check if the user is able to grade (e.g. is a teacher).
            if (has_capability('mod/assign:grade', $mod->context)) {
                // Show submission numbers and ungraded submissions if any.
                // Check if the assignment allows group submissions.
                if ($assignment->teamsubmission && ! $assignment->requireallteammemberssubmit) {
                    $o .= $this->show_assign_group_submissions($mod);
                } else {
                    $o .= $this->show_assign_submissions($mod);
                }
            } else {
                // Show date of submission.
                $o .= $this->show_assign_submission($mod);
            }
        }
        return $o;
    }
    public function show_assignment_badges($mod) {
        $o = '';
        if (isset($this->assignment_data[$mod->instance])) {

            // Show assignment due date if it has one.
            $o .= $this->show_due_date_badge($this->assignment_data[$mod->instance]->duedate, $this->assignment_data[$mod->instance]->cutoffdate);

            // Check if the user is able to grade (e.g. is a teacher).
            if (has_capability('mod/assign:grade', $mod->context)) {
                // Show submission numbers and ungraded submissions if any.
                // Check if the assignment allows group submissions.
                if ($this->assignment_data[$mod->instance]->teamsubmission && ! $this->assignment_data[$mod->instance]->requireallteammemberssubmit) {
                    $o .= $this->show_assign_group_submissions($mod);
                } else {
                    $o .= $this->show_assign_submissions($mod);
                }
            } else {
                // Show date of submission.
                $o .= $this->show_assign_submission($mod);
            }
        }
        return $o;
    }

    /**
     * Show badge with submissions and gradings for all students
     *
     * @param $mod
     * @return string
     * @throws coding_exception
     * @throws dml_exception
     */
    public function show_assign_submissions0($mod) {
        global $COURSE;
        // Show submissions by enrolled students.
        $spacer = get_string('badge_commaspacer', 'format_qmultopics');
        $badgetext = false;
        $badgeclass = '';
        $capability = 'assign';
        $pretext = '';
        $xofy = get_string('badge_xofy', 'format_qmultopics');
        $posttext = get_string('badge_submitted', 'format_qmultopics');
        $groupstext = get_string('badge_groups', 'format_qmultopics');
        $ungradedtext = get_string('badge_ungraded', 'format_qmultopics');
        $enrolledstudents = $this->enrolled_users($capability);
        if (!empty($mod->availability)) {

            // Get availability information.
            $info = new \core_availability\info_module($mod);
            $restrictedstudents = $info->filter_user_list($enrolledstudents);
        } else {
            $restrictedstudents = $enrolledstudents;
        }

        if ($enrolledstudents) {
            $submissions = 0;
            $gradings = 0;
            if ($COURSE->module_data) {
                foreach ($COURSE->module_data as $module) {
                    if ($module->module_name == 'assign' &&
                        $module->assign_id == $mod->instance &&
                        $module->assign_submission_status == 'submitted') {
                        $submissions++;
                        if ($module->assign_grade > 0) {
                            $gradings++;
                        }
                    }
                }
            }
            $ungraded = $submissions - $gradings;
            $badgetext = $pretext
                .$submissions
                .$xofy
                .count($restrictedstudents)
                .$posttext;

            if ($ungraded) {
                $badgetext =
                    $badgetext
                    .$spacer
                    .$ungraded
                    .$ungradedtext;
            }

            if ($badgetext) {
                return $this->html_badge($badgetext, $badgeclass);
            } else {
                return '';
            }
        }
    }
    public function show_assign_submissions($mod) {
        global $COURSE;
        // Show submissions by enrolled students.
        $spacer = get_string('badge_commaspacer', 'format_qmultopics');
        $pretext = '';
        $xofy = get_string('badge_xofy', 'format_qmultopics');
        $posttext = get_string('badge_submitted', 'format_qmultopics');
        $ungradedtext = get_string('badge_ungraded', 'format_qmultopics');
        $enrolledstudents = $this->enrolled_users('assign');

        if (!empty($mod->availability)) {
            // Get availability information.
            $info = new \core_availability\info_module($mod);
            $restrictedstudents = $info->filter_user_list($enrolledstudents);
        } else {
            $restrictedstudents = $enrolledstudents;
        }

        if ($restrictedstudents) {
            if (!$submissions = $this->assignments_submitted[$mod->instance]->submitted) {
                $submissions = 0;
            }
            if (!$gradings = $this->assignments_graded[$mod->instance]->graded) {
                $gradings = 0;
            }

            $ungraded = $submissions - $gradings;
            $badgetext = $pretext
                .$submissions
                .$xofy
                .count($restrictedstudents)
                .$posttext;

            if ($ungraded) {
                $badgetext =
                    $badgetext
                    .$spacer
                    .$ungraded
                    .$ungradedtext;
            }

            if ($badgetext) {
                return $this->html_badge($badgetext);
            } else {
                return '';
            }
        }
    }

    /**
     * Show badge with submissions and gradings for all groups
     *
     * @param $mod
     * @return string
     * @throws coding_exception
     * @throws dml_exception
     */
    public function show_assign_group_submissions0($mod) {
        global $COURSE;
        // Show group submissions by enrolled students.
        $spacer = get_string('badge_commaspacer', 'format_qmultopics');
        $badgeclass = '';
        $capability = 'assign';
        $pretext = '';
        $xofy = get_string('badge_xofy', 'format_qmultopics');
        $posttext = get_string('badge_submitted', 'format_qmultopics');
        $groupstext = get_string('badge_groups', 'format_qmultopics');
        $ungradedtext = get_string('badge_ungraded', 'format_qmultopics');
        $enrolledstudents = $this->enrolled_users($capability);
        if ($enrolledstudents) {
            // Go through the group_data to get numbers for groups, submissions and gradings.
            $coursegroupsarray = [];
            $groupsubmissionsarray = [];
            $groupgradingsarray = [];
            if (isset($COURSE->group_assign_data)) {
                foreach ($COURSE->group_assign_data as $record) {
                    $coursegroupsarray[$record->groupid] = $record->groupid;
                    if ($record->assignment == $mod->instance && $record->status == 'submitted') {
                        $groupsubmissionsarray[$record->groupid] = true;
                        if ($record->grade > 0) {
                            $groupgradingsarray[$record->groupid] = $record->grade;
                        }
                    }
                }
            }
            $coursegroups = count($coursegroupsarray);
            $groupsubmissions = count($groupsubmissionsarray);
            $groupgradings = count($groupgradingsarray);
            $ungraded = $groupsubmissions - $groupgradings;
            $badgetext = $pretext
                .$groupsubmissions
                .$xofy
                .$coursegroups
                .$groupstext
                .$posttext;
            // If there are ungraded submissions show that in the badge as well.
            if ($ungraded) {
                $badgetext =
                    $badgetext
                    .$spacer
                    .$ungraded
                    .$ungradedtext;
            }

            if ($badgetext) {
                return $this->html_badge($badgetext, $badgeclass);
            } else {
                return '';
            }
        }
    }
    public function show_assign_group_submissions($mod) {
        global $COURSE;
        // Show group submissions by enrolled students.
        $spacer = get_string('badge_commaspacer', 'format_qmultopics');
        $badgeclass = '';
        $pretext = '';
        $xofy = get_string('badge_xofy', 'format_qmultopics');
        $posttext = get_string('badge_submitted', 'format_qmultopics');
        $groupstext = get_string('badge_groups', 'format_qmultopics');
        $ungradedtext = get_string('badge_ungraded', 'format_qmultopics');
        $enrolledstudents = $this->enrolled_users('assign');
        if ($enrolledstudents) {
            // Go through the group_data to get numbers for groups, submissions and gradings.
            $coursegroupsarray = [];
            $groupsubmissionsarray = [];
            $groupgradingsarray = [];
            if (isset($this->group_assignment_data)) {
                foreach ($this->group_assignment_data as $record) {
                    $coursegroupsarray[$record->groupid] = $record->groupid;
                    if ($record->assignment == $mod->instance && $record->status == 'submitted') {
                        $groupsubmissionsarray[$record->groupid] = true;
                        if ($record->grade > 0) {
                            $groupgradingsarray[$record->groupid] = $record->grade;
                        }
                    }
                }
            }
            $coursegroups = count($coursegroupsarray);
            $groupsubmissions = count($groupsubmissionsarray);
            $groupgradings = count($groupgradingsarray);
            $ungraded = $groupsubmissions - $groupgradings;
            $badgetext = $pretext
                .$groupsubmissions
                .$xofy
                .$coursegroups
                .$groupstext
                .$posttext;
            // If there are ungraded submissions show that in the badge as well.
            if ($ungraded) {
                $badgetext =
                    $badgetext
                    .$spacer
                    .$ungraded
                    .$ungradedtext;
            }

            if ($badgetext) {
                return $this->html_badge($badgetext, $badgeclass);
            } else {
                return '';
            }
        }
    }

    /**
     * A badge to show the student as $USER his/her submission status
     * It will display the date of a submission, a mouseover will show the time for the submission
     *
     * @param $mod
     * @return string
     * @throws coding_exception
     */
    public function show_assign_submission($mod) {
        global $USER;
        $badgeclass = '';
        $badgetitle = '';
        $dateformat = "%d %B %Y";
        $timeformat = "%d %B %Y %H:%M:%S";

        $submission = false;
        foreach ($this->user_data as $module) {
            if ($module->module_name == 'assign' &&
                $module->assign_userid == $USER->id &&
                $module->assign_id == $mod->instance &&
                $module->assign_submission_status == 'submitted') {
                $submission = $module;
                break;
            }
        }

        if ($submission) {
            $badgetext = get_string('badge_submitted',
                    'format_qmultopics').userdate($submission->assign_submit_time, $dateformat);
            if ($this->get_grading($mod) || $this->get_group_grading($mod)) {
                $badgetext .= get_string('badge_feedback', 'format_qmultopics');
            }
            $badgetitle = get_string('badge_submission_time_title',
                    'format_qmultopics') . userdate($submission->assign_submit_time, $timeformat);
        } else {
            $badgetext = get_string('badge_notsubmitted', 'format_qmultopics');
        }
        if ($badgetext) {
            return $this->html_badge($badgetext, $badgeclass, $badgetitle);
        } else {
            return '';
        }
    }

    /**
     * Return grading if the given student as $USER has been graded yet
     *
     * @param $mod
     * @return array
     */
    protected function get_grading($mod) {
        global $USER;

        if (isset($this->user_data)) {
            foreach ($this->user_data as $module) {
                if ($module->module_name == 'assign'
                    && $module->assign_id == $mod->instance
                    && $module->assign_userid == $USER->id
                    && $module->assign_grade > 0
                    && ($module->gi_hidden == 0 || ($module->gi_hidden > 1 && $module->gi_hidden < time()))
                    && ($module->gg_hidden == 0 || ($module->gg_hidden > 1 && $module->gg_hidden < time()))
                    && $module->gi_locked == 0
                    && $module->gg_locked == 0
                ) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Return true if the submission of the group of which the given student is a member has already been graded
     *
     * @param $mod
     * @return bool
     */
    protected function get_group_grading($mod) {
        global $USER;

        if (!isset($this->group_assign_data)) {
            return false;
        }
        foreach ($this->group_assign_data as $record) {
            if ($record->assignment == $mod->instance
                && $record->userid == $USER->id
                && $record->grade > 0
                && ($record->gi_hidden == 0 || ($record->gi_hidden > 1 && $record->gi_hidden < time()))
                && ($record->gg_hidden == 0 || ($record->gg_hidden > 1 && $record->gg_hidden < time()))
                && $record->gi_locked == 0
                && $record->gg_locked == 0
            ) {
                return true;
            }
        }
        return false;
    }

    // Choices.
    /**
     * Show badge for choice plus a due date badge if there is a due date
     *
     * @param $mod
     * @return string
     * @throws coding_exception
     * @throws dml_exception
     */
    public function show_choice_badge($mod) {
        $o = '';

        if (isset($this->choice_data[$mod->instance])) {
            // Show a due date badge if there is a due date.
            $o .= $this->show_due_date_badge($this->choice_data[$mod->instance]->duedate);

            // Check if the user is able to grade (e.g. is a teacher).
            if (has_capability('mod/assign:grade', $mod->context)) {
                // Show submission numbers and ungraded submissions if any.
                $o .= $this->show_choice_answers($mod);
            } else {
                // Show date of submission.
                $o .= $this->show_choice_answer($mod);
            }
        }

        return $o;
    }

    /**
     * Show badge with choice answers of all students
     *
     * @param $mod
     * @return string
     * @throws coding_exception
     * @throws dml_exception
     */
    public function show_choice_answers0($mod) {
        global $COURSE;

        // Show answers by enrolled students.
        $badgetext = '';
        $badgeclass = '';
        $capability = 'choice';
        $pretext = '';
        $xofy = get_string('badge_xofy', 'format_qmultopics');
        $posttext = get_string('badge_answered', 'format_qmultopics');
        $enrolledstudents = $this->enrolled_users($capability);
        if ($enrolledstudents) {
            $submissions = 0;
            if (isset($COURSE->module_data)) {
                foreach ($COURSE->module_data as $module) {
                    if ($module->module_name == 'choice' && $module->choice_userid != null &&
                        $module->choice_id == $mod->instance) {
                        $submissions++;
                    }
                }
            }
            $badgetext = $pretext
                .$submissions
                .$xofy
                .count($enrolledstudents)
                .$posttext;
        }
        if ($badgetext != '') {
            return $this->html_badge($badgetext, $badgeclass);
        } else {
            return '';
        }
    }
    public function show_choice_answers($mod) {
        if (!$enrolledstudents = $this->enrolled_users('choice')) {
            return '';
        }
        $pretext = '';
        $xofy = get_string('badge_xofy', 'format_qmultopics');
        $posttext = get_string('badge_answered', 'format_qmultopics');
        // Get the number of submissions for this module.
        if (!$submissions = $this->choice_answers[$mod->instance]->submitted) {
            $submissions = 0;
        }
        $badgetext = $pretext
            .$submissions
            .$xofy
            .count($enrolledstudents)
            .$posttext;
        return $this->html_badge($badgetext);
    }

    /**
     * Show choice answer for current student as $USER
     *
     * @param $mod
     * @return string
     * @throws coding_exception
     */
    public function show_choice_answer($mod) {
        global $USER;
        $badgeclass = '';
        $dateformat = "%d %B %Y";

        $submittime = false;
        if (isset($this->user_data)) {
            foreach ($this->user_data as $module) {
                if ($module->module_name == 'choice' && $module->choice_id == $mod->instance &&
                    $module->choice_userid == $USER->id) {
                    $submittime = $module->choice_submit_time;
                    break;
                }
            }
        }
        if ($submittime) {
            $badgetext = get_string('badge_answered',
                    'format_qmultopics').userdate($submittime, $dateformat);
        } else {
            $badgetext = get_string('badge_notanswered', 'format_qmultopics');
        }
        return $this->html_badge($badgetext, $badgeclass);
    }

    // Feedbacks.
    /**
     * Show feedback badge plus a due date badge if there is a due date
     *
     * @param $mod
     * @return string
     * @throws coding_exception
     * @throws dml_exception
     */
    public function show_feedback_badge0($mod) {
        global $COURSE;
        $o = '';

        if (isset($COURSE->module_data)) {
            foreach ($COURSE->module_data as $module) {
                // If the feedback has a due date show it.
                if ($module->module_name == 'feedback' && $module->feedback_id == $mod->instance && $module->feedback_duedate > 0) {
                    $o .= $this->show_due_date_badge($module->feedback_duedate);
                    break;
                }
            }
        }

        // Check if the user is able to grade (e.g. is a teacher).
        if (has_capability('mod/assign:grade', $mod->context)) {
            // Show submission numbers and ungraded submissions if any.
            $o .= $this->show_feedback_completions($mod);
        } else {
            // Show date of submission.
            $o .= $this->show_feedback_completion($mod);
        }

        return $o;
    }
    public function show_feedback_badge($mod) {
        $o = '';

        if (isset($this->choice_data[$mod->instance])) {
            $o .= $this->show_due_date_badge($this->feedback_data[$mod->instance]->duedate);
        }
        // Check if the user is able to grade (e.g. is a teacher).
        if (has_capability('mod/assign:grade', $mod->context)) {
            // Show submission numbers and ungraded submissions if any.
            $o .= $this->show_feedback_completions($mod);
        } else {
            // Show date of submission.
            $o .= $this->show_feedback_completion($mod);
        }

        return $o;
    }

    /**
     * Show badge with feedback completions of all students
     *
     * @param $mod
     * @return string
     * @throws coding_exception
     * @throws dml_exception
     */
    public function show_feedback_completions0($mod) {
        global $COURSE;

        // Show answers by enrolled students.
        $badgetext = '';
        $badgeclass = '';
        $capability = 'feedback';
        $pretext = '';
        $xofy = get_string('badge_xofy', 'format_qmultopics');
        $posttext = get_string('badge_completed', 'format_qmultopics');
        $enrolledstudents = $this->enrolled_users($capability);
        if ($enrolledstudents) {
            $submissions = 0;
            if (isset($COURSE->module_data)) {
                foreach ($COURSE->module_data as $module) {
                    if ($module->module_name == 'feedback' && $module->feedback_id == $mod->instance &&
                        $module->feedback_userid != null) {
                        $submissions++;
                    }
                }
            }

            $badgetext = $pretext
                .$submissions
                .$xofy
                .count($enrolledstudents)
                .$posttext;
        }
        if ($badgetext != '') {
            return $this->html_badge($badgetext, $badgeclass);
        } else {
            return '';
        }
    }
    public function show_feedback_completions($mod) {
        global $COURSE;
        if (!$enrolledstudents = $this->enrolled_users('feedback')) {
            return '';
        }

        // Show answers by enrolled students.
        $pretext = '';
        $xofy = get_string('badge_xofy', 'format_qmultopics');
        $posttext = get_string('badge_completed', 'format_qmultopics');

        // Get the number of submissions for this module.
        if (!$completions = $this->feedback_completions[$mod->instance]->completed) {
            $completions = 0;
        }
        $badgetext = $pretext
            .$completions
            .$xofy
            .count($enrolledstudents)
            .$posttext;
        return $this->html_badge($badgetext);
    }

    /**
     * Show feedback by current student as $USER
     *
     * @param $mod
     * @return string
     * @throws coding_exception
     */
    public function show_feedback_completion($mod) {
        global $USER;
        $badgeclass = '';
        $dateformat = "%d %B %Y";
        $submission = false;
        if (isset($this->user_data)) {
            foreach ($this->user_data as $module) {
                if ($module->module_name == 'feedback' && $module->feedback_id == $mod->instance &&
                    $module->feedback_userid == $USER->id) {
                    $submission = $module;
                    break;
                }
            }
        }
        if ($submission) {
            $badgetext = get_string('badge_completed',
                    'format_qmultopics').userdate($submission->feedback_submit_time, $dateformat);
        } else {
            $badgetext = get_string('badge_notcompleted', 'format_qmultopics');
        }
        return $this->html_badge($badgetext, $badgeclass);
    }

    // Lessons.
    /**
     * Show lesson badge plus additional due date badge if there is a due date
     *
     * @param $mod
     * @return string
     * @throws coding_exception
     * @throws dml_exception
     */
    public function show_lesson_badge0($mod) {
        global $COURSE;
        $o = '';

        if (isset($COURSE->module_data)) {
            foreach ($COURSE->module_data as $module) {
                // If the feedback has a due date show it.
                if ($module->module_name == 'lesson' & $module->lesson_id == $mod->instance && $module->lesson_duedate > 0) {
                    $o .= $this->show_due_date_badge($module->lesson_duedate);
                    break;
                }
            }
        }

        // Check if the user is able to grade (e.g. is a teacher).
        if (has_capability('mod/assign:grade', $mod->context)) {
            // Show submission numbers and ungraded submissions if any.
            $o .= $this->show_lesson_attempts($mod);
        } else {
            // Show date of submission.
            $o .= $this->show_lesson_attempt($mod);
        }

        return $o;
    }
    public function show_lesson_badge($mod) {
        global $COURSE;
        $o = '';

        if (isset($COURSE->module_data)) {
            foreach ($COURSE->module_data as $module) {
                // If the feedback has a due date show it.
                if ($module->module_name == 'lesson' & $module->lesson_id == $mod->instance && $module->lesson_duedate > 0) {
                    $o .= $this->show_due_date_badge($module->lesson_duedate);
                    break;
                }
            }
        }

        if (isset($this->lessonk_data) && $this->lesson_data[$mod->instance]->duedate > 0) {
            $o .= $this->show_due_date_badge($this->lesson_data[$mod->instance]->duedate);
        }

        // Check if the user is able to grade (e.g. is a teacher).
        if (has_capability('mod/assign:grade', $mod->context)) {
            // Show submission numbers and ungraded submissions if any.
            $o .= $this->show_lesson_attempts($mod);
        } else {
            // Show date of submission.
            $o .= $this->show_lesson_attempt($mod);
        }

        return $o;
    }

    /**
     * Show badge with lesson attempts of all students
     *
     * @param $mod
     * @return string
     * @throws coding_exception
     * @throws dml_exception
     */
    public function show_lesson_attempts0($mod) {
        global $COURSE;

        // Show answers by enrolled students.
        $spacer = get_string('badge_commaspacer', 'format_qmultopics');
        $badgetext = '';
        $badgeclass = '';
        $capability = 'lesson';
        $pretext = '';
        $xofy = get_string('badge_xofy', 'format_qmultopics');
        $posttext = get_string('badge_attempted', 'format_qmultopics');
        $completedtext = get_string('badge_completed', 'format_qmultopics');
        $enrolledstudents = $this->enrolled_users($capability);
        if ($enrolledstudents) {
            $submissions = [];
            $completed = [];
            if (isset($COURSE->module_data)) {
                foreach ($COURSE->module_data as $module) {
                    if ($module->module_name == 'lesson' && $module->lesson_id == $mod->instance &&
                        $module->lesson_userid != null) {
                        $submissions[$module->lesson_userid] = true;
                        if ($module->lesson_completed != null) {
                            $completed[$module->lesson_userid] = true;
                        }
                    }
                }
            }

            $badgetext = $pretext
                .count($submissions)
                .$xofy
                .count($enrolledstudents)
                .$posttext;

            if ($completed > 0) {
                $badgetext =
                    $badgetext
                    .$spacer
                    .count($completed)
                    .$completedtext;
            }
        }
        if ($badgetext != '') {
            return $this->html_badge($badgetext, $badgeclass);
        } else {
            return '';
        }
    }
    public function show_lesson_attempts($mod) {
        if (!$enrolledstudents = $this->enrolled_users('lesson')) {
            return '';
        }

        // Show answers by enrolled students.
        $spacer = get_string('badge_commaspacer', 'format_qmultopics');
        $pretext = '';
        $xofy = get_string('badge_xofy', 'format_qmultopics');
        $posttext = get_string('badge_attempted', 'format_qmultopics');
        $completedtext = get_string('badge_completed', 'format_qmultopics');


        // Get the number of submissions for this module.
        if (!$submissions = $this->lesson_submissions[$mod->instance]->submitted) {
            $submissions = 0;
        }
        // Get the number of completed submissions for this module
        if (!$completed = $this->lesson_completions[$mod->instance]->completed) {
            $completed = 0;
        }

        $badgetext = $pretext
            .$submissions
            .$xofy
            .count($enrolledstudents)
            .$posttext;

        if ($completed > 0) {
            $badgetext =
                $badgetext
                .$spacer
                .$completed
                .$completedtext;
        }
        return $this->html_badge($badgetext);
    }

    /**
     * Show lesson attempt for the current student as $USER
     *
     * @param $mod
     * @return string
     * @throws coding_exception
     */
    public function show_lesson_attempt($mod) {
        global $USER;
        $badgeclass = '';
        $dateformat = "%d %B %Y";
        $submission = false;
        if (isset($this->user_data)) {
            foreach ($this->user_data as $module) {
                if ($module->module_name == 'lesson' && $module->lesson_id == $mod->instance &&
                    $module->lesson_userid == $USER->id) {
                    $submission = $module;
                    break;
                }
            }
        }
        if ($submission) {
            if ($submission->lesson_completed) {
                $badgetext = get_string('badge_completed',
                        'format_qmultopics').userdate($submission->lesson_completed, $dateformat);
            } else {
                $badgetext = get_string('badge_attempted',
                        'format_qmultopics').userdate($submission->lesson_submit_time, $dateformat);
            }
        } else {
            $badgetext = get_string('badge_notcompleted', 'format_qmultopics');
        }
        return $this->html_badge($badgetext, $badgeclass);
    }

    // Quizzes.
    /**
     * Quiz badge plus a due date badge if there is a due date
     *
     * @param $mod
     * @return string
     * @throws coding_exception
     * @throws dml_exception
     */
    public function show_quiz_badge($mod) {
        $o = '';

        if (isset($this->quiz_data) && $this->quiz_data[$mod->instance]->duedate > 0) {
            $o .= $this->show_due_date_badge($this->quiz_data[$mod->instance]->duedate);
        }

        // Check if the user is able to grade (e.g. is a teacher).
        if (has_capability('mod/assign:grade', $mod->context)) {
            // Show submission numbers and ungraded submissions if any.
            $o .= $this->show_quiz_attempts($mod);
        } else {
            // Show date of submission.
            $o .= $this->show_quiz_attempt($mod);
        }

        return $o;
    }

    /**
     * Show quiz attempts of all students.
     *
     * @param $mod
     * @return string
     * @throws coding_exception
     * @throws dml_exception
     */
    public function show_quiz_attempts($mod) {
        if (!$enrolledstudents = $this->enrolled_users('quiz')) {
            return '';
        }

        // Show attempts by enrolled students.
        $pretext = '';
        $xofy = get_string('badge_xofy', 'format_qmultopics');
        $posttext = get_string('badge_attempted', 'format_qmultopics');

        // Get the number of submissions for this module.
        if (!$submissions = $this->quiz_submitted[$mod->instance]->submitted) {
            $submissions = 0;
        }
        // Get the number of finished submissions for this module
        if (!$finished = $this->quiz_submitted[$mod->instance]->finished) {
            $finished = 0;
        }
        $badgetext = $pretext
            .$submissions
            .$xofy
            .count($enrolledstudents)
            .$posttext
            .($submissions > 0 ? ', '.$finished.get_string('badge_finished', 'format_qmultopics') : '');

        return $this->html_badge($badgetext);
     }

    /**
     * Show quiz attempts for the current student as $USER
     *
     * @param $mod
     * @return string
     * @throws coding_exception
     */
    public function show_quiz_attempt($mod) {
        global $USER;
        $o = '';
        $badgeclass = '';
        $dateformat = "%d %B %Y";

        $submissions = [];
        if (isset($this->user_data)) {
            foreach ($this->user_data as $module) {
                if ($module->module_name == 'quiz' && $module->quiz_id == $mod->instance && $module->quiz_userid == $USER->id) {
                    $submissions[] = $module;
                }
            }
            if (count($submissions)) {
                foreach ($submissions as $submission) {
                    switch($submission->quiz_state) {
                        case "inprogress":
                            $badgetext = get_string('badge_inprogress',
                                    'format_qmultopics').userdate($submission->quiz_timestart, $dateformat);
                            break;
                        case "finished":
                            $badgetext = get_string('badge_finished',
                                    'format_qmultopics').userdate($submission->quiz_submit_time, $dateformat);
                            break;
                    }
                    if ($badgetext) {
                        $o .= $this->html_badge($badgetext, $badgeclass);
                    }
                }
            } else {
                $badgetext = get_string('badge_notattempted', 'format_qmultopics');
                $o .= $this->html_badge($badgetext, $badgeclass);
            }
        }

        return $o;
    }

    //==================================================================================================================

    // Assignment.
    protected function get_assignment_data() {
        global $COURSE, $DB;

        $sql = "
            select
            cm.instance as moduleid
            ,a.duedate as duedate
            ,a.cutoffdate as cutoffdate
            ,a.teamsubmission
            ,a.requireallteammemberssubmit
            from {course_modules} cm
            join {modules} m on m.id = cm.module
            join {assign} a on a.id = cm.instance and a.course = cm.course
            where m.name = 'assign'
            and cm.course = $COURSE->id
        ";
        return $DB->get_records_sql($sql);
    }

    protected function get_group_assignment_data() {
        global $COURSE, $DB;
        $sql = "
            select
            concat_ws('_', g.id,gm.id, asu.id, ag.id, gi.id, gg.id) as row_id
            ,g.id
            ,gi.hidden as gi_hidden
            ,gi.locked as gi_locked
            ,gg.hidden as gg_hidden
            ,gg.locked as gg_locked
            ,gm.id as ID
            ,gm.groupid
            ,gm.userid
            ,asu.assignment
            ,asu.status
            ,ag.grade
            from {groups} g
            join {groups_members} gm on gm.groupid = g.id
            left join {assign_submission} asu on asu.groupid = g.id
            left join {assign_grades} ag on (ag.assignment = asu.assignment and ag.userid = gm.userid)
            # grading
            left join {grade_items} gi on (gi.courseid = g.courseid
                and gi.itemmodule = 'assign' and gi.iteminstance = asu.assignment)
            left join {grade_grades} gg on (gg.itemid = gi.id and gg.userid = asu.userid)
            where g.courseid = $COURSE->id and asu.userid = 0";
        return $DB->get_records_sql($sql);
    }

    protected function get_assignments_submitted($studentids) {
        global $COURSE, $DB;

        $sql = "
            select
            cm.instance as moduleid
            ,count(distinct asu.userid) as submitted
            from {course_modules} cm
            join {modules} m on m.id = cm.module
            join {assign} a on a.id = cm.instance and a.course = cm.course and m.name = 'assign'
            join {assign_submission} asu on asu.assignment = a.id
            where m.name = 'assign'
            and cm.course = $COURSE->id
            and asu.userid in ('".$studentids."')
            and asu.status = 'submitted'
            group by cm.instance
        ";
        return $DB->get_records_sql($sql);
    }

    protected function get_assignments_graded($studentids) {
        global $COURSE, $DB;

        $sql = "
            select
            cm.instance as moduleid
            ,count(distinct asu.userid) as graded
            from {course_modules} cm
            join {modules} m on m.id = cm.module
            join {assign} a on a.id = cm.instance and a.course = cm.course
            join {assign_submission} asu on asu.assignment = a.id
            join {assign_grades} ag on ag.assignment = asu.assignment and ag.userid = asu.userid
            join {grade_items} gi on (gi.courseid = cm.course and gi.itemmodule = m.name and gi.iteminstance = cm.instance)
            join {grade_grades} gg on (gg.itemid = gi.id and gg.userid = ag.userid)
            where m.name = 'assign'
            and cm.course = $COURSE->id
            and asu.userid in ('".$studentids."')
            and asu.status = 'submitted'
            and ag.grade > 0
            group by cm.instance
        ";
        return $DB->get_records_sql($sql);
    }

    // Choice.
    protected function get_choice_data() {
        global $COURSE, $DB;

        $sql = "
            select
            cm.instance as moduleid
            ,c.timeclose as duedate
            from {course_modules} cm
            join {modules} m on m.id = cm.module
            join {choice} c on c.id = cm.instance and c.course = cm.course
            where m.name = 'choice'
            and cm.course = $COURSE->id
        ";
        return $DB->get_records_sql($sql);
    }

    protected function get_choice_answers($studentids) {
        global $COURSE, $DB;

        $sql = "
            select
            cm.instance as moduleid
            ,count(distinct ca.userid) as submitted
            from {course_modules} cm
            join {modules} m on m.id = cm.module
            join {choice} c on c.id = cm.instance and c.course = cm.course
            join {choice_answers} ca on ca.choiceid = c.id
            where m.name = 'choice'
            and cm.course = $COURSE->id
            and ca.userid in ('".$studentids."')
            group by cm.instance
        ";
        return $DB->get_records_sql($sql);
    }

    // Choice.
    protected function get_feedback_data() {
        global $COURSE, $DB;

        $sql = "
            select
            cm.instance as moduleid
            ,f.timeclose as duedate
            from {course_modules} cm
            join {modules} m on m.id = cm.module
            join {feedback} f on f.id = cm.instance and f.course = cm.course
            where m.name = 'feedback'
            and cm.course = $COURSE->id
        ";
        return $DB->get_records_sql($sql);
    }

    protected function get_feedback_completions($studentids) {
        global $COURSE, $DB;

        $sql = "
            select
            cm.instance as moduleid
            ,count(distinct fc.userid) as completed
            from {course_modules} cm
            join {modules} m on m.id = cm.module
            join {feedback} f on f.id = cm.instance and f.course = cm.course
            join {feedback_completed} fc on fc.feedback = f.id
            where m.name = 'feedback'
            and cm.course = $COURSE->id
            and fc.userid in ('".$studentids."')
            group by cm.instance
        ";
        return $DB->get_records_sql($sql);
    }

    // Lesson.
    protected function get_lesson_data() {
        global $COURSE, $DB;

        $sql = "
            select
            cm.instance as moduleid
            ,l.deadline as duedate
            from {course_modules} cm
            join {modules} m on m.id = cm.module
            join {lesson} l on l.id = cm.instance and l.course = cm.course
            where m.name = 'lesson'
            and cm.course = $COURSE->id
        ";
        return $DB->get_records_sql($sql);
    }

    protected function get_lesson_submissions($studentids) {
        global $COURSE, $DB;

        $sql = "
            select
            cm.instance as moduleid
            ,count(distinct la.userid) as submitted
            from {course_modules} cm
            join {modules} m on m.id = cm.module
            join {lesson} l on l.id = cm.instance and l.course = cm.course
            join {lesson_attempts} la on la.lessonid = l.id
            where m.name = 'lesson'
            and cm.course = $COURSE->id
            and la.userid in ('".$studentids."')
            group by cm.instance
        ";
        return $DB->get_records_sql($sql);
    }

    protected function get_lesson_completions($studentids) {
        global $COURSE, $DB;

        $sql = "
            select
            cm.instance as moduleid
            ,count(distinct lg.userid) as completed
            from {course_modules} cm
            join {modules} m on m.id = cm.module
            join {lesson} l on l.id = cm.instance and l.course = cm.course
            join {lesson_attempts} la on la.lessonid = l.id
            join {lesson_grades} lg on lg.lessonid = la.lessonid and lg.userid = la.userid
            where m.name = 'lesson'
            and cm.course = $COURSE->id
            and lg.userid in ('".$studentids."')
            group by cm.instance
        ";
        return $DB->get_records_sql($sql);
    }

    // Quiz.
    protected function get_quiz_data() {
        global $COURSE, $DB;

        $sql = "
            select
            cm.instance as moduleid
            ,q.timeclose as duedate 
            #,cm.id
            from {course_modules} cm
            join {modules} m on m.id = cm.module
            # quiz
            join {quiz} q on q.id = cm.instance and q.course = cm.course
            where m.name = 'quiz'
            and cm.course = $COURSE->id
        ";
        return $DB->get_records_sql($sql);
    }

    protected function get_quiz_submitted($studentids) {
        global $COURSE, $DB;

        $sql = "
            select
            cm.instance as moduleid
            ,count(distinct qa.userid) as submitted
            ,count(distinct case when qa.state = 'finished' then qa.userid end) as finished
            from {course_modules} cm
            join {modules} m on m.id = cm.module
            join {quiz} q on q.id = cm.instance and q.course = cm.course
            join {quiz_attempts} qa on qa.quiz = q.id
            where m.name = 'quiz'
            and cm.course = $COURSE->id
            and qa.userid in ('".$studentids."')
            group by cm.instance
        ";
        return $DB->get_records_sql($sql);
    }

    protected function get_quiz_graded($studentids) {
        global $COURSE, $DB;

        $sql = "
            select
            cm.instance as moduleid
            ,count(distinct qa.userid) as count
            from {course_modules} cm
            join {modules} m on m.id = cm.module
            # quiz
            join {quiz} q on q.id = cm.instance and q.course = cm.course
            join {quiz_attempts} qa on qa.quiz = q.id
            join {quiz_grades} qg on qg.quiz = qa.quiz and qg.userid = qa.userid
            # grading
            join {grade_items} gi on (gi.courseid = cm.course and gi.itemmodule = m.name and gi.iteminstance = cm.instance)
            join {grade_grades} gg on (gg.itemid = gi.id and gg.userid = qa.userid)
            where m.name = 'quiz'
            and cm.course = $COURSE->id
            and qa.userid in ('".$studentids."')
            and qa.state = 'finished'
            and qg.grade > 0
            group by cm.instance
        ";
        $temp = $DB->get_records_sql($sql);
        $result = [];
        if ($temp) foreach ($temp as $key=>$value) {
            $result[$key] = $value->count;
        }
        return $result;
    }
}

