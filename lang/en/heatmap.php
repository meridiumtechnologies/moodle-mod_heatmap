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
 * English strings for heatmap
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod_heatmap
 * @copyright  2015 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['modulename'] = 'Heatmap';
$string['modulenameplural'] = 'Heatmaps';
$string['modulename_help'] = 'Use this module for displaying Heat Map of participants';
$string['heatmapfieldset'] = 'Detailed report';
$string['amMapfieldset'] = 'Heatmap settings';
$string['heatmapname'] = 'Name of activity/page';
$string['heatmapname_help'] = 'This is the content of the help tooltip associated with the heatmapname field. Markdown syntax is supported.';
$string['heatmap'] = 'heatmap';
$string['heatmap:view'] = 'View Heatmaps';
$string['heatmap:view'] = 'Add Heatmaps';
$string['pluginadministration'] = 'heatmap administration';
$string['pluginname'] = 'heatmap';
$string['crondescription'] = 'Update Heatmap data';
$string['moduleheader'] = 'Origin of participants';
$string['downloadtitle'] = 'Download detailed report';
$string['displaytotal'] = 'Display total number of users';
$string['displaycontinentbreakdown'] = 'Display continental breakdown';
$string['lockemptycountries'] = 'Lock countries with no users';
$string['continentBreakdown'] = '{$a->numberOfParticipants} participants from {$a->numberOfCountries} countries';
$string['total'] = 'As of <span class="total">{$a->date}</span> (<i>{$a->timezone}</i>) this website had <span class="total">{$a->totalparticipants}</span> Registered Participants from <span class="total">{$a->totalcountries}</span> Countries.';
$string['EU'] = 'Europe';
$string['AF'] = 'Africa';
$string['AS'] = 'Asia';
$string['NA'] = 'North-America';
$string['SA'] = 'South-America';
$string['OC'] = 'Oceania';
$string['nodata'] = 'There is no data to display just yet. Make sure to run the cron job at least once!';
