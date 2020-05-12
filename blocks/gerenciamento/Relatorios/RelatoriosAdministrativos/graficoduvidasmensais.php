<?php
require_once('../../../../config.php');
require_once('forms/graficoduvidasmensais_form.php');
require_once('../RelatoriosUser/grafico/highcharts.php');
require_once('../../lib.php');

global $CFG, $DB;

$curso = optional_param('curso', null, PARAM_INT);

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url('/blocks/gerenciamento/Relatorios/RelatoriosAdministrativos/graficoduvidasmensais.php');
$PAGE->set_title(get_string('graficoduvidasmensais', 'block_gerenciamento'));

$PAGE->navbar->add(get_string('blockname', 'block_gerenciamento'))->
	add(get_string('relatorios', 'block_gerenciamento'))->
	add(get_string('relatoriosadministrativos', 'block_gerenciamento'))->
	add(get_string('graficoduvidasmensais', 'block_gerenciamento'), '/blocks/gerenciamento/Relatorios/RelatoriosAdministrativos/graficoduvidasmensais.php');
$PAGE->set_pagelayout('incourse');

$has_capability = false;
$mycourses = get_my_courses($USER->id);
$mycoursesids = array();
foreach ($mycourses as $mycourse){	
	if (has_capability('block/gerenciamento:graficoduvidasmensais', context_course::instance($mycourse->id))) {
		$mycoursesids[] = $mycourse->id;
		$has_capability = true;
	}
}

if (has_capability('block/gerenciamento:graficoduvidasmensais', $context) || $has_capability) {

	echo '<script src="//code.jquery.com/jquery-1.10.2.js"></script>
		  <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
		  <script src="../RelatoriosUser/grafico/js/highcharts.js"></script>
		  <script src="../RelatoriosUser/grafico/js/modules/exporting.js"></script>';

	echo $OUTPUT->heading(get_string('graficoduvidasmensais', 'block_gerenciamento'));
	echo $OUTPUT->header();

	echo $OUTPUT->heading(get_string('descricao', 'block_gerenciamento'));
	echo $OUTPUT->heading(get_string('descricaograficoduvidasmensais', 'block_gerenciamento'), 6);

	$parametros = array("curso" => $curso);
	$form = new blocks_gerenciamento_graficoduvidasmensais_form($CFG->wwwroot.'/blocks/gerenciamento/Relatorios/RelatoriosAdministrativos/graficoduvidasmensais.php', $parametros);

	if ($data = $form->get_data()) {
			
		if (isset($data->pesquisames)) {
			$subtitle = get_string('subtitleduvidayear', 'block_gerenciamento').$data->pesquisames;
				
			$sql="select FROM_UNIXTIME(d.hora_duvida,'%m') as valor,
									 d.hora_duvida,
									 count(*) as total
								from {tira_duvidas} d 
								where ";
			
			if(!$data->curso or $data->curso == 1){
				$title = get_string('titleduvidaallcursos', 'block_gerenciamento');
				$sql.="FROM_UNIXTIME(d.hora_duvida, '%Y')='".$data->pesquisames."' group by valor";
			}else{
				$sql.="d.idcurso=".$data->curso." and FROM_UNIXTIME(d.hora_duvida, '%Y')='".$data->pesquisames."'
							group by valor";
				$curso = $DB->get_record('course', array('id' => $data->curso));
				$title = get_string('titleduvidacurso', 'block_gerenciamento').$curso->shortname;
			}
		}else{
			$subtitle = get_string('subtitleduvidaallyears', 'block_gerenciamento');

			$sql="select FROM_UNIXTIME(d.hora_duvida,'%m') as valor,
									 d.hora_duvida,
									 count(*) as total
								from {tira_duvidas} d ";

			if($data->curso>1){
				$sql.=" where d.idcurso=".$data->curso;
				$curso = $DB->get_record('course', array('id' => $data->curso));
				$title = get_string('titleduvidacurso', 'block_gerenciamento').$curso->shortname;
			}else{
				$title = get_string('titleduvidaallcursos', 'block_gerenciamento');
			}
			
			$sql.=" group by valor";
		}
		$result = $DB->get_records_sql($sql);

		if($result){
			$curso_pormes= array ('01' => 0, '02' => 0, '03' => 0, '04' => 0, '05' => 0, '06' => 0, '07' => 0, '08' => 0, '09' => 0, '10' => 0, '11' => 0, '12' => 0);

			foreach ($result as $lista){
				$curso_pormes[$lista->valor]=(int)$lista->total;
			}

			$graf = new Highcharts();
			$graf->chart->renderTo = "column";
			$graf->chart->type = "column";
				
			$graf->title->text= $title;
			$graf->subtitle->text= $subtitle;
			$graf->xAxis->categories = array('Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun','Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez');
			$graf->yAxis->title = new object();
			$graf->yAxis->title->text = get_string('qtdduvidas', 'block_gerenciamento');
			$graf->tooltip = new object();
			$graf->tooltip->formatter = "function";
				
			$graf->functions['formatter'] = "function() {
									                    return ''+
									                        this.x +': '+ this.y;
									                }";
				
			$data = new object();
			$data->name = get_string('duvidas', 'block_gerenciamento');
			$data->data = $curso_pormes;
			$graf->series[] = $data;
				
			$graf->divStyles = array( "min-width" => "400px" , "height" => "400px", "margin" =>"0 auto");
		}
	}

	$form->display();
	if(isset($graf))
	$graf->display();

} else {
	redirect(new moodle_url($CFG->wwwroot), get_string('erropermissao', 'block_gerenciamento'));
}
echo $OUTPUT->footer();
?>