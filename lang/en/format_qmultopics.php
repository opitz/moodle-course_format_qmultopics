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
 * QMUL Topics Information
 *
 * @package    format_qmultopics
 * @copyright  2020 Matthias Opitz m.opitz@qmul.ac.uk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 *
 */
// Used by the Moodle Core for identifing the format and displaying in the list of formats for a course in its settings.
// Possibly legacy to be removed after Moodle 2.0 is stable.
$string['nameqmultopics'] = 'QMUL Topics';
$string['formatqmultopics'] = 'QMUL Topics';

// Used in format.php.
$string['qmultopicstoggle'] = 'Toggle';
$string['qmultopicssidewidthlang'] = 'en-28px';

// Toggle all - Moodle Tracker CONTRIB-3190.
$string['qmultopicsall'] = 'sections.';  // Leave as AMOS maintains only the latest translation - so previous versions are still supported.
$string['qmultopicsopened'] = 'Open all';
$string['qmultopicsclosed'] = 'Close all';

// Moodle 2.0 Enhancement - Moodle Tracker MDL-15252, MDL-21693 & MDL-22056 - http://docs.moodle.org/en/Development:Languages.
$string['sectionname'] = 'Section';
$string['pluginname'] = 'Topics (QMUL)';
$string['section0name'] = 'General';

// MDL-26105.
$string['page-course-view-qmultopics'] = 'Any course main page in the topics format';
$string['page-course-view-qmultopics-x'] = 'Any course page in the topics format';

// Moodle 2.3 Enhancement.
$string['hidefromothers'] = 'Hide section';
$string['showfromothers'] = 'Show section';
$string['currentsection'] = 'This section';
$string['editsection'] = 'Edit section';
$string['deletesection'] = 'Delete section';
// These are 'sections' as they are only shown in 'section' based structures.
$string['markedthissection'] = 'This section is highlighted as the current section';
$string['markthissection'] = 'Highlight this section as the current section';

// MDL-51802.
$string['editsectionname'] = 'Edit section name';
$string['newsectionname'] = 'New name for section {$a}';

// Reset.
$string['resetgrp'] = 'Reset:';
$string['resetallgrp'] = 'Reset all:';

// Layout enhancement - Moodle Tracker CONTRIB-3378.
$string['formatsettings'] = 'Format reset settings'; // CONTRIB-3529.
$string['formatsettingsinformation'] = '<br />To reset the settings of the course format to the defaults, click on the icon to the right.';
$string['setlayout'] = 'Set layout';

// Negative view of layout, kept for previous versions until such time as they are updated.
$string['setlayout_default'] = 'Default';
$string['setlayout_no_toggle_section_x'] = 'No toggle section x';
$string['setlayout_no_section_no'] = 'No section number';
$string['setlayout_no_toggle_section_x_section_no'] = 'No toggle section x and section number';
$string['setlayout_no_toggle_word'] = 'No toggle word';
$string['setlayout_no_toggle_word_toggle_section_x'] = 'No toggle word and toggle section x';
$string['setlayout_no_toggle_word_toggle_section_x_section_no'] = 'No toggle word, toggle section x and section number';

// Positive view of layout.
$string['setlayout_all'] = "Toggle word, 'Topic x' / 'Week x' / 'Day x' and section number";
$string['setlayout_toggle_word_section_number'] = 'Toggle word and section number';
$string['setlayout_toggle_word_section_x'] = "Toggle word and 'Topic x' / 'Week x' / 'Day x'";
$string['setlayout_toggle_word'] = 'Toggle word';
$string['setlayout_toggle_section_x_section_number'] = "'Topic x' / 'Week x' / 'Day x' and section number";
$string['setlayout_section_number'] = 'Section number';
$string['setlayout_no_additions'] = 'No additions';
$string['setlayout_toggle_section_x'] = "'Topic x' / 'Week x' / 'Day x'";

$string['setlayoutelements'] = 'Elements';
$string['setlayoutstructure'] = 'Structure';
$string['setlayoutstructuretopic'] = 'Topic';
$string['setlayoutstructureweek'] = 'Week';
$string['setlayoutstructurelatweekfirst'] = 'Current week first';
$string['setlayoutstructurecurrenttopicfirst'] = 'Current topic first';
$string['setlayoutstructureday'] = 'Day';
$string['resetlayout'] = 'Layout'; // CONTRIB-3529.
$string['resetalllayout'] = 'Layouts';

// Colour enhancement - Moodle Tracker CONTRIB-3529.
$string['setcolour'] = 'Colour';
$string['colourrule'] = "Please enter a valid RGB colour, six hexadecimal digits.";
$string['settoggleforegroundcolour'] = 'Toggle foreground';
$string['settoggleforegroundhovercolour'] = 'Toggle foreground hover';
$string['settogglebackgroundcolour'] = 'Toggle background';
$string['settogglebackgroundhovercolour'] = 'Toggle background hover';
$string['resetcolour'] = 'Colour';
$string['resetallcolour'] = 'Colours';

// Columns enhancement.
$string['setlayoutcolumns'] = 'Columns';
$string['one'] = 'One';
$string['two'] = 'Two';
$string['three'] = 'Three';
$string['four'] = 'Four';
$string['setlayoutcolumnorientation'] = 'Column orientation';
$string['columnvertical'] = 'Vertical';
$string['columnhorizontal'] = 'Horizontal';

// MDL-34917 - implemented in M2.5 but needs to be here to support M2.4- versions.
$string['maincoursepage'] = 'Main course page';

// Help.
$string['setlayoutelements_help'] = 'How much information about the toggles / sections you wish to be displayed.';
$string['setlayoutstructure_help'] = "The layout structure of the course.  You can choose between:<br />'Topics' - where each section is presented as a topic in section number order.<br />'Weeks' - where each section is presented as a week in ascending week order from the start date of the course.<br />'Current week first' - which is the same as weeks but the current week is shown at the top and preceding weeks in descending order are displayed below except in editing mode where the structure is the same as 'Weeks'.<br />'Current topic first' - which is the same as 'Topics' except that the current topic is shown at the top if it has been set.<br />'Day' - where each section is presented as a day in ascending day order from the start date of the course.";
$string['setlayout_help'] = 'Contains the settings to do with the layout of the format within the course.';
$string['resetlayout_help'] = 'Resets the layout element, structure, columns, icon position and shown section summary to the default values so it will be the same as a course the first time it is in the \'Collapsed Topics\' format.';
$string['resetalllayout_help'] = 'Resets the layout to the default values for all courses so it will be the same as a course the first time it is in the \'Collapsed Topics \'format.';

// Moodle Tracker CONTRIB-3529.
$string['setcolour_help'] = 'Contains the settings to do with the colour of the format within the course.';
$string['settoggleforegroundcolour_help'] = 'Sets the colour of the text on the toggle.';
$string['settoggleforegroundhovercolour_help'] = 'Sets the colour of the text on the toggle when the mouse moves over it.';
$string['settogglebackgroundcolour_help'] = 'Sets the background colour of the toggle.';
$string['settogglebackgroundhovercolour_help'] = 'Sets the background colour of the toggle when the mouse moves over it.';
$string['resetcolour_help'] = 'Resets the colours to the default values so it will be the same as a course the first time it is in the \'Collapsed Topics\' format.';
$string['resetallcolour_help'] = 'Resets the colours to the default values for all courses so it will be the same as a course the first time it is in the \'Collapsed Topics\' format.';

// Columns enhancement.
$string['setlayoutcolumns_help'] = 'How many columns to use.';
$string['setlayoutcolumnorientation_help'] = 'Vertical - Sections go top to bottom.<br />Horizontal - Sections go left to right.';

// Moodle 2.4 Course format refactoring - MDL-35218.
$string['numbersections'] = 'Number of sections';
$string['ctreset'] = 'Collapsed Topics reset options';
$string['ctreset_help'] = 'Reset to Collapsed Topics defaults.';

// Toggle alignment - CONTRIB-4098.
$string['settogglealignment'] = 'Toggle text alignment';
$string['settogglealignment_help'] = 'Sets the alignment of the text in the toggle.';
$string['left'] = 'Left';
$string['center'] = 'Centre';
$string['right'] = 'Right';
$string['resettogglealignment'] = 'Toggle alignment';
$string['resetalltogglealignment'] = 'Toggle alignments';
$string['resettogglealignment_help'] = 'Resets the toggle alignment to the default values so it will be the same as a course the first time it is in the \'Collapsed Topics\' format.';
$string['resetalltogglealignment_help'] = 'Resets the toggle alignment to the default values for all courses so it will be the same as a course the first time it is in the \'Collapsed Topics\' format.';

// Icon position - CONTRIB-4470.
$string['settoggleiconposition'] = 'Icon position';
$string['settoggleiconposition_help'] = 'States that the icon should be on the left or the right of the toggle text.';
$string['defaulttoggleiconposition'] = 'Icon position';
$string['defaulttoggleiconposition_desc'] = 'States if the icon should be on the left or the right of the toggle text.';

// Icon set enhancement.
$string['settoggleiconset'] = 'Icon set';
$string['settoggleiconset_help'] = 'Sets the icon set of the toggle.';
$string['settoggleallhover'] = 'Toggle all icon hover';
$string['settoggleallhover_help'] = 'Sets if the toggle all icons will change when the mouse moves over them.';
$string['arrow'] = 'Arrow';
$string['bulb'] = 'Bulb';
$string['cloud'] = 'Cloud';
$string['eye'] = 'Eye';
$string['groundsignal'] = 'Ground signal';
$string['led'] = 'Light emitting diode';
$string['point'] = 'Point';
$string['power'] = 'Power';
$string['radio'] = 'Radio';
$string['smiley'] = 'Smiley';
$string['square'] = 'Square';
$string['sunmoon'] = 'Sun / Moon';
$string['switch'] = 'Switch';
$string['resettoggleiconset'] = 'Toggle icon set';
$string['resetalltoggleiconset'] = 'Toggle icon sets';
$string['resettoggleiconset_help'] = 'Resets the toggle icon set and toggle all hover to the default values so it will be the same as a course the first time it is in the \'Collapsed Topics\' format.';
$string['resetalltoggleiconset_help'] = 'Resets the toggle icon set and toggle all hover to the default values for all courses so it will be the same as a course the first time it is in the \'Collapsed Topics\' format.';

// Site Administration -> Plugins -> Course formats -> Collapsed Topics.
$string['defaultheadingsub'] = 'Defaults';
$string['defaultheadingsubdesc'] = 'Default settings';
$string['configurationheadingsub'] = 'Configuration';
$string['configurationheadingsubdesc'] = 'Configuration settings';

$string['off'] = 'Off';
$string['on'] = 'On';
$string['defaultcoursedisplay'] = 'Course display';
$string['defaultcoursedisplay_desc'] = "Either show all the sections on a single page or section zero and the chosen section on page.";
$string['defaultlayoutelement'] = 'Layout';
// Negative view of layout, kept for previous versions until such time as they are updated.
$string['defaultlayoutelement_desc'] = "The layout setting can be one of:<br />'Default' with everything displayed.<br />No 'Topic x' / 'Week x' / 'Day x'.<br />No section number.<br />No 'Topic x' / 'Week x' / 'Day x' and no section number.<br />No 'Toggle' word.<br />No 'Toggle' word and no 'Topic x' / 'Week x' / 'Day x'.<br />No 'Toggle' word, no 'Topic x' / 'Week x' / 'Day x' and no section number.";
// Positive view of layout.
$string['defaultlayoutelement_descpositive'] = "The layout setting can be one of:<br />Toggle word, 'Topic x' / 'Week x' / 'Day x' and section number.<br />Toggle word and 'Topic x' / 'Week x' / 'Day x'.<br />Toggle word and section number.<br />'Topic x' / 'Week x' / 'Day x' and section number.<br />Toggle word.<br />'Topic x' / 'Week x' / 'Day x'.<br />Section number.<br />No additions.";

$string['defaultlayoutstructure'] = 'Structure configuration';
$string['defaultlayoutstructure_desc'] = "The structure setting can be one of:<br />Topic<br />Week<br />Latest Week First<br />Current Topic First<br />Day";

$string['defaultlayoutcolumns'] = 'Number of columns';
$string['defaultlayoutcolumns_desc'] = "Number of columns between one and four.";

$string['defaultlayoutcolumnorientation'] = 'Column orientation';
$string['defaultlayoutcolumnorientation_desc'] = "The default column orientation: Vertical or Horizontal.";

$string['defaulttgfgcolour'] = 'Toggle foreground colour';
$string['defaulttgfgcolour_desc'] = "Toggle foreground colour in hexidecimal RGB.";

$string['defaulttgfghvrcolour'] = 'Toggle foreground hover colour';
$string['defaulttgfghvrcolour_desc'] = "Toggle foreground hover colour in hexidecimal RGB.";

$string['defaulttgbgcolour'] = 'Toggle background colour';
$string['defaulttgbgcolour_desc'] = "Toggle background colour in hexidecimal RGB.";

$string['defaulttgbghvrcolour'] = 'Toggle background hover colour';
$string['defaulttgbghvrcolour_desc'] = "Toggle background hover colour in hexidecimal RGB.";

$string['defaulttogglealignment'] = 'Toggle text alignment';
$string['defaulttogglealignment_desc'] = "'Left', 'Centre' or 'Right'.";

$string['defaulttoggleiconset'] = 'Toggle icon set';
$string['defaulttoggleiconset_desc'] = "'Arrow'                => Arrow icon set.<br />'Bulb'                 => Bulb icon set.<br />'Cloud'                => Cloud icon set.<br />'Eye'                  => Eye icon set.<br />'Light Emitting Diode' => LED icon set.<br />'Point'                => Point icon set.<br />'Power'                => Power icon set.<br />'Radio'                => Radio icon set.<br />'Smiley'               => Smiley icon set.<br />'Square'               => Square icon set.<br />'Sun / Moon'           => Sun / Moon icon set.<br />'Switch'               => Switch icon set.";

$string['defaulttoggleallhover'] = 'Toggle all icon hovers';
$string['defaulttoggleallhover_desc'] = "'No' or 'Yes'.";

$string['defaulttogglepersistence'] = 'Toggle persistence';
$string['defaulttogglepersistence_desc'] = "'On' or 'Off'.  Turn off for an AJAX performance increase but user toggle selections will not be remembered on page refresh or revisit.<br />Note: When turning persistence off, please remove any rows containing 'qmultopics_toggle_x' in the 'name' field of the 'user_preferences' table in the database.  Where the 'x' in 'qmultopics_toggle_x' will be a course id.  This is to save space if you do not intend to turn it back on.";

$string['defaultuserpreference'] = 'Initial toggle state';
$string['defaultuserpreference_desc'] = 'States what to do with the toggles when the user first accesses the course, the state of additional sections when they are added or toggle persistence is off.';

// Toggle icon size.
$string['defaulttoggleiconsize'] = 'Toggle icon size';
$string['defaulttoggleiconsize_desc'] = "Icon size: Small = 16px, Medium = 24px and Large = 32px.";
$string['small'] = 'Small';
$string['medium'] = 'Medium';
$string['large'] = 'Large';

// Toggle border radius.
$string['defaulttoggleborderradiustl'] = 'Toggle top left border radius';
$string['defaulttoggleborderradiustl_desc'] = 'Border top left radius of the toggle.';
$string['defaulttoggleborderradiustr'] = 'Toggle top right border radius';
$string['defaulttoggleborderradiustr_desc'] = 'Border top right radius of the toggle.';
$string['defaulttoggleborderradiusbr'] = 'Toggle bottom right border radius';
$string['defaulttoggleborderradiusbr_desc'] = 'Border bottom right radius of the toggle.';
$string['defaulttoggleborderradiusbl'] = 'Toggle bottom left border radius';
$string['defaulttoggleborderradiusbl_desc'] = 'Border bottom left radius of the toggle.';
$string['em0_0'] = '0.0em';
$string['em0_1'] = '0.1em';
$string['em0_2'] = '0.2em';
$string['em0_3'] = '0.3em';
$string['em0_4'] = '0.4em';
$string['em0_5'] = '0.5em';
$string['em0_6'] = '0.6em';
$string['em0_7'] = '0.7em';
$string['em0_8'] = '0.8em';
$string['em0_9'] = '0.9em';
$string['em1_0'] = '1.0em';
$string['em1_1'] = '1.1em';
$string['em1_2'] = '1.2em';
$string['em1_3'] = '1.3em';
$string['em1_4'] = '1.4em';
$string['em1_5'] = '1.5em';
$string['em1_6'] = '1.6em';
$string['em1_7'] = '1.7em';
$string['em1_8'] = '1.8em';
$string['em1_9'] = '1.9em';
$string['em2_0'] = '2.0em';
$string['em2_1'] = '2.1em';
$string['em2_2'] = '2.2em';
$string['em2_3'] = '2.3em';
$string['em2_4'] = '2.4em';
$string['em2_5'] = '2.5em';
$string['em2_6'] = '2.6em';
$string['em2_7'] = '2.7em';
$string['em2_8'] = '2.8em';
$string['em2_9'] = '2.9em';
$string['em3_0'] = '3.0em';
$string['em3_1'] = '3.1em';
$string['em3_2'] = '3.2em';
$string['em3_3'] = '3.3em';
$string['em3_4'] = '3.4em';
$string['em3_5'] = '3.5em';
$string['em3_6'] = '3.6em';
$string['em3_7'] = '3.7em';
$string['em3_8'] = '3.8em';
$string['em3_9'] = '3.9em';
$string['em4_0'] = '4.0em';

$string['formatresponsive'] = 'Format responsive';
$string['formatresponsive_desc'] = "Turn on if you are using a non-responsive theme and the format will adjust to the screen size / device.  Turn off if you are using a responsive theme.  Bootstrap 2.3.2 support is built in, for other frameworks and versions, override the methods 'get_row_class()' and 'get_column_class()' in renderer.php.";

// Do not show date.
$string['donotshowdate'] = 'Do not show the date';
$string['donotshowdate_help'] = 'Do not show the date when using a weekly based structure and \'Use default section name\' has been un-ticked.';

// Capabilities.
$string['qmultopics:changelayout'] = 'Change or reset the layout';
$string['qmultopics:changecolour'] = 'Change or reset the colour';
$string['qmultopics:changetogglealignment'] = 'Change or reset the toggle alignment';
$string['qmultopics:changetoggleiconset'] = 'Change or reset the toggle icon set';

// Instructions.
$string['instructions'] = 'Instructions: Clicking on the section name will show / hide the section.';
$string['displayinstructions'] = 'Display instructions';
$string['displayinstructions_help'] = 'States that the instructions should be displayed to the user or not.';
$string['defaultdisplayinstructions'] = 'Display instructions to users';
$string['defaultdisplayinstructions_desc'] = "Display instructions to users informing them how to use the toggles.  Can be yes or no.";
$string['resetdisplayinstructions'] = 'Display instructions';
$string['resetalldisplayinstructions'] = 'Display instructions';
$string['resetdisplayinstructions_help'] = 'Resets the display instructions to the default value so it will be the same as a course the first time it is in the Collapsed Topics format.';
$string['resetalldisplayinstructions_help'] = 'Resets the display instructions to the default value for all courses so it will be the same as a course the first time it is in the Collapsed Topics format.';

// Readme.
$string['readme_title'] = 'Collapsed Topics read-me';
$string['readme_desc'] = 'Please click on \'{$a->url}\' for lots more information about Collapsed Topics.';


// QMUL Strings.
$string['assessmentinformation'] = 'Assessment Information';
$string['assessmentinformation_help'] = 'Add assessment information here';
$string['assignmentsdue'] = 'Assignments Due';
$string['assignmentssubmitted'] = 'Assignments Submitted';
$string['enabletab'] = 'Enable Tab';
$string['extratab'] = 'Extra Tab {$a}';
$string['extratab_help'] = 'Here you can add additional information for your course';
$string['modulecontent'] = 'Module Content';
$string['noassignmentsdue'] = 'No Assignments Due';
$string['noassignmentssubmitted'] = 'No Assignments Submitted';
$string['tabtitle'] = 'Tab Title';
$string['tabcontent'] = 'Tab Content';
$string['titlerequiredwhenenabled'] = 'Tab is required to have a title when enabled';
$string['editnewssettings'] = 'Edit news display settings';

// Strings for tabs.
$string['extratabname'] = 'All';
$string['orphaned_tabname'] = 'Orphaned';
$string['section0'] = 'First Topic';
$string['section0_label'] = 'Show Topic 0 above all tabs';
$string['all_tab'] = 'The All Tab';
$string['all_tab_label'] = 'Show \'Module Content\' tab always';
$string['tabtitle_label'] = 'Tab {$a} Title';
$string['sectiontitle_label'] = 'Use title of 1st section as tab title.';
$string['tabsections_label'] = 'Tab {$a} Sections';

$string['tabtitle_help'] = 'The title of the tab as shown on the page.';
$string['tabsections_help'] = 'Enter the topic numbers separated by commas that will be displayed under this tab. Leave empty for no tab.';
$string['section0_help'] = 'When checked topic 0 is always shown above the tabs.';
$string['all_tab_help'] = 'When checked the \'Module Content\' tab containing all topics is always shown while editing a course to allow re-arrangement of topics accross tabs.';
$string['sectiontitle_help'] = 'When checked the title of the first topic of a tab is used as the tab title if available.';

$string['sectiontitle'] = 'Topic';
$string['tabsections'] = 'Tab Sections';

$string['orphan_tab_edit_only'] = 'Show Orphan Tab in edit mode only';
$string['orphan_tab_edit_only_label'] = 'Show \'Orphaned\' tab in edit mode only';
$string['orphan_tab_edit_only_help'] = 'When checked the \'Orphaned\' tab will only be shown while in edit mode. The \'Orphaned\' tab is never shown to students.';
$string['orphan_tab_hint'] = 'This tab and its content is not shown to students!';

$string['single_section_tabs'] = 'Use section name as tab name for single sections';
$string['single_section_tabs_label'] = 'Use section name as tab name for single sections';
$string['single_section_tabs_help'] = 'When checked tabs with a single section will use the section name as tab name.';

$string['tabtitle_edithint'] = 'Edit tab name';
$string['tabtitle_editlabel'] = 'New value for {a}';

$string['defaultsectionnameastabname'] = 'Use section name as tab name for single sections by default';
$string['defaultsectionnameastabname_desc'] = 'Tabs with a single section will use the section name as tab name by default';

$string['defaultshowassessmentinfotab'] = 'Show "Assessment Information" tab by default';
$string['defaultshowassessmentinfotab_desc'] = 'When installed show the "Assessment Information" block under a separate tab by default';

$string['hidden_tab_hint'] = 'This tab contains only hidden sections and will not be shown to students';

// News strings.
$string['readfullpost'] = 'Read full post';
$string['morenews'] = 'More news';

$string['shownewsfull'] = 'I want to display a shortened version of news and announcements';
$string['statictext'] = 'I want to display some static text';
$string['usestatictext'] = 'Use static text instead of Module Announcements';
$string['currentsection'] = 'This topic';
$string['editsection'] = 'Edit topic';
$string['deletesection'] = 'Delete topic';
$string['sectionname'] = 'Topic';
$string['pluginname'] = 'Topics format (QMUL)';
$string['section0name'] = 'General';
$string['page-course-view-qmultopics'] = 'Any course main page in topics format (QMUL)';
$string['page-course-view-qmultopics-x'] = 'Any course page in topics format (QMUL)';
$string['hidefromothers'] = 'Hide topic';
$string['showfromothers'] = 'Show topic';
$string['addsections'] = 'Add Topics';

// Instructions.
$string['instructions'] = 'Instructions: Clicking on the section name will show / hide the section.';
$string['displayinstructions'] = 'Display instructions';
$string['displayinstructions_help'] = 'States that the instructions should be displayed to the user or not.';
$string['defaultdisplayinstructions'] = 'Display instructions to users';
$string['defaultdisplayinstructions_desc'] = "Display instructions to users informing them how to use the toggles.  Can be yes or no.";
$string['resetdisplayinstructions'] = 'Display instructions';
$string['resetalldisplayinstructions'] = 'Display instructions';
$string['resetdisplayinstructions_help'] = 'Resets the display instructions to the default value so it will be the same as a course the first time it is in the Collapsed Topics format.';
$string['resetalldisplayinstructions_help'] = 'Resets the display instructions to the default value for all courses so it will be the same as a course the first time it is in the Collapsed Topics format.';

// QMUL related.
$string['assessmentinformation'] = 'Assessment Information';
$string['assessmentinformation_help'] = 'Add assessment information here';
$string['assignmentsdue'] = 'Assignments Due';
$string['assignmentssubmitted'] = 'Assignments Submitted';
$string['enabletab'] = 'Enable Tab';
$string['extratab'] = 'Extra Tab {$a}';
$string['extratab_help'] = 'Here you can add additional information for your course';
$string['modulecontent'] = 'Module Content';
$string['noassignmentsdue'] = 'No Assignments Due';
$string['noassignmentssubmitted'] = 'No Assignments Submitted';
$string['tabtitle'] = 'Tab Title';
$string['tabcontent'] = 'Tab Content';
$string['titlerequiredwhenenabled'] = 'Tab is required to have a title when enabled';

$string['defaultcollapse'] = 'Default collapse status';
$string['defaultcollapsed'] = 'All topics are collapsed';
$string['defaultexpanded'] = 'All topics are expanded';
$string['alwaysexpanded'] = 'All topics are always expanded (No collapse option)';
$string['defaultcollapse_help'] = 'By default all topics will be shown collapsed to users who have not yet made an own selection. You may select to show all topics expanded instead.';

// Tab related.
$string['single_section_tabs'] = 'Use section name as tab name for single sections';
$string['single_section_tabs_label'] = 'Use section name as tab name for single sections';
$string['single_section_tabs_help'] = 'When checked tabs with a single section will use the section name as tab name.';

$string['tab_assessment_info_title'] = 'Assessment Information';
$string['tab_assessment_information_title'] = 'Assessment Information';

$string['tab_assessment_info_block_title'] = 'Assessment Information';

$string['assessment_info_block_tab'] = 'How to show "Assessment Info" block when installed';
$string['assessment_info_block_tab_label'] = 'How to show the "Assessment Info" block when installed';
$string['assessment_info_block_tab_help'] = 'When installed the "Assessment Info" block may be shown in one of 3 ways: <UL><LI>as a regular block (default)</LI><LI>under a separate tab or</LI><LI>merged with the "Assessment Information" tab.</LI></UL> <B>Please note</B> that when set to "merged" but the "Assessment Information" tab is deactivated, the "Assessment Info" Block is shown as a normal block again!';
$string['assessment_info_block_tab_option0'] = 'Show as Block';
$string['assessment_info_block_tab_option1'] = 'Show as Tab';
$string['assessment_info_block_tab_option2'] = 'Merge with "Assessment Information" Tab';

$string['tabname'] = 'Tab';
$string['tabzero_title'] = 'Module Content';
$string['tabtitle_edithint'] = 'Edit tab name';
$string['tabtitle_editlabel'] = 'New value for {a}';

$string['hidden_tab_hint'] = 'This tab contains only hidden topics and will not be shown to students';

// News settings.
$string['newssettingsof'] = 'News settings for {$a}';
$string['displaynews'] = 'Display the news section';
$string['shownewsfull'] = 'Show the full news content';
$string['statictext'] = 'Static text';
$string['usestatictext'] = 'Use static text';

// Badges.
$string['label_due'] = 'Due ';
$string['label_duetoday'] = 'DUE TODAY ';
$string['label_cutoffdate'] = 'LATE submission until  ';
$string['label_wasdue'] = 'Was due ';
$string['label_spacer'] = '&nbsp;&nbsp;';
$string['label_commaspacer'] = ',&nbsp;&nbsp;';
$string['label_submitted'] = '  Submitted ';
$string['label_notsubmitted'] = 'Not submitted';
$string['label_answered'] = '  Answered ';
$string['label_notanswered'] = 'Not answered';
$string['label_attempted'] = '  Attempted ';
$string['label_notattempted'] = 'Not attempted';
$string['label_completed'] = '  Completed ';
$string['label_notcompleted'] = 'Not completed';
$string['label_groups'] = ' Groups';
$string['label_graded'] = ' Graded';
$string['label_ungraded'] = ' Ungraded';
$string['label_inprogress'] = 'In Progress since ';
$string['label_xofy'] = ' of ';
$string['label_finished'] = ' Finished ';
$string['label_feedback'] = ' - Feedback available!';
$string['label_submission_time_title'] = 'Submission time: ';
$string['usethemebadges'] = 'Use assessment labels from QMUL theme';
$string['usethemebadges_desc'] = 'Switch to either use assessment labels provided by the course format or by the theme (in case the new format badges are affecting performance too much)';
$string['useassignlabels'] = 'Use assessment labels';
$string['useassignlabels_desc'] = 'If switched on labels will be shown for assign, choice, feedback, lesson and quiz assessments on course pages';
$string['useassignlabelcaches'] = 'Use caches for assessment label data';
$string['useassignlabelcaches_desc'] = 'If switched on data used by assessment labels will be cached where possible to reduce database load. Changes to the module structure may show in assessment labels with some delay';

// Cache strings.
$string['cachedef_assignment_data'] = 'This is the assessment data cache used by assessment labels';
$string['cachedef_choice_data'] = 'This is the choice data cache used by assessment labels';
$string['cachedef_feedback_data'] = 'This is the feedback data cache used by assessment labels';
$string['cachedef_lesson_data'] = 'This is the lesson data cache used by assessment labels';
$string['cachedef_quiz_data'] = 'This is the quiz data cache used by assessment labels';
// Admin caches.
$string['cachedef_admin_assignment_data'] = 'Cache holding assignment submission and grading numbers for all eligible students of a course';
$string['cachedef_admin_group_assignment_data'] = 'Cache holding assignment submission and grading numbers for all eligible student groups of a course';
$string['cachedef_admin_choice_data'] = 'Cache holding choice submission numbers for all eligible students of a course';
$string['cachedef_admin_feedback_data'] = 'Cache holding feedback submission numbers for all eligible students of a course';
$string['cachedef_admin_lesson_data'] = 'Cache holding lesson submission numbers for all eligible students of a course';
$string['cachedef_admin_quiz_data'] = 'Cache holding quiz submission numbers for all eligible students of a course';
// Student caches.
$string['cachedef_student_assessment_data'] = 'Cache holding all assignment submissions for a course by a student';
$string['cachedef_student_group_assessment_data'] = 'Cache holding all group assignment submissions for a course by a student';
$string['cachedef_student_choice_data'] = 'Cache holding all choice submissions for a course by a student';
$string['cachedef_student_feedback_data'] = 'Cache holding all feedback submissions for a course by a student';
$string['cachedef_student_lesson_data'] = 'Cache holding all lesson submissions for a course by a student';
$string['cachedef_student_quiz_data'] = 'Cache holding all quiz submissions for a course by a student';
