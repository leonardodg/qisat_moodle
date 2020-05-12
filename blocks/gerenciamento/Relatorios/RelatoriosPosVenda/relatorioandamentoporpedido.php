<?php
ini_set('max_execution_time','360000');
require_once('../../../../config.php');
require_once($CFG->libdir . '/tablelib.php');
require_once('forms/relatorioandamentoporpedido_form.php');
require_once('../../lib.php');

global $CFG, $DB;

$page = optional_param ( 'page', 0, PARAM_INT ); 
$perpage = optional_param ( 'perpage', 20, PARAM_INT ); 
//$pedido = optional_param ( 'pedido', '', PARAM_INT );
$chave = optional_param ( 'chave', '', PARAM_TEXT );

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url('/blocks/gerenciamento/Relatorios/RelatoriosPosVenda/relatorioandamentoporpedido.php');
$PAGE->set_title(get_string('relatorioandamentoporpedido', 'block_gerenciamento'));

$PAGE->navbar->add(get_string('blockname', 'block_gerenciamento'))->
	add(get_string('relatorios', 'block_gerenciamento'))->
	add(get_string('relatoriosposvenda', 'block_gerenciamento'))->
	add(get_string('relatorioandamentoporpedido', 'block_gerenciamento'), '/blocks/gerenciamento/Relatorios/RelatoriosPosVenda/relatorioandamentoporpedido.php');
$PAGE->set_pagelayout('incourse');

$baseurl = $CFG->wwwroot . '/blocks/gerenciamento/Relatorios/RelatoriosPosVenda/relatorioandamentoporpedido.php';
$variaveis = array (
	//'pedido' => $pedido,
	'chave' => $chave
);

if (has_capability('block/gerenciamento:relatorioandamentoporpedido', $context)) {
	//exec ( 'wmic cpu get loadpercentage', $retval );

	echo '<link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
		  <script src="//code.jquery.com/jquery-1.10.2.js"></script>
		  <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>';

	echo $OUTPUT->heading(get_string('relatorioandamentoporpedido', 'block_gerenciamento'));
	echo $OUTPUT->header();
	
	//if ($retval [1] <= 60) {
		echo '<script type="text/javascript" src="js/relatorioAndamento.js" /> </script> ';
		/*echo '<div id="preloaderPaginaAndamento">
					<center><img style="width: 32px;height:32px;" src="imagens/preloader.gif"></center>
			  </div>';
		echo '<div id="paginaAndamento" style="display:none;">';*/

		echo $OUTPUT->heading(get_string('descricao', 'block_gerenciamento'));
		echo $OUTPUT->heading(get_string('descricaorelatorioandamentoporpedido', 'block_gerenciamento'), 6);
		
		$relatorioandamentoporpedido_form = new blocks_gerenciamento_relatorioandamentoporpedido_form($baseurl, $variaveis);
		$relatorioandamentoporpedido_form->display ();
		
		//echo '<div id="resultadoPaginaAndamento">';		
		if ($relatorioandamentoporpedido_form->no_submit_button_pressed () || $chave/* || $pedido*/) {
			
			$dados = $relatorioandamentoporpedido_form->get_submitted_data ();
			/*if(!$pedido){
				$pedido = $dados->pedido;
			}*/
			if(!$chave){
				$chave = $dados->chave;
			}			
						
			$where = '';

			$chave = trim($chave);

			if (!empty( $chave )) {
				if(is_numeric($chave)){
					$where .= " AND u.idnumber like '" . $chave . "'";
				}else{
					$posString = strpos($chave, ' ');
					if($posString>0){
						$firstName = substr($chave, 0, $posString);
						$lastName = substr($chave, $posString+1, strlen($chave));
						$where .= " AND u.firstname LIKE'%" . $firstName."%'";
						$where .= " AND u.lastname LIKE'%" . $lastName."%'";
					}else{
						$where .= " AND u.firstname LIKE'%" . $chave."%'";
					}
				}
			}
			if($where != ''){
				$where = substr_replace($where, " WHERE", 0, 4);
			}	
			
			$tablecolumns = array (
					"chave",
					"nome",
					"email",
					"curso",
					"data_inicio_curso",
					"data_fim_curso",
					"total_cursos",
					"status"					
			);
			
			$tableheaders = array (
					get_string ( 'chave', 'block_gerenciamento'),
					get_string ( 'name' ),
					get_string ( 'email', 'block_gerenciamento' ),
					get_string ( 'course' ),
					get_string ( 'data_inicio_curso', 'block_gerenciamento' ),
					get_string ( 'data_fim_curso', 'block_gerenciamento' ),
					get_string ( 'totalCursos', 'block_gerenciamento' ),
					get_string ( 'status', 'block_gerenciamento' )
			);
			
			$table = new flexible_table ( 'chave' );
			
			$table->define_columns ( $tablecolumns );
			$table->define_headers ( $tableheaders );

			$table->column_style ['chave'] = array (
					'text-align' => 'center', 'width' => '8%'
			);
			$table->column_style ['nome'] = array (
					'text-align' => 'center', 'width' => '20%' 
			);
			$table->column_style ['email'] = array (
					'text-align' => 'center', 'width' => '20%' 
			);
			$table->column_style ['curso'] = array (
					'text-align' => 'center', 'width' => '8%' 
			);
			$table->column_style ['data_inicio_curso'] = array (
					'text-align' => 'center', 'width' => '8%' 
			);
			$table->column_style ['data_fim_curso'] = array (
					'text-align' => 'center', 'width' => '8%' 
			);
			$table->column_style ['total_cursos'] = array (
					'text-align' => 'center', 'width' => '8%' 
			);
			$table->column_style ['status'] = array (
					'text-align' => 'center', 'width' => '20%' 
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
				$baseurl .= '?chave='.$chave/*.'&pedido=' . $pedido*/;
			}
			
			$table->define_baseurl ( $baseurl );
			
			$table->setup ();
			$table->initialbars ( true );
			
			$order = " ORDER BY tab2.timestart";
			if ($table->get_sql_sort ()) {
				$order = " ORDER BY " . $table->get_sql_sort ();
			}
			
			$whereSubConsulta = ' ra.roleid IN (5,8,9,10)';
			
			$count = getTotalUsuariosPorPeriodo($whereSubConsulta, $where, 1);
			$table->pagesize ( $perpage, $count );
			
			$sql = "SELECT tab2.id AS 'id',
						u.idnumber AS 'chave',
						u.id AS 'userid',
				 		CONCAT(u.firstname, ' ' , u.lastname) AS 'nome',
				 		u.email AS 'email',
				 		c.shortname AS 'curso',
				 		c.id AS 'idcurso',
				 		e.enrolperiod as duracao,
				 		ue.timestart AS 'data_inicio_curso',
				 		ue.timeend AS 'data_fim_curso',
				 		(select count(id) from {role_assignments} ra where userid = u.id and roleid in (5,8,9,10)) as 'total_cursos',
				 		tab2.data_conclusao,
				 		tab2.roleid,
				 		r.shortname AS 'role_shortname'
				FROM (
						SELECT *
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
						
						WHERE $whereSubConsulta
					 ) tab2
					
				INNER JOIN {user} u
				ON tab2.userid = u.id
					
				INNER JOIN {context} ctx
				ON ctx.id = tab2.contextid
					
				INNER JOIN {course} c
				ON c.id = ctx.instanceid 
					
				INNER JOIN {enrol} e
				ON e.courseid = c.id 
					
				INNER JOIN {user_enrolments} ue
				ON ue.enrolid = e.id 
				AND ue.userid = u.id

				INNER JOIN {role} r
				ON r.id = tab2.roleid";
	
			$result = $DB->get_records_sql ( $sql . $where . $order, null, $table->get_page_start (), $table->get_page_size () );

			foreach ( $result as $dados ) {
				
				$dataFim = $dados->data_fim_curso == 0 ? get_string ( 'prazoilimitado', 'block_gerenciamento' ):date ( 'd/m/Y', $dados->data_fim_curso );

				$detalhar='<img style="cursor:pointer;margin-left:10px;" id="'.$dados->userid.'" src="imagens/detalhar.png" class="iconsmall abrir-detalhes" title="'.get_string('detalhar','block_gerenciamento').'"/>';

				//situacao
				$agendado = $dados->data_inicio_curso > time();
				$dias = ($dados->data_fim_curso - time())/3600/24;
				$diasint = ceil($dias);
				$dias = ceil($dias);
				
				$user_roles = get_user_roles($context, $dados->userid);
				foreach ($user_roles as $user_role) {
					if ($user_role->role_shortname == "bloqueado"){
						$bloqueado = true;
					}
				}
				$situacao = '';
				if ($dados->duracao == 0) {
					if (isset($bloqueado)) {
						$situacao = '<b>' . get_string('usuariobloqueado', 'block_gerenciamento') . '</b><br>';
					}
					elseif ($dados->role_shortname == "bloqueado") {
						$situacao = '<b>' . get_string('cursobloqueado', 'block_gerenciamento') . '</b><br>';
					}
					$situacao .= get_string('prazoilimitado', 'block_gerenciamento');
				}elseif (isset($dados->data_conclusao)) {
						$situacao = get_string('finalizadoem', 'block_gerenciamento').'<br>'.date("d/m/Y",$dados->data_conclusao);
				}else {
					if (isset($bloqueado)) {
						$situacao = '<b>' . get_string('usuariobloqueado', 'block_gerenciamento') . '</b><br>';
					}
					elseif ($dados->role_shortname == "bloqueado") {
						$situacao = '<b>' . get_string('cursobloqueado', 'block_gerenciamento') . '</b><br>';
					}
					elseif ($dados->role_shortname == "naohabilitado") {
						$situacao = get_string('naohabilitado', 'block_gerenciamento') . '<br>';
					}
					elseif ($dados->role_shortname == "semaceite") {
						$situacao = get_string('aguardando', 'block_gerenciamento')."<br>".get_string('aceitecontrato', 'block_gerenciamento')."<br>";
					}
					if (date("d/m/Y",$dados->data_fim_curso) == date("d/m/Y", time())) {
						$situacao .= get_string('ate', 'block_gerenciamento').date("d/m/Y",$dados->data_fim_curso)."<br>";
						$situacao .= get_string('expirahoje', 'block_gerenciamento');
					}
					elseif ($agendado) {
						$situacao .= get_string('cursoagendadopara', 'block_gerenciamento').date("d/m/Y",$dados->data_inicio_curso)."<br>";
					}
					elseif ($diasint > 0) {
						$situacao .= get_string('ate', 'block_gerenciamento').date("d/m/Y",$dados->data_fim_curso)."<br>";
						if ($diasint == 1){
							$situacao .= get_string('resta', 'block_gerenciamento').$dias.get_string('dia', 'block_gerenciamento');
						}else{
							$situacao .= get_string('restam', 'block_gerenciamento').$dias.get_string('dias', 'block_gerenciamento');
						}	
					}
					else {
						$situacao .= get_string('expirouem', 'block_gerenciamento').'<br>'.date("d/m/Y",$dados->data_fim_curso);
					}
				}

				if (has_capability('block/gerenciamento:verhistorico', $context)) {
					$situacao .= "<div><a href='../../Central/AlunosAcessos/historico.php?iduser=$dados->userid&courseid=$dados->idcurso&chave=$dados->chave'>".get_string('historico', 'block_gerenciamento')."</a></div>";
				}
									
				//situacao
				$data = array (
						$dados->chave,
						'<a href="' . $CFG->wwwroot . '/user/view.php?id=' . $dados->userid . '">' . $dados->nome . '</a>',
						$dados->email,
						$dados->curso,
						date ( 'd/m/Y', $dados->data_inicio_curso ),
						$dataFim,
						$dados->total_cursos.$detalhar,
						$situacao
						);
				
				$table->add_data ( $data );
			}
			
			echo '<br>';
			if (isset ( $table )){
				$tableTotal = new html_table();
				$tableTotal->width = "100%";
				$tableTotal->size = array ("50%","50%");
				$tableTotal->align = array ("left","left");
					
				$tableTotal->head = array (
						get_string ( 'indice', 'block_gerenciamento' ),
						get_string ( 'valor', 'block_gerenciamento' )
				);
					
				$tableTotal->data[]=array(get_string ( 'totalRegistros', 'block_gerenciamento' ),$count);
				$tableTotal->data[]=array(get_string ( 'totalChaves', 'block_gerenciamento' ),getTotalUsuariosPorPeriodo($whereSubConsulta,$where,2));
				//$tableTotal->data[]=array(get_string ( 'totalPedidos', 'block_gerenciamento' ),getTotalUsuariosPorPeriodo($whereSubConsulta,$where,3));
					
				echo html_writer::table($tableTotal);
			}
			
		}
		//echo '</div></div>';
	/*} else {
		echo '<br>';
		echo $OUTPUT->notification(get_string('erroNivelProcessamento', 'block_gerenciamento'), $CFG->wwwroot . '/blocks/gerenciamento/Relatorios/RelatoriosPosVenda/relatorioandamentoporpedido.php');
	}*/
} else {
	redirect(new moodle_url($CFG->wwwroot), get_string('erropermissao', 'block_gerenciamento'));
}
echo $OUTPUT->footer();

?>