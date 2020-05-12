<?php

global $CFG, $COURSE, $DB, $USER;

require ("../../../config.php");
require ("$CFG->libdir/tablelib.php");

$cid = optional_param('cid', 0, PARAM_INT);

$course = get_course($cid);
//require_course_login($course);
require_login();
$context = context_course::instance($cid);
if(!is_enrolled($context))
	require_course_login($course);
	//require_login($course, false, $cm);

require_capability('block/tira_duvidas:historico', $context);
$PAGE->set_context($context);
$PAGE->set_url('/blocks/tira_duvidas/historico/historico.php');
$PAGE->set_title(get_string('tituloTiraDuvidas', 'block_tira_duvidas'));
$PAGE->navigation->add(get_string('tituloTiraDuvidas', 'block_tira_duvidas'));

$PAGE->set_heading($course->fullname);

$category = $DB->get_record('course_categories', array('id' => $course->category), '*', MUST_EXIST);

$PAGE->navbar->ignore_active();
$PAGE->navbar->add($category->name, new moodle_url('/course/index.php', array('category' => $category->id)));
$PAGE->navbar->add($course->fullname, new moodle_url('/course/view.php', array('id' => $cid)));
$PAGE->navbar->add(get_string('tituloTiraDuvidas', 'block_tira_duvidas'), null);
$PAGE->navbar->add(get_string('historico', 'block_tira_duvidas'), null);

$PAGE->set_pagelayout('incourse');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('historico', 'block_tira_duvidas'));

echo '<br /><div class="box generalbox boxaligncenter boxwidthnormal" style="text-align:center">';
echo get_string('historico', 'block_tira_duvidas');
echo '</div>';

$duvidassql =  "SELECT *
FROM {tira_duvidas} td
WHERE td.iduser = $USER->id
AND td.idcurso = $cid
ORDER BY hora_duvida DESC ";

$duvidas = $DB->get_records_sql($duvidassql);
$count = 0;
foreach ($duvidas as $d) {
	$count++;
		
	$categoria = $DB->get_record('tira_duvidas_categorias', array('id'=>$d->idcategoria));
	
	$table = new html_table();
	$table->width = '90%';
	$table->size = array ('65%', '10%', '15%');
	$table->align = array ('left', 'center', 'center');
	$table->head = array(
			$USER->idnumber.' - '.fullname($USER),
			get_string('dataenvio', 'block_tira_duvidas'),
			get_string('categoria', 'block_tira_duvidas'));
	$table->data[] = array (
			'<span class="tituloduvida">'.get_string('duvida', 'block_tira_duvidas').':<br></span>'.$d->duvida.'<br>',
			userdate($d->hora_duvida),
			$categoria->categoria);
		
	if($d->resposta == ''){
		$resposta = '<span class="semresposta">'.get_string('semresposta','block_tira_duvidas').'</span>';
		$hora_resposta = '';
	}else{
		$resposta = '<span class="tituloduvida">'.get_string('resposta', 'block_tira_duvidas').':<br></span>'.$d->resposta;
		$hora_resposta = userdate($d->hora_resposta);
	}
	$table->data[] = array (
			$resposta.'<br>',
			$hora_resposta);
		
	echo html_writer::table($table);
	echo "<br>";
}

$table = new html_table();
$table->width = '90%';
$table->head = array (get_string('totalduvidascurso', 'block_tira_duvidas').': '.$count);
echo html_writer::table($table);

echo '<div align="center"><input type="button" value="'.get_string('back').'" onClick="history.go(-1)"></div>';

echo $OUTPUT->footer();
