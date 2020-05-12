<?php
require_once($CFG->libdir.'/formslib.php');

class blocks_gerenciamento_aulasbloqueadas_form extends moodleform {
	
	function definition () {
		global $DB;

		$mform = $this->_form;

		$mform->addElement('header', 'titulo', get_string('consultar', 'block_gerenciamento'));

		$mform->addElement('text', 'chave', get_string('chave', 'block_gerenciamento'));
		$mform->setType('chave', PARAM_TEXT );
		$mform->addRule('chave', 'teste', 'required', 'server');

		$cursos = array(get_string('selecioneCurso', 'block_gerenciamento'));
		$selectCursos = $DB->get_records_select_menu('course', 'visible = 1 AND ID != 1', null, 'shortname', 'id,shortname');
		$selectCursos = array_replace($cursos, $selectCursos);
		$mform->addElement('select', 'curso', get_string('curso', 'block_gerenciamento'), $selectCursos);
		$mform->addRule('curso', null, 'required', 'server');
         
        $mform->addElement('submit', 'submitbutton', get_string('enviar', 'block_gerenciamento'));
	}

	function validation($data, $files) {
		$errors= array();

		if (empty($data['chave'])) {
			$errors['chave'] = get_string('chaveErro', 'block_gerenciamento');
		}

		if ($data['curso'] == 1) {
			$errors['curso'] = get_string('cursoErro', 'block_gerenciamento');
		}
		
		return $errors;
	}
}