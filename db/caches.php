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
/**
 * Assignment label caches
 *
 * @package    format_qmultopics
 * @copyright  2021 Queen Mary University London / M.Opitz
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
$definitions = array(
    'assignment_data' => array(
        'mode' => cache_store::MODE_APPLICATION,
        'staticacceleration' => true,
        'ttl' => 300
    ),
    'choice_data' => array(
        'mode' => cache_store::MODE_APPLICATION,
        'staticacceleration' => true,
        'ttl' => 300
    ),
    'feedback_data' => array(
        'mode' => cache_store::MODE_APPLICATION,
        'staticacceleration' => true,
        'ttl' => 300
    ),
    'lesson_data' => array(
        'mode' => cache_store::MODE_APPLICATION,
        'staticacceleration' => true,
        'ttl' => 300
    ),
    'quiz_data' => array(
        'mode' => cache_store::MODE_APPLICATION,
        'staticacceleration' => true,
        'ttl' => 300
    ),
    'enrolled_users' => array(
        'mode' => cache_store::MODE_APPLICATION,
        'staticacceleration' => true,
        'ttl' => 300
    ),
    'admin_assignment_data' => array(
        'mode' => cache_store::MODE_SESSION,
        'staticacceleration' => true,
        'ttl' => 300
    ),
    'admin_group_assignment_data' => array(
        'mode' => cache_store::MODE_SESSION,
        'staticacceleration' => true,
        'ttl' => 300
    ),
    'admin_choice_data' => array(
        'mode' => cache_store::MODE_SESSION,
        'staticacceleration' => true,
        'ttl' => 300
    ),
    'admin_feedback_data' => array(
        'mode' => cache_store::MODE_SESSION,
        'staticacceleration' => true,
        'ttl' => 300
    ),
    'admin_lesson_data' => array(
        'mode' => cache_store::MODE_SESSION,
        'staticacceleration' => true,
        'ttl' => 300
    ),
    'admin_quiz_data' => array(
        'mode' => cache_store::MODE_SESSION,
        'staticacceleration' => true,
        'ttl' => 300
    ),
    'student_assignment_data' => array(
        'mode' => cache_store::MODE_SESSION,
        'ttl' => 300
    ),
    'student_group_assignment_data' => array(
        'mode' => cache_store::MODE_SESSION,
        'ttl' => 300
    ),
    'student_choice_data' => array(
        'mode' => cache_store::MODE_SESSION,
        'ttl' => 300
    ),
    'student_feedback_data' => array(
        'mode' => cache_store::MODE_SESSION,
        'ttl' => 300
    ),
    'student_lesson_data' => array(
        'mode' => cache_store::MODE_SESSION,
        'ttl' => 300
    ),
    'student_quiz_data' => array(
        'mode' => cache_store::MODE_SESSION,
        'ttl' => 300
    ),
);
