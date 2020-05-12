<?php
require_once($CFG->libdir.'/formslib.php');

class blocks_gerenciamento_consultageral_form extends moodleform {
	
	function definition () {
		global $DB;

		$mform = $this->_form;

		$mform->addElement('header', 'titulo', get_string('consultar', 'block_gerenciamento'));

		$mform->addElement('text', 'chave', get_string('chave', 'block_gerenciamento'));
		$mform->setType('chave', PARAM_TEXT );

		$mform->addElement('text', 'email', get_string('email', 'block_gerenciamento'));
		$mform->setType('email', PARAM_TEXT );
         
        $mform->addElement('submit', 'submitbutton', get_string('enviar', 'block_gerenciamento'));
	}

	function validation($data, $files) {
		$errors= array();

		if (empty($data['chave']) && empty($data['email'])) {
			$errors['chave'] = get_string('chaveErro', 'block_gerenciamento');
		}
		
		return $errors;
	}
}