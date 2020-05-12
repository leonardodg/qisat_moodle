<?php
require_once '../../config.php';
require_once 'forms_gerenciamento/gerenciarcurso_form.php';
require_once 'forms_gerenciamento/gerenciarcategorias_form.php';
//php.ini:allow_url_include=on
$filename = '../../local/nuvemtags/lib.php';
if (file_exists($filename)) {
	include_once $filename;
}

$cid = optional_param ( 'cid', '1', PARAM_INT );

$context = context_course::instance($cid);
$PAGE->set_context($context);
$PAGE->set_url('/blocks/faq/gerenciarcategorias.php');
$PAGE->set_title(get_string('gerenciarcategorias', 'block_faq'));
$PAGE->navigation->add(get_string('gerenciarcategorias', 'block_faq'));

$PAGE->navbar->add(get_string('gerenciarcategorias', 'block_faq'),
	new moodle_url('/blocks/faq/gerenciarcategorias.php', array('cid'=>$cid)));

echo '<link href="'.$CFG->wwwroot.'/blocks/faq/css/nestable.css" rel="stylesheet">';

echo $OUTPUT->header();

if (has_capability ( 'block/faq:manage', $context )) {

	$mform = new blocks_faq_gerenciarcurso_form($CFG->wwwroot.'/blocks/faq/gerenciarcategorias.php?cid='.$cid, array('cid'=>$cid));
	$mform->display ();
	if ($fromform = $mform->get_data()) {
		$cid = $fromform->cid;
	}

	if (file_exists($filename)) {
		get_nuvemtags_botao_gerenciador($cid, 'blocks', 'faq');
	}

	if ($cid>1) {

		$mform2 = new blocks_faq_gerenciarcategorias_form($CFG->wwwroot.'/blocks/faq/gerenciarcategorias.php?cid='.$cid, array('cid'=>$cid));
		$mform2->display ();

	} else {
		print_error(get_string('selecioneCurso', 'block_faq'));
	}

} else {
	redirect($CFG->wwwroot.'/course/view.php?id='.$cid, get_string('erroAcesso','block_faq'));
}

echo $OUTPUT->footer();

?>