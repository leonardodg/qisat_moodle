<?php
global $CFG;
require_once($CFG->libdir.'/formslib.php');

	class blocks_gerenciamento_relatoriouserturma_form extends moodleform{

		function definition () {
	    	global $CFG, $DB;
		
	    	$mform = $this->_form;

	    	$course = $this->_customdata['course'];

	    	$mform->addElement('header', 'titulo', get_string('consultar', 'block_gerenciamento'));

	    	$cursos = $DB->get_records_select('course', "format like 'topicstime'", null, 'shortname', 'id,shortname,fullname');

	    	$selectCursos = html_writer::start_tag('div', array('id'=>'fitem_id_course','class'=>'fitem fitem_ftext '));
			$tag = html_writer::start_tag('img', array('class'=>'req', 
				'src'=>$CFG->wwwroot.'/theme/image.php/clean/core/1450271992/req', 
				'alt'=>get_string('required'), 
				'title'=>get_string('required')));
			$tag = html_writer::tag('label', get_string('escolhacurso', 'block_gerenciamento').$tag, array('for'=>'id_course'));
			$selectCursos .= html_writer::tag('div', $tag, array('class'=>'fitemtitle'));
			$selectCursos .= html_writer::start_tag('div', array('class'=>'felement ftext'));
			$selectCursos .= html_writer::start_tag('select', array('id'=>'id_course', 'name'=>'course', 'onchange'=>'javascript:showselect(this);'));
			$selectCursos .= html_writer::tag('option', get_string('selecioneCurso', 'block_gerenciamento'), array('value'=>'0'));
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
			$mform->addRule('course', null, 'required');


			$grupos = $DB->get_records('groups', null, null, 'id,name,courseid');
	    	$allgroups = array();
	    	$attr = array("style" => "display:none;");
			foreach ($cursos as $c) {
				$select_grupo = array();
				$select_grupo[0] = get_string('allgrupos', 'block_gerenciamento');
				foreach ($grupos as $g) {
					if($c->id == $g->courseid){
						$select_grupo[$g->id] = $g->name;
					}
				}
				$allgroups[] = &$mform->createElement('select', 'selectturma_'.$c->id, '', $select_grupo, $attr);
			}
			$mform->addGroup($allgroups, 'groups', get_string('escolhagrupo', 'block_gerenciamento'), array(''), false);
			$mform->addElement('hidden', 'cursoselected', 0);
			$mform->setType('cursoselected', PARAM_INT);


	    	$javascript = '<script type="text/javascript">
				<!-- 
	        		function showselect(select){
		        		var options = select.options;
						var value   = options[options.selectedIndex].value;
			        	var nome = "id_selectturma_"+value.toString();
			        	
			        	if(value > 1){
							var elem = document.getElementById( nome );
								elem.style.display="block";

							document.getElementById("fgroup_id_groups").firstChild.style.display="block";
						} else {
							document.getElementById("fgroup_id_groups").firstChild.style.display="none";
						}
		
						elem = document.getElementsByName("cursoselected");
						var hide = elem[0].value; 

						elem[0].value = value;
						if( hide > 0){
							nome = "id_selectturma_"+hide.toString();
							elem = document.getElementById( nome );
							elem.style.display="none";
						}
					}
					function resetall(){
						var elem = document.getElementsByName("cursoselected");
						var hide = elem[0].value; 
											
						var select = document.getElementById( "id_course" );
						select.options.selectedIndex = 0;
						
						var nome = "id_selectturma_"+hide.toString();
						select = document.getElementById( nome );
						select.options.selectedIndex = 0;
						
						if( hide > 0){
							elem[0].value = 0;
							nome = "id_selectturma_"+hide.toString();
							elem = document.getElementById( nome );
							elem.style.display="none";
						}

						document.getElementById("fgroup_id_groups").firstChild.style.display="none";
					}
					document.getElementById("fgroup_id_groups").firstChild.style.display="none";';
			if($course){
		        $javascript .= 'elem = document.getElementsByName("cursoselected");
					elem[0].value = 0;
					showselect(document.getElementById("id_course"));';
			}
			$javascript .= '//-->
				</script>';
			$mform->addElement('html', $javascript);


			$buttonarray=array();
			$attr = array( "onclick" => "javascript:resetall();");
			$buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('pesquisar', 'block_gerenciamento'));
			$buttonarray[] = &$mform->createElement('button', 'resetbutton', get_string('limpafiltro', 'block_gerenciamento'), $attr);
			$mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
		}

		/*function validation($data, $files) {
	        $errors = parent::validation($data, $files);
		        		
	        if ($data['course'] == 0)
	       		$errors['course'] = get_string('choosecourse');
		       		
	        return $errors;
	    }*/
	}

?>