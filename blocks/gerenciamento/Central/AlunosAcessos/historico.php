<?php
//require_once('/../../course/lib.php');
require_once('../../../../config.php');
require_once('../../lib.php');
date_default_timezone_set("Brazil/East");

$userid = optional_param('iduser', 0, PARAM_INT);   // user id
$courseid  = optional_param('courseid', SITEID, PARAM_INT);   // course id (defaults to Site)
$chave = optional_param('chave', null, PARAM_TEXT);

global $CFG, $DB, $SITE;

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url('/blocks/gerenciamento/Central/AlunosAcessos/historico.php');
$PAGE->set_title(get_string('historico', 'block_gerenciamento'));
$PAGE->navigation->add(get_string('historico', 'block_gerenciamento'));

$PAGE->set_heading($SITE->fullname);

$PAGE->navbar->ignore_active();
$PAGE->navbar->add(get_string('blockname', 'block_gerenciamento'), null);
$PAGE->navbar->add(get_string('centraldeinscricoes', 'block_gerenciamento'), null);
$PAGE->navbar->add(get_string('alunoseacessos', 'block_gerenciamento'), null);
$PAGE->navbar->add(get_string('historico', 'block_gerenciamento'), new moodle_url($PAGE->url));

$PAGE->set_pagelayout('incourse');

echo $OUTPUT->header();

if (has_capability('block/gerenciamento:verhistorico', $context)) {

	if (!($course = get_my_course($USER->id, $courseid)))
		error('Invalid course id');

	$user = $DB->get_record('user', array('id'=>$userid));

	$nome = $user->firstname." ". $user->lastname;

	echo $OUTPUT->heading($course->fullname);

	//agendamentos
	echo $OUTPUT->heading(get_string('agendamentos', 'block_gerenciamento'), 3);

	$sql = "SELECT 	a.*,
					concat(u.firstname,' ', u.lastname) as usermodified
			FROM {agendamentos} a
			INNER JOIN {user} u
			ON u.id = a.usermodified
			WHERE a.userid = $user->id
			AND a.courseid = $course->id";

	if ($result = $DB->get_records_sql($sql)) {
		$table = new html_table();
		$table->align = array ("center", "center", "center", "center", "center");
		$table->size=array('12%','24%','20%','20%','24%');
		$table->head = array (get_string('quantidade', 'block_gerenciamento'), get_string('dataagendamento', 'block_gerenciamento'), get_string('agendadode', 'block_gerenciamento'), get_string('agendadopara', 'block_gerenciamento'), get_string('agendadopor', 'block_gerenciamento'));

		$i = 1;
		foreach ($result as $r){
			$table->data [] = array($i++, userdate($r->timemodified, '%c'), userdate($r->old_date, '%x'), date('d/m/Y',$r->new_date), $r->usermodified);
		}

		echo html_writer::table($table);
	} else {
		echo '<br>';
		echo $OUTPUT->notification(get_string('nenhumagendamentonocurso', 'block_gerenciamento'));
	}

	//prorrogações
	echo $OUTPUT->heading(get_string('prorrogacoes', 'block_gerenciamento'), 3);

	$sql = "SELECT p.timemodified, 
				p.nu_dias_prorrogacao, 
				p.data_prorrogacao, 
				concat(u.firstname,' ', u.lastname) as usermodified 
					FROM {prorrogacoes} p 
					INNER JOIN {user} u 
						ON u.id = p.usermodified 
				WHERE p.userid = $user->id 
					AND p.courseid = $course->id";

	if ($result = $DB->get_records_sql($sql)) {
		$table = new html_table();
		$table->align = array ("center", "center", "center", "center", "center");
		$table->size=array('12%','24%','20%','20%','24%');
		$table->head = array (get_string('quantidade', 'block_gerenciamento'), 
								get_string('dataprorrogacao', 'block_gerenciamento'), 
								get_string('extendperiod'), 
								get_string('startingfrom'), 
								get_string('prorrogadopor', 'block_gerenciamento'));

		$i = 1;
		foreach ($result as $r){
			$table->data [] = array($i++, 
									userdate($r->timemodified, '%c'), 
									$r->nu_dias_prorrogacao . ' ' . get_string('days'), 
									date('d/m/Y',$r->data_prorrogacao), 
									$r->usermodified);
		}

		echo html_writer::table($table);
	} else {
		echo '<br>';
		echo $OUTPUT->notification(get_string('nenhumaprorrogacaonocurso', 'block_gerenciamento'));
	}

	//aulas bloqueadas
	echo $OUTPUT->heading(get_string('aulasbloqueadas', 'block_gerenciamento'), 3, 'center');

	$sql = "SELECT
			al.id,
			al.data_liberacao,
			cs.summary,
			ab.data_bloqueio, CONCAT(u.firstname,' ', u.lastname) AS usermodified
			FROM {aulas_liberadas} al
			INNER JOIN {aulas_bloqueadas} ab ON ab.section = al.section AND ab.userid = al.userid
			INNER JOIN {course_sections} cs ON cs.course = al.courseid AND cs.id = al.section
			LEFT JOIN {user} u ON u.id = al.usermodified
			WHERE al.userid = ? AND al.courseid = ?
			GROUP BY al.data_liberacao";

	if ($result = $DB->get_records_sql($sql, [$user->id, $course->id])) {
		$table = new html_table();
		$table->align = array ("center", "center", "center", "center", "center");
		$table->size = array('12%','24%','20%','20%','24%');
		$table->head = array(get_string('quantidade', 'block_gerenciamento'), 
							get_string('aulabloqueada', 'block_gerenciamento'), 
							get_string('databloqueio', 'block_gerenciamento'), 
							get_string('datadesbloqueio', 'block_gerenciamento'), 
							get_string('desbloqueadapor', 'block_gerenciamento'));

		$i = 1;
		foreach ($result as $r){
			if ($r->data_liberacao){
				$data_lioberacao = userdate($r->data_liberacao, '%c');
				$usermodified = $r->usermodified;
			}else{
				$data_lioberacao = ' - ';
				$usermodified = ' - ';
			}

			$table->data [] = array($i++, 
									$r->summary, 
									userdate($r->data_bloqueio, '%c'), 
									$data_lioberacao, 
									$usermodified);
		}

		echo html_writer::table($table);
	} else {
		echo '<br>';
		echo $OUTPUT->notification(get_string('nenhumaauladesbloqueadanocurso', 'block_gerenciamento'));
	}

	//bloqueios do curso
	echo $OUTPUT->heading(get_string('bloqueiodocurso', 'block_gerenciamento'), 3);

	$sql = "SELECT 	bc.date_block,
					bc.date_unblock,
					concat(u.firstname, ' ', u.lastname) as usermodified,
					bc.bloqueio_plataforma
 			FROM mdl_bloqueio_curso bc
   			INNER JOIN mdl_user_enrolments ue
   			ON ue.id = bc.user_enrolments
   			INNER JOIN mdl_enrol e
   			ON e.id = ue.enrolid
   			INNER JOIN mdl_user u
   			ON u.id = ue.modifierid
   			WHERE  ue.userid = $user->id
            AND e.courseid = $course->id";

	if ($result = $DB->get_records_sql($sql)) {
		$table = new html_table();
		$table->align = array ("center", "center", "center", "center", "center");
		$table->size=array('12%','22%','22%','22%','22%');
		$table->head = array (get_string('quantidade', 'block_gerenciamento'),
			get_string('databloqueio', 'block_gerenciamento'),
			get_string('datadesbloqueio', 'block_gerenciamento'),
			get_string('bloqueadoplataforma', 'block_gerenciamento'),
			get_string('ultimaatualizacao', 'block_gerenciamento'));

		$i = 1;

		foreach ($result as $r){
			$date_unblock = get_string('cursobloqueado', 'block_gerenciamento');

			if(isset($r->date_unblock) && ($r->date_unblock > 0)){
				$date_unblock = date('d/m/Y H:i:s',$r->date_unblock);
			}elseif($r->bloqueio_plataforma){
				$date_unblock = get_string('bloqueadoplataforma', 'block_gerenciamento');
			}

			$bloqueado_plataforma = get_string('no');

			if($r->bloqueio_plataforma){
				$bloqueado_plataforma = get_string('yes');
			}

			$table->data [] = array($i++,
									date('d/m/Y H:i:s',$r->date_block),
									$date_unblock,
									$bloqueado_plataforma,
									$r->usermodified);
		}

		echo html_writer::table($table);
	} else {
		echo '<br>';
		echo $OUTPUT->notification(get_string('nenhumbloqueionocurso', 'block_gerenciamento'));
	}

	echo '<br><div style="text-align:center"><input type="button" value="'.get_string('back').'" onclick="window.history.back();return false;"/></div>';

} else {
	redirect(new moodle_url($CFG->wwwroot), get_string('erropermissao', 'block_gerenciamento'));
}

echo $OUTPUT->footer();
?>