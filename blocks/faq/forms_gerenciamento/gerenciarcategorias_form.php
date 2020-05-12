<?php
global $CFG;
require_once ($CFG->libdir . '/formslib.php');
class blocks_faq_gerenciarcategorias_form extends moodleform {
	function definition() {
		global $CFG, $DB;

		$cid = $this->_customdata['cid'];
		
		$mform = & $this->_form;

		$mform->addElement ( 'header', 'head', get_string ( 'filtroCurso', 'block_faq' ) );

		$pesquisa = array ();
		$pesquisa [] = & $mform->createElement ( 'text', 'name', '', 'name="name"' );
		$pesquisa [] = & $mform->createElement ( 'button', 'buttonAdd', get_string ( 'adicionar', 'block_faq' ), 'name="buttonAdd"' );
		$mform->addGroup ( $pesquisa, 'pesquisa', '', '', '' );
		$mform->setType ( 'name', PARAM_TEXT );

		$lista_categorias = $DB->get_records ( 'faq_categoria', array('idcurso'=>$cid), 'idparent,ordem' );
		$lista_artigos = $DB->get_records ( 'faq_artigo', null, 'ordem' );

		$subLista = false;
		$html_lista = '<div class="dd"><ol class="dd-list">';
		foreach ( $lista_categorias as $tipo ) {
			if (! $tipo->idparent) {
				$html_lista .= '<li class="dd-item" data-id="' . $tipo->id . '" id="item_' . $tipo->id . '" data-value="' . $tipo->categoria . '" data-ordem="' . $tipo->ordem . '" data-show="' . $tipo->visivel . '">
								<div class="dd-handle">Categoria: ' . $tipo->categoria . '</div>';
				foreach ( $lista_categorias as $t ) {
					if ($t->idparent == $tipo->id) {
						if (! $subLista) {
							$html_lista .= '<ol class="dd-list">';
							$subLista = ! $subLista;
						}
						$html_lista .= '<li class="dd-item" data-id="' . $t->id . '" id="item_' . $t->id . '" data-value="' . $t->categoria . '" data-ordem="' . $t->ordem . '" data-show="' . $t->visivel . '" data-parent="' . $tipo->id . '">
								<div class="dd-handle">Categoria: ' . $t->categoria . '</div>';
						$html_lista .= lista_artigos ( $lista_artigos, $t->id, true );
						$html_lista .= '</li>';
					}
				}
				$html_lista .= lista_artigos ( $lista_artigos, $tipo->id, !$subLista );
				if ($subLista) {
					$html_lista .= '</ol>';
					$subLista = !$subLista;
				}
				$html_lista .= '</li>';
			}
		}
		$html_lista .= '</ol>';
		$mform->addElement('html', $html_lista);
		
		$wwwroot = $CFG->wwwroot . "/blocks/faq";
		$html = '<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
				<script src="' . $CFG->wwwroot . '/blocks/faq/jquery/jquery.nestable.js" type="text/javascript"></script>
				<script>
					//$(".multiselect").multiselect();

					$("#id_buttonAdd").click(function(){
    					var nome = $("#id_name").val();
						if (nome != "") {
    						$("#id_name").val("");
    						$(".dd").nestable.addItem(nome);
						}
    				});
						
					$(".dd").nestable({ wwwRoot : "' . ($wwwroot) . '",
    					imagens : {
							outra : {
								src   : "' . $CFG->wwwroot . "/blocks/faq/imagens/editar.png" . '",
    							titlelink : "Inserir novo artigo",
    							href : "' . $CFG->wwwroot . '/blocks/faq/artigo.php?cid=' . $cid . '&categoria=",
    							srclink  :	true,
    							id : "artigo"
    						}
						}
    				});
	
    				$(\'input[type = "hidden"][name = "data"]\').val(JSON.stringify($(".dd").nestable("serialize")));
    			</script>';
		$mform->addElement('html', $html);
		
		$html = '<script type="text/javascript">
				<!--
					function insertCategoria(categoria){
						var retorno;
						$.ajax({
							url: "ajax/insertCategoria.php",
							data: {categoria:categoria, cid:'.$cid.'},
							type: "POST",
							success:function(data) {
								retorno = data;
							},
							async: false
						});
						return retorno;
	    			}

					function updateCategoria(id, categoria, visivel){
						$.ajax({
							url: "ajax/updateCategoria.php",
							data: {id:id, categoria:categoria, visivel:visivel},
							type: "POST"
						});
	    			}
								
					function deleteCategoria(id, idparent){
						if(idparent == undefined){
							idparent = 0;
						}
						$.ajax({
							url: "ajax/deleteCategoria.php",
							data: {id:id, idparent:idparent},
							type: "POST"
						});
						organizarLista();
	    			}
			
					function updateArtigo(idcategoria, idartigo){
						window.location = "' . $CFG->wwwroot . '/blocks/faq/artigo.php?cid=' . $cid . '&categoria="+idcategoria+"&artigo="+idartigo;
	    			}
					
					function updateArtigoVisible(id, visivel){
						$.ajax({
							url: "ajax/updateArtigoVisible.php",
							data: {id:id, visivel:visivel},
							type: "POST"
						});
	    			}
								
					function deleteArtigo(id){
						$.ajax({
							url: "ajax/deleteArtigo.php",
							data: {id:id},
							type: "POST"
						});
						organizarLista();
	    			}
								
					function organizarLista(){
						var cont1 = 1;
						var cont2 = 1;
						$( "div ol li" ).each(function( index ) {
							if( $( this ).attr("data-parent") == undefined ){
								if($( this ).attr("data-ordem") != cont1){
									$( this ).attr("data-ordem", cont1);
									$.ajax({
										url: "ajax/updateCategoriaOrdem.php",
										data: {id:$( this ).attr("data-id"),idparent:0,ordem:cont1},
										type: "POST"
									});
								}
								cont1++;
								cont2 = 1;
							} else {
								if($( this ).attr("data-ordem") != cont2){
									$( this ).attr("data-ordem", cont2);
									if ($( this ).attr("id").indexOf("item") >= 0) {
										$.ajax({
											url: "ajax/updateCategoriaOrdem.php",
											data: {id:$( this ).attr("data-id"),idparent:$( this ).attr("data-parent"),ordem:cont2},
											type: "POST"
										});
									} else {
										$.ajax({
											url: "ajax/updateArtigoOrdem.php",
											data: {id:$( this ).attr("data-id"),idparent:$( this ).attr("data-parent"),ordem:cont2},
											type: "POST"
										});
									}
								}
								cont2++;
							}
						});
					};
				-->
	   		</script>';
		$mform->addElement('html', $html);
		
	}
}

function lista_artigos($lista, $id, $novaLista) {
	$lista_vazia = true;
	
	if ($novaLista)
		$html_lista_artigos = '<ol class="dd-list">';
	
	foreach ( $lista as $artigo ) {
		if ($id == $artigo->idcategoria) {
			$lista_vazia = false;
			$html_lista_artigos .= '<li class="dd-item" data-parent="' . $id . '" data-id="' . $artigo->id . '" id="artigo_' . $artigo->id . '" data-value="' . $artigo->titulo . '" data-ordem="' . $artigo->ordem . '" data-show="' . $artigo->visivel . '">
					<div class="dd-handle">Artigo: ' . $artigo->titulo . '</div></li>';
		}
	}
	
	if ($lista_vazia)
		return '';
	
	if ($novaLista)
		$html_lista_artigos .= '</ol>';
	
	return $html_lista_artigos;
}
