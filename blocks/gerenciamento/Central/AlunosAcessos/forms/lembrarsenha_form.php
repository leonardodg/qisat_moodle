<?php
/**
 * Formulário para buscar usuário para enviar o lembrete de senha
 *
 * @author Deyvison Fernandes Baldoino 
 */

require_once($CFG->libdir.'/formslib.php');

class blocks_gerenciamento_lembrarsenha_form extends moodleform {
	
	function definition () {
		global $DB;

		$mform = $this->_form;

		$mform->addElement('header', 'titulo', get_string('lembreteSenha', 'block_gerenciamento'));

		$rotuloNome = get_string('nome', 'block_gerenciamento');
		$rotuloBotao = get_string('buscar', 'block_gerenciamento');

		if(isset($this->_customdata['lembreteSite'])){
			$rotuloNome = get_string('nomeUsuario', 'block_gerenciamento');
			$rotuloBotao = get_string('enviar', 'block_gerenciamento');
		}

		$mform->addElement('text', 'nome', $rotuloNome);
		$mform->setType('nome', PARAM_TEXT );

		$mform->addElement('text', 'chave', get_string('chave', 'block_gerenciamento'));
		$mform->setType('chave', PARAM_TEXT );

		$mform->addElement('text', 'email', get_string('email', 'block_gerenciamento'));
		$mform->setType('email', PARAM_TEXT );
         
        $mform->addElement('submit', 'submitbutton', $rotuloBotao);
	}

	function validation($data, $files) {
		$errors= array();

		if (trim($data['chave']) == '' && trim($data['nome']) == '' && trim($data['email']) == '') {
			$errors['chave'] = get_string('informeUmCampo', 'block_gerenciamento');
			$errors['nome'] = get_string('informeUmCampo', 'block_gerenciamento');
			$errors['email'] = get_string('informeUmCampo', 'block_gerenciamento');
		}elseif(isset($this->_customdata['lembreteSite'])){
			if((trim($data['chave']) != '' && trim($data['nome']) != '') || 
			   (trim($data['chave']) != '' && trim($data['email']) != '')|| 
			   (trim($data['nome']) != '' && trim($data['email']) != '')){
			   	
				$errors['chave'] = get_string('informeApenasUmCampo', 'block_gerenciamento');
				$errors['nome'] = get_string('informeApenasUmCampo', 'block_gerenciamento');
				$errors['email'] = get_string('informeApenasUmCampo', 'block_gerenciamento');
			}
		}

		return $errors;
	}
}