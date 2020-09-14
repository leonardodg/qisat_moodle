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

	$bimester_id = $_POST["id"];
	$etas = $_POST["etas"];	

	if (!empty($etas)){

		foreach ($etas as $eta) {

			$rs = $DB->get_records_sql('
				SELECT *
				FROM {report_coursestats_ebi} 
				WHERE
					eta_userid = ' . $eta . ' AND bimester_id = ' . $bimester_id			
			);

			if (empty($rs)){
				$new_eta_bimester = new stdClass();
				$new_eta_bimester->eta_userid = $eta;
				$new_eta_bimester->bimester_id = $bimester_id;
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

