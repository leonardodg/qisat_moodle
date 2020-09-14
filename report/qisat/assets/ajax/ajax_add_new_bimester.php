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
 * Strings for component 'report_coursestats_fulbright', language 'en'
 *
 * @package    report
 * @subpackage coursestats_fulbright
 * @copyright  2018 Ricardo Wierzynski
 * @license   	http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../../config.php');

try {

	$bimester_start_date = $_POST["starting_date"];
	$etas = $_POST["etas"];

	$new_bimester = new stdClass();
	$new_bimester->pm_userid = $USER->id;
	$new_bimester->institution = $USER->profile["fulbright_institution"];
	$new_bimester->bimester_start_date = $bimester_start_date;
	$new_bimester->timecreated = time();

	//var_dump($new_bimester);
	$bimester_lastinsertid = $DB->insert_record("report_coursestats_bimester", $new_bimester);		

	for($i = 1; $i <= 8; $i++){

		//echo "Week " .$i . ": " . date("Y-m-d", strtotime($bimester_start_date . " +" . $i . " week")) . "<br />";
		$new_week = new stdClass();
		$new_week->bimester_id = $bimester_lastinsertid;
		$new_week->week_number = $i;
		$new_week->timecreated = time();
		$lastinsertid = $DB->insert_record("report_coursestats_weeks", $new_week);		


	}

	if (!empty($etas)){

		foreach ($etas as $eta) {
			
			if (!empty($eta) && $eta != ""){

				$new_eta_bimester = new stdClass();
				$new_eta_bimester->eta_userid = $eta;
				$new_eta_bimester->bimester_id = $bimester_lastinsertid;
				$new_eta_bimester->timecreated = time();
				$lastinsertid = $DB->insert_record("report_coursestats_ebi", $new_eta_bimester);	

			}
			
		}

	}

	echo "ok";


}
catch (Exception $e){

	echo "n";

}

