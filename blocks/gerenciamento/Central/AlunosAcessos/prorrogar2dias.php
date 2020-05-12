<?php
require_once("../../../../config.php");
require_once('../../lib.php');

$idcourse = required_param('course', PARAM_INT); 
$iduser = required_param('iduser', PARAM_INT);
$chave = optional_param('chave', null, PARAM_TEXT);

global $USER, $DB, $SITE;

if (!$course = get_my_course($iduser, $idcourse)) {
	error('Incorrect course id');
}
//if ($course->id != SITEID) {
require_login();
//}

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url('/blocks/gerenciamento/Central/AlunosAcessos/prorrogar2dias.php');
$PAGE->set_title(get_string('prorrogar2dias', 'block_gerenciamento'));
$PAGE->navigation->add(get_string('prorrogar2dias', 'block_gerenciamento'));

$PAGE->set_heading($SITE->fullname);

$PAGE->navbar->ignore_active();
$PAGE->navbar->add(get_string('blockname', 'block_gerenciamento'), null);
$PAGE->navbar->add(get_string('centraldeinscricoes', 'block_gerenciamento'), null);
$PAGE->navbar->add(get_string('alunoseacessos', 'block_gerenciamento'), null);
$PAGE->navbar->add(get_string('prorrogar2dias', 'block_gerenciamento'), new moodle_url($PAGE->url));

$PAGE->set_pagelayout('incourse');

echo $OUTPUT->header();

	if (has_capability('block/gerenciamento:prorrogar2dias', $context)) {
		
		$prorrogacao = new object;
		$prorrogacao->userid = $iduser;
		$prorrogacao->courseid = $idcourse;
		$prorrogacao->nu_dias_prorrogacao = 2;
		$prorrogacao->data_prorrogacao = time();
		$prorrogacao->timemodified = time();
		$prorrogacao->usermodified = $USER->id;
		
		if ($DB->insert_record('prorrogacoes', $prorrogacao)) {
/*
			$context = $DB->get_record('context', array('instanceid'=>$idcourse, 'contextlevel'=>'50'));

			$time = $DB->get_record('role_assignments', array('contextid'=>$context->id, 'userid'=>$iduser));
			$data = time();
			$time->timeend = mktime(23, 59, 59, date('m', $data), date('d', $data) + 2, date('Y', $data));
			$time->timemodified = time();
			$DB->update_record('role_assignments', $time);
*/
			$sql = "select ue.*
						from {user_enrolments} ue 
						INNER JOIN {enrol} co 
							ON ue.enrolid = co.id 
						WHERE ue.userid = ".$iduser.
						" AND co.courseid = ".$idcourse;
			$result = $DB->get_record_sql($sql);
			$data = time();
			$result->timeend = mktime(23, 59, 59, date('m', $data), date('d', $data) + 2, date('Y', $data));
			$result->modifierid = $USER->id;
			$result->timemodified = time();
			$DB->update_record("user_enrolments", $result);			
			//-----------------------------------------***E-mail***-----------------------------------------
			$recipient = $DB->get_record('user', array('id'=>$iduser));
			
			$fromsite = new object;
			$fromsite->firstname = get_site()->fullname;
			$fromsite->lastname = '';
			$fromsite->lastnamephonetic = '';
			$fromsite->firstnamephonetic = '';
			$fromsite->middlename = '';
			$fromsite->alternatename = '';
			$fromsite->email = $CFG->noreplyaddress;
			$fromsite->maildisplay = true;
			$fromsite->mailformat  = 1;
			
			$subject = get_site()->shortname.' | '.get_string('subject', 'block_gerenciamento');
			$subjectstudent = get_site()->shortname.' | '.get_string('subjectstudent', 'block_gerenciamento');

			// definition of $messagehtml
			$a = $course->fullname;
			$messagehtml_student = get_string('cursoprorrogado', 'block_gerenciamento', $a);
			
			$a = $course->fullname;
			$messagehtml_admin = get_string('cursoprorrogado', 'block_gerenciamento', $a);
			
			$a = $recipient->firstname . " " . $recipient->lastname;
			$messagehtml_admin .= get_string('usuarioprorrogado', 'block_gerenciamento', $a);
			
			$a = 2;
			$messagehtml = get_string('tempoprorrogacao', 'block_gerenciamento', $a);
			
			$a = date("d/m/Y" ,$prorrogacao->data_prorrogacao);
			$messagehtml .= get_string('iniciadoem', 'block_gerenciamento', $a);
			
			$a = date("d/m/Y" ,$time->timeend);
			$messagehtml .= '<br>'.get_string('finalizaem', 'block_gerenciamento', $a);
			
			$a = date("d/m/Y H:i:s");
			$messagehtml .= get_string('dataprorrogacaoparam', 'block_gerenciamento', $a);
										   
			$titulo_email = get_string('prorrogacaoprazo', 'block_gerenciamento');
			$messagehtml_student =  stripslashes_str($messagehtml_student.$messagehtml);
			//----------$messagehtml_student = email_body($messagehtml_student, $titulo_email);
			
			$messagehtml_admin =  stripslashes_str($messagehtml_admin.$messagehtml);
			//----------$messagehtml_admin = email_body($messagehtml_admin, $titulo_email);
			// end of definition of $messagehtml

			// definition of $messagetext
			//--$messagetext_student =  str_replace('<br />', "\n", $messagehtml_student);
			//--$messagetext_student =  strip_tags($messagetext_student);
			//--$messagetext_admin =  str_replace('<br />', "\n", $messagehtml_admin);
			//--$messagetext_admin =  strip_tags($messagetext_admin);
			// end of definition of $messagetext
			
			if (!email_to_user($recipient, $fromsite, $subjectstudent, '', $messagehtml_student) ) {
				echo 'An error was encountered sending an email in sendmessage.php. It is likely that your email settings are not configured properly. The error reported was "'. print_r(error_get_last()) .'"<br />';
			}
			$admin = get_admin();
			if (!email_to_user($admin, $fromsite, $subject, '', $messagehtml_admin) ) {
				echo 'An error was encountered sending an email in sendmessage.php. It is likely that your email settings are not configured properly. The error reported was "'. print_r(error_get_last()) .'"<br />';
			}
			
			redirect($CFG->wwwroot.'/blocks/gerenciamento/Central/AlunosAcessos/consultageral.php?chave='.$chave, get_string('cursoprorrogado', 'block_gerenciamento'));
		} else {
			redirect($CFG->wwwroot.'/blocks/gerenciamento/Central/AlunosAcessos/consultageral.php?chave='.$chave, get_string('naoprorrogado', 'block_gerenciamento'));
		}
	} else {
		redirect(new moodle_url($CFG->wwwroot), get_string('erropermissao', 'block_gerenciamento'));
	}

echo $OUTPUT->footer();

?>