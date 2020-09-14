<?php

/** 
 *
 * @package    report
 * @subpackage qfeedback
 * @copyright  2020 Ricardo Wierzynski 
 */

defined('MOODLE_INTERNAL') || die();

class ncewebapps {	

	public function get_profile_type($userid, $instituicao = 'fulbright_profile'){

		global $DB;		
		
		$rs = $DB->get_records_sql('
				SELECT uid.data AS ROLE

				FROM {user_info_data} uid				

				WHERE
					uid.userid = '.$userid.' AND 
					uid.fieldid IN (SELECT id FROM {user_info_field} WHERE shortname = :profile)
			', 			
			array('profile' => $instituicao)
		);

		return $rs;

	}

	public function get_bimesters(){

		global $DB;		
		
		$rs = $DB->get_records_sql('
				SELECT *
				FROM {report_coursestats_bimester}
				WHERE
					YEAR(bimester_start_date) = \''.date("Y").'\'
			'
		);

		return $rs;

	}

	public function getActivityInfoEdit($activity_id){

		global $DB;		
		
		$rs = $DB->get_records_sql('
			SELECT 
				a.*
			FROM {report_coursestats_pma} a
			WHERE a.id = ' . $activity_id . ' LIMIT 1'
		);		

		return $rs;

	}


	public function getListCoursesByCategory($institution = ""){

		global $DB;		

		if (!empty($institution)){
			$rs = $DB->get_records_sql('
				SELECT c.* 
				FROM {course} c 	
				JOIN {course_categories} ct ON ct.id = c.category
				WHERE
					ct.name = \''.$institution.'\' AND c.visible = 1 ORDER BY c.id DESC'
			);					
		}
		else{
			$rs = $DB->get_records_sql('
				SELECT c.* 
				FROM {course} c 					
				WHERE
					c.visible = 1 ORDER BY c.id DESC'
			);		
		}

		return $rs;

	}

	/*
		SELECT DISTINCT
			q.course,
			q.name,
			quiza.userid,
			quiza.quiz,
			quiza.id AS quizattemptid,
			quiza.attempt,
			quiza.sumgrades,
			qu.preferredbehaviour,
			qa.slot,
			qa.behaviour,
			qa.questionid,
			qa.questionsummary,
			qa.rightanswer,
			qa.responsesummary

		FROM mdl_quiz_attempts quiza
		JOIN mdl_quiz q ON q.id=quiza.quiz
		JOIN mdl_question_usages qu ON qu.id = quiza.uniqueid
		JOIN mdl_question_attempts qa ON qa.questionusageid = qu.id

		WHERE q.id = 1

		ORDER BY quiza.userid, quiza.attempt, qa.slot
	*/

	public function get_list_quiz_by_course($courseid){
		global $DB;
		$rs = $DB->get_records_sql('
			SELECT q.* 
			FROM {quiz} q
			WHERE
				q.course = \''.$courseid.'\'' 
		);	
		return $rs;
	}

	public function get_list_questions_by_quiz($quizid){
		global $DB;
		$rs = $DB->get_records_sql('
			SELECT mqs.slot as ordem, mq.id as id_questao, mq.name as nome_questao, t.rawname as tag
			FROM mdl_quiz_slots mqs
			JOIN mdl_question mq on mq.id = mqs.questionid
			JOIN mdl_tag_instance ti on ti.itemid = mqs.questionid and ti.component = \'core_question\' and ti.itemtype = \'question\'
			JOIN mdl_tag t on t.id = ti.tagid		
			WHERE mqs.quizid = \''.$quizid.'\'
			ORDER BY mqs.slot'
		);	
		return $rs;
	}

	public function get_count_questions_by_quiz_by_tag($quizid){
		global $DB;
		$rs = $DB->get_records_sql('
			SELECT DISTINCT
				t.rawname AS name, count(qa.questionid) AS total
			FROM mdl_quiz_attempts quiza
			JOIN mdl_quiz q ON q.id=quiza.quiz
			JOIN mdl_question_usages qu ON qu.id = quiza.uniqueid
			JOIN mdl_question_attempts qa ON qa.questionusageid = qu.id
			JOIN mdl_question mq on mq.id = qa.questionid
			join mdl_tag_instance ti on ti.itemid = qa.questionid and ti.component = \'core_question\' and ti.itemtype = \'question\'
			join mdl_tag t on t.id = ti.tagid
			WHERE q.id = \''.$quizid.'\'			
			GROUP BY t.name'
		);	
		return $rs;
	}

	public function get_information_about_course($id){
		global $DB;
		return $DB->get_record("course", array("id" => $id));
	}

	public function get_attempt_information($quizid, $user = ""){
		global $DB, $USER;
		$user = (empty($user)) ? $USER->id : $user;		
		$rs = $DB->get_record_sql('SELECT * FROM mdl_quiz_attempts WHERE quiz = \''.$quizid.'\' AND userid = \''.$user.'\' AND state = \'finished\' ORDER BY id DESC');	
		return $rs;
	}

	public function get_attempt_answer($questionusage, $questionid){
		global $DB;
		$rs = $DB->get_records_sql('SELECT * FROM mdl_question_attempts WHERE questionusageid = \''.$questionusage.'\' AND questionid = \''.$questionid.'\'');	
		return $rs;
	}

	public function get_nota_geral_questionario($quizid, $user = ""){
		global $DB, $USER;
		$user = (empty($user)) ? $USER->id : $user;		
		$rs = $DB->get_record_sql('SELECT sumgrades FROM mdl_quiz_attempts WHERE quiz = \''.$quizid.'\' AND userid = \''.$user.'\'');	
		return $rs;
	}

	public function get_nota_geral_questionario_ordem($quizid, $ordem = 1){
		global $DB, $USER;
		$ordem = ($ordem == 1) ? " ORDER BY sumgrades DESC LIMIT 1" : " ORDER BY sumgrades ASC LIMIT 1";		
		$rs = $DB->get_record_sql('SELECT sumgrades FROM mdl_quiz_attempts WHERE quiz = \''.$quizid.'\'' . $ordem);	
		return $rs;
	}

	public function get_ranking_geral_questionario($quizid, $ordem = 1){
		global $DB;
		$ordem = ($ordem == 1) ? " ORDER BY sumgrades DESC" : " ORDER BY sumgrades ASC";
		$rs = $DB->get_records_sql('SELECT userid, sumgrades FROM mdl_quiz_attempts WHERE quiz = \''.$quizid.'\'' . $ordem);	
		return $rs;
	}

	public function get_melhores_notas_por_materia($ids){
		global $DB;				
		$rs = $DB->get_record_sql('
			SELECT DISTINCT questionusageid, count(id) AS nota 
			FROM {question_attempts} 
			WHERE questionid IN ('.substr($ids, 0, -1).') AND rightanswer = responsesummary GROUP BY questionusageid ORDER BY nota DESC LIMIT 1
		');
		return $rs;
	}

	public function get_piores_notas_por_materia($ids){
		global $DB;
		$rs = $DB->get_record_sql('
			SELECT DISTINCT 
				questionusageid,
				COUNT(id) AS nota
			FROM {question_attempts}
			WHERE
					questionid IN ('.substr($ids, 0, -1).') 
				AND rightanswer <> responsesummary 
				AND (
					   UPPER(responsesummary) NOT LIKE \'%NADA%\' 
					OR UPPER(responsesummary) NOT LIKE \'%BRANCO%\' 
					OR UPPER(responsesummary) NOT LIKE \'%BRANCA%\' 
					OR UPPER(responsesummary) NOT LIKE \'%NDA%\'
					OR UPPER(responsesummary) NOT LIKE \'%EM BRANCO%\'
					OR UPPER(responsesummary) NOT LIKE \'%EM BRANCA%\'
					OR UPPER(responsesummary) NOT LIKE \'%SEM RESPOSTA%\'
					OR UPPER(responsesummary) NOT LIKE \'%VAZIO%\'
					OR UPPER(responsesummary) NOT LIKE \'%NULO%\'
					OR UPPER(responsesummary) NOT LIKE \'%NULA%\'
				)
			GROUP BY questionusageid 
			ORDER BY nota ASC LIMIT 1
		');
		return $rs;
	}

	public function get_list_activivies_by_course($courseid, $type = 0){

		global $DB;

		$modtype = ($type != 0) ? " AND m.module = '".$type."'" : "";

		$rs = $DB->get_records_sql('
			SELECT m.* 
			FROM {course_modules} m 				
			WHERE
				m.course = \''.$courseid.'\'' . $modtype

		);	

		return $rs;

	}


	public function getListEnrolledCoursesByUser($userid){

		global $DB;		
		
		$rs = $DB->get_records_sql('
			SELECT c.*
			FROM {user} u
			INNER JOIN {role_assignments} ra ON ra.userid = u.id
			INNER JOIN {context} ct ON ct.id = ra.contextid
			INNER JOIN {course} c ON c.id = ct.instanceid
			INNER JOIN {role} r ON r.id = ra.roleid
			WHERE 				
				r.id IN (1, 2, 3, 4, 5)			
				AND u.id = ' . $userid	. ' ORDER BY c.id DESC'		
		);

		return $rs;
		
	}

	public function get_list_all_courses($institution = "", $default = true){

		global $DB;

		if ($default){

			$query_institution = ($institution == "") ? "" : " AND uid2.data = '".$institution."'";
			
			$rs = $DB->get_records_sql('

					SELECT mls.objectid, mls.courseid, mls.userid, mc.fullname, uid.data, uid2.data

					FROM {logstore_standard_log} mls

					JOIN {course} mc ON mc.id = mls.courseid

					JOIN {user_info_data} uid on 
						uid.userid = mls.userid AND 
						uid.fieldid IN (SELECT id FROM {user_info_field} WHERE shortname = :profile)

					JOIN {user_info_data} uid2 on 
						uid2.userid = mls.userid AND
						uid2.fieldid IN (SELECT id FROM {user_info_field} WHERE shortname = :institution)

					WHERE

						mls.component = \'core\' AND
						mls.action = \'created\' AND 
						mls.target = \'course\' AND
						mls.objecttable = \'course\''
						.$query_institution, 

				array('institution' => 'fulbright_institution', 'profile' => 'fulbright_profile')

			);

		}
		else{

			$query_institution = ($institution == "") ? "" : " AND uid2.data = '".$institution."'";
			
			$rs = $DB->get_records_sql('

					SELECT mls.objectid, mls.courseid, mls.userid, mc.fullname, uid.data, uid2.data

					FROM {logstore_standard_log} mls

					JOIN {course} mc ON mc.id = mls.courseid

					JOIN {user_info_data} uid on 
						uid.userid = mls.userid AND 
						uid.fieldid IN (SELECT id FROM {user_info_field} WHERE shortname = :profile)

					JOIN {user_info_data} uid2 on 
						uid2.userid = mls.userid AND
						uid2.fieldid IN (SELECT id FROM {user_info_field} WHERE shortname = :institution)

					WHERE

						mls.component = \'core\' AND
						mls.action = \'created\' AND 
						mls.target = \'course\' AND
						mls.objecttable = \'course\''
						.$query_institution, 

				array('institution' => 'fulbright_institution', 'profile' => 'fulbright_profile')

			);

		}

		return $rs;

	}


	public function get_amount_courses_by_institution(){

		global $DB;

		$rs = $DB->get_records_sql('


				SELECT 
					CASE 
						WHEN INSTR(uid2.data, \'-\') <> 0 THEN
							SUBSTRING(uid2.data, 0, INSTR(uid2.data, \'-\') - 1)
						ELSE
							uid2.data
					END AS name,
					
					COUNT(mls.courseid) AS qtd
				FROM {logstore_standard_log} mls
				JOIN {course} mc ON mc.id = mls.courseid

				JOIN {user_info_data} uid on 
					uid.userid = mls.userid AND 
					uid.fieldid IN (SELECT id FROM {user_info_field} WHERE shortname = :profile)

				JOIN {user_info_data} uid2 on 
					uid2.userid = mls.userid AND 
					uid2.fieldid IN (SELECT id FROM {user_info_field} WHERE shortname = :institution)
				

				where 

				mls.component = \'core\' AND
				mls.action = \'created\' AND 
				mls.target = \'course\' AND
				mls.objecttable = \'course\'

				GROUP BY uid2.data

			', 			
			array('institution' => 'fulbright_institution', 'profile' => 'fulbright_profile', 'shortname' => 'fulbright_institution_shortname')
		);
	
		return $rs;

	}

	public function get_amount_courses_by_role($role = "Program Manager"){

		global $DB;
		
		$rs = $DB->get_records_sql('
				SELECT CONCAT(mu.firstname, \' \', mu.lastname) AS name, COUNT(mls.courseid) AS qtd
				FROM {logstore_standard_log} mls
				JOIN {course} mc ON mc.id = mls.courseid

				JOIN {user_info_data} uid on 
					uid.userid = mls.userid AND 
					uid.fieldid IN (SELECT id FROM {user_info_field} WHERE shortname = :profile)

				JOIN {user_info_data} uid2 on 
					uid2.userid = mls.userid AND 
					uid2.fieldid IN (SELECT id FROM {user_info_field} WHERE shortname = :institution)

				JOIN {user} mu on mu.id = mls.userid

				where 

				mls.component = \'core\' AND
				mls.action = \'created\' AND 
				mls.target = \'course\' AND
				mls.objecttable = \'course\' AND
				uid.data = \''.$role.'\'

				GROUP BY mls.userid

			', 			
			array('institution' => 'fulbright_institution', 'profile' => 'fulbright_profile')
		);

		return $rs;

	}


	public function get_list_institutions() {

		global $DB;	

		$rs = $DB->get_records_sql('SELECT DISTINCT name as data FROM {course_categories}');

		return $rs;

	}

	public function get_list_eta($institution = "") {

		global $DB, $USER;
		
		$rs = $DB->get_records_sql('
				SELECT DISTINCT u.id, u.firstname, u.lastname
				FROM {user_info_field} f
				JOIN {user_info_data} d ON d.fieldid = f.id AND d.data = :profile
				JOIN {user} u ON u.id = d.userid
				JOIN {user_info_field} ff ON ff.shortname = \'fulbright_institution\'
				JOIN {user_info_data} dd ON dd.fieldid = ff.id AND dd.data = :institution AND u.id = dd.userid
				WHERE 
				f.shortname = :type	

			', 			
			array('type' => 'fulbright_profile', 'profile' => 'ETA', 'institution' => $USER->profile["fulbright_institution"])
		);

		return $rs;

	}

	public function get_list_eta_by_bimester($id) {

		global $DB;
		
		$rs = $DB->get_records_sql('
				SELECT DISTINCT u.id, u.firstname, u.lastname
				FROM {user_info_field} f
				JOIN {user_info_data} d ON d.fieldid = f.id AND d.data = :profile
				JOIN {user} u ON u.id = d.userid
				WHERE 
				f.shortname = :type	

			', 			
			array('type' => 'fulbright_profile', 'profile' => 'ETA')
		);

		return $rs;

	}

	public function get_list_roles() {

		global $DB;
		
		$rs = $DB->get_records_sql('
				SELECT DISTINCT d.data, f.id, d.userid
				FROM mdl_user_info_field f
				JOIN mdl_user_info_data d ON d.fieldid = f.id
				WHERE 
				f.shortname = :type	

			', 			
			array('type' => 'fulbright_profile')
		);

		return $rs;

	}

	public function get_amount_users_institution($type, $data, $id) {

		global $DB;
		
		$rs = $DB->get_records_sql('
				SELECT DISTINCT d.userid
				FROM mdl_user_info_field f
				JOIN mdl_user_info_data d ON d.fieldid = f.id
				WHERE 
					f.shortname = :type	AND
					d.data = :data AND
					f.id = :id
			'
			, 			
			array('type' => $type, 'data' => $data, 'id' => $id)
		);

		return count($rs);

	}

	public function get_list_users_institution($type, $data, $id, $userid = "") {

		global $DB;
		
		$rs = $DB->get_records_sql('
				SELECT DISTINCT d.userid
				FROM mdl_user_info_field f
				JOIN mdl_user_info_data d ON d.fieldid = f.id
				WHERE 
					f.shortname = :type	AND
					d.data = :data AND
					f.id = :id
					'. $userid
			, 			
			array('type' => $type, 'data' => $data, 'id' => $id)
		);

		return $rs;

	}

	public function get_info_about_user($userid) {

		global $DB;
		
		$rs = $DB->get_records_sql('
				SELECT *
				FROM mdl_user
				WHERE 
					id = :id
			'					
			, 			
			array('id' => $userid)
		);

		return $rs;

	}

	public function is_user_that_role($userid, $type, $data, $id) {

		global $DB;
		
		$rs = $DB->get_records_sql('
				SELECT DISTINCT d.userid
				FROM mdl_user_info_field f
				JOIN mdl_user_info_data d ON d.fieldid = f.id
				WHERE 
					f.shortname = :type	AND
					d.data = :data AND
					f.id = :id AND
					d.userid = :userid
			'					
			, 			
			array('type' => $type, 'data' => $data, 'id' => $id, 'userid' => $userid)
		);

		return $rs;

	}


	public function get_amount_created_courses($category) {

		global $DB;
		
		if ($category == ALL_CATEGORIES) {
			return ($DB->count_records(COURSE_TABLE_NAME, 
				array('visible'=>'1')) - 1);
		} else {
			return $DB->count_records(COURSE_TABLE_NAME, 
				array('visible'=>'1', 'category'=>$category));
		} 

	}

	public function get_amount_used_courses($category) {

		global $DB;
		
		if ($category == ALL_CATEGORIES) {
			return $DB->count_records_sql('SELECT COUNT(*) FROM {report_coursestats} cs JOIN {course} co ON co.id = cs.courseid WHERE co.visible = :visible', 
				array('visible'=>'1'));
		} else {
			return $DB->count_records_sql('SELECT COUNT(*) FROM {report_coursestats} cs JOIN {course} co ON co.id = cs.courseid WHERE co.category = :cat AND co.visible = :visible', 
				array('cat'=>$category, 'visible'=>'1'));
		} 

	}

	public function get_amount_enroll_users($category) {

		global $DB;
		
		if ($category == ALL_CATEGORIES) {

			$rs = $DB->get_records_sql('
				SELECT DISTINCT u.id
				FROM {user} u 
				JOIN {user_info_field} uif ON uif.shortname = :type
				JOIN {user_info_data} uid ON uid.fieldid = uif.id
				WHERE u.id = uid.userid
				', 			
				array('type' => 'fulbright_unity', 'visible'=>'1')
			);
			
			return count($rs);

		} else {
			
			$rs = $DB->get_records_sql('
				SELECT DISTINCT u.id
				FROM {user} u 
				JOIN {user_info_field} uif ON uif.shortname = :type
				JOIN {user_info_data} uid ON uid.fieldid = uif.id
				WHERE 
					uid.data = :category
					AND u.id = uid.userid
				', 			
				array('type' => 'fulbright_unity', 'category' => $category)
			);
			


			return count($rs);

		} 

	}

	public function get_login_report(){

		global $DB;
		
		$rs = $DB->get_records_sql('
				SELECT DATE_FORMAT(FROM_UNIXTIME(timecreated), \'%Y-%m-%e\') AS name, count(id) AS qtd
				FROM {logstore_standard_log} 				
				WHERE 
					action = \'loggedin\'
				GROUP BY DATE_FORMAT(FROM_UNIXTIME(timecreated), \'%Y-%m-%e\')
				LIMIT 10
			'	
		);

		return $rs;

	}


	public function create_chart_institution($institution = ""){

		$output = "";


		$output .= '
			<script>
				window.onload = function () {

					var chart = new CanvasJS.Chart("institutionReports", {
						animationEnabled: true,
						theme: "light2", // "light1", "light2", "dark1", "dark2"
						title:{
							text: "Institutions"
						},
						axisY: {
							title: "Created courses"
						},
						data: [{        
							type: "column",  
							showInLegend: true, 
							legendMarkerColor: "grey",
							legendText: "Created courses",
							dataPoints: [  
		';    

		$list_of_institution_and_courses = $this->get_amount_courses_by_institution();

		foreach($list_of_institution_and_courses as $l){

			$output .= '{ y: '.$l->created_courses.', label: "'.$l->institution_name.'" },';
			
		}
								
		$output .= '
							]
						}]
					});
					chart.render();

				}
		</script>';

		return $output;



	}


	public function create_chart_js_horizontal_bar($elementid, $charttitle, $labellegend, $dataChart, $institution = "", $horizontal = "bar"){

		$output = "";

		//$list_of_institution_and_courses = $this->get_amount_courses_by_institution();

		$labels = "";
		$data = "";

		foreach($dataChart as $l){

			$words = explode(" ", $l->name);
			$acronym = "";

			foreach ($words as $w) {
				if (
					strtoupper($w) != "DE" && 
					strtoupper($w) != "DA" &&
					strtoupper($w) != "DO" &&
					strtoupper($w) != "DES" && 
					strtoupper($w) != "DAS" &&
					strtoupper($w) != "DOS" &&
					strtoupper($w) != "E"
				){
				  	$acronym .= $w[0];
			  	}
			}


			$labels .= '"'. $acronym .'", ';
			$data .= $l->qtd.', ';

		}
//
		$output .= '
			<script>
			//window.onload = function () {
				var ctx = document.getElementById(\''.$elementid.'\').getContext(\'2d\');
				new Chart(ctx, {
				    type: \''.$horizontal.'\',
				    data: {
				      labels: ['.$labels.'],
				      datasets: [
				        {
				          label: "'.$labellegend.'",
				          backgroundColor: ["#3e95cd", "#8e5ea2","#3cba9f","#e8c3b9","#c45850"],
				          data: ['.$data.']
				        }
				      ]
				    },
				    options: {
			    	  scales : {
            			yAxes : [{
                		  ticks : {
                    		beginAtZero : true
                		  }   
            			}],
            			xAxes : [{
                		  ticks : {
                    		beginAtZero : true
                		  }   
            			}]
        			  },
        			  scaleBeginAtZero : true,
				      legend: { display: false },
				      title: {
				        display: true,
				        text: \''.$charttitle.'\',
				        fontSize: 24
				      }
				      
				    }
				});		
			//}
			</script>
		';

		return $output;

	}


	public function create_chart_js_line($elementid, $charttitle, $labellegend, $dataChart){

		$output = "";

		//$list_of_institution_and_courses = $this->get_amount_courses_by_institution();

		$labels = "";
		$data = "";

		foreach($dataChart as $l){

			$labels .= '"'. $l->name .'", ';
			$data .= $l->qtd.', ';

		}
//
		$output .= '
			<script>
			//window.onload = function () {
				var ctx = document.getElementById(\''.$elementid.'\').getContext(\'2d\');
				var Chartline = new Chart(ctx, {
				    type: \'line\',
				    data: {
				      labels: ['.$labels.'],
				      datasets: [
				        {
				          label: "'.$labellegend.'",
				          borderColor: "#3e95cd",
				          data: ['.$data.'],
				          fill: false
				        }
				      ]
				    },
				    options: {
			    	  scales : {
            			yAxes : [{
                		  ticks : {
                    		beginAtZero : true
                		  }   
            			}]
        			  },
        			  scaleBeginAtZero : true,
				      legend: { display: false },
				      title: {
				        display: true,
				        text: \''.$charttitle.'\',
				        fontSize: 24
				      }
				      
				    }
				});		
			//}
				
			</script>
		';

		return $output;

	}


	public function saveNewSemanalReport($report){

		global $DB;
		

		/*$rs = $DB->get_records_sql('
				SELECT id
				FROM {report_coursestats_fsr} 				
				WHERE 
					eta_userid = '.$report->eta_userid.' AND
					date = \''.$report->date.'\' AND
					institution = \''.$report->institution.'\' AND
					classroom_time = \''.$report->classroom_time.'\' AND
					online_time = \''.$report->online_time.'\''
		);

		if (count($rs) > 0){
			return "exists";
		}
		else{*/
	 		$lastinsertid = $DB->insert_record("report_coursestats_fsr", $report, false);		
			return $lastinsertid;
		/*}

		return 0;*/

	}

	public function getListSemanalReport($userid = 0, $institution = ""){

		global $DB;

		if ($userid != 0){
			$rs = $DB->get_records_sql('
				SELECT fsr.*, u.firstname
				FROM {report_coursestats_fsr} fsr
				JOIN {user} u ON u.id = fsr.eta_userid
				WHERE fsr.eta_userid = '.$userid
			);
		}
		else{

			$rs = $DB->get_records_sql('
				SELECT fsr.*, u.firstname
				FROM {report_coursestats_fsr} fsr
				JOIN {user} u ON u.id = fsr.eta_userid
				WHERE fsr.institution = \''.$institution.'\''
			);

		}

		return $rs;

	}

	public function updateSemanalReport($id, $status){

		global $DB;


		$rs = $DB->execute('
				UPDATE {report_coursestats_fsr} 				
				SET 
					approved = \''.$status.'\'
				WHERE
					id = '.$id, array()
		);
		
		return $rs;

	}

	public function getListBimesters($institution = "", $userid = 0, $profile = ""){

		global $DB;

		if ($profile == "ETA"){
			$rs = $DB->get_records_sql('
				SELECT *
				FROM {report_coursestats_ebi}
				WHERE eta_userid = ' . $userid
			);					
		}
		else if ($profile == "Admin"){

			

			$rs = $DB->get_records_sql('
				SELECT *
				FROM {report_coursestats_bimester}			
				WHERE institution = \'' . $institution . '\'
				ORDER BY bimester_start_date DESC'

			);			
		}
		else{
			$rs = $DB->get_records_sql('
				SELECT *
				FROM {report_coursestats_bimester}			
				WHERE pm_userid = ' . $userid . ' AND institution = \'' . $institution . '\'
				ORDER BY bimester_start_date DESC'

			);			
		}

		return $rs;

	}

	public function getListEtasBimesters($bimester_id, $institution = ""){

		global $DB, $USER;

		$etas_list = $this->get_list_eta();


		$instituicao = (empty($institution)) ? $USER->profile["fulbright_institution"] : $institution;





		if (!empty($etas_list)){

			foreach ($etas_list as $eta) {
		
				$rs = $DB->get_records_sql('
					SELECT*
					FROM {report_coursestats_ebi} E					
					WHERE E.bimester_id = ' . $bimester_id . ' AND E.eta_userid = ' . $eta->id
				);

				if (empty($rs)){
					$new_eta_bimester = new stdClass();
					$new_eta_bimester->eta_userid = $eta->id;
					$new_eta_bimester->bimester_id = $bimester_id;
					$new_eta_bimester->timecreated = time();
					$lastinsertid = $DB->insert_record("report_coursestats_ebi", $new_eta_bimester);	
				}				

			}

		}

		/*if ($institution == "" || $institution == 0){

			echo "<div style='display: none;'>
				SELECT U.id, U.firstname, U.lastname
				FROM {report_coursestats_ebi} E
				JOIN {user} U ON U.id = E.eta_userid				
				WHERE E.bimester_id = " . $bimester_id . "</div>";

			$rs = $DB->get_records_sql('
				SELECT U.id, U.firstname, U.lastname
				FROM {report_coursestats_ebi} E
				JOIN {user} U ON U.id = E.eta_userid				
				WHERE E.bimester_id = ' . $bimester_id
			);			
		}
		else{*/
			//echo "<div style='display: none;'>" . $institution . "</div>";


			/*echo "<div style='display: none;'>
				SELECT U.id, U.firstname, U.lastname
				FROM {report_coursestats_ebi} E
				JOIN {user} U ON U.id = E.eta_userid
				JOIN {user_info_field} ff ON ff.shortname = \'fulbright_institution\'
				JOIN {user_info_data} dd ON dd.fieldid = ff.id AND dd.data = '".$instituicao."' AND U.id = dd.userid
				WHERE E.bimester_id = " . $bimester_id . "</div>";*/

			$rs = $DB->get_records_sql('
				SELECT U.id, U.firstname, U.lastname
				FROM {report_coursestats_ebi} E
				JOIN {user} U ON U.id = E.eta_userid
				JOIN {user_info_field} ff ON ff.shortname = \'fulbright_institution\'
				JOIN {user_info_data} dd ON dd.fieldid = ff.id AND dd.data = \''.$instituicao.'\' AND U.id = dd.userid
				WHERE E.bimester_id = ' . $bimester_id
			);		
		//}
		
		

		return $rs;

	}

	public function getListActivitiesBimesters($bimester_id){

		global $DB;
		
		$rs = $DB->get_records_sql('
			SELECT a.*
			FROM {report_coursestats_bimester} b
			JOIN {report_coursestats_pma} a ON a.activity_start_date >= b.bimester_start_date
			WHERE b.id = ' . $bimester_id
		);		

		return $rs;

	}

	public function getListActivitiesPM($bimester_id = ""){

		global $DB, $USER;

		if ($bimester_id != ""){
			$rs = $DB->get_records_sql('
				SELECT *			
				FROM {report_coursestats_pma} 
				WHERE pm_userid = ' . $USER->id . ' AND activity_location LIKE \'%'.$bimester_id.'%\''
			);		
		}
		else{
			$rs = $DB->get_records_sql('
				SELECT *			
				FROM {report_coursestats_pma} 
				WHERE pm_userid = ' . $USER->id
			);		
		}
		

		return $rs;
		

	}

	public function getListWeeksBimesters($bimester_id){

		global $DB;

		switch ($bimester_id) {
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

		
		/*$rs = $DB->get_records_sql('
			SELECT *
			FROM {report_coursestats_weeks} 			
			WHERE bimester_id = ' . $bimester_id
		);*/		

		$rs = $DB->get_records_sql('
			SELECT *
			FROM {report_coursestats_weeks} 			
			WHERE timecreated = ' . $timestamp
		);


		if (empty($rs)){

			for($i = 1; $i <= 8; $i++){

				//echo "Week " .$i . ": " . date("Y-m-d", strtotime($bimester_start_date . " +" . $i . " week")) . "<br />";
				$new_week = new stdClass();
				$new_week->bimester_id = $bimester_id;
				$new_week->week_number = $i;
				$new_week->timecreated = $timestamp;
				$lastinsertid = $DB->insert_record("report_coursestats_weeks", $new_week);		

			}

			$rs = $DB->get_records_sql('
				SELECT *
				FROM {report_coursestats_weeks} 			
				WHERE timecreated = ' . $timestamp
			);

		}


		return $rs;

	}


	public function getActivityEtaWeek($week_id, $eta_id){

		global $DB;
		
		$rs = $DB->get_records_sql('
			SELECT act.id, act.activity_name, eta_act.activity_estimated
			FROM {report_coursestats_ea} eta_act
			JOIN {report_coursestats_pma} act ON act.id = eta_act.activity_id
			WHERE eta_act.eta_userid = ' . $eta_id . ' AND eta_act.week_id = ' . $week_id
		);		

		return $rs;

	}

	public function getActivityInfo($activity_id, $eta_id){

		global $DB;

		
		
		$rs = $DB->get_records_sql('
			SELECT 
				a.id,
				a.activity_name, 
				a.activity_syllabus, 
				a.activity_location, 
				a.activity_max_students, 
				a.activity_type_of_program, 
				a.activity_themes, 
				e.activity_estimated,
				e.activity_executed
			FROM {report_coursestats_pma} a
			JOIN {report_coursestats_ea} e ON e.activity_id = a.id
			WHERE a.id = ' . $activity_id . ' AND e.eta_userid = ' . $eta_id
		);		

		return $rs;

	}

	public function getListBimestersByPeriod($start, $end, $eta = 0, $institution){

		global $DB, $USER;

		$start = date("Y-m-d", strtotime(str_replace("/", "-", $start)));
		
		
		if ($eta != 0){

			if ($end != ""){

				$end = date("Y-m-d", strtotime(str_replace("/", "-", $end)));

				$rs = $DB->get_records_sql('
					SELECT b.id, b.bimester_start_date
					FROM {report_coursestats_ebi} e				
					JOIN {report_coursestats_bimester} b ON b.id = e.bimester_id
					WHERE 
						b.bimester_start_date >= \'' . $start . '\' AND 
						b.bimester_start_date <= \'' . $end . '\' AND 
						e.eta_userid = ' . $eta
				);		

			}
			else{

				$rs = $DB->get_records_sql('
					SELECT b.id, b.bimester_start_date
					FROM {report_coursestats_ebi} e				
					JOIN {report_coursestats_bimester} b ON b.id = e.bimester_id
					WHERE 
						b.bimester_start_date >= \'' . $start . '\' AND 						
						e.eta_userid = ' . $eta
				);		
			}

			
			

		}
		else{


			if ($USER->profile["fulbright_profile"] != "Admin"){

				if ($end != ""){

					$end = date("Y-m-d", strtotime(str_replace("/", "-", $end)));

					$rs = $DB->get_records_sql('
						SELECT id, bimester_start_date
						FROM {report_coursestats_bimester} 			
						WHERE 
							bimester_start_date >= \'' . $start . '\' AND 
							bimester_start_date <= \'' . $end . '\' AND
							pm_userid = ' . $USER->id
					);


				}
				else{


					$rs = $DB->get_records_sql('
						SELECT id, bimester_start_date
						FROM {report_coursestats_bimester} 			
						WHERE 
							bimester_start_date >= \'' . $start . '\' AND 						
							pm_userid = ' . $USER->id
					);


				}	
			}
			else{

				if ($end != ""){

					$end = date("Y-m-d", strtotime(str_replace("/", "-", $end)));

					$rs = $DB->get_records_sql('
						SELECT id, bimester_start_date
						FROM {report_coursestats_bimester} 			
						WHERE 
							bimester_start_date >= \'' . $start . '\' AND 
							bimester_start_date <= \'' . $end . '\' AND
							pm_userid IN (
								SELECT
									userid
								FROM {user_info_data}
								WHERE 
									data = \''.$institution.'\'
							)
						'
					);


				}
				else{


					$rs = $DB->get_records_sql('
						SELECT id, bimester_start_date
						FROM {report_coursestats_bimester} 			
						WHERE 
							bimester_start_date >= \'' . $start . '\' AND 						
							pm_userid IN (
							SELECT
								userid
							FROM {user_info_data}
							WHERE 
								data = \''.$institution.'\'
						)
						'
					);


				}

			}

	

		}

		

		return $rs;

	}


	public function getListBimestersWeeks($id, $eta = 0, $institution){

		global $DB, $USER;

		$start = date("Y-m-d", strtotime(str_replace("/", "-", $start)));
		
		
		if ($eta != 0){

			if ($end != ""){

				$end = date("Y-m-d", strtotime(str_replace("/", "-", $end)));

				$rs = $DB->get_records_sql('
					SELECT b.id, b.bimester_start_date
					FROM {report_coursestats_ebi} e				
					JOIN {report_coursestats_bimester} b ON b.id = e.bimester_id
					WHERE 
						b.bimester_start_date >= \'' . $start . '\' AND 
						b.bimester_start_date <= \'' . $end . '\' AND 
						e.eta_userid = ' . $eta
				);		

			}
			else{

				$rs = $DB->get_records_sql('
					SELECT b.id, b.bimester_start_date
					FROM {report_coursestats_ebi} e				
					JOIN {report_coursestats_bimester} b ON b.id = e.bimester_id
					WHERE 
						b.bimester_start_date >= \'' . $start . '\' AND 						
						e.eta_userid = ' . $eta
				);		
			}

			
			

		}
		else{


			if ($USER->profile["fulbright_profile"] != "Admin"){

				if ($end != ""){

					$end = date("Y-m-d", strtotime(str_replace("/", "-", $end)));

					$rs = $DB->get_records_sql('
						SELECT id, bimester_start_date
						FROM {report_coursestats_bimester} 			
						WHERE 
							bimester_start_date >= \'' . $start . '\' AND 
							bimester_start_date <= \'' . $end . '\' AND
							pm_userid = ' . $USER->id
					);


				}
				else{


					$rs = $DB->get_records_sql('
						SELECT id, bimester_start_date
						FROM {report_coursestats_bimester} 			
						WHERE 
							bimester_start_date >= \'' . $start . '\' AND 						
							pm_userid = ' . $USER->id
					);


				}	
			}
			else{

				if ($end != ""){

					$end = date("Y-m-d", strtotime(str_replace("/", "-", $end)));

					$rs = $DB->get_records_sql('
						SELECT id, bimester_start_date
						FROM {report_coursestats_bimester} 			
						WHERE 
							bimester_start_date >= \'' . $start . '\' AND 
							bimester_start_date <= \'' . $end . '\' AND
							pm_userid IN (
								SELECT
									userid
								FROM {user_info_data}
								WHERE 
									data = \''.$institution.'\'
							)
						'
					);


				}
				else{


					$rs = $DB->get_records_sql('
						SELECT id, bimester_start_date
						FROM {report_coursestats_bimester} 			
						WHERE 
							bimester_start_date >= \'' . $start . '\' AND 						
							pm_userid IN (
							SELECT
								userid
							FROM {user_info_data}
							WHERE 
								data = \''.$institution.'\'
						)
						'
					);


				}

			}

	

		}

		

		return $rs;

	}


	public function getListBimestersFromToday($eta = 0, $institution){

		global $DB, $USER;

		if ($eta != 0){

			$rs = $DB->get_records_sql('
				SELECT b.id, b.bimester_start_date
				FROM {report_coursestats_ebi} e				
				JOIN {report_coursestats_bimester} b ON b.id = e.bimester_id
				WHERE 
					(NOW() BETWEEN b.bimester_start_date AND DATE_ADD(b.bimester_start_date, INTERVAL 8 WEEK)) AND
					e.eta_userid = ' . $eta . ' LIMIT 1'
			);		
			

		}
		else{

			if ($USER->profile["fulbright_profile"] != "Admin"){

				$rs = $DB->get_records_sql('
					SELECT id, bimester_start_date
					FROM {report_coursestats_bimester} 			
					WHERE 
						(NOW() BETWEEN bimester_start_date AND DATE_ADD(bimester_start_date, INTERVAL 8 WEEK)) AND					
						pm_userid = ' . $USER->id . ' LIMIT 1'
				);	

			}
			else{

				/*echo 'SELECT id, bimester_start_date
					FROM {report_coursestats_bimester} 			
					WHERE 
						(NOW() BETWEEN bimester_start_date AND DATE_ADD(bimester_start_date, INTERVAL 8 WEEK)) AND					
						pm_userid IN (
							SELECT
								userid
							FROM {user_info_data}
							WHERE 
								data = \''.$institution.'\'
						)
					';*/

				$rs = $DB->get_records_sql('
					SELECT id, bimester_start_date
					FROM {report_coursestats_bimester} 			
					WHERE 
						(NOW() BETWEEN bimester_start_date AND DATE_ADD(bimester_start_date, INTERVAL 8 WEEK)) AND					
						pm_userid IN (
							SELECT
								userid
							FROM {user_info_data}
							WHERE 
								data = \''.$institution.'\'
						)
					'
				);	

			}	

		}

		

		return $rs;

	}


	public function deleteActivityEta($activity_id, $eta_id, $week_id){

		global $DB;

		$rs = $DB->execute('
				DELETE FROM {report_coursestats_ea}				
				WHERE
					activity_id = '.$activity_id.' AND 
					eta_userid = '.$eta_id.' AND 
					week_id = '.$week_id
			, array()
		);
		
		return $rs;

	}

	public function deleteActivity($activity_id){

		global $DB;

		$rs = $DB->execute('
				DELETE FROM {report_coursestats_ea}				
				WHERE
					activity_id = '.$activity_id
			, array()
		);

		$rs = $DB->execute('
				DELETE FROM {report_coursestats_pma}				
				WHERE
					id = '.$activity_id
			, array()
		);
		
		return $rs;

	}

	public function getCategoryIdByInstitution($institution){

		global $DB;		
		
		$rs = $DB->get_record_sql('
				SELECT id

				FROM {course_categories} 				

				WHERE
					name = :institution LIMIT 1
			', 			
			array('institution' => $institution)
		);

		return $rs;

	}

	public function getEtaSemanalReport($eta_userid, $week_id, $bimester_id){

		global $DB;		
		
		$rs = $DB->get_record_sql('
				SELECT id
				FROM {report_coursestats_fsr} 				
				WHERE
					eta_userid = :eta AND
					bimester_id = :bimester AND
					week_id = :week
			', 			
			array(
				'eta' => $eta_userid,
				'bimester' => $bimester_id,
				'week' => $week_id
			)
		);

		return $rs;

	}

	public function getEtaSemanalReportInfo($report_id){

		global $DB;		
		
		$rs = $DB->get_record_sql('
				SELECT *
				FROM {report_coursestats_fsr} 				
				WHERE
					id = :id
			', 			
			array(
				'id' => $report_id
			)
		);

		return $rs;

	}

	/**
     * Generate a summary of the activites in a section
     *
     * @param stdClass $section The course_section entry from DB
     * @param stdClass $course the course record from DB     
     * @return array 
     */
    public function get_course_progress($section, $course) {

        $modinfo = get_fast_modinfo($course);
        if (empty($modinfo->sections[$section->section])) {
            return '';
        }

        // Generate array with count of activities in this section.
        $sectionmods = array();
        $total = 0;
        $complete = 0;
        $cancomplete = isloggedin() && !isguestuser();
        $completioninfo = new completion_info($course);
        foreach ($modinfo->sections[$section->section] as $cmid) {
            $thismod = $modinfo->cms[$cmid];

            if ($thismod->uservisible) {
                if (isset($sectionmods[$thismod->modname])) {
                    $sectionmods[$thismod->modname]['name'] = $thismod->modplural;
                    $sectionmods[$thismod->modname]['count']++;
                } else {
                    $sectionmods[$thismod->modname]['name'] = $thismod->modfullname;
                    $sectionmods[$thismod->modname]['count'] = 1;
                }
                if ($cancomplete && $completioninfo->is_enabled($thismod) != COMPLETION_TRACKING_NONE) {
                    $total++;
                    $completiondata = $completioninfo->get_data($thismod, true);
                    if ($completiondata->completionstate == COMPLETION_COMPLETE ||
                            $completiondata->completionstate == COMPLETION_COMPLETE_PASS) {
                        $complete++;
                    }
                }
            }
        }

        if (empty($sectionmods)) {
            // No sections.
            return '';
        }

        // Output section completion data.        
        if ($total > 0) {
            return array("completos" => $complete, "total" => $total);
        }

        return array("completos" => 0, "total" => 0);

	}

	/**
     * Generate a summary of the activites in a section
     *
     * @param stdClass $section The course_section entry from DB
     * @param stdClass $course the course record from DB     
     * @return array 
     */
    public function get_list_course_progress_week($section, $course, $qtd_semanas = 0) {

		global $DB;

		/*
		$day = date('w'); 0 = Domingo / 1 = Segunda-feira				
		$sgunda = date('m-d-Y', strtotime( "previous monday" ));		
		$domingo = date('m-d-Y', strtotime( "next sunday" ));
		*/

		$day = date('w');				
		$segunda = ($day == 1) ? date("Y-m-d") : date('Y-m-d', strtotime("previous monday"));		
		$domingo = ($day == 0) ? date("Y-m-d") : date('Y-m-d', strtotime("next sunday"));

		$alunos = $DB->get_records("user");
		$ranking_tarefas = array();

		foreach ($alunos as $a) {
			
			$modinfo = get_fast_modinfo($course, $a->id);
			if (empty($modinfo->sections[$section->section])) {
				return '';
			}

			// Generate array with count of activities in this section.
			$sectionmods = array();
			$total = 0;
			$complete = 0;
			$cancomplete = isloggedin() && !isguestuser();
			$completioninfo = new completion_info($course);
			foreach ($modinfo->sections[$section->section] as $cmid) {
				$thismod = $modinfo->cms[$cmid];

				if ($thismod->uservisible) {
					if (isset($sectionmods[$thismod->modname])) {
						$sectionmods[$thismod->modname]['name'] = $thismod->modplural;
						$sectionmods[$thismod->modname]['count']++;
					} else {
						$sectionmods[$thismod->modname]['name'] = $thismod->modfullname;
						$sectionmods[$thismod->modname]['count'] = 1;
					}
					if ($cancomplete && $completioninfo->is_enabled($thismod) != COMPLETION_TRACKING_NONE) {					

						$total++;
						$completiondata = $completioninfo->get_data($thismod, true, $a->id);

						// echo "<pre>";							
						// print_r($completiondata);
						// echo "</pre>";
					
						if ( ($completiondata->completionstate == COMPLETION_COMPLETE ||
								$completiondata->completionstate == COMPLETION_COMPLETE_PASS) && $completiondata->userid == $a->id) {

							if (date("Y-m-d", $completiondata->timemodified) >= $segunda && date("Y-m-d", $completiondata->timemodified) <= $domingo) {
								
								// echo "<pre>";							
								// print_r($a->firstname);
								// echo "</pre>";

								// echo "<pre>";
								// print_r(date("Y-m-d", $completiondata->timemodified));
								// echo "</pre>";
								$complete++;
								
								if (!isset($ranking_tarefas[$a->firstname])) {
									$ranking_tarefas[$a->firstname]["completas"] = 1;
									$ranking_tarefas[$a->firstname]["id"] = $a->id;
								}
								else {
									$ranking_tarefas[$a->firstname]["completas"]++;
									$ranking_tarefas[$a->firstname]["id"] = $a->id;
								}
							}							
							
						}
					}
				}
			}

		}

        return $ranking_tarefas;
		/*
        if (empty($sectionmods)) {
            // No sections.
            return '';
        }

        // Output section completion data.        
        if ($total > 0) {
            return array("completos" => $complete, "total" => $total);
        }

		return array("completos" => 0, "total" => 0);
		*/

	}
	
	public function get_course_metadata($courseid) {
		$handler = \core_customfield\handler::get_handler('core_course', 'course');
		// This is equivalent to the line above.
		//$handler = \core_course\customfield\course_handler::create();
		$datas = $handler->get_instance_data($courseid);
		$metadata = [];
		foreach ($datas as $data) {
			if (empty($data->get_value())) {
				continue;
			}
			$cat = $data->get_field()->get_category()->get('name');
			$metadata[$data->get_field()->get('shortname')]["label"] = $cat;
			$metadata[$data->get_field()->get('shortname')]["value"] = $data->get_value();
		}
		return $metadata;
	}


}