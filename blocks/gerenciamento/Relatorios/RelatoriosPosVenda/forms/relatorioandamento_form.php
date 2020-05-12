<?php
require_once($CFG->libdir.'/formslib.php');

class blocks_gerenciamento_relatorioandamento_form extends moodleform {
	
	function definition () {
		global $DB;

		$mform = $this->_form;

		$course = $this->_customdata['course'];
		$radioConsulta = $this->_customdata['radioConsulta'];

		$mform->addElement('header', 'titulo', get_string('consultar', 'block_gerenciamento'));

		$radioarray = array();
		$radioarray[] =& $mform->createElement('radio', 'radioConsulta', '', get_string('andamento', 'block_gerenciamento'), 1);
		$radioarray[] =& $mform->createElement('radio', 'radioConsulta', '', get_string('finalizados', 'block_gerenciamento'), 2);
		$radioarray[] =& $mform->createElement('radio', 'radioConsulta', '', get_string('expirados', 'block_gerenciamento'), 3);
		$radioarray[] =& $mform->createElement('radio', 'radioConsulta', '', get_string('inicioem', 'block_gerenciamento'), 4);
		$mform->addGroup($radioarray, 'radioConsultaGroup', get_string('formaPesquisa', 'block_gerenciamento'), array(' '), false);
		$mform->setDefault('radioConsulta', 1);

		if (has_capability('block/gerenciamento:duvidasrespondidas', context_system::instance())){
			$cursos = $DB->get_records_select('course', "format like 'topicstime'", null, 'shortname', 'id,shortname,fullname');
		}else{
			$mycourses  = get_my_courses($USER->id);
			$mycoursesids = array();
			foreach ($mycourses as $mycourse){
				if (has_capability('block/gerenciamento:duvidasrespondidas', context_course::instance($mycourse->context))) {
					$mycoursesids[] = $mycourse->id;
				}
			}
			$where = "format like 'topicstime' AND c.id in (".implode(',',$mycoursesids).")";
			$cursos = $DB->get_records_select('course', $where, null, 'shortname', 'id,shortname,fullname');
		}	

		$selectCursos = html_writer::start_tag('div', array('id'=>'fitem_id_course','class'=>'fitem fitem_ftext '));
		$tag = html_writer::tag('label', get_string('escolhacurso', 'block_gerenciamento'), array('for'=>'id_course'));
		$selectCursos .= html_writer::tag('div', $tag, array('class'=>'fitemtitle'));
		$selectCursos .= html_writer::start_tag('div', array('class'=>'felement ftext'));
		$selectCursos .= html_writer::start_tag('select', array('id'=>'id_course', 'name'=>'course'));
		$selectCursos .= html_writer::tag('option', get_string('allcursos', 'block_gerenciamento'), array('value'=>'0'));
		foreach ($cursos as $c) {
			$parametros = array('value'=>$c->id, 'title'=>$c->fullname);
			if($course == $c->id){
				$parametros['selected'] = 'selected';
			}
			$selectCursos .= html_writer::tag('option', $c->shortname, $parametros);
		}
		$selectCursos .= html_writer::end_tag('select');
		$selectCursos .= html_writer::end_tag('div');
		$selectCursos .= html_writer::end_tag('div');

		$mform->addElement('html', $selectCursos);
		$mform->addElement('hidden', 'course');
		$mform->setType('course', PARAM_INT);
		
		$mform->addElement('text', 'datepickerinicio', get_string('buscadatainicio', 'block_gerenciamento'));
		$mform->setType('datepickerinicio', PARAM_TEXT);
		$mform->setDefault('datepickerinicio', date('d/m/Y', time()- (90 * DAYSECS)));

		$mform->addElement('text', 'datepickerfim', get_string('buscadatafim', 'block_gerenciamento'));
		$mform->setType('datepickerfim', PARAM_TEXT);
		$mform->setDefault('datepickerfim',date('d/m/Y', time()));

		$mform->addElement('hidden', 'pesquisa_inicio');
		$mform->setType('pesquisa_inicio', PARAM_INT);

		$mform->addElement('hidden', 'pesquisa_fim');
		$mform->setType('pesquisa_fim', PARAM_INT);

		$datepicker  = '<script>
							$.noConflict();
							jQuery(function() {
								$("#id_datepickerinicio").datepicker({
									dateFormat:"dd/mm/yy",
									onSelect: function(){
										setValue("inicio");
										$("#id_datepickerfim").datepicker( "option", "minDate", $("#id_datepickerinicio").val() );
									},
									maxDate:"'.date('d/m/Y').'"
								});
								$("#id_datepickerfim").datepicker({
									dateFormat:"dd/mm/yy",
									onSelect: function(){
										setValue("fim");
										$("#id_datepickerinicio").datepicker( "option", "maxDate", $("#id_datepickerfim").val() );
									},
									maxDate:"'.date('d/m/Y').'",
									minDate:"'.date('d/m/Y', time()-(90 * DAYSECS)).'"
								});
								setValue("inicio");
								setValue("fim");
							});
							function setValue(value){
								var data = $("#id_datepicker"+value).datepicker("getDate");
								var milliseconds = Date.parse(data);
								$("input[name*=\'pesquisa_"+value+"\']").val(milliseconds/1000);
							}
						</script>';
		$mform->addElement('html', $datepicker);

		$radioarray = array();
		$radioarray[] =& $mform->createElement('radio', 'radioTotais', '', get_string('yes'), 1);
		$radioarray[] =& $mform->createElement('radio', 'radioTotais', '', get_string('no'), 0);
		$mform->addGroup($radioarray, 'radioTotaisGroup', get_string('exibirTotais', 'block_gerenciamento'), array(' '), false);

		$mform->registerNoSubmitButton('submitbutton');
        $mform->addElement('submit', 'submitbutton', get_string('enviar', 'block_gerenciamento'));
	}
}