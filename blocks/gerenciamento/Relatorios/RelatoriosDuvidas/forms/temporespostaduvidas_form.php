<?php
require_once($CFG->libdir.'/formslib.php');

class blocks_gerenciamento_temporespostaduvidas_form extends moodleform {
	
	function definition () {
		global $DB, $CFG;

		$mform = $this->_form;

		$idcurso = $this->_customdata['idcurso'];

		$mform->addElement('header', 'titulo', get_string('consultar', 'block_gerenciamento'));
		
		$mform->addElement('text', 'datepickerinicio', get_string('buscadatainicio', 'block_gerenciamento'));
		$mform->setType('datepickerinicio', PARAM_TEXT);
		$mform->setDefault('datepickerinicio', date('d/m/Y', time()-(30 * DAYSECS)));

		$mform->addElement('hidden', 'pesquisa_inicio');
		$mform->setType('pesquisa_inicio', PARAM_INT);

		$mform->addElement('text', 'datepickerfim', get_string('buscadatafim', 'block_gerenciamento'));
		$mform->setType('datepickerfim', PARAM_TEXT);
		$mform->setDefault('datepickerfim', date('d/m/Y'));

		$mform->addElement('hidden', 'pesquisa_fim');
		$mform->setType('pesquisa_fim', PARAM_INT);

		$datepicker  = '<link rel="stylesheet" href="'.$CFG->wwwroot.'/lib/jquery/jquery-ui-1.12.1/jquery-ui.css">
                        <script src="'.$CFG->wwwroot.'/lib/jquery/jquery-ui-1.12.1/jquery-ui.js"></script>
						<script>
							$(function() {
								$("#id_datepickerinicio").datepicker({
									dateFormat:"dd/mm/yy",
									changeMonth : true,
									onClose : function(selectedDate) {
										setValue("inicio")
									},
									beforeShow: function () {
										$(this).datepicker("option", "maxDate", $("#id_datepickerfim").datepicker( "getDate" ));
									}
								});

								$("#id_datepickerfim").datepicker({
									dateFormat:"dd/mm/yy",
									changeMonth : true,
									onClose : function(selectedDate) {
										setValue("fim");
									},
									beforeShow: function () {
										$(this).datepicker("option", "minDate", $("#id_datepickerinicio").datepicker( "getDate" ));
									},
									maxDate:"0"
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

		$arrayoptions = array('0' => get_string('all'),
							  '1' => get_string('yes'),
							  '2' => get_string('no'));
		$mform->addElement('select', 'respondida', get_string('respondida','block_gerenciamento'), $arrayoptions);
		$mform->setType('respondida', PARAM_INT);

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

		$selectCursos = html_writer::start_tag('div', array('id'=>'fitem_id_idcurso','class'=>'fitem fitem_ftext '));
		$selectCursos .= html_writer::start_tag('div', array('class'=>'fitemtitle'));
		$selectCursos .= html_writer::tag('label', get_string('escolhacurso', 'block_gerenciamento'), array('for'=>'id_idcurso'));
		$selectCursos .= html_writer::end_tag('div');
		$selectCursos .= html_writer::start_tag('div', array('class'=>'felement ftext'));
		$selectCursos .= html_writer::start_tag('select', array('id'=>'id_idcurso', 'name'=>'idcurso'));
		$selectCursos .= html_writer::tag('option', get_string('allcursos', 'block_gerenciamento'), array('value'=>'0'));
		foreach ($cursos as $c) {
			$parametros = array('value'=>$c->id, 'title'=>$c->fullname);
			if($idcurso == $c->id){
				$parametros['selected'] = 'selected';
			}
			$selectCursos .= html_writer::tag('option', $c->shortname, $parametros);
		}
		$selectCursos .= html_writer::end_tag('select');
		$selectCursos .= html_writer::end_tag('div');
		$selectCursos .= html_writer::end_tag('div');

		$mform->addElement('html', $selectCursos);
		$mform->addElement('hidden', 'idcurso');
		$mform->setType('idcurso', PARAM_INT);

        $mform->addElement('submit', 'submitbutton', get_string('enviar', 'block_gerenciamento'));
	}
}