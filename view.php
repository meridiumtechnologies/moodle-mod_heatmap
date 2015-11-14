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
 * Prints a particular instance of heatmap
 *
 * @package    mod_heatmap
 * @copyright  2015 Meridium Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname($_SERVER["SCRIPT_FILENAME"]))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

$id = optional_param('id', 0, PARAM_INT); // Course_module ID, or
$n  = optional_param('h', 0, PARAM_INT);  // ... heatmap instance ID - it should be named as the first character of the module.

if ($id) {
    $cm         = get_coursemodule_from_id('heatmap', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $heatmap  = $DB->get_record('heatmap', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($n) {
    $heatmap  = $DB->get_record('heatmap', array('id' => $n), '*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $heatmap->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance('heatmap', $heatmap->id, $course->id, false, MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}
require_login($course, true, $cm);

$event = \mod_heatmap\event\course_module_viewed::create(array(
    'objectid' => $PAGE->cm->instance,
    'context' => $PAGE->context,
));
$event->add_record_snapshot('course', $PAGE->course);
$event->add_record_snapshot($PAGE->cm->modname, $heatmap);
$event->trigger();

$heatmapdata = 'data/';
$ammapdatafile = $heatmapdata.'data-'.$cm->instance.'.js';
$breakdownfile = $heatmapdata.'breakdown-'.$cm->instance.'.html';

if(!file_exists($ammapdatafile) || !file_exists($breakdownfile)) {
    throw new file_exception("Unable to find source files in data directory! \n\r Make sure your run heatmap task at leat once through cron!", $heatmapdata);
}


// Print the page header.
$PAGE->set_url('/mod/heatmap/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($heatmap->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->requires->css(new moodle_url('/mod/heatmap/vendor/ammap.css'));
$PAGE->requires->css(new moodle_url('/mod/heatmap/stylesheets/styles.css'));
$PAGE->requires->js(new moodle_url('/mod/heatmap/javascript/jquery-1.7.2.min.js'));
$PAGE->requires->js(new moodle_url('/mod/heatmap/vendor/ammap.js'));
$PAGE->requires->js(new moodle_url('/mod/heatmap/vendor/maps/js/worldLow.js'));
$PAGE->requires->js(new moodle_url('/mod/heatmap/'.$ammapdatafile));
$PAGE->requires->js(new moodle_url('/mod/heatmap/javascript/toggler.js'));
$PAGE->add_body_class('content-only'); // Hide all blocks


// Output starts here.
echo $OUTPUT->header();
if ($heatmap->intro) {
    $heatmap->intro = '<nolink>'.$heatmap->intro.'</nolink>';
    echo $OUTPUT->box(format_module_intro('heatmap', $heatmap, $cm->id), 'generalbox mod_introbox', 'heatmapintro');
}
// Render ammap area
echo '<div id="mapdiv" style="width:80%; background-color:#FFFFFF; height:500px; margin:0 auto 0 auto;"></div>';
// Render attached file, if any
if (!empty(heatmap_print_attachments($cm, 'html'))) {
    echo '<blockquote>' . heatmap_print_attachments($cm, 'html') . '</blockquote>';
}
echo file_get_contents($breakdownfile);
echo $OUTPUT->footer();