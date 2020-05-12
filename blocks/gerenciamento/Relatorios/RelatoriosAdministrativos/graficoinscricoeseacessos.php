<?php
require_once('../../../../config.php');
require_once('forms/graficoinscricoeseacessos_form.php');
require_once('../RelatoriosUser/grafico/highcharts.php');

global $CFG, $DB;

$curso = optional_param('curso', null, PARAM_INT);

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url('/blocks/gerenciamento/Relatorios/RelatoriosAdministrativos/graficoinscricoeseacessos.php');
$PAGE->set_title(get_string('graficoinscricoeseacessos', 'block_gerenciamento'));

$PAGE->navbar->add(get_string('blockname', 'block_gerenciamento'))->
	add(get_string('relatorios', 'block_gerenciamento'))->
	add(get_string('relatoriosadministrativos', 'block_gerenciamento'))->
	add(get_string('graficoinscricoeseacessos', 'block_gerenciamento'), '/blocks/gerenciamento/Relatorios/RelatoriosAdministrativos/graficoinscricoeseacessos.php');
$PAGE->set_pagelayout('incourse');

if (has_capability('block/gerenciamento:graficoinscricoeseacessos', $context)) {

	echo '<script src="//code.jquery.com/jquery-1.10.2.js"></script>
		  <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
		  <script src="../RelatoriosUser/grafico/js/highcharts.js"></script>
		  <script src="../RelatoriosUser/grafico/js/modules/exporting.js"></script>';

	echo $OUTPUT->heading(get_string('graficoinscricoeseacessos', 'block_gerenciamento'));
	echo $OUTPUT->header();

	echo $OUTPUT->heading(get_string('descricao', 'block_gerenciamento'));
	echo $OUTPUT->heading(get_string('descricaograficoinscricoeseacessos', 'block_gerenciamento'), 6);

	$parametros = array("curso" => $curso);
	$form = new blocks_gerenciamento_graficoinscricoeseacessos_form($CFG->wwwroot.'/blocks/gerenciamento/Relatorios/RelatoriosAdministrativos/graficoinscricoeseacessos.php', $parametros);

	if ($data = $form->get_data()) {

		$sqlinscricao="select ue.id,FROM_UNIXTIME(ue.timestart , '%Y') as ano,count(*) as total_inscritos
				 	   from {user_enrolments} ue 
				 	   left join {enrol} e on ue.enrolid=e.id ";
		if($data->curso > 1){
			$sqlinscricao .= " inner join {user} u on ue.userid=u.id 
								inner join {course} co on e.courseid = co.id and co.id=".$data->curso;
		}
		$sqlinscricao .= " where e.enrol='manual' and FROM_UNIXTIME(ue.timestart , '%Y')>= {$data->ano}
					 and FROM_UNIXTIME(ue.timestart , '%Y')<=".date("Y",time())." group by ano";

		/*----=-=-=-=-----------=-=-= Comprou e Acessou no Mesmo Ano =-=-=-------------- */

		$anoatual=date('Y',time());
		$sqlacessonoano="select ra.id, FROM_UNIXTIME(tab.time , '%Y') as ano ,count(*) as total
							 from {role_assignments} ra
								inner join {context} ctx on ctx.id = ra.contextid
								left join (	select min(csl.id) as id,csl.user_id as userid, cs.course, csl.inicio_acesso as time 
											from {course_section_log} csl
											left join {course_sections} cs 
											on cs.id=csl.course_section_id
											group by csl.user_id, cs.course) as tab
									 on tab.userid = ra.userid and tab.course = ctx.instanceid 
								left join {course} c on ctx.instanceid=c.id
								left join {user} u on ra.userid=u.id 
								left join {enrol} e on c.id=e.courseid 
								left join {user_enrolments} ue on e.id=ue.enrolid AND u.id=ue.userid 
							where e.enrol = 'manual' and";
		if($data->curso>1){
			$sqlacessonoano.=" c.id=".$data->curso." and ";
			$curso = $DB->get_record('course', array('id' => $data->curso));
		} else {
			$curso = new stdClass();
			$curso->fullname = get_string('allcursos', 'block_gerenciamento');
		}

		$sqlacessonoano.=" FROM_UNIXTIME(tab.time , '%Y')>={$data->ano} and FROM_UNIXTIME(tab.time , '%Y')<={$anoatual} group by ano";

		$ano=array();
		for($i=$data->ano;$i<=date('Y',time());$i++){
			$ano[]=(int) $i;
		}
			
		$inscrito=array_fill($data->ano, date('Y',time())-$data->ano, 0);
		$acesso=$inscrito;

		$resultacesso=$DB->get_records_sql($sqlacessonoano);
		foreach ($resultacesso as $lista){
			$acesso[$lista->ano]=(int)$lista->total;
		}

		$resultinscrito=$DB->get_records_sql($sqlinscricao);
		foreach ($resultinscrito as $lista){
			$inscrito[$lista->ano]=(int)$lista->total_inscritos;
		}

		/*Montando Grafico*/
		######################################################################################################
		$graf = new Highcharts();
		$graf->chart->marginRight = 0;
		$graf->title->text = get_string('titlegraficoinscricoeseacessos', 'block_gerenciamento');
		$graf->subtitle->text=$curso->fullname;
		$graf->xAxis->categories = $ano;
		$graf->yAxis->title = new object();
		$graf->yAxis->title->text = get_string('qtdaluno', 'block_gerenciamento');
		$graf->yAxis->min=0;

		$graf->tooltip = new object();
		$graf->tooltip->formatter = "function";

		$graf->functions['formatter'] = "function() {
						                        return \"<b>\"+ this.series.name +\"</b><br/>\"+
						                        this.x +\": \"+ this.y ;
						                	}";

		$data1 = new object();
		$data1->name = get_string('qtdincricoes', 'block_gerenciamento');
		$data1->data = $inscrito;

		$data2 = new object();
		$data2->name = get_string('qtdacesso', 'block_gerenciamento');
		$data2->data = $acesso;

		$graf->series[] = $data1;
		$graf->series[] = $data2;
		$graf->divStyles = array( "min-width" => "400px" , "height" => "400px", "margin" =>"0 auto");
	}

	$form->display();
	if(isset($graf))
	$graf->display();

} else {
	redirect(new moodle_url($CFG->wwwroot), get_string('erropermissao', 'block_gerenciamento'));
}
echo $OUTPUT->footer();

?>