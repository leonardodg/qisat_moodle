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

	$report_id = $_POST["id"];
		
	$REPORTS = new reports_fulbright();

	$report = $REPORTS->getEtaSemanalReportInfo($report_id);

	if (!empty($report)){		

		$total = $report->ns_letras_licenciatura + $report->ns_letras_bacharelado + $report->ns_other_courses + $report->ns_not_enrolled;

		echo '
			<div class="card">
			  	<div class="card-body">			    	
			    	<p class="card-text"><strong>Total weekly hours of classes and activities:</strong> '.$report->classroom_time.'</p>
			    	<p class="card-text"><strong>Description of each class and activity:</strong> '.$report->classroom_time_description.'</p>
			    	<p class="card-text"><strong>Number of students</strong></p>
			    	<p class="card-text"><strong>Letras Licenciatura:</strong> '.$report->ns_letras_licenciatura.'</p>
			    	<p class="card-text"><strong>Letras Bacharelado:</strong> '.$report->ns_letras_bacharelado.'</p>
			    	<p class="card-text"><strong>ISF:</strong> '.$report->ns_other_courses.'</p>
			    	<p class="card-text"><strong>Others:</strong> '.$report->ns_not_enrolled.'</p>
			    	<p class="card-text"><strong>Total of students:</strong> '.$total.'</p>
			    	<p class="card-text"><strong>Total weekly hours of planning and preparation:</strong> '.$report->online_time.'</p>
			    	<p class="card-text"><strong>Description of planning and preparation:</strong> '.$report->online_time_description.'</p>			    	
			  	</div>
			</div>
			
		';

	}
	else{
		echo html_writer::start_div("col-md-12");
		echo html_writer::tag("h5", "There is no information for this report!");
		echo html_writer::end_div();
	}

}
catch (Exception $e){

	echo "n";

}

