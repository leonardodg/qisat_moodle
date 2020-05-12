<?php
require_once($CFG->libdir.'/formslib.php');
//require_once ($CFG->dirroot.'/conexao/RelatorioIntranet.php');

class blocks_gerenciamento_relatorioAndamentoCurso_form extends moodleform{

	function definition () {
		global $CFG, $DB;

		$mform =& $this->_form;

		$curso = $this->_customdata['curso'];
		$turma = $this->_customdata['turmaSelecionado'];
				
		$mform->addElement('header', 'titulo', get_string('consultar', 'block_gerenciamento'));
		
		$cursos = $DB->get_records_select('course', "format like 'topicstime'", null, 'shortname', 'id,shortname,fullname');
		
		$selectCursos = html_writer::start_tag('div', array('id'=>'fitem_id_curso','class'=>'fitem fitem_ftext '));
		$tag = html_writer::tag('label', get_string('escolhacurso', 'block_gerenciamento'), array('for'=>'id_curso'));

		$tag .= html_writer::start_tag('img', array('class'=>'req', 
				'title'=>get_string('campoobrigatorio', 'block_gerenciamento'), 
				'alt'=>get_string('campoobrigatorio', 'block_gerenciamento'), 
				'src'=>$CFG->wwwroot . '/theme/image.php/clean/core/1451403906/req'));
		
		$selectCursos .= html_writer::tag('div', $tag, array('class'=>'fitemtitle'));
		$selectCursos .= html_writer::start_tag('div', array('class'=>'felement ftext'));
		$selectCursos .= html_writer::start_tag('select', array('id'=>'id_curso', 'name'=>'curso'));
		$selectCursos .= html_writer::tag('option', get_string('selecione', 'block_gerenciamento'), array('value'=>'0'));
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
		$mform->setDefault('curso', $curso);
		$mform->addRule('curso', 0, 'required', 'server');

		$mform->addElement('select', 'turma', get_string('turma', 'block_gerenciamento'), array(0=>get_string('todos', 'block_gerenciamento')));
		$mform->addElement('hidden', 'turmaSelecionado','',array('id'=>'turmaSelecionado'));
		$mform->setType('turmaSelecionado', PARAM_INT);
		$mform->setDefault('turmaSelecionado', $turma);
		
		$mform->addElement('submit', 'buscar', get_string('pesquisar','block_gerenciamento'));
	}
	
	function validation($data, $files) {
		$errors = parent::validation($data, $files);
		if($data['curso'] == 0){
			$errors['curso'] = get_string('cursoDeveSerSelecionado', 'block_gerenciamento');
		}
		return $errors;
	}
}
?>