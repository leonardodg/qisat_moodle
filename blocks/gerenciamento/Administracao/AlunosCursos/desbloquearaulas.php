
<?php
require_once('../../../../config.php');
require_once('forms/aulasbloqueadas_form.php');

global $CFG, $DB, $USER, $SITE;

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url('/blocks/gerenciamento/Administracao/AlunosCursos/desbloquearaulas.php');
$PAGE->set_title(get_string('desbloquearaulas', 'block_gerenciamento'));
$PAGE->navigation->add(get_string('desbloquearaulas', 'block_gerenciamento'));

$PAGE->navbar->add(get_string('blockname', 'block_gerenciamento'))->
	add(get_string('administracao', 'block_gerenciamento'))->
	add(get_string('alunosecursos', 'block_gerenciamento'))->
	add(get_string('desbloquearaulas', 'block_gerenciamento'), '/blocks/gerenciamento/Administracao/AlunosCursos/desbloquearaulas.php');

$PAGE->set_heading($SITE->fullname);

$PAGE->set_pagelayout('incourse');

$chave = optional_param('chave', null, PARAM_TEXT);
$curso = optional_param('curso', 0, PARAM_INT);

echo $OUTPUT->header();

if (has_capability('block/gerenciamento:desbloquearaulas', $context)) {
		
	$form = new blocks_gerenciamento_aulasbloqueadas_form($CFG->wwwroot.'/blocks/gerenciamento/Administracao/AlunosCursos/desbloquearaulas.php');

	if ($data = $form->get_data ()) {
		$chave = $data->chave;
		$curso = $data->curso;
	}
	$chave = trim($chave);
	$form->display();

	if (isset($chave) && $curso) {
			
		if (is_numeric($chave))
			$user = $DB->get_record('user', array('idnumber'=>$chave));
		else 
			$user = $DB->get_record('user', array('username'=>$chave));
			
		$course = $DB->get_record('course', array('id'=>$curso));

		if($user) {

			if(isset($_POST["selecoes"])) {
				foreach ($_POST["selecoes"] as $key => $value) {
					$aulaLiberada = new stdClass();
					$aulaLiberada->userid = $user->id;
					$aulaLiberada->courseid = $curso;
					$aulaLiberada->section = substr($key, 4);
					$aulaLiberada->usermodified = $USER->id;
					$aulaLiberada->data_liberacao = time();
					$DB->insert_record("aulas_liberadas", $aulaLiberada);

					/**
					 * Email de desbloqueio da aula
					 */
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

					$fromemail = new object;
					$fromemail->aluno = $user->firstname . " " . $user->lastname;

					$sql = 'SELECT cs.name, c.fullname FROM {course_sections} cs
							INNER JOIN {course} c ON c.id = cs.course
							WHERE cs.id = ' . substr($key, 4);
					$courseSection = $DB->get_record_sql($sql);
					$fromemail->aula = $courseSection->name;
					$fromemail->curso = $courseSection->fullname;

					$fromemail->diadesbloqueio = date('d/m/Y - H:i:s');
					$fromemail->mailformat = 1;

					$emailsubject = get_string('auladesbloqueada', 'block_gerenciamento');
					$emailbody = get_string('auladesbloqueadaaluno', 'block_gerenciamento', $fromemail);
					if (!email_to_user($user, $fromsite, get_site()->shortname .' | '.$emailsubject, '', $emailbody) ) {
						mtrace("An error was encountered sending an email to " . $fromemail->username ." - ". $fromemail->nome);
					}else{
						mtrace("Email sent to " . $fromemail->username ." - ". $fromemail->nome);
						$admin = get_admin();
						$admin->mailformat = 1;

						$emailbody = get_string('auladesbloqueadaadmin', 'block_gerenciamento', $fromemail);
						email_to_user($admin, $fromsite, get_site()->shortname .' | '.$emailsubject, '', $emailbody);
					}

					$sql = "UPDATE {course_section_access} SET tempo_total = tempo_utilizado + ".($course->timeaccesssection*3600)
						. " WHERE user_id=".$user->id." AND course_section_id=".substr($key, 4);
			    	$DB->execute($sql);
				}
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
			
			$sql = "select ue.id
					from {user_enrolments} ue,
						 {enrol} en
					where ue.userid = $user->id
					and ue.enrolid = en.id
					and en.courseid = ".$course->id;
					
			if ($result = $DB->get_record_sql($sql)) {

				$action = $CFG->wwwroot.'/blocks/gerenciamento/Administracao/AlunosCursos/desbloquearaulas.php?chave='.$chave.'&curso='.$curso;

				echo "<form method='post' action='$action'>";

				$sql = "select csa.id,
							   csa.tempo_total,
					   		   csa.tempo_utilizado,
							   csa.course_section_id as section,
							   cs.name
						from {course_section_access} csa,
							 {course_sections} cs
						where csa.user_id = $user->id
						  and cs.id = csa.course_section_id
						  and cs.course = $course->id
						order by cs.course,
								 csa.course_section_id";

				$aulasBloqueadas = array();
				if ($result = $DB->get_records_sql($sql)) {

					foreach ($result as $r){
						$horas_restantes = $r->tempo_total - $r->tempo_utilizado;
				
						if($horas_restantes <= 0) {
							$section = $r->section;
							$nomeAula = strip_tags($r->name);
							if (empty($nomeAula)) {
								$nomeAula = "Aula ".$section;
							}

							$sql = "select MAX(fim_acesso) as data_bloqueio from {course_section_log} where course_section_id = $section AND user_id = $user->id";
							$data_bloqueio = $DB->get_record_sql($sql);
							$horas_restantes = get_string('bloqueadaem', 'block_gerenciamento').date("d/m/Y", $data_bloqueio->data_bloqueio);
							if(!isset($data_bloqueio->data_bloqueio)){
								$horas_restantes = get_string('naoencontrado', 'block_gerenciamento');
							}
							$aulasBloqueadas[] = array ($nomeAula, $horas_restantes, '<input type="checkbox" name="selecoes[aula'.$section.']" />');
						}
					}
				}

				if(sizeof($aulasBloqueadas)){
					$table = new html_table();
					$table->size = array('70%', '20%', '10%');
					$table->align = array('left', 'center', 'center');
					$table->head = array($course->fullname, get_string('databloqueio', 'block_gerenciamento'), get_string('desbloquearaulas', 'block_gerenciamento'));
					$table->data = $aulasBloqueadas;
					echo html_writer::table($table);

					echo "<br><div style='width:100%;text-align:center;'><input type='submit' value='".get_string('desbloquearaulas', 'block_gerenciamento')."'/></form>";
				} else {
					$msgErro = array(get_string('nenhumaaulabloqueada', 'block_gerenciamento'));
				}
			} else {
				$msgErro = array(get_string('naopossuicurso', 'block_gerenciamento'));
			}
		} else {
			$msgErro = array(get_string('alunonaoencontrado', 'block_gerenciamento'));
		}
		if(isset($msgErro)){
			$table = new html_table();
			$table->align = array("center");
			$table->head = array($course->fullname);
			$table->data[] = $msgErro;
			echo html_writer::table($table);
		}
	}
} else {
	redirect(new moodle_url($CFG->wwwroot), get_string('erropermissao', 'block_gerenciamento'));
}		

echo $OUTPUT->footer();

?>