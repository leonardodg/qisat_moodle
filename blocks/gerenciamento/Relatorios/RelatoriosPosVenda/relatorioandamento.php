<?php
ini_set('max_execution_time','360000');
require_once('../../../../config.php');
require_once($CFG->libdir . '/tablelib.php');
require_once('forms/relatorioandamento_form.php');
require_once('../../lib.php');

global $CFG, $DB;

$page = optional_param ( 'page', 0, PARAM_INT ); 
$perpage = optional_param ( 'perpage', 20, PARAM_INT ); 
$course = optional_param ( 'course', 0, PARAM_INT );
$databegin = optional_param ( 'pesquisa_inicio', 0, PARAM_INT );
$dataend = optional_param ( 'pesquisa_fim', 0, PARAM_INT );
$radioConsulta = optional_param ( 'radioConsulta', 0, PARAM_INT );
$radioTotais = optional_param ( 'radioTotais', 0, PARAM_INT );
if(!$databegin || !$dataend){
	$databegin = optional_param ( 'databegin', 0, PARAM_INT );
	$dataend = optional_param ( 'dataend', 0, PARAM_INT );
}

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url('/blocks/gerenciamento/Relatorios/RelatoriosPosVenda/relatorioandamento.php');
$PAGE->set_title(get_string('relatorioandamento', 'block_gerenciamento'));

$PAGE->navbar->add(get_string('blockname', 'block_gerenciamento'))->
	add(get_string('relatorios', 'block_gerenciamento'))->
	add(get_string('relatoriosposvenda', 'block_gerenciamento'))->
	add(get_string('relatorioandamento', 'block_gerenciamento'), '/blocks/gerenciamento/Relatorios/RelatoriosPosVenda/relatorioandamento.php');
$PAGE->set_pagelayout('incourse');

$baseurl = $CFG->wwwroot . '/blocks/gerenciamento/Relatorios/RelatoriosPosVenda/relatorioandamento.php';
$variaveis = array (
	'course' => $course,
	/*'pesquisa_inicio' => $pesquisa_inicio,'pesquisa_fim' => $pesquisa_fim,*/
	'radioConsulta' => $radioConsulta
	/*,radioTotais' => $radioTotais*/
);

if (has_capability('block/gerenciamento:relatorioandamento', $context)) {
	//exec ( 'wmic cpu get loadpercentage', $retval );

	echo '<link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
		  <script src="//code.jquery.com/jquery-1.10.2.js"></script>
		  <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>';

	echo $OUTPUT->heading(get_string('relatorioandamento', 'block_gerenciamento'));
	echo $OUTPUT->header();
	
	//if ($retval [1] <= 60) {
		echo '<script type="text/javascript" src="js/relatorioAndamento.js"/></script>';
		echo '<div id="preloaderPaginaAndamento">
					<center><img style="width: 32px;height:32px;" src="imagens/preloader.gif"></center>
			  </div>';
		echo '<div id="paginaAndamento" style="display:none;">';

		echo $OUTPUT->heading(get_string('descricao', 'block_gerenciamento'));
		echo $OUTPUT->heading(get_string('descricaorelatorioandamento', 'block_gerenciamento'), 6);
		
		$relatorioandamento_form = new blocks_gerenciamento_relatorioandamento_form($baseurl, $variaveis);
		$relatorioandamento_form->display ();
		
		echo '<div id="resultadoPaginaAndamento">';
		if ($relatorioandamento_form->no_submit_button_pressed () || $radioConsulta) {
			$dados = $relatorioandamento_form->get_submitted_data ();
			
			$where = '';
			if ($course > 1) {
				$where .= " AND c.id = $course";
			}
			if ($radioConsulta == 1) {
				$where .= " AND (tab2.timeend >= $databegin AND tab2.timestart < $dataend)";
			} elseif($radioConsulta == 2){
				$where .= " AND (tab2.data_conclusao BETWEEN $databegin AND $dataend)";
			} elseif($radioConsulta == 3){
				$where .= " AND tab2.timeend BETWEEN $databegin AND $dataend ";				
			} elseif($radioConsulta == 4){
				$where .= " tab2.timestart BETWEEN $databegin AND $dataend ";
			}

			$tablecolumns = array (
					"chave",
					"nome",
					"email",
					"curso",
					"data_inicio_curso",
					"data_fim_curso"
			);
						
			$tableheaders = array (
					get_string ( 'chave', 'block_gerenciamento' ),
					get_string ( 'name' ),
					get_string ( 'email', 'block_gerenciamento' ),
					get_string ( 'course' ),
					get_string ( 'data_inicio_curso', 'block_gerenciamento' ),
					get_string ( 'data_fim_curso', 'block_gerenciamento' )
			);
			
			if ($radioConsulta == 2 || $radioConsulta == 4) {
				$tablecolumns [] = "data_conclusao";
				$tableheaders [] = get_string ( 'dataConclusao', 'block_gerenciamento' );
			}
				
			$tablecolumns [] = "total_cursos";
			$tablecolumns [] = "status";
			
			$tableheaders [] = get_string ( 'totalCursos', 'block_gerenciamento' );
			$tableheaders [] = get_string ( 'status', 'block_gerenciamento' );
			
			$table = new flexible_table ( 'chave' );
			
			$table->define_columns ( $tablecolumns );
			$table->define_headers ( $tableheaders );

			$table->column_style ['chave'] = array (
					'text-align' => 'center'
			);
			$table->column_style ['nome'] = array (
					'text-align' => 'center' 
			);
			$table->column_style ['email'] = array (
					'text-align' => 'center' 
			);
			$table->column_style ['curso'] = array (
					'text-align' => 'center' 
			);
			$table->column_style ['data_inicio_curso'] = array (
					'text-align' => 'center' 
			);
			$table->column_style ['data_fim_curso'] = array (
					'text-align' => 'center' 
			);
			$table->column_style ['total_cursos'] = array (
					'text-align' => 'center' 
			);
			$table->column_style ['status'] = array (
					'text-align' => 'center' 
			);
			
			$table->sortable ( true, 'nome' );
			$table->no_sorting ( 'chave ' );
			$table->no_sorting ( 'email' );
			$table->sortable ( true, 'data_inicio_curso' );
			$table->sortable ( true, 'data_fim_curso' );
			$table->no_sorting ( 'status' );

			$table->set_attribute ( 'width', '100%' );
			$table->set_attribute ( 'cellspacing', '0' );
			$table->set_attribute ( 'class', 'generaltable generalbox' );
			
			if ($where) {
				$baseurl .= '?course=' . $course . '&databegin=' . $databegin . '&dataend=' . $dataend . '&radioConsulta=' . $radioConsulta . '&radioTotais=' . $radioTotais;
			}
			$table->define_baseurl ( $baseurl );
			
			$table->setup ();
			$table->initialbars ( true );
			
			$order = " ORDER BY tab2.timestart";
			if ($table->get_sql_sort ()) {
				$order = " ORDER BY " . $table->get_sql_sort ();
			}
			
			$whereIn = '';
			$whereSubConsulta = ' ra.roleid IN (5,8,9,10)';
			switch ($radioConsulta) {
				case 1 :
					$whereIn = ' tab2.ra_id IS NULL';
					$whereSubConsulta = " ue.timeend >= $databegin AND ue.timestart < $dataend AND ra.roleid IN (5,8,9,10)";
					break;
				case 2 :
					$whereIn = ' tab2.ra_id IS NOT NULL';
					break;
				case 3 :
					$whereIn = ' tab2.ra_id IS NULL';
					$whereSubConsulta = ' ue.timeend < UNIX_TIMESTAMP() AND ue.timeend <> 0 AND ra.roleid IN (5,8,9,10)';
					break;
				/*case 4 :
					$whereIn = '';
					$whereSubConsulta = ' ra.roleid IN (5,8,9,10) ';
					break;*/
			}
			
			$count = getTotalUsuarios($whereSubConsulta,$whereIn,$where,1);
			$table->pagesize ( $perpage, $count );
			
			$sql = "SELECT tab2.id AS 'id',
						u.idnumber AS 'chave',
						u.id AS 'userid',
				 		CONCAT(u.firstname, ' ' , u.lastname) AS 'nome',
				 		u.email AS 'email',
				 		c.shortname AS 'curso',
				 		c.id AS 'idcurso',
				 		tab2.enrolperiod as duracao,
				 		tab2.timestart AS 'data_inicio_curso',
				 		tab2.timeend AS 'data_fim_curso',
				 		(select count(id) from {role_assignments} ra where userid = u.id and roleid in (5,8,9,10)) as 'total_cursos',
				 		tab2.data_conclusao 
				FROM (
						SELECT ra.*, tab1.*, ue.timestart, ue.timeend, e.enrolperiod 
						FROM {role_assignments} ra
						
						LEFT JOIN ( SELECT ra1.id AS ra_id, cti.`timecreated` as data_conclusao 
                        
                                    FROM {certificate} ct
                                        
                                    INNER JOIN {certificate_issues} cti
                                    ON cti.certificateid = ct.id
                                        
                                    INNER JOIN {context} ctx
                                    ON ctx.instanceid = ct.course
                                    AND ctx.contextlevel = 50
                                        
                                    INNER JOIN {role_assignments} ra1 
                                    ON ra1.contextid = ctx.id
                                    AND ra1.userid = cti.userid 
									
								  ) tab1
						ON tab1.ra_id = ra.id 
    
                        INNER JOIN {context} c 
                        ON c.id = ra.contextid 

                        INNER JOIN {enrol} e 
                        ON e.courseid = c.instanceid 

                        INNER JOIN {user_enrolments} ue 
                        ON ue.enrolid = e.id 
                        AND ue.userid = ra.userid 
						
						WHERE $whereSubConsulta
					 ) tab2
					
				INNER JOIN {user} u
				ON tab2.userid = u.id
					
				INNER JOIN {context} ctx
				ON ctx.id = tab2.contextid
					
				INNER JOIN {course} c
				ON c.id = ctx.instanceid 
									
				WHERE $whereIn";

			$result = $DB->get_records_sql ( $sql . $where . $order, null, $table->get_page_start (), $table->get_page_size () );
			
			foreach ( $result as $dados ) {
				
				$dataFim = $dados->data_fim_curso == 0 ? get_string ( 'prazoilimitado', 'block_gerenciamento' ):date ( 'd/m/Y', $dados->data_fim_curso );
				
				$data = array (
						$dados->chave,
						'<a href="' . $CFG->wwwroot . '/user/view.php?id=' . $dados->userid . '">' . $dados->nome . '</a>',
						$dados->email,
						$dados->curso,
						date ( 'd/m/Y', $dados->data_inicio_curso ),
						$dataFim,
						);
				
				if($radioConsulta == 2 || $radioConsulta == 4){
					$data[] = $dados->data_conclusao? date ( 'd/m/Y', $dados->data_conclusao):get_string ( 'naofinalizado', 'block_gerenciamento' );
				}
				
				$detalhar='<img style="cursor:pointer;margin-left:10px;" id="'.$dados->userid.'" src="imagens/detalhar.png" class="iconsmall abrir-detalhes" title="'.get_string('detalhar','block_gerenciamento').'"/>';
				
				$data [] = $dados->total_cursos.$detalhar;
				$data [] = get_string ( 'pacote', 'block_gerenciamento' ) . ': ' . get_string ( 'no' ) . '<br><a href="../../Central/AlunosAcessos/historico.php?iduser=' . $dados->userid . '&courseid=' . $dados->idcurso . '&chave=' . $dados->chave . '" target="_blank">Historico</a>'; 
				
				$table->add_data ( $data );
			}
			
			echo '<br>';
			if (isset ( $table )){
				if($radioTotais){
					$tableTotal = new html_table();
					$tableTotal->width = "100%";
					$tableTotal->size = array ("50%","50%");
					$tableTotal->align = array ("left","left");
					
					$tableTotal->head = array (
							get_string ( 'indice', 'block_gerenciamento' ),
							get_string ( 'valor', 'block_gerenciamento' )
					);
					
					$tableTotal->data[]=array(get_string ( 'totalRegistros', 'block_gerenciamento' ),$count);
					$tableTotal->data[]=array(get_string ( 'totalChaves', 'block_gerenciamento' ),getTotalUsuarios($whereSubConsulta,$whereIn,$where,2));
					//$tableTotal->data[]=array(get_string ( 'totalPedidos', 'block_gerenciamento' ),getTotalUsuarios($whereSubConsulta,$whereIn,$where,3));
					
					echo html_writer::table($tableTotal);
				} else {
					$table->print_html();
				}
			}
		}
		echo '</div></div>';
	/*} else {
		echo '<br>';
		echo $OUTPUT->notification(get_string('erroNivelProcessamento', 'block_gerenciamento'), $CFG->wwwroot . '/blocks/gerenciamento/Relatorios/RelatoriosPosVenda/relatorioandamento.php');
	}*/
} else {
	redirect(new moodle_url($CFG->wwwroot), get_string('erropermissao', 'block_gerenciamento'));
}
echo $OUTPUT->footer();

?>