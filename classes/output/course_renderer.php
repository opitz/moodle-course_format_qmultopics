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
 *
 * @package format_qmultopics
 */
class qmultopics_course_renderer extends \core_course_renderer{

    /**
     * Renders HTML to display one course module in a course section
     *
     * This includes link, content, availability, completion info and additional information
     * that module type wants to display (i.e. number of unread forum posts)
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
        // hidden in a way that leaves no info, such as using the
        // eye icon.
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

        // Amending badges - but for courses with < 1000 students only.
        if (count($this->enrolled_users('')) < 1000) {
            $output .= html_writer::start_div();
            $output .= $this->show_badges($mod);
            $output .= html_writer::end_div();
        }

        $output .= html_writer::end_tag('div');

        // End of indentation div.
        $output .= html_writer::end_tag('div');

        $output .= html_writer::end_tag('div');
        return $output;
    }

    /**
     * Show a badge for the given module
     *
     * @param stdClass $mod
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
     * @param stdClass $duedate
     * @param date $cutoffdate
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

        $today = new DateTime(); // This object represents current date/time.
        $today->setTime( 0, 0, 0 ); // Reset time part, to prevent partial comparison.

        $matchdate = DateTime::createFromFormat( "Y.m.d\\TH:i", date("Y.m.d\\TH:i", $duedate ));
        $matchdate->setTime( 0, 0, 0 ); // Reset time part, to prevent partial comparison.

        $diff = $today->diff( $matchdate );
        $diffdays = (integer)$diff->format( "%R%a" ); // Extract days count in interval.

        switch( true ) {
            case $diffdays == 0:
                $badgeclass = ' badge-danger';
                if ($cutoffdate > 0 && $duedate < time()) {
                    $matchdate = DateTime::createFromFormat( "Y.m.d\\TH:i", date("Y.m.d\\TH:i", $cutoffdate ));
                    $matchdate->setTime( 0, 0, 0 ); // Reset time part, to prevent partial comparison.

                    $diff = $today->diff( $matchdate );
                    $diffdays = (integer)$diff->format( "%R%a" ); // Extract days count in interval.

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
                } else if ($duedate < time()) {
                    $dateformat = "%d %B %Y %H:%M:%S";
                    $duetext = get_string('badge_wasdue', 'format_qmultopics');
                } else {
                    $duetext = get_string('badge_duetoday', 'format_qmultopics');
                }
                break;
            case $diffdays < 0:
                if ($cutoffdate > 0) {
                    $matchdate = DateTime::createFromFormat( "Y.m.d\\TH:i", date("Y.m.d\\TH:i", $cutoffdate ));
                    $matchdate->setTime( 0, 0, 0 ); // Reset time part, to prevent partial comparison.

                    $diff = $today->diff( $matchdate );
                    $diffdays = (integer)$diff->format( "%R%a" ); // Extract days count in interval.

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
     * @param string $badgetext
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
     * @param stdClass $capability
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
     * @param stdClass $mod
     * @return string
     * @throws coding_exception
     * @throws dml_exception
     */
    public function show_assignment_badges($mod) {
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

    /**
     * Show badge with submissions and gradings for all students
     *
     * @param stdClass $mod
     * @return string
     * @throws coding_exception
     * @throws dml_exception
     */
    public function show_assign_submissions($mod) {
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

    /**
     * Show badge with submissions and gradings for all groups
     *
     * @param stdClass $mod
     * @return string
     * @throws coding_exception
     * @throws dml_exception
     */
    public function show_assign_group_submissions($mod) {
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

    /**
     * A badge to show the student as $USER his/her submission status
     * It will display the date of a submission, a mouseover will show the time for the submission
     *
     * @param stdClass $mod
     * @return string
     * @throws coding_exception
     */
    public function show_assign_submission($mod) {
        global $COURSE, $USER;
        $badgeclass = '';
        $badgetitle = '';
        $dateformat = "%d %B %Y";
        $timeformat = "%d %B %Y %H:%M:%S";

        $submission = false;
        foreach ($COURSE->module_data as $module) {
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
     * @param stdClass $mod
     * @return array
     */
    protected function get_grading($mod) {
        global $COURSE, $USER;

        if (isset($COURSE->module_data)) {
            foreach ($COURSE->module_data as $module) {
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
     * @param stdClass $mod
     * @return bool
     */
    protected function get_group_grading($mod) {
        global $COURSE, $USER;

        if (!isset($COURSE->group_assign_data)) {
            return false;
        }
        foreach ($COURSE->group_assign_data as $record) {
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
     * @param stdClass $mod
     * @return string
     * @throws coding_exception
     * @throws dml_exception
     */
    public function show_choice_badge($mod) {
        global $COURSE;

        $o = '';
        if (isset($COURSE->module_data)) {
            foreach ($COURSE->module_data as $module) {
                // If the choice has a due date show it.
                if ($module->module_name == 'choice' && $module->choice_id == $mod->instance && $module->choice_duedate > 0) {
                    $o .= $this->show_due_date_badge($module->choice_duedate);
                    break;
                }
            }
        }

        // Check if the user is able to grade (e.g. is a teacher).
        if (has_capability('mod/assign:grade', $mod->context)) {
            // Show submission numbers and ungraded submissions if any.
            $o .= $this->show_choice_answers($mod);
        } else {
            // Show date of submission.
            $o .= $this->show_choice_answer($mod);
        }

        return $o;
    }

    /**
     * Show badge with choice answers of all students
     *
     * @param stdClass $mod
     * @return string
     * @throws coding_exception
     * @throws dml_exception
     */
    public function show_choice_answers($mod) {
        global $COURSE;

        // Show answers by enrolled students.
        $badgetext = '';
        $badgeclass = '';
        $capability = 'choice';
        $pretext = '';
        $xofy = ' of ';
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

    /**
     * Show choice answer for current student as $USER
     *
     * @param stdClass $mod
     * @return string
     * @throws coding_exception
     */
    public function show_choice_answer($mod) {
        global $COURSE, $DB, $USER;
        $badgeclass = '';
        $dateformat = "%d %B %Y";

        $submittime = false;
        if (isset($COURSE->module_data)) {
            foreach ($COURSE->module_data as $module) {
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
     * @param stdClass $mod
     * @return string
     * @throws coding_exception
     * @throws dml_exception
     */
    public function show_feedback_badge($mod) {
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

    /**
     * Show badge with feedback completions of all students
     *
     * @param stdClass $mod
     * @return string
     * @throws coding_exception
     * @throws dml_exception
     */
    public function show_feedback_completions($mod) {
        global $COURSE;

        // Show answers by enrolled students.
        $badgetext = '';
        $badgeclass = '';
        $capability = 'feedback';
        $pretext = '';
        $xofy = ' of ';
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

    /**
     * Show feedback by current student as $USER
     *
     * @param stdClass $mod
     * @return string
     * @throws coding_exception
     */
    public function show_feedback_completion($mod) {
        global $COURSE, $USER;
        $badgeclass = '';
        $dateformat = "%d %B %Y";
        $submission = false;
        if (isset($COURSE->module_data)) {
            foreach ($COURSE->module_data as $module) {
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
     * @param stdClass $mod
     * @return string
     * @throws coding_exception
     * @throws dml_exception
     */
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
     * @param stdClass $mod
     * @return string
     * @throws coding_exception
     * @throws dml_exception
     */
    public function show_lesson_attempts($mod) {
        global $COURSE;

        // Show answers by enrolled students.
        $spacer = get_string('badge_commaspacer', 'format_qmultopics');
        $badgetext = '';
        $badgeclass = '';
        $capability = 'lesson';
        $pretext = '';
        $xofy = ' of ';
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

    /**
     * Show lesson attempt for the current student as $USER
     *
     * @param stdClass $mod
     * @return string
     * @throws coding_exception
     */
    public function show_lesson_attempt($mod) {
        global $COURSE, $USER;
        $badgeclass = '';
        $dateformat = "%d %B %Y";
        $submission = false;
        if (isset($COURSE->module_data)) {
            foreach ($COURSE->module_data as $module) {
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
     * @param stdClass $mod
     * @return string
     * @throws coding_exception
     * @throws dml_exception
     */
    public function show_quiz_badge($mod) {
        global $COURSE;
        $o = '';

        if (isset($COURSE->module_data)) {
            foreach ($COURSE->module_data as $module) {
                // If the quiz has a due date show it.
                if ($module->quiz_id == $mod->instance && $module->quiz_duedate > 0) {
                    $o .= $this->show_due_date_badge($module->quiz_duedate);
                    break;
                }
            }
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
     * @param stdClass $mod
     * @return string
     * @throws coding_exception
     * @throws dml_exception
     */
    public function show_quiz_attempts($mod) {
        global $COURSE;

        // Show attempts by enrolled students.
        $badgetext = '';
        $badgeclass = '';
        $capability = 'quiz';
        $pretext = '';
        $xofy = get_string('badge_xofy', 'format_qmultopics');
        $posttext = get_string('badge_attempted', 'format_qmultopics');
        $enrolledstudents = $this->enrolled_users($capability);

        if ($enrolledstudents) {
            $submissions = [];
            $finished = [];
            if (isset($COURSE->module_data)) {
                foreach ($COURSE->module_data as $module) {
                    if ($module->module_name == 'quiz' && $module->quiz_id == $mod->instance && $module->quiz_userid != null) {
                        $submissions[$module->quiz_userid] = 1;
                        if ($module->quiz_state == 'finished') {
                            $finished[$module->quiz_userid] = 1;
                        }
                    }
                }
            }
            $badgetext = $pretext
                .count($submissions)
                .$xofy
                .count($enrolledstudents)
                .$posttext
                .($submissions > 0 ? ', '.count($finished).get_string('badge_finished', 'format_qmultopics') : '');
            ;

        }
        if ($badgetext) {
            return $this->html_badge($badgetext, $badgeclass);
        } else {
            return '';
        }
    }

    /**
     * Show quiz attempts for the current student as $USER
     *
     * @param stdClass $mod
     * @return string
     * @throws coding_exception
     */
    public function show_quiz_attempt($mod) {
        global $COURSE, $DB, $USER;
        $o = '';
        $badgeclass = '';
        $dateformat = "%d %B %Y";

        $submissions = [];
        if (isset($COURSE->module_data)) {
            foreach ($COURSE->module_data as $module) {
                if ($module->module_name == 'quiz' && $module->quiz_id == $mod->instance && $module->quiz_userid == $USER->id) {
                    $submissions[] = $module;
                }
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
        return $o;
    }

}

