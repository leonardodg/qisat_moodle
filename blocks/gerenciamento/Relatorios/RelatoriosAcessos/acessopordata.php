<?php
require_once('../../../../config.php');
require_once('forms/acessopordata_form.php');

global $CFG, $DB;

require_once($CFG->libdir.'/tablelib.php');

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url('/blocks/gerenciamento/Relatorios/RelatoriosAcessos/acessopordata.php');
$PAGE->set_title(get_string('acessopordata', 'block_gerenciamento'));

$PAGE->navbar->add(get_string('blockname', 'block_gerenciamento'))->
	add(get_string('relatorios', 'block_gerenciamento'))->
	add(get_string('relatoriosacessos', 'block_gerenciamento'))->
	add(get_string('acessopordata', 'block_gerenciamento'), '/blocks/gerenciamento/Relatorios/RelatoriosAcessos/acessopordata.php');
$PAGE->set_pagelayout('incourse');

$sort    			= optional_param('sort', null, PARAM_ALPHANUM);
$page 				= optional_param('page', 0, PARAM_INT);	
$perpage			= optional_param('perpage', 20, PARAM_INT);	
$curso				= optional_param('curso', 1, PARAM_INT);
$datainicio			= optional_param('datainicio', 0, PARAM_INT);
$datafim			= optional_param('datafim', 0, PARAM_INT);
$pesquisaCategoria 	= optional_param('pesquisacategoria', 0, PARAM_INT);
$radioSelecionado 	= optional_param('radioSelecionado',0, PARAM_INT);
$selectCategoria 	= optional_param('selectcategoria', '', PARAM_TEXT);

if (has_capability('block/gerenciamento:acessopordata', $context)) {

	echo '<link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
			<script src="//code.jquery.com/jquery-1.10.2.js"></script>
			<script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>';

	echo $OUTPUT->heading(get_string('acessopordata', 'block_gerenciamento'));
	echo $OUTPUT->header();

	echo $OUTPUT->heading(get_string('descricao', 'block_gerenciamento'));
	echo $OUTPUT->heading(get_string('descricaoacessopordata', 'block_gerenciamento'), 6);

	$baseurl = $CFG->wwwroot.'/blocks/gerenciamento/Relatorios/RelatoriosAcessos/acessopordata.php';
	$variaveis = array('selectcategoria'=>$selectCategoria,'curso'=>$curso,'datainicio'=>$datainicio,'datafim'=>$datafim,'pesquisacategoria'=>$pesquisaCategoria,'radioSelecionado'=>$radioSelecionado);
	$form = new blocks_gerenciamento_acessopordata_form($baseurl, $variaveis);
	$form->display();

	$where = '';

	if ($dados = $form->get_data()) {

		$dados = $form->get_submitted_data();
		$curso = $dados->curso;
		$datainicio = $dados->datainicio;
		$datafim = $dados->datafim;
		$pesquisaCategoria = $dados->categorias;
		$radioSelecionado = $dados->radiocurso;

		$where =" where
				e.enrol = 'manual' and tab.time >= ".$dados->datainicio." and tab.time <=".$dados->datafim;

		if(($dados->categorias)&&($dados->radiocurso == 0)){
			$where .= " and c.category = ".$dados->categorias;
		}

		if(($dados->curso>1)&&($dados->radiocurso == 1)){
			$where .= " and c.id =".$dados->curso;
		}

	}else if($datainicio){

		$where =" where
				e.enrol = 'manual' and tab.time >= ".$datainicio." and tab.time <=".$datafim;

		if(($pesquisaCategoria)&&($radioSelecionado == 0)){
			$where .= " and c.category= ".$pesquisaCategoria;
		}

		if(($curso>1)&&($radioSelecionado == 1)){
			$where .= " and c.id =".$curso;
		}

	}

	if(!empty($where)){

		$order = '';
		if(isset($sort)){
			$order = " order by ".$sort;
		}

		$sql = "select ue.id
				,c.shortname as curso
				,FROM_UNIXTIME(tab.time , '%m') as mes
				,FROM_UNIXTIME(tab.time , '%Y') as ano
				,count(*) as total

				from {user_enrolments} ue 

				inner join {enrol} e 
				on e.id = ue.enrolid 

				inner join (
					(select min(csl.id) as id,csl.user_id as userid, cs.course, csl.inicio_acesso as time 
							from {course_section_log} csl
							left join {course_sections} cs 
							on cs.id=csl.course_section_id
							group by csl.user_id, cs.course)
				UNION
					(select min(id) as id, userid, courseid as course, timecreated as time 
							from {logstore_standard_log} lsl
							group by userid, courseid)
				) as tab
				on tab.userid = ue.userid
				and tab.course = e.courseid

				left join {course} c
				on e.courseid = c.id

				left join {user} u
				on ue.userid = u.id  $where 
				group by curso,ano,mes  $order";

		//$result = $DB->get_records_sql($sql, null, $page*$perpage, $perpage);
		$result = $DB->get_records_sql($sql);

		if(!empty($result)){

			$result2 = array_chunk($result, $perpage);
			$result2 = $result2[$page];

			$somaValores = 0;
			foreach ($result as $registros){
				$somaValores += (int)$registros->total;
			}

			$media = $somaValores/count($result);

			$tableTotal = new html_table();
			$tableTotal->align = array ("center","center","center");
			$tableTotal->head = array(get_string('regtabela', 'block_gerenciamento'),
										get_string('acessostabela', 'block_gerenciamento'),
										get_string('mediatabela', 'block_gerenciamento'));
			$tableTotal->data[] = array(count($result),$somaValores,round($media,2));
			echo html_writer::table($tableTotal);

			$tablecolumns = array( "curso" , "mes", "ano" , "total");

			$tableheaders  = array (
				get_string('curso', 'block_gerenciamento'),
				get_string('mestabela', 'block_gerenciamento'),
				get_string('anotabela', 'block_gerenciamento'),
				get_string('totaltabela', 'block_gerenciamento')
			);

			$table = new flexible_table('curso');

			$table->define_columns($tablecolumns);
			$table->define_headers($tableheaders);

			$table->column_style['curso'] = array('width' => '100px' );
			$table->column_style['mes'] = array('width' => '100px' , 'text-align' => 'center');
			$table->column_style['ano'] = array('width' => '100px' , 'text-align' => 'center');
			$table->column_style['total'] = array('width' => '100px' , 'text-align' => 'center');

			$table->sortable(true,'curso');

			$table->set_attribute('cellspacing', '20');
			$table->set_attribute('class', 'generaltable generalbox');

			$baseurl .= '?curso='.$curso.'&selectcategoria='.$selectCategoria.'&datainicio='.$datainicio.'&datafim='.$datafim.'&pesquisacategoria='.$pesquisaCategoria.'&radioSelecionado='.$radioSelecionado;
			$table->define_baseurl($baseurl);

			$table->setup();
			$table->initialbars(true);

			foreach ($result2 as $dados){
				$data = array(
					$dados->curso,
					userdate(mktime(0, 0, 0, $dados->mes, 10, $dados->ano), '%B',99),
					$dados->ano,
					$dados->total
				);

				$table->add_data($data);
			}

			$table->print_html();

		}else{
			echo '<br/>';
			echo $OUTPUT->notification(get_string('nodados', 'block_gerenciamento'));
		}
	}
} else {
	redirect(new moodle_url($CFG->wwwroot), get_string('erropermissao', 'block_gerenciamento'));
}
echo $OUTPUT->footer();
?>