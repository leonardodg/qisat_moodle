<?php

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
						   WHERE ra.userid = ".$user->id."
						   GROUP BY ra.roleid
								   ,ra.contextid
								   ,ra.userid) AS matricula

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

					$inputPrazoExtra = '';
					if($statusCurso->situacao == 'expirado') {
						/*if (has_capability('block/gerenciamento:emailprazoextra', $context)) {
							$inputPrazoExtra = "<input type=button value='" . get_string('emailprazoextra.php', 'block_gerenciamento') . "' onClick='enviarEmail();'/>";
						}*/
					}

					$data = '';
					$prorrogar2dias = '';
					$prorrogar = '';

					if ($statusCurso->datafim != 0 && !$statusCurso->agendado) {
						if (($statusCurso->data_prorrogacao == "") && (!$statusCurso->finalizado) && ($statusCurso->diasrestantes <= 0) && $value->role_shortname == "student") {
							if (has_capability('block/gerenciamento:prorrogarcursos', context_course::instance($value->idcurso)) || has_capability('block/gerenciamento:prorrogarcursos', $context)) {
								$prorrogar2dias = "<form name=Prorrogar method=post action='prorrogar2dias.php' onSubmit='return Confirma(this);'>
														<input name=course type=hidden value='$value->idcurso'/>
														<input name=iduser type=hidden value='$user->id'/>
														<input name=chave type=hidden value='$chave'/>
														<input type=submit value='".get_string('prorrogar2dias', 'block_gerenciamento')."'/>
												   </form>";
								if($statusCurso->situacao == "expirado"){
									$prorrogar = "<form method='post' action='../../Administracao/AlunosCursos/prorrogacaomoodle.php'>
										<input type='hidden' name='id' value='$value->idcurso'/>
										<input type='hidden' name='iduser' value='$user->id'/>
										<input type='hidden' name='chave' value='$chave'/>
										<input type='submit' value='".get_string('prorrogar', 'block_gerenciamento')."'>
									</form>";
								}
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
						$data .= "<div><a href='historico.php?iduser=$user->id&courseid=$value->idcurso&chave=$chave'>".get_string('historico', 'block_gerenciamento')."</a></div>";
					}

					$form = "";

					if (has_capability('block/gerenciamento:excluircurso', $context)){
						$form = "<form name=Excluir method=post action='tabelas/unenroluser.php' >
									<input name=ue type=hidden value='$value->id'/>
									<input name=chave type=hidden value='$chave'/>
									<input type=submit value='".get_string('excluir', 'block_gerenciamento')."'/>
								</form>";
					}

					$andamento = '';
					$acessos = '';

					$agendar = '';
					if($statusCurso->agendado){
						$agendar = "<form method='post' action='../../Administracao/AlunosCursos/editenrolment.php'>
										<input type='hidden' name='ue' value='$value->id'/>
										<input type='hidden' name='course' value='$value->idcurso'/>
										<input type='hidden' name='user' value='$user->id'/>
										<input type='submit' value='".get_string('reagendar', 'block_gerenciamento')."'>
									</form>";
					} else {
						$andamento = '<br><a href="' . $CFG->wwwroot . '/blocks/completionstatus/alldetails.php?course=' . $value->idcurso . '&user=' . $user->id . '">' . get_string('andamento','block_gerenciamento') . ' <b>' . str_replace('.',',',$statusCurso->andamento) . '%</b></a>';
						$acessos = '<br><a href="' . $CFG->wwwroot . '/blocks/gerenciamento/Administracao/AlunosCursos/acessosaulas.php?course=' . $value->idcurso . '&user=' . $user->id . '">' . get_string('acessostabela','block_gerenciamento') . '</a>';
					}

					$acao = $form.$prorrogar2dias.$prorrogar.$agendar.$inputPrazoExtra;

					if(trim($acao == "")){
						$acao = get_string('noaction', 'block_gerenciamento');
					}

					$fase = '';
					if(!is_null($value->descricao_fase))
						$fase .= '<br /><b>'.$value->descricao_fase.'</b>';

					$table->data[] = array($name,
							$value->shortname.' - '.$statusCurso->turma.$andamento.$acessos.$fase,
							$statusCurso->inicio_descricao.$data,
							$statusCurso->situacao_descricao,
							$acao);
				}
				echo html_writer::table($table);

				echo "<SCRIPT LANGUAGE='JavaScript'>
						function Confirma(form) {
							if (confirm('".get_string('confirmarprorrogacao','block_gerenciamento')."')){
								document.form.submit();
							}else{
								return false;
							}
						}
					  </SCRIPT>";
				/*
				echo "<SCRIPT LANGUAGE='JavaScript'>
						function ConfirmaExclusao(form) {
							if (confirm('".get_string('confirmarexclusao','block_gerenciamento')."')){
								document.form.submit();
							}else {
								return false;
							}
						}
						</SCRIPT>";
				*/
				echo "<SCRIPT LANGUAGE='JavaScript'>
						function enviarEmail() {
							$(function() {
								$.ajax({
									type : 'POST',
									data : 'userid=".$user->id."',
									url : 'ajax/enviarEmailPrazoExtra.php',
									success: function(result) {
										alert(result);
									},
									error: function(result) {
										alert('Não foi possivel enviar o email para o cliente ".$user->firstname." ".$user->lastname."');
									}
								});
							});
						}
						</SCRIPT>";

			}