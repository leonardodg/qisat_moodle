<?php
global $CFG;

require_once ($CFG->libdir . '/formslib.php');

class blocks_faq_visualizarlistaartigos_form extends moodleform {
	function definition() {
		global $CFG, $DB;
		
		$cid = $this->_customdata['cid'];
		$tag = isset($this->_customdata['tag'])?$this->_customdata['tag']:null;
		$categoria = isset($this->_customdata['categoria'])?$this->_customdata['categoria']:null;
		$pesquisa = isset($this->_customdata['pesquisa'])?$this->_customdata['pesquisa']:'';
		$tipo = isset($this->_customdata['tipo'])?$this->_customdata['tipo']:2;
		
		$mform = & $this->_form;
		
		$curso = $DB->get_record('course', array('id'=>$cid), 'fullname');
		$mform->addElement('static', 'nomeCurso', $curso->fullname);

		if (isset($tag)) {
			$filtro = " AND tag.descricao LIKE '" . $tag . "'";
		} else if (isset($categoria)) {
			$filtro = " AND cat.categoria LIKE '" . $categoria . "'";
		} else if ($tipo == 0) {
			$filtro = " AND art.artigo LIKE '%" . $pesquisa . "%'";
			$filtro .= " OR tag.descricao LIKE '" . $pesquisa . "'";
		} else if ($tipo == 1) {
			$filtro = " AND art.titulo LIKE '%" . $pesquisa . "%'";
			$filtro .= " OR tag.descricao LIKE '" . $pesquisa . "'";
		} else {
			$filtro = " AND art.artigo LIKE '%" . $pesquisa . "%'";
			$filtro .= " OR art.titulo LIKE '%" . $pesquisa . "%'";
			$filtro .= " OR tag.descricao LIKE '" . $pesquisa . "'";
		}
			
		$sql = "SELECT
			art.id,	art.titulo,	art.datacriacao, art.visualizacoes,	art.assunto, art.artigo,
			cat.categoria, cat.idparent,
			aut.autor, aut.descricao
				
			from (((
			{faq_artigo} art
			LEFT JOIN {faq_categoria} cat
			ON art.idcategoria = cat.id)
			LEFT JOIN {faq_autor} aut
			ON art.idautor = aut.id)
			LEFT JOIN {faq_tags} tag
			ON tag.idartigo = art.id)
							
			where art.visivel = 1 AND cat.visivel = 1 AND cat.idcurso = ".$cid.$filtro." 
			GROUP BY art.id
			ORDER BY cat.idparent, cat.ordem";
			
		$lista = $DB->get_records_sql ( $sql );

		if ($lista != NULL) {
			$where = array('visivel'=>1, 'idparent'=>null, 'idcurso'=>$cid);
			$categoria_lista = $DB->get_records('faq_categoria', $where, 'idparent,ordem', 'id,categoria');
		
			$table = new html_table();
			$table->colclasses = array ("centeralign");
			
			$link = $CFG->wwwroot . '/blocks/faq/visualizarartigo.php?idartigo=';
				
			foreach ( $lista as $art ) {
				$titulo = "";
				if ($art->idparent != NULL) {
					foreach ( $categoria_lista as $cat ) {
						if ($art->idparent == $cat->id) {
							$titulo = $cat->categoria . " - ";
							break;
						}
					}
				}
				$table->head = array (
					'<div style="border-bottom: 1px solid #efefef; padding: 10px !important;">' . $titulo . $art->categoria . '</div>' 
				);
				$html = '<div style="border-bottom: 1px solid #efefef; padding: 25px;">';
				$html .= '<a href="' . $link . $art->id . '" style="color: #555555;">';
				$html .= '<p style="color: #02416d; font-size: 16px;">' . $art->titulo . '</p>';
				$html .= '<p style="line-height: 25px">' . $art->assunto . '</p>';
				$html .= '<div style="margin-top: 28px;"><img src="imagens/autor.png" height="16" width="16" style="margin-bottom: -3px;">';
				$html .= $art->autor . '&nbsp;&nbsp;|&nbsp;&nbsp;';
				$html .= '<img src="imagens/calendario.png" height="16" width="16" style="margin-bottom: -3px;"> ';
				$html .= date ( "d/m/y", $art->datacriacao ) . '&nbsp;&nbsp;|&nbsp;&nbsp;';
				$html .= '<img src="imagens/visualizacoes.png" height="16" width="16" style="margin-bottom: -3px;"> ';
				if ($art->visualizacoes == 0) {
					$html .= get_string ( 'naovisualizado', 'block_faq' );
				} else if ($art->visualizacoes == 1) {
					$html .= $art->visualizacoes . get_string ( 'visualizacao', 'block_faq' );
				} else {
					$html .= $art->visualizacoes . get_string ( 'visualizacoes', 'block_faq' );
				}
				$html .= '<br/></a></div></div>';
				$table->data [] = array (
					$html 
				);
				$mform->addElement('html', html_writer::table($table));
				$table->data = NULL;
			}
				
			$html = count ( $lista ) . ' resultado';
			if (count ( $lista ) > 1) {
				$html .= 's';
			}
			if ($pesquisa != "") {
				$html .= ' da busca por "' . $pesquisa . '"';
			}
			$table->head = array (
				$html 
			);
			$table->data [] = array ();
			$mform->addElement('html', html_writer::table($table));
		} else {
			$mform->addElement ('static', 'semregistros', get_string('semregistros', 'block_faq'));
		}

	}
}
	