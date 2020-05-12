<?php
require_once('../../../../config.php');
require_once('forms/consultageral_form.php');
require_once($CFG->dirroot.'/blocks/gerenciamento/lib.php');

global $CFG, $DB, $USER, $SITE;

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url('/blocks/gerenciamento/Administracao/AlunosCursos/emitircertificado.php');
$PAGE->set_title(get_string('emitircertificado', 'block_gerenciamento'));
$PAGE->navigation->add(get_string('emitircertificado', 'block_gerenciamento'));

$PAGE->navbar->add(get_string('blockname', 'block_gerenciamento'))->
add(get_string('administracao', 'block_gerenciamento'))->
add(get_string('alunosecursos', 'block_gerenciamento'))->
add(get_string('emitircertificado', 'block_gerenciamento'), '/blocks/gerenciamento/Administracao/AlunosCursos/emitircertificado.php');

$PAGE->set_heading($SITE->fullname);

$PAGE->set_pagelayout('incourse');

$chave = optional_param('chave', null, PARAM_TEXT);
$courseid = optional_param('courseid', 0, PARAM_INT);
$userid = optional_param('userid', 0, PARAM_INT);

echo $OUTPUT->header();

if (has_capability('block/gerenciamento:emitircertificado', $context) ||
	has_capability('block/gerenciamento:visualizarcertificado', $context)) {

	$form = new blocks_gerenciamento_consultageral_form($CFG->wwwroot.'/blocks/gerenciamento/Administracao/AlunosCursos/emitircertificado.php', array('chave'=>$chave));
	if ($data = $form->get_data() || isset($chave)) {
		$form->display();
		if(!isset($chave)){
			$chave = $data->chave;
		}
		$chave = trim($chave);
		if (is_numeric($chave))
			$user = $DB->get_record('user', array('idnumber'=>$chave));
		else
			$user = $DB->get_record('user', array('username'=>$chave));

		if($user) {
			if (!$m = $DB->get_record('modules', array('name'=> 'certificate'))) {
				print_error('Course Module ID was incorrect');
			}

			if($courseid || $userid){
				require_once("$CFG->dirroot/mod/certificate/locallib.php");

				if (!$course = $DB->get_record('course', array('id'=> $courseid))) {
					print_error('course is misconfigured');
				}

				if (!$cm = $DB->get_record('course_modules', array('course'=> $courseid, 'module'=> $m->id))) {
					print_error('Course Module ID was incorrect');
				}

				if (!$certificate = $DB->get_record('certificate', array('id'=> $cm->instance))) {
					print_error('course module is incorrect');
				}

				// Create new certificate record, or return existing record
				$certrecord = certificate_get_issue($course, $user, $certificate, $cm);
			}

			$name = '<a href="'.$CFG->wwwroot.'/user/profile.php?id='.$user->id.'">'.$user->firstname . ' ' . $user->lastname.'</a>';
			$email = '<a href="'.$CFG->wwwroot.'/user/profile.php?id='.$user->id.'">'.$user->email.'</a>';

			echo html_writer::script('','https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js');
			$PAGE->requires->js('/blocks/gerenciamento/Central/AlunosAcessos/js/lembrarsenha.js');

			$urlLoading = $OUTPUT->pix_url('y/loading');
			$envioOk = $OUTPUT->pix_url('i/grade_correct');
			$envioErro = $OUTPUT->pix_url('i/grade_incorrect');

			$acaosenha = '<img class="enviarLembreteSenha" data-id="'.$user->id.'" data-load="'.$urlLoading.'" ';
			$acaosenha .= 'data-ok="'.$envioOk.'" data-erro="'.$envioErro.'" data-enviado="false"';
			$acaosenha .= 'src="'. $OUTPUT->pix_url('t/email').'" style="cursor:pointer;"/>';

			$table = new html_table();
			$table->align = array("center", "center", "center", "center", "center", "center");
			$table->head = array('ID', 'Chave AltoQi', 'Seu Nome', 'E-mail', 'Usuário', 'Ação');
			$table->data[] = array($user->id, $user->username, $name,
				$email, $user->username, $acaosenha);
			echo html_writer::table($table);

			$table = new html_table();
			$table->align = array("left", "left", "center", "center", "center");
			$table->size  = array('20%', '25%', '20%', '20%', '15%');
			$table->head = array('Dados do Aluno', 'Curso', 'Datas', 'Situação', 'Ação');

			$sql = "SELECT ue.id
						  ,ue.timestart
						  ,ue.timeend
						  ,ue.enrolid AS enrol
						  ,matricula.roleid
						  ,c.shortname
						  ,c.id AS idcurso
						  ,c.idnumber
						  ,r.shortname as role_shortname

					FROM ( SELECT ra.roleid
								 ,ra.contextid
								 ,ra.userid
						   FROM {role_assignments} ra
						   WHERE ra.userid = ".$user->id.") AS matricula

					INNER JOIN {context} co
					ON matricula.contextid = co.id

					INNER JOIN {course} c
					ON c.id = co.instanceid

					INNER JOIN {enrol} e
					ON e.courseid = c.id

					INNER JOIN {user_enrolments} ue
					ON ue.userid = matricula.userid
					AND ue.enrolid = e.id

					INNER JOIN {role} r
					ON r.id = matricula.roleid

					ORDER BY ue.timeend DESC";

			$result = $DB->get_records_sql($sql);

			if (count($result)) {

				$sql = "SELECT c.id, c.course
					FROM {certificate} c
					INNER JOIN {certificate_issues} ci ON c.id = ci.certificateid
					WHERE ci.userid = " . $user->id;
				$certificate_issues = $DB->get_records_sql_menu($sql);

				foreach ($result as $key => $value) {

					$statusCurso = buscaStatusCurso($value->idcurso, $user->id);

					$data = '';

					if (has_capability('block/gerenciamento:verhistorico', $context)) {
						$data .= "<div><a href='".$CFG->wwwroot."/blocks/gerenciamento/Central/AlunosAcessos/historico.php?iduser=$user->id&courseid=$value->idcurso&chave=$chave'>".get_string('historico', 'block_gerenciamento')."</a></div>";
					}

					$acao = "";

					switch($statusCurso->situacao){
						case 'finalizado':
							$acao = get_string('cursofinalizado', 'block_gerenciamento');
							$acao .= "<br>".get_string('noaction', 'block_gerenciamento');
							break;
						case 'curso_bloqueado':
							$acao = get_string('cursobloqueado', 'block_gerenciamento');
							$acao .= "<br>".get_string('noaction', 'block_gerenciamento');
							break;
						case 'usuario_bloqueado':
							$acao = get_string('usuariobloqueado', 'block_gerenciamento');
							$acao .= "<br>".get_string('noaction', 'block_gerenciamento');
							break;
						case 'prazo_ilimitado':
						case 'liberado':
						case 'expirado':

							if($statusCurso->finalizado){
								$acao = get_string('cursofinalizado', 'block_gerenciamento');
								$acao .= "<br>".get_string('noaction', 'block_gerenciamento');
							} elseif($statusCurso->andamento < 90){
								$acao = get_string('naoatingiu', 'block_gerenciamento');
								$acao .= "<br>".get_string('noaction', 'block_gerenciamento');
							}else if(has_capability('block/gerenciamento:emitircertificado', $context)){
								$acao .= "<form method='post' action='emitircertificado.php'>
												<input type='hidden' name='chave' value='$chave'/>
												<input type='hidden' name='courseid' value='$value->idcurso'/>
												<input type='hidden' name='userid' value='$user->id'/>
										  		<input type='submit' value='".get_string('emitircertificado', 'block_gerenciamento')."'>
										  </form>";
							}
							break;
					}

					if(has_capability('block/gerenciamento:visualizarcertificado', $context)){
						if(in_array($value->idcurso, $certificate_issues)) {
							if ($cm = $DB->get_record('course_modules', array('course' => $value->idcurso, 'module' => $m->id))) {
								$acao .= "<br/><br/><b><a href='" . $CFG->wwwroot . "/mod/certificate/view.php?id=" . $cm->id . "&action=get&userid=" . $user->id . "' target='_blank'>" .
									get_string('visualizarcertificado', 'block_gerenciamento') . "</a></b>";
							}
						}
					}

					if($acao == ""){
						$acao = get_string('noaction', 'block_gerenciamento');
					}

					$table->data[] = array($name,
						$value->shortname.' - '.$statusCurso->turma.'<br><a href="'.$CFG->wwwroot.'/blocks/completionstatus/details.php?course='.$value->idcurso.'&user='.$user->id.'">'.get_string('andamento','block_gerenciamento') . ' <b>' . str_replace('.',',',$statusCurso->andamento) . '%</b></a>',
						$statusCurso->inicio_descricao.$data,
						$statusCurso->situacao_descricao,
						$acao);
				}
				echo html_writer::table($table);
			} else {
				$msgErro = array(get_string('naoencontrado', 'block_gerenciamento'))." Teste";
			}
		} else {
			$msgErro = array(get_string('alunonaoencontrado', 'block_gerenciamento'));
		}
		if(isset($msgErro)){
			$table = new html_table();
			$table->align = array("center");
			if(isset($course)){
				$table->head = array($course->fullname);
			}else{
				$table->head = array(get_string('cursonaoencontrado', 'block_gerenciamento'));
			}
			$table->data[] = $msgErro;
			echo html_writer::table($table);
		}
	} else {
		$form->display();
	}
} else {
	redirect(new moodle_url($CFG->wwwroot), get_string('erropermissao', 'block_gerenciamento'));
}

echo $OUTPUT->footer();

?>