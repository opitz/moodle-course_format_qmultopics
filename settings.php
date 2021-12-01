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
 * Settings for format_qmultopics
 *
 * @package    format_qmultopics
 * @copyright  2020 Matthias Opitz
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    /* Format assignemnt badges - 0 = no, 1 = yes. */
    $name = 'format_qmultopics/useassignlabels';
    $title = get_string('useassignlabels', 'format_qmultopics');
    $description = get_string('useassignlabels_desc', 'format_qmultopics');
    $default = 0;
    $choices = array(
        0 => new lang_string('no'), // No.
        1 => new lang_string('yes')   // Yes.
    );
    $settings->add(new admin_setting_configselect($name, $title, $description, $default, $choices));

    if (get_config('format_qmultopics', 'useassignlabels')) {
        /* Format assignemnt badges use caches - 0 = no, 1 = yes. */
        $name = 'format_qmultopics/useassignlabelcaches';
        $title = get_string('useassignlabelcaches', 'format_qmultopics');
        $description = get_string('useassignlabelcaches_desc', 'format_qmultopics');
        $default = 1;
        $choices = array(
            0 => new lang_string('no'), // No.
            1 => new lang_string('yes')   // Yes.
        );
        $settings->add(new admin_setting_configselect($name, $title, $description, $default, $choices));
    }
}
