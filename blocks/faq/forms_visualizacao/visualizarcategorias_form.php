<?php
global $CFG;

require_once ($CFG->libdir . '/formslib.php');

class blocks_faq_visualizarcategorias_form extends moodleform {
	function definition() {
		global $CFG, $DB;

		$cid = $this->_customdata['cid'];
		
		$mform = & $this->_form;
		
		$curso = $DB->get_record ( 'course', array('id'=>$cid), 'fullname' );
		$mform->addElement ( 'static', 'nomeCurso', $curso->fullname );

		$sql = 'SELECT 
			cat.id,
			cat.categoria,
			cat.idparent,
			art.idcategoria
				
			from {faq_categoria} cat
			LEFT JOIN {faq_artigo} art
			ON cat.id = art.idcategoria
							
			where (art.visivel = 1 AND cat.visivel = 1 AND cat.idcurso = '.$cid.') 
			OR (cat.idparent is NULL AND cat.idcurso = '.$cid.')
			group by id
			order by cat.idparent, cat.ordem';
			
		$categoria_lista = $DB->get_records_sql ( $sql );
			
		if (count ( $categoria_lista ) > 0) {
			
			$table = new html_table();
			$table->head = NULL;
			$table->colclasses = array ("leftalign", "leftalign", "leftalign");
			$table->size = array ("30%", "30%", "30%");
				
			$posY = 0;
			$maior = 0;
			$linha = '<div class="linha" id="linha"></div>';
			$arrCategoria = array ();
			$link = $CFG->wwwroot . '/blocks/faq/visualizarcategorias.php?cid='.$cid;
			foreach ( $categoria_lista as $cat ) {
				$arrCategoria [] = array ();
				if ($cat->idparent == NULL) {
					$posX = 0;
					foreach ( $categoria_lista as $sub ) {
						if ($cat->id == $sub->idparent) {
							for($i = 0; $i < $posY; $i ++) {
								if (! isset ( $arrCategoria [$posX] [$i] )) {
									$arrCategoria [$posX] [$i] = '';
								}
							}
							$subCategoria = $sub->categoria;
							$arrCategoria [$posX] [$posY] = '<div class="pontilhado" style="border-bottom: 1px dashed #ccc;">';
							$arrCategoria [$posX] [$posY] .= '<a class="link" href="' . $link . '&categoria=' . $subCategoria . '">' . $subCategoria . '</a>';
							$arrCategoria [$posX] [$posY] .= '</div>';
							$posX ++;
						}
					}
					if ($cat->id == $cat->idcategoria) {
						$table->head [] = '<a href="' . $link . '&categoria=' . $cat->categoria . '">' . $cat->categoria . '</a><br/>' . $linha;
					} else {
						$table->head [] = $cat->categoria . $linha;
					}
					if ($posX != 0) {
						$posX --;
						$arrCategoria [$posX] [$posY] = '<div class="pontilhado">';
						$arrCategoria [$posX] [$posY] .= '<a class="link" href="' . $link . '&categoria=' . $subCategoria . '">' . $subCategoria . '</a>';
						$arrCategoria [$posX] [$posY] .= '</div>';
					}
					$posY ++;
					if (sizeof ( $table->head ) == 3) {
						$table->data = $arrCategoria;
						$mform->addElement('html', html_writer::table($table));
						$arrCategoria = NULL;
						$table->head = array ();
						$table->data = array ();
						$table->size = array ("30%", "30%", "30%");
						$posY = 0;
						$maior = 0;
						$mform->addElement ('html', '<br/><br/>');
					}
				}
			}
			switch (sizeof($table->head)) {
				case 1 :
					$table->head[] = '';
				case 2 :
					$table->data = $arrCategoria;
					$table->head[] = '';
					$mform->addElement ('html', html_writer::table($table));
					break;
			}
		} else {
			$mform->addElement ('static', 'semregistros', get_string('semregistros', 'block_faq'));
		}

	}
}
	