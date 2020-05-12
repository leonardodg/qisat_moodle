<?php
global $CFG;
require_once($CFG->libdir.'/formslib.php');

class blocks_gerenciamento_bitratecurso_form extends moodleform {

	function definition() {
		global $DB;

		$mform =& $this->_form;
		$mform->addElement('header', 'titulo', get_string('consultar', 'block_gerenciamento'));
		
		$curso = $this->_customdata['curso'];
		$download = $this->_customdata['download'];

		$cursos = array(get_string('selecioneCurso', 'block_gerenciamento'));
		$selectCursos = $DB->get_records_select_menu('course', 'visible = 1 AND ID != 1', null, 'shortname', 'id,shortname');
		$selectCursos = array_replace($cursos, $selectCursos);
		$mform->addElement('select', 'curso', get_string('curso', 'block_gerenciamento'), $selectCursos);
		$mform->setType('curso', PARAM_INT );
		$mform->setDefault('curso', $curso);
		//$mform->addRule('curso', null, 'required', 'server');

		$options = array(0 => get_string('no'), 1 => get_string('yes'));
		$mform->addElement('select', 'download', get_string('download', 'block_gerenciamento'), $options);
		$mform->setType('download', PARAM_BOOL );
		$mform->setDefault('download', $download);
		//$mform->addRule('download', null, 'required', 'server');

		$attributes = array('style' => 'margin-left:65px;margin-top:20px;');
		$mform->addElement('submit', 'submitbutton', get_string('consultar', 'block_gerenciamento'), $attributes);
	}
}

?>