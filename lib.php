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
 * Library of interface functions and constants for module heatmap
 *
 * All the core Moodle functions, needed to allow the module to work
 * integrated in Moodle should be placed here.
 *
 * All the heatmap specific functions, needed to implement all the module
 * logic, should go to locallib.php. This will help to save some memory when
 * Moodle is performing actions across all modules.
 *
 * @package    mod_heatmap
 * @copyright  2015 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/* Moodle core API */

/**
 * Returns the information on whether the module supports a feature
 *
 * See {@link plugin_supports()} for more info.
 *
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed true if the feature is supported, null if unknown
 */
function heatmap_supports($feature) {

    switch($feature) {
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return false;
        case FEATURE_GRADE_HAS_GRADE:
            return false;
        case FEATURE_BACKUP_MOODLE2:
            return false;
        case FEATURE_MOD_ARCHETYPE:
            return MOD_ARCHETYPE_RESOURCE;
        default:
            return null;
    }
}

/**
 * Saves a new instance of the heatmap into the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param stdClass $heatmap Submitted data from the form in mod_form.php
 * @param mod_heatmap_mod_form $mform The form instance itself (if needed)
 * @return int The id of the newly inserted heatmap record
 */
function heatmap_add_instance(stdClass $heatmap, mod_heatmap_mod_form $mform = null) {
    global $DB;

    $cmid        = $heatmap->coursemodule;

    $heatmap->timecreated = time();
    $draftitemid = $heatmap->attachment;

    $heatmap->id = $DB->insert_record('heatmap', $heatmap);
    $context = context_module::instance($cmid);

    if ($draftitemid) {
        file_save_draft_area_files($draftitemid, $context->id, 'mod_heatmap', 'attachment', 0, array('subdirs'=>0));
    }
    return $heatmap->id;
}

/**
 * Updates an instance of the heatmap in the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param stdClass $heatmap An object from the form in mod_form.php
 * @param mod_heatmap_mod_form $mform The form instance itself (if needed)
 * @return boolean Success/Fail
 */
function heatmap_update_instance(stdClass $heatmap, mod_heatmap_mod_form $mform = null) {
    global $DB;

    $cmid        = $heatmap->coursemodule;
    $heatmap->timemodified = time();
    $heatmap->id = $heatmap->instance;
    $draftitemid = $heatmap->attachment;

    $result = $DB->update_record('heatmap', $heatmap);
    $context = context_module::instance($cmid);
    if ($draftitemid = file_get_submitted_draft_itemid('attachment')) {
        file_save_draft_area_files($draftitemid, $context->id, 'mod_heatmap', 'attachment', 0, array('subdirs'=>0));
    }

    return $result;
}

/**
 * Removes an instance of the heatmap from the database
 *
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function heatmap_delete_instance($id) {
    global $DB;

    if (! $heatmap = $DB->get_record('heatmap', array('id' => $id))) {
        return false;
    }

    // Delete any dependent records here.

    $DB->delete_records('heatmap', array('id' => $heatmap->id));

    heatmap_grade_item_delete($heatmap);

    return true;
}

/**
 * Returns a small object with summary information about what a
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 *
 * $return->time = the time they did it
 * $return->info = a short text description
 *
 * @param stdClass $course The course record
 * @param stdClass $user The user record
 * @param cm_info|stdClass $mod The course module info object or record
 * @param stdClass $heatmap The heatmap instance record
 * @return stdClass|null
 */
function heatmap_user_outline($course, $user, $mod, $heatmap) {

    $return = new stdClass();
    $return->time = 0;
    $return->info = '';
    return $return;
}

/**
 * Prints a detailed representation of what a user has done with
 * a given particular instance of this module, for user activity reports.
 *
 * It is supposed to echo directly without returning a value.
 *
 * @param stdClass $course the current course record
 * @param stdClass $user the record of the user we are generating report for
 * @param cm_info $mod course module info
 * @param stdClass $heatmap the module instance record
 */
function heatmap_user_complete($course, $user, $mod, $heatmap) {
}

/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in heatmap activities and print it out.
 *
 * @param stdClass $course The course record
 * @param bool $viewfullnames Should we display full names
 * @param int $timestart Print activity since this timestamp
 * @return boolean True if anything was printed, otherwise false
 */
function heatmap_print_recent_activity($course, $viewfullnames, $timestart) {
    return false;
}

/**
 * Prepares the recent activity data
 *
 * This callback function is supposed to populate the passed array with
 * custom activity records. These records are then rendered into HTML via
 * {@link heatmap_print_recent_mod_activity()}.
 *
 * Returns void, it adds items into $activities and increases $index.
 *
 * @param array $activities sequentially indexed array of objects with added 'cmid' property
 * @param int $index the index in the $activities to use for the next record
 * @param int $timestart append activity since this time
 * @param int $courseid the id of the course we produce the report for
 * @param int $cmid course module id
 * @param int $userid check for a particular user's activity only, defaults to 0 (all users)
 * @param int $groupid check for a particular group's activity only, defaults to 0 (all groups)
 */
function heatmap_get_recent_mod_activity(&$activities, &$index, $timestart, $courseid, $cmid, $userid=0, $groupid=0) {
}

/**
 * Prints single activity item prepared by {@link heatmap_get_recent_mod_activity()}
 *
 * @param stdClass $activity activity record with added 'cmid' property
 * @param int $courseid the id of the course we produce the report for
 * @param bool $detail print detailed report
 * @param array $modnames as returned by {@link get_module_types_names()}
 * @param bool $viewfullnames display users' full names
 */
function heatmap_print_recent_mod_activity($activity, $courseid, $detail, $modnames, $viewfullnames) {
}

/**
 * Function to be run periodically according to the moodle cron
 *
 * This function searches for things that need to be done, such
 * as sending out mail, toggling flags etc ...
 *
 * Note that this has been deprecated in favour of scheduled task API.
 *
 * @return boolean
 */
function heatmap_cron () {
    return true;
}

/**
 * Returns all other caps used in the module
 *
 * For example, this could be array('moodle/site:accessallgroups') if the
 * module uses that capability.
 *
 * @return array
 */
function heatmap_get_extra_capabilities() {
    return array();
}

/* Gradebook API */

/**
 * Is a given scale used by the instance of heatmap?
 *
 * This function returns if a scale is being used by one heatmap
 * if it has support for grading and scales.
 *
 * @param int $heatmapid ID of an instance of this module
 * @param int $scaleid ID of the scale
 * @return bool true if the scale is used by the given heatmap instance
 */
function heatmap_scale_used($heatmapid, $scaleid) {
    global $DB;

    if ($scaleid and $DB->record_exists('heatmap', array('id' => $heatmapid, 'grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}

/**
 * Checks if scale is being used by any instance of heatmap.
 *
 * This is used to find out if scale used anywhere.
 *
 * @param int $scaleid ID of the scale
 * @return boolean true if the scale is used by any heatmap instance
 */
function heatmap_scale_used_anywhere($scaleid) {
    global $DB;

    if ($scaleid and $DB->record_exists('heatmap', array('grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}

/**
 * Creates or updates grade item for the given heatmap instance
 *
 * Needed by {@link grade_update_mod_grades()}.
 *
 * @param stdClass $heatmap instance object with extra cmidnumber and modname property
 * @param bool $reset reset grades in the gradebook
 * @return void
 */
function heatmap_grade_item_update(stdClass $heatmap, $reset=false) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    $item = array();
    $item['itemname'] = clean_param($heatmap->name, PARAM_NOTAGS);
    $item['gradetype'] = GRADE_TYPE_VALUE;

    if ($heatmap->grade > 0) {
        $item['gradetype'] = GRADE_TYPE_VALUE;
        $item['grademax']  = $heatmap->grade;
        $item['grademin']  = 0;
    } else if ($heatmap->grade < 0) {
        $item['gradetype'] = GRADE_TYPE_SCALE;
        $item['scaleid']   = -$heatmap->grade;
    } else {
        $item['gradetype'] = GRADE_TYPE_NONE;
    }

    if ($reset) {
        $item['reset'] = true;
    }

    grade_update('mod/heatmap', $heatmap->course, 'mod', 'heatmap',
            $heatmap->id, 0, null, $item);
}

/**
 * Delete grade item for given heatmap instance
 *
 * @param stdClass $heatmap instance object
 * @return grade_item
 */
function heatmap_grade_item_delete($heatmap) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    return grade_update('mod/heatmap', $heatmap->course, 'mod', 'heatmap',
            $heatmap->id, 0, null, array('deleted' => 1));
}

/**
 * Update heatmap grades in the gradebook
 *
 * Needed by {@link grade_update_mod_grades()}.
 *
 * @param stdClass $heatmap instance object with extra cmidnumber and modname property
 * @param int $userid update grade of specific user only, 0 means all participants
 */
function heatmap_update_grades(stdClass $heatmap, $userid = 0) {
    global $CFG, $DB;
    require_once($CFG->libdir.'/gradelib.php');

    // Populate array of grade objects indexed by userid.
    $grades = array();

    grade_update('mod/heatmap', $heatmap->course, 'mod', 'heatmap', $heatmap->id, 0, $grades);
}

/* File API */

/**
 * Returns the lists of all browsable file areas within the given module context
 *
 * The file area 'intro' for the activity introduction field is added automatically
 * by {@link file_browser::get_file_info_context_module()}
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @return array of [(string)filearea] => (string)description
 */
function heatmap_get_file_areas($course, $cm, $context) {
    return array();
}

/**
 * File browsing support for heatmap file areas
 *
 * @package mod_heatmap
 * @category files
 *
 * @param file_browser $browser
 * @param array $areas
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @param string $filearea
 * @param int $itemid
 * @param string $filepath
 * @param string $filename
 * @return file_info instance or null if not found
 */
function heatmap_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {
    return null;
}

/**
 * Serves the files from the heatmap file areas
 *
 * @package mod_heatmap
 * @category files
 *
 * @param stdClass $course the course object
 * @param stdClass $cm the course module object
 * @param stdClass $context the heatmap's context
 * @param string $filearea the name of the file area
 * @param array $args extra arguments (itemid, path)
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 */
function heatmap_pluginfile($course, $cm, $context, $filearea, array $args, $forcedownload, array $options=array()) {
    global $DB, $CFG;

    if ($context->contextlevel != CONTEXT_MODULE) {
        send_file_not_found();
    }

    require_login($course, true, $cm);

    array_shift($args); // ignore revision - designed to prevent caching problems only

    $fs = get_file_storage();
    $relativepath = implode('/', $args);
    $fullpath = "/$context->id/mod_heatmap/attachment/0/$relativepath";
    if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
        return false;
    }

    // finally send the file
    // for folder module, we force download file all the time
    send_stored_file($file, 0, 0, true, $options);
}

/* Navigation API */

/**
 * Extends the global navigation tree by adding heatmap nodes if there is a relevant content
 *
 * This can be called by an AJAX request so do not rely on $PAGE as it might not be set up properly.
 *
 * @param navigation_node $navref An object representing the navigation tree node of the heatmap module instance
 * @param stdClass $course current course record
 * @param stdClass $module current heatmap instance record
 * @param cm_info $cm course module information
 */
function heatmap_extend_navigation(navigation_node $navref, stdClass $course, stdClass $module, cm_info $cm) {
    // TODO Delete this function and its docblock, or implement it.
}

/**
 * Extends the settings navigation with the heatmap settings
 *
 * This function is called when the context for the page is a heatmap module. This is not called by AJAX
 * so it is safe to rely on the $PAGE.
 *
 * @param settings_navigation $settingsnav complete settings navigation tree
 * @param navigation_node $heatmapnode heatmap administration node
 */
function heatmap_extend_settings_navigation(settings_navigation $settingsnav, navigation_node $heatmapnode=null) {
    // TODO Delete this function and its docblock, or implement it.
}

/**
 * if return=html, then return a html string.
 * if return=text, then return a text-only string.
 * otherwise, print HTML for non-images, and return image HTML
 *     if attachment is an image, $align set its aligment.
 *
 * @global object
 * @global object
 * @param object $entry
 * @param object $cm
 * @param string $type html, txt, empty
 * @param string $unused This parameter is no longer used
 * @return string image string or nothing depending on $type param
 */
function heatmap_print_attachments($cm, $type=NULL) {
    global $CFG, $DB, $OUTPUT;

    $filecontext = $context = context_module::instance($cm->id,IGNORE_MISSING);
    $strattachment = get_string('heatmapfieldset', 'heatmap');
    $component = 'mod_heatmap';
    $filearea = 'attachment';

    $imagereturn = '';
    $output = '';
    $fs = get_file_storage();
    if ($files = $fs->get_area_files($filecontext->id, $component, $filearea, false, '', false)) {
        foreach ($files as $file) {
            $filename = $file->get_filename();
            $mimetype = $file->get_mimetype();
            $timecreated = date('F d Y', $file->get_timecreated());

            $iconimage = $OUTPUT->pix_icon(file_file_icon($file), get_mimetype_description($file), 'moodle', array('class' => 'icon'));
            $path = file_encode_url($CFG->wwwroot.'/pluginfile.php', '/'.$context->id.'/mod_heatmap/attachment/0/'.$filename);

            if ($type == 'html') {
                $output .= "<a href=\"$path\">$iconimage</a> ";
                $output .= "<a href=\"$path\">".get_string('downloadtitle', 'heatmap')."</a> <span class=\"datetime\">File updated on $timecreated</span>";
                $output .= "<br />";

            } else if ($type == 'text') {
                $output .= "$strattachment ".s($filename).":\n$path\n";

            } else {
                if (in_array($mimetype, array('image/gif', 'image/jpeg', 'image/png'))) {
                    // Image attachments don't get printed as links
                    $imagereturn .= "<br /><img src=\"$path\" alt=\"\" />";
                } else {
                    $output .= "<a href=\"$path\">$iconimage</a> ";
                    $output .= format_text("<a href=\"$path\">".s($filename)."</a>", FORMAT_HTML, array('context'=>$context));
                    $output .= '<br />';
                }
            }
        }
    }

    if ($type) {
        return $output;
    } else {
        echo $output;
        return $imagereturn;
    }
}