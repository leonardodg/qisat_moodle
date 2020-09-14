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

	//$activities = $REPORTS->getListActivitiesBimesters($bimester_id);
	$activities = $REPORTS->getListActivitiesPM($bimester_id);


	if (!empty($activities)){

		echo html_writer::start_tag("select", array("id" => "selActivity", "class" => "form-control mb-1"));	

		foreach($activities as $activity){

			echo html_writer::tag(
				"option", 
				$activity->activity_name, 
				array(					
					"value" => $activity->id
				)
			);


		}

		echo html_writer::end_tag("select");	

		echo html_writer::tag(
			"a", 
			"add", 
			array(
				"class" => "btn btn-success float-right mb-3", 
				"id" => "btnAddACtivityToTable"
			)
		);

		//echo html_writer::tag("hr");

		echo '
			<table class="table table-striped text-center">
				<thead>
					<tr>
						<th>Subject(s)</th>
						<th>Hour(s)</th>
						<th>#</th>
					</tr>
				</thead>

				<tbody id="tbListOfActivities">
					
				</tbody>
			</table>
		';




	}
	else{
		echo html_writer::start_div("col-md-12");
		echo html_writer::tag("h5", "There is no activities for this bimester!");
		echo html_writer::end_div();
	}

	//echo "ok";


}
catch (Exception $e){

	echo "n";

}

