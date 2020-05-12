<?php
global $CFG;
require_once($CFG->libdir.'/formslib.php');

class blocks_gerenciamento_proficiencia_form extends moodleform {

	function definition() {
		global $DB;

		$mform =& $this->_form;
		$mform->addElement('header', 'titulo', get_string('consultar', 'block_gerenciamento'));
		
		$curso = $this->_customdata['curso'];
		$aula  = $this->_customdata['aula'];

		$cursos = $DB->get_records_select('course', "fullname like '%proficiência%'", null, 'shortname', 'id,shortname');

		$aulas = $DB->get_records_sql_menu('SELECT course, MAX(section)-1 as aula FROM {course_sections}
											WHERE course IN('.implode(array_keys($cursos), ',').') GROUP BY course');

		$selectCursos = html_writer::start_tag('div', array('id'=>'fitem_id_curso','class'=>'fitem fitem_ftext '));
		$tag = html_writer::tag('label', get_string('escolhacurso', 'block_gerenciamento'), array('for'=>'id_curso'));
		$selectCursos .= html_writer::tag('div', $tag, array('class'=>'fitemtitle'));
		$selectCursos .= html_writer::start_tag('div', array('class'=>'felement ftext'));
		$selectCursos .= html_writer::start_tag('select', array('id'=>'id_curso', 'name'=>'curso', 'onchange'=>'javascript:showselect(this);'));
		$selectCursos .= html_writer::tag('option', get_string('nocourses', 'block_gerenciamento'), array('value'=>'0'));
		foreach ($cursos as $c) {
			$parametros = array('value'=>$c->id, 'title'=>$c->fullname, 'data-aula'=>$aulas[$c->id]);
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


		$selectAulas = html_writer::start_tag('div', array('id'=>'fitem_id_aula','class'=>'fitem fitem_ftext '));
		$tag = html_writer::tag('label', get_string('escolhaaula', 'block_gerenciamento'), array('for'=>'id_aula'));
		$selectAulas .= html_writer::tag('div', $tag, array('class'=>'fitemtitle'));
		$selectAulas .= html_writer::start_tag('div', array('class'=>'felement ftext'));
		$selectAulas .= html_writer::start_tag('select', array('id'=>'id_aula', 'name'=>'aula', 'onchange'=>'javascript:showselect(this);'));
		$selectAulas .= html_writer::tag('option', get_string('noclasses', 'block_gerenciamento'), array('value'=>'0'));

		for ($i = 1; $i <= max($aulas); $i++) {
			$parametros = array('value'=>$i, 'title'=>'Aula '.$i, 'disabled'=>'disabled');
			if($aula == $i){
				$parametros['selected'] = 'selected';
			}
			$selectAulas .= html_writer::tag('option', 'Aula '.$i, $parametros);
		}
		$selectAulas .= html_writer::end_tag('select');
		$selectAulas .= html_writer::end_tag('div');
		$selectAulas .= html_writer::end_tag('div');

		$mform->addElement('html', $selectAulas);
		$mform->addElement('hidden', 'aula');
		$mform->setType('aula', PARAM_INT);
		$mform->addRule('aula', null, 'required');


		$liberarAula  = '<script>
							$("#id_curso").change(function() {
								var aula = $("#id_curso option:selected").attr("data-aula");
								$("#id_aula option").each(function( index ) {
									if(index == 0 || index <= aula){
										$(this).removeAttr("disabled");
									}else{
										$(this).attr("disabled", "disabled");
									}
								});
								if(!aula || $("#id_aula option:selected").is(":disabled"))
									$("#id_aula option:eq(0)").prop("selected", true);
							});
						</script>';
		$mform->addElement('html', $liberarAula);


		$attributes = array('style' => 'margin-left:65px;margin-top:20px;');
		$mform->addElement('submit', 'submitbutton', get_string('consultar', 'block_gerenciamento'), $attributes);
	}
}

?>