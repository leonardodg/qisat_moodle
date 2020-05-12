<?php
ini_set('max_execution_time','360000');
require_once('../../../../config.php');
require_once('grafico/highcharts.php');
require_once('forms/relatoriouserturma_form.php');
require_once('../../lib.php');

global $CFG, $DB;

$courseid = optional_param('course', 0, PARAM_INT);

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url('/blocks/gerenciamento/Relatorios/RelatoriosUser/relatoriouserturma.php');
$PAGE->set_title(get_string('relatoriouserturma', 'block_gerenciamento'));

$PAGE->navbar->add(get_string('blockname', 'block_gerenciamento'))->
	add(get_string('relatorios', 'block_gerenciamento'))->
	add(get_string('relatoriosusuarios', 'block_gerenciamento'))->
	add(get_string('relatoriouserturma', 'block_gerenciamento'), '/blocks/gerenciamento/Relatorios/RelatoriosUser/relatoriouserturma.php');
$PAGE->set_pagelayout('incourse');

$variaveis = array('course' => $courseid);

if (has_capability('block/gerenciamento:relatoriouserturma', $context)) {

	echo '<script src="//code.jquery.com/jquery-1.10.2.js"></script>
		  <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
		  <script src="grafico/js/highcharts.js"></script>
		  <script src="grafico/js/modules/exporting.js"></script>';

	echo $OUTPUT->heading(get_string('relatoriouserturma', 'block_gerenciamento'));
	echo $OUTPUT->header();

	echo $OUTPUT->heading(get_string('descricao', 'block_gerenciamento'));
	echo $OUTPUT->heading(get_string('descricaorelatoriouserturma', 'block_gerenciamento'), 6);
	
	$relatoriouserturma_form = new blocks_gerenciamento_relatoriouserturma_form($CFG->wwwroot.'/blocks/gerenciamento/Relatorios/RelatoriosUser/relatoriouserturma.php', $variaveis);
	$relatoriouserturma_form->display();

	if ($dataForm = $relatoriouserturma_form->get_data()){

		$courseid = (int)$dataForm->course;
		$groupid = (int)$dataForm->{'selectturma_'.$dataForm->cursoselected};

		if($courseid){
			$context = context_course::instance($courseid);
			$curso = $DB->get_record('course', array('id'=>$courseid));

			if(!$groupid){
				$groupid = '';
				$groupids = $DB->get_records('groups', array('courseid'=>$courseid));
				foreach ($groupids as $g){
					$groupid .= $g->id . ',';
				}
				$groupid = substr($groupid, 0, -1);
			}

			if ($courseid > 1){
				$sql = "SELECT turma.userid as chave, turma.userid, user.idnumber
			 				from {groups_members} as turma 
								inner join {role_assignments} as matricula on (matricula.userid = turma.userid and matricula.roleid = 5 and matricula.contextid = {$context->id})
								inner join {user} as user on (matricula.userid = user.id) 
							where ( turma.groupid in ({$groupid}) ) 
							order by turma.userid;";

				$andcourse = "and (cs.course = {$courseid})";
			}

			$usersGroup = $DB->get_records_sql($sql);

			$usersSql = "(";
			$usersIdNumber = "(";

			if(empty($usersGroup)){
				$usersSql .= '0';
				$usersIdNumber .= '0';
			}else{
				foreach($usersGroup as $user){
					$usersSql .= $user->userid.',';
					$usersIdNumber .= $user->idnumber.',';
				}
				$usersSql = substr($usersSql, 0, -1);
				$usersIdNumber = substr($usersIdNumber, 0, -1);
			}
		
			$usersSql .= ")";
			$usersIdNumber .= ")";

			$sql = "SELECT csa.user_id as chave, csa.user_id, cs.course 
						from {course_section_access} csa 
						inner join {course_sections} cs 
						on cs.section = csa.course_section_id 
						where csa.user_id in {$usersSql} 
						{$andcourse} group by csa.user_id order by csa.user_id";

			$usersLog = $DB->get_records_sql($sql);

			$sql = "SELECT p.userid as chave, p.userid 
						from {prorrogacoes} p where p.courseid = {$courseid} and p.userid in {$usersSql} order by p.userid;";

			$usersProrrogacao = $DB->get_records_sql($sql);

			$contNoAcesso = 0;
			$contExpirado = 0;
			$contFinalizado = 0;
			$contLiberado = 0;
			$contProrrogacao = 0;
			
			foreach ($usersGroup as $chaveGroup => $user){

				$course = get_my_course($user->userid, $courseid);

				switch (buscaStatusCurso($courseid, $user->userid)->situacao) {
					case "finalizado":
						$contFinalizado++;
						break;
					case "expirado":
						$contExpirado++;
						break;
					case "liberado":
						$contLiberado++;
						break;
				}
					
				if(array_key_exists($chaveGroup, $usersProrrogacao))
					$contProrrogacao++;
					
				if(!array_key_exists($chaveGroup, $usersLog))
					$contNoAcesso++;
			}

			$grafico = new Highcharts();
			$grafico->divStyles = array( "min-width" => "400px" , "height" => "400px" , "margin" =>"0 auto" );

			$grafico->chart->renderTo = "pizza";
			$grafico->chart->type = "pie";	
			$grafico->chart->plotBackgroundColor = null;
			$grafico->chart->plotBorderWidth = null;
			$grafico->chart->plotShadow = false;

			$grafico->title->text = get_string('relatorioturma', 'block_gerenciamento');
			$grafico->subtitle->text = $curso->fullname;

			$grafico->tooltip = new stdClass();
			$grafico->tooltip->formatter = "function";
			$grafico->functions['tooltip']['formatter'] = "function() { return '<b>'+ this.point.name+'<br/> Quantidade: '+this.point.y+'</b><br/>'; }";

			$grafico->plotOptions = new stdClass();
			$grafico->plotOptions->pie = new stdClass();
			$grafico->plotOptions->pie->allowPointSelect = true;
			$grafico->plotOptions->pie->cursor = "pointer";
			$grafico->plotOptions->pie->dataLabels = new stdClass();
			$grafico->plotOptions->pie->dataLabels->enabled = true;
			$grafico->plotOptions->pie->showInLegend =  true;
			$grafico->plotOptions->pie->dataLabels->formatter = "function";

			$grafico->functions['dataLabels']['formatter'] = "function() {
	                          return this.percentage.toFixed(2) +' %';   }";

			$data = new stdClass();
			$data->type = "pie";
			$data->data = array();
			$data->data[] = array (get_string('noacesso', 'block_gerenciamento'), (int)$contNoAcesso );
			$data->data[] = array (get_string('endcurso', 'block_gerenciamento'), (int)$contFinalizado );
			$data->data[] = array (get_string('bloqueado', 'block_gerenciamento'), (int)$contExpirado );
			$data->data[] = array (get_string('ativo', 'block_gerenciamento'), (int)$contLiberado );
			$data->data[] = array (get_string('prorrogacao', 'block_gerenciamento'), (int)$contProrrogacao );

			$grafico->series[] = $data;
			
			$grafico->display();
		}
	}
} else {
	redirect(new moodle_url($CFG->wwwroot), get_string('erropermissao', 'block_gerenciamento'));
}
echo $OUTPUT->footer();

?>