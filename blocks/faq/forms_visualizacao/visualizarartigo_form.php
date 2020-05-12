<?php
global $CFG;

require_once ($CFG->libdir . '/formslib.php');

class blocks_faq_visualizarartigo_form extends moodleform {
	function definition() {
		global $CFG, $DB, $USER;
		
		$mform = & $this->_form;

		$idartigo = $this->_customdata['idartigo'];

		$artigo = $DB->get_record ( 'faq_artigo', array('id'=>$idartigo));
		$autor = $DB->get_record ( 'faq_autor', array('id'=>$artigo->idautor));
		$avaliacoes = $DB->get_records ( 'faq_avaliacao', array('idartigo'=>$idartigo));
		$tags = $DB->get_records ( 'faq_tags', array('idartigo'=>$idartigo));

		$html = '<h1 id="titulo">' . $artigo->titulo . '</h1>';

		$html .= '<div class="linha" id="linha"></div>';

		$html .= '<div class="texto">';
		$html .= '<img src="imagens/autor.png" height="16" width="16">';
		$html .= $autor->autor . '&nbsp;&nbsp;|&nbsp;&nbsp;';
		$html .= '<img src="imagens/calendario.png" height="16" width="16"> ';
		$html .= date ( "d/m/y", $artigo->datacriacao ) . '&nbsp;&nbsp;|&nbsp;&nbsp;';
		$html .= '<img src="imagens/visualizacoes.png" height="16" width="16"> ';
		$html .= $artigo->visualizacoes;
		if ($artigo->visualizacoes == 1) {
			$html .= get_string ( 'visualizacao', 'block_faq' );
		} else {
			$html .= get_string ( 'visualizacoes', 'block_faq' );
		}
		$html .= '<br/></div>';

		$avaliacaoAluno = 0;
		$totalAvaliacoes = 0;
		$quantidadeAvaliacoes = 0;
		foreach ( $avaliacoes as $ava ) {
			$totalAvaliacoes += $ava->descricao;
			$quantidadeAvaliacoes ++;
			if ($ava->idaluno == 2) {
				$avaliacaoAluno = $ava->descricao;
			}
		}
		if ($quantidadeAvaliacoes == 0) {
			$mediaAvaliacoes = 0;
		} else {
			$mediaAvaliacoes = $totalAvaliacoes / $quantidadeAvaliacoes;
		}
		$html .= '<br/><div class="basic1" data-average="' . $mediaAvaliacoes . '" data-id="1" style="float: left; margin-top: -2px;"></div>
				<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
				<script type="text/javascript" src="' . $CFG->wwwroot . '/blocks/faq/jquery/jRating.jquery.js"></script>
				<script type="text/javascript">
					$(document).ready(function(){
						$(".basic1").jRating({
							isDisabled : true
						});
					});
				</script>';
		$html .= '<div class="avaliacao">(';
		if ($quantidadeAvaliacoes == 0) {
			$html .= get_string ( 'naoavaliado', 'block_faq' );
		} else if ($quantidadeAvaliacoes == 1) {
			$html .= $quantidadeAvaliacoes . get_string ( 'avaliacao', 'block_faq' );
		} else {
			$html .= $quantidadeAvaliacoes . get_string ( 'avaliacoes', 'block_faq' );
		}
		$html .= ')</div><br/>';

		$html .= '<b>'.get_string('assunto', 'block_faq').'</b>';
		$html .= '<div class="texto">';
		$html .= $artigo->assunto;
		$html .= '</div><br/>';

		$html .= '<b>'.get_string('artigo', 'block_faq').'</b>';
		$html .= '<div class="texto">';
		$html .= $artigo->artigo;
		$html .= '</div><br/>';

		$html .= '<div class="texto">';
		$html .= '<b>'.get_string('tags', 'block_faq').'</b>';
		foreach ( $tags as $tag ) {
			$html .= $tag->descricao . ', ';
		}
		$html = substr ( $html, 0, - 2 );
		$html .= '.</div><br/>';

		$html .= '<b>'.get_string('sobreAutor', 'block_faq').'</b>';
		$html .= '<div class="texto">';
		$html .= $autor->descricao;
		$html .= '</div><br/>';

		$idaluno = $USER->id;

		$html .= '<b>'.get_string('avaliarartigo', 'block_faq').'</b>';
		$html .= '<div class="basic2" data-average="' . $avaliacaoAluno . '" data-id="2" ></div>
				<script type="text/javascript">
					$(document).ready(function(){
						$(".basic2").jRating({
							onClick : function(element,rate){
								$.ajax({
									url: "ajax/updateAvaliacao.php",
									data: {descricao:rate, idartigo:' . $idartigo . ', idaluno:' . $idaluno . '},
									type: "POST"
								});
							}
						});
					});
				</script><br/>';

		$html .= '<center>
					<input type="button" value="'.get_string('voltar', 'block_faq').'" onclick="history.go(-1)"/>
				</center>';

		$mform->addElement ( 'html', $html);

	}
}
	