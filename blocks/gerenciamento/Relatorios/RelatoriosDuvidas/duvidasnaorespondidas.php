<?php
require_once('../../../../config.php');
require_once('../../lib.php');
require_once ('forms/duvidasnaorespondidas_form.php');

global $CFG, $DB, $USER, $SITE;

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url('/blocks/gerenciamento/Relatorios/RelatoriosDuvidas/duvidasnaorespondidas.php');
$PAGE->set_title(get_string('duvidasnaorespondidas', 'block_gerenciamento'));
$PAGE->navigation->add(get_string('duvidasnaorespondidas', 'block_gerenciamento'));

$PAGE->set_heading($SITE->fullname);

$PAGE->navbar->add(get_string('blockname', 'block_gerenciamento'))->
	add(get_string('relatorios', 'block_gerenciamento'))->
	add(get_string('relatoriosdeduvidas', 'block_gerenciamento'))->
	add(get_string('duvidasnaorespondidas', 'block_gerenciamento'), '/blocks/gerenciamento/Relatorios/RelatoriosDuvidas/duvidasnaorespondidas.php');

$PAGE->set_heading($SITE->fullname);

$PAGE->set_pagelayout('incourse');

$has_capability = false;
$mycourses  = get_my_courses($USER->id);
$mycoursesids = array();
foreach ($mycourses as $mycourse){	
	if (has_capability('block/gerenciamento:duvidasnaorespondidas', $context = context_course::instance($mycourse->id))) {
		$mycoursesids[] = $mycourse->id;
		$has_capability = true;
	}
}

if ((has_capability('block/gerenciamento:duvidasnaorespondidas', $context)) || $has_capability) {
	echo $OUTPUT->header();
	echo $OUTPUT->heading(get_string('duvidasnaorespondidas', 'block_gerenciamento'));

	echo '<script>

	$(function(){
		$(".downloadcsv").click(function(){
			DownloadJSON2CSV($("#jsoncsv").html())
		});
	});

	function DownloadJSON2CSV(objArray)
	{

	 var array = typeof objArray != \'object\' ? JSON.parse(objArray) : objArray;
	  var str = \'\';

	  for (var i = 0; i < array.length; i++) {
		var line = \'\';
		for (var index in array[i]) {
		  if(line != \'\') line += \';\'

		  line += array[i][index];
		}

		str += line + \'\r\n\';
	  }

		var uri = \'data:text/csv;charset=utf-8,\' + escape(str);
		var link = document.createElement("a");
		link.href = uri;

		link.style = "visibility:hidden";
		link.download = "duvidas_pendentes.csv";

		document.body.appendChild(link);
		link.click();
		document.body.removeChild(link);
	}

</script>';

	$link = $CFG->wwwroot.'/blocks/gerenciamento/Relatorios/RelatoriosDuvidas/duvidasnaorespondidas.php';
	$form = new blocks_gerenciamento_duvidasnaorespondidas_form($link);

	$idCurso = null;
	$idGrupo = null;

	if ($data = $form->get_data ()) {
		$idCurso = $data->select_curso;
		$idGrupo = $data->idgrupo;
	}
	$form->display ();
	echo '<button class="downloadcsv">Download CSV</button>';

	$sql = "select
			td.id,
			td.idcurso, 
			td.iduser, 
			td.hora_duvida, 
			td.duvida, 
			td.log_browser,
			td.last_section,
			td.last_resource,
			td.last_resource_time,
			tdc.categoria, 
			max(g.id) as idturma,
			max(g.name) as turma,
			u.firstname,
			u.lastname,
			u.idnumber,
			c.fullname,
			c.shortname  
			
			from (((((
			{tira_duvidas} td
				JOIN {tira_duvidas_categorias} tdc
					ON td.idcategoria = tdc.id)
				JOIN {user} u 
					ON u.id = td.iduser) 
				LEFT JOIN {groups_members} gm
				 	ON td.iduser = gm.userid) 
				LEFT JOIN {groups} g
					ON g.courseid = td.idcurso AND g.id = gm.groupid)
				JOIN {course} c
					ON c.id = td.idcurso)	
					
			where hora_resposta is null";
			
		if (!has_capability('block/gerenciamento:duvidasnaorespondidas', $context)) {
			$sql .= " AND td.idcurso in (".implode(',',$mycoursesids).")";
		}else{
			if(!empty($idCurso))
				$sql .= ' AND c.id = '.$idCurso;
			if(!empty($idGrupo))
				$sql .= ' AND g.id = '.$idGrupo;
		}
			
		$sql .= " group by td.id
				order by idturma DESC, hora_duvida";

		$result = $DB->get_records_sql($sql);

		$turmaAtual = 0;
		$cursoAtual = 0;
		$usuarioAtual = 0;
		$count = 0;
		$dataAtual = new DateTime();

		$dadosDownload[] = [
								'turma' => 'Turma',
								'chave' => 'Chave',
								'nome' => 'Nome',
								'dadosBrowser' => 'Dados do Browser',
								'dataRegistro' => 'Data de Registro',
								'categoria' => 'Categoria',
								'dataVencimento' => 'Data de Vencimento',
								'duvida' => 'Duvida'
							];

		foreach ($result as $r) {
			$browser = $DB->get_record('log_browser', array('id'=>$r->log_browser));
			$lastcoursesection = $DB->get_record('course_sections', array('id'=>$r->last_section));
			$lastresource = $DB->get_record('resource', array('id'=>$r->last_resource));

			$browser_str = '';
			if(isset($browser->id)){
				$browser_str = '<br>';
				if($browser->mobile == 'no'){
					$browser->mobile = get_string('no');
				}else{
					$browser->mobile = get_string('yes');
				}
				$browser_str .= '<font style="font-weight:normal">'.get_string('infotecnicas', 'block_tira_duvidas', $browser).'</font>';
				$browser_str .= '<br><br>';
			}

			$lastresource_str = '';
			if(isset($lastresource->id)){
				$lastresource->summary = strip_tags($lastcoursesection->summary);
				$lastresource->time_str = userdate($r->last_resource_time);
				//$lastresource->time_str = date('l, d M Y, H:i', $r->last_resource_time);
				$lastresource_str .= '<font style="font-weight:normal">'.get_string('infolastresource', 'block_tira_duvidas', $lastresource).'</font>';
				$lastresource_str .= '<br><br>';
			}

			$linkresponder = $CFG->wwwroot.'/blocks/tira_duvidas/responder.php?idduvida='.$r->id.'&cid='.$r->idcurso;
			$responder = '';
			if (has_capability('block/tira_duvidas:responder', context_course::instance($r->idcurso), $USER->id))
			$responder = '<a href='.$linkresponder.' target="_blank" title="'.get_string('cliqueresponder', 'block_gerenciamento').'"><input type="button" value="'.get_string('responder', 'block_gerenciamento').'"/></a>';
			
			if (($turmaAtual != $r->idturma) || ($cursoAtual != $r->idcurso) || ($usuarioAtual != $r->iduser)) {
				if(isset($table)){
					echo html_writer::table($table);
				}
				$table = new html_table();
			}
			if (($turmaAtual != $r->idturma) || ($cursoAtual != $r->idcurso)) {
				if ($count != 0) {
					echo $OUTPUT->heading(get_string('totalsemresposta', 'block_gerenciamento').': '.$count, 5);
					echo "<br>";
					$count = 0;
				}
				echo "<br>";
				if($r->turma == ''){
					echo $OUTPUT->heading($r->fullname, 5);
				}else{
					echo $OUTPUT->heading($r->turma . ' (' . $r->shortname . ')', 5);
				}
			}
			$turmaAtual = $r->idturma;
			$cursoAtual = $r->idcurso;
			$usuarioAtual = $r->iduser;
			$count++;

			$link = '<a href="'.$CFG->wwwroot.'/user/profile.php?id='.$r->iduser.'">';

			$table->width = '90%';
			$table->size = array ('65%', '10%', '15%');
			$table->align = array ('left', 'center', 'center');

			$table->head = array(
			$link.$r->idnumber.' - '.$r->firstname.' '.$r->lastname.'</a>',
			get_string('dataenvio', 'block_gerenciamento'),
			get_string('categoria', 'block_gerenciamento'),
			get_string('datavencimento', 'block_gerenciamento'));

			$dataDuvida = new DateTime();
			$dataDuvida->setTimestamp($r->hora_duvida);
			$dayNumberWeek = $dataDuvida->format('w');

			switch($dayNumberWeek){
				case 0:
					$dataDuvida->setTime(0,0,0);
					$dataDuvida->modify((24*3).' hours');
					break;
				case 4:
					$dataDuvida->modify((24*4).' hours');
					break;
				case 5:
					$dataDuvida->modify((24*4).' hours');
					break;
				case 6:
					$dataDuvida->setTime(0,0,0);
					$dataDuvida->modify((24*4).' hours');
					break;
				default:
					$dataDuvida->modify('48 hours');
			}

			$dataVencimento = userdate($dataDuvida->getTimestamp());
			if($dataAtual > $dataDuvida)
				$dataVencimento = '<span style="color: red;">'.userdate($dataDuvida->getTimestamp()).'</span>';

			$table->data[] = array (
				$r->duvida.'<br>'.$browser_str.$lastresource_str.$responder,
				userdate($r->hora_duvida).'<br />',
				//date('l, d M Y, H:i', $r->hora_duvida),
				$r->categoria,
				$dataVencimento
			);

			$dadosDownload[] = [
				'turma' => $r->turma . ' (' . $r->shortname . ')',
				'chave' => $r->idnumber,
				'nome' => $r->firstname.' '.$r->lastname,
				'dadosBrowser' => strip_tags($browser_str.$lastresource_str),
				'dataRegistro' => userdate($r->hora_duvida),
				'categoria' => $r->categoria,
				'dataVencimento' => strip_tags($dataVencimento),
				'duvida' => str_replace('&nbsp;', ' ', strip_tags($r->duvida))
			];
			
		}

		echo '<div id="jsoncsv" style="display: none;">'.json_encode($dadosDownload).'</div>';

		if(isset($table)){
			echo html_writer::table($table);
		}

		echo $OUTPUT->heading(get_string('totalsemresposta', 'block_gerenciamento').': '.$count, 5);

		echo '<button class="downloadcsv">Download CSV</button>';

} else {
	redirect(new moodle_url($CFG->wwwroot), get_string('erropermissao', 'block_gerenciamento'));
}

echo $OUTPUT->footer();
?>