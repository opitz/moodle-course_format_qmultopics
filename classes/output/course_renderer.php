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
        $rolename = isset($roles[$role]) ? $roles[$role]->shortname : '';

        // Get the module data sets for the current $COURSE
        $this->assignment_data = $this->get_assignment_data($COURSE->id);
        $this->choice_data = $this->get_choice_data($COURSE->id);
        $this->feedback_data = $this->get_feedback_data($COURSE->id);
        $this->lesson_data = $this->get_lesson_data($COURSE->id);
        $this->quiz_data = $this->get_quiz_data($COURSE->id);

        if ($rolename != 'student' || is_siteadmin()) {
            // Pre-load the assessment label data for course admins
            $this->enrolled_students = $this->get_enrolled_students();
            // Assignment.
            if (isset($this->enrolled_students['assign'])){
                $studentids = implode("','",array_keys($this->enrolled_students['assign']));

                $this->group_assignment_data = $this->get_group_assignment_data($COURSE->id);
                $this->assignments_submitted = $this->get_assignments_submitted($COURSE->id, $studentids);
            }
            // Choice.
            if (isset($this->enrolled_students['choice'])){
                $studentids = implode("','",array_keys($this->enrolled_students['choice']));

                $this->choice_answers = $this->get_choice_answers($COURSE->id, $studentids);
            }
            // Feedback.
            if (isset($this->enrolled_students['feedback'])){
                $studentids = implode("','",array_keys($this->enrolled_students['feedback']));

                $this->feedback_completions = $this->get_feedback_completions($COURSE->id, $studentids);
            }
            // Lesson.
            if (isset($this->enrolled_students['lesson'])){
                $studentids = implode("','",array_keys($this->enrolled_students['lesson']));
                $this->lesson_submissions = $this->get_lesson_submissions($COURSE->id, $studentids);
            }
            // Quiz.
            if (isset($this->enrolled_students['quiz'])){
                $studentids = implode("','",array_keys($this->enrolled_students['quiz']));

                $this->quiz_submitted = $this->get_quiz_submitted($COURSE->id, $studentids);
            }
        } else{
            // Pre-load the assessment label data for a student
            // Assignment.
            $this->assignments_graded = $this->get_student_assignments_graded($COURSE->id, $USER->id);
            $this->group_assignments_graded = $this->get_student_group_assignments_graded($COURSE->id, $USER->id);

            // Choice.
            $this->choice_answers = $this->get_student_choice_answers($COURSE->id, $USER->id);

            // Feedback.
            $this->feedback_completions = $this->get_student_feedback_completions($COURSE->id, $USER->id);

            // Lesson.
            $this->lesson_submissions = $this->get_student_lesson_submissions($COURSE->id, $USER->id);

            // Quiz.
            $this->quiz_submitted = $this->get_student_quiz_submitted($COURSE->id, $USER->id);
        }
        parent::__construct($page, $target);
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

        // For some reason the 'w-100' class causes the module titles to be intended randomly(?)
        // when the QMUL themes are installed
        // For this reason it is disabled here and replaced by a width applied to the indent div below.
        //$output .= html_writer::start_tag('div', array('class' => 'mod-indent-outer w-100'));
        $output .= html_writer::start_tag('div', array('class' => 'mod-indent-outer'));

        // This div is used to indent the content.
        //$output .= html_writer::div('', $indentclasses);
        $output .= html_writer::div('', $indentclasses, ['style' => 'width: 10px;']);

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
    public function show_due_date_badge($mod, $duedate, $cutoffdate = 0) {
        global $CFG;
        // If duedate is 0 don't show a badge.
        if ($duedate == 0) {
            return '';
        }
        $dateformat = "%d %B %Y";
        $badgedate = $duedate;
        $badgeclass = 'badge-default';
        $url = '/mod/'.$mod->modname.'/view.php?id='.$mod->id;

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
        return $this->html_badge($badgecontent, $badgeclass,'', $url);
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
    public function html_badge($badgetext, $badgeclass = "badge-default", $title = "", $url = "") {
        if ($badgeclass == '') {
            $badgeclass = 'badge-default';
        }
        $o = '';
        if ($url != '') {
            $badgetext = "<a href='$url'>".$badgetext."</a>";
        }
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

        $cache = cache::make('format_qmultopics', 'enrolled_users');
        if (!get_config('format_qmultopics', 'useassignlabelcaches') || !$result = $cache->get($capability)) {
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
            $result = $DB->get_records_sql($sql, $capjoin->params);
            $cache->set($capability, $result);
        }
        return $result;
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
    public function show_assignment_badges($mod) {
        $o = '';
        if (isset($this->assignment_data[$mod->instance])) {
            // Show assignment due date if it has one.
            $o .= $this->show_due_date_badge($mod, $this->assignment_data[$mod->instance]->duedate, $this->assignment_data[$mod->instance]->cutoffdate);

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
    public function show_assign_submissions($mod) {
        global $CFG;

        // Show submissions by enrolled students.
        $spacer = get_string('badge_commaspacer', 'format_qmultopics');
        $pretext = '';
        $xofy = get_string('badge_xofy', 'format_qmultopics');
        $posttext = get_string('badge_submitted', 'format_qmultopics');
        $ungradedtext = get_string('badge_ungraded', 'format_qmultopics');
        $enrolledstudents = $this->enrolled_users('assign');
        $url = '/mod/'.$mod->modname.'/view.php?action=grading&id='.$mod->id.'&tsort=timesubmitted&filter=require_grading';

        if (!empty($mod->availability)) {
            // Get availability information.
            $info = new \core_availability\info_module($mod);
            $restrictedstudents = $info->filter_user_list($enrolledstudents);
        } else {
            $restrictedstudents = $enrolledstudents;
        }

        if ($restrictedstudents) {
            if (!isset($this->assignments_submitted[$mod->instance]->submitted) ||
                !$submissions = $this->assignments_submitted[$mod->instance]->submitted) {
                $submissions = 0;
            }
            if (!isset($this->assignments_submitted[$mod->instance]->graded) ||
                !$gradings = $this->assignments_submitted[$mod->instance]->graded) {
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
                return $this->html_badge($badgetext, '', '', $url);
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
        global $CFG;

        // Show group submissions by enrolled students.
        $spacer = get_string('badge_commaspacer', 'format_qmultopics');
        $pretext = '';
        $xofy = get_string('badge_xofy', 'format_qmultopics');
        $posttext = get_string('badge_submitted', 'format_qmultopics');
        $groupstext = get_string('badge_groups', 'format_qmultopics');
        $ungradedtext = get_string('badge_ungraded', 'format_qmultopics');
        $enrolledstudents = $this->enrolled_users('assign');
        $url = $CFG->baseurl.'/mod/'.$mod->modname.'/view.php?action=grading&id='.$mod->id.'&tsort=timesubmitted&filter=require_grading';
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
                return $this->html_badge($badgetext,'','',$url);
            } else {
                return '';
            }
        }
    }
    public function show_assign_group_submissions1($mod) {
        // Show group submissions by enrolled students.
        $spacer = get_string('badge_commaspacer', 'format_qmultopics');
        $pretext = '';
        $xofy = get_string('badge_xofy', 'format_qmultopics');
        $posttext = get_string('badge_submitted', 'format_qmultopics');
        $groupstext = get_string('badge_groups', 'format_qmultopics');
        $ungradedtext = get_string('badge_ungraded', 'format_qmultopics');
        $enrolledstudents = $this->enrolled_users('assign');
        $url = '/mod/'.$mod->modname.'/view.php?action=grading&id='.$mod->id.'&tsort=timesubmitted&filter=require_grading';
        if ($enrolledstudents) {
            // Go through the group_data to get numbers for groups, submissions and gradings.
            $coursegroupsarray = [];
            $groupsubmissionsarray = [];
            $groupgradingsarray = [];
            $default_group = 0;
            if (isset($this->group_assignment_data)) {
                foreach ($this->group_assignment_data as $record) {
                    // The default group is present only when a group assignment allows submissions by non-group members
                    // or by users that are member of more than one group - so we need to add 1 to the number of groups
                    if (!$default_group && !$record->preventsubmissionnotingroup) {
                        $default_group = 1;
                    }

                    $coursegroupsarray[$record->groupid] = $record->groupid;
                    if ($record->assignment == $mod->instance && $record->status == 'submitted') {
                        $groupsubmissionsarray[$record->groupid] = true;
                        if ($record->grade > 0) {
                            $groupgradingsarray[$record->groupid] = $record->grade;
                        }
                    }
                }
            }
            $coursegroups = count($coursegroupsarray) + $default_group;
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
                return $this->html_badge($badgetext,'','',$url);
            } else {
                return '';
            }
        }
    }
    public function show_assign_group_submissions($mod) {
        // Show group submissions by enrolled students.
        $spacer = get_string('badge_commaspacer', 'format_qmultopics');
        $pretext = '';
        $xofy = get_string('badge_xofy', 'format_qmultopics');
        $posttext = get_string('badge_submitted', 'format_qmultopics');
        $groupstext = get_string('badge_groups', 'format_qmultopics');
        $ungradedtext = get_string('badge_ungraded', 'format_qmultopics');
        $enrolledstudents = $this->enrolled_users('assign');
        $url = '/mod/'.$mod->modname.'/view.php?action=grading&id='.$mod->id.'&tsort=timesubmitted&filter=require_grading';
        if ($enrolledstudents && isset($this->group_assignment_data) && $this->group_assignment_data[$mod->instance]) {

            $coursegroups = $this->group_assignment_data[$mod->instance]->groups;
            $groupsubmissions = $this->group_assignment_data[$mod->instance]->submitted > 0 ? $this->group_assignment_data[$mod->instance]->submitted : 0;
            $groupgradings = $this->group_assignment_data[$mod->instance]->graded;
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
                return $this->html_badge($badgetext,'','',$url);
            } else {
                return '';
            }
        }
    }

    /**
     * A badge to show the student her/his submission status
     * It will display the date of a submission, a mouseover will show the time for the submission
     *
     * @param $mod
     * @return string
     * @throws coding_exception
     */
    public function show_assign_submission($mod) {
        global $CFG, $COURSE, $USER;

        $context = context_module::instance($mod->id);
        $assign = new assign($context, $mod, $COURSE);
        $user_submission = $assign->get_user_submission($USER->id, false);
        $url = '/mod/'.$mod->modname.'/view.php?id='.$mod->id;

        $badgetitle = '';
        $dateformat = "%d %B %Y";
        $timeformat = "%d %B %Y %H:%M:%S";

        if (!isset($user_submission->status) || $user_submission->status != 'submitted') {
            $badgetext = get_string('badge_notsubmitted', 'format_qmultopics');
        } else {
            $badgetext = get_string('badge_submitted',
                    'format_qmultopics').userdate($user_submission->timemodified, $dateformat);
            if ($this->get_grading($mod) || $this->get_group_grading($mod)) {
                $badgetext .= get_string('badge_feedback', 'format_qmultopics');
            }
            $badgetitle = get_string('badge_submission_time_title',
                    'format_qmultopics') . userdate($user_submission->timemodified, $timeformat);
        }
        return $this->html_badge($badgetext, '', $badgetitle, $url);
    }

    /**
     * Return true if the given student has been graded yet and the grading is neither hidden nor locked
     *
     * @param $mod
     * @return array
     */
    protected function get_grading($mod) {
        foreach ($this->assignments_graded as $assignment) {
            if ($assignment->instance == $mod->instance
                && $assignment->finalgrade > 0
                && ($assignment->gi_hidden == 0 || ($assignment->gi_hidden > 1 && $assignment->gi_hidden < time()))
                && ($assignment->gg_hidden == 0 || ($assignment->gg_hidden > 1 && $assignment->gg_hidden < time()))
                && $assignment->gi_locked == 0
                && $assignment->gg_locked == 0
            ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Return true if the submission of the group of which the given student is a member has already been graded
     * and the grading is neither hidden nor locked.
     *
     * @param $mod
     * @return bool
     */
    protected function get_group_grading($mod) {
        if (!isset($this->group_assignments_graded)) {
            return false;
        }
        foreach ($this->group_assignments_graded as $assignment) {
            if ($assignment->instance == $mod->instance
                && $assignment->finalgrade > 0
                && ($assignment->gi_hidden == 0 || ($assignment->gi_hidden > 1 && $assignment->gi_hidden < time()))
                && ($assignment->gg_hidden == 0 || ($assignment->gg_hidden > 1 && $assignment->gg_hidden < time()))
                && $assignment->gi_locked == 0
                && $assignment->gg_locked == 0
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
            $o .= $this->show_due_date_badge($mod, $this->choice_data[$mod->instance]->duedate);

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
    public function show_choice_answers($mod) {
        global $CFG;

        if (!$enrolledstudents = $this->enrolled_users('choice')) {
            return '';
        }
        $pretext = '';
        $xofy = get_string('badge_xofy', 'format_qmultopics');
        $posttext = get_string('badge_answered', 'format_qmultopics');
        $url = '/mod/'.$mod->modname.'/view.php?action=grading&id='.$mod->id.'&tsort=timesubmitted&filter=require_grading';
        // Get the number of submissions for this module.
        if (!isset($this->choice_answers[$mod->instance]->submitted) || !$submissions = $this->choice_answers[$mod->instance]->submitted) {
            $submissions = 0;
        }
        $badgetext = $pretext
            .$submissions
            .$xofy
            .count($enrolledstudents)
            .$posttext;
        return $this->html_badge($badgetext,'','',$url);
    }

    /**
     * Show choice answer for current student
     *
     * @param $mod
     * @return string
     * @throws coding_exception
     */
    public function show_choice_answer($mod) {
        global $CFG;

        $dateformat = "%d %B %Y";
        $url = '/mod/'.$mod->modname.'/view.php?id='.$mod->id;

        if (isset($this->choice_answers[$mod->instance]->submit_time) && $submittime = $this->choice_answers[$mod->instance]->submit_time) {
            $badgetext = get_string('badge_answered',
                    'format_qmultopics').userdate($submittime, $dateformat);
        } else {
            $badgetext = get_string('badge_notanswered', 'format_qmultopics');
        }
        return $this->html_badge($badgetext,'','',$url);
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
    public function show_feedback_badge($mod) {
        $o = '';

        if (isset($this->choice_data[$mod->instance])) {
            $o .= $this->show_due_date_badge($mod, $this->feedback_data[$mod->instance]->duedate);
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
    public function show_feedback_completions($mod) {
        global $CFG;

        if (!$enrolledstudents = $this->enrolled_users('feedback')) {
            return '';
        }

        // Show answers by enrolled students.
        $pretext = '';
        $xofy = get_string('badge_xofy', 'format_qmultopics');
        $posttext = get_string('badge_completed', 'format_qmultopics');
        $url = '/mod/'.$mod->modname.'/view.php?action=grading&id='.$mod->id.'&tsort=timesubmitted&filter=require_grading';

        // Get the number of submissions for this module.
        if (!$completions = $this->feedback_completions[$mod->instance]->completed) {
            $completions = 0;
        }
        $badgetext = $pretext
            .$completions
            .$xofy
            .count($enrolledstudents)
            .$posttext;
        return $this->html_badge($badgetext,'','',$url);
    }

    /**
     * Show feedback by current student
     *
     * @param $mod
     * @return string
     * @throws coding_exception
     */
    public function show_feedback_completion($mod) {
        global $CFG;

        $dateformat = "%d %B %Y";
        $url = '/mod/'.$mod->modname.'/view.php?id='.$mod->id;
        if (isset($this->feedback_completions[$mod->instance]->completed) && $submission = $this->feedback_completions[$mod->instance]->completed) {
            $badgetext = get_string('badge_completed',
                    'format_qmultopics').userdate($this->feedback_completions[$mod->instance]->submit_time, $dateformat);
        } else {
            $badgetext = get_string('badge_notcompleted', 'format_qmultopics');
        }
        return $this->html_badge($badgetext,'','',$url);
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
    public function show_lesson_badge($mod) {
        $o = '';

        if (isset($this->lesson_data[$mod->instance]->duedate) && $this->lesson_data[$mod->instance]->duedate) {
            $o .= $this->show_due_date_badge($mod, $this->lesson_data[$mod->instance]->duedate);
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
    public function show_lesson_attempts($mod) {
        global $CFG;

        if (!$enrolledstudents = $this->enrolled_users('lesson')) {
            return '';
        }

        // Show answers by enrolled students.
        $spacer = get_string('badge_commaspacer', 'format_qmultopics');
        $pretext = '';
        $xofy = get_string('badge_xofy', 'format_qmultopics');
        $posttext = get_string('badge_attempted', 'format_qmultopics');
        $completedtext = get_string('badge_completed', 'format_qmultopics');
        $url = '/mod/'.$mod->modname.'/view.php?action=grading&id='.$mod->id.'&tsort=timesubmitted&filter=require_grading';

        // Get the number of submissions for this module.
        if (!$submissions = $this->lesson_submissions[$mod->instance]->submitted) {
            $submissions = 0;
        }
        // Get the number of completed submissions for this module
        if (!$completed = $this->lesson_submissions[$mod->instance]->completed) {
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
        return $this->html_badge($badgetext,'','',$url);
    }

    /**
     * Show lesson attempt for the current student
     *
     * @param $mod
     * @return string
     * @throws coding_exception
     */
    public function show_lesson_attempt($mod) {
        global $CFG;

        $o = '';
        $dateformat = "%d %B %Y";
        $url = '/mod/'.$mod->modname.'/view.php?id='.$mod->id;

        foreach ($this->lesson_submissions as $submission) {
            if($submission->moduleid == $mod->instance) {
                if (isset($submission->completed) && $submission->completed) {
                    $o .=  $this->html_badge(get_string('badge_completed',
                            'format_qmultopics').userdate($submission->completed, $dateformat),'','',$url);
                } else {
                    $o .= $this->html_badge(get_string('badge_attempted',
                            'format_qmultopics').userdate($submission->submit_time, $dateformat),'','',$url);
                }
            }
        }
        if ($o != '') {
            return $o;
        }
        return $this->html_badge(get_string('badge_notcompleted', 'format_qmultopics'),'','',$url);
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
            $o .= $this->show_due_date_badge($mod, $this->quiz_data[$mod->instance]->duedate);
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
        global $CFG;

        if (!$enrolledstudents = $this->enrolled_users('quiz')) {
            return '';
        }

        // Show attempts by enrolled students.
        $pretext = '';
        $xofy = get_string('badge_xofy', 'format_qmultopics');
        $posttext = get_string('badge_attempted', 'format_qmultopics');
        $url = '/mod/'.$mod->modname.'/view.php?action=grading&id='.$mod->id.'&tsort=timesubmitted&filter=require_grading';

        // Get the number of submissions for this module.
        if (!isset($this->quiz_submitted[$mod->instance]->submitted) || !$submissions = $this->quiz_submitted[$mod->instance]->submitted) {
            $submissions = 0;
        }
        // Get the number of finished submissions for this module
        if (!isset($this->quiz_submitted[$mod->instance]->finished) || !$finished = $this->quiz_submitted[$mod->instance]->finished) {
            $finished = 0;
        }
        $badgetext = $pretext
            .$submissions
            .$xofy
            .count($enrolledstudents)
            .$posttext
            .($submissions > 0 ? ', '.$finished.get_string('badge_finished', 'format_qmultopics') : '');

        return $this->html_badge($badgetext, '', '', $url);
    }

    /**
     * Show quiz attempts for the current student
     *
     * @param $mod
     * @return string
     * @throws coding_exception
     */
    public function show_quiz_attempt($mod) {
        global $CFG;

        $o = '';
        $dateformat = "%d %B %Y";
        $url = '/mod/'.$mod->modname.'/view.php?id='.$mod->id;

        foreach ($this->quiz_submitted as $submission) {
            if ($submission->moduleid == $mod->instance) {
                if (isset($submission->submitted) && $submission->submitted) {
                    switch($submission->state) {
                        case "inprogress":
                            $o .= $this->html_badge(get_string('badge_inprogress',
                                    'format_qmultopics').userdate($submission->timestart, $dateformat),'','',$url);
                            break;
                        case "finished":
                            $o .= $this->html_badge(get_string('badge_finished',
                                    'format_qmultopics').userdate($submission->submit_time, $dateformat),'','',$url);
                            break;
                    }
                }
            }
        }
        if ($o != '') {
            return $o;
        }
        return $this->html_badge(get_string('badge_notattempted', 'format_qmultopics'),'','',$url);
    }

    //==================================================================================================================

    /**
     * Get information about all assignment assessments of a given course
     *
     * @param $courseid
     * @return array|bool|float|int|mixed|string
     * @throws coding_exception
     * @throws dml_exception
     */
    protected function get_assignment_data($courseid) {
        global $DB;

        // Check if $courseid is actually a course object and if so get the ID.
        if (is_object($courseid)) {
            $courseid = $courseid->id;
        }

        $cache = cache::make('format_qmultopics', 'assignment_data');
        if (!get_config('format_qmultopics', 'useassignlabelcaches') || !$data = $cache->get($courseid)) {
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
            and cm.course = $courseid
            ";
            if ($data = $DB->get_records_sql($sql)) {
                $cache->set($courseid, $data);
            }
        }
        return $data;
    }

    /**
     * Get information about all group assignments of a given course
     *
     * @param $courseid
     * @return array
     * @throws dml_exception
     */
    protected function get_group_assignment_data0($courseid) {
        global $DB;

        // Check if $courseid is actually a course object and if so get the ID.
        if (is_object($courseid)) {
            $courseid = $courseid->id;
        }

        $cache = cache::make('format_qmultopics', 'admin_group_assignment_data');
        if (!$data = $cache->get($courseid)) {
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
            where g.courseid = $courseid and asu.userid = 0
            ";
            if ($data = $DB->get_records_sql($sql)) {
                $cache->set($courseid, $data);
            }
        }
        return $data;
        $sql = "
            ";
        return $DB->get_records_sql($sql);
    }
    protected function get_group_assignment_data1($courseid) {
        global $DB;

        // Check if $courseid is actually a course object and if so get the ID.
        if (is_object($courseid)) {
            $courseid = $courseid->id;
        }

        $cache = cache::make('format_qmultopics', 'admin_group_assignment_data');
        if (!get_config('format_qmultopics', 'useassignlabelcaches') || !$data = $cache->get($courseid)) {
            $sql = "
            select
            uuid_short() as row_id
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
            ,a.preventsubmissionnotingroup
            from {groups} g
            join {groups_members} gm on gm.groupid = g.id
            left join {assign_submission} asu on asu.groupid = g.id
            left join {assign} a on a.id = asu.assignment 
            left join {assign_grades} ag on (ag.assignment = asu.assignment and ag.userid = gm.userid)
            # grading
            left join {grade_items} gi on (gi.courseid = g.courseid
                and gi.itemmodule = 'assign' and gi.iteminstance = asu.assignment)
            left join {grade_grades} gg on (gg.itemid = gi.id and gg.userid = asu.userid)
            where g.courseid = $courseid and asu.userid = 0
            ";
            if ($data = $DB->get_records_sql($sql)) {
                $cache->set($courseid, $data);
            }
        }
        return $data;
        $sql = "
            ";
        return $DB->get_records_sql($sql);
    }
    protected function get_group_assignment_data($courseid) {
        global $DB;

        // Check if $courseid is actually a course object and if so get the ID.
        if (is_object($courseid)) {
            $courseid = $courseid->id;
        }

        $cache = cache::make('format_qmultopics', 'admin_group_assignment_data');
        if (!get_config('format_qmultopics', 'useassignlabelcaches') || !$data = $cache->get($courseid)) {
            $sql = "
                select
                a.id
                ,count(distinct gr.id) as groups
                ,count(distinct asu.groupid) - a.preventsubmissionnotingroup as submitted
                ,count(distinct case when ag.grade > 0 then asu.groupid end) as graded
                from {course_modules} cm
                join {modules} m on m.id = cm.module
                join {groups} gr on gr.courseid = cm.course
                join {assign} a on a.id = cm.instance and a.course = cm.course and a.teamsubmission = 1
                left join {assign_submission} asu on asu.assignment = a.id and asu.status = 'submitted'
                left join {groups} g on g.id = asu.groupid
                left join {groups_members} gm on gm.groupid = g.id
                left join {assign_grades} ag on ag.assignment = asu.assignment and ag.userid = gm.userid
                where cm.course = $courseid
                group by a.id
            ";
            if ($data = $DB->get_records_sql($sql)) {
                $cache->set($courseid, $data);
            }
        }
        return $data;
        $sql = "
            ";
        return $DB->get_records_sql($sql);
    }

    /**
     * Get submission and grading numbers by a list of students for all assignments of a given course
     *
     * @param $courseid
     * @param $studentids
     * @return array
     * @throws dml_exception
     */
    protected function get_assignments_submitted($courseid, $studentids) {
        global $DB;

        // Check if $courseid is actually a course object and if so get the ID.
        if (is_object($courseid)) {
            $courseid = $courseid->id;
        }

        $cache = cache::make('format_qmultopics', 'admin_assignment_data');
        if (!get_config('format_qmultopics', 'useassignlabelcaches') || !$data = $cache->get($courseid)) {
            $sql = "
            select
            cm.instance as moduleid
            ,count(distinct asu.userid) as submitted
            ,count(distinct gg.userid) as graded
            from {course_modules} cm
            join {modules} m on m.id = cm.module
            join {assign} a on a.id = cm.instance and a.course = cm.course
            join {assign_submission} asu on asu.assignment = a.id
            left join {assign_grades} ag on ag.assignment = asu.assignment and ag.userid = asu.userid and ag.grade > 0
            left join {grade_items} gi on (gi.courseid = cm.course and gi.itemmodule = m.name and gi.iteminstance = cm.instance)
            left join {grade_grades} gg on (gg.itemid = gi.id and gg.userid = ag.userid)
            where m.name = 'assign'
            and cm.course = $courseid
            and asu.userid in ('".$studentids."')
            and asu.status = 'submitted'
            group by cm.instance
            ";
            if ($data = $DB->get_records_sql($sql)) {
                $cache->set($courseid, $data);
            }
        }
        return $data;
    }

    protected function get_student_assignments_submitted0($courseid, $studentid) {
        global $DB;

        // Check if $courseid is actually a course object and if so get the ID.
        if (is_object($courseid)) {
            $courseid = $courseid->id;
        }

        $sql = "
            select
            cm.instance as moduleid
            ,asu.userid as submitted
            ,asu.timemodified as submit_time
            from {course_modules} cm
            join {modules} m on m.id = cm.module
            join {assign} a on a.id = cm.instance and a.course = cm.course
            join {assign_submission} asu on asu.assignment = a.id
            where m.name = 'assign'
            and cm.course = $courseid
            and asu.userid = $studentid
            and asu.status = 'submitted'
        ";
        return $DB->get_records_sql($sql);
    }

    /**
     * Get all graded assignments of a given course and student
     *
     * @param $courseid
     * @param $studentid
     * @return array
     * @throws dml_exception
     */
    protected function get_student_assignments_graded($courseid, $studentid) {
        global $DB;

        // Check if $courseid is actually a course object and if so get the ID.
        if (is_object($courseid)) {
            $courseid = $courseid->id;
        }

        $cache = cache::make('format_qmultopics', 'student_assignment_data');
        if (!get_config('format_qmultopics', 'useassignlabelcaches') || !$data = $cache->get($courseid)) {
            $sql = "
            select
            uuid_short()
            ,cm.instance
            ,gg.finalgrade
            ,gi.hidden as gi_hidden
            ,gi.locked as gi_locked
            ,gg.hidden as gg_hidden
            ,gg.locked as gg_locked
            from {course_modules} cm
            join {modules} m on m.id = cm.module
            join {assign} a on a.id = cm.instance and a.course = cm.course
            join {assign_submission} asu on asu.assignment = a.id
            join {grade}_items gi on (gi.courseid = cm.course and gi.itemmodule = m.name and gi.iteminstance = cm.instance)
            join {grade_grades} gg on (gg.itemid = gi.id and gg.userid = asu.userid)
            where m.name = 'assign'
            and gg.finalgrade > 0
            and cm.course = $courseid
            and asu.userid = $studentid
            and gg.finalgrade > 0
            ";
            if ($data = $DB->get_records_sql($sql)) {
                $cache->set($courseid, $data);
            }
        }
        return $data;
    }

    /**
     * Get all graded group assignments of a given course and student
     *
     * @param $courseid
     * @param $studentid
     * @return array
     * @throws dml_exception
     */
    protected function get_student_group_assignments_graded($courseid, $studentid) {
        global $DB;

        // Check if $courseid is actually a course object and if so get the ID.
        if (is_object($courseid)) {
            $courseid = $courseid->id;
        }

        $cache = cache::make('format_qmultopics', 'student_group_assignment_data');
        if (!get_config('format_qmultopics', 'useassignlabelcaches') || !$data = $cache->get($courseid)) {
            $sql = "
            select
            uuid_short()
            ,asu.assignment as instance
            ,gi.hidden as gi_hidden
            ,gi.locked as gi_locked
            ,gg.hidden as gg_hidden
            ,gg.locked as gg_locked
            ,gg.finalgrade
            from {groups} g
            join {groups_members} gm on gm.groupid = g.id
            join {assign_submission} asu on asu.groupid = g.id
            join {grade_items} gi on (gi.courseid = g.courseid
                and gi.itemmodule = 'assign' and gi.iteminstance = asu.assignment)
            join {grade_grades} gg on (gg.itemid = gi.id and gg.userid = gm.userid)
            where g.courseid = $courseid 
            and asu.userid = 0
            and gm.userid = $studentid
            ";
            if ($data = $DB->get_records_sql($sql)) {
                $cache->set($courseid, $data);
            }
        }
        return $data;
    }

    // Choice.

    /**
     * Get information about all choice assessments of a given course
     *
     * @param $courseid
     * @return array|bool|float|int|mixed|string
     * @throws coding_exception
     * @throws dml_exception
     */
    protected function get_choice_data($courseid) {
        global $DB;

        // Check if $courseid is actually a course object and if so get the ID.
        if (is_object($courseid)) {
            $courseid = $courseid->id;
        }

        $cache = cache::make('format_qmultopics', 'choice_data');
        if (!get_config('format_qmultopics', 'useassignlabelcaches') || !$data = $cache->get($courseid)) {
            $sql = "
            select
            cm.instance as moduleid
            ,c.timeclose as duedate
            from {course_modules} cm
            join {modules} m on m.id = cm.module
            join {choice} c on c.id = cm.instance and c.course = cm.course
            where m.name = 'choice'
            and cm.course = $courseid
            ";
            if ($data = $DB->get_records_sql($sql)) {
                $cache->set($courseid, $data);
            }
        }
        return $data;
    }

    /**
     * Get choice answer numbers for a list of students for all assignments of a given course
     *
     * @param $courseid
     * @param $studentids
     * @return array
     * @throws dml_exception
     */
    protected function get_choice_answers($courseid, $studentids) {
        global $DB;

        // Check if $courseid is actually a course object and if so get the ID.
        if (is_object($courseid)) {
            $courseid = $courseid->id;
        }

        $cache = cache::make('format_qmultopics', 'admin_choice_data');
        if (!get_config('format_qmultopics', 'useassignlabelcaches') || !$data = $cache->get($courseid)) {
            $sql = "
            select
            cm.instance as moduleid
            ,count(distinct ca.userid) as submitted
            from {course_modules} cm
            join {modules} m on m.id = cm.module
            join {choice} c on c.id = cm.instance and c.course = cm.course
            join {choice_answers} ca on ca.choiceid = c.id
            where m.name = 'choice'
            and cm.course = $courseid
            and ca.userid in ('".$studentids."')
            group by cm.instance
            ";
            if ($data = $DB->get_records_sql($sql)) {
                $cache->set($courseid, $data);
            }
        }
        return $data;
    }

    /**
     * Get the choice answers for a given course and user
     *
     * @param $courseid
     * @param $studentid
     * @return array
     * @throws dml_exception
     */
    protected function get_student_choice_answers($courseid, $studentid) {
        global $DB;

        // Check if $courseid is actually a course object and if so get the ID.
        if (is_object($courseid)) {
            $courseid = $courseid->id;
        }

        $cache = cache::make('format_qmultopics', 'student_choice_data');
        if (!get_config('format_qmultopics', 'useassignlabelcaches') || !$data = $cache->get($courseid)) {
            $sql = "
            select
            cm.instance as moduleid
            ,ca.userid as submitted
            ,ca.timemodified as submit_time
            from {course_modules} cm
            join {modules} m on m.id = cm.module
            join {choice} c on c.id = cm.instance and c.course = cm.course
            join {choice_answers} ca on ca.choiceid = c.id
            where m.name = 'choice'
            and cm.course = $courseid
            and ca.userid = $studentid
            ";
            if ($data = $DB->get_records_sql($sql)) {
                $cache->set($courseid, $data);
            }
        }
        return $data;
    }

    // Feedback.

    /**
     * Get information about all feedback assessments of a given course
     *
     * @param $courseid
     * @return array|bool|float|int|mixed|string
     * @throws coding_exception
     * @throws dml_exception
     */
    protected function get_feedback_data($courseid)
    {
        global $DB;

        // Check if $courseid is actually a course object and if so get the ID.
        if (is_object($courseid)) {
            $courseid = $courseid->id;
        }

        $cache = cache::make('format_qmultopics', 'feedback_data');
        if (!get_config('format_qmultopics', 'useassignlabelcaches') || !$data = $cache->get($courseid)) {
            $sql = "
            select
            cm.instance as moduleid
            ,f.timeclose as duedate
            from {course_modules} cm
            join {modules} m on m.id = cm.module
            join {feedback} f on f.id = cm.instance and f.course = cm.course
            where m.name = 'feedback'
            and cm.course = $courseid
            ";
            if ($data = $DB->get_records_sql($sql)) {
                $cache->set($courseid, $data);
            }
        }
        return $data;
    }

    /**
     * Get the number of completions by a given list of students for all feedback assessments of a given course
     *
     * @param $courseid
     * @param $studentids
     * @return array
     * @throws dml_exception
     */
    protected function get_feedback_completions($courseid, $studentids) {
        global $DB;

        // Check if $courseid is actually a course object and if so get the ID.
        if (is_object($courseid)) {
            $courseid = $courseid->id;
        }

        $cache = cache::make('format_qmultopics', 'admin_feedback_data');
        if (!get_config('format_qmultopics', 'useassignlabelcaches') || !$data = $cache->get($courseid)) {
            $sql = "
            select
            cm.instance as moduleid
            ,count(distinct fc.userid) as completed
            from {course_modules} cm
            join {modules} m on m.id = cm.module
            join {feedback} f on f.id = cm.instance and f.course = cm.course
            join {feedback_completed} fc on fc.feedback = f.id
            where m.name = 'feedback'
            and cm.course = $courseid
            and fc.userid in ('".$studentids."')
            group by cm.instance
            ";
            if ($data = $DB->get_records_sql($sql)) {
                $cache->set($courseid, $data);
            }
        }
        return $data;
    }

    /**
     * Get the completions for all feedbacks of a given student and course
     *
     * @param $courseid
     * @param $studentid
     * @return array
     * @throws dml_exception
     */
    protected function get_student_feedback_completions($courseid, $studentid) {
        global $DB;

        // Check if $courseid is actually a course object and if so get the ID.
        if (is_object($courseid)) {
            $courseid = $courseid->id;
        }

        $cache = cache::make('format_qmultopics', 'student_feedback_data');
        if (!get_config('format_qmultopics', 'useassignlabelcaches') || !$data = $cache->get($courseid)) {
            $sql = "
            select
            cm.instance as moduleid
            ,fc.userid as completed
            ,fc.timemodified as submit_time
            from {course_modules} cm
            join {modules} m on m.id = cm.module
            join {feedback} f on f.id = cm.instance and f.course = cm.course
            join {feedback_completed} fc on fc.feedback = f.id
            where m.name = 'feedback'
            and cm.course = $courseid
            and fc.userid = $studentid
            group by cm.instance
            ";
            if ($data = $DB->get_records_sql($sql)) {
                $cache->set($courseid, $data);
            }
        }
        return $data;
    }

    // Lesson.

    /**
     * Get information about all lesson assessments of a given course
     *
     * @param $courseid
     * @return array|bool|float|int|mixed|string
     * @throws coding_exception
     * @throws dml_exception
     */
    protected function get_lesson_data($courseid)
    {
        global $DB;

        // Check if $courseid is actually a course object and if so get the ID.
        if (is_object($courseid)) {
            $courseid = $courseid->id;
        }

        $cache = cache::make('format_qmultopics', 'lesson_data');
        if (!get_config('format_qmultopics', 'useassignlabelcaches') || !$data = $cache->get($courseid)) {
            $sql = "
            select
            cm.instance as moduleid
            ,l.deadline as duedate
            from {course_modules} cm
            join {modules} m on m.id = cm.module
            join {lesson} l on l.id = cm.instance and l.course = cm.course
            where m.name = 'lesson'
            and cm.course = $courseid
            ";
            if ($data = $DB->get_records_sql($sql)) {
                $cache->set($courseid, $data);
            }
        }
        return $data;
    }

    /**
     * Get the number of submissions of a list of given students for all lessons of a given course
     *
     * @param $courseid
     * @param $studentids
     * @return array
     * @throws dml_exception
     */
    protected function get_lesson_submissions($courseid, $studentids) {
        global $DB;

        // Check if $courseid is actually a course object and if so get the ID.
        if (is_object($courseid)) {
            $courseid = $courseid->id;
        }

        $cache = cache::make('format_qmultopics', 'admin_lesson_data');
        if (!get_config('format_qmultopics', 'useassignlabelcaches') || !$data = $cache->get($courseid)) {
            $sql = "
            select
            cm.instance as moduleid
            ,count(distinct la.userid) as submitted
            ,count(distinct lg.userid) as completed
            from {course_modules} cm
            join {modules} m on m.id = cm.module
            join {lesson} l on l.id = cm.instance and l.course = cm.course
            join {lesson_attempts} la on la.lessonid = l.id
            left join {lesson_grades} lg on lg.lessonid = la.lessonid and lg.userid = la.userid
            where m.name = 'lesson'
            and cm.course = $courseid
            and la.userid in ('".$studentids."')
            group by cm.instance
            ";
            if ($data = $DB->get_records_sql($sql)) {
                $cache->set($courseid, $data);
            }
        }
        return $data;
    }

    /**
     * Get the submissions for all lessons of a given course and student
     *
     * @param $courseid
     * @param $studentid
     * @return array
     * @throws dml_exception
     */
    protected function get_student_lesson_submissions($courseid, $studentid) {
        global $DB;

        // Check if $courseid is actually a course object and if so get the ID.
        if (is_object($courseid)) {
            $courseid = $courseid->id;
        }

        $cache = cache::make('format_qmultopics', 'student_lesson_data');
        if (!get_config('format_qmultopics', 'useassignlabelcaches') || !$data = $cache->get($courseid)) {
            $sql = "
            select
            uuid_short()
            ,cm.instance as moduleid
            ,la.userid as submitted
            ,la.timeseen as submit_time
            ,lg.grade as grade
            ,lg.completed as completed
           from {course_modules} cm
            join {modules} m on m.id = cm.module
            join {lesson} l on l.id = cm.instance and l.course = cm.course
            join {lesson_attempts} la on la.lessonid = l.id
            left join {lesson_grades} lg on lg.lessonid = la.lessonid and lg.userid = la.userid
            where m.name = 'lesson'
            and cm.course = $courseid
            and la.userid = $studentid
            ";
            if ($data = $DB->get_records_sql($sql)) {
                $cache->set($courseid, $data);
            }
        }
        return $data;
    }

    // Quiz.

    /**
     * Get information about all quiz assessments of a given course
     *
     * @param $courseid
     * @return array|bool|float|int|mixed|string
     * @throws coding_exception
     * @throws dml_exception
     */
    protected function get_quiz_data($courseid)
    {
        global $DB;

        // Check if $courseid is actually a course object and if so get the ID.
        if (is_object($courseid)) {
            $courseid = $courseid->id;
        }

        $cache = cache::make('format_qmultopics', 'quiz_data');
        if (!get_config('format_qmultopics', 'useassignlabelcaches') || !$data = $cache->get($courseid)) {
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
            and cm.course = $courseid
            ";
            if ($data = $DB->get_records_sql($sql)) {
                $cache->set($courseid, $data);
            }
        }
        return $data;
    }

    /**
     * Get the number of submissions of a list of given students for all quizzes of a given course
     *
     * @param $courseid
     * @param $studentids
     * @return array
     * @throws dml_exception
     */
    protected function get_quiz_submitted($courseid, $studentids) {
        global $DB;

        // Check if $courseid is actually a course object and if so get the ID.
        if (is_object($courseid)) {
            $courseid = $courseid->id;
        }

        $cache = cache::make('format_qmultopics', 'admin_quiz_data');
        if (!get_config('format_qmultopics', 'useassignlabelcaches') || !$data = $cache->get($courseid)) {
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
            and cm.course = $courseid
            and qa.userid in ('".$studentids."')
            group by cm.instance
            ";
            if ($data = $DB->get_records_sql($sql)) {
                $cache->set($courseid, $data);
            }
        }
        return $data;
    }

    /**
     * Get the submissions for all quizzes of a given course and student
     *
     * @param $courseid
     * @param $studentid
     * @return array
     * @throws dml_exception
     */
    protected function get_student_quiz_submitted($courseid, $studentid) {
        global $DB;

        // Check if $courseid is actually a course object and if so get the ID.
        if (is_object($courseid)) {
            $courseid = $courseid->id;
        }

        $cache = cache::make('format_qmultopics', 'student_quiz_data');
        if (!get_config('format_qmultopics', 'useassignlabelcaches') || !$data = $cache->get($courseid)) {
            $sql = "
            select
            uuid_short()
            ,cm.instance as moduleid
            ,qa.userid as submitted
            ,qa.state as state
            ,qa.timestart as timestart
            ,qa.timefinish as submit_time
            from {course_modules} cm
            join {modules} m on m.id = cm.module
            join {quiz} q on q.id = cm.instance and q.course = cm.course
            join {quiz_attempts} qa on qa.quiz = q.id
            where m.name = 'quiz'
            and cm.course = $courseid
            and qa.userid = $studentid
        ";
            if ($data = $DB->get_records_sql($sql)) {
                $cache->set($courseid, $data);
            }
        }
        return $data;
    }

}

