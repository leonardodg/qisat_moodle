<?php
require_once($CFG->libdir.'/formslib.php');

class blocks_gerenciamento_relatorioandamentoporpedido_form extends moodleform {
	
	function definition () {
		global $DB;

		$mform = $this->_form;

		//$pedido = $this->_customdata['pedido'];
		$chave = $this->_customdata['chave'];

		$mform->addElement('header', 'titulo', get_string('consultar', 'block_gerenciamento'));

		/*
		$mform->addElement('text', 'pedido', get_string('pedido', 'block_gerenciamento'));
		$mform->setType('pedido', PARAM_TEXT);
		$mform->setDefault('pedido', $pedido);
		*/

		$mform->addElement('text', 'chave', get_string('chave', 'block_gerenciamento'));
		$mform->setType('chave', PARAM_TEXT);
		$mform->setDefault('chave', $chave);

		$mform->registerNoSubmitButton('submitbutton');
        $mform->addElement('submit', 'submitbutton', get_string('enviar', 'block_gerenciamento'));
	}
}