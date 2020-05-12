<?php
/**
 * Formulário para buscar usuário para enviar o lembrete de senha
 *
 * @author Deyvison Fernandes Baldoino 
 */

require_once($CFG->libdir.'/formslib.php');

class blocks_gerenciamento_alterarnome_form extends moodleform {
	
	function definition () {
		global $DB;

		$id = $this->_customdata['id'];
		$user = $DB->get_record('user', array('id' => $id), 'idnumber,firstname,lastname,email,username', MUST_EXIST);

		$mform = $this->_form;

		$mform->addElement('header', 'titulo', get_string('alterarnome', 'block_gerenciamento'));

		$mform->addElement('hidden', 'id');
		$mform->setType('id', PARAM_INT);
		$mform->setDefault('id', $id);

		$mform->addElement('static', 'chave', get_string('chave', 'block_gerenciamento'));
		$mform->setType('chave', PARAM_TEXT );
		$mform->setDefault('chave', $user->idnumber);

		$mform->addElement('text', 'firstname', get_string('nome', 'block_gerenciamento'));
		$mform->setType('firstname', PARAM_TEXT );
		$mform->setDefault('firstname', $user->firstname);

		$mform->addElement('text', 'lastname', get_string('sobrenome', 'block_gerenciamento'));
		$mform->setType('lastname', PARAM_TEXT );
		$mform->setDefault('lastname', $user->lastname);

		$mform->addElement('static', 'email', get_string('email', 'block_gerenciamento'));
		$mform->setType('email', PARAM_TEXT );
		$mform->setDefault('email', $user->email);

		$mform->addElement('static', 'username', get_string('username', 'block_gerenciamento'));
		$mform->setType('username', PARAM_TEXT );
		$mform->setDefault('username', $user->username);

		$this->add_action_buttons();
	}

	function validation($data, $files) {
		$errors= array();

		if (trim($data['nome']) == '') {
			$errors['nome'] = get_string('informeOCampo', 'block_gerenciamento');
		}

		return $errors;
	}
}