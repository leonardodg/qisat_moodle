<?php
require_once('../../../../config.php');
require_once ('../../lib.php');
require_once('forms/temporespostaduvidas_form.php');

global $CFG, $DB, $USER, $SITE;

$idcurso = optional_param('idcurso', 0, PARAM_INT);

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url('/blocks/gerenciamento/Relatorios/RelatoriosDuvidas/temporespostaduvidas.php');
$PAGE->set_title(get_string('tempoderesposta', 'block_gerenciamento'));
$PAGE->navigation->add(get_string('tempoderesposta', 'block_gerenciamento'));

$PAGE->set_heading($SITE->fullname);

$PAGE->navbar->add(get_string('blockname', 'block_gerenciamento'))->
	add(get_string('relatorios', 'block_gerenciamento'))->
	add(get_string('relatoriosdeduvidas', 'block_gerenciamento'))->
	add(get_string('temporespostaduvidas', 'block_gerenciamento'), '/blocks/gerenciamento/Relatorios/RelatoriosDuvidas/temporespostaduvidas.php');

$PAGE->set_pagelayout('incourse');

$has_capability = false;
$mycourses  = get_my_courses($USER->id);
$mycoursesids = array();
foreach ($mycourses as $mycourse){	
	if (has_capability('block/gerenciamento:duvidasrespondidas', $context = context_course::instance($mycourse->id))) {
		$mycoursesids[] = $mycourse->id;
		$has_capability = true;
	}
}

if ((has_capability('block/gerenciamento:temporespostaduvidas', $context)) || $has_capability) {

	echo $OUTPUT->header();
	echo $OUTPUT->heading(get_string('tempoderesposta', 'block_gerenciamento'));

	$link = $CFG->wwwroot.'/blocks/gerenciamento/Relatorios/RelatoriosDuvidas/temporespostaduvidas.php';
	$parametros = array('idcurso'=>$idcurso);
	$form = new blocks_gerenciamento_temporespostaduvidas_form($link, $parametros);
	$form->display ();

	if ($data = $form->get_data()) {
		$sql = "select 
					td.id, 
					td.idcurso, 
					td.iduser, 
					td.hora_duvida, 
					td.hora_resposta, 
					u.firstname, 
					u.lastname, 
					c.fullname 
				
				from ((
				{tira_duvidas} td
					LEFT JOIN {user} u 
						ON u.id = td.iduser) 
					LEFT JOIN {course} c
						ON c.id = td.idcurso)";
				
		$where = '';
		if ($data->pesquisa_inicio > 0 && $data->pesquisa_fim > 0) {
			$where .= " AND hora_resposta between " . $data->pesquisa_inicio . " AND " . $data->pesquisa_fim;
		} else if ($data->pesquisa_inicio > 0) {
			$where .= " AND hora_resposta >= " . $data->pesquisa_inicio;
		} else if ($data->pesquisa_fim > 0) {
			$where .= " AND hora_resposta <= " . $data->pesquisa_fim;
		}

		if ($data->respondida == 1)
			$where .= " AND not isnull(hora_resposta)";
		elseif ($data->respondida == 2)
			$where .= " AND isnull(hora_resposta)";
			
		if ($data->idcurso)
			$where .= " AND c.id = " . $data->idcurso;
		
		if (!has_capability('block/gerenciamento:temporespostaduvidas', $context)) {
			$where .= " AND td.idcurso in (".implode(',',$mycoursesids).")";
		} 

		if($where != ''){
			$where = substr_replace($where, "WHERE", 0, 4);
		}	
		$sql .= $where." order by idcurso, hora_duvida";

		$result = $DB->get_records_sql($sql);

		$cursoAtual = 0;
		$count = 0;
		$tempoTotalRespostas = 0;
		if ($result) {
			foreach ($result as $r) {
				if ($cursoAtual != $r->idcurso) {
					if(isset($table)){
						echo html_writer::table($table);
					}
					$table = new html_table();
					$table->width = '100%';
					$table->size = array ('40%', '25%', '25%', '10%');
					$table->align = array ('left', 'center', 'center', 'center');
					$table->head = array(
						get_string('aluno', 'block_gerenciamento'),
						get_string('dataenvio', 'block_gerenciamento'),
						get_string('dataresposta', 'block_gerenciamento'),
						get_string('decorrido', 'block_gerenciamento'));

					if ($count != 0) {
						$media = (int)floor($tempoTotalRespostas / $count);

						echo $OUTPUT->heading(get_string('totalnocurso', 'block_gerenciamento').' '.$count, 5);
						echo $OUTPUT->heading(get_string('tempomedio', 'block_gerenciamento').' '.$media.' '.get_string('horas', 'block_gerenciamento'), 5);

						$count = 0;
						$tempoTotalRespostas = 0;
					}
					echo "<br><br>";
					echo $OUTPUT->heading($r->fullname, 5);
				}
				$cursoAtual = $r->idcurso;
				$count++;

				$link = '<a href="'.$CFG->wwwroot.'/user/profile.php?id='.$r->iduser.'">';
				if (is_null($r->hora_resposta)) {
					$hora_resposta = '';
					$decorrido = time() - $r->hora_duvida;
				} else {
					$hora_resposta = date("d/m/Y H:i:s", $r->hora_resposta);
					$decorrido = $r->hora_resposta - $r->hora_duvida;
				}
				$fds = weekend($r->hora_duvida, $r->hora_resposta);
				$diferenca = 0;
				if (isweekwend($r->hora_duvida)) {
					$diferenca = mktime(date('H', $r->hora_duvida), date('i', $r->hora_duvida), date('s', $r->hora_duvida), date('m', $r->hora_duvida), date('d', $r->hora_duvida), 
						date('Y', $r->hora_duvida)) - mktime(0,0,0, date('m', $r->hora_duvida), date('d', $r->hora_duvida), date('Y', $r->hora_duvida));
					$diferenca = (int)floor($diferenca / 3600);
				}

				$tempoResposta = (int)floor($decorrido / 3600);
				$decorrido = (int)floor($decorrido / 3600) - (24*$fds) + $diferenca;

				if($decorrido <0){
					$decorrido = $tempoResposta;
				}

				if ($decorrido > 48)
					$font = '<font color="red">';
				else
					$font = '';
					
				$tempoTotalRespostas += $decorrido;

				$table->data[] = array (
					$link.$r->firstname.' '.$r->lastname.'</a>',
					date("d/m/Y H:i:s", $r->hora_duvida),
					$hora_resposta,
					$font.$decorrido.' '.get_string('horas', 'block_gerenciamento').'</font>');
			}
			
			if(isset($table)){
				echo html_writer::table($table);
			}

			$media = (int)floor($tempoTotalRespostas / $count);
		
			echo $OUTPUT->heading(get_string('totalnocurso', 'block_gerenciamento').' '.$count, 5);
			echo $OUTPUT->heading(get_string('tempomedio', 'block_gerenciamento').' '.$media.' '.get_string('horas', 'block_gerenciamento'), 5);
		} else {
			echo $OUTPUT->heading(get_string('nenhumaduvidaencontrada', 'block_gerenciamento'), 5);
		}
	}
} else {
	redirect(new moodle_url($CFG->wwwroot), get_string('erropermissao', 'block_gerenciamento'));
}

function weekend($pesquisa_inicio, $pesquisa_fim){
	$fds = 0;
	if (is_null($pesquisa_fim))
		$pesquisa_fim = time();
	while ($pesquisa_inicio < $pesquisa_fim) {
		$date = date("l", $pesquisa_inicio);
		$date = strtolower($date);
		if ($date == "saturday" || $date == "sunday")
			$fds++;
		$pesquisa_inicio = mktime(0, 0, 0, date('m', $pesquisa_inicio), date('d', $pesquisa_inicio) + 1, date('Y', $pesquisa_inicio));
	}
	return $fds;
}

function isweekwend($date){
	$date = date("l", $date);
	$date = strtolower($date);
	if($date == "saturday" || $date == "sunday"){
		return true;
	} else {
		return false;
	}
}

echo $OUTPUT->footer();
?>