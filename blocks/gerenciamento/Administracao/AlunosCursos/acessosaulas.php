<?php
require_once('../../../../config.php');
date_default_timezone_set("Brazil/East");
require_once($CFG->dirroot.'/blocks/gerenciamento/lib.php');

global $CFG, $DB, $USER, $SITE;

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url('/blocks/gerenciamento/Administracao/AlunosCursos/acessosaulas.php');
$PAGE->set_title(get_string('acessostabela', 'block_gerenciamento'));
$PAGE->navigation->add(get_string('acessostabela', 'block_gerenciamento'));

$PAGE->navbar->add(get_string('blockname', 'block_gerenciamento'))->
add(get_string('administracao', 'block_gerenciamento'))->
add(get_string('alunosecursos', 'block_gerenciamento'))->
add(get_string('acessostabela', 'block_gerenciamento'), '/blocks/gerenciamento/Administracao/AlunosCursos/acessosaulas.php');

$PAGE->set_heading($SITE->fullname);

$PAGE->set_pagelayout('incourse');

$userid = required_param('user', PARAM_INT);   // user id
$courseid  = required_param('course', PARAM_INT);   // course id (defaults to Site)

echo $OUTPUT->header();

if (has_capability('block/gerenciamento:acessosaulas', $context)) {

	$course = $DB->get_record('course', array('id'=> $courseid));

	if (!$course)
		print_error('invalidcourseid');

	$user = $DB->get_record('user', array('id'=> $userid));

	if (!$user)
		print_error('invaliduserid');

	$statusCurso = buscaStatusCurso($courseid, $userid);

	if (!$statusCurso->matriculado)
		print_error('invalidcourseid');

	$sql = "SELECT	csl.id,
					csl.inicio_acesso,
					csl.fim_acesso,
					csl.ip,
					cm.module,
					cm.instance,
					cs.section,
					cs.name,
					m.name as modulename,
					csa.tempo_total,
					csa.tempo_utilizado
			FROM {course_section_log} csl
			INNER JOIN {course_modules} cm
			ON cm.id = csl.course_module_id
			AND cm.course = $courseid
			INNER JOIN {course_sections} cs
			ON cs.id = csl.course_section_id
			INNER JOIN {modules} m
			ON m.id = cm.module
			LEFT JOIN {course_section_access} csa
			ON csa.course_section_id = cs.id
			AND csa.user_id = csl.user_id
			WHERE csl.user_id = $userid
			ORDER BY cs.section,
					 csl.id";

	$result = $DB->get_records_sql($sql);

	echo $OUTPUT->heading($course->fullname, 2);
	echo $OUTPUT->heading(get_string('user') . ': ' . fullname($user), 5);

	$tempo_total_utilizado = 0;

	if(count($result)){
		$table = new html_table();
		$table->head = array(get_string('acesso', 'block_gerenciamento'),
							 get_string('topico', 'block_gerenciamento'),
							 get_string('entrada', 'block_gerenciamento'),
							 get_string('saida', 'block_gerenciamento'),
							 get_string('tempoacesso', 'block_gerenciamento'),
							 get_string('ip', 'block_gerenciamento')
							);
		$table->align = array ("center", "center", "center", "center", "center", "center");

		$i = 1;
		$section = -1;

		foreach ($result as $r){
			if($section <> $r->section){
				if($section != -1){
					echo html_writer::table($table);

					echo html_writer::table($table_total);

					$table_total = new html_table();

					$table = new html_table();
					$table->head = array(get_string('acesso', 'block_gerenciamento'),
										 get_string('topico', 'block_gerenciamento'),
										 get_string('entrada', 'block_gerenciamento'),
										 get_string('saida', 'block_gerenciamento'),
										 get_string('tempoacesso', 'block_gerenciamento'),
										 get_string('ip', 'block_gerenciamento')
					);
					$table->align = array ("center", "center", "center", "center", "center", "center");
				}

				echo $OUTPUT->heading($r->name, 3);

				$section = $r->section;
				$i=1;

				$tempo_total_utilizado = $tempo_total_utilizado + $r->tempo_utilizado;
			}

			$sql = "SELECT m.name
					FROM {{$r->modulename}} m
					WHERE m.id = $r->instance";

			$name = $DB->get_record_sql($sql);

			$tempo_acesso = $r->fim_acesso - $r->inicio_acesso;

			$inicio_acesso = new DateTime();
			$inicio_acesso->setTimestamp($r->inicio_acesso);
			$fim_acesso = new DateTime();
			$fim_acesso->setTimestamp($r->fim_acesso);
			$tempo_acesso=$inicio_acesso->diff($fim_acesso);

			$tempo_acesso_format = $tempo_acesso->format('%H:%I:%S');

			if($tempo_acesso->format('%d') > 0)
				$tempo_acesso_format = $tempo_acesso->format('%d dia e %H:%I:%S');

			$table->data[] = array ($i++,
									$name->name,
									date("d/m/Y H:i:s", $r->inicio_acesso),
									date("d/m/Y H:i:s", $r->fim_acesso),
									$tempo_acesso_format,
									($r->ip == '') ? '-' : $r->ip);

			$table_total = new html_table();
			$table_total->head  = array (get_string('tempototal', 'block_gerenciamento') . $r->section, get_string('temporestante', 'block_gerenciamento'));
			$d1 = new DateTime();
			$d2 = new DateTime();
			$d3 = new DateTime();

			$d2->setTimestamp($d2->getTimestamp() + $r->tempo_utilizado);
			$d3->setTimestamp($d3->getTimestamp() + $r->tempo_total);

			$tempo_utilizado = $d1->diff($d2);
			$tempo_restante = $d2->diff($d3);

			if($tempo_restante->invert === 1){
				$tempo_restante_str = "00:00:00";
			}else{
				$tempo_restante_str = $tempo_restante->format('%H:%I:%S');
			}

			$table_total->data[] = array ($tempo_utilizado->format('%H:%I:%S'), $tempo_restante_str);
		}

		echo html_writer::table($table);
		echo html_writer::table($table_total);
	}else{
		echo $OUTPUT->notification(get_string('nenhumacesso', 'block_gerenciamento'));
	}

	$table = new html_table();
	$table->head  = array(get_string('iniciocurso', 'block_gerenciamento'),
		                  get_string('totalacessos', 'block_gerenciamento'),
						  get_string('tempototalcurso', 'block_gerenciamento'),
						  get_string('diasrestantes', 'block_gerenciamento'));

	$table->align = array ("center", "center", "center", "center");

	$d1 = new DateTime();
	$d2 = new DateTime();
	$d2->setTimestamp($d2->getTimestamp() + $tempo_total_utilizado);
	$tempo_total_utilizado = $d1->diff($d2);

	$table->data[] = array (date("d/m/Y", $statusCurso->datainicio),
							count($result),
							$tempo_total_utilizado->format('%H:%I:%S'),
							$statusCurso->diasrestantes * 1);

	echo html_writer::table($table);

} else {
	redirect(new moodle_url($CFG->wwwroot), get_string('erropermissao', 'block_gerenciamento'));
}

echo $OUTPUT->footer();

?>