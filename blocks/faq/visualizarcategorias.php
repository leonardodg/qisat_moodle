<?php
require_once '../../config.php';
require_once 'forms_gerenciamento/gerenciarcurso_form.php';
require_once 'forms_visualizacao/pesquisarapida_form.php';
require_once 'forms_visualizacao/visualizarcategorias_form.php';
require_once 'forms_visualizacao/visualizarlistaartigos_form.php';
//php.ini:allow_url_include=on
$filename = '../../local/nuvemtags/lib.php';
if (file_exists($filename)) {
	include_once $filename;
}

$cid = optional_param('cid', '1', PARAM_INT);
$tag = optional_param('tag', null, PARAM_TEXT);
$categoria = optional_param('categoria', null, PARAM_TEXT);

$context = context_course::instance($cid);
$PAGE->set_context($context);
$PAGE->set_url('/blocks/faq/visualizarcategorias.php');
$PAGE->set_title(get_string('visualizarcategorias', 'block_faq'));
$PAGE->navigation->add(get_string('visualizarcategorias', 'block_faq'));

$PAGE->navbar->add(get_string('visualizarcategorias', 'block_faq'),
	new moodle_url('/blocks/faq/visualizarcategorias.php', array('cid'=>$cid)));

echo $OUTPUT->header();

if (file_exists($filename)) {
	get_nuvemtags_estilo(null);
}

echo '<link href="'.$CFG->wwwroot.'/blocks/faq/css/estilo.categoria.css" rel="stylesheet">';

if (has_capability ( 'block/faq:manage', $context )) {
	
	$mform = new blocks_faq_gerenciarcurso_form($CFG->wwwroot . '/blocks/faq/visualizarcategorias.php?cid='.$cid, array('cid'=>$cid));
	
	$mform->display ();

	if ($fromform = $mform->get_data()) {
		$cid = $fromform->cid;
	}

}

if ($cid>1) {

	$mform2 = new blocks_faq_pesquisarapida_form($CFG->wwwroot . '/blocks/faq/visualizarcategorias.php?cid='.$cid);
	$mform2->display ();

	if (isset($tag) || isset($categoria)){
		$parametros = array('cid'=>$cid, 'tag'=>$tag, 'categoria'=>$categoria);
		$mform3 = new blocks_faq_visualizarlistaartigos_form($CFG->wwwroot.'/blocks/faq/visualizarcategorias.php?cid='.$cid, $parametros);
	} else if($pesquisarapida = $mform2->get_data()) {
		$parametros = array('cid'=>$cid, 'pesquisa'=>$pesquisarapida->pesquisa_text, 'tipo'=>$pesquisarapida->radiogrupo);
		$mform3 = new blocks_faq_visualizarlistaartigos_form($CFG->wwwroot.'/blocks/faq/visualizarcategorias.php?cid='.$cid, $parametros);
	} else {
		$mform3 = new blocks_faq_visualizarcategorias_form($CFG->wwwroot.'/blocks/faq/visualizarcategorias.php?cid='.$cid, array('cid'=>$cid));
	}
	$mform3->display ();

	if (file_exists($filename) && !isset($parametros)) {
		$link = $CFG->wwwroot.'/blocks/faq/visualizarcategorias.php?cid='.$cid;
		get_nuvemtags($cid, 'blocks', 'faq', $link);
	}
	
} else {
	redirect($CFG->wwwroot.'/course/view.php?id='.$cid, get_string('erroAcesso','block_faq'));
}

echo $OUTPUT->footer();

?>