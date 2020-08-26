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

	$bimester_id = $_POST["id"];
	$etas = $REPORTS->getListEtasBimesters($bimester_id);
	$weeks = $REPORTS->getListWeeksBimesters($bimester_id);
	
	if (!empty($etas)){

?>

	<table class="table">
	  	<thead>
		    <tr>
		      <th scope="col">ETA</th>
		      <th scope="col">W1</th>
		      <th scope="col">W2</th>
		      <th scope="col">W3</th>
		      <th scope="col">W4</th>
		      <th scope="col">W5</th>
		      <th scope="col">W6</th>
		      <th scope="col">W7</th>
		      <th scope="col">W8</th>
		    </tr>
	  	</thead>
	  	<tbody>

  		<?php foreach($etas as $eta){ ?>

  			<tr>
		      <th scope="row"><?php echo $eta->firstname . " " . $eta->lastname ?></th>
		      
		    

			<?php foreach($weeks as $week){ ?>

				<td>

					<?php

						$eta_activities = $REPORTS->getActivityEtaWeek($week->id, $eta->id);
						$total_hours = 0;

						if (!empty($eta_activities)){

							foreach($eta_activities as $activity){ 
								$total_hours += $activity->activity_estimated;
					?>
				        	
		            			<p> 
		            				<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalInfoActivity">
		            					<?php echo $activity->activity_name . " (" . $activity->activity_estimated . ")" ?>
	            					</button> 
	            				</p>				          	

			  			<?php } ?>

			  				<p> 
	            				<button type="button" class="btn btn-primary">
	            					Total Hours: <span class="btn-default p-1"><?php echo $total_hours ?></span>
            					</button> 
            				</p>

			  				<p align="text-center"><a href="#"> See Report </a></p>

		  			<?php }else{ ?>

		  					<div class="card text-center bg-light mb-3">
				          		
				          		<div class="card-body">
				            		<p class="card-text">No activity</p>			            		
				          		</div>
				        	</div>

	  				<?php } ?>

	  				<p>
	  					<?php

	  						echo html_writer::tag(
								"button", 
								"+ activity", 
								array(
									"class" => "btn btn-default btn-sm float-right btnAddActivityToEta", 									
									"data-toggle" => "modal", 
									"data-target" => "#modalAddActivity",
									"data-bimester" => $bimester_id,
									"data-week" => $week->id,
									"data-eta" => $eta->id
								)
							); 

						?>
					</p>  

		     	</td>

			<?php } ?>

			</tr>

<?php 
		} 

	}
	else{

		echo html_writer::start_div("col-md-12 text-center mt-5");

		echo html_writer::tag("h5", "There is no ETAs for this bimester");

		echo html_writer::tag(
			"button", 
			"Click here to add ETAs", 
			array(
				"class" => "btn btn-success btnAddEtaModal", 
				
				"data-toggle" => "modal", 
				"data-target" => "#modalAddEta",
				"data-bimester" => $bimester_id
			)
		);

		echo html_writer::end_div();

		//echo "<h4>No ETAs for this bimester</h4>";
	}


}
catch (Exception $e){

	echo "n";

}

