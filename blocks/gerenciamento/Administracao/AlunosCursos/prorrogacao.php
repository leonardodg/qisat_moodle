<?php
require_once('../../../../config.php');
require_once('forms/consultageral_form.php');
require_once($CFG->dirroot.'/blocks/gerenciamento/lib.php');

global $CFG, $DB, $USER, $SITE;

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url('/blocks/gerenciamento/Administracao/AlunosCursos/prorrogacao.php');
$PAGE->set_title(get_string('prorrogarcurso', 'block_gerenciamento'));
$PAGE->navigation->add(get_string('prorrogarcurso', 'block_gerenciamento'));

$PAGE->navbar->add(get_string('blockname', 'block_gerenciamento'))->
	add(get_string('administracao', 'block_gerenciamento'))->
	add(get_string('alunosecursos', 'block_gerenciamento'))->
	add(get_string('prorrogarcurso', 'block_gerenciamento'), '/blocks/gerenciamento/Administracao/AlunosCursos/prorrogacao.php');

$PAGE->set_heading($SITE->fullname);

$PAGE->set_pagelayout('incourse');

$chave = optional_param('chave', null, PARAM_TEXT);

echo $OUTPUT->header();

if (has_capability('block/gerenciamento:prorrogacao', $context)) {

	$form = new blocks_gerenciamento_consultageral_form($CFG->wwwroot.'/blocks/gerenciamento/Administracao/AlunosCursos/prorrogacao.php', array('chave'=>$chave));
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
						  ,(
								SELECT f.descricao
								FROM {user_enrolments} ue1
								INNER JOIN {enrol} e1 ON e1.id = ue1.enrolid
								INNER JOIN {groups} g ON g.courseid = e1.courseid
								INNER JOIN {groups_members} gm ON gm.groupid = g.id AND ue1.userid = gm.userid
								INNER JOIN {fase} f ON f.id = g.mdl_fase_id
								INNER JOIN ecm_produto_mdl_course pc ON f.ecm_produto_id = pc.ecm_produto_id AND pc.mdl_course_id = e1.courseid
								WHERE ue1.userid = ue.userid and e1.courseid = c.id
						  ) as descricao_fase

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
				$prorrogacoesilimitadas = false;
				if(has_capability('block/gerenciamento:prorrogacoesilimitadas', $context)){
					$prorrogacoesilimitadas = true;
				}	

				foreach ($result as $key => $value) {

					$statusCurso = buscaStatusCurso($value->idcurso, $user->id);

					$data = '';
					$prorrogar2dias = '';

					if ($statusCurso->datafim != 0 && !$statusCurso->agendado) {
						if (($statusCurso->data_prorrogacao == "") && (!$statusCurso->finalizado) && ($statusCurso->diasrestantes <= 0) && $value->role_shortname == "student") {
							if (has_capability('block/gerenciamento:prorrogarcursos', context_course::instance($value->idcurso)) || has_capability('block/gerenciamento:prorrogarcursos', $context)) {
								$prorrogar2dias = "<form name=Prorrogar method=post action='../../Central/AlunosAcessos/prorrogar2dias.php?course=$value->idcurso&iduser=$user->id' onSubmit='return Confirma(this);'>
														<input name=course type=hidden value='$value->idcurso'/>
														<input name=iduser type=hidden value='$user->id'/>
														<input name=chave type=hidden value='$chave'/>
														<input type=submit value='".get_string('prorrogar2dias', 'block_gerenciamento')."'/>
												   </form>";
							} else {
								$data = '<span class="situacao-status-prorrogado">'.get_string('nenhumaprorrogacao', 'block_gerenciamento').'</span>';
							}
						} else if (!isset($statusCurso->data_prorrogacao) || $statusCurso->dias_prorrogados == 0) {
							$data = '<span class="situacao-status-prorrogado">'.get_string('nenhumaprorrogacao', 'block_gerenciamento').'</span>';
						} else {
							$data = '<span class="situacao-status-prorrogado">'.get_string('prorrogado', 'block_gerenciamento')." ".$statusCurso->dias_prorrogados.get_string('dias', 'block_gerenciamento').'</span><br><span class="situacao-data">'.get_string('em', 'block_gerenciamento').date("d/m/Y", $statusCurso->data_prorrogacao).'</span>';
						}
					}

					if (has_capability('block/gerenciamento:verhistorico', $context)) {
						$data .= "<div><a href='".$CFG->wwwroot."/blocks/gerenciamento/Central/AlunosAcessos/historico.php?iduser=$user->id&courseid=$value->idcurso&chave=$chave'>".get_string('historico', 'block_gerenciamento')."</a></div>";
					}

					$acao = "";
					$acao = $prorrogar2dias;

					if($statusCurso->situacao == "expirado" || (!is_null($value->descricao_fase) && $statusCurso->situacao != "liberado")){
						$exibirBotaoProrrogar = true;

						if(!has_capability('block/gerenciamento:prorrogacoesilimitadas', $context))
							$exibirBotaoProrrogar = !curso_prorrogado($value->idcurso, $user->id);

						if($exibirBotaoProrrogar){
							$acao .= "<form method='post' action='prorrogacaomoodle.php'>
											<input type='hidden' name='id' value='$value->idcurso'/>
											<input type='hidden' name='iduser' value='$user->id'/>
											<input type='hidden' name='chave' value='$chave'/>
											<input type='submit' value='".get_string('prorrogar', 'block_gerenciamento')."'>
										</form>";
						}
					}

					if($statusCurso->agendado){
						$acao .= "<form method='post' action='editenrolment.php'>
										<input type='hidden' name='ue' value='$value->id'/>
										<input type='hidden' name='course' value='$value->idcurso'/>
										<input type='hidden' name='user' value='$user->id'/>
										<input type='submit' value='".get_string('reagendar', 'block_gerenciamento')."'>
									</form>";
					}

					if($acao == ""){
						$acao = "Nenhuma ação disponível";
					}

					$fase = '';
					if(!is_null($value->descricao_fase))
						$fase .= '<br /><b>'.$value->descricao_fase.'</b>';

					$table->data[] = array($name,
						$value->shortname.' - '.$statusCurso->turma.'<br><a href="'.$CFG->wwwroot.'/blocks/completionstatus/details.php?course='.
						$value->idcurso.'&user='.$user->id.'">'.get_string('andamento','block_gerenciamento') . ' <b>' .
						str_replace('.',',',$statusCurso->andamento) . '%</b></a>'.$fase,
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

function curso_prorrogado($idcurso, $iduser){
	global $DB;

	$sql = 'SELECT COUNT(p.id) as total
			FROM {prorrogacoes} p
			WHERE p.userid = ? AND p.courseid = ?';

	if($result = $DB->get_record_sql($sql, [$iduser, $idcurso]))
		return $result->total > 0;

	return false;
}

?>