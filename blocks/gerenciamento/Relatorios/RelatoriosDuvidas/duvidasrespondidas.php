<?php
require_once ('../../../../config.php');
require_once ('../../lib.php');
require_once ('forms/duvidasrespondidas_form.php');

global $CFG, $DB, $USER, $SITE;

$sort    = optional_param('sort', get_string('datahora', 'block_gerenciamento'), PARAM_ALPHANUM);
$dir     = optional_param('dir', 'ASC', PARAM_ALPHA);
$perpage = optional_param('perpage', 20, PARAM_INT);
$page    = optional_param('page', 0, PARAM_INT);

$idcurso       = optional_param('idcurso', 0, PARAM_INT);
$chave         = optional_param('chave', null, PARAM_ALPHANUM);
$dataInicio    = optional_param('dataInicio', 0, PARAM_INT);
$dataFim       = optional_param('dataFim', 0, PARAM_INT);
$iddificuldade = optional_param('iddificuldade', 0, PARAM_INT);

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url('/blocks/gerenciamento/Relatorios/RelatoriosDuvidas/duvidasrespondidas.php');
$PAGE->set_title(get_string('duvidasrespondidas', 'block_gerenciamento'));
$PAGE->navigation->add(get_string('duvidasrespondidas', 'block_gerenciamento'));

$PAGE->navbar->add(get_string('blockname', 'block_gerenciamento'))->
	add(get_string('relatorios', 'block_gerenciamento'))->
	add(get_string('relatoriosdeduvidas', 'block_gerenciamento'))->
	add(get_string('duvidasrespondidas', 'block_gerenciamento'), '/blocks/gerenciamento/Relatorios/RelatoriosDuvidas/duvidasrespondidas.php');

$PAGE->set_heading($SITE->fullname);

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

if ((has_capability('block/gerenciamento:duvidasrespondidas', $context)) || $has_capability){
	echo '<link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
		  <script src="//code.jquery.com/jquery-1.10.2.js"></script>
		  <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>';

	echo $OUTPUT->header();
	echo $OUTPUT->heading(get_string('duvidasrespondidas', 'block_gerenciamento'));
	
	$link = $CFG->wwwroot.'/blocks/gerenciamento/Relatorios/RelatoriosDuvidas/duvidasrespondidas.php';
	$parametros = array('idcurso'=>$idcurso);
	$form = new blocks_gerenciamento_duvidasrespondidas_form($link, $parametros);
	
	if ($data = $form->get_data ()) {
		$idcurso = $data->idcurso;
		$chave = trim($data->chave);
		$dataInicio = $data->pesquisa_inicio;
		$dataFim = $data->pesquisa_fim;
		$iddificuldade = (int)$data->select_dificuldade;
	}
	$form->display ();

	if (isset($chave)) {
		$parametros = array('sort' => $sort, 'dir' => $dir, 'perpage' => $perpage, 'idcurso' => $idcurso, 'chave' => $chave, 
							'dataInicio' => $dataInicio, 'dataFim' => $dataFim, 'iddificuldade' => $iddificuldade);

		$where = '';
		if ($idcurso > 0) {
			$where .= " AND td.idcurso = ".$idcurso;
		}
		if (!empty($chave)) {
			$where .= " AND u.firstname like '%".$chave."%'";
			$where .= " OR u.lastname like '%".$chave."%'";
			$where .= " OR u.idnumber like '".$chave."'";
		}
		if ($dataInicio > 0 && $dataFim > 0) {
			$where .= " AND hora_resposta between " . $dataInicio . " AND " . $dataFim;
		} else if ($dataInicio > 0) {
			$where .= " AND hora_resposta >= " . $dataInicio;
		} else if ($dataFim > 0) {
			$where .= " AND hora_resposta <= " . $dataFim;
		}
		if ($iddificuldade > 0) {
			$where .= " AND tds.id = $iddificuldade";
		}
		
		$sql = "select
					td.id,
					td.hora_duvida,
					td.hora_resposta,
					td.duvida,
					td.resposta,
					td.log_browser,
					td.last_section,
					td.last_resource,
					td.last_resource_time,
					td.vai_para_faq, 
					u.firstname,
					u.lastname,
					u.idnumber,
					tds.status";
		
		$sqlJoin = " from (((
					{tira_duvidas} td
					JOIN {user} u
						ON u.id = td.iduser)
					JOIN {tira_duvidas_status_duvida} tdsd
						ON td.id = tdsd.idduvida)
					JOIN {tira_duvidas_status} tds
						ON tdsd.idstatus = tds.id)
					where hora_resposta not like ''".$where;

		if (!has_capability('block/gerenciamento:duvidasrespondidas', $context)) {
			$sqlJoin .= " AND td.idcurso in (".implode(',',$mycoursesids).")";
		} 			
		$sqlJoin .= " order by hora_duvida DESC";
		
		$sqlTotal = "select COUNT(*) total " . $sqlJoin;
		$resultTotal = $DB->get_record_sql($sqlTotal);

		if(isset($resultTotal) && $resultTotal->total != '0'){
			$table = new html_table();
			$table->head = array(get_string('datahora', 'block_gerenciamento'), get_string('pedido', 'block_gerenciamento'));
			$table->colclasses = array('mdl-align', 'mdl-left');

			$sql .= $sqlJoin;
			$result = $DB->get_records_sql($sql, null, $page*$perpage, $perpage);

			$table->data = array();

			$dificuldades = $DB->get_records('tira_duvidas_status');
			echo '<script type="text/javascript">
					<!--
						function getDificuldade(combobox, idduvida) {
							var idstatus = combobox.value;
							$.ajax({
								url: "ajax/updateDificuldade.php",
								data: {idstatus:idstatus, idduvida:idduvida},
								type: "POST"
							});
						}
						function alterarCheckBox(checkbox, idduvida) {
							var vai_para_faq = checkbox.checked ? 1 : 0;
							$.ajax({
								url: "ajax/updateVaiParaFaq.php",
								data: {vai_para_faq:vai_para_faq, idduvida:idduvida},
								type: "POST"
							});
						}
					-->
		   			</script>';

			foreach ( $result as $r ) {
			
				$browser = $DB->get_record('log_browser', array('id'=>$r->log_browser));
				if (isset($browser->id)) {
					$browser_str = '<br>';
					if ($browser->mobile == 'no') {
						$browser->mobile = get_string('no');
					} else {
						$browser->mobile = get_string('yes');
					}
					$browser_str .= '<font style="font-weight:normal">' . get_string('infotecnicas', 'block_contact_form', $browser) . '</font>';
					$browser_str .= '<br><br>';
				} else {
					$browser_str = '';
				}
				
				$combobox = 'Dificuldade:<select name="dificuldade_'.$r->id.'" 
								onchange="getDificuldade(this, '. $r->id .')">';
				
				foreach ($dificuldades as $dificuldade) {
					$combobox .= '<option value="' . $dificuldade->id . '"';
					if($dificuldade->status == $r->status){
						$combobox .= ' selected';
					}
					$combobox .= '>' . $dificuldade->status . '</option>';
				}
				$combobox .= '</select><br/><br/>';
				
				$checkbox = '<input type="checkbox" name="faq_'. $r->id .'" value="faq_'. $r->id .'" onchange="alterarCheckBox(this, '. $r->id .')"';
				if($r->vai_para_faq){
					$checkbox .= ' checked';
				}
				$checkbox .= '>';

				$lastresource_str = '';
				$lastcoursesection = $DB->get_record('course_sections', array('id'=>$r->last_section));
				$lastresource = $DB->get_record('resource', array('id'=>$r->last_resource));
				if(isset($lastresource->id)){
					$lastresource->summary = strip_tags($lastcoursesection->summary);
					$lastresource->time_str = userdate($r->last_resource_time);
					$lastresource_str .= '<font style="font-weight:normal">'.get_string('infolastresource', 'block_tira_duvidas', $lastresource).'</font>';
					$lastresource_str .= '<br><br>';
				}
				
				$data = array (
						get_string ( 'perguntaenviadaem', 'block_gerenciamento' ) . '<br>' . userdate ( $r->hora_duvida ) . "<hr/>" . 
						get_string ( 'respostaenviadaem', 'block_gerenciamento' ) . '<br>' . userdate ( $r->hora_resposta ) ,
						$r->duvida . '<br><b>' . $browser_str . $lastresource_str . $r->idnumber . ' - ' . $r->firstname . ' ' . $r->lastname . '</b>' . "<hr/>" . 
						$r->resposta . $combobox . get_string ( 'selecioneFaq','block_gerenciamento' ) . $checkbox
				);
				
				$table->data[] = $data;
			}

			$baseurl = new moodle_url($link, $parametros);
			echo $OUTPUT->paging_bar((int)$resultTotal->total, $page, $perpage, $baseurl);
			echo html_writer::table($table);
			echo $OUTPUT->paging_bar((int)$resultTotal->total, $page, $perpage, $baseurl);
		}
	}
} else {
	redirect(new moodle_url($CFG->wwwroot), get_string('erropermissao', 'block_gerenciamento'));
}
echo $OUTPUT->footer();
?>