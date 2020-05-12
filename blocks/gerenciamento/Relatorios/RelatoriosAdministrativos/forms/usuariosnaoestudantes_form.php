<?php
global $CFG;
require_once($CFG->libdir.'/formslib.php');

class blocks_gerenciamento_usuariosnaoestudantes_form extends moodleform {

	function definition() {
		global $CFG, $DB;
		
    	$mform = $this->_form;

    	$curso = isset($this->_customdata['curso'])?$this->_customdata['curso']:0;

    	$mform->addElement('header', 'titulo', get_string('consultar', 'block_gerenciamento'));

    	$cursos = $DB->get_records_select('course', "format like 'topicstime'", null, 'shortname', 'id,shortname,fullname');

    	$selectCursos = html_writer::start_tag('div', array('id'=>'fitem_id_curso','class'=>'fitem fitem_ftext '));
		$tag = html_writer::tag('label', get_string('escolhacurso', 'block_gerenciamento'), array('for'=>'id_curso'));
		$selectCursos .= html_writer::tag('div', $tag, array('class'=>'fitemtitle'));
		$selectCursos .= html_writer::start_tag('div', array('class'=>'felement ftext'));
		$selectCursos .= html_writer::start_tag('select', array('id'=>'id_curso', 'name'=>'curso', 'onchange'=>'javascript:showselect(this);'));
		$selectCursos .= html_writer::tag('option', get_string('allcursos', 'block_gerenciamento'), array('value'=>'0'));
		foreach ($cursos as $c) {
			$parametros = array('value'=>$c->id, 'title'=>$c->fullname);
			if($curso == $c->id){
				$parametros['selected'] = 'selected';
			}
			$selectCursos .= html_writer::tag('option', $c->shortname, $parametros);
		}
		$selectCursos .= html_writer::end_tag('select');
		$selectCursos .= html_writer::end_tag('div');
		$selectCursos .= html_writer::end_tag('div');

		$mform->addElement('html', $selectCursos);
		$mform->addElement('hidden', 'curso');
		$mform->setType('curso', PARAM_INT);
		$mform->addRule('curso', null, 'required');

		$mform->addElement('submit', 'submitbutton', get_string('consultar', 'block_gerenciamento'));
	}
}

?>