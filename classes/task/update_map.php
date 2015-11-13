<?php
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
		global $DB, $CFG;

		$country = get_string_manager()->get_list_of_countries();
		$continent = array('EU' => 'Europe', 'AF' => 'Africa', 'AS' => 'Asia', 'NA' => 'North-America', 'SA' => 'South-America', 'OC' => 'Oceania');
		// Source : http://dev.maxmind.com/geoip/legacy/codes/country_continent/
		$countryByContinent = array('A1' => '--', 'A2' => '--', 'AD' => 'EU', 'AE' => 'AS', 'AF' => 'AS', 'AG' => 'NA', 'AI' => 'NA', 'AL' => 'EU', 'AM' => 'AS', 'AN' => 'NA', 'AO' => 'AF', 'AP' => 'AS', 'AQ' => 'AN', 'AR' => 'SA', 'AS' => 'OC', 'AT' => 'EU', 'AU' => 'OC', 'AW' => 'NA', 'AX' => 'EU', 'AZ' => 'AS', 'BA' => 'EU', 'BB' => 'NA', 'BD' => 'AS', 'BE' => 'EU', 'BF' => 'AF', 'BG' => 'EU', 'BH' => 'AS', 'BI' => 'AF', 'BJ' => 'AF', 'BL' => 'NA', 'BM' => 'NA', 'BN' => 'AS', 'BO' => 'SA', 'BR' => 'SA', 'BS' => 'NA', 'BT' => 'AS', 'BV' => 'AN', 'BW' => 'AF', 'BY' => 'EU', 'BZ' => 'NA', 'CA' => 'NA', 'CC' => 'AS', 'CD' => 'AF', 'CF' => 'AF', 'CG' => 'AF', 'CH' => 'EU', 'CI' => 'AF', 'CK' => 'OC', 'CL' => 'SA', 'CM' => 'AF', 'CN' => 'AS', 'CO' => 'SA', 'CR' => 'NA', 'CU' => 'NA', 'CV' => 'AF', 'CX' => 'AS', 'CY' => 'AS', 'CZ' => 'EU', 'DE' => 'EU', 'DJ' => 'AF', 'DK' => 'EU', 'DM' => 'NA', 'DO' => 'NA', 'DZ' => 'AF', 'EC' => 'SA', 'EE' => 'EU', 'EG' => 'AF', 'EH' => 'AF', 'ER' => 'AF', 'ES' => 'EU', 'ET' => 'AF', 'EU' => 'EU', 'FI' => 'EU', 'FJ' => 'OC', 'FK' => 'SA', 'FM' => 'OC', 'FO' => 'EU', 'FR' => 'EU', 'FX' => 'EU', 'GA' => 'AF', 'GB' => 'EU', 'GD' => 'NA', 'GE' => 'AS', 'GF' => 'SA', 'GG' => 'EU', 'GH' => 'AF', 'GI' => 'EU', 'GL' => 'NA', 'GM' => 'AF', 'GN' => 'AF', 'GP' => 'NA', 'GQ' => 'AF', 'GR' => 'EU', 'GS' => 'AN', 'GT' => 'NA', 'GU' => 'OC', 'GW' => 'AF', 'GY' => 'SA', 'HK' => 'AS', 'HM' => 'AN', 'HN' => 'NA', 'HR' => 'EU', 'HT' => 'NA', 'HU' => 'EU', 'ID' => 'AS', 'IE' => 'EU', 'IL' => 'AS', 'IM' => 'EU', 'IN' => 'AS', 'IO' => 'AS', 'IQ' => 'AS', 'IR' => 'AS', 'IS' => 'EU', 'IT' => 'EU', 'JE' => 'EU', 'JM' => 'NA', 'JO' => 'AS', 'JP' => 'AS', 'KE' => 'AF', 'KG' => 'AS', 'KH' => 'AS', 'KI' => 'OC', 'KM' => 'AF', 'KN' => 'NA', 'KP' => 'AS', 'KR' => 'AS', 'KW' => 'AS', 'KY' => 'NA', 'KZ' => 'AS', 'LA' => 'AS', 'LB' => 'AS', 'LC' => 'NA', 'LI' => 'EU', 'LK' => 'AS', 'LR' => 'AF', 'LS' => 'AF', 'LT' => 'EU', 'LU' => 'EU', 'LV' => 'EU', 'LY' => 'AF', 'MA' => 'AF', 'MC' => 'EU', 'MD' => 'EU', 'ME' => 'EU', 'MF' => 'NA', 'MG' => 'AF', 'MH' => 'OC', 'MK' => 'EU', 'ML' => 'AF', 'MM' => 'AS', 'MN' => 'AS', 'MO' => 'AS', 'MP' => 'OC', 'MQ' => 'NA', 'MR' => 'AF', 'MS' => 'NA', 'MT' => 'EU', 'MU' => 'AF', 'MV' => 'AS', 'MW' => 'AF', 'MX' => 'NA', 'MY' => 'AS', 'MZ' => 'AF', 'NA' => 'AF', 'NC' => 'OC', 'NE' => 'AF', 'NF' => 'OC', 'NG' => 'AF', 'NI' => 'NA', 'NL' => 'EU', 'NO' => 'EU', 'NP' => 'AS', 'NR' => 'OC', 'NU' => 'OC', 'NZ' => 'OC', 'O1' => '--', 'OM' => 'AS', 'PA' => 'NA', 'PE' => 'SA', 'PF' => 'OC', 'PG' => 'OC', 'PH' => 'AS', 'PK' => 'AS', 'PL' => 'EU', 'PM' => 'NA', 'PN' => 'OC', 'PR' => 'NA', 'PS' => 'AS', 'PT' => 'EU', 'PW' => 'OC', 'PY' => 'SA', 'QA' => 'AS', 'RE' => 'AF', 'RO' => 'EU', 'RS' => 'EU', 'RU' => 'EU', 'RW' => 'AF', 'SA' => 'AS', 'SB' => 'OC', 'SC' => 'AF', 'SD' => 'AF', 'SE' => 'EU', 'SG' => 'AS', 'SH' => 'AF', 'SI' => 'EU', 'SJ' => 'EU', 'SK' => 'EU', 'SL' => 'AF', 'SM' => 'EU', 'SN' => 'AF', 'SO' => 'AF', 'SR' => 'SA', 'ST' => 'AF', 'SV' => 'NA', 'SY' => 'AS', 'SZ' => 'AF', 'TC' => 'NA', 'TD' => 'AF', 'TF' => 'AN', 'TG' => 'AF', 'TH' => 'AS', 'TJ' => 'AS', 'TK' => 'OC', 'TL' => 'AS', 'TM' => 'AS', 'TN' => 'AF', 'TO' => 'OC', 'TR' => 'EU', 'TT' => 'NA', 'TV' => 'OC', 'TW' => 'AS', 'TZ' => 'AF', 'UA' => 'EU', 'UG' => 'AF', 'UM' => 'OC', 'US' => 'NA', 'UY' => 'SA', 'UZ' => 'AS', 'VA' => 'EU', 'VC' => 'NA', 'VE' => 'SA', 'VG' => 'NA', 'VI' => 'NA', 'VN' => 'AS', 'VU' => 'OC', 'WF' => 'OC', 'WS' => 'OC', 'YE' => 'AS', 'YT' => 'AF', 'ZA' => 'AF', 'ZM' => 'AF', 'ZW' => 'AF');

		$header = <<<EOT
			var map;

			AmCharts.ready(function() {
				map = new AmCharts.AmMap();
				map.colorSteps = 15;
				var dataProvider = {
					mapVar: AmCharts.maps.worldLow,
					getAreasFromMap:true,
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
			    valueLegend.minValue = "50";
			    valueLegend.maxValue = "3000+";
			    map.valueLegend = valueLegend;
			    map.write("mapdiv");
			});
EOV;

		$query = 'SELECT country, COUNT(*) AS count FROM mdl_user WHERE confirmed = 1 AND suspended = 0 AND country != "" GROUP BY country ORDER BY count DESC';
		$results = $DB->get_records_sql_menu($query);
		$countryInfo = array();
		$continentInfo = array();
		$totalparticipants = 0;
		$totalcountries = 0;

		$fr = fopen($CFG->dirroot . '/mod/heatmap/data/data.js', 'w');
		fputs($fr, $header . chr(10));
		foreach ($results as $key => $value) {
			if (isset($key) && array_key_exists($countryByContinent[$key],$continent)) {
				// Storing amMap data file
				fputs($fr, utf8_encode('      {id:"' . $key . '", value:' . $value . ', balloonText: "[[value]] participants from [[title]]"},' . chr(10)));

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
				$totalcountries++;
			}
		}
		fputs($fr, $footer . chr(10));
		fclose($fr);

		// Storing country breakdown
		$fc = fopen($CFG->dirroot . '/mod/heatmap/data/breakdown.html', 'w');
		$a = array('date' => date("F j, Y \a\\t H\hi"), 'totalparticipants' => number_format($totalparticipants), 'totalcountries' => $totalcountries);
		fputs($fc, '<div class="totalparticipants"><img src="'.$CFG->wwwroot.'/mod/heatmap/pix/participants.png" width="16" height="16"> '.get_string('displaytotal', 'heatmap', $a).'</div>' . chr(10));
		foreach ($continent as $iso2 => $continentName) {
			if(isset($continentInfo[$iso2])) {
				fputs($fc, '<ul class="toggle-view">' . chr(10));
				$numberofcountries = count($continentInfo[$iso2]['countries']);
				fputs($fc, '<li><h3>' . $continentName . '</h3><span> +</span> <div>' . number_format($continentInfo[$iso2]['totalparticipants']) . ' participants from ' . $numberofcountries . ' countries</div><div class="panel">' . chr(10));
				fputs($fc, '<ul>' . chr(10));
				foreach ($continentInfo[$iso2]['countries'] as $currentCountry) {
					$flag = (file_exists($CFG->dirroot . '/mod/heatmap/pix/flag/' . strtolower($currentCountry['iso']) . '.png')) ? $CFG->wwwroot . '/mod/heatmap/pix/flag/' . strtolower($currentCountry['iso']) . '.png' : $CFG->wwwroot . '/mod/heatmap/pix/flag/notfound.png';
					fputs($fc, '<li><img src="' . $flag . '" />' . $currentCountry['countryname'] . ' => ' . number_format($currentCountry['participants']) . '</li>' . chr(10));
				}
				fputs($fc, '</ul></div></li>' . chr(10));
			}
		}
		fputs($fc, '</ul>' . chr(10));
		fclose($fc);
	}
}
?>