<?php
ini_set('max_execution_time','360000');
require_once('../../../../config.php');
require_once('forms/relatorioquantidades_form.php');
require_once('../RelatoriosUser/grafico/highcharts.php');

global $CFG, $DB;

$tiporelatorio = optional_param('tiporelatorio', null, PARAM_INT);
$curso = optional_param('curso', null, PARAM_INT);

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url('/blocks/gerenciamento/Relatorios/RelatoriosAdministrativos/relatorioquantidades.php');
$PAGE->set_title(get_string('relatorioquantidades', 'block_gerenciamento'));

$PAGE->navbar->add(get_string('blockname', 'block_gerenciamento'))->
	add(get_string('relatorios', 'block_gerenciamento'))->
	add(get_string('relatoriosadministrativos', 'block_gerenciamento'))->
	add(get_string('relatorioquantidades', 'block_gerenciamento'), '/blocks/gerenciamento/Relatorios/RelatoriosAdministrativos/relatorioquantidades.php');
$PAGE->set_pagelayout('incourse');

if (has_capability('block/gerenciamento:relatorioquantidades', $context)) {

	echo '<script src="//code.jquery.com/jquery-1.10.2.js"></script>
		  <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
		  <script src="../RelatoriosUser/grafico/js/highcharts.js"></script>
		  <script src="../RelatoriosUser/grafico/js/modules/exporting.js"></script>';

	echo $OUTPUT->heading(get_string('relatorioquantidades', 'block_gerenciamento'));
	echo $OUTPUT->header();

	echo $OUTPUT->heading(get_string('descricao', 'block_gerenciamento'));
	echo $OUTPUT->heading(get_string('descricaorelatorioquantidades', 'block_gerenciamento'), 6);

	$parametros = array('tiporelatorio'=>$tiporelatorio, 'curso'=>$curso);
	$form = new blocks_gerenciamento_relatorioquantidades_form($CFG->wwwroot.'/blocks/gerenciamento/Relatorios/RelatoriosAdministrativos/relatorioquantidades.php', $parametros);

	if ($data = $form->get_data()) {

		$arrayTipoRelatorio = array("inscritosNoMoodle" => get_string('inscritosnomoodle', 'block_gerenciamento'),
										"nuncaAcessarram" => get_string('nuncaacessarram', 'block_gerenciamento'),
										"expirados" => get_string('expirados', 'block_gerenciamento'),
										"finalizados" => get_string('finalizados', 'block_gerenciamento'),
										"comprouMesmoAno" => get_string('comproumesmoano', 'block_gerenciamento'));
		$relatorio = $arrayTipoRelatorio[$data->tiporelatorio];

		$ano = date("Y",time());
		$sqlAndCurso = "";

		if($data->curso>1)
			$sqlAndCurso = " and c.id=".$data->curso;

		if($data->tiporelatorio == 'inscritosNoMoodle'){ //------------------------------

			$sql="select ue.id,FROM_UNIXTIME(ue.timestart , '%Y') as ano,c.shortname as curso,count(*) as total
							from {user_enrolments} ue
								inner join {enrol} e on e.id = ue.enrolid
								left join {course} c on e.courseid = c.id
								left join {user} u on ue.userid = u.id  
							where FROM_UNIXTIME(ue.timestart , '%Y')<={$ano} {$sqlAndCurso} 
							group by ano,c.shortname ORDER by c.shortname,ano";
		}elseif($data->tiporelatorio=='nuncaAcessarram'){ //------------------------------

			$sql="select ue.id, c.shortname as curso, FROM_UNIXTIME(ue.timestart , '%Y') as ano, count(*) as total
							from {user_enrolments} ue
								inner join {enrol} e on e.id = ue.enrolid
								left join ( select min(csl.id) as id,csl.user_id as userid, cs.course, csl.inicio_acesso as time 
											from {course_section_log} csl
											left join {course_sections} cs 
											on cs.id=csl.course_section_id
											group by csl.user_id, cs.course) as tab
									 on tab.userid = ue.userid and tab.course = e.courseid 	
								left join {course} c on e.courseid=c.id
								left join {user} u on ue.userid=u.id	
							where tab.time is null and FROM_UNIXTIME(ue.timestart , '%Y')<={$ano} {$sqlAndCurso}
							group by c.shortname,ano";	
		}elseif($data->tiporelatorio=='expirados'){ //------------------------------

			$sql="select ue.id,e.courseid,c.shortname as curso,FROM_UNIXTIME(ue.timeend  , '%Y') as ano,count(*) as total 
						  from {user_enrolments} ue 
							inner join {enrol} e on e.id = ue.enrolid 
							left join (select sci.id,sci.userid, sc.course, sci.timecreated 
											from {certificate_issues} sci 
											left join {certificate} sc 
											on sci.certificateid=sc.id) as tab 
									 on tab.userid = ue.userid and tab.course = e.courseid 
							left join {course} c on e.courseid=c.id 
							left join {user} u on ue.userid=u.id 
						where FROM_UNIXTIME(ue.timeend  , '%Y')<={$ano} {$sqlAndCurso}  AND tab.id IS NULL
							  and ue.timeend > 0
						group by c.shortname,ano";
		}elseif($data->tiporelatorio=='finalizados'){ //------------------------------

			$sql="select sci.id,c.shortname as curso,FROM_UNIXTIME(sci.timecreated , '%Y') as ano,count(*) total
							from {certificate_issues} sci
								inner join {certificate} sc on sci.certificateid=sc.id
								inner join {user} u on sci.userid=u.id
								inner join {course} c on sc.course=c.id
								inner join {role_assignments} ra
								inner join {context} co on ra.contextid = co.id
								and co.instanceid = c.id 
								and ra.userid=u.id
							where FROM_UNIXTIME(sci.timecreated , '%Y')<={$ano} {$sqlAndCurso}
							group by curso,ano";
		}elseif($data->tiporelatorio=='comprouMesmoAno'){ //------------------------------

			$sql="select ue.id, c.shortname as curso, FROM_UNIXTIME(ue.timestart , '%Y') as ano, count(*) as total
							from {user_enrolments} ue
								inner join {enrol} e on e.id = ue.enrolid
								left join ( select min(csl.id) as id,csl.user_id as userid, cs.course, csl.inicio_acesso as time 
											from {course_section_log} csl
											left join {course_sections} cs 
											on cs.id=csl.course_section_id
											group by csl.user_id, cs.course) as tab
									 on tab.userid = ue.userid and tab.course = e.courseid 	
								left join {course} c on e.courseid=c.id
								left join {user} u on ue.userid=u.id
							where FROM_UNIXTIME(ue.timestart , '%Y')=FROM_UNIXTIME(tab.time , '%Y') {$sqlAndCurso}
							group by c.shortname,ano";
		}

		/*------------------------------- Montando a Tabela------------------------------------------------------*/

		$sql_curso = "select id,shortname from {course} c where c.id!=1 {$sqlAndCurso}";
		$result = $DB->get_records_sql($sql_curso);

		$nomeHeader=get_string('relatorios', 'block_gerenciamento');
		$subtitle = get_string('subtitleqtd', 'block_gerenciamento');
		if($data->curso>1){
			$nomeHeader.=" do Curso ".$result[$data->curso]->shortname;
			$subtitle .=" do Curso ".$result[$data->curso]->shortname;
		}else{
			$nomeHeader.=" Geral";
			$subtitle .=" Geral";
		}

		$curso = array();
		$ano = "SELECT FROM_UNIXTIME(MIN(ue.timestart), '%Y') AS ano
				FROM mdl_user_enrolments ue
				WHERE ue.timestart > 0";
		$result_ano = $DB->get_record_sql($ano);

		$total[] = "TOTAL";
		for($i=$result_ano->ano;$i<=date('Y',time());$i++){
			foreach($result as $lista){
				$curso[$lista->shortname][$i]=0;
				$total[$i] = 0;
			}
		}

		$result = $DB->get_records_sql($sql);

		if($result){
			foreach ($result as $lista){
				$curso[$lista->curso][$lista->ano]= $lista->total;
			}

			$table = new html_table();
			$table->head = array (get_string('curso', 'block_gerenciamento'));
			$table->align = array ("left");

			for($cont = $result_ano->ano;$cont<= date("Y", time());$cont++){
				$anos[$cont] = 0;
				$table->head[] = (int)$cont;
				$table->align[] = "center";
			}
			$table->data = array();
			unset($curso[0]);

			ksort($curso);
			foreach ($curso as $key => $data){
				$result = array();
				$result[] = $key;
				foreach ($data as $ano => $valor){
					$result[] = $valor;
					if(!isset($total[$ano]))
						$total[$ano] = 0;
					
					$total[$ano] += $valor;
				}
				$table->data[] = $result;
			}

			$table->data[] = $total;

			$totalGeral = "Total Geral: ".array_sum($total);

			//---------------------------------------------Grafico-----------------------------------------------------------

			$ob = new Highcharts();
			$ob->chart->marginRight = 0;
			$ob->title->text = get_string('titleqtd', 'block_gerenciamento').$relatorio;
			$ob->subtitle->text = $subtitle;
			$ob->xAxis->categories = array();

			$cont = 1;
			for($i=$result_ano->ano;$i<=date('Y',time());$i++){
				$ob->xAxis->categories[$cont] = $i;
				$cont++;
			}
			$ob->yAxis->title = new object();
			$ob->yAxis->title->text = '';
			$ob->yAxis->min = 0;

			$ob->tooltip = new object();
			$ob->tooltip->formatter = "function";

			$ob->functions['formatter'] = "function() {
					                        return \"<b>\"+ this.series.name +\"</b><br/>\"+
					                        this.x +\": \"+ this.y ;
					                	}";
			$ob->divStyles = array( "min-width" => "400px" , "height" => "400px", "margin" =>"0 auto");

			$data1 = new object();
			$data1->data = array();
			$data1->name = "Total";
			unset($total[0]);
			foreach ($total as $valor){
				$data1->data [] = $valor;
			}
			$ob->series[] = $data1;
		}else{
			echo '<br>';
			echo $OUTPUT->notification(get_string('nodados', 'block_gerenciamento'));
		}
	}

	$form->display();

	if(isset($result) && $result){
		echo '<br>';
		$ob->display();
		echo '<center>'.$OUTPUT->heading($totalGeral, 5).'</center><br/>';
		echo $OUTPUT->heading($nomeHeader, 5);
		echo html_writer::table($table);
	}

} else {
	redirect(new moodle_url($CFG->wwwroot), get_string('erropermissao', 'block_gerenciamento'));
}
echo $OUTPUT->footer();
?>