<?php
require_once($CFG->libdir.'/formslib.php');

class blocks_gerenciamento_consultageral_form extends moodleform {
	
	function definition () {
		global $DB;

		$mform = $this->_form;

		$chave = isset($this->_customdata['chave'])?$this->_customdata['chave']:null;

		$mform->addElement('header', 'titulo', get_string('consultar', 'block_gerenciamento'));

		$mform->addElement('text', 'chave', get_string('chave', 'block_gerenciamento'));
		$mform->setType('chave', PARAM_TEXT );
		$mform->setDefault('chave', $chave );
		$mform->addRule('chave', 'teste', 'required', 'server');
         
        $mform->addElement('submit', 'submitbutton', get_string('enviar', 'block_gerenciamento'));
	}

	function validation($data, $files) {
		$errors= array();

		if (empty($data['chave'])) {
			$errors['chave'] = get_string('chaveErro', 'block_gerenciamento');
		}
		
		return $errors;
	}
}