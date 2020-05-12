<?php
global $CFG;

require_once ($CFG->libdir . '/formslib.php');

class blocks_faq_pesquisarapida_form extends moodleform {
	function definition() {
		global $CFG;
		
		$mform = & $this->_form;
		
		$mform->addElement ( 'header', 'head', get_string ( 'pesquisarapida', 'block_faq' ) );
		$mform->addElement ( 'static', 'descricao', get_string ( 'descricaoVisualizarCategorias', 'block_faq' ) );
		
		$pesquisa = array ();
		$pesquisa [] = & $mform->createElement ( 'text', 'pesquisa_text', '' );
		$pesquisa [] = & $mform->createElement ( 'submit', 'pesquisa_button', get_string ( 'Pesquisar', 'block_faq' ) );
		$mform->addGroup ( $pesquisa, 'pesquisa', get_string ( 'pesquisarapida', 'block_faq' ), '', '' );
		$mform->setType ( 'pesquisa_text', PARAM_TEXT );
		
		$radiobutton = array ();
		$radiobutton [] = & $mform->createElement ( 'radio', 'radiogrupo', '', get_string ( 'conteudoEspaco', 'block_faq' ), 0 );
		$radiobutton [] = & $mform->createElement ( 'radio', 'radiogrupo', '', get_string ( 'tituloEspaco', 'block_faq' ), 1 );
		$radiobutton [] = & $mform->createElement ( 'radio', 'radiogrupo', '', get_string ( 'ambos', 'block_faq' ), 2 );
		$mform->addGroup ( $radiobutton, 'tipo', get_string ( 'formapesquisa', 'block_faq' ), '', '' );
		$mform->setDefault ( 'radiogrupo', 0 );

	}
}
	