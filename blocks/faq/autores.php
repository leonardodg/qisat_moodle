<?php
require_once '../../config.php';
require_once 'forms_gerenciamento/autores_form.php';

$cid = optional_param ( 'cid', '1', PARAM_INT );
$categoria = optional_param ( 'categoria', '0', PARAM_INT );

$context = context_course::instance($cid);
$PAGE->set_context($context);
$PAGE->set_url('/blocks/faq/autores.php');
$PAGE->set_title(get_string('autores', 'block_faq'));
$PAGE->navigation->add(get_string('autores', 'block_faq'));

$PAGE->navbar->add(get_string('autores', 'block_faq'),
	new moodle_url('/blocks/faq/autores.php', array('cid'=>$cid, 'categoria'=>$categoria)));

echo $OUTPUT->header();

if (has_capability ( 'block/faq:manage', $context )) {
	
	$parametros = array('cid'=>$cid, 'categoria'=>$categoria);
	$form = new blocks_faq_autores_form ( $CFG->wwwroot . '/blocks/faq/autores.php?cid='.$cid.'&categoria='.$categoria, $parametros );
	
	$form->display ();
	
} else {
	redirect($CFG->wwwroot.'/course/view.php?id='.$cid, get_string('erroAcesso','block_faq'));
}

echo $OUTPUT->footer();

?>