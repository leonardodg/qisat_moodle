<?php

/**
 * Formulário para envio de mensagens pelo sistema
 *
 * @author Deyvison Fernandes Baldoino
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class Contato_form extends moodleform{
	function definition () {
		global $CFG,$DB;

		$mform = $this->_form;
        $infoContato = $this->_customdata['infoContato'];

		$mform->addElement('header', 'dados',  get_string('contato', 'local_contato'));

		$mform->addElement('html','<div class="div_campos">');

		$mform->addElement('text', 'nome', get_string('nome', 'local_contato'), '');
		$mform->addRule('nome', get_string('nomeDeveSerInformado', 'local_contato'), 'required', null,'client');
		$mform->setType('nome', PARAM_TEXT);

		$mform->addElement('text', 'email', get_string('email', 'local_contato'), '');
		$mform->setType('email', PARAM_NOTAGS);
		$mform->addRule('email', get_string('emailDeveSerInformado', 'local_contato'), 'required', null,'client');

		$estados = $DB->get_records_menu('estado',null,'nome','id,nome');

		$listaEstados = array(''=>get_string('selecioneEstado', 'local_contato'),0=>get_string('foraBrasil', 'local_contato'));

		$listaEstados = array_merge($listaEstados,$estados);
		
		$mform->addElement('select', 'estado', get_string('estado','local_contato'),$listaEstados);
		$mform->addRule('estado', get_string('estadoDeveSerSelecionado','local_contato'), 'required', null,'client');

		$mform->addElement('select', 'cidade', get_string('cidade','local_contato'),array(get_string('selecioneEstado', 'local_contato')));
		$mform->addRule('cidade',get_string('cidadeDeveSerSelecionada','local_contato'), 'required');

		$mform->addElement('hidden', 'idcidade', '',array('id'=>'idcidade'));
		$mform->setType('idcidade', PARAM_TEXT);

		$mform->addElement('text', 'assunto', get_string('assunto', 'local_contato'), '');
		$mform->addRule('assunto', get_string('assuntoDeveSerInformado', 'local_contato'), 'required', null,'client');
		$mform->setType('assunto', PARAM_TEXT);
	
		$mform->addElement('textarea', 'mensagem', get_string('mensagem','local_contato'),'wrap="virtual" rows="8" cols="49"');
		$mform->addRule('mensagem', get_string('mensagemDeveSerInformada', 'local_contato'), 'required', null,'client');

		$mform->addElement('submit', 'enviar', get_string('enviar', 'local_contato'));
		$mform->addElement('html','</div>');

		$mform->addElement('html','<div class="box_contato">'.$infoContato->value.'</div><br style="clear: both;">');
	}

	function validation($data, $files){
		global $CFG;
		$errors = parent::validation($data, $files);

		if(trim($data['nome']) == '' ){
			$errors['nome'] = get_string('nomeDeveSerInformado','local_contato');
		}

		if(trim($data['mensagem']) == '' ){
			$errors['mensagem'] = get_string('mensagemDeveSerInformada','local_contato');
		}

		if(trim($data['assunto']) == '' ){
			$errors['assunto'] = get_string('assuntoDeveSerInformado','local_contato');
		}

		if(trim($data['email']) != '' ){
			if (!validate_email($data['email'])) {
				$errors['email'] = get_string('emailInvalido','local_contato');
			}
		}else{
			$errors['email'] = get_string('emailDeveSerInformado','local_contato');
		}

		if(trim($data['estado']) == ''){
			$errors['estado'] = get_string('estadoDeveSerSelecionado','local_contato');
		}else{
			if($data['estado'] > 0){
				if(trim($data['idcidade']) == ''){
					$errors['cidade'] = get_string('cidadeDeveSerSelecionada','local_contato');
				}
			}
		}
		

		return $errors;
	}
}
