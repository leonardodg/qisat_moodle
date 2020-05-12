<?php
/**
 * Formulário para buscar usuário para enviar o lembrete de senha
 *
 * @author Deyvison Fernandes Baldoino 
 */

require_once($CFG->libdir.'/formslib.php');

class blocks_gerenciamento_alteraremail_form extends moodleform {
	
	function definition () {
		global $DB;

		$id = $this->_customdata['id'];
		$user = $DB->get_record('user', array('id' => $id), 'idnumber,firstname,lastname,email,username,emailstop', MUST_EXIST);

		$mform = $this->_form;

		$mform->addElement('header', 'titulo', get_string('alteraremail', 'block_gerenciamento'));

		$mform->addElement('hidden', 'id');
		$mform->setType('id', PARAM_INT);
		$mform->setDefault('id', $id);

		$mform->addElement('static', 'chave', get_string('chave', 'block_gerenciamento'));
		$mform->setType('chave', PARAM_TEXT );
		$mform->setDefault('chave', $user->idnumber);

		$mform->addElement('static', 'nome', get_string('nome', 'block_gerenciamento'));
		$mform->setType('nome', PARAM_TEXT );
		$mform->setDefault('nome', $user->firstname . ' ' . $user->lastname);

		$mform->addElement('text', 'email', get_string('email', 'block_gerenciamento'));
		$mform->setType('email', PARAM_TEXT );
		$mform->setDefault('email', $user->email);

		$mform->addElement('static', 'username', get_string('username', 'block_gerenciamento'));
		$mform->setType('username', PARAM_TEXT );
		$mform->setDefault('username', $user->username);

		$options = array(0 => get_string('no'), 1 => get_string('yes'));
		$mform->addElement('select', 'emailstop', get_string('emaildesabilitado', 'block_gerenciamento'), $options);
		$mform->setType('emailstop', PARAM_TEXT );
		$mform->setDefault('emailstop', $user->emailstop);

		$this->add_action_buttons();
	}

	function validation($data, $files) {
		$errors= array();

		if (trim($data['email']) == '') {
			$errors['email'] = get_string('informeOCampo', 'block_gerenciamento');
		}

		return $errors;
	}
}