<?php
global $CFG;
require_once ($CFG->libdir . '/formslib.php');
class blocks_faq_gerenciarcurso_form extends moodleform {
	function definition() {
		global $CFG, $DB;
		
		$cid = $this->_customdata['cid'];
		
		$mform = & $this->_form;
		
		$mform->addElement ( 'header', 'head', get_string ( 'filtroCurso', 'block_faq' ) );
		$mform->addElement ( 'static', 'descricao', get_string ( 'descricaoGerenciarCategorias', 'block_faq' ) );
		$mform->setExpanded ( 'head', false );
				
		$opcoes = $DB->get_records_select_menu('course', 'id != 1', null, '', 'id,shortname');
		$opcoes[0] = get_string ( 'nenhumCursoSelecionado', 'block_faq' );
		ksort($opcoes);
		$mform->addElement ( 'select', 'cid', get_string ( 'selecioneCurso', 'block_faq' ), $opcoes );
		$mform->setDefault('cid', $cid);

		$mform->addElement ( 'submit', 'submitbutton', get_string ( 'consultar', 'block_faq' ) );
	}
}
	