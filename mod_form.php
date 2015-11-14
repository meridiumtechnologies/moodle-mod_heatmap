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
 * The main heatmap configuration form
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package    mod_heatmap
 * @copyright  2015 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

/**
 * Module instance settings form
 *
 * @package    mod_heatmap
 * @copyright  2015 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_heatmap_mod_form extends moodleform_mod
{

    /**
     * Defines forms elements
     */
    public function definition()
    {

        $mform = $this->_form;

        // Adding the "general" fieldset, where all the common settings are showed.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field.
        $mform->addElement('text', 'name', get_string('heatmapname', 'heatmap'), array('size' => '64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEAN);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('name', 'heatmapname', 'heatmap');

        // Adding the standard "intro" and "introformat" fields.
        $this->standard_intro_elements();

        //-------------------------------------------------------

        // Heatmap settings
        $mform->addElement('header', 'amMapfieldset', get_string('amMapfieldset', 'heatmap'));
        $mform->addElement('selectyesno', 'displaytotal', get_string('displaytotal', 'heatmap'));
        $mform->setDefault('displaytotal', 1);

        $mform->addElement('selectyesno', 'displaycontinentbreakdown', get_string('displaycontinentbreakdown', 'heatmap'));
        $mform->setDefault('displaycontinentbreakdown', 1);

        $mform->addElement('selectyesno', 'lockemptycountries', get_string('lockemptycountries', 'heatmap'));
        $mform->setDefault('lockemptycountries', 1);

        //-------------------------------------------------------
        // Add filemanager for monthly statistics
        $mform->addElement('header', 'heatmapfieldset', get_string('heatmapfieldset', 'heatmap'));
        $mform->addElement('filemanager', 'attachment', get_string('file'), null, array('subdirs'=>0, 'accepted_types'=>'*'));

        //-------------------------------------------------------
        // Add standard elements, common to all modules.
        $this->standard_coursemodule_elements();

        //-------------------------------------------------------
        // Add standard buttons, common to all modules.
        $this->add_action_buttons();

    }
    /**
     * Any data processing needed before the form is displayed
     * (needed to set up draft areas for editor and filemanager elements)
     * @param array $defaultvalues
     */
    function data_preprocessing(&$default_values) {
        if ($this->current->instance) {
            // editing existing instance - copy existing files into draft area
            $draftitemid = file_get_submitted_draft_itemid('attachment');
            file_prepare_draft_area($draftitemid, $this->context->id, 'mod_heatmap', 'attachment', 0, array('subdirs'=>0));
            $default_values['attachment'] = $draftitemid;
        }
    }
    /**
     * Perform minimal validation on the settings form
     * @param array $data
     * @param array $files
     */
    function validation($data, $files) {
        $errors = parent::validation($data, $files);

        /*TO BE COMPLETED*/

        return $errors;
    }
}