<?php

global $CFG;
require_once ($CFG->libdir . '/formslib.php');

class blocks_faq_artigo_form extends moodleform {
	function definition() {
		global $CFG, $DB;
		
		$cid = $this->_customdata['cid'];
		$categoria = $this->_customdata['categoria'];
		$idartigo = isset($this->_customdata['artigo'])?$this->_customdata['artigo']:0;
		
		$titulo = "";
		$autor_artigo = "0";
		$assunto = "";
		$artigo = "";
		$tags = "";
		$relacionados = array();
		
		if ($idartigo > 0) {
			$artigo = $DB->get_record_sql ( 'faq_artigo', array('id'=>$idartigo) );
			
			$titulo = $artigo->titulo;
			$autor_artigo = $artigo->idautor;
			$assunto = $artigo->assunto;
			$artigo = $artigo->artigo;
			
			$tags_lista = $DB->get_records_sql ( 'faq_tags', array('idartigo'=>$idartigo) );
			
			foreach ( $tags_lista as $tag ) {
				$tags .= $tag->descricao . ";";
			}
			$tags = substr ( $tags, 0, - 1 );
			
			$relacionados_lista = $DB->get_records_sql ( 'faq_relacionados', array('idartigo'=>$idartigo) );
			
			foreach ( $relacionados_lista as $relacionado ) {
				$relacionados[] = $relacionado->idcurso;
			}
		}
		
		$mform = & $this->_form;
		
		$mform->addElement ( 'header', 'head', get_string ( 'criacaoArtigo', 'block_faq' ) );
		$mform->addElement ( 'static', 'descricao', get_string ( 'descricaoArtigo', 'block_faq' ) );
		
		$mform->addElement ( 'hidden', 'cid', $cid );
		$mform->setType ( 'cid', PARAM_INT );
		$mform->addElement ( 'hidden', 'categoria', $categoria );
		$mform->setType ( 'categoria', PARAM_INT );
		$mform->addElement ( 'hidden', 'idartigo', $idartigo );
		$mform->setType ( 'idartigo', PARAM_INT );
		
		$mform->addElement ( 'text', 'titulo', get_string ( 'titulo', 'block_faq' ), array (
				'value' => $titulo 
		) );
		$mform->setType ( 'titulo', PARAM_TEXT );
		$mform->addRule('titulo', null, 'required', 'server');

		$selectAutor = $DB->get_records_select_menu ( 'faq_autor', 'visivel=1', null, 'autor', 'id,autor' );
		$selectAutor[0] = get_string ( 'selecioneAutor', 'block_faq' );
		
		$mform->addElement ( 'select', 'select_autor', get_string ( 'autor', 'block_faq' ), $selectAutor, 'onChange="mostraAutor();"' );
		$mform->setDefault ( 'select_autor', $autor_artigo );
		$mform->addRule('select_autor', null, 'required', 'server');

		$mform->addElement ( 'button', 'gerenciarAutor', get_string ( 'autorGerenciar', 'block_faq' ), 'onClick="autores();"' );
		
		$mform->addElement ( 'text', 'assunto', get_string ( 'assunto', 'block_faq' ), array (
				'value' => $assunto 
		) );
		$mform->setType ( 'assunto', PARAM_TEXT );
		$mform->addRule('assunto', null, 'required', 'server');
		
		$html = '<script type="text/javascript">
					function autores(){
						window.location = "' . $CFG->wwwroot . '/blocks/faq/autores.php?cid=' . $cid . '&categoria=' . $categoria . '";
	    			}
					/*function enviar(){
						document.forms["mform1"].editor.value = CKEDITOR.instances.editor1.getData();
    					var valores = $(".multiselect").multiselect("serialize");
						$(\'input[type = "hidden"][name = "cursos"]\').val(JSON.stringify(valores));
	    			}*/
	  			</script>';
		$mform->addElement ( 'html', $html );

		$editoroptions = array('maxfiles'=> 99, 'maxbytes'=>$CFG->maxbytes, 'context'=>context_course::instance($cid));
		$mform->addElement('editor', 'editor', get_string('artigo', 'block_faq'), null, $editoroptions);
        $mform->setType('editor', PARAM_RAW);
        $mform->addRule('editor', null, 'required', 'server');
		
		$mform->addElement ( 'text', 'tags', get_string ( 'tagsDescricao', 'block_faq' ), array (
				'value' => $tags 
		) );
		$mform->setType ( 'tags', PARAM_TEXT );
		
		$mform->addElement ( 'submit', 'submitbutton', get_string ( 'enviar', 'block_faq' ), 'onClick="enviar();"' );
	}

	function validation($data, $files) {
		$errors= array();

		if (empty($data['titulo'])) {
			$errors['titulo'] = get_string('tituloErro', 'block_faq');
		}

		if ($data['select_autor'] == 0) {
			$errors['select_autor'] = get_string('select_autorErro', 'block_faq');
		}

		if (empty($data['assunto'])) {
			$errors['assunto'] = get_string('assuntoErro', 'block_faq');
		}
		if (empty($data['editor'])) {
			$errors['editor'] = get_string('editorErro', 'block_faq');
		}
		
		return $errors;
	}
}
