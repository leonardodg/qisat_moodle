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

	$activity_id = $_POST["id"];
	$eta_id = $_POST["eta_id"];
	$week_id = $_POST["week_id"];
	
	$REPORTS = new reports_fulbright();

	$activity = $REPORTS->getActivityInfo($activity_id, $eta_id);




	if (!empty($activity)){

		$array_semanas = array(
			1 => "March & April", 
			2 => "May & June", 
			3 => "August & September", 
			4 => "October & November"
		);


		foreach($activity as $act){

			$vd = explode(",", $act->activity_location);

			$datas = "";

			foreach ($vd as $item) {
				$datas .= $array_semanas[$item] . ", ";
			}

			echo '
				<div class="card">
				  	<div class="card-body">
				    	<h5 id="activity_name_info" class="card-title">'.$act->activity_name.'</h5>			    	
				    	<p class="card-text"><strong>Syllabus:</strong> <span id="syllabus_info">'.$act->activity_syllabus.'</span></p>

				    	<p class="card-text"><strong>Dates:</strong> '.$datas.'</p>
				    	
				    	<p class="card-text"><strong>Students enrolled in:</strong> '.$act->activity_type_of_program.'</p>
				    	<p class="card-text"><strong>Students English Level:</strong> '.$act->activity_themes.'</p>
				    	<p class="card-text"><strong>Estimated time of execution:</strong> '.$act->activity_estimated.'</p>
				    	<p class="card-text"><strong>Executed time:</strong> '.$act->activity_executed.'</p>			    	
				  	</div>
				</div>
				<input type="hidden" id="activityDeleteEtaActivityBimester" value="'.$activity_id.'" />
				<input type="hidden" id="activityEditEtaActivityBimester" value="'.$activity_id.'" />
				<input type="hidden" id="etaDeleteEtaActivityWeek" value="'.$week_id.'" />
				<input type="hidden" id="etaDeleteEtaActivityBimester" value="'.$eta_id.'" />
			';
		}


	}
	else{
		echo html_writer::start_div("col-md-12");
		echo html_writer::tag("h5", "There is no information for this activity!");
		echo html_writer::end_div();
	}

	//echo "ok";


}
catch (Exception $e){

	echo "n";

}

