<?php
global $CFG;
require_once($CFG->libdir.'/formslib.php');

class blocks_gerenciamento_acessopordata_form extends moodleform {

	function definition() {
		global $CFG, $DB;

		$mform =& $this->_form;
		$mform->addElement('header', 'titulo', get_string('consultar', 'block_gerenciamento'));
		
		$selectCategoria = $this->_customdata['selectcategoria'];
		$curso = $this->_customdata['curso'];
		$datainicio = $this->_customdata['datainicio'];
		$datafim = $this->_customdata['datafim'];
		$pesquisacategoria = $this->_customdata['pesquisacategoria'];
		$radioSelecionado = $this->_customdata['radioSelecionado'];

		$mform->addElement('hidden', 'selectcategoria', 0);
		$mform->setType('selectcategoria', PARAM_INT);

		$objs = array();
		$objs[] =& $mform->createElement('radio','radiocurso','','Categoria', 	0,'onclick="mostra(\'categoriadiv\',\'fitem_id_curso\')"');
		$objs[] =& $mform->createElement('radio','radiocurso','','Curso',		1,'onclick="mostra(\'fitem_id_curso\',\'categoriadiv\')"');
		$mform->addGroup($objs, 'group1', get_string('formapesquisa', 'block_gerenciamento'), array(' '), false);

		if(!isset($radioSelecionado)){
			$radioSelecionado = 0;
		}
		$mform->setDefault('radiocurso', $radioSelecionado);

		$mform->addElement('text', 'datepickerinicio', get_string('datainicio', 'block_gerenciamento'));
		$mform->setType('datepickerinicio', PARAM_TEXT);
		$mform->setDefault('datepickerinicio', date('d/m/Y', time()-(30 * DAYSECS)));

		$mform->addElement('hidden', 'datainicio');
		$mform->setType('datainicio', PARAM_INT);

		$mform->addElement('text', 'datepickerfim', get_string('datafim', 'block_gerenciamento'));
		$mform->setType('datepickerfim', PARAM_TEXT);
		$mform->setDefault('datepickerfim', date('d/m/Y'));

		$mform->addElement('hidden', 'datafim');
		$mform->setType('datafim', PARAM_INT);

		$datepicker  = '<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
                        <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
                        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
                        <script>
							$(function() {
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
									minDate:"'.date('d/m/Y', time()-(30 * DAYSECS)).'"
								});
								setValue("inicio");
								setValue("fim");
							});
							function setValue(value){
								var data = $("#id_datepicker"+value).datepicker("getDate");
								var milliseconds = Date.parse(data);
								$("input[name*=\'data"+value+"\']").val(milliseconds/1000);
								if(value=="fim"){
									$("#id_datepickerinicio").datepicker( "option", "maxDate", $("#id_datepickerfim").val() );
								}
							}
						</script>';
		$mform->addElement('html', $datepicker);

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

		$categorias = $DB->get_records('course_categories', null, '', 'id,name');

		$arrayCategorias = array();
		$arrayCategorias[0] = 'Selecione';
		foreach ($categorias as $ca) {
			$arrayCategorias[$ca->id] = $ca->name;
		}

		unset($arrayCategorias[9]);

		if (($selectCategoria == "categoriadiv")||($selectCategoria != "cursodiv"))
			$mform->addElement('html', '<div id="categoriadiv"  style="display: block;">');
		else
			$mform->addElement('html', '<div id="categoriadiv"  style="display: none;">');

		$categoriaSelecionada = $mform->addElement('select', 'categorias', get_string('categoria','block_gerenciamento'), $arrayCategorias);
		$mform->addElement('html', '</div>');

		if($pesquisacategoria){
			$categoriaSelecionada->setSelected($pesquisacategoria);
		}

		$html = '<script type="text/javascript">
					<!--
						function mostra(id1,id2) {
							document.getElementById(id1).style.display = "block";
							document.getElementById(id2).style.display = "none";
						}
						if(document.getElementById("id_radiocurso_0").checked){
							mostra(\'categoriadiv\',\'fitem_id_curso\');
						} else {
							mostra(\'fitem_id_curso\',\'categoriadiv\');
						}
					-->
			   	</script>';
		$mform->addElement('html', $html);
		
		$attributes = array('style' => 'margin-left:65px;margin-top:20px;');
		$mform->addElement('submit', 'submitbutton', get_string('consultar', 'block_gerenciamento'), $attributes);
	}
}

?>