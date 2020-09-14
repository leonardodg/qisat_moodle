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
require_once('../../classes/reports.php');

try {

	$bimester_id = $_POST["id"];
	
	$REPORTS = new reports_fulbright();

	$eta_list = $REPORTS->get_list_eta();

	$array_etas_in_bimester = array();

	$rs = $DB->get_records_sql('
		SELECT eta_userid
		FROM {report_coursestats_ebi} 
		WHERE
			bimester_id = ' . $bimester_id			
	);

	

	if (!empty($rs)){
		foreach ($rs as $eta_in_bimester) {
			$array_etas_in_bimester = $array_etas_in_bimester + array($eta_in_bimester->eta_userid);
		}
	}

	
	if (!empty($eta_list)){
		foreach($eta_list as $eta){

			$checked = (in_array($eta->id, $array_etas_in_bimester)) ? "checked" : "";

			echo html_writer::start_div("form-group mb-1");	
			echo html_writer::tag("input", "", array(
				"class" => "chkETA", 
				"type" => "checkbox", 
				"name" => "activity_eta[]", 
				"value" => $eta->id,
				$checked => $checked
			));
			echo html_writer::tag("label", $eta->firstname . " " . $eta->lastname, array("class" => "ml-2"));
			echo html_writer::end_div();
		}
	}
	else{
		echo html_writer::start_div("col-md-12");
		echo html_writer::tag("h5", "There is no ETA registered!");
		echo html_writer::end_div();
	}

	//echo "ok";


}
catch (Exception $e){

	echo "n";

}

