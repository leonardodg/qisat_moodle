<?php  // $Id: extendenrol.php,v 1.12 2007/08/17 19:09:26 nicolasconnault Exp $
require_once "../../../../config.php";
require_once '../../lib.php';

date_default_timezone_set("Brazil/East");

global $CFG, $DB, $USER, $SITE;

$id    = required_param('id', PARAM_INT); // course id
$iduser = optional_param('iduser', null, PARAM_INT);
//$dados = optional_param('dados', null, PARAM_INT); // array of user id
$chave = optional_param('chave', null, PARAM_TEXT);
if(isset($_POST["dados"]) && is_array($_POST["dados"])){
	$dados = $_POST["dados"];
} else {
	$dados = array();
}

$context_system = context_system::instance();
$PAGE->set_context($context_system);
$PAGE->set_url('/blocks/gerenciamento/Administracao/AlunosCursos/prorrogacaomoodle.php');
$PAGE->set_title(get_string('prorrogarcurso', 'block_gerenciamento'));
$PAGE->navigation->add(get_string('prorrogarcurso', 'block_gerenciamento'));

$PAGE->set_heading($SITE->fullname);

$PAGE->navbar->ignore_active();
$PAGE->navbar->add(get_string('blockname', 'block_gerenciamento'), null);
$PAGE->navbar->add(get_string('administracao', 'block_gerenciamento'), null);
$PAGE->navbar->add(get_string('alunosecursos', 'block_gerenciamento'), null);
$PAGE->navbar->add(get_string('prorrogarcurso', 'block_gerenciamento'), new moodle_url($PAGE->url));

$PAGE->set_pagelayout('incourse');

if (has_capability('block/gerenciamento:prorrogacaomoodle', $context_system)) {

	if (!$course = $DB->get_record('course', array('id'=>$id))) {
		error("Course ID is incorrect");
	}

	$context = context_course::instance($id);
	require_login($course->id);

	$today = time();
	$today = make_timestamp(date('Y', $today), date('m', $today), date('d', $today), 0, 0, 0);
	if ((count($dados) > 0) and ($form = data_submitted())) {

		$item = null;
		if(isset($form->item)){
			$item =  (int) $form->item;
		}
			
		$fromsite = new stdClass();
		$fromsite->firstname = $SITE->fullname;
		$fromsite->lastname = '';
		$fromsite->lastnamephonetic = '';
		$fromsite->firstnamephonetic = '';
		$fromsite->middlename = '';
		$fromsite->alternatename = '';
		$fromsite->email = $CFG->noreplyaddress;
		$fromsite->maildisplay = true;
		$fromsite->mailformat  = 1;

		$subject = '['.$course->shortname.'] '.get_string('subject', 'block_gerenciamento');
		foreach ($form->dados as $dado) {

			$userid = $dado['userid'];
			$enrolid = $dado['enrolid'];
			$extendperiod =  $dado['extendperiod'];
			$extendbase =  $dado['extendbase'];

			// find all roles this student have in this course
			if ($student = $DB->get_record_sql("SELECT ue.*
												FROM {user_enrolments} ue
												INNER JOIN {enrol} en
													ON ue.enrolid = en.id
												WHERE ue.userid = ".$userid."
													AND en.id = ".$enrolid)) {
			// enrol these users again, with time extension
			// not that this is not necessarily a student role

			//foreach ($students as $student) {
				// only extend if the user can make role assignments on this role
				//if (user_can_assign($context, $student->roleid)) {
					switch($extendperiod) {
						case 0: // No change
							break;
						case -1: // unlimited
							$student->timeend = 0;
							break;
						default: // extend
							switch($extendbase) {
								case 0: // course start date
									$student->timeend = $course->startdate + $extendperiod;
									$student->timeend = mktime(23, 59, 59, date('m', $student->timeend), date('d', $student->timeend), date('Y', $student->timeend));
									$dt_prorrogacao = $course->startdate;
									break;
								case 1: // student enrolment start date
									// we check for student enrolment date because Moodle versions before 1.9 did not set this for
									// unlimited enrolment courses, so it might be 0
									if($student->timestart > 0) {
										$student->timeend = $student->timestart + $extendperiod;
										$student->timeend = mktime(23, 59, 59, date('m', $student->timeend), date('d', $student->timeend), date('Y', $student->timeend));
									}
									$dt_prorrogacao = $student->timestart;
									break;
								case 2: // student enrolment start date
									// enrolment end equals 0 means Unlimited, so adding some time to that will still yield Unlimited
									$dt_prorrogacao = $student->timeend;
									if($student->timeend > 0) {
										$student->timeend = $student->timeend + $extendperiod;
										$student->timeend = mktime(23, 59, 59, date('m', $student->timeend), date('d', $student->timeend), date('Y', $student->timeend));
									}
									//$dt_prorrogacao = $student->timeend;
									break;
								case 3: // current date
									$student->timeend = $today + $extendperiod;
									$student->timeend = mktime(23, 59, 59, date('m', $student->timeend), date('d', $student->timeend), date('Y', $student->timeend));
									$dt_prorrogacao = $today;
									break;
								case 4: // course enrolment start date
									if($course->enrolstartdate > 0) {
										$student->timeend = $course->enrolstartdate + $extendperiod;
										$student->timeend = mktime(23, 59, 59, date('m', $student->timeend), date('d', $student->timeend), date('Y', $student->timeend));
									}
									$dt_prorrogacao = $course->enrolstartdate;
									break;
								case 5: // course enrolment end date
									if($course->enrolenddate > 0) {
										$student->timeend = $course->enrolenddate + $extendperiod;
										$student->timeend = mktime(23, 59, 59, date('m', $student->timeend), date('d', $student->timeend), date('Y', $student->timeend));
									}
									$dt_prorrogacao = $course->enrolenddate;
									break;
									
							}
					}
						
					if ($extendperiod != 0){
						$recipient = $DB->get_record('user', array('id'=>$userid));

						// definition of $messagehtml
						$a = $course->fullname;
						$messagehtml_student = get_string('ocursoprorrogado', 'block_gerenciamento', $a);

						$a = $course->fullname;
						$messagehtml_admin = get_string('ocursoprorrogado', 'block_gerenciamento', $a);

						$a = $recipient->firstname . " " . $recipient->lastname;
						$messagehtml_admin .= get_string('usuarioprorrogado', 'block_gerenciamento', $a);

						$a = $extendperiod/86400;
						$messagehtml = get_string('tempoprorrogacao', 'block_gerenciamento', $a);

						$a = date("d/m/Y" ,$dt_prorrogacao);
						$messagehtml .= get_string('iniciadoem', 'block_gerenciamento', $a);

						$a = date("d/m/Y" ,$student->timeend);
						$messagehtml .= get_string('finalizaem', 'block_gerenciamento', $a);

						$a = date("d/m/Y H:i:s" ,time());
						$messagehtml .= get_string('dataprorrogacaoparam', 'block_gerenciamento', $a);

						$titulo_email = get_string('prorrogacaoprazo', 'block_gerenciamento');
						$messagehtml_student =  stripslashes_str($messagehtml_student.$messagehtml);
						//----------$messagehtml_student = email_body($messagehtml_student, $titulo_email);

						$messagehtml_admin =  stripslashes_str($messagehtml_admin.$messagehtml);
						//----------$messagehtml_admin = email_body($messagehtml_admin, $titulo_email);
						// end of definition of $messagehtml

						// definition of $messagetext
						$messagetext_student =  str_replace('<br />', "\n", $messagehtml_student);
						$messagetext_student =  strip_tags($messagetext_student);
						$messagetext_admin =  str_replace('<br />', "\n", $messagehtml_admin);
						$messagetext_admin =  strip_tags($messagetext_admin);
						// end of definition of $messagetext

						$p = new stdClass();
						$p->userid = $userid;
						$p->nu_dias_prorrogacao = $extendperiod/86400;
						$p->data_prorrogacao = $dt_prorrogacao;
						$p->timemodified = time();
						$p->item = $item;
						$p->usermodified = $USER->id;

						$student->timeend = $dt_prorrogacao + $extendperiod;
						$student->timeend = mktime(23, 59, 59, date('m', $student->timeend), date('d', $student->timeend), date('Y', $student->timeend));

						$student->modifierid = $USER->id;
						$student->timemodified = time();

						if($cursoFase = busca_cursos_fase($student)){
							foreach($cursoFase as $curso){
								$p->courseid = $curso->courseid;
								$inserted = $DB->insert_record("prorrogacoes", $p);

								$student->id = $curso->id;
								$student->status = $curso->status;
								$student->enrolid = $curso->enrolid;
								$DB->update_record("user_enrolments", $student);
							}
						}else{
							$p->courseid = $course->id;
							$inserted = $DB->insert_record("prorrogacoes", $p);

							$DB->update_record("user_enrolments", $student);
						}

						$student = $DB->get_record_sql("SELECT *
		                                       			FROM {role_assignments} ra
		                                       			WHERE ra.userid = $userid
		                                       			AND ra.contextid = $context->id");

						//if (!email_to_user($recipient, $fromsite, '=?ISO-8859-1?B?'.base64_encode($subject).'?=', $messagetext, $messagehtml) ) {
						if (!email_to_user($recipient, $fromsite, $subject, '', $messagehtml_student) ) {
							echo 'An error was encountered sending an email in sendmessage.php. It is likely that your email settings are not configu//red properly. The error reported was "'. print_r(error_get_last()) .'"<br />';
						}
						$admin = get_admin();
						if (!email_to_user($admin, $fromsite, $subject, $messagetext_admin) ) {
							echo 'An error was encountered sending an email in sendmessage.php. It is likely that your email settings are not configured properly. The error reported was "'. print_r(error_get_last()) .'"<br />';
						}

					}
				//}
			//}
		                                       }
		}

		redirect($CFG->wwwroot.'/blocks/gerenciamento/Administracao/AlunosCursos/prorrogacao.php?chave='.$chave, get_string('cursoprorrogado', 'block_gerenciamento'));
	}

	$timeformat = get_string('strftimedate', 'block_gerenciamento');
	$unlimited = get_string('unlimited', 'block_gerenciamento');

	if(!has_capability('block/gerenciamento:prorrogacoesilimitadas', $context_system) && !has_capability('block/gerenciamento:prorrogacaolimitada', $context_system)){
		$periodmenu[(25 * 86400)] = get_string('numdays', 'block_gerenciamento', 25);
	}else{
		$ndays = 25;

		if(has_capability('block/gerenciamento:prorrogacoesilimitadas', $context_system)){
			$ndays = 365;
			$periodmenu[-1] = $unlimited;
		}

		for ($i=1; $i<=$ndays; $i++) {
			$seconds = $i * 86400;
			$periodmenu[$seconds] = get_string('numdays', 'block_gerenciamento', $i);
		}
	}

	// this will contain all available the based On select options, but we'll disable some on them on a per user basis
	if(!has_capability('block/gerenciamento:prorrogacoesilimitadas', $context_system)){
		$basemenu[3] = get_string('today', 'block_gerenciamento') . ' (' . userdate($today, $timeformat) . ')' ;
	}else{
		$basemenu[0] = get_string('startdate', 'block_gerenciamento') . ' (' . userdate($course->startdate, $timeformat) . ')';
		$basemenu[1] = get_string('enrolmentstart', 'block_gerenciamento');
		$basemenu[2] = get_string('enrolmentend', 'block_gerenciamento');

		$enrol = $DB->get_record('enrol', array('enrol'=>'self', 'courseid'=>$course->id), 'status');

		if(isset($enrol) || ($course->enrolstartdate == 0 || $course->enrolstartdate <= $today) && ($course->enrolenddate == 0 || $course->enrolenddate > $today)) {
			$basemenu[3] = get_string('today', 'block_gerenciamento') . ' (' . userdate($today, $timeformat) . ')' ;
		}
		if(!isset($enrol)) {
			if($course->enrolstartdate > 0) {
				$basemenu[4] = get_string('courseenrolstartdate', 'block_gerenciamento') . ' (' . userdate($course->enrolstartdate, $timeformat) . ')';
			}
			if($course->enrolenddate > 0) {
				$basemenu[5] = get_string('courseenrolenddate', 'block_gerenciamento') . ' (' . userdate($course->enrolenddate, $timeformat) . ')';
			}
		}
	}

	echo $OUTPUT->header();

	echo $OUTPUT->heading($course->fullname);

	echo "<form method=\"post\" action=\"prorrogacaomoodle.php\">\n";
	echo '<input type="hidden" name="id" value="'.$course->id.'" />';
	echo '<input type="hidden" name="sesskey" value="'.$USER->sesskey.'" />';
	
	$table = new html_table();
	$table->head  = array (get_string('fullname', 'block_gerenciamento'), get_string('timestart', 'block_gerenciamento'), 
						get_string('timeend', 'block_gerenciamento'), get_string('extendperiod', 'block_gerenciamento'), 
						get_string('startingfrom', 'block_gerenciamento'), get_string('turma', 'block_gerenciamento'));
	$table->align = array ('left', 'center', 'center', 'center','left','center');
	$table->width = "600";
	$nochange = get_string('nochange', 'block_gerenciamento');
	$notavailable = get_string('notavailable', 'block_gerenciamento');

	$count = 0;

	$sql = "SELECT ra.id, ra.userid,
					ue.timestart, ue.timeend, ue.enrolid,
					u.idnumber, u.firstname, u.lastname, u.lastnamephonetic, u.firstnamephonetic, u.middlename, u.alternatename
			FROM {user} u
			INNER JOIN {role_assignments} ra
				ON u.id=ra.userid
			INNER JOIN {enrol} e
				ON e.courseid = $course->id
			INNER JOIN {user_enrolments} ue
				ON ue.userid = u.id
				AND enrolid = e.id
			WHERE u.id=$iduser AND ra.contextid = $context->id";

	$users = $DB->get_recordset_sql($sql);
		
	foreach ($users as $key => $user) {
		$userbasemenu = $basemenu;
		if ($user->timestart) {
			$timestart = userdate($user->timestart, $timeformat);
		} else {
			$timestart = $notavailable;
			unset($userbasemenu[1]);
		}
		if ($user->timeend) {
			$timeend = userdate($user->timeend, $timeformat);
		} else {
			$timeend = $unlimited;
			unset($userbasemenu[2]);
		}

		$group = "SELECT name
						FROM {groups} g
						LEFT JOIN {groups_members} gm
						ON (g.id = gm.groupid)
						WHERE g.courseid = $course->id
						AND gm.userid = $iduser
						ORDER BY gm.id DESC
						LIMIT 1";

		$group = $DB->get_record_sql($group);

		if (isset($group->name)) {
			$group_name = $group->name;
		}else{
			$group_name = get_string('semturma','block_gerenciamento');
		}

		$checkbox = html_writer::select($periodmenu, "dados[$count][extendperiod]=$iduser", '', $nochange);
		$checkbox2 = html_writer::select($userbasemenu, "dados[$count][extendbase]=$iduser", "3", "");

		$table->data[] = array(
		fullname($user, true),
		$timestart,
		$timeend,
			'<input type="hidden" name="dados['.$count.'][userid]" value="'.$user->userid.'" />'.
			'<input type="hidden" name="dados['.$count.'][enrolid]" value="'.$user->enrolid.'" />'.
			$checkbox,
		$checkbox2,
		$group_name.
			'<input type="hidden" name="chave" value="'.$chave.'"/>');
		$count++;
	}

	echo html_writer::table($table);
	echo "<br><div style=\"width:100%;text-align:center;\"><input type=\"submit\" value=\"".get_string('savechanges', 'block_gerenciamento')."\" /><input type=\"button\" value=\"Voltar\" onClick=\"history.go(-1)\"/></div>\n</form>\n";

} else {
	redirect(new moodle_url($CFG->wwwroot), get_string('erropermissao', 'block_gerenciamento'));
}
echo $OUTPUT->footer();

function busca_cursos_fase($userEnrolments){
	GLOBAL $DB;

	$sql = 'SELECT ue.*, e.courseid
            FROM mdl_user_enrolments ue
            INNER JOIN mdl_fase f ON f.ecm_produto_id = ue.ecm_produto_id
            INNER JOIN mdl_enrol e on e.id = ue.enrolid
            WHERE ue.userid = ? AND ue.ecm_produto_id = ?';

	if($result = $DB->get_records_sql($sql, [$userEnrolments->userid, $userEnrolments->ecm_produto_id]))
		return $result;

	return false;
}
?>
