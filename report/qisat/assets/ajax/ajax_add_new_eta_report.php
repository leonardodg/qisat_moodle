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

$REPORTS = new reports_fulbright();

try {

	$new_report = new stdClass();
	$new_report->eta_userid = $USER->id;
	$new_report->date = date('Y-m-d');
	$new_report->classroom_time = $_POST["classroom_time"];
	$new_report->classroom_time_description = $_POST["classroom_time_description"];
	$new_report->online_time = $_POST["online_time"];
	$new_report->online_time_description = $_POST["online_time_description"];
	$new_report->institution = $USER->profile["fulbright_institution"];

	$new_report->ns_letras_licenciatura = $_POST["ns_letras_licenciatura"];
	$new_report->ns_letras_bacharelado = $_POST["ns_letras_bacharelado"];
	$new_report->ns_other_courses = $_POST["ns_other_courses"];
	$new_report->ns_not_enrolled = $_POST["ns_not_enrolled"];

	$new_report->week_id = $_POST["week_id"];
	$new_report->bimester_id = $_POST["bimester_id"];

	$new_report->approved = "pending";
	$new_report->timecreated = time();

	

	//$result = $REPORTS->saveNewSemanalReport($new_report);
	
	if ($REPORTS->saveNewSemanalReport($new_report) != 0){
		echo "ok";
	}
	else{
		echo "nok";
	}


}
catch (Exception $e){

	echo "n";

}

