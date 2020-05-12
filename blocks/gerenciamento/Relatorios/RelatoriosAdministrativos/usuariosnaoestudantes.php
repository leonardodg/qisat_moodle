<?php
require_once('../../../../config.php');
require_once('forms/usuariosnaoestudantes_form.php');

global $CFG, $DB;

$curso = optional_param('curso', null, PARAM_INT);

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url('/blocks/gerenciamento/Relatorios/RelatoriosAdministrativos/usuariosnaoestudantes.php');
$PAGE->set_title(get_string('usuariosnaoestudantes', 'block_gerenciamento'));

$PAGE->navbar->add(get_string('blockname', 'block_gerenciamento'))->
	add(get_string('relatorios', 'block_gerenciamento'))->
	add(get_string('relatoriosadministrativos', 'block_gerenciamento'))->
	add(get_string('usuariosnaoestudantes', 'block_gerenciamento'), '/blocks/gerenciamento/Relatorios/RelatoriosAdministrativos/usuariosnaoestudantes.php');
$PAGE->set_pagelayout('incourse');

if (has_capability('block/gerenciamento:usuariosnaoestudantes', $context)) {

	$link = $CFG->wwwroot.'/blocks/gerenciamento/Relatorios/RelatoriosAdministrativos/usuariosnaoestudantes.php';
	$parametros = array('curso'=>$curso);
	$form = new blocks_gerenciamento_usuariosnaoestudantes_form($link, $parametros);

	if ($data = $form->get_data()) {
		$curso = $data->curso;
	}

	echo $OUTPUT->heading(get_string('usuariosnaoestudantes', 'block_gerenciamento'));
	echo $OUTPUT->header();

	echo $OUTPUT->heading(get_string('descricao', 'block_gerenciamento'));
	echo $OUTPUT->heading(get_string('descricaousuariosnaoestudantes', 'block_gerenciamento'), 6);

	$form->display();

	if(isset($curso)){

		$sqlCurso = "";
		if($curso){
			$sqlCurso = " and c.id = $curso ";
		}

		$sql = "select a.id,u.id as iduser,u.firstname as nome,u.lastname as sobrenome,r.name as perfil,c.id as courseid,c.fullname as coursename,
						if(c.shortname is null,'".get_string('todoscursos','block_gerenciamento')."',c.shortname) as curso
					from {role_assignments} a
						left join {role} r on a.roleid=r.id	
						left join {user} u on a.userid=u.id	
						left join {context} ctx on a.contextid=ctx.id
						left join {course} c on ctx.instanceid=c.id	$sqlCurso
					 where roleid not in(5,6,7,8,9,10) $sqlCurso 
							group by nome,sobrenome,curso,perfil
							order by a.contextid, perfil, nome, sobrenome";

		$result = $DB->get_records_sql($sql);
		if($result){
			$tableTotal = new html_table();
			$tableTotal->width = "100%";
			$tableTotal->align = array ("center");
			$tableTotal->head = array("Total");
			$tableTotal->data[] = array(count($result));
			echo '<br>';
			echo html_writer::table($tableTotal);

			$table = new html_table();
			$table->width = "100%";
		
			$prev_course = '';
			foreach ($result as $r){
				if (!$r->courseid){
					$r->courseid = 1;
					$r->coursename = get_string('todoscursos','block_gerenciamento');
				}

				if($prev_course != $r->courseid){
					$prev_course = $r->courseid;
					echo html_writer::table($table);
					
					echo '<br>';
					echo $OUTPUT->heading($r->coursename, 5);

					$table = new html_table();
					$table->width = "100%";
					$table->head = array (get_string('course'),get_string('name'),get_string('profile'));
				}
				$name = '<a href="'.$CFG->wwwroot.'/user/view.php?id='.$r->iduser.'">'.$r->nome.' '.$r->sobrenome.'</a>';
				$table->data[] = array($r->curso,$name,$r->perfil);
			}
			echo html_writer::table($table);
		}else{
			echo '<br>';
			echo $OUTPUT->notification(get_string('nodados', 'block_gerenciamento'));
		}
	}
} else {
	redirect(new moodle_url($CFG->wwwroot), get_string('erropermissao', 'block_gerenciamento'));
}
echo $OUTPUT->footer();
?>