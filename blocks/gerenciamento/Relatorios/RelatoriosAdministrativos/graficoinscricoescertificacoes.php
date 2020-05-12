<?php
require_once('../../../../config.php');
require_once('forms/graficoinscricoeseacessos_form.php');
require_once('../RelatoriosUser/grafico/highcharts.php');

global $CFG, $DB;

$curso = optional_param('curso', null, PARAM_INT);

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url('/blocks/gerenciamento/Relatorios/RelatoriosAdministrativos/graficoinscricoescertificacoes.php');
$PAGE->set_title(get_string('graficoinscricoescertificacoes', 'block_gerenciamento'));

$PAGE->navbar->add(get_string('blockname', 'block_gerenciamento'))->
	add(get_string('relatorios', 'block_gerenciamento'))->
	add(get_string('relatoriosadministrativos', 'block_gerenciamento'))->
	add(get_string('graficoinscricoescertificacoes', 'block_gerenciamento'), '/blocks/gerenciamento/Relatorios/RelatoriosAdministrativos/graficoinscricoescertificacoes.php');
$PAGE->set_pagelayout('incourse');

if (has_capability('block/gerenciamento:graficoinscricoescertificacoes', $context)) {

	echo '<script src="//code.jquery.com/jquery-1.10.2.js"></script>
		  <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
		  <script src="../RelatoriosUser/grafico/js/highcharts.js"></script>
		  <script src="../RelatoriosUser/grafico/js/modules/exporting.js"></script>';

	echo $OUTPUT->heading(get_string('graficoinscricoescertificacoes', 'block_gerenciamento'));
	echo $OUTPUT->header();

	echo $OUTPUT->heading(get_string('descricao', 'block_gerenciamento'));
	echo $OUTPUT->heading(get_string('descricaograficoinscricoescertificacoes', 'block_gerenciamento'), 6);

	$parametros = array("curso" => $curso);
	$form = new blocks_gerenciamento_graficoinscricoeseacessos_form($CFG->wwwroot.'/blocks/gerenciamento/Relatorios/RelatoriosAdministrativos/graficoinscricoescertificacoes.php', $parametros);

	if ($data = $form->get_data()) {

		$innerJoin = "";
		$group = " group by ano";
		$sql = "select FROM_UNIXTIME(ue.timestart , '%Y') as ano, count(*) as total_inscritos
					from {user_enrolments} ue 
					left join {enrol} e on ue.enrolid=e.id 
					left join {role_assignments} ra on ue.userid=ra.userid
					inner join {context} c on c.contextlevel=50 AND c.instanceid=e.courseid AND c.id=ra.contextid ";

		$where5 = " where ra.roleid = 5
							and FROM_UNIXTIME(ue.timestart , '%Y')>=".$data->ano."
				 			and FROM_UNIXTIME(ue.timestart , '%Y')<=".date("Y",time());

		$where10 = " where ra.roleid = 10
							and FROM_UNIXTIME(ue.timestart , '%Y')>=".$data->ano."
				 			and FROM_UNIXTIME(ue.timestart , '%Y')<=".date("Y",time());

		if($data->curso> 1 ){
			$innerJoin = " inner join {user} u on ue.userid=u.id
				    		  inner join {course} co on e.courseid = co.id ";

			$where5 .=" and co.id =".$data->curso ;
			$where10 .=" and co.id =".$data->curso ;
			$curso = $DB->get_record('course', array('id' => $data->curso));
		}

		$resultAtivo = $DB->get_records_sql($sql.$innerJoin.$where5.$group);

		$resultSemAceite = $DB->get_records_sql($sql.$innerJoin.$where10.$group);

		$sql="select FROM_UNIXTIME(sci.timecreated , '%Y') as ano, count(*) total_certificacoes
				from {certificate_issues} sci
					INNER JOIN {certificate} sc ON sci.certificateid = sc.id
				where FROM_UNIXTIME(sci.timecreated , '%Y')>=".$data->ano;
		if($data->curso>1){
			$sql .= " and sc.course =".$data->curso ;
		}
		$sql .= " GROUP BY ano";

		$resultCertificate = $DB->get_records_sql($sql);

		$ano=array();
		$inscricao=array();
		$certificados=array();
		$semAceite=array();

		for($i = $data->ano;$i<=date("Y",time());$i++){
			$ano[]= $i;
			if(array_key_exists($i, $resultAtivo)){
				$inscricao[$i]= (int) $resultAtivo[$i]->total_inscritos;
			}else{
				$inscricao[$i]= (int) 0;
			}

			if(array_key_exists($i, $resultCertificate)){
				$certificados[$i]= (int) $resultCertificate[$i]->total_certificacoes;
			}else{
				$certificados[$i]=(int) 0;
			}

			if(array_key_exists($i, $resultSemAceite)){
				$semAceite[$i]= (int) $resultSemAceite[$i]->total_inscritos;
			}else{
				$semAceite[$i]=(int) 0;
			}
		}

		$graf = new Highcharts();
		$graf->chart->marginRight = 0;
		$graf->title->text = get_string('titlegraficoinscricao', 'block_gerenciamento');

		if($curso){
			$graf->subtitle->text=$curso->fullname;
		}else{
			$graf->subtitle->text= get_string('allcursos', 'block_gerenciamento');
		}

		$graf->xAxis->categories = $ano;
		$graf->yAxis->title = new object();
		$graf->yAxis->title->text = get_string('qtd', 'block_gerenciamento');
		$graf->yAxis->min = 0;
		$graf->divStyles = array( "min-width" => "400px" , "height" => "400px", "margin" =>"0 auto");

		$graf->tooltip = new object();
		$graf->tooltip->formatter = "function";
		$graf->functions['formatter'] = "function() {
					                        return \"<b>\"+ this.series.name +\"</b><br/>\"+
					                        this.x +\": \"+ this.y ;
					                	}";
					                	
		$data1 = new object();
		$data1->name = get_string('inscricoes', 'block_gerenciamento');
		$data1->data = $inscricao;
		$data2 = new object();
		$data2->name = get_string('certificados', 'block_gerenciamento');
		$data2->data = $certificados;
		$data3 = new object();
		$data3->name = get_string('semaceite', 'block_gerenciamento');
		$data3->data = $semAceite;

		$graf->series[] = $data1;
		$graf->series[] = $data2;
		$graf->series[] = $data3;

	}
		
	$form->display();
	if(isset($graf))
	$graf->display();

} else {
	redirect(new moodle_url($CFG->wwwroot), get_string('erropermissao', 'block_gerenciamento'));
}
echo $OUTPUT->footer();
?>