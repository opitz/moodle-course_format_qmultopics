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
 * Generator for QMULtopics course format.
 *
 * @package    format_qmultopics
 * @category   test
 * @copyright  2021 Queen Mary University London
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


/**
 * Generator for QMULtopics course format.
 *
 * @package    format_qmultopics
 * @category   test
 * @copyright  2021 Queen Mary University London
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class format_qmultopics_generator extends component_generator_base {
    public function create_config_plugins($pluginConfig) {
        global $DB;
        $DB->insert_record("config_plugins", $pluginConfig);
    }
}
