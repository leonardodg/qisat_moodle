<?php
/**
 * Formulário para alterar informações de contato exibidos na tela de contato
 *
 * @author Deyvison Fernandes Baldoino
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class InformacoesContato_form extends moodleform{
	function definition () {
		global $CFG,$DB;

		$mform = $this->_form;
		$mform->addElement('header', 'dados_informacao',  get_string('informacoesContato', 'local_contato'));

		$mform->addElement('editor', 'informacoes_contato', get_string('informacoesContato', 'local_contato'));
        $mform->setType('informacoes_contato', PARAM_RAW);

		$mform->addElement('submit', 'salvar', get_string('salvar', 'local_contato'));
	}
}