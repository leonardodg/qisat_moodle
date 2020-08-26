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

	$bimester_id = $_POST["bimesterIdEtaActivity"];
	
	$week_id = $_POST["weekIdEtaActivity"];
	
	$eta_userid = $_POST["etaIdActivity"];
	
	$activities = $_POST["activity_eta_bimester"];	
	

	if (!empty($activities)){

		foreach ($activities as $activity) {


			$rs = $DB->get_records_sql('
				SELECT *
				FROM {report_coursestats_ea} 
				WHERE
					eta_userid = ' . $eta_userid . 					
					' AND week_id = ' . $week_id . 
					' AND activity_id = ' . $activity
			);


			if (empty($rs)){
				//echo "teste";
				$new_eta_activity = new stdClass();
				$new_eta_activity->eta_userid = $eta_userid;
				$new_eta_activity->activity_id = $activity;
				$new_eta_activity->week_id = $week_id;
				$new_eta_activity->activity_estimated = $_POST["activity_estimated_".$activity];
				$new_eta_activity->activity_executed = 0;
				$new_eta_activity->timecreated = time();
				$lastinsertid = $DB->insert_record("report_coursestats_ea", $new_eta_activity);
				/*$new_eta_bimester = new stdClass();
				$new_eta_bimester->eta_userid = $eta;
				$new_eta_bimester->bimester_id = $bimester_id;
				$new_eta_bimester->timecreated = time();
				$lastinsertid = $DB->insert_record("report_coursestats_ebi", $new_eta_bimester);*/		
			}

			//echo $activity_estimated = $_POST["activity_estimated_".$activity];

			
		}

	}

	echo "ok";


}
catch (Exception $e){

	echo "n" . $e->getMessage();

}

