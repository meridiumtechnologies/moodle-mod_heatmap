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
 * Generates datafiles for all heatmap instances
 *
 * @package    mod_heatmap
 * @copyright  2015 Meridium Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_heatmap\task;

defined('MOODLE_INTERNAL') || die();

class update_map extends \core\task\scheduled_task
{
	public function get_name()
	{
		// Shown in admin screens
		return get_string('crondescription', 'mod_heatmap');
	}

	public function execute()
	{
		global $DB, $CFG, $SITE;
		$countryInfo = array();
		$continentInfo = array();
		$totalparticipants = 0;
		$totalcountries = 0;
		$listOfCountries = '';
		$maxvalue = 0;

		$country = get_string_manager()->get_list_of_countries();
		$continents = array('EU', 'AF', 'AS', 'NA', 'SA', 'OC');
		// Source : http://dev.maxmind.com/geoip/legacy/codes/country_continent/
		$countryByContinent = array('A1' => '--', 'A2' => '--', 'AD' => 'EU', 'AE' => 'AS', 'AF' => 'AS', 'AG' => 'NA', 'AI' => 'NA', 'AL' => 'EU', 'AM' => 'AS', 'AN' => 'NA', 'AO' => 'AF', 'AP' => 'AS', 'AQ' => 'AN', 'AR' => 'SA', 'AS' => 'OC', 'AT' => 'EU', 'AU' => 'OC', 'AW' => 'NA', 'AX' => 'EU', 'AZ' => 'AS', 'BA' => 'EU', 'BB' => 'NA', 'BD' => 'AS', 'BE' => 'EU', 'BF' => 'AF', 'BG' => 'EU', 'BH' => 'AS', 'BI' => 'AF', 'BJ' => 'AF', 'BL' => 'NA', 'BM' => 'NA', 'BN' => 'AS', 'BO' => 'SA', 'BR' => 'SA', 'BS' => 'NA', 'BT' => 'AS', 'BV' => 'AN', 'BW' => 'AF', 'BY' => 'EU', 'BZ' => 'NA', 'CA' => 'NA', 'CC' => 'AS', 'CD' => 'AF', 'CF' => 'AF', 'CG' => 'AF', 'CH' => 'EU', 'CI' => 'AF', 'CK' => 'OC', 'CL' => 'SA', 'CM' => 'AF', 'CN' => 'AS', 'CO' => 'SA', 'CR' => 'NA', 'CU' => 'NA', 'CV' => 'AF', 'CX' => 'AS', 'CY' => 'AS', 'CZ' => 'EU', 'DE' => 'EU', 'DJ' => 'AF', 'DK' => 'EU', 'DM' => 'NA', 'DO' => 'NA', 'DZ' => 'AF', 'EC' => 'SA', 'EE' => 'EU', 'EG' => 'AF', 'EH' => 'AF', 'ER' => 'AF', 'ES' => 'EU', 'ET' => 'AF', 'EU' => 'EU', 'FI' => 'EU', 'FJ' => 'OC', 'FK' => 'SA', 'FM' => 'OC', 'FO' => 'EU', 'FR' => 'EU', 'FX' => 'EU', 'GA' => 'AF', 'GB' => 'EU', 'GD' => 'NA', 'GE' => 'AS', 'GF' => 'SA', 'GG' => 'EU', 'GH' => 'AF', 'GI' => 'EU', 'GL' => 'NA', 'GM' => 'AF', 'GN' => 'AF', 'GP' => 'NA', 'GQ' => 'AF', 'GR' => 'EU', 'GS' => 'AN', 'GT' => 'NA', 'GU' => 'OC', 'GW' => 'AF', 'GY' => 'SA', 'HK' => 'AS', 'HM' => 'AN', 'HN' => 'NA', 'HR' => 'EU', 'HT' => 'NA', 'HU' => 'EU', 'ID' => 'AS', 'IE' => 'EU', 'IL' => 'AS', 'IM' => 'EU', 'IN' => 'AS', 'IO' => 'AS', 'IQ' => 'AS', 'IR' => 'AS', 'IS' => 'EU', 'IT' => 'EU', 'JE' => 'EU', 'JM' => 'NA', 'JO' => 'AS', 'JP' => 'AS', 'KE' => 'AF', 'KG' => 'AS', 'KH' => 'AS', 'KI' => 'OC', 'KM' => 'AF', 'KN' => 'NA', 'KP' => 'AS', 'KR' => 'AS', 'KW' => 'AS', 'KY' => 'NA', 'KZ' => 'AS', 'LA' => 'AS', 'LB' => 'AS', 'LC' => 'NA', 'LI' => 'EU', 'LK' => 'AS', 'LR' => 'AF', 'LS' => 'AF', 'LT' => 'EU', 'LU' => 'EU', 'LV' => 'EU', 'LY' => 'AF', 'MA' => 'AF', 'MC' => 'EU', 'MD' => 'EU', 'ME' => 'EU', 'MF' => 'NA', 'MG' => 'AF', 'MH' => 'OC', 'MK' => 'EU', 'ML' => 'AF', 'MM' => 'AS', 'MN' => 'AS', 'MO' => 'AS', 'MP' => 'OC', 'MQ' => 'NA', 'MR' => 'AF', 'MS' => 'NA', 'MT' => 'EU', 'MU' => 'AF', 'MV' => 'AS', 'MW' => 'AF', 'MX' => 'NA', 'MY' => 'AS', 'MZ' => 'AF', 'NA' => 'AF', 'NC' => 'OC', 'NE' => 'AF', 'NF' => 'OC', 'NG' => 'AF', 'NI' => 'NA', 'NL' => 'EU', 'NO' => 'EU', 'NP' => 'AS', 'NR' => 'OC', 'NU' => 'OC', 'NZ' => 'OC', 'O1' => '--', 'OM' => 'AS', 'PA' => 'NA', 'PE' => 'SA', 'PF' => 'OC', 'PG' => 'OC', 'PH' => 'AS', 'PK' => 'AS', 'PL' => 'EU', 'PM' => 'NA', 'PN' => 'OC', 'PR' => 'NA', 'PS' => 'AS', 'PT' => 'EU', 'PW' => 'OC', 'PY' => 'SA', 'QA' => 'AS', 'RE' => 'AF', 'RO' => 'EU', 'RS' => 'EU', 'RU' => 'EU', 'RW' => 'AF', 'SA' => 'AS', 'SB' => 'OC', 'SC' => 'AF', 'SD' => 'AF', 'SE' => 'EU', 'SG' => 'AS', 'SH' => 'AF', 'SI' => 'EU', 'SJ' => 'EU', 'SK' => 'EU', 'SL' => 'AF', 'SM' => 'EU', 'SN' => 'AF', 'SO' => 'AF', 'SR' => 'SA', 'ST' => 'AF', 'SV' => 'NA', 'SY' => 'AS', 'SZ' => 'AF', 'TC' => 'NA', 'TD' => 'AF', 'TF' => 'AN', 'TG' => 'AF', 'TH' => 'AS', 'TJ' => 'AS', 'TK' => 'OC', 'TL' => 'AS', 'TM' => 'AS', 'TN' => 'AF', 'TO' => 'OC', 'TR' => 'EU', 'TT' => 'NA', 'TV' => 'OC', 'TW' => 'AS', 'TZ' => 'AF', 'UA' => 'EU', 'UG' => 'AF', 'UM' => 'OC', 'US' => 'NA', 'UY' => 'SA', 'UZ' => 'AS', 'VA' => 'EU', 'VC' => 'NA', 'VE' => 'SA', 'VG' => 'NA', 'VI' => 'NA', 'VN' => 'AS', 'VU' => 'OC', 'WF' => 'OC', 'WS' => 'OC', 'YE' => 'AS', 'YT' => 'AF', 'ZA' => 'AF', 'ZM' => 'AF', 'ZW' => 'AF');

		// Fetch and aggregate data
		$query = 'SELECT country, COUNT(*) AS count FROM {user} WHERE confirmed = 1 AND suspended = 0 AND country != "" GROUP BY country ORDER BY count DESC';
		$results = $DB->get_records_sql_menu($query);
		foreach ($results as $key => $value) {
			if (isset($key) && in_array($countryByContinent[$key],$continents)) {
				// Storing amMap data file
				$listOfCountries .= '      {id:"' . $key . '", value:' . $value . ', balloonText: "[[value]] participants from [[title]]"},' . chr(10);

				// transfering data into multidimensional array for later use
				$countryInfo[$key] = array('iso' => $key, 'countryname' => $country[$key], 'participants' => $value);
				if (!isset($continentInfo[$countryByContinent[$key]])) {
					$continentInfo[$countryByContinent[$key]] = array();
				}
				$continentInfo[$countryByContinent[$key]]['totalparticipants'] = !isset($continentInfo[$countryByContinent[$key]]['totalparticipants']) ? 0 + $value : $continentInfo[$countryByContinent[$key]]['totalparticipants'] + $value;
				if (!isset($continentInfo[$countryByContinent[$key]]['countries'])) {
					$continentInfo[$countryByContinent[$key]]['countries'] = array();
				}
				array_push($continentInfo[$countryByContinent[$key]]['countries'], $countryInfo[$key]);
				$totalparticipants += $value;
				if($value > $maxvalue){
					$maxvalue = $value;
				}
				$totalcountries++;
			}
		}
		$maxvalue = ceil($maxvalue/10) * 10 - 10;

		// Getting all heatmap instances and generating corresponding data
		$heatmapinstances  = $DB->get_records('heatmap');
		foreach($heatmapinstances as $instance => $heatmap) {
			$breakdowndata = '';

			$activateallcountries = ($heatmap->lockemptycountries == 1 ? 'false' : 'true');

			$header = <<<EOT
			var map;

			AmCharts.ready(function() {
				map = new AmCharts.AmMap();
				map.colorSteps = 15;
				var dataProvider = {
					mapVar: AmCharts.maps.worldLow,
					getAreasFromMap:$activateallcountries,
					areas: [

EOT;

			$footer = <<<EOV
					]
				};
				map.areasSettings = {
				autoZoom: true
			    };
			    map.dataProvider = dataProvider;
			    var valueLegend = new AmCharts.ValueLegend();
			    valueLegend.right = 10;
			    valueLegend.minValue = "0";
			    valueLegend.maxValue = "$maxvalue+";
			    map.valueLegend = valueLegend;
			    map.write("mapdiv");
			});
EOV;

			// Total number of registered participants
			$a = array(	'date' => date("F j, Y \a\\t H\hi"), 'totalparticipants' => number_format($totalparticipants),
						'totalcountries' => $totalcountries, 'sitename' => format_string($SITE->shortname),
						'timezone' => date_default_timezone_get());

			$total = '<div class="totalparticipants"><img src="' . $CFG->wwwroot . '/mod/heatmap/pix/participants.png" width="16" height="16"> ' . get_string('total', 'heatmap', $a) . '</div>' . chr(10);

			// Continent breakdown
				foreach ($continents as $continent) {
					if (isset($continentInfo[$continent])) {
						$breakdowndata .= '<ul class="toggle-view">' . chr(10);

						$a = array(	'numberOfCountries' => count($continentInfo[$continent]['countries']),
									'numberOfParticipants' => number_format($continentInfo[$continent]['totalparticipants']));

						$breakdowndata .= '<li><h3>' . get_string($continent, 'heatmap') . '</h3><span> +</span> <div>'. get_string('continentBreakdown','heatmap', $a) .'</div><div class="panel">' . chr(10);
						$breakdowndata .= '<ul>' . chr(10);
						foreach ($continentInfo[$continent]['countries'] as $currentCountry) {
							$flag = (file_exists($CFG->dirroot . '/mod/heatmap/pix/flag/' . strtolower($currentCountry['iso']) . '.png')) ? $CFG->wwwroot . '/mod/heatmap/pix/flag/' . strtolower($currentCountry['iso']) . '.png' : $CFG->wwwroot . '/mod/heatmap/pix/flag/notfound.png';
							$breakdowndata .= '<li><img src="' . $flag . '" />' . $currentCountry['countryname'] . ' => ' . number_format($currentCountry['participants']) . '</li>' . chr(10);
						}
						$breakdowndata .= '</ul></div></li></ul>' . chr(10);
					}
				}
			// Storing data back to heatmap record
			$data = array();
			$data['id']= $heatmap->id;
			$data['mapdata'] = $header . $listOfCountries . $footer;
			$data['continentbreakdown'] = $breakdowndata;
			$data['totals'] = $total;
			if ($DB->update_record('heatmap', $data)) {
				echo 'Heatmap ID=' .$heatmap->id .' updated!'.chr(10);
			} else {
				echo 'Update failed!'. chr(10);
			}
		}
	}
}
?>