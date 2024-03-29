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
 * This file contains main class for the course format QMULtopics
 *
 * @since     Moodle 2.0
 * @package   format_qmultopics
 * @copyright 2020 Matthias Opitz
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot. '/course/format/lib.php');
require_once($CFG->dirroot. '/course/format/topics2/lib.php');

/**
 * Main class for the Topics (QMUL) course format
 */
class format_qmultopics extends format_topics2 {

    /**
     * Adds format options elements to the course/section edit form
     *
     * @param MoodleQuickForm $mform
     * @param bool $forsection
     * @return array
     * @throws coding_exception
     *
     */
    public function create_edit_form_elements(&$mform, $forsection = false) {
        global $CFG, $OUTPUT;
        $elements = parent::create_edit_form_elements($mform, $forsection);
        if ($forsection == false) {
            $fo = $this->get_format_options();
            // Assessment Information.
            if (isset($fo['enable_assessmentinformation']) && $fo['enable_assessmentinformation'] == "1") {
                $elements[] = $mform->addElement('header', 'assessmentinformation',
                    get_string('assessmentinformation', 'format_qmultopics'));
                $mform->addHelpButton('assessmentinformation', 'assessmentinformation',
                    'format_qmultopics', '', true);
                $elements[] = $mform->addElement('checkbox', 'enable_assessmentinformation',
                    get_string('enabletab', 'format_qmultopics'));
                $elements[] = $mform->addElement('editor', 'content_assessmentinformation',
                    get_string('assessmentinformation', 'format_qmultopics'));
            }

            // Extra Tab 1.
            if (isset($fo['enable_extratab1']) && $fo['enable_extratab1'] == "1") {
                $elements[] = $mform->addElement('header', 'extratab1',
                    get_string('extratab', 'format_qmultopics', 1));
                $mform->addHelpButton('extratab1', 'extratab',
                    'format_qmultopics', '', true);
                $elements[] = $mform->addElement('checkbox', 'enable_extratab1',
                    get_string('enabletab', 'format_qmultopics'));
                $elements[] = $mform->addElement('text', 'title_extratab1',
                    get_string('tabtitle', 'format_qmultopics'));
                $elements[] = $mform->addElement('editor', 'content_extratab1',
                    get_string('tabcontent', 'format_qmultopics'));
            }

            // Extra Tab 2.
            if (isset($fo['enable_extratab2']) && $fo['enable_extratab2'] == "1") {
                $elements[] = $mform->addElement('header', 'extratab2',
                    get_string('extratab', 'format_qmultopics', 2));
                $mform->addHelpButton('extratab2', 'extratab',
                    'format_qmultopics', '', true);
                $elements[] = $mform->addElement('checkbox', 'enable_extratab2',
                    get_string('enabletab', 'format_qmultopics'));
                $elements[] = $mform->addElement('text', 'title_extratab2',
                    get_string('tabtitle', 'format_qmultopics'));
                $elements[] = $mform->addElement('editor', 'content_extratab2',
                    get_string('tabcontent', 'format_qmultopics'));
            }

            // Extra Tab 3.
            if (isset($fo['enable_extratab3']) && $fo['enable_extratab3'] == "1") {
                $elements[] = $mform->addElement('header', 'extratab3',
                    get_string('extratab', 'format_qmultopics', 3));
                $mform->addHelpButton('extratab3', 'extratab',
                    'format_qmultopics', '', true);
                $elements[] = $mform->addElement('checkbox', 'enable_extratab3',
                    get_string('enabletab', 'format_qmultopics'));
                $elements[] = $mform->addElement('text', 'title_extratab3',
                    get_string('tabtitle', 'format_qmultopics'));
                $elements[] = $mform->addElement('editor', 'content_extratab3',
                    get_string('tabcontent', 'format_qmultopics'));
            }
        }

        return $elements;
    }

    /**
     * Validate the form edit
     *
     * @param array $data
     * @param array $files
     * @param array $errors
     * @return array
     * @throws coding_exception
     */
    public function edit_form_validation($data, $files, $errors) {
        $return = parent::edit_form_validation($data, $files, $errors);

        if (isset($data['enable_extratab1'])) {
            if (empty($data['title_extratab1'])) {
                $return['title_extratab1'] = get_string('titlerequiredwhenenabled', 'format_qmultopics');
            }
        } else {
            $data['enabled_extratab1'] = 0;
        }
        if (isset($data['enable_extratab2'])) {
            if (empty($data['title_extratab2'])) {
                $return['title_extratab2'] = get_string('titlerequiredwhenenabled', 'format_qmultopics');
            }
        } else {
            $data['enabled_extratab1'] = 0;
        }
        if (isset($data['enable_extratab3'])) {
            if (empty($data['title_extratab3'])) {
                $return['title_extratab3'] = get_string('titlerequiredwhenenabled', 'format_qmultopics');
            }
        } else {
            $data['enabled_extratab1'] = 0;
        }
        return $return;
    }

    /**
     * Get the course format options
     *
     * @param bool $foreditform
     * @return array|bool
     * @throws coding_exception
     * @throws dml_exception
     */
    public function course_format_options($foreditform = false) {
        global $CFG, $COURSE, $DB;

        $fo = $DB->get_records('course_format_options', array('courseid' => $COURSE->id));
        $formatoptions = array();
        foreach ($fo as $o) {
            $formatoptions[$o->name] = $o->value;
        }

        // Check for legacy 'toggle' format_option and change 'coursedisplay' accordingly where needed.
        if (isset($formatoptions['toggle']) && $formatoptions['toggle'] && $formatoptions['coursedisplay'] ==
            COURSE_DISPLAY_SINGLEPAGE) {
            $rec = $DB->get_record('course_format_options', array('courseid' => $COURSE->id, 'name' => 'coursedisplay'));
            $rec->value = COURSE_DISPLAY_SINGLEPAGE;
            $DB->update_record('course_format_options', $rec);
        }

        $maxtabs = ((isset($formatoptions['maxtabs']) && $formatoptions['maxtabs'] > 0) ?
            $formatoptions['maxtabs'] : (isset($CFG->max_tabs) ? $CFG->max_tabs : 9));
        static $courseformatoptions = false;
        if ($courseformatoptions === false) {
            $courseconfig = get_config('moodlecourse');
            $courseformatoptions = array(
                'maxtabs' => array(
                    'label' => get_string('maxtabs_label', 'format_topics2'),
                    'help' => 'maxtabs',
                    'help_component' => 'format_topics2',
                    'default' => (isset($CFG->max_tabs) ? $CFG->max_tabs : 5),
                    'type' => PARAM_INT,
                ),
                'limittabname' => array(
                    'label' => get_string('limittabname_label', 'format_topics2'),
                    'help' => 'limittabname',
                    'help_component' => 'format_topics2',
                    'default' => 0,
                    'type' => PARAM_INT,
                ),
                'hiddensections' => array(
                    'label' => new lang_string('hiddensections'),
                    'help' => 'hiddensections',
                    'help_component' => 'moodle',
                    'element_type' => 'select',
                    'element_attributes' => array(
                        array(
                            0 => new lang_string('hiddensectionscollapsed'),
                            1 => new lang_string('hiddensectionsinvisible')
                        )
                    ),
                ),
                'coursedisplay' => array(
                    'label' => new lang_string('coursedisplay'),
                    'element_type' => 'select',
                    'element_attributes' => array(
                        array(
                            COURSE_DISPLAY_SINGLEPAGE => new lang_string('coursedisplay_single'),
                            COURSE_DISPLAY_MULTIPAGE => new lang_string('coursedisplay_multi')
                        )
                    ),
                    'help' => 'coursedisplay',
                    'help_component' => 'moodle',
                ),
                'defaultcollapse' => array(
                    'label' => get_string('defaultcollapse', 'format_qmultopics'),
                    'element_type' => 'select',
                    'element_attributes' => array(
                        array(
                            0 => get_string('defaultcollapsed', 'format_qmultopics'),
                            1 => get_string('defaultexpanded', 'format_qmultopics'),
                            2 => get_string('alwaysexpanded', 'format_topics2')
                        )
                    ),
                    'help' => 'defaultcollapse',
                    'help_component' => 'format_qmultopics',
                ),
                'section0_ontop' => array(
                    'label' => get_string('section0_label', 'format_topics2'),
                    'element_type' => 'advcheckbox',
                    'default' => 0,
                    'help' => 'section0',
                    'help_component' => 'format_topics2',
                    'element_type' => 'hidden',
                ),
                'single_section_tabs' => array(
                    'label' => get_string('single_section_tabs_label', 'format_topics2'),
                    'element_type' => 'advcheckbox',
                    'help' => 'single_section_tabs',
                    'help_component' => 'format_topics2',
                ),
                'assessment_info_block_tab' => array(
                    'default' => get_config('format_qmultopics', 'defaultshowassessmentinfotab'),
                    'label' => get_string('assessment_info_block_tab_label', 'format_qmultopics'),
                    'element_type' => 'hidden',
                    'element_attributes' => array(
                        array(
                            0 => get_string('assessment_info_block_tab_option0', 'format_qmultopics'),
                            1 => get_string('assessment_info_block_tab_option1', 'format_qmultopics'),
                            2 => get_string('assessment_info_block_tab_option2', 'format_qmultopics')
                        )
                    ),
                    'help' => 'assessment_info_block_tab',
                    'help_component' => 'format_qmultopics',
                ),

            );

            // The sequence in which the tabs will be displayed.
            $courseformatoptions['tab_seq'] = array(
                'default' => '',
                'type' => PARAM_TEXT,
                'label' => '',
                'element_type' => 'hidden'
            );

            // Now loop through the tabs but don't show them as we only need the DB records...
            $courseformatoptions['tab0_title'] = array(
                'default' => get_string('tabzero_title', 'format_topics2'),
                'type' => PARAM_TEXT,
                'label' => '',
                'element_type' => 'hidden'
            );
            $courseformatoptions['tab0'] = array(
                'default' => "",
                'type' => PARAM_TEXT,
                'label' => '',
                'element_type' => 'hidden'
            );
            for ($i = 1; $i <= $maxtabs; $i++) {
                $courseformatoptions['tab'.$i.'_title'] = array(
                    'default' => "Tab ".$i,
                    'type' => PARAM_TEXT,
                    'label' => '',
                    'element_type' => 'hidden'
                );
                $courseformatoptions['tab'.$i] = array(
                    'default' => "",
                    'type' => PARAM_TEXT,
                    'label' => '',
                    'element_type' => 'hidden'
                );
                $courseformatoptions['tab'.$i.'_sectionnums'] = array(
                    'default' => "",
                    'type' => PARAM_TEXT,
                    'label' => '',
                    'element_type' => 'hidden'
                );
            }
        }
        // Allow to store a name for the Assessment Info tab.
        $courseformatoptions['tab_assessment_information_title'] = array(
            'default' => get_string('tab_assessment_information_title', 'format_qmultopics'),
            'type' => PARAM_TEXT,
            'label' => '',
            'element_type' => 'hidden'
        );

        // Allow to store a name for the Assessment Info Block tab.
        $courseformatoptions['tab_assessment_info_block_title'] = array(
            'default' => get_string('tab_assessment_info_block_title', 'format_qmultopics'),
            'type' => PARAM_TEXT,
            'label' => '',
            'element_type' => 'hidden'
        );

        return $courseformatoptions;
    }

    /**
     * Updates format options for a course
     *
     * @param array|stdClass $data
     * @param null $oldcourse
     * @return bool
     * @throws dml_exception
     */
    public function update_course_format_options($data, $oldcourse = null) {
        global $DB;

        $newdata = (array) $data;
        $savedata = array();
        if (isset($newdata['fullname'])) {
            if (isset($newdata['enable_assessmentinformation'])) {
                $savedata['enable_assessmentinformation'] = $newdata['enable_assessmentinformation'];
            } else {
                $savedata['enable_assessmentinformation'] = 0;
            }
            if (isset($newdata['content_assessmentinformation'])) {
                $savedata['content_assessmentinformation'] = $newdata['content_assessmentinformation'];
            }
            if (isset($newdata['enable_extratab1'])) {
                $savedata['enable_extratab1'] = $newdata['enable_extratab1'];
            } else {
                $savedata['enable_extratab1'] = 0;
            }
            if (isset($newdata['title_extratab1'])) {
                $savedata['title_extratab1'] = $newdata['title_extratab1'];
            }
            if (isset($newdata['content_extratab1'])) {
                $savedata['content_extratab1'] = $newdata['content_extratab1'];
            }
            if (isset($newdata['enable_extratab2'])) {
                $savedata['enable_extratab2'] = $newdata['enable_extratab2'];
            } else {
                $savedata['enable_extratab2'] = 0;
            }
            if (isset($newdata['title_extratab2'])) {
                $savedata['title_extratab2'] = $newdata['title_extratab2'];
            }
            if (isset($newdata['content_extratab2'])) {
                $savedata['content_extratab2'] = $newdata['content_extratab2'];
            }
            if (isset($newdata['enable_extratab3'])) {
                $savedata['enable_extratab3'] = $newdata['enable_extratab3'];
            } else {
                $savedata['enable_extratab3'] = 0;
            }
            if (isset($newdata['title_extratab3'])) {
                $savedata['title_extratab3'] = $newdata['title_extratab3'];
            }
            if (isset($newdata['content_extratab3'])) {
                $savedata['content_extratab3'] = $newdata['content_extratab3'];
            }
        }

        $records = $DB->get_records('course_format_options',
            array('courseid' => $this->courseid,
                'format' => $this->format,
                'sectionid' => 0
            ), '', 'name,id,value');

        foreach ($savedata as $key => $value) {
            // From 3.6 on HTML editor will return an array - if so just get the txt to store.
            if (gettype($value) == 'array' && isset($value['text'])) {
                $value = $value['text'];
            }
            if (isset($records[$key])) {
                if (array_key_exists($key, $newdata) && $records[$key]->value !== $newdata[$key]) {
                    $DB->set_field('course_format_options', 'value',
                        $value, array('id' => $records[$key]->id));
                    $changed = true;
                } else {
                    $DB->set_field('course_format_options', 'value',
                        $value, array('id' => $records[$key]->id));
                    $changed = true;
                }
            } else {
                $DB->insert_record('course_format_options', (object) array(
                    'courseid' => $this->courseid,
                    'format' => $this->format,
                    'sectionid' => 0,
                    'name' => $key,
                    'value' => $value
                ));
            }
        }

        $changes = parent::update_course_format_options($data, $oldcourse);

        return $changes;
    }

    /**
     * Returns the format options stored for this course or course section
     *
     * When overriding please note that this function is called from rebuild_course_cache()
     * and section_info object, therefore using of get_fast_modinfo() and/or any function that
     * accesses it may lead to recursion.
     *
     * @param null|int|stdClass|section_info $section if null the course format options will be returned
     *     otherwise options for specified section will be returned. This can be either
     *     section object or relative section number (field course_sections.section)
     * @return array
     */
    public function get_format_options($section = null) {
        global $DB;

        $options = parent::get_format_options($section);

        if ($section === null) {
            // Course format options will be returned.
            $sectionid = 0;
        } else if ($this->courseid && isset($section->id)) {
            // Course section format options will be returned.
            $sectionid = $section->id;
        } else if ($this->courseid && is_int($section) &&
            ($sectionobj = $DB->get_record('course_sections',
                array('section' => $section, 'course' => $this->courseid), 'id'))) {
            // Course section format options will be returned.
            $sectionid = $sectionobj->id;
        } else {
            // Non-existing (yet) section was passed as an argument.
            // Default format options for course section will be returned.
            $sectionid = -1;
        }

        if ($sectionid == 0) {
            $alloptions = $DB->get_records('course_format_options',
                array('courseid' => $this->courseid, 'format' => $this->format, 'sectionid' => 0));
            foreach ($alloptions as $option) {
                if (!isset($options[$option->name])) {
                    $options[$option->name] = $option->value;
                }
            }
            $this->formatoptions[$sectionid] = $options;
        }
        return $options;
    }
}

/**
 * Implements callback inplace_editable() allowing to edit values in-place
 *
 * @param string $itemtype
 * @param int $itemid
 * @param mixed $newvalue
 * @return \core\output\inplace_editable
 */
function format_qmultopics_inplace_editable($itemtype, $itemid, $newvalue) {
    global $DB, $CFG;
    require_once($CFG->dirroot . '/course/lib.php');
    if ($itemtype === 'sectionname' || $itemtype === 'sectionnamenl') {
        $section = $DB->get_record_sql(
            'SELECT s.* FROM {course_sections} s JOIN {course} c ON s.course = c.id WHERE s.id = ? AND c.format = ?',
            array($itemid, 'qmultopics'), MUST_EXIST);
        return course_get_format($section->course)->inplace_editable_update_section_name($section, $itemtype, $newvalue);
    }
    // Deal with inplace changes of a tab name.
    if ($itemtype === 'tabname') {
        global $DB, $PAGE;
        $courseid = key($_SESSION['USER']->currentcourseaccess);
        // The $itemid is actually the name of the record so use it to get the id.

        // Update the database with the new value given.
        // Must call validate_context for either system, or course or course module context.
        // This will both check access and set current context.
        \external_api::validate_context(context_system::instance());

        // Clean input and update the record.
        $newvalue = clean_param($newvalue, PARAM_NOTAGS);
        $record = $DB->get_record('course_format_options', array('id' => $itemid), '*', MUST_EXIST);
        $DB->update_record('course_format_options', array('id' => $record->id, 'value' => $newvalue));

        // Prepare the element for the output ().
        $output = new \core\output\inplace_editable('format_qmultopics', 'tabname', $record->id,
            true,
            format_string($newvalue), $newvalue, 'Edit tab name',  'New value for ' . format_string($newvalue));

        return $output;
    }
}
