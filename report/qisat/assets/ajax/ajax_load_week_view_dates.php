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

	//$start = $_POST["start"];
	//$end = $_POST["end"];
	$eta_userid = $_POST["eta"];
	$admin_institution = isset($_POST["institution"]) ? $_POST["institution"] : "";

	$id = $_POST["id"];

	$colors = array("info", "warning", "success", "primary", "danger");
	

	//echo "<div style='display: none;'>" . $admin_institution . "</div>";

	/*if ($start == 9999 && $end == 9999){		
		$bimesters = $REPORTS->getListBimestersFromToday($eta_userid, $admin_institution);
	}
	else{
		$bimesters = $REPORTS->getListBimestersByPeriod($start, $end, $eta_userid, $admin_institution);
	}*/
	//$bimesters = $REPORTS->getListBimestersWeeks($id, $eta_userid, $admin_institution);

	

	

	//if (!empty($bimesters)){

		//foreach ($bimesters as $bimester_id) {
		
//var_dump($bimester_id);
			$count_colors = 0;

			$etas = $REPORTS->getListEtasBimesters($id, $admin_institution);

			$weeks = $REPORTS->getListWeeksBimesters($id);


			switch ($id) {
				case 1:
					$timestamp = strtotime(date("Y") . '-03-11');
					break;
				case 2:
					$timestamp = strtotime(date("Y") . '-05-06');
					break;
				case 3:
					$timestamp = strtotime(date("Y") . '-08-05');
					break;
				case 4:
					$timestamp = strtotime(date("Y") . '-09-30');
					break;
				default:
					# code...
					break;
			}

		
			if (!empty($etas)){

	?>
			<!--<div class="alert alert-danger" id="success-alert_week" style="display: none;">				    
			    The workload must be at least 20 and at most 25 hours per week, including planning and preparation.
			</div>-->

			<table class="table">
				<!--
				<thead>
					<tr>
						<th colspan="9">
							Bimester starting at 
							<?php 
								echo date("Y-m-d", $timestamp); 

								if ($USER->profile["fulbright_profile"] == "Program Manager"){

									/*echo html_writer::tag(
										"button", 
										"Add ETA", 
										array(
											"class" => "btn btn-success btnAddEtaToBimester float-right btnAddEtaModal", 									
											"data-toggle" => "modal", 
											"data-target" => "#modalAddEta",
											"data-bimester" => $id
										)
									);*/

								}

							?>								
							</th>
					</tr>
				</thead>
				-->
			  	<thead>
				    <tr>
				      <th scope="col">ETA</th>

				      <?php 

				      	for ($i = 1; $i <= 8; $i++){


				      		$dia_inicio = date("d", ($timestamp + (($i-1) * 86400 * 7)));

				      		$data_inicio = date("Y-m-d", ($timestamp + (($i-1) * 86400 * 7)));

				      		$mes_inicio = date("M", ($timestamp + (($i-1) * 86400 * 7)));

				      		$data_final = date("d", strtotime($data_inicio . " 5 days"));



				      		$dia_fim = date("d", ($timestamp + (($i) * 86400 * 6)));
				      		echo '
				      			<th scope="col">
				      				<span class="tooltip_fulbright">
				      					W'.$i.' 
				      					<span class="tooltip_fulbright_text">'.$mes_inicio.". ".$dia_inicio.'-'.$data_final.'</span>
			      					</span>
		      					</th>';

				      	}


				      ?>

				      
				    </tr>
			  	</thead>
			  	<tbody>

		  		<?php foreach($etas as $eta){ ?>

	  				<?php //if($eta_userid == 0 || ($eta_userid != 0 && $eta_userid == $eta->id) ) { ?>		  			

			  			<tr class="text-<?php echo $colors[$count_colors] ?>">

					      <th scope="row"><?php echo $eta->firstname . " " . $eta->lastname ?></th>

						<?php 

							$conta_semana = 1;

							foreach($weeks as $week){ 


						?>

							<td>

								<?php

						

									$eta_activities = $REPORTS->getActivityEtaWeek($week->id, $eta->id);
									$total_hours = 0;

									if (!empty($eta_activities)){

										foreach($eta_activities as $activity){ 
											$total_hours += $activity->activity_estimated;
								?>
							        	
					            			<p> 
					            				<button 
					            					type="button" 
					            					class="btn btn-info btnActivityInfo text-white" 
					            					data-toggle="modal" 
					            					data-target="#modalInfoActivity" 
					            					data-id="<?php echo $activity->id ?>" 
					            					data-eta="<?php echo $eta->id ?>" 
					            					data-eta-online="<?php echo $eta_userid ?>" 
					            					data-week="<?php echo $week->id ?>" 
					            					data-bimester-id="<?php echo $id ?>"
					            					data-week-conta="<?php echo $conta_semana ?>">
					            					<?php echo $activity->activity_name . " (" . $activity->activity_estimated . ")" ?>
				            					</button> 
				            				</p>				          	

						  			<?php } ?>

						  				<p> 
				            				<button type="button" class="btn btn-primary">
				            					Total Hours: <span class="btn-default p-1 horas_total_semana"><?php echo $total_hours ?></span>
			            					</button> 
			            				</p>

			            				<?php 

			            					if (
				            					$USER->profile["fulbright_profile"] != "Student"
												&&
												$USER->profile["fulbright_profile"] != "Grantees"
											){


			            						if($eta_userid == 0) { 

				            						echo '<p align="text-center">';

						  							$relatorio = $REPORTS->getEtaSemanalReport($eta->id, $week->id, $id);

						  							//var_dump($relatorio);

						  							if (!empty($relatorio)){
						  								echo '
						  									<a 
						  										href="#" 
						  										class="btnModalEtaSendReportInfo" 
						  										data-toggle="modal" 
						  										data-target="#modalEtaSendReportInfo" 
						  										data-id="'. $relatorio->id .'"> See Report 
					  										</a>';
						  							}
						  							else{
						  								echo '<a href="#" class="text-muted"> No Report Sent</a>';
						  							}

						  							echo '</p>';


						  						}else{ 

						  							echo '<p align="text-center">';

						  							$relatorio = $REPORTS->getEtaSemanalReport($eta_userid, $week->id, $id);

						  							//var_dump($relatorio);

						  							if (!empty($relatorio)){
						  								echo '
						  									<a 
						  										href="#" 
						  										class="btnModalEtaSendReportInfo" 
						  										data-toggle="modal" 
						  										data-target="#modalEtaSendReportInfo" 
						  										data-id="'. $relatorio->id .'"> See Report 
					  										</a>';
						  							}
						  							else{

						  								echo '
						  									<a 
						  										href="#" 
						  										class="btnModalEtaSendReport" 
						  										data-toggle="modal" 
						  										data-target="#modalEtaSendReport" 
						  										data-id="'. $id .'" 
						  										data-eta="'. $eta->id .'" 
						  										data-week="'. $week->id .'"> Send Report 
					  										</a>';

						  							}

						  							echo '</p>';
												}
		            							

			            					}


			            					

										?>

					  			<?php }else{ ?>

					  					<div class="card text-center bg-light mb-3">
							          		
							          		<div class="card-body">
							            		<p class="card-text">No activity</p>			            		
							          		</div>
							        	</div>

				  				<?php } ?>

				  				<?php if($eta_userid == 0) { ?>

					  				<p>
					  					<?php

					  						if ($USER->profile["fulbright_profile"] != "Admin"){

						  						echo html_writer::tag(
													"button", 
													"+ hours", 
													array(
														"class" => "btn btn-default btn-sm float-right btnAddActivityToEta", 									
														"data-toggle" => "modal", 
														"data-target" => "#modalAddActivity",
														"data-bimester" => $id,
														"data-week" => $week->id,
														"data-eta" => $eta->id,
														"data-etanome" => $eta->firstname . " " . $eta->lastname,
														"data-semana" => $conta_semana
													)
												); 

											}

										?>
									</p>  

								<?php } ?>

					     	</td>

						<?php 

							$conta_semana++; 

							} 

						?>

						</tr>



						<?php 
							$count_colors++;

							if ($count_colors > 4){
								$count_colors = 0;
							}
						?>

	<?php 
					//}

				} 

			}
			/*else{

				echo html_writer::start_div("col-md-12 text-center m-5");

				echo html_writer::tag("h5", "There is no ETAs for this bimester (". date("Y-m-d", $timestamp) . ")");

				echo html_writer::tag(
					"button", 
					"Click here to add ETAs", 
					array(
						"class" => "btn btn-success btnAddEtaModal", 						
						"data-toggle" => "modal", 
						"data-target" => "#modalAddEta",
						"data-bimester" => $id
					)
				);

				echo html_writer::end_div();

				//echo "<h4>No ETAs for this bimester</h4>";
			}*/

		//}


	/*}
	else{

		echo "No bimesters found for this period.";

	}*/



}
catch (Exception $e){

	echo "n";

}

