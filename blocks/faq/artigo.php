<?php
require_once '../../config.php';
require_once 'forms_gerenciamento/artigo_form.php';

global $DB;

$cid = optional_param ( 'cid', '1', PARAM_INT );
$categoria = optional_param ( 'categoria', '0', PARAM_INT );

$context = context_course::instance($cid);
$PAGE->set_context($context);
$PAGE->set_url('/blocks/faq/artigo.php');
$PAGE->set_title(get_string('artigo', 'block_faq'));
$PAGE->navigation->add(get_string('artigo', 'block_faq'));

$PAGE->navbar->add(get_string('artigo', 'block_faq'),
	new moodle_url('/blocks/faq/artigo.php', array('cid'=>$cid, 'categoria'=>$categoria)));

echo $OUTPUT->header();

if (has_capability ( 'block/faq:manage', $context )) {
	
	$parametros = array('cid'=>$cid, 'categoria'=>$categoria);
	$form = new blocks_faq_artigo_form ( $CFG->wwwroot . '/blocks/faq/artigo.php?cid='.$cid.'&categoria='.$categoria, $parametros );
	
	if ($data = $form->get_data ()) {
		echo '<span>Salvando dados.</span>';

		$idartigo = $data->idartigo;
		
		$saveartigo = new stdClass;
		$saveartigo->idcategoria = $data->categoria;
		$saveartigo->titulo = $data->titulo;
		$saveartigo->assunto = $data->assunto;
		$saveartigo->artigo = $data->editor['text'];
		$saveartigo->idautor = $data->select_autor;
		if ($data->idartigo > 0) {
			$saveartigo->id = $idartigo;
			$DB->update_record ( 'faq_artigo', $saveartigo );
		} else {
			$ordemsql = "SELECT MAX(ordem) as ordem FROM {$CFG->prefix}faq_artigo
			where idcategoria = " . $data->categoria . " AND id != " . $idartigo;
			$ordem = $DB->get_record_sql ( $ordemsql );
			
			$saveartigo->ordem = $ordem->ordem + 1;
			$saveartigo->datacriacao = time ();
			$idartigo = $DB->insert_record ( 'faq_artigo', $saveartigo );
		}
		
		$tags = explode ( ";", $data->tags );
		$tags = array_unique($tags);
		$savetags = array();
		foreach ( $tags as $tag ) {
			if (trim($tag) != '') {
				$savetag = new stdClass;
				$savetag->idartigo = $idartigo;
				$savetag->descricao = $tag;
				$savetags[] = $savetag;
			}
		}
		$DB->insert_records ( 'faq_tags', $savetags );

		redirect ( $CFG->wwwroot . '/blocks/faq/gerenciarcategorias.php?cid=' . $data->cid, '', - 1, false );
	} else {
		$form->display ();
	}
} else {
	redirect($CFG->wwwroot.'/course/view.php?id='.$cid, get_string('erroAcesso','block_faq'));
}

echo $OUTPUT->footer();

?>