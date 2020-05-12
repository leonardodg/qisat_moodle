<?php
require_once ('../../../../config.php');
require_once ($CFG->libdir . '/tablelib.php');
require_once ('forms/relatorioAndamentoCurso_form.php');
require_once('../../lib.php');

global $CFG, $DB;

$page = optional_param ( 'page', 0, PARAM_INT ); 
$perpage = optional_param ( 'perpage', 20, PARAM_INT ); 
$curso = optional_param ( 'curso', 0, PARAM_INT );
$turma = optional_param ( 'turma', null, PARAM_INT );

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url('/blocks/gerenciamento/Relatorios/RelatoriosPosVenda/relatorioAndamentoCurso.php');
$PAGE->set_title(get_string('relatorioandamentocurso', 'block_gerenciamento'));

$PAGE->navbar->add(get_string('blockname', 'block_gerenciamento'))->
	add(get_string('relatorios', 'block_gerenciamento'))->
	add(get_string('relatoriosposvenda', 'block_gerenciamento'))->
	add(get_string('relatorioandamentocurso', 'block_gerenciamento'), '/blocks/gerenciamento/Relatorios/RelatoriosPosVenda/relatorioAndamentoCurso.php');
$PAGE->set_pagelayout('incourse');

echo '<script src="//code.jquery.com/jquery-1.10.2.js"></script>
	  <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>';

echo $OUTPUT->heading(get_string('relatorioandamentocurso', 'block_gerenciamento'));
echo $OUTPUT->header();

if (has_capability('block/gerenciamento:relatorioandamentocurso', $context)) {

	echo '<script type="text/javascript" src="js/relatorioAndamentoCurso.js" /></script> ';
	
	ini_set('max_execution_time','360000');
	$baseurl = $CFG->wwwroot . '/blocks/gerenciamento/Relatorios/RelatoriosPosVenda/relatorioAndamentoCurso.php';
	$parametros = array('curso'=>$curso,'turmaSelecionado'=>$turma);
	$formulario = new blocks_gerenciamento_relatorioAndamentoCurso_form($baseurl, $parametros);
	
	if($dados = $formulario->get_data()){
		$curso = $dados->curso;
		$turma = $dados->turmaSelecionado == 0? null:$dados->turmaSelecionado;
	}

	echo $OUTPUT->heading(get_string('descricao', 'block_gerenciamento'));
	echo $OUTPUT->heading(get_string('descricaorelatorioandamento', 'block_gerenciamento'), 6);
	
	$formulario->display();

	if($curso > 0){
		$baseurl .= '?curso='.$curso;
		if(!is_null($turma)){
			$baseurl .= '&turma='.$turma;
		}
		
		$tablecolumns = array ("nome","turma","cidade","estado","topicos_total","topicos_assistidos","porcentagem","status");
		
		$tableheaders = array ( get_string ( 'name' ),
								get_string ( 'turma', 'block_gerenciamento' ), 
								get_string ( 'cidade', 'block_gerenciamento' ), 
								get_string ( 'estado', 'block_gerenciamento' ),
								get_string ( 'topicosTotais', 'block_gerenciamento' ), 
								get_string ( 'topicosAssistidos', 'block_gerenciamento' ), 
								get_string ( 'assistido', 'block_gerenciamento'),
								get_string ( 'status', 'block_gerenciamento'));
		
		$table = new flexible_table ( 'nome' );
		$table->sortable(true);
		$table->define_columns ( $tablecolumns );
		$table->define_headers ( $tableheaders );
		
		$table->column_style ['nome'] = array ('text-align' => 'center');
		$table->column_style ['turma'] = array ('text-align' => 'center');
		$table->column_style ['cidade'] = array ('text-align' => 'center');
		$table->column_style ['estado'] = array ('text-align' => 'center');
		$table->column_style ['topicos_total'] = array ('text-align' => 'center');
		$table->column_style ['topicos_assistidos'] = array ('text-align' => 'center');
		$table->column_style ['porcentagem'] = array ('text-align' => 'center');
		$table->column_style ['status'] = array ('text-align' => 'center');

		$table->no_sorting('topicos_total');
		$table->no_sorting('topicos_assistidos');
		$table->no_sorting('porcentagem');
		$table->no_sorting('status');
		
		$table->set_attribute ( 'width', '100%' );
		$table->set_attribute ( 'cellspacing', '0' );
		$table->set_attribute ( 'class', 'generaltable generalbox' );
		
		$table->define_baseurl ( $baseurl );
			
		$table->setup ();
		$table->initialbars ( true );
	
		$count = getTotal($curso,$turma);
		$table->pagesize($perpage, $count);
		
		$order = '';
		if ($table->get_sql_sort ()) {
			$order = " ORDER BY " . $table->get_sql_sort ();
		}
		
		$result = buscaAndamento($curso,$table->get_page_start(), $table->get_page_size (),$order,$turma);
		
		foreach ( $result as $andamento ) {
			$status = '';
			
			if(!is_null($andamento->finalizado)){
				$status = '<b>'.get_string('finalizadoem','block_gerenciamento').' '.date ( 'd/m/Y', $andamento->finalizado).'</b>';
			}elseif($andamento->timeend == 0){
				$status = get_string('prazoilimitado','block_gerenciamento');
			}elseif(date("d/m/Y",$andamento->timeend) == date("d/m/Y", time())) {
				$status = get_string('expirahoje', 'block_gerenciamento').' '.date("d/m/Y",$andamento->timeend);
			}elseif($andamento->timeend < time()){
				$status = get_string('cursoExpiradoEm','block_gerenciamento').': '.date('d/m/Y',$andamento->timeend);
			}else{
				$dias = ($andamento->timeend - time())/3600/24;
				$diasint = ceil($dias);
				$dias = ceil($dias);
				
				$situacao = get_string('ate', 'block_gerenciamento').date("d/m/Y",$andamento->timeend).'<br />'; 
				if ($diasint == 1){
					$situacao .= get_string('resta', 'block_gerenciamento').$dias.get_string('dia', 'block_gerenciamento');
				}else{
					$situacao .= get_string('restam', 'block_gerenciamento').$dias.get_string('dias', 'block_gerenciamento');
				}
				
				$status = $situacao;
			}
			
			if($andamento->topicos_total){
				$assistido = round(((100 * $andamento->topicos_assistidos) / ($andamento->topicos_total)),2).'%';
			} else {
				$assistido = '0%';
			}
			
			$status = '<b>'.$status.'</b>';
			
			$data = array ($andamento->nome,$andamento->turma,$andamento->cidade,$andamento->estado,$andamento->topicos_total,$andamento->topicos_assistidos,$assistido,$status);
			
			$table->add_data ( $data );
		}
		
		$table->print_html();
	}
	
}else{
	redirect(new moodle_url($CFG->wwwroot), get_string('erropermissao', 'block_gerenciamento'));
}

echo $OUTPUT->footer();
?>