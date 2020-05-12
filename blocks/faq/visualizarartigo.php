<?php
require_once '../../config.php';
require_once 'forms_visualizacao/visualizarartigo_form.php';

global $DB;

$idartigo = optional_param ( 'idartigo', 0, PARAM_INT );

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url('/blocks/faq/visualizarartigo.php');
$PAGE->set_title(get_string('visualizarartigo', 'block_faq'));
$PAGE->navigation->add(get_string('visualizarartigo', 'block_faq'));

$PAGE->navbar->add(get_string('visualizarartigo', 'block_faq'),
	new moodle_url('/blocks/faq/visualizarartigo.php', array('idartigo'=>$idartigo)));

echo '<link href="' . $CFG->wwwroot . '/blocks/faq/jquery/jRating.jquery.css" type="text/css" rel="stylesheet" />';
echo '<link href="' . $CFG->wwwroot . '/blocks/faq/css/artigo.css" type="text/css" rel="stylesheet" />';

echo $OUTPUT->header();

if ($idartigo>0) {
	$artigo = $DB->get_record ( 'faq_artigo', array('id'=>$idartigo), 'id,visualizacoes');
	$artigo->visualizacoes++;
	$DB->update_record ( 'faq_artigo', $artigo );

	$mform = new blocks_faq_visualizarartigo_form($CFG->wwwroot . '/blocks/faq/visualizarartigo.php?idartigo='.$idartigo, array('idartigo'=>$idartigo));
	$mform->display ();
} else {
	print_error(get_string('selecioneArtigo', 'block_faq'));
}

echo $OUTPUT->footer();

?>