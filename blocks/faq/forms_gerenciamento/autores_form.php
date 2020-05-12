<?php

	global $CFG;
	require_once($CFG->libdir.'/formslib.php');
	
	class blocks_faq_autores_form extends moodleform {
	    function definition () {
	    	global $CFG, $DB;
		
			$cid = $this->_customdata['cid'];
			$categoria = $this->_customdata['categoria'];
 
			$mform =& $this->_form;

			$mform->addElement('header', 'head', get_string('criacaoAutor', 'block_faq'));
			$mform->addElement('static', 'descricao', get_string('descricaoAutor', 'block_faq'));
			
			$selectAutor = $DB->get_records_select_menu('faq_autor', 'visivel = 1', null, 'autor', 'id,autor');
			$selectAutor = array(0 => get_string('novoAutor','block_faq'));
			$mform->addElement('select', 'select_autor', get_string('autor','block_faq'), $selectAutor, 'onChange="mostraAutor();"');
			
			$mform->addElement('text', 'autor', get_string('autor', 'block_faq'), '');
			$mform->setType ( 'autor', PARAM_TEXT );
			$mform->addElement('textarea', 'autorDescricao', get_string('autorDescricao', 'block_faq'), '');
			
			$objs = array();
			$titulo = "gerenciarAutor";
			$objs[0] =& $mform->createElement('button', 'inclusao', get_string('autorInclusao', 'block_faq'),
			'onClick="incluirAutor();"');
			$objs[1] =& $mform->createElement('button', 'exclusao', get_string('autorExclusao', 'block_faq'),
			'onClick="excluirAutor();"');
			$mform->addGroup($objs,'botoes','', '', '');
			
			$html = '<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
				<script type="text/javascript">
					function mostraAutor(){
						var select_autor = document.getElementById("id_select_autor");
						var id = select_autor.options[select_autor.selectedIndex].value;
						if(id>0){
							$.ajax({
								url: "ajax/buscaAutor.php",
								data: {id:id},
								type: "POST",
								success:function(data) {
									var autor = select_autor.options[select_autor.selectedIndex].text;
									document.getElementById("id_autor").value = autor;
									document.getElementById("id_autorDescricao").value = data;
								}
							});
						} else {
							document.getElementById("id_autor").value = "";
							document.getElementById("id_autorDescricao").value = "";
	    				}
	    			}
					function incluirAutor(){
						var select_autor = document.getElementById("id_select_autor");
						var id = select_autor.options[select_autor.selectedIndex].value;
						var autor = document.getElementById("id_autor").value;
						var descricao = document.getElementById("id_autorDescricao").value;
						$.ajax({
							url: "ajax/insertAutor.php",
							data: {id:id,autor:autor,descricao:descricao},
							type: "POST",
							success:function(data) {
								if(id==0){
									$("#id_select_autor").append("<option value="+data+">"+autor+"</option>");
									document.getElementById("id_autor").value = "";
									document.getElementById("id_autorDescricao").value = "";
								} else {
									select_autor.options[select_autor.selectedIndex].text = autor;
	    						}
							}
						});
	    			}
					function excluirAutor(){
						var id = $("#id_select_autor").val();
						if(id>0){
							$.ajax({
								url: "ajax/deleteAutor.php",
								data: {id:id},
								type: "POST",
								success:function(data) {
									$("#id_select_autor :selected").remove();
									document.getElementById("id_autor").value = "";
									document.getElementById("id_autorDescricao").value = "";
								}
							});
						}
	    			}
					function voltar(){
						window.location = "'.$CFG->wwwroot.'/blocks/faq/artigo.php?cid='.$cid.'&categoria='.$categoria.'";
	    			}
	  			</script>';
			$mform->addElement('html', $html);
			
			$mform->addElement('button', 'submitbutton', get_string('voltar', 'block_faq'), 'onClick="voltar();"');
	    }

	}
